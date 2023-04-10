<?php

header("Access-Control-Allow-Origin: *");

// cancelTicketAssignment.php
//		Cancels a Ticket Assignment in the system and performs cleanup duty..
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 3/9/2018         JAB                 Initial creation
//
//
// Pass In Values:
// 
// Pass in JSON object representing the Ticket Request that needs to be cancelled.
//

include 'databaseConstants.php';
    
// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
    {   
	    die(500);
    }

// Get json object data
$json = json_decode(file_get_contents('php://input',true));

// Steps to cancelling a Ticket Assignment -
//
// #1 Mark the status of the TicketAssignmentNumber to CANCELLED
// #2 Mark the status of the related TicketRequestNumber to CANCELLED
// #3 Mark the status of the related TicketRecordNumber in tblTicketDetail to AVAILABLE
// #4 Email Ticket Requestor and Admin a notice of Cancellation
//

include 'updateDataValues.php';

    // Step 1:  Change status of TicketAssignmentNumber to CANCELLED
    $statusTicketAssignmentUpdate = updateTicketAssignmentStatus($json->AssignmentNumber, 'CANCELLED');
        // echo "<br />Ticket Assignment Status Update: " . $statusTicketAssignmentUpdate . "";

    // Step 2:  Change status of TicketRequestNumber in tblTicketDetail to CANCELLED
    $statusTicketRequestUpdate = updateTicketRequestStatus($json->AssociatedTicketRequestNumber, 'CANCELLED');
        // echo "<br />Ticket Request Status Update: " . $statusTicketRequestUpdate . "";
    
    // Step 3:  Change status of TicketRecordNumber in tblTicketDetail to AVAILABLE
    $statusTicketRecordUpdate = updateTicketStatus($json->AssociatedTicketRecordNumber, 'AVAILABLE');
        // echo "<br />Ticket Record Status Update: " . $statusTicketRecordUpdate . "";
    
    // Step 4:  Send notification email of cancellation to requestor and admin
    include 'sendEmailNotification.php';
        $sendEmailTo = "jbucar@walterhav.com";
        sendEmailTicketAssignmentCancelled($sendEmailTo, $json->AssignmentNumber);
        sendEmailTicketRequestCancelled($sendEmailTo, $json->AssociatedTicketRequestNumber);
    
    // Clean Up
    sqlsrv_close($databaseConnection);

?>
