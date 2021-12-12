<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php

	// Get info from the URL:
  $item_id = $_GET['item_id'];
  $listing_query = "SELECT endtime, itemdescription, item_title, listing_id, startprice FROM listings WHERE listing_id = '$item_id'";

  $_SESSION['bided_item_id'] = $item_id; // Add the viewed item id into session info.

	//Use item_id to make a query to the database.
	include 'opendb.php';
	$result = mysqli_query($connection, $listing_query)
	or die('Error making select users query');

	$row = mysqli_fetch_array($result);

	$count_bid_query = "SELECT COUNT(*) FROM bids WHERE listing_id = {$row['listing_id']}";

		$count_bid_result = mysqli_query($connection, $count_bid_query)
			or die('Error making top bid query');

		$bid_count = mysqli_fetch_array($count_bid_result);

	$title = $row[2];
	$description = $row[1];
	$num_bids = $bid_count[0];
	$end_time = date_create($row[0]);


	if ($num_bids == 0)
	{
		$current_price = $row['startprice'];
	}

	 else
	{
		$top_bid_query = "SELECT MAX(bidprice) FROM bids WHERE listing_id = '$item_id'";

		$top_bid_result = mysqli_query($connection, $top_bid_query)
			or die('Error making top bid query');

		$top_bid = mysqli_fetch_array($top_bid_result);

		$current_price = $top_bid[0];
	}

  $_SESSION['bided_current_price'] = $current_price; // Add the viewed item's current price into session info.


	// Calculate time to auction end:
	$now = new DateTime();

	if ($now < $end_time) {
		$time_to_end = date_diff($now, $end_time);
		$time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
	}

	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true)
	{
		$has_session = true;
		$user_id = $_SESSION['user_id'];
		$watch_check_query = "SELECT * FROM watchlist WHERE user_id = $user_id AND listing_id = $item_id";
		$watch_check_result = mysqli_query($connection, $watch_check_query)
			or die('Error making watch check query');

		$watch_bool = mysqli_fetch_array($watch_check_result);
		if ($watch_bool == "") {
			$watching = false;
		}
		else {
			$watching = true;
		}
	}
	else
	{
		$has_session = false;
		$watching = false;
	}
mysqli_close($connection);


?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
<?php if ($now > $end_time): ?>
     This auction ended <?php echo(date_format($end_time, 'j M H:i')) ?>
     <!-- TODO: Print the result of the auction here? -->
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>

    <!-- Bidding form -->
    <form method="POST" action="place_bid.php">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" class="form-control" id="bid" name="bid">
      </div>
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>
<?php endif ?>


  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>


<script>
// JavaScript functions: addToWatchlist and removeFromWatchlist.


function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success:
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
		console.log(objT);
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success:
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();

        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func


</script>
