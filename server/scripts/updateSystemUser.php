<?php

header("Access-Control-Allow-Origin: *");

// updateSystemUser.php
//		Calls the System user update routine with the funished $json record
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 4/3/2018         JAB                 Initial creation

include 'databaseConstants.php';
include 'updateDataValues.php';
include 'addLogMessage.php';

// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
	die("<pre>".print_r(sqlsrv_errors(), true));


    $logMessage = "Starting User Information Update Process for " . $json->userAccount . "";
        logAction("UPDATE USER", $logMessage); 

    $json = json_decode(file_get_contents('php://input',true));
   
    // Step 1: updateTicketAssignmentTo

    $status = updateSystemUser($json);

    // Step 2: Add a log record

   $logMessage = "System user " . $json->userAccount . " (" . $json->userName . ") values updated to [" . $json->userName . " | ";
        $logMessage .= $json->userStatus . " | ME: " . $json->memberOfEveryone . " MA: " . $json->memberOfAssociate . " | MP: ";
        $logMessage .= $json->memberOfPartner . " | MSH: " . $json->memberOfSectionHead . " | MTA: " . $json->memberOfTicketAdministration . "]" . " | MSysAdmin: " . $json->memberOfSystemAdministration . "]";
    
    logAction("UPDATE USER", $logMessage);
?>