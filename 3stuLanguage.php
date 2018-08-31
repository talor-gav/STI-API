<?php
$time_start = microtime(true); //timestamp for logging

include('ApiClient.include.php');
include('include.php');

//creating API URLs
$urlLanguage = "$apiUrl" . "$languageUrl";
$urlLookup = "$apiUrl" . "$lLookupUrl";

//if opening the files or the curl fails log the error and kill script
if (!$lookups = getReq($urlLookup, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlLookup");
}
if (!$languages = getReq($urlLanguage, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlLanguage");
}
if (!$fp = fopen("$emailFile", 'r')) {
    wlogDie("ERROR: Cannot open input file: $emailFile");
}
if (!$output = fopen("$languageFile","w")) {
	wlogDie("ERROR: Cannot open output file: $languageFile");
}

foreach($lookups as $lookup){ 
    if(!isset($lData[$lookup['Id']])){ //if Id is not yet in the lData array
        $lData[$lookup['Id']][] = $lookup['Name']; //create an array and add the language with the Id as the key
    } 
}

unset($lookups); //get rid of unused variable

$max = 0; //setting a count to pad rows

foreach($languages as $language){
    if(isset($lData[$language['LanguageId']])){
        if($language['IsPrimary'] === True){  //if the language "IsPrimary" field is set to "true" 
            if(!isset($eData[$language['PersonId']])){ //if PersonId is not yet in the eData array
                $eData[$language['PersonId']][] = $lData[$language['LanguageId']][0]; //create an array and add the language with the PersonId as the key
            }
            if(count($eData[$language['PersonId']]) > $max){ //if the count of LanguageId in array is greater than max
                $max = count($eData[$language['PersonId']]); //set max to the new highest amount of LanguageId
            }
        }
    }
} 

unset($lData); //get rid of unused variable
unset($languages); //get rid of unused variable

$stuCount = 0; //count for records without data - one less to count because of header
$eCount = 0; //setting a count for data written

while($row = fgetcsv($fp, ",")){ //parse input file
    $count = count($row); //setting a count to pad rows
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
unlink($emailFile); //removes comparison file

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete
if($stuCount > 0){
wlog(($stuCount) . " students had no language data");
}
wlog("Wrote $eCount languages to matching student records ###");
?>  