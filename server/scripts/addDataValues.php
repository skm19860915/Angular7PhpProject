<?php

header("Access-Control-Allow-Origin: *");
session_start();

// addDataValues.php
//	  This script includes all the functions thatadd various data records as requested by the controlling scripts.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -------------------------------------------------------------------------
// 05/06/2019       Samuel Leicht       Added 'AssociatedTicketDeliveryType'
// 05/02/2019       Samuel Leicht       Added 'AssignmentNotes'
// 03/09/2018       JAB                 Initial creation
//
//
// Functions:                                                       Returns:
//

include_once('databaseConstants.php');
include_once('addLogMessage.php');

function addTicketAssignment($json)
{

    $logMessage = "Entering addTicketAssignment Function for " . $json->RequestNumber . " (" . $json->statusOfTicketRequest . ")";
    logAction("DEBUG ADD ASSIGNMENT", $logMessage);

    // Connect to database
    $databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

    // If we cannot get a connection lets terminate.
    if ($databaseConnection === false) 
      die(500);

    // Workflow of Ticket Assignment for each ticket
    //
    // 1) Create a record in the tblTicketAssignments from the JSON passed in from the tblTicketRequests
    //    Initial status for this Assignment should be AWAITING REVIEW.
    // 2) Set status field in tblTicketDetail to "ASSIGNED"
    // 3) Set status field in the corresponding tblTicketRequests to "APPROVED".
    // 4) Execute sendEmailTicketAssignmentApproved.php to notify requesting user and admin of approval.

    // 1) Insert Record

    $logMessage = "Decoding JSON for addTicketAssignment Function";
    logAction("DEBUG ADD ASSIGNMENT", $logMessage);

    $json = json_decode(file_get_contents('php://input',true));
        
    // Lets add the record to the Ticket Assignments table

    $createdBy = $_SESSION["user"];

    $logMessage = "Creating SQL for addTicketAssignment Function";
    logAction("DEBUG ADD ASSIGNMENT", $logMessage);
    
    // Start Transaction
    // $sqlStatement  = "BEGIN TRAN ";

    // Action
    $sqlStatement  = "INSERT INTO [" . DB_TABLE_TICKET_ASSIGNMENTS . "] ";
    
    // Columns
    $sqlStatement .= "( ";
    $sqlStatement .= "Status, ";
    $sqlStatement .= "AssociatedTicketRecordNumber, ";
    $sqlStatement .= "AssociatedTicketRequestNumber, ";
    $sqlStatement .= "AssociatedReasonCode, ";
    $sqlStatement .= "AssociatedTicketDeliveryType, ";
    $sqlStatement .= "GuestAssociatedFirmAttorney, ";
    $sqlStatement .= "GuestClientNumber, ";
    $sqlStatement .= "GuestCompany, ";
    $sqlStatement .= "GuestName, ";
    $sqlStatement .= "GuestPhoneNumber, ";
    $sqlStatement .= "GuestEmail, ";
    $sqlStatement .= "AssignmentNotes, ";
    // $sqlStatement .= "stepInitialCreatedDateTime, ";
    $sqlStatement .= "stepInitialCreatedBy ";
    $sqlStatement .= ") ";
   
    $newStatus = "AWAITING ADMIN";

    $sqlStatement .= "VALUES ( ";
    $sqlStatement .= DB_SINGLE_QUOTE . $newStatus . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "" . $json->AssociatedTicketRecordNumber . ", ";
    $sqlStatement .= "" . $json->RequestNumber . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->ReasonCode . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->AssociatedTicketDeliveryType . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->FirmAttorneyForGuest . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->AssociatedGuestClientNumber . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestCompany . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestName . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestPhoneNumber . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestEmail . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->RequestNotes . DB_SINGLE_QUOTE . ", ";
    // $sqlStatement .= DB_SINGLE_QUOTE . $json->ApprovedDateTime . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $createdBy . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "); ";

    // Execute the insert
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    // Error checking in the SQL processing

    if( $sqlData === false ) {
      $logMessage = "Error in Executing SQL for addTicketAssignment Function";
      logAction("DEBUG ADD ASSIGNMENT", $logMessage);
      sqlsrv_free_stmt( $sqlData);
      sqlsrv_close($databaseConnection);
      die(500);
    }
    
    $logMessage = "Decoding JSON for addTicketAssignment Function)";
    logAction("DEBUG ADD ASSIGNMENT", $logMessage);
        
    // 2) Set status field in tblTicketDetail to ASSIGNED

    // Call updateTicketStatus.php - passing in 

    // Action and Table/View

    $sqlStatement = "BEGIN TRAN ";
    $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_DETAIL . " SET [Status]=" . DB_SINGLE_QUOTE . "ASSIGNED" . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "WHERE [TicketRecordNumber]=" . $json->AssociatedTicketRecordNumber  . "; ";

    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    if( $sqlData === false ) {
      $logMessage = "SQL Error in Updating Ticket Status";
      logAction("DEBUG ADD ASSIGN/UTS SQL", $logMessage);
      sqlsrv_close($databaseConnection);       
      die(500);
    }

    // 3) Set status field in the corresponding tblTicketRequests to "APPROVED".

    // Action and Table/View

    $approvedStatus = "APPROVED";

    $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET [Status]=" . DB_SINGLE_QUOTE . $approvedStatus . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "WHERE [RequestNumber]=" . $json->AssociatedTicketRequestNumber  . "; ";

    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    $logMessage = "Updated Ticket Requests for addTicketAssignment Function";
    logAction("DEBUG ADD ASSIGNMENT", $logMessage);

    // Error Check

    if( $sqlData === false ) {
      die(500);
      sqlsrv_close($databaseConnection);    
    }

    // 4) Execute sendEmailTicketAssignmentApproved.php to notify user and admin of approval.

    // Notification Emails

    //$argumentTicketRequestNumber = $json->AssociatedTicketRequestNumber;
    //    $argumentMailTo = 'jbucar@walterhav.com';
    //    $argumentSeatsDetail = 'This is a seat detail text information line.';
        
    //include 'sendEmailNotification.php';
    //    sendEmailTicketsAssigned($argumentMailTo, $argumentTicketRequestNumber, $argumentSeatsDetail);

    // Clean Up

    sqlsrv_free_stmt( $sqlData);
    sqlsrv_close($databaseConnection);    

  }

?>