<?php

// Constants used to access Scraperwiki API.
define("SCRAPERWIKI_API_URL", "http://api.scraperwiki.com/api/1.0/datastore/sqlite");
define("SCRAPERWIKI_FORMAT", "jsondict");
define("SCRAPERWIKI_NAME", "table1");
define("SCRAPERWIKI_QUERY", "select%20*%20from%20%60swdata%60%20where%20%60flight_num%60%20%3D%20%22[[flight_num]]%22%20and%20date%20%3D%20%22[[date]]%22%20and%20%60flight_type%60%20%3D%20%22[[direction]]%22");

// Function to fetch JSON for a specific flight from Scrapewiki.
function getFlightInfo($flight_num, $date, $direction) {
	
	$direction = (strtolower($direction) == "d") ? "MARITIMO" : "AEREO";
	$query = str_replace(array("[[flight_num]]", "[[date]]", "[[direction]]"), array($flight_num, $date, $direction), SCRAPERWIKI_QUERY);
	$url = SCRAPERWIKI_API_URL."?format=".SCRAPERWIKI_FORMAT."&name=".SCRAPERWIKI_NAME."&query=".$query;
	return json_decode(file_get_contents($url));

}

// Format response based on channel.
function formatResponse($direction, $flight_info, $channel) {
	
	// Determine if the flight is an arrival or departure.
	$leaveorarrive = (strtolower($direction) == "d") ? "actualizado por" : "actualizado por";
	$gate = (strtolower($direction) == "d") ? "  " : "   ";
	
	// Format the flight number for the channel used.
	$flight_num = $channel == "VOICE" ? implode(" ", str_split($flight_info->flight_num)) : $flight_info->flight_num;
	
	// Properly case destination an remarks.
	$destination = ucwords(strtolower($flight_info->destination));
	$remarks = ucwords(strtolower($flight_info->remarks));
	
	// Build response to user.
	$say = $flight_info->airline . " Numero " . $flight_num . " $leaveorarrive " . $destination . " a " . $flight_info->time . $gate . $flight_info->gate . "info: " . $remarks;
	return $say;
}

// Set the date.
$date = date("m.d.y");

if($currentCall->channel == "VOICE") {
	say("Gracias por llamar a excel express cargo.", array("voice" => "Diego"));
	$flight = ask("Por favor diga o entre su numero de rastreo.", array('recognizer' => 'es-cl','voice' => 'Diego', "choices" => "[1-24 DIGITS]", "attempts" => 3, "timeout" => 5));
	$flight_type = ask("Es un envio maritimo o aereo?", array("choices" => "aereo, maritimo", "attempts" => 3, "timeout" => 5,'recognizer' => 'es-cl','voice' => "Diego"));
	
	$flight_num = $flight->value;
	$direction = $flight_type->value;
}
else {
	// Get flight number entered by user.
	$message = explode(" ", $currentCall->initialText);
	$flight_num = $message[0];
	$direction = $message[1];
}

try {
	$flight_info = getFlightInfo($flight_num, $date, $direction);
	if(count($flight_info) == 0) {
		say("No se encotro informacion con el numero $flight_num el $date.", array("voice" => "Diego"));
	}
	else {
		$say = formatResponse($direction, $flight_info[0], $currentCall->channel);
		say($say);	
	}
}

catch (Exception $ex) {
	say("Lo sentimos, no hemos encontrado informacion. por favor intente de nuevo.", array("voice" => "Diego"));
}

?>
