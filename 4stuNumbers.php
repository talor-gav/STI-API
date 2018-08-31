<?php
$time_start = microtime(true); //timestamp for logging

include('ApiClient.include.php');
include('include.php');
//creating API URLs
$urlPhones = "$apiUrl" . "$numbersUrl";

//if opening the files or the curl fails log the error and kill script
if (!$phones = getReq($urlPhones, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlPhones");
}
if (!$fp = fopen("$languageFile", 'r')) {
    wlogDie("ERROR: Cannot open input file: $languageFile");
}
if (!$output = fopen("$numberFile","w")) {
	wlogDie("ERROR: Cannot open output file: $numberFile");
}

$max = 0; //setting a count to pad rows

foreach($phones as $phone){
    if(!isset($eData[$phone['PersonId']])){ //if the PersonId is not yet in the eData array
        $eData[$phone['PersonId']][] = $phone['TelephoneNumber']; //create an array and add the first TelephoneNumber with the PersonId as the key
    } else{
        $eData[$phone['PersonId']][] = $phone['TelephoneNumber']; //if the PersonId is in the eData array add the next TelephoneNumber with the matching PersonId key
    }
    if(count($eData[$phone['PersonId']]) > $max){ //if the count of TelephoneNumbers in array is greater than max
        $max = count($eData[$phone['PersonId']]); //set max to the new highest amount of TelephoneNumbers
    }
}

unset($emails); //free up memory with no longer used variable

$stuCount = 0; //count for records without data - one less to count because of header
$eCount = 0; //setting a count for data written

while($row = fgetcsv($fp, ",")){ //parse input file 
    $count = count($row);
    if(isset($eData[$row[0]])){ //if the PersonId in row[0] is set in eData array
        foreach($eData[$row[0]] as $value){ 
            $row[] = $value; //add each value to the end of the row
            ++$eCount; //count the number of values for logging
        }
    } else{
        ++$stuCount; //count the number of records without values for logging
    }
    $row = array_pad($row, $max+$count, ''); //pad data fields to the max amount of values
    writeLine($output, $row); //writes $row to $output file
}


fclose($output);
fclose($fp);
unlink($languageFile); //removes comparison file

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete
if($stuCount > 0){
wlog(($stuCount) . " students had no phone data");
}
wlog("Wrote $eCount phone numbers to matching student records ###");
?>