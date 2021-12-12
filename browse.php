<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php include 'opendb.php'?>
<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" name="keyword" id="keyword" placeholder="Search for anything">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat" >
		<option selected value="all">All categories</option>
		
<!-- Loop to pull categories from table into the drop down menu -->
		
		<?php $cat_query = "SELECT name FROM categories ORDER BY name";
		$cat_result = mysqli_query($connection, $cat_query)
			or die('Error making select cat query');
	
		while ($cat_row = mysqli_fetch_array($cat_result)) {
			echo ('<option value='. $cat_row[0]. '>'. $cat_row[0] .'</option>');
		}
		?>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option selected value="pricelow">Price (low to high)</option>
          <option value="pricehigh">Price (high to low)</option>
          <option value="date">Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->


</div>

<?php
// Can choose the number of results per page you would like
	$results_per_page = 5;
	
// Checks if Keyword exists 
	if (!isset($_GET['keyword']))
	{
		 $query = "SELECT listings.listing_id, listings.item_title, listings.itemdescription, MAX(bids.bidprice), listings.startprice, listings.endtime
FROM listings LEFT JOIN bids ON listings.listing_id=bids.listing_id WHERE item_title IS NOT NULL";
	}

	else
	{
		// Sets keyword Variable - prevents injection attack
		$keyword = htmlspecialchars(mysqli_real_escape_string($connection,$_GET['keyword']));
		
		// Checks if Keyword is blank
		if ($keyword == '')
		{
			 // If blank set query to any item title 
			 $query = "SELECT listings.listing_id, listings.item_title, listings.itemdescription, MAX(bids.bidprice), listings.startprice, listings.endtime
FROM listings LEFT JOIN bids ON listings.listing_id=bids.listing_id WHERE item_title IS NOT NULL";
		}
		else
		{
			 // Otherwise check if item title is like keyword searched 
			 $query = "SELECT listings.listing_id, listings.item_title, listings.itemdescription, MAX(bids.bidprice), listings.startprice, listings.endtime
FROM listings LEFT JOIN bids ON listings.listing_id=bids.listing_id WHERE item_title LIKE '%$keyword%'";
		}
	}
// Checks if category exists 
	if (!isset($_GET['cat']))
	{
		 $query .= " AND category IS NOT NULL";
	}
	
	else
	{
		$category = $_GET['cat'];
		
		if ($category == "all")
		{
			 $query .= " AND category IS NOT NULL";
		}
		else
		{
			// Converts string to catID which is used in DB
			 $catID_query = "SELECT catID FROM categories WHERE name = '$category'";
			 $catID_result = mysqli_query($connection, $catID_query) 
				or die('Error making listing title query');
			 $catID = mysqli_fetch_array($catID_result);
			 $category = $catID[0];
			 $query .= " AND category = '$category'";
		}
	}
// Checks if 'order by' exists 
	if (!isset($_GET['order_by']))
	{
		// At this point we divide our SQL queries into two. $query will be used to count the number of listings for pagination
		// $query_ordered will be used to pull the actual listings in the correct order. $query_ordered is what is outputted to screen later
		
		//Default search is for soonest expiry. Pushes finished auctions to the back by increasing the absolute value of the time difference
		$query_ordered = $query . " GROUP BY listings.listing_id ORDER BY (CASE 
			WHEN (listings.endtime > CURRENT_TIMESTAMP) THEN TIMEDIFF(listings.endtime,CURRENT_TIMESTAMP) 
			ELSE ADDTIME((TIMEDIFF(CURRENT_TIMESTAMP, listings.endtime)),\"10000:0:0\") 
			END) LIMIT $results_per_page";
		

	}
	else
	{
		$order_by = $_GET['order_by'];
		if ($order_by == '')
		{
			
			$query_ordered = $query . " GROUP BY listings.listing_id ORDER BY (CASE 
			WHEN (listings.endtime > CURRENT_TIMESTAMP) THEN TIMEDIFF(listings.endtime,CURRENT_TIMESTAMP) 
			ELSE ADDTIME((TIMEDIFF(CURRENT_TIMESTAMP, listings.endtime)),\"10000:0:0\") 
			END) LIMIT $results_per_page";
		}
				
		if ($order_by == 'date')
		{
			$query_ordered = $query . " GROUP BY listings.listing_id ORDER BY (CASE 
			WHEN (listings.endtime > CURRENT_TIMESTAMP) THEN TIMEDIFF(listings.endtime,CURRENT_TIMESTAMP) 
			ELSE ADDTIME((TIMEDIFF(CURRENT_TIMESTAMP, listings.endtime)),\"10000:0:0\") 
			END) LIMIT $results_per_page";
	
		}
		
		if ($order_by == 'pricelow')
		{
			$query_ordered = $query . " GROUP BY listings.listing_id ORDER BY (CASE
			WHEN MAX(bids.bidprice) IS NULL THEN listings.startprice
			ELSE MAX(bids.bidprice)
			END) LIMIT $results_per_page";
		}
		if ($order_by == 'pricehigh')
		{
			$query_ordered = $query . " GROUP BY listings.listing_id ORDER BY (CASE
			WHEN MAX(bids.bidprice) IS NULL THEN listings.startprice
			ELSE MAX(bids.bidprice)
			END) DESC LIMIT $results_per_page";
		}

	}
	
	// Here we divide $query into an array so we can remove columns and return the COUNT of listings for pagination 
	$tmp = explode(" ",$query);
	$tmp[1] = "COUNT(DISTINCT listings.listing_id)";
	$tmp[2] = "";
	$tmp[3] = "";
	$tmp[4] = "";
	$tmp[5] = "";
	$tmp[6] = "FROM ";

	// $num_query introduced. Turn $tmp array into a string. Like $query but uses SQL 'COUNT'
	
	$num_query = implode(" ",$tmp);
	$num_result = mysqli_query($connection, $num_query)
			or die('Error making count query');

	$row = mysqli_fetch_array($num_result);

	$num_results = $row[0]; 
	
	if ($num_results < 1) {
		$max_page = 1;
	}
	else {
		$max_page = ceil($num_results / $results_per_page);
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
			$query_ordered .= " OFFSET $offset"; 

		}
	}
?>

<div class="container mt-5">

<?php
if ($num_results < 1) {
		echo ("Sorry, your search didn't yield any results! Perhaps try again with a different keyword or category...");
	}
?> 


<ul class="list-group">


<?php
	//Get results of $query_ordered so we can print to user
	$result = mysqli_query($connection, $query_ordered)
		or die('Error making select users query');
	
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

			print_listing_li($row['listing_id'], $row['item_title'], $row['itemdescription'], $row['MAX(bids.bidprice)'], $bid_count[0], date_create($row['endtime']));
		
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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
