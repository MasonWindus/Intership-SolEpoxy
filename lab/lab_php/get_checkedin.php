<?php
// This script is used by the set_status and check_in pages to 
// retrieve the checked in samples from the Sample_Information table

//Include file with all database connection information
include '../../config/config.php';

//Establish connection with server and log in to database
$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
$conn = sqlsrv_connect($dbHost, $connectionInfo);	

//Query to select all checked in samples, batch number, bin number, and sample number
$query = "SELECT batch_number, bin_number, sample_number FROM Sample_Information WHERE qa_status = 1";

//Exit if connection to database failed
if($conn == false){
	exit('Connection Failed');
}

//Query database
$sent = sqlsrv_query($conn, $query);

//Error reporting if query failed
if($sent == false){
	die( print_r( sqlsrv_errors(), true));
}

//instantiate empty array to be filled with database entries fields
$table = array();
$count = 0;

//Fill array with database entries, sqlsrv_fetch_array returns false when no more entries are available
while($row = sqlsrv_fetch_array($sent, SQLSRV_FETCH_ASSOC)){
	$table[$count] = $row;
	$count++;
}
echo json_encode($table);
?>