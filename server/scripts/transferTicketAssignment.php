<?php

header("Access-Control-Allow-Origin: *");

// transferTicketAssignment.php
//		Transfers a Ticket Assignment between users
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
	die(500);

//
// Steps needed to accomplish in the transferTicketProcess.
//
// 1) Call updateTicketAssignmentTo passing json object
// 2) Call updateTicketRequestBy passing request number and user transferred to
// 3) Add log record
// 4) Send email notifications
//          To person whos ticket was transferred
//          To person who received the ticket.

    $json = json_decode(file_get_contents('php://input',true));
   
    // Step 1: updateTicketAssignmentTo

    updateTicketAssignmentTo($json);

    // Step 2: updateTicketRequest

    updateTicketRequestBy($json);

    // Step 3: Add a log record

    $logMessage = "User " . $json->GuestAssociatedFirmAttorney . " transferred a Ticket Assignment (#" . $json->AssignmentNumber . ") for Ticket Record Number " . $json->AssociatedTicketRecordNumber . " ";
        $logMessage .= "to User " . $json->TransferToUser->userAccount . "";
            logAction("TRANSFER TICKET ASSIGNMENT", $logMessage);

    // Step 4: Send email notifications
    include 'sendEmailNotification.php';
    
        $sendEmailTo = "jbucar@walterhav.com";
            sendEmailTicketAssignmentTransferredFrom($sendEmailTo,$json->AssociatedTicketRecordNumber);
                $logMessage = "Email notice sent for FROM: transfer Ticket Assignment (#" . $json->AssignmentNumber . ") for Ticket Record Number " . $json->AssociatedTicketRecordNumber . " ";
                     logAction("SEND EMAIL NOTIFICATION", $logMessage);
        
        $sendEmailTo = "jbucar@walterhav.com";
            sendEmailTicketAssignmentTransferredTo($sendEmailTo,$json->AssociatedTicketRecordNumber);
                $logMessage = "Email notice sent for TO: transfer of Ticket Assignment (#" . $json->AssignmentNumber . ") for Ticket Record Number " . $json->AssociatedTicketRecordNumber . " ";
                    logAction("SEND EMAIL NOTIFICATION", $logMessage);

            $IcalStatus = sendIcalEvent('Event Ticketing System', 'jabucar@huntinglake.com', 'Joseph Bucar', 'jbucar@walterhav.com', '11/12/2013 18:00:00','11/12/2013 19:00:00', $startTime, $endTime, 'Test Event','This is a test event','Test location');
                $logMessage = "Ical Event sent (" . $IcalStatus . " TO: transfer of Ticket Assignment (#" . $json->AssignmentNumber . ") for Ticket Record Number " . $json->AssociatedTicketRecordNumber . " ";
                    logAction("SEND ICAL EVENT", $logMessage);
      
?>
