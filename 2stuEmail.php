<?php
$time_start = microtime(true); //timestamp for logging

include('ApiClient.include.php');
include('include.php');

//creating API URL
$urlEmails = "$apiUrl" . "$emailUrl";

//if opening the files or curl fails log the error and kill script
if (!$emails = getReq($urlEmails, $headers)) {
	wlogDie("ERROR: Cannot connect to $urlEmails");
}
if (!$output = fopen("$emailFile","w")) {
	wlogDie("ERROR: Cannot open output file: $emailFile");
}
if (!$fp = fopen("$stuFile","r")) {
    wlogDie("ERROR: Cannot open input file: $stuFile");
}

$max = 0; //setting a count to pad rows

foreach($emails as $email){ 
    if(!isset($eData[$email['PersonId']])){ //if PersonId is not yet in the eData array
        $eData[$email['PersonId']][] = $email['EmailAddress']; //create an array and add the first EmailAddress with the PersonId as the key
    } else{
        $eData[$email['PersonId']][] = $email['EmailAddress']; //if the PersonId is in the eData array add the next EmailAddress with the matching PersonId key
    }
    if(count($eData[$email['PersonId']]) > $max){ //if the count of EmailAddresses in array is greater than max
        $max = count($eData[$email['PersonId']]); //set max to the new highest amount of EmailAddresses
    }
    
} 

unset($emails); //get rid of unused variable

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
    if($row[4] === '1'){  
        $row[4] = 'Male';
    } else{
        $row[4] = 'Female';
    }
    $row = array_pad($row, $max+$count, ''); //pad data fields to the max amount of values
    writeLine($output, $row); //writes $row to $output file
}

fclose($fp);
fclose($output);
unlink($stuFile); //removes comparison file

$time_end = microtime(true); //time stamp for logging
$execution_time = round(($time_end - $time_start),2); //seconds rounded to the nearest hundredth 

wlog("Peak memory usage was " . round((memory_get_peak_usage(True)*.000001), 2) . " MB"); //log the highest memory used during script
wlog('Total execution time: '.$execution_time.' seconds'); //log the time it took for script to complete
if($stuCount > 0){
wlog(($stuCount) . " students had no email data");
}
wlog("Wrote $eCount emails to matching student records ###");
?>