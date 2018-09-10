<?php
// This script takes the info passed in from scanning the bar code
// and changes the qa_status value of the given sample to 1 to check it in

//Include file with all database connection information
include '../../config/config.php';


//Pull out info that was passed in from js page
$allInfo = $_GET['allinfo'];

//Data from js page is written to be well formed JSON object, json_decode with true converts to associative array
$infoArr = json_decode($allInfo, true);

//Pull out data from associative array
$batchNumber = $infoArr['batch_number'];
$sampleNumber = $infoArr['sample_number'];

//Establish connection with server and log in to database
$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
$conn = sqlsrv_connect($dbHost, $connectionInfo);

//Formulate query to check scanned sample, used to prevent rechecking in samples that have already been tested or checked in
$query = "SELECT qa_status FROM Sample_Information WHERE batch_number='".$batchNumber."' AND sample_number='".$sampleNumber."'";

//Exit if connection to database failed
if($conn == false){
	exit('Connection Failed');
}

//Query database to check current status
$statusTest = sqlsrv_query($conn, $query);

//Error reporting if query failed
if($statusTest == false){
	die( print_r( sqlsrv_errors(), true));
}

//Convert database response into associative array, easiest way to access fields even though we only selected one field
$status = sqlsrv_fetch_array($statusTest, SQLSRV_FETCH_ASSOC);

//Display message and exit if sample was already checked in or tested
if($status['qa_status'] === 1){
	exit('Sample Already Checked In');
} else if($status['qa_status'] != 0){
	exit('Sample Already Tested');
}

//Formulate query to check for outside chance of non-unique samples or non-existent samples
$query = "SELECT COUNT(*) as count FROM Sample_Information WHERE batch_number='".$batchNumber."' AND sample_number='".$sampleNumber."'";

//Exit if connection to database failed
if($conn == false){
	exit('Connection Failed');
}

//Query database to check for non-unique samples or non-existent samples
$sent = sqlsrv_query($conn, $query);

//Error reporting if query failed
if($sent == false){
	die( print_r( sqlsrv_errors(), true));
}

//retrieve count of matching samples as associative array
$count = sqlsrv_fetch_array($sent, SQLSRV_FETCH_ASSOC);


if($count['count'] === 0){
	//Exit if sample is not in database
	echo 'Sample Not Found';
} else if($count['count'] == 1){
	//Generate time and date to add to timestamp database
	$entryTime = date("H:i:s");
	$entryDate = date("Y-m-d");
	
	//Formulate query to update qa_status to 1, which shows that the sample is checked in
	$query = "UPDATE Sample_Information SET qa_status = 1 WHERE batch_number = '".$batchNumber."' AND sample_number='".$sampleNumber."'";
	
	//Formulate query to add timestamp, string literal 'checkin' used to indicate this timestamp is from when the sample was checked in
	$query2 = "INSERT INTO Sample_Time_Stamps VALUES ('$batchNumber', $sampleNumber, 'checkin', '$entryTime', '$entryDate');";
	
	//Update qa_status
	$updated = sqlsrv_query($conn, $query);
	
	//Add timestamp
	$updated2 = sqlsrv_query($conn, $query2);
	
	//Return correct message
	if($updated == false){
		echo 'Check In Failed';
	} else {
		echo 'Sample Checked In Successfully';
	}
} else {
	//Return message in case of non-unique sample
	echo 'Sample Not Unique';
}

?>