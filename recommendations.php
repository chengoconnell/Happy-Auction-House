<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php include 'opendb.php'?>
<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
//Sets results per page, can modify as you like
$results_per_page = 5;

//Checks to see if a buyer is logged in
if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer')
{
	$user_id = $_SESSION['user_id'];
}

//Query takes live bids that users with similar bidding history have bid on 
	$recco_listing_query = "SELECT * FROM listings WHERE (CURRENT_TIMESTAMP<endtime) AND listing_id IN(
								SELECT DISTINCT listing_id FROM bids WHERE user_id IN(
									SELECT user_id FROM bids WHERE listing_id IN(
										SELECT listing_id FROM bids WHERE user_id = $user_id)
									AND user_id != $user_id)
							AND listing_id NOT IN(SELECT listing_id FROM bids WHERE user_id = $user_id)) LIMIT $results_per_page";

//Counts the number of these bids for pagination 
	$num_recco_query = "SELECT COUNT(*) FROM listings WHERE (CURRENT_TIMESTAMP<endtime) AND listing_id IN(
								SELECT DISTINCT listing_id FROM bids WHERE user_id IN(
									SELECT user_id FROM bids WHERE listing_id IN(
										SELECT listing_id FROM bids WHERE user_id = $user_id)
									AND user_id != $user_id)
							AND listing_id NOT IN(SELECT listing_id FROM bids WHERE user_id = $user_id))";

	$num_recco_result = mysqli_query($connection, $num_recco_query)
			or die('Error making recco count query');

	$row = mysqli_fetch_array($num_recco_result);

	if ($row[0] < 1) {
		$max_page = 1;
	}
	else {
		$max_page = ceil($row[0] / $results_per_page);
	}
	if (!isset($_GET['page']))
		{
		$curr_page = 1;
		}
	else
	{
		if ($_GET['page'] == 1)
		{
			$curr_page = 1;
		}
		else
		{
			//This limits the number of answers per page to $results_per_page, and ensures not the same 'x' results are printed on each page using SQL 'offset'
			$curr_page = $_GET['page'];
			$offset = ($curr_page*$results_per_page)-$results_per_page;
			$recco_listing_query .= " OFFSET $offset";

		}
	}
?>

<div class="container mt-5">

<?php
//Helpful message for when there are no reccos
if ($row[0]  < 1) {
		echo ("Sorry, either you have not made any bids, or other users who have a similar bid history have not bid on anything you have not bid on. <br><br>

		As such, we have no reccomendations right now! Check again soon!");
	}
?>


<ul class="list-group">


<?php
	//Get results of $query_ordered so we can print to user
	$result = mysqli_query($connection, $recco_listing_query)
		or die('Error making recco select id query');

	while ($row = mysqli_fetch_array($result))
	{
		//Count the number of bids
		$count_bid_query = "SELECT COUNT(*) FROM bids WHERE listing_id = {$row['listing_id']}";

		$count_bid_result = mysqli_query($connection, $count_bid_query)
			or die('Error making top bid query');

		$bid_count = mysqli_fetch_array($count_bid_result);

		//Use print listing function to print the listings. If not bids then show start price, else show highest bid
		if ($bid_count[0] == 0) {
			print_listing_li($row['listing_id'], $row['item_title'], $row['itemdescription'], $row['startprice'], $bid_count[0], date_create($row['endtime']));
		}
		else {

			$top_bid_query = "SELECT MAX(bidprice) FROM bids WHERE listing_id = {$row['listing_id']}";

			$top_bid_result = mysqli_query($connection, $top_bid_query)
				or die('Error making top bid query');

			$top_bid = mysqli_fetch_array($top_bid_result);

			print_listing_li($row['listing_id'], $row['item_title'], $row['itemdescription'], $top_bid[0], $bid_count[0], date_create($row['endtime']));

		}

	}
	mysqli_close($connection);

?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">

<?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }

  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

  if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }

  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }

    // Do this in any case
    echo('
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="recommendations.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>
