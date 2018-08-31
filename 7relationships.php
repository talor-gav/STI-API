<?php
$time_start = microtime(true); //timestamp for logging

include('ApiClient.include.php');
include('include.php');

//creating API URL
$urlRelationships = "$apiUrl" . "$relationshipUrl";
$urlGuardians = "$apiUrl" . "$guardianUrl";

//if opening the files or the curl fails log the error and kill script
if (!$relationships = getReq($urlGuardians, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlGuardians");
}
if (!$lookups = getReq($urlRelationships, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlRelationships");
}
if (!$fp = fopen("$relationshipFile", 'w')) {
    wlogDie("ERROR: Cannot open input file: $relationshipFile");
}

foreach($lookups as $lookup){ 
    if(!isset($lData[$lookup['Id']])){ //if Id is not yet in the lData array
        $lData[$lookup['Id']][] = $lookup['Name']; //create an array and add the language with the Id as the key
    } 
}

unset($lookups); //get rid of unused variable

$count = 0;

foreach($relationships as $relationship){
    $row['ContactId'] = $relationship['ContactId'];
    $row['StudentId'] = $relationship['StudentId'];
    if(isset($lData[$relationship['ContactRelationshipId']])){
        $row['Relationship'] = $lData[$relationship['ContactRelationshipId']][0];
    }
    ++$count;
    writeLine($fp, $row);
} 

fclose($fp);

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete
wlog("Wrote $count guardian to student relationships ###");
?>