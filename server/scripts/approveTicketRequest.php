<?php

header("Access-Control-Allow-Origin: *");

// approveTicketRequest.php
// Approves a ticket request
//
// Walter | Haverfield
//
// Author:  Samuel Leicht
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------
// 05/07/2019       Samuel Leicht       Auto-decline all pending requests
// 05/02/2019       Samuel Leicht       Added 'AssignmentNotes'
// 04/29/2019       Samuel Leicht       Initial creation

include_once('databaseConstants.php');
include_once('addLogMessage.php');
include_once('addDataValues.php');

$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "APPROVE TICKET REQUEST";

if ( ! isset( $input->RequestNumber ) || ! isset( $input->statusOfTicketRequest ) || ! isset( $input->AssociatedTicketRecordNumber ) ) {
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else {

  // Connect to database
  $databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME );
  $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions );

  if ( $databaseConnection === false ) {
    logAction( $logAction, "Unable to connect to database." );
    http_response_code(500);
    die();
  }
  else {

    // Approve request and auto-decline all other pending request for this ticket
    $sql = "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET ";
    $sql .= "Status = 'APPROVED' WHERE RequestNumber = '$input->RequestNumber';";
    $sql .= "UPDATE " . DB_TABLE_TICKET_REQUESTS;
    $sql .= " SET Status = 'NOT APPROVED'";
    $sql .= " WHERE AssociatedTicketRecordNumber = ";
    $sql .=  $input->AssociatedTicketRecordNumber;
    $sql .= " AND Status = 'SUBMITTED/PENDING'";

    // Execute query
    $sqlData = sqlsrv_query( $databaseConnection, $sql );

    // Error handling
    if( $sqlData === false ) {
      logAction( $logAction, "Error while approving request for ticket $input->AssociatedTicketRecordNumber.");
      http_response_code( 500 );
    }
    else {
      logAction( $logAction, "Sucessfully approved request $input->RequestNumber." );
      http_response_code( 204 );
    }

    // Initiate ticket assignment creation since the request was approved
    addTicketAssignment( $input );

    sqlsrv_close( $databaseConnection );
  }
}
