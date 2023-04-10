<?php

header("Access-Control-Allow-Origin: *");
session_start();

// addTicketRequest.php
// Used to add a ticket request to the system.
//
// Walter | Haverfield
//
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// ---------------------------------------------------------------------------------------------------------------------------
// 05/06/2019       Samuel Leicht       Save 'AssociatedTicketDeliveryType' directly to request table to make it changeable
// 05/03/2019       Samuel Leicht       Added 'AssociatedTicketDeliveryType'
// 05/01/2019       Samuel Leicht       Added 'RequestNotes'
// 03/29/2019       Samuel Leicht       Added 'TicketDeliveryType'
// 03/09/2018       JAB                 Initial creation

include 'databaseConstants.php';
include 'addLogMessage.php';

// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD TICKET REQUEST";

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

  Steps needed to accomplish in the addTicketRequest process

  1) Check for auto approval
  2) Insert record into ticket requests and ticket request notes table
  3) Ticket Detail Record needs status updated to PENDING REQUESTS
      or ASSIGNED based on Auto Approval being turned on.
  4) Update ticket status

  */

  // Step 1: Check if auto approval is on.
  $requestStatus = 'SUBMITTED/PENDING';

  if ( $input->AutoApprove === 'Y' ) {
    $requestStatus = 'APPROVED';
  }

  // Step 2: Insert table records
  $formattedRequestNotes = str_replace( "'", "''", $input->RequestNotes );

  $sql  = "INSERT INTO " . DB_TABLE_TICKET_REQUESTS . " (";
  $sql .= "AssociatedTicketRecordNumber, ";
  $sql .= "CreatedBy, ";
  $sql .= "Status, ";
  $sql .= "RequestedBy, ";
  // $sql .= "RequestDateTime, ";
  $sql .= "ReasonCode, ";
  $sql .= "FirmAttorneyForGuest, ";
  $sql .= "AssociatedGuestClientNumber, ";
  $sql .= "AssociatedTicketDeliveryType, ";
  $sql .= "GuestCompany, ";
  $sql .= "GuestName, ";
  $sql .= "GuestEmail, ";
  $sql .= "GuestPhoneNumber, ";
  $sql .= "RequestNotes, ";
  $sql .= "ClientMatter";
  $sql .= ") VALUES (";
  $sql .= $input->TicketRecordNumber . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->CreatedBy . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $requestStatus . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->RequestedBy . DB_SINGLE_QUOTE . ", ";
  // $sql .= DB_SINGLE_QUOTE . $input->RequestedDateTime . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->ReasonCode . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->FirmAttorneyForGuest . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->AssociatedGuestClientNumber . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->TicketDeliveryType . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->GuestCompany . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->GuestName . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->GuestEmail . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->GuestPhoneNumber . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $formattedRequestNotes . DB_SINGLE_QUOTE . ", ";
  $sql .= DB_SINGLE_QUOTE . $input->fldClientMatter . DB_SINGLE_QUOTE . " ";
  $sql .= "); ";

   // Append this to return the added Request Number
  $sql .= "SELECT SCOPE_IDENTITY() AS addedRequestNumber; ";

  // Execute query
  $sqlData = sqlsrv_query( $databaseConnection, $sql );

  // Get recently added request number
  $addedReqNbr = getAddedRequestNumber( $sqlData );

  // Error handling
  if( $sqlData === false ) {
    logAction( $logAction, "Error while adding ticket request for ticket $input->TicketRecordNumber.");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Ticket request $addedReqNbr for ticket $input->TicketRecordNumber by user $input->RequestedBy was successfully saved." );
    http_response_code( 201 );
  }

  // Send notification email(s) for Request Creation

  // include 'sendEmailNotification.php';

  // $sendEmailTo = "jbucar@walterhav.com";
  // sendEmailTicketRequestCreated( $sendEmailTo, $addedReqNbr );
  // logAction( $logAction, "Sent notification email after adding request $input->TicketRecordNumber.");

  // Step 3: Check auto approval field. If true, go ahead and automatically approve it calling addTicketAssignment.php
  // to assign the tickets automatically.
  if ( $requestStatus == 'APPROVED' ) {

    logAction( $logAction, "Request $addedReqNbr gets auto approved.");

    $newInitialDate = date_format(new DateTime(), 'Y-m-d H:i:s');

    $sql  = "INSERT INTO " . DB_TABLE_TICKET_ASSIGNMENTS . " (";
    $sql .= "Status, ";
    $sql .= "AssociatedTicketRecordNumber, ";
    $sql .= "AssociatedTicketDeliveryType, ";
    $sql .= "AssociatedTicketRequestNumber, ";
    $sql .= "AssociatedReasonCode, ";
    $sql .= "GuestAssociatedFirmAttorney, ";
    $sql .= "GuestClientNumber, ";
    $sql .= "GuestCompany, ";
    $sql .= "GuestName, ";
    $sql .= "GuestPhoneNumber, ";
    $sql .= "GuestEmail, ";
    $sql .= "stepInitialCreatedDateTime, ";
    $sql .= "AssignmentNotes, ";
    $sql .= "stepInitialCreatedBy, ";
    $sql .= "ClientMatter";
    $sql .= ") ";
    $sql .= "VALUES ( ";
    $sql .= DB_SINGLE_QUOTE . "AWAITING ADMIN" . DB_SINGLE_QUOTE . ", ";
    $sql .= $input->TicketRecordNumber . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->TicketDeliveryType . DB_SINGLE_QUOTE . ", ";
    $sql .= $addedReqNbr . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->ReasonCode . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->FirmAttorneyForGuest . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->AssociatedGuestClientNumber . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->GuestCompany . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->GuestName . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->GuestPhoneNumber . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->GuestEmail . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $newInitialDate . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $formattedRequestNotes . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $_SESSION["user"] . DB_SINGLE_QUOTE . ", ";
    $sql .= DB_SINGLE_QUOTE . $input->fldClientMatter . DB_SINGLE_QUOTE . " ";
    $sql .= "); ";

    // Execute query
    $sqlData = sqlsrv_query($databaseConnection, $sql);

    // Error handling
    if( $sqlData === false ) {
      logAction( $logAction, "Error while adding ticket assignment for ticket $input->TicketRecordNumber and request $addedReqNbr.");
      http_response_code( 500 );
    }
    else {
      logAction( $logAction, "Ticket assignment for ticket $input->TicketRecordNumber and request $addedReqNbr for user $input->RequestedBy was successfully saved." );
      http_response_code( 201 );
    }

    // Send notification email(s) for Assignment Creation

    // $sendEmailTo = "jbucar@walterhav.com";
    // sendEmailTicketRequestCreated( $sendEmailTo, $addedReqNbr, 'This would be the seat detail.' );
    // logAction( $logAction, "Sent notification email after adding assignment $input->TicketRecordNumber.");
  }

  // Step 4: Update the Ticket Record

  include 'updateDataValues.php';

  if ( $requestStatus == 'APPROVED' ) {
    updateTicketStatus( $input->TicketRecordNumber, 'ASSIGNED' );
    logAction( $logAction, "Set status of ticket $input->TicketRecordNumber to ASSIGNED." );
  }
  else {
    updateTicketStatus( $input->TicketRecordNumber, 'PENDING REQUESTS' );
    logAction( $logAction, "Set status of ticket $input->TicketRecordNumber to PENDING REQUESTS." );
  }
}

function getAddedRequestNumber( $queryID ) {
  sqlsrv_next_result( $queryID );
  sqlsrv_fetch( $queryID );
  return sqlsrv_get_field( $queryID, 0 );
}
