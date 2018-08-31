<?php
//End-points for data
//List of end-points Goes here
$apiUrl = 'Url goes here';
$studentUrl = 'students';
$emailUrl = 'persons/emailaddresses';
$languageUrl = 'persons/languages';
$numbersUrl = 'persons/telephonenumbers';
$gradesUrl = 'students/gradeLevels';
$gLookupUrl = 'gradelevels';
$lLookupUrl = 'languages';
$guardianUrl = 'students/contacts';
$householdUrl = 'households';
$relationshipUrl = 'contactrelationships';


//File paths
$logfilename = 'testlog.log';
$stuFile = 'students.csv';
$emailFile ='stuEmail.csv';
$languageFile = 'stuLanguage.csv';
$numberFile = 'stuNumber.csv';
$gradeFile = 'studentData.csv';
$guardianFile = 'guardians.csv';
$relationshipFile ='relationships.csv';

//API authentication Password
$pass = "password goes here";

//Header array for API Authentication
$headers = array();
$headers[] = 'ApplicationKey: app key goes here';
$headers[] = 'Authorization: Basic ' . base64_encode($pass);
$headers[] = 'Content-Type: application/json';


//Creates an array of API Endpoint defined by $url
function getReq($url, $headers){
    $crl = curl_init();

    curl_setopt($crl, CURLOPT_URL, $url);
    curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);


    $execCrl = curl_exec($crl);
    $data = json_decode($execCrl, true);
    unset($execCrl);
    curl_close($crl);


    return($data);
}
?>