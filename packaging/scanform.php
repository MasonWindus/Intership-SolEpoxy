<!doctype html>
<html>
<head>
<title>Retain Form</title>
<meta charset="utf-8" />
<meta author="Mason Windus" />
<link rel="stylesheet" type="text/css" href="scannergun.css">
</head>
<body>

<!--This page has been written to be compatible with the version of Internet Explorer on the scanner guns
Modifications must be double checked to ensure compatibility with this version-->

<form name="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" onsubmit="return validate()" method="post">
<table align="center">
	<th colspan="2">
		Retains Form
	</th>
	<tr>
		<td colspan="2"><p id="bin_error" class="error" color="red"></p></td>
	</tr>
	<tr>
		<td style="font-size:16px">Bin Number</td>
		<td><input type="text" id="bin_number" name="bin_number"></td>
	</tr>
	<tr>
		<td colspan=2><p id="operator_error" class="error" color="red"></p></td>
	</tr>
	<tr>
		<td style="font-size:16px">Operator ID</td>
		<td><input type="text" id="operator_ID" name="operator_ID"></td>
	</tr>
	<tr>
		<td colspan=2>
				<select onChange="autofill();" id='batchSelector'>
					<option value="blank" selected disabled>
						Select A Batch
					</option>
					<?php
						//Pull in page with database details
						include '../config/config.php';
						
						//Connect to database
						$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
						$conn = sqlsrv_connect($dbHost, $connectionInfo);
						
						//Formulate query
						$query = "SELECT TOP 15 product_name, batch_number FROM Temp_Batch_Data ORDER BY date DESC, time DESC;";
						
						//Test Connection, exit if connection failed since data can still be typed
						if($conn === false){
							exit('failed');
						}
						
						//Query database
						$result = sqlsrv_query($conn, $query);
						
						//Exit if query failed, since data can still be typed
						if($result === false){
							exit(print_r(sqlsrv_errors(), true));
						}
						
						if(isset($_GET['machineScanned']) && $_GET['machineScanned'] == 'lightnin'){
							$productClassRegex = '/^dk[0-9]{4}$/i';
						} elseif(isset($_GET['machineScanned']) && ($_GET['machineScanned'] == 'FB51' || $_GET['machineScanned'] == 'FB61')){
							$productClassRegex = '/^m[ghf][0-9]{4}$/i';
						}

						while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
							$isCorrectProductClass = preg_match($productClassRegex, $row['product_name']);
							if($isCorrectProductClass){
								echo '<option value="'.$row['product_name'].','.$row['batch_number'].'">'.$row['product_name'].', '.$row['batch_number'].'</option>';
							}
						}
					?>
				</select>
		</td>
	</tr>
	<tr>
		<td colspan=2><p id="batch_error" class="error" color="red"></p></td>
	</tr>
	<tr>
		<td style="font-size:16px">Batch Number</td>
		<td><input type="text" id="batch_number" name="batch_number"></td>
	</tr>
	<tr>
		<td colspan=2><p id="product_error" class="error" color="red"></p></td>
	</tr>
	<tr>
		<td style="font-size:16px">Product Name</td>
		<td><input type="text" id="product_name" name="product_name"></td>
	</tr>
	<tr>
		<td colspan=2><p id="machine_error" class="error" style="color:black">Machine Number</p></td>
	</tr>
	<tr>
		<td colspan=2 style="text-align:left">
			<input type="radio" id="fb51" name="machine_number" value="FB51">FB-51<br>
			<input type="radio" id="fb61" name="machine_number" value="FB61">FB-61<br>
			<input type="radio" id="lightnin" name="machine_number" value="Lightnin">Lightnin
		</td>
	</tr>

	<tr>
		<td colspan=2 id='submitcontainer'>
			<input id='submit' type="submit" value="OK">
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
//Javascript has all been written to be compatible with the version of Internet Explorer on the scanner guns
//Modifications must be double checked to ensure compatibility with this version


window.onload = function(){
	document.getElementById("bin_number").focus();
	document.getElementById("batchSelector").onchange = function(){
		autofill();
	}
	
	//Pull in name of machine that operator has scanned, php section only echos if machineScanned is set
	var machineScanned = "<?php if(isset($_GET['machineScanned'])) echo $_GET['machineScanned']?>";

	//Check radio button and disable others based on machine scanned
	if(machineScanned == "FB51"){
		document.getElementById("fb51").checked = true;
		document.getElementById("fb61").disabled = true;
		document.getElementById("lightnin").disabled = true;
	} else if(machineScanned == "FB61"){
		document.getElementById("fb51").disabled = true;
		document.getElementById("fb61").checked = true;
		document.getElementById("lightnin").disabled = true;
	} else if(machineScanned == "lightnin"){
		document.getElementById("fb51").disabled = true;
		document.getElementById("fb61").disabled = true;
		document.getElementById("lightnin").checked = true;
	}
}

function autofill() {
	//Function used to eliminate operator data entry, autofills batch number and product name with data from extrusion
	
	//Pull batch data out of select field
	var batchData = document.getElementById('batchSelector').value;
	
	//Data is formatted to be separated by a comma, since this early version of internet explorer doesn't have JSON.parse()
	var batchData = batchData.split(',');
	
	//Set values of input fields to selected batch data
	document.getElementById('batch_number').value = batchData[1];
	document.getElementById('product_name').value = batchData[0];
}

function validate() {
	
	//Regex objects to sanity check some of the inputs
	var productRegex = /^[a-zA-Z0-9]{6}$/;
	var batchRegex = /^[0-9]{8,10}$/;
	var binRegex = /^[0-9]{4}$/;
	var operatorRegex = /^[0-9]{3,4}$/;

	//Pulling out all the text input values to check them against the regex objects above
	var productName = document.getElementById("product_name").value;
	var batchNumber = document.getElementById("batch_number").value;
	var binNumber = document.getElementById("bin_number").value;
	var operatorID = document.getElementById("operator_ID").value;
	
	//Boolean values of the regex sanity check, the false values will have their respective error messages displayed
	var productMatch = productRegex.test(productName);
	var batchMatch = batchRegex.test(batchNumber);
	var binMatch = binRegex.test(binNumber);
	var operatorMatch = operatorRegex.test(operatorID);
	
	//Boolean to determine whether or not to submit the form
	var submit = true;
	
	//Check to be sure all fields are filled in
	if(productName.length == 0 || batchNumber.length == 0 || binNumber.length == 0 || operatorID == 0){
		alert("Missing Fields");
		submit = false;
	}
	
	//Display error message if product name was invalid or remove error message if product name was valid
	if(!productMatch){
		document.getElementById("product_error").innerHTML = "Invalid Product Name";
		submit = false;
	} else {
		document.getElementById("product_error").innerHTML = "";
	}
	
	//Display error message if batch number was invalid or remove error message if batch number was valid
	if(!batchMatch){
		document.getElementById("batch_error").innerHTML = "Invalid Batch Number";
		submit = false;
	} else {
		document.getElementById("batch_error").innerHTML = "";
	}
	
	//Display error message if bin number was invalid or remove error message if bin number was valid
	if(!binMatch){
		document.getElementById("bin_error").innerHTML = "Invalid Bin Number";
		submit = false;
	} else {
		document.getElementById("bin_error").innerHTML = "";
	}
	
	//Display error message if operator id was invalid or remove error message if operator id was valid
	if(!operatorMatch){
		document.getElementById("operator_error").innerHTML = "Invalid Operator ID";
		submit = false;
	} else {
		document.getElementById("operator_error").innerHTML = "";
	}
	
	//Submit form if all fields were valid or display error message
	if(submit){
		document.getElementsByClassName("error").innerHTML= "";
	} else {
		alert("Invalid Field(s)");
	}
	return submit;
	
}
</script>

<?php

//Include file with all database connection information
include '../config/config.php';

//Instantiate variables for all user-entered fields
$productName = $batchNumber = $binNumber = $entryDate = $entryTime = $operatorID = $machineNumber = $qaStatus = $allInfo = $sampleNumber = "";

//Only run when form is submitted in order to avoid falsely processing nulls on page load
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	//Pull in all user entered fields
	$productName = $_POST['product_name'];
	$batchNumber = $_POST['batch_number'];
	$binNumber = $_POST['bin_number'];
	$entryDate = date("Y-m-d");
	$entryTime = date("H:i:s");
	$operatorID = $_POST['operator_ID'];
	$machineNumber = $_POST['machine_number'];
	
	//Auto fill qa_status with 0, meaning the sample has been taken but not checked in yet
	$qaStatus = 0;
	
	//Establish connection with server and log in to database
	$connectionInfo = array('Database' => $dbName, 'UID'=> $dbUser, 'PWD'=> $dbPass);
	$conn = sqlsrv_connect($dbHost, $connectionInfo);

	//Formulate query to determine number of samples from current batch, used to set sample_number
	$query = "SELECT sample_number FROM Sample_Information WHERE batch_number='".$batchNumber."' ORDER BY sample_number DESC";
	
	//Query database if connection was successfully established, exit php if not
	if($conn == false){
		exit('Connection Failed');
	}
	
	$sent = sqlsrv_query($conn, $query);
	
	if($sent == false){
		die( print_r( sqlsrv_errors(), true));
	}
	
	$result = sqlsrv_fetch_array($sent, SQLSRV_FETCH_ASSOC);
	
	//Set sample number to 1 more than the current number of samples from the given batch
	$sampleNumber;
	if($result['sample_number'] === null){
		$sampleNumber = 1;
	} else {
		$sampleNumber = $result['sample_number'] + 1;
	}
	//Encode the batch number and sample number to a JSON object for embedding in the barcode
	$barCodeInfo = json_encode(array("batch_number"=>$batchNumber, "sample_number"=>$sampleNumber));
	
	//Boolean to indicate that all fields are valid and ready to be entered into database
	$allValid = true;
	
	//Test for validity of product name
	$patternProduct='/^[a-zA-Z0-9]{6}$/';
	$valid = preg_match($patternProduct, $productName);
	if($valid == false){
		echo 'Product Name field was invalid';
		$allValid = false;
	} 
	
	//Test for validity of batch number
	$patternBatch = '/^[0-9]{8,10}$/';
	$valid = preg_match($patternBatch, $batchNumber);
	if($valid == false){
		echo 'Batch Number field was invalid';
		$allValid = false;
	}
	
	//Test for validity of bin number, must also be between 1000 and 1500
	$patternBin = '/^[0-9]{4}$/';
	$valid = preg_match($patternBin, $binNumber);
	if($valid == false || $binNumber < 1000 || $binNumber > 1500){
		echo 'Bin Number field was invalid';
		$allValid = false;
	}
	
	//Test for validity of operator id
	$patternOperator = '/^[0-9]{3,4}$/';
	$valid = preg_match($patternOperator, $operatorID);
	if($valid == false){
		echo 'Operator field was invalid';
		$allValid = false;
	}
	
	//Enter info into database if all fields were valid
	if($allValid){
		
		//Because page will be used in both grinding and packaging, location and integration variables need to be set accordingly
		if($machineNumber == 'FB51' || $machineNumber == 'FB61'){
			$subject = 'print packaging';
			$location = 'packaging';
		} elseif($machineNumber == 'Lightnin'){
			$subject = 'print grinding';
			$location = 'grinding';
		} else {
			$subject = 'print packaging';
			$location = 'packaging';
		}
		
		//Formulate query to insert sample info
		$query = "INSERT INTO Sample_Information
		VALUES ('$productName', '$batchNumber', '$binNumber', '$operatorID', '$machineNumber', 0, $qaStatus, $sampleNumber, 70, 50, '$barCodeInfo');";
		
		//Formulate query to insert time stamp for when sample is taken
		$query2 = "INSERT INTO Sample_Time_Stamps VALUES ('$batchNumber', $sampleNumber, '$location', '$entryTime', '$entryDate');";
		
		//Query database if all connection was successfully established, if not, exit php and alert operator
		if($conn == false){
			exit('Connection Failed, Contact IT or Try Again');
		} else {
			$sent = sqlsrv_query($conn, $query);
			$sent2 = sqlsrv_query($conn, $query2);
			if($sent == false || $sent2 == false){
				exit('Something Went Wrong, Contact IT');
			} else {
				
				//Alert operator of successful entry and send email to retains server to initiate label printing
				$message = 'print';
				$headers = "From: bartender@retains.solepoxy.local". "\r\n";
				mail("bartender@retains.solepoxy.local", $subject, $message, $headers);
				echo '<script type="text/javascript">alert("Entry Success"); window.location.assign("http://babu.solepoxy.local/");</script>';
			}
		}
	}
}
?>

</body>
</html>