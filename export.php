<?php  

session_start();

if ( isset( $_SESSION['user_id'] ) AND $_SESSION['admin'] =='yes' ) {
    // Grab user data from the database using the user_id
    // Let them access the "logged in only" pages
	$now = time(); // Checking the time now when home page starts.

        if ($now > $_SESSION['expire']) {
            session_destroy();
            header("Location: login.php");
        }
	
	
} else {
    // Redirect them to the login page
    header("Location: login.php");
}

// connect to the DB
include('connect.php'); 

// POST fields from form

$search = $_POST['search'];
$startdate = $_POST['startdate'];	
$enddate = $_POST['enddate'];	
$library = $_POST['library'];
$name = $_POST['name'];
//convert search string to uppdercase 
$search = strtoupper($search);

//query for barcode search
if(isset($search) and $search !=='') {$searchsql = " ptraylocation LIKE '%".$search."%' ";}
else $searchsql = ' ptraylocation IS NOT NULL ';

//Create SQL query for date range
$startdateformatted = date("Y-m-d", strtotime($startdate));
$enddateformatted = date("Y-m-d", strtotime($enddate));

if((isset($startdate) AND $startdate !='') AND (isset($enddate) AND $enddate != ''))
$datesql = " (ptimestamp BETWEEN '".$startdateformatted." 00:00:00' AND '".$enddateformatted." 23:59:59') ";
else $datesql ='';

// build query
$query = "SELECT * FROM ProcessingAll WHERE ProcessingKey IS NOT NULL ";
if(isset($datesql) AND $datesql !='') $query .= ' AND '.$datesql;
if(isset($name) AND $name !='') $query .= " AND (pname = '".$name."' OR ccname ='".$name."') ";
if(isset($library) AND $library !='') $query .= " AND plibrary = '".$library."' ";
if(isset($search) and $search !='') {$query .= " AND ptraylocation LIKE '%".$search."%' ";}
$query .= " ORDER BY ProcessingKey;";

//name query as sql variable
$sql = $query;

//begin export to CSV
$query = mysqli_query($conn, $sql);	  
	  
 if(isset($_POST["export"]))  
 {  
     // $conn = mysqli_connect("localhost", "root", "", "testing");  
      header('Content-Type: text/csv; charset=utf-8');  
      header('Content-Disposition: attachment; filename=scf-processing.csv');  
      $output = fopen("php://output", "w");  
      fputcsv($output, array('Processing Key', 'Processing Date', 'Processing Name', 'Barcode', 'Type', 'Processing Count','Full','Processing Alma Search','Processing Verified', 'Library','Cross Check Date','Cross Check Name','Cross Check Count','Cross Check Alma Search', 'Cross Check Verified'));  
     // $query = "SELECT * from tbl_employee ORDER BY id DESC";  
      $result = mysqli_query($conn, $sql);  
      while($row = mysqli_fetch_assoc($result))  
      {  
           fputcsv($output, $row);  
      }  
      fclose($output);  
 }  
 ?>  