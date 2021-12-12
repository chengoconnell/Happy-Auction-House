<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.

include_once 'opendb.php'; 
$email_nocheck = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['email']));
$input_email = (filter_var($email_nocheck, FILTER_SANITIZE_EMAIL));
$input_password = htmlspecialchars(mysqli_real_escape_string($connection, $_POST["password"]));

// Retreive the form input from header.php

$check_query = "SELECT * FROM users WHERE email='$input_email' AND password=SHA('$input_password');";
$check_result = mysqli_query($connection,$check_query);
$check = mysqli_fetch_array($check_result);                     // Make query to the database and fetch stored user information

if (!$check)
{ 
    echo('<div class="text-center">Login Failed. Email does not exist or Password Incorrect.</div>'); 
    mysqli_close($connection);
    header("refresh:5;url=index.php");
}                                                              // Invalid input, Login Failed, Return to browse page

else 
{  
    $_SESSION['logged_in'] = true;                            
    $_SESSION['user_id'] = $check['id'];

    if ($check['type']==1) {$_SESSION['account_type'] = 'seller';}
    if ($check['type']==0) {$_SESSION['account_type'] = 'buyer';}

    if(!isset($_SESSION['account_type'])) {$_SESSION['account_type']= 'visitor';}  
    
    // If the user was not registered as buyer or seller, mark him/her as a visitor

    echo('<div class="text-center">You are now logged in as: '. $_SESSION['account_type'] . ', You will be redirected shortly.</div>');
    mysqli_close($connection);
    header("refresh:2;url=index.php");                         // Tell user of login success and give redirection
}

?>