<?php include 'opendb.php'?>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


//Get the emails of people who are watching an item
$watchlist_email_query = "SELECT email FROM users WHERE id IN(SELECT user_id FROM watchlist WHERE listing_id = $item_id)";
$watchlist_result = mysqli_query($connection, $watchlist_email_query)
	or die('Error making watchlist email query');

// Gets the email of the person placing the bid
$buyer_email_query = "SELECT email FROM users WHERE id= $user_id";
$result = mysqli_query($connection, $buyer_email_query)
	or die('Error making buyer email query');
$buyer_email = mysqli_fetch_array($result);

//Gets the title of the auction in question
$listing_title_query = "SELECT item_title FROM listings WHERE listing_id = $item_id";
$title_result = mysqli_query($connection, $listing_title_query)
	or die('Error making listing title query');
$listing_title = mysqli_fetch_array($title_result);

//Gets the email of the outbid buyer
$outbid_query = "SELECT email FROM users WHERE id IN(
						SELECT user_id FROM bids WHERE listing_id = $item_id AND bidprice = $previous_top_bid)";
$outbid_result = mysqli_query($connection, $outbid_query)
	or die('Error making outbid email query');
$outbid_email = mysqli_fetch_array($outbid_result);


function smtpmailer($to, $from, $from_name, $subject, $body)
    {

        $mail = new PHPMailer();

        /* Tells PHPMailer to use SMTP. */
        $mail->isSMTP();
        $mail->Mailer = "smtp";
        $mail->SMTPDebug  = false;
        $mail->SMTPAuth   = TRUE;
        $mail->SMTPSecure = "tls";
        /* SMTP parameters. */
        $mail->Port       = 465;
        $mail->Host       = "ssl://smtp.gmail.com";
        $mail->Username   = "happyauctionhouse@gmail.com";
        $mail->Password   = "badpassword";
        $mail->IsHTML(true);
        /* Add a recipient. */
        $mail->AddAddress($to, 'User');
        /* Set the mail sender. */
        $mail->SetFrom($from, $from_name);
        /* Set the subject. */
        $mail->Subject = $subject;
        /* Set the mail message body. */
        $mail->Body = $body;
        /* Finally send the mail. */
        if(!$mail->Send())
        {
            $error ="Email failed to send.";
            return $error;
        }
        else
        {
            $error = "Thank You !! Your email is sent.";
            return $error;
        }
    }

?>

<?php

$buyer_notified = 0;

if ($buyer_email[0] == $outbid_email[0]) {
	$name = "Happy Auction House"; //sender’s name
	$from = "happyauctionhouse@gmail.com"; //sender’s e mail address
	$recipient = "$buyer_email[0]"; //recipient
	$mail_body = "This is an email confirming that you have bid £$bid_price on the auction called '$listing_title[0]'"; //mail body
	$subject = "Bid confirmation"; //subject
	$error = smtpmailer($recipient,$from,$name,$subject,$mail_body); //mail function
	$buyer_notified = 1; 
}
else {
	$name = "Happy Auction House"; //sender’s name
	$from = "happyauctionhouse@gmail.com"; //sender’s e mail address
	$recipient = "$outbid_email[0]"; //recipient
	$mail_body = "Your bid of £$previous_top_bid on the auction called '$listing_title[0]' has been outbid. The current highest bid is now £$bid_price"; //mail body
	$subject = "Outbid notification"; //subject
	$error = smtpmailer($recipient,$from,$name,$subject,$mail_body); //mail function
}

while ($row = mysqli_fetch_array($watchlist_result))
{
	if ($row[0] == $outbid_email[0]) {
		continue;
	}
	
	if (($row[0] == $buyer_email[0]) and ($buyer_notified == 1)) {
		continue;
	}
	
	if ($row[0] == $buyer_email[0]) {
		$name = "Happy Auction House"; //sender’s name
		$from = "happyauctionhouse@gmail.com"; //sender’s e mail address
		$recipient = "$buyer_email[0]"; //recipient
		$mail_body = "This is an email confirming that you have bid £$bid_price on the auction called '$listing_title[0]'"; //mail body
		$subject = "Bid confirmation"; //subject
		$error = smtpmailer($recipient,$from,$name,$subject,$mail_body); //mail function
	}
	else {
		$name = "Happy Auction House"; //sender’s name
		$from = "happyauctionhouse@gmail.com"; //sender’s e mail address
		$recipient = "$row[0]"; //recipient
		$mail_body = "The auction called '$listing_title[0]' which you are watching just received a bid for £$bid_price "; //mail body
		$subject = "Auction you are watching"; //subject
		$error = smtpmailer($recipient,$from,$name,$subject,$mail_body); //mail function
	}
}


?>
