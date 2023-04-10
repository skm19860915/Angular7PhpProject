<?php

// addTicketAssignment.php
// Used to assign a ticket to the system.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------
// 21/06/2019       Samuel Leicht       Added IF condition for updating request
// 05/06/2019       Samuel Leicht       Added 'AssociatedTicketDeliveryType'
// 05/02/2019       Samuel Leicht       Added 'AssignmentNotes'
// 03/09/2018       JAB                 Initial creation

header("Access-Control-Allow-Origin: *");
session_start();

include 'databaseConstants.php';
include 'addLogMessage.php';
   
// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD TICKET ASSIGNMENT";

// If we cannot get a connection lets terminate.
if ( $databaseConnection === false ) {
  logAction( $logAction, "Unable to connect to database." );
  http_response_code(500);
  die();
}
// If SpecialRequestNumber is unset lets refuse request.
else if ( ! isset( $input ) ) { 
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else {

  /*
  
  Workflow of Ticket Assignment for each ticket
  
  1) Create a record in the tblTicketAssignments from the JSON passed in from the tblTicketRequests.
     Initial status for this Assignment should be AWAITING REVIEW.
  2) Set status field in tblTicketDetail to "ASSIGNED"
  3) Set status field in the corresponding tblTicketRequests to "APPROVED".
  4) Execute sendEmailTicketAssignmentApproved.php to notify requesting user and admin of approval.

  */

  // Step 1) Insert Record
      
  $createdBy = $_SESSION["user"];

  $sqlStatement  = "INSERT INTO [" . DB_TABLE_TICKET_ASSIGNMENTS . "] ";
  $sqlStatement .= "( ";
  $sqlStatement .= "Status, ";
  $sqlStatement .= "AssociatedTicketRecordNumber, ";
  //$sqlStatement .= "AssociatedTicketRequestNumber, ";
  $sqlStatement .= "AssociatedReasonCode, ";
  $sqlStatement .= "AssociatedTicketDeliveryType, ";
  $sqlStatement .= "GuestAssociatedFirmAttorney, ";
  $sqlStatement .= "GuestClientNumber, ";
  $sqlStatement .= "GuestCompany, ";
  $sqlStatement .= "GuestName, ";
  $sqlStatement .= "GuestPhoneNumber, ";
  $sqlStatement .= "GuestEmail, ";
  $sqlStatement .= "AssignmentNotes, ";
  $sqlStatement .= "stepInitialCreatedBy ";
  $sqlStatement .= ") ";
  $sqlStatement .= "VALUES ( ";
  $sqlStatement .= DB_SINGLE_QUOTE . "AWAITING ADMIN" . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "" . $json->AssociatedTicketRecordNumber . ", ";
  //$sqlStatement .= "" . $json->RequestNumber . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->AssociatedReasonCode . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->AssociatedTicketDeliveryType . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestAssociatedFirmAttorney . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->AssociatedGuestClientNumber . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestCompany . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestName . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestPhoneNumber . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->GuestEmail . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $json->AssignmentNotes . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $createdBy . DB_SINGLE_QUOTE . " ";
  $sqlStatement .= "); ";

  // Execute the insert
  $sqlData = sqlsrv_query( $databaseConnection, $sql );

  // Error handling
  if( $sqlData === false ) { 
    logAction( $logAction, "Error while adding ticket assignment for ticket $input->AssociatedTicketRecordNumber and request $input->AssociatedTicketRequestNumber.");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Ticket assignment for ticket $input->AssociatedTicketRecordNumber and request $input->AssociatedTicketRequestNumber was successfully created." );
    http_response_code( 201 );
  }
         
  // 2) Set status field in tblTicketDetail to "ASSIGNED"

  $sql .= "UPDATE " . DB_TABLE_TICKET_DETAIL . " SET [Status]=" . DB_SINGLE_QUOTE . "ASSIGNED" . DB_SINGLE_QUOTE . " ";
  $sql .= "WHERE [TicketRecordNumber]=" . $input->AssociatedTicketRecordNumber;

  // Execute the update
  $sqlData = sqlsrv_query( $databaseConnection, $sql);

  // Error handling
  if( $sqlData === false ) { 
    logAction( $logAction, "Error while updating status of ticket $input->AssociatedTicketRecordNumber.");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Status for ticket $input->AssociatedTicketRecordNumber was successfully updated (ASSIGNED)." );
    http_response_code( 201 );
  }

  // 3) Set status field in the corresponding tblTicketRequests to "APPROVED" if provided
  if ( isset ( $input->RequestNumber ) ) {

    $sql .= "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET [Status]=" . DB_SINGLE_QUOTE . "APPROVED" . DB_SINGLE_QUOTE . " ";
    $sql .= "WHERE [RequestNumber]=" . $input->AssociatedTicketRequestNumber  . "; ";

    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sql);

    // Error handling
    if( $sqlData === false ) { 
      logAction( $logAction, "Error while updating status of request $input->AssociatedTicketRequestNumber.");
      http_response_code( 500 );
    }
    else {
      logAction( $logAction, "Status for request $input->AssociatedTicketRequestNumber was successfully updated (APPROVED)." );
      http_response_code( 201 );
    }
  }

  // 4) Execute sendEmailTicketAssignmentApproved.php to notify user and admin of approval.
   
  $argumentTicketRequestNumber = $input->AssociatedTicketRequestNumber;
  $argumentMailTo = 'jbucar@walterhav.com';
  $argumentSeatsDetail = 'This is a seat detail text information line.';
        
  include 'sendEmailNotification.php';
  sendEmailTicketsAssigned($argumentMailTo, $argumentTicketRequestNumber, $argumentSeatsDetail);

  sqlsrv_free_stmt( $sqlData);
  sqlsrv_close($databaseConnection);
}