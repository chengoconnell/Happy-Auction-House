<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php
  // For now, index.php just redirects to browse.php, but you can change this
  // if you like.
  if(!isset($_SESSION['logged_in'])) 
  {
    echo "Directing to browse page.";
    header("refresh:2;url=browse.php");
  }
  else 
  {
    if ($_SESSION['account_type'] == 'buyer') {
      echo "Welcome! Redirecting to browse page.";
      header("refresh:2;url=browse.php");}
    if ($_SESSION['account_type'] == 'seller'){
      echo "Welcome! Redirecting to my listings page.";
      header("refresh:2;url=mylistings.php");}
  }
?>