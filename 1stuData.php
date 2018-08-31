<?php
$time_start = microtime(true); //timestamp for logging

/* Customize fields to be written to student file
Link to confluence page with available fields xxx */
$data = array( 
'Id', 
'StudentNumber',
'FirstName',
'LastName',
'GenderId'
);

include('ApiClient.include.php');
include('include.php');

//creating API URL
$urlStudents = "$apiUrl" . "$studentUrl";

if (!$students = getReq($urlStudents, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlStudents URL");
}
if (!$fp = fopen("$stuFile", 'w')) {
    wlogDie("ERROR: Cannot open input file: $stuFile");
}

$stuCount = 0;

foreach($students as $student){
    foreach($data as $value){
        $stuData[] = $student["$value"];
    }
    //$stuData[] = $student['Value']; //If data in a field needs modified uncomment this line and replace value
    writeLine($fp, $stuData); //write $stuData array to students.csv
    ++$stuCount;
    unset($stuData);
}

fclose($fp);

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete
wlog("Wrote $stuCount student records ###");
?>