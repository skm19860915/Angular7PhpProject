<?php

header("Access-Control-Allow-Origin: *");

// cancelTicketRequest.php
// Cancels a Ticket Request in the system and performs cleanup duty..
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 04/28/2019       Samuel Leicht       Bugfix: Ticketstatus should remain PENDING if there are any other pending requests
// 03/09/2018       JAB                 Initial creation
//
// Pass In Values:
// 
// Pass in JSON object representing the Ticket Request that needs to be cancelled.
//

include 'databaseConstants.php';
    
// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "CANCEL TICKET REQUEST";

// If we cannot get a connection lets terminate.
if ( $databaseConnection === false ) {
  logAction( $logAction, "Unable to connect to database." );
  http_response_code(500);
  die();
}
else if ( ! isset( $input ) ) { 
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else {

  // Step 1:  Change status of TicketRequestNumber to "CANCELLED"
  include 'updateDataValues.php';
  $statusTicketRequestUpdate = updateTicketRequestStatus( $input->RequestNumber, 'CANCELLED' );
  logAction( $logAction, "Ticket Request $input->RequestNumber was cancelled." );

  // Step 2:  Get number of other requests associated with this ticket
  // If there are no other requests, reset ticket status

  include 'getTicketRequestsForTicket.php';
  $requests = getTicketRequestsForTicket( $input->AssociatedTicketRecordNumber );
  $requests = count ( $requests );

  if( $requests == 1 ) {
    logAction( $logAction, "There is $requests more request for ticket " . $input->AssociatedTicketRecordNumber ."." );
  }
  else if( $requests > 1 ) {
    logAction( $logAction, "There are $requests more requests for ticket " . $input->AssociatedTicketRecordNumber ."." );
  }
  else {
    $statusTicketUpdate = updateTicketStatus( $input->AssociatedTicketRecordNumber, 'AVAILABLE' );
    logAction( $logAction, "Status of ticket $input->AssociatedTicketRecordNumber was reset to AVAILABLE since there are no more requests." );
  }

  // Step 3:  Send notification email of cancellation to requestor and admin

  // include 'sendEmailNotification.php';
  // $sendEmailTo = "jbucar@walterhav.com";
  // sendEmailTicketRequestCancelled($sendEmailTo, $input->RequestNumber);

  sqlsrv_close( $databaseConnection );
}