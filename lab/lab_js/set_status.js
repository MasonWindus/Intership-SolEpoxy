function get_table(){
//Function called to draw the table showing what samples have been checked in, used as a primitive way of keeping
//Lab technicians aware of what samples are ready for testing
	
	//Create http request object to get database entries via external php page without reloading current page
	var xmlhttp;
	
	//if statement used to allow compatibility with previous versions of internet explorer
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//Function executed once the page responds with valid data
	xmlhttp.onreadystatechange = function(){
		//readyState == 4 means the request has been sent to the server and the server has finished responding
		//status == 200 means the request was successfully completed
		if(this.readyState == 4 && this.status == 200){
			
			//External php page set up to respond with well-formed JSON object, JSON.parse converts to regular object
			var rows = JSON.parse(this.responseText);
			
			//Write table headers to screen, done every call due to use of append in the for loop
			//Using .html deletes whatever table content was already on screen, letting the table both shrink and grow
			$('.checked').html("<tr><th colspan='3'>Previously Checked In Samples</th></tr><tr><th>Batch Number</th><th>Bin Number</th><th>Sample Number</th>")
			for(var i = 0; i < rows.length; i++){
				var row = rows[i];
				$('.checked').append("<tr><td>" + row['batch_number'] + "</td><td>" + row['bin_number'] + "</td><td>" + row['sample_number'] + "</td></tr>");
			}
		}
	}
	xmlhttp.open("GET", "../lab_php/get_checkedin.php", true);
	xmlhttp.send();
}

function get_dropdown(){
//Function used to get entries for the dropdown menu via an external php page without reloading current page

	//Create http request object
	var xmlhttp;
	var dropdownOption = "<option value ='' selected disabled >Choose A Sample</option>";
	
	//if statement used to allow compatibility with previous versions of internet explorer
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//Function to be executed when php responds with valid data
	xmlhttp.onreadystatechange = function() {
		//readyState == 4 means the request has been sent to the server and the server has finished responding
		//status == 200 means the request was successfully completed
		if(this.readyState == 4 && this.status == 200){
			
			//PHP written to respond with well formed JSON object
			var values = JSON.parse(this.responseText);
			
			//Generate HTML for the dropdown options
			for(var i = 0; i < values.length; i++){
				var value = values[i];
				dropdownOption += "<option value = '" + JSON.stringify(value) + "'>Batch: " + value['batch_number'] + " Bin: " + value['bin_number'] + ", Sample: " + value['sample_number'] + "</option>";
			}
			$('#dropdown').html(dropdownOption);
		}
	}
	xmlhttp.open("GET", "../lab_php/get_checkedin.php", true);
	xmlhttp.send();
}

//JQuery call that executes the above functions once the page is done loading and then once every 60 seconds
$(document).ready(function(){
	get_table();
	setInterval(get_table, 60000);
	get_dropdown();
	setInterval(get_dropdown, 60000);
});