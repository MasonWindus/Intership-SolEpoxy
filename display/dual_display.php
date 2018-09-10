<html>
<head>
<title>Retains Status Display</title>
<style>
html * {
	font-size: 18pt;
}

table {
	border-collapse: collapse;
	width: 100%;
}

td, th{
	border: 2px solid #000000;
	text-align: left;
	padding: 8px;
}

#moldingCompoundTableWrapper{
	height: 50%;
}

#driKoteTableWrapper{
	position: relative;
}

.statusGenerated{
	background-color: #555555;
	color: white;
}

.statusCheckedIn{
	background-color: #0000ff;
	color: white;
}

.statusPassed{
	background-color: #00ff00;
	color: black;
}

.statusFailed{
	background-color: #ff0000;
	color: white;
}

.statusError{
	background-color: #ff00ff;
	color: black;
}

.product_name {
	width: 15%;
}

.batch_number {
	width: 15%;
}

.bin_number {
	width: 10%;
}

.operator_ID {
	width: 10%;
}

.machine_number {
	width: 15%;
}

.sample_number {
	width: 15%;
}

.status {
	width: 20%;
}
</style>
<script src="../config/jquery-3.3.1.min.js"></script>
<script>
//.ready used to make sure functions don't run before page has completely loaded to avoid attempting to access non-existant page elements
$(document).ready(function(){
	//Calls draw table to start page, then sets it to run every 10 minutes
	draw_table('updatetable.php?location=packaging', 'moldingCompoundsTable', 'moldingCompounds');
	draw_table('updatetable.php?location=grinding', 'driKoteTable', 'driKotes');
	setInterval(draw_table, 600000, 'updatetable.php?location=packaging', 'moldingCompoundsTable', 'moldingCompounds');
	setInterval(draw_table, 600000, 'updatetable.php?location=grinding', 'driKoteTable', 'driKotes');

	//Sets update_status and update_table to run every minute
	setInterval(update_table, 10000, 'updatetable.php?location=packaging', 'moldingCompoundsTable');
	setInterval(update_status, 10000, 'updatetable.php?location=packaging', 'moldingCompoundsTable');
	setInterval(update_table, 10000, 'updatetable.php?location=grinding', 'driKoteTable');
	setInterval(update_status, 10000, 'updatetable.php?location=grinding', 'driKoteTable');
});

function draw_table(URL, tableID, tableDivID){
//Function called to draw table from scratch, used to update table completely every 10 minutes and to draw on pageload

	//Open http request object
	var xmlhttp;
	var table;
	
	//if statement used to allow compatibility with previous versions of internet explorer
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//Function to run only when server responds with valid data
	xmlhttp.onreadystatechange = function(){
		//readyState == 4 means the request has been sent to the server and the server has finished responding
		//status == 200 means the request was successfully completed
		if(this.readyState == 4 && this.status == 200){
		
			//PHP is written to respond with valid JSON, containing all database entries younger than 7 days old in descending chronological order
			var records = JSON.parse(this.responseText);
			
			//Starts string with table tag, will add row by row in the for loop
			table = "<table id='" + tableID + "'><tbody id='tbody-" + tableID + "'>";
			
			for(var i = 0; i < 10; i++){
			
				var record = records[i];
				
				//Decode status into class and inner text
				var statusInfo = status_decode(record['qa_status']);
				
				//rowClass holds the class the entire row will take and rowStatus holds the text displayed in the status cell
				var rowClass = statusInfo['newClass'];
				var rowStatus = statusInfo['innerText'];
				
				table += format_new_row(record, rowClass, rowStatus);
			}
			
			//Finish the table string with ending table tag and write it to the screen
			table += "</tbody></table>";
			document.getElementById(tableDivID).innerHTML = table;
		}
		
	}
	xmlhttp.open("GET", URL, true);
	xmlhttp.send();
	
}

function update_table(URL, tableID){
//Function used to update the top row of the table in the case of a new sample being added

	var xmlhttp;
	var newRow;
	
	//if statement used to allow compatibility with previous versions of internet explorer
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//Function to run only when server responds with valid data
	xmlhttp.onreadystatechange = function(){
		//readyState == 4 means the request has been sent to the server and the server has finished responding
		//status == 200 means the request was successfully completed
		if(this.readyState == 4 && this.status == 200){
		
			//PHP written to respond with valid JSON containing all database records younger than 7 days old
			var records = JSON.parse(this.responseText);
			
			//Get ID of current top row of table, .children used twice because rows are actually added to
			//a tbody container on the DOM, so the hierarchy goes <table> -> <tbody> -> <tr>
			var currentTopRowID = $("#" + tableID).children(":first").children(":first").attr("id");
			
			var i = 0;
			//The first record in the array is the youngest record, the ID of this record will be compared to the ID of the
			//current top row of the table to determine if a new record has been added
			var youngestRecord = records[i];
			
			//Generate ID of youngestRecord to compare to the ID of the current top row of the table
			var youngestRecordID = youngestRecord['batch_number'] + "_" + youngestRecord['sample_number'];
			
			if(currentTopRowID != null){
				//If lastrow and currentTopRowID are not the same, test further to see if just at statuschange or entirely new sample
				while(youngestRecordID != currentTopRowID){
					//Decode status into class and inner text
					var statusInfo = status_decode(youngestRecord['qa_status']);
					
					//rowClass holds the class the entire row will take and rowStatus holds the text displayed in the status cell
					var rowClass = statusInfo['newClass'];
					var rowStatus = statusInfo['innerText'];
					
					var newRow = format_new_row(youngestRecord, rowClass, rowStatus);
					
					//Add row above current rows, below headers
					$("#tbody-" + tableID).prepend(newRow);
					i = i + 1;
					youngestRecord = records[i];
					
					youngestRecordID = youngestRecord['batch_number'] + "_" + youngestRecord['sample_number'];
				}
			}
		}
	}
	xmlhttp.open("GET", URL, true);
	xmlhttp.send();
}

function update_status(URL, tableID){
//Function used to update the status cells of all rows dynamically without reloading entire table

	var xmlhttp;
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		//readyState == 4 means the request has been sent to the server and the server has finished responding
		//status == 200 means the request was successfully completed
		if(this.readyState == 4 && this.status == 200){
		
			//PHP written to respond with valid JSON
			var records = JSON.parse(this.responseText);
			for(var i = 0; i < 10; i++){
				var record = records[i];
				
				//Formulate ID of record to look up row in table
				var rowID = record['batch_number'] + "_" + record['sample_number'];
				
				//Decode status into class and inner text
				var statusInfo = status_decode(record['qa_status']);
				
				//newClass holds the class the entire row will take and innerText holds the text displayed in the status cell
				var newClass = statusInfo['newClass'];
				var innerText = statusInfo['innerText'];
				
				//Set class of row and text in status cell
				$("#" + rowID).attr("class", newClass);
				$("#" + rowID).children(":last").text(innerText);
			}
		}
	}
	xmlhttp.open("GET", URL, true);
	xmlhttp.send();
}

function status_decode(status){
	var new_class;
	var inner_text;
	if(status == '0'){
		new_class = "statusGenerated";
		inner_text = "Sample Not Yet Checked In";
	} else if(status == '1'){
		new_class = "statusCheckedIn";
		inner_text = "Sample Being Tested";
	} else if(status == '2'){
		new_class = "statusPassed"
		inner_text = "Sample Passed";
	} else if(status == '3'){
		new_class = "statusFailed";
		inner_text = "Sample Failed";
	} else {
		new_class = "statusError";
		inner_text = "Error"
	}
	var statusInfo = {newClass:new_class, innerText:inner_text};
	return statusInfo;
}

function format_new_row(rowInfo, rowClass, rowStatus){
	//Each row can be accessed through this ID, allowing JQuery to be utilized throughout the page
	var rowID = rowInfo['batch_number'] + "_" + rowInfo['sample_number']; 
	
	//Adds record to table with statusClass inserted to assign the class to the row and statusCell to give the status column the correct text
	var row = "<tr class='" + rowClass + "' id='" + rowID + "'><td class='product_name'>" + rowInfo['product_name'] 
	+ "</td><td class='batch_number'>" + rowInfo['batch_number'] 
	+ "</td><td class='bin_number'>" + rowInfo['bin_number'] 
	+ "</td><td class='status'>" + rowStatus + "</td></tr>";
	
	return row;
}
</script>
</head>
<body>
<div id="moldingCompoundTableWrapper">
	<div>
		<h1 style="float: left; font-size:36pt"><?php echo date("n/j/Y") ?></h1>
		<h1 style="float: right; font-size:36pt;"><?php echo date("g:i A") ?></h1>
	</div>
	<br><br><br>
	<div>
		<h2 style="text-align: center">Molding Compounds</h2>
	</div>
	<table id='moldingCompoundHeaders'>
		<tr> 
			<th class='product_name'>Product Name</th> 
			<th class='batch_number'>Batch Number</th> 
			<th class='bin_number'>Bin Number</th> 
			<th class='status'>Status</th> 
		</tr>
	</table>
	<div id="moldingCompounds"></div>
</div>


<div id="driKoteTableWrapper">
	<div>
		<h2 style="text-align: center">Dri Kotes</h2>
	</div>
	<table id='driKoteHeaders'>
		<tr> 
			<th class='product_name'>Product Name</th> 
			<th class='batch_number'>Batch Number</th> 
			<th class='bin_number'>Bin Number</th> 
			<th class='status'>Status</th> 
		</tr>
	</table>
	<div id="driKotes"></div>
</div>
</body>
</html>