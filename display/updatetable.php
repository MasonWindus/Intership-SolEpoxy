<?php
// This script returns all rows in the Sample_Information table in order to draw or update the last row of the display

//Include file with all database connection information
include '../config/config.php';


$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
$conn = sqlsrv_connect($dbHost, $connectionInfo);		

//Allows this page to be used for both molding compounds and dri kotes
$location = $_GET['location'];

//This query selects all database entries that were created less than 7 days ago		
$query = "SELECT product_name, Sample_Information.batch_number, bin_number, operator_ID, machine_number, Sample_Information.sample_number, qa_status FROM Sample_Information 
INNER JOIN Sample_Time_Stamps 
ON Sample_Information.batch_number = Sample_Time_Stamps.batch_number 
AND Sample_Information.sample_number = Sample_Time_Stamps.sample_number
WHERE Sample_Time_Stamps.sample_location = '$location' AND DATEDIFF(DAY, Sample_Time_Stamps.entry_date, GETDATE()) < 7
ORDER BY Sample_Time_Stamps.entry_date DESC, Sample_Time_Stamps.location_time DESC;";

//Exit page if connection to database fails
if($conn == false){
	exit('Connection Failed');
}

//Query database to select entries
$selected = sqlsrv_query($conn, $query);

//Exit page and report errors if query fails
if($selected == false){
	die( print_r( sqlsrv_errors(), true));
}

//Instantiate array to hold entries
$table = array();
$count = 0;

//Fill array with entries
while($row = sqlsrv_fetch_array($selected, SQLSRV_FETCH_ASSOC)){
	$table[$count] = $row;
	$count++;
}

//Convert array to JSON and return to display
echo json_encode($table);
?>