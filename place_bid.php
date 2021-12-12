<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
include_once 'opendb.php'; 
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && $_SESSION['account_type']=='buyer')  // Check if the user has loggin in as a buyer
{
	$user_id = $_SESSION['user_id'];
    $bid_price = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['bid']));
    $item_id = $_SESSION['bided_item_id'];         
    $current_price = $_SESSION['bided_current_price'];                 // retreive necessary information for placing the bid
    $previous_top_bid = $current_price;
	
    if ($bid_price<=$current_price)
    {
        echo "Invalid price entered. Place bid unsuccessful.";
        header("refresh:2;url=browse.php");                      // check if the user input price is higher than current price
    }

    else
    {
        date_default_timezone_set("Europe/London");
        $bid_time = date("Y-m-d H:i:s");                               // get the time of placing bid (in London timezone)

        $bid_query = "INSERT INTO bids (user_id, listing_id, bidtime, bidprice) VALUES ($user_id, $item_id, '$bid_time', $bid_price)";
        $bid_result = mysqli_query($connection,$bid_query) or die(" Insert into database unsuccessfull.");
                                                                    // insert the bid information to database
        if($bid_result)
        {
            echo "Bid placed successfully at: " . $bid_time;
			
			
			include_once 'watchlist_notif.php';
            header("refresh:2;url=browse.php");                  // notify the user of success bid placement and redirection
        }
        mysqli_close($connection);
    }
}

else
{
    echo "You are not logged in with a buyer account. Try again.";      // The user is not loggin in or not as a buyer
    header("refresh:2;url=browse.php");
}
?>