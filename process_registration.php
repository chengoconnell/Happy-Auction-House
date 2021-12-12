<?php

//connecting to database

$servername = 'localhost';
$username = 'COMP0022';
$password = 'test';
$database_name = 'testdb';
$connect=mysqli_connect($servername, $username, $password, $database_name);
	
//checking connection and printing whether or not it connected	
if($connect){
}
else{
	echo "Connection failed";
}

//defining POST variables
//email validity check
//injection protection
$email1 = htmlspecialchars(mysqli_real_escape_string($connect, $_POST['email']));


$email = (filter_var($email1, FILTER_SANITIZE_EMAIL));

//defining variables from html input form
$Password = htmlspecialchars(mysqli_real_escape_string($connect, $_POST['password']));
$repeat_password = $_POST['passwordrepeat'];
$accounttype = $_POST['accountType'];

//variable is initiated depending on if user signs up as buyer of seller
if ($accounttype == "buyer"){
	$typevar = 0;
}
if ($accounttype == "seller"){
	$typevar = 1;
}



//Creating insert code to insert registration into user table of testdb database

$query = "INSERT INTO users (email, password, type) VALUES ('$email', SHA('$Password'),$typevar)";

//cheking all where email already exists
$emailquery = ("SELECT * FROM users WHERE email = '$email'");



$emailresult = mysqli_query($connect, $emailquery);

//checking if email is in more than zero rows already
$emailcheck = mysqli_num_rows($emailresult)>0;

/*testing many inputs for registration
such as password length, password match, and whitespces */

if($emailcheck){
	echo " Email already registered. ";
	header('Refresh:3, url=browse.php'); 
}
else{
if (filter_var($email, FILTER_VALIDATE_EMAIL)){
	if ($Password != $repeat_password){
		echo " Passwords do not match. ";
	}
	elseif (strlen($Password) < 5){
		echo " Password must contain at least 5 characters. ";
	}
	elseif (strpos($Password, " ") !== false){
		echo " Password must not contain white spaces. ";
	}
	else{
		$result = mysqli_query($connect, $query);
		header('Refresh:3, url=browse.php');
	}
	

}
else{
	echo " Email or password is not valid. ";
}
}


/*only inserting data if the password and password repeat match, password contains not whitespaces,
password is at least 5 characters, and email is valid/available.
noting to user that information did not insert*/




// telling user they registered successfully and returning the email.  

if (isset($result)){
	echo " Registered successfully. Registered with email: $email ";
}
else{
	echo " Registered unsuccessfully. ";
	header('Refresh:3; url=register.php'); //add sessions later.  
}


mysqli_close($connect);
	
?> 



