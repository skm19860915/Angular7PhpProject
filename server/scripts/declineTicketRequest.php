<?php

header("Access-Control-Allow-Origin: *");

// declineTicketRequest.php
// Declines a ticket request
//
// Walter | Haverfield
// 
// Author:  Samuel Leicht
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------
// 04/29/2019       Samuel Leicht       Initial creation

include_once('databaseConstants.php');
include_once('addLogMessage.php');
include_once('addDataValues.php');

$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "DECLINE TICKET REQUEST";

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

    $sql = "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET ";
    $sql .= "Status = 'NOT APPROVED' WHERE RequestNumber = '$input->RequestNumber'";

    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sql );

    // Error Handling
    if( $sqlData === false ) {
      logAction( $logAction, "Error while executing sql query." );
      http_response_code(500);
      die();
    }

    logAction( $logAction, "Declined request " . $input->RequestNumber . "." );

    sqlsrv_close( $databaseConnection );
  }
}