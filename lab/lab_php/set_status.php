<html>
<head>
<!--meta tag used to make page compatible with tablet screen-->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Retains Status</title>
<link rel="stylesheet" type="text/css" href="../lab_css/lab.css">
<style>
input[type=submit]{
	width: 40%;
	height: 15%;
	display: inline;
	text-align: center;
	font-size: 32;
	color: white;
}
#pass {
	background-color: #00ff00;
	margin: 25px 10px 25px 0px;
}

#fail {
	background-color: #ff0000;
	margin: 25px 0px 25px 10px;
}

select {
	font-size: 15px;
	height: 40px;
	width: 200px;
	padding: 6px;
	border-radius: 6px;
	border: 1px solid #666666;
}

</style>
<script src="../../config/jquery-3.3.1.min.js"></script>
<script src="../lab_js/set_status.js"></script>
<script>
	function validate(){
	//Provided in case future validation is required, currently just returns true
		
		return true;
	}
</script>
</head>
<body>
	<div id='titletext'>Select A Sample And Set It's Status</div><br>
		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" onsubmit="return validate()">
			<div class='center'>
				<select id = "dropdown" name = 'entry'>
				</select><br>
				<input type = 'submit' name='pass' id = 'pass' value="Pass"></input>
				<input type = 'submit' name='fail' id = 'fail' value="Fail"></input>
			</div>
		</form>
	<div class='center'><button id='back' onclick = "window.location.replace('../lab_html/menu.html')">Back To Menu</button></div>
	<br><br>
	<table class='checked'>
	</table>

	<?php
	
	//Include file with all database connection information
	include '../../config/config.php';
	
	$entry = array();
	$status = "";

	//Exit if form hasn't been submitted yet, necessary to have PHP embedded in this page
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		
		//Exit if no entry has been selected from the dropdown to avoid altering undefined database entries
		if(isset($_POST['entry'])){
			$entry = $_POST['entry'];
		} else {
			exit();
		}
		
		//Exit if both or neither submit button was pressed, otherwise set submit to correct value
		if(isset($_POST['pass']) && isset($_POST['fail'])){
			exit();
		} else if(isset($_POST['pass'])){
			$status = "Pass";
		} else if(isset($_POST['fail'])){
			$status = "Fail";
		} else {
			exit();
		}
	} else {
		exit();
	}

	//Dropdown option values written to be well formed JSON objects, json_decode with true converts to regular associative array
	$entry = json_decode($entry, true);
	$statusCode;

	//Set statusCode based on button pressed, 10 is used on outside chance this code is run before pass/fail has been pressed
	if($status == 'Pass'){
		$statusCode = 2;
	} elseif($status == 'Fail'){
		$statusCode = 3;
	} else {
		$statusCode = 10;
	}

	//Establish connection with server and log in to database
	$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
	$conn = sqlsrv_connect($dbHost, $connectionInfo);		

	//Pull out values from associative array formed earlier
	$batchNumber = $entry['batch_number'];
	$sampleNumber = $entry['sample_number'];

	//Generate time and date for timestamp table
	$entryTime = date("H:i:s");
	$entryDate = date("Y-m-d");

	//Formulate query to update qa_status
	$changeStatusQuery = "UPDATE Sample_Information SET qa_status = $statusCode WHERE batch_number = '$batchNumber' AND sample_number = '$sampleNumber'";

	//Formulate query to add timestamp, string literal 'statuschange' used to indicate this timestamp is for when the status changed
	$addTimeStampQuery = "INSERT INTO Sample_Time_Stamps VALUES('$batchNumber', $sampleNumber, 'statuschange', '$entryTime', '$entryDate');";

	//Change qa_status
	$statusChanged = sqlsrv_query($conn, $changeStatusQuery);

	//Add timestamp
	$timeStampAdded = sqlsrv_query($conn, $addTimeStampQuery);

	//Error reporting
	if($statusChanged == false || $timeStampAdded == false){
		die( print_r( sqlsrv_errors(), true));
	} else {
		echo 'Status Successfully Updated';
	}
	?>



</body>
</html>