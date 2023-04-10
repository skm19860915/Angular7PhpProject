<?php

header("Access-Control-Allow-Origin: *");

// updateTicketAssignment.php
//		Calls the TicketAssignment Update routine with the funished $json record
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

//
// Steps needed to accomplish in the transferTicketProcess.
//
// 1) Call updateTicketAssignment passing json object
// 2) Add log record


    $json = json_decode(file_get_contents('php://input',true));
   
    // Step 1: updateTicketAssignmentTo

    updateTicketAssignmentStatusAdmin($json);

    // Step 2: Add a log record

    $logMessage = "Ticket Assignment (#" . $json->AssignmentNumber . ") status has been updated with new data. ";
            logAction("UPDATE TICKET ASSIGN STATUS", $logMessage);
  
?>
