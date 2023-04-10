<?php

header("Access-Control-Allow-Origin: *");

// updateTicketRequest.php
// Updates the passed in TicketRequest record
//
// Walter | Haverfield
//
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------
// 05/03/2019       Samuel Leicht       Added 'TicketDeliveryType'
// 04/29/2019       Samuel Leicht       Bugfix: Request Deny doesn't reset ticket status
// 03/09/2018       JAB                 Initial creation

include_once('databaseConstants.php');
include_once('addLogMessage.php');
include_once('addDataValues.php');

$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "UPDATE TICKET REQUEST";

if ( ! isset( $input->RequestNumber ) || ! isset( $input->statusOfTicketRequest ) ) {
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
    $formattedRequestNotes = str_replace( "'", "''", $input->RequestNotes );

    $sqlStatement .= "UPDATE ";
    $sqlStatement .= DB_TABLE_TICKET_REQUESTS . " ";
    $sqlStatement .= "SET ";
    $sqlStatement .= "[ReasonCode]=" . DB_SINGLE_QUOTE . $input->ReasonCode . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[ClientMatter]=" . DB_SINGLE_QUOTE . $input->ClientMatter . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[Status]="  . DB_SINGLE_QUOTE . $input->statusOfTicketRequest . DB_SINGLE_QUOTE . ", ";
    // $sqlStatement .= "[Status]="  . DB_SINGLE_QUOTE . $input->Status . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[AssociatedGuestClientNumber]="  . DB_SINGLE_QUOTE . $input->AssociatedGuestClientNumber . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[AssociatedTicketDeliveryType]="  . DB_SINGLE_QUOTE . $input->AssociatedTicketDeliveryType . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[GuestCompany]="  . DB_SINGLE_QUOTE . $input->GuestCompany . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[GuestName]="  . DB_SINGLE_QUOTE . $input->GuestName . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[GuestEmail]="  . DB_SINGLE_QUOTE . $input->GuestEmail . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[RequestNotes]="  . DB_SINGLE_QUOTE . $formattedRequestNotes . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "[GuestPhoneNumber]="  . DB_SINGLE_QUOTE . $input->GuestPhoneNumber . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "WHERE [RequestNumber]=" . $input->RequestNumber  . "; ";

    $sqlStatement .= "COMMIT TRAN";

    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement );

    // Error Handling
    if( $sqlData === false ) {
      logAction( $logAction, "Unable to connect to database." );
      http_response_code(500);
      die();
    }

    // ADD DELIVERY TYPE TO REQUEST TABLE?????

    logAction( $logAction, "Updated request $input->RequestNumber with new values." );

    // Now lets check and see if the request is APPROVED,
    // if so we need to call the addTicketAssignment.

    if( $input->statusOfTicketRequest === 'APPROVED' ) {

      logAction( $logAction, "Create ticket assignment after request $input->RequestNumber was approved." );
      addTicketAssignment( $input );
    }

    sqlsrv_close( $databaseConnection );
  }
}
