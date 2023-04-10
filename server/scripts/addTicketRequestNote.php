<?php

header("Access-Control-Allow-Origin: *");
session_start();

// addTicketRequestNote.php
// Used to add notes to a ticket request.
//
// Walter | Haverfield
// 
// Author:  Samuel Leicht
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 04/30/2019       Samuel Leicht       Initial Creation

include_once('databaseConstants.php');
include_once('mailConstants.php');
include_once('addLogMessage.php');
//include_once('sendEmailNotification.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD TICKET REQUEST NOTE";

// If we cannot get a connection lets terminate.
if ( $databaseConnection === false ) {
  logAction( $logAction, "Unable to connect to database." );
  http_response_code(500);
  die();
}
// If SpecialRequestNumber is unset lets refuse request.
else if ( ! isset( $input->AssociatedRequestNumber ) || ! isset( $input->Content ) || ! isset( $input->CreatedBy ) ) { 
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else {

  $formatNoteContent = str_replace( "'", "''", $input->Content );
  
  $sql = "INSERT INTO " . DB_TICKET_REQUEST_NOTES . "(AssociatedRequestNumber, CreatedBy, Content) ";
  $sql .= "VALUES ($input->AssociatedRequestNumber, '$input->CreatedBy', '$input->Content')";

  // Execute the insert
	$sqlData = sqlsrv_query( $databaseConnection, $sql );
 
  // Error checking in the SQL processing
  if( $sqlData === false ) { 
    logAction( $logAction, "Error while adding ticket request note.");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Ticket request note was successfully saved." );
    http_response_code( 201 );
  }
	
	sqlsrv_free_stmt( $sqlData );
  sqlsrv_close( $databaseConnection );
}