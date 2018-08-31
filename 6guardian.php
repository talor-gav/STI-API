<?php
$time_start = microtime(true); //timestamp for logging

/* Customize fields to be written to student file
Link to confluence page with available fields xxx */
$data = array( 
'Id', 
'TelephoneNumber'
);

include('ApiClient.include.php');
include('include.php');

//creating API URL
$urlHouseholds = "$apiUrl" . "$householdUrl";

//if opening the files or the curl fails log the error and kill script
if (!$households = getReq($urlHouseholds, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlHouseholds");
}
if (!$fp = fopen("$guardianFile", 'w')) {
    wlogDie("ERROR: Cannot open input file: $guardianFile");
}

$count = 0;

foreach($households as $household){
    $first = substr(explode(", ", $household['Name'])[1], 0 , strpos(explode(", ", $household['Name'])[1], ' ')); //Removes middle name from string
    foreach($data as $value){
        $guarData[] = $household["$value"]; 
    }
    
    if($first == null){ //If there is no middle name
        $guarData['FirstName'] = explode(", ", $household['Name'])[1];

    } else{
        $guarData['FirstName'] = $first;  //if middle name remove it and put first name into array $guarData
    }
    
    $guarData['LastName'] = explode(", ", $household['Name'])[0]; //Returns last name from string and puts it into array $guarData
    ++$count;

    writeLine($fp, $guarData); //writes array $guarData to $output file
    unset($guarData);
}

fclose($fp);

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete

wlog("Wrote $count guardian records ###");
?>