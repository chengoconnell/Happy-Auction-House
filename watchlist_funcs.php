 <?php
session_start();
include 'opendb.php';

$user_id = $_SESSION['user_id'];

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}

// Extract arguments from the POST variables:
$item_id = $_POST['arguments'];

if ($_POST['functionname'] == "add_to_watchlist") {
  // TODO: Update database and return success/failure.
  $add_to_watch_query = "INSERT INTO watchlist VALUES ($user_id, $item_id[0])";
  mysqli_query($connection,$add_to_watch_query)
	or die("$add_to_watch_query");
  
  $res = "success";
}
else if ($_POST['functionname'] == "remove_from_watchlist") {
  // TODO: Update database and return success/failure.
  $remove_watch_query = "DELETE FROM watchlist WHERE user_id = $user_id AND listing_id = $item_id[0]";
  mysqli_query($connection,$remove_watch_query)
	or die("$remove_watch_query");
  $res = "success";
}
mysqli_close($connection);
// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>