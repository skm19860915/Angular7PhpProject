<?php

header("Access-Control-Allow-Origin: *");

/**
 * deleteTicketTemplate.php
 * Used to delete a ticket template in the system
 * @param TicketTemplateName the unique name of the template
 */

session_start();

include_once('databaseConstants.php');
include_once('mailConstants.php');
include_once('addLogMessage.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "DELETE TICKET TEMPLATE";

// If we cannot get a connection lets terminate.
if ( $databaseConnection === false ) {
  logAction( $logAction, "Unable to connect to database." );
  http_response_code(500);
  die();
}
// If there's no name (only required field), lets refuse request
else if ( ! isset( $input->TicketTemplateName ) ) { 
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else {

  logAction( $logAction, "Starting Ticket Template Removal (Name: $input->TicketTemplateName).");

  // Start Transaction
  $sqlStatement  = "BEGIN TRAN ";

  // Action
  $sqlStatement .= "DELETE FROM [" . DB_TABLE_TICKET_TEMPLATES . "] ";
  $sqlStatement .= "WHERE TicketTemplateName='" . $input->TicketTemplateName . "' ";
  $sqlStatement .= "COMMIT TRAN";
     
  // Execute the insert
  $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
  // Error checking in the SQL processing
  if( $sqlData === false ) { 
    // if ( ($errors = sqlsrv_errors() ) != null) {
    //   foreach( $errors as $error ) {
    //     logAction( $logAction, "SQLSTATE: ".$error[ 'SQLSTATE']."<br />");
    //     logAction( $logAction, "code: ".$error[ 'code']."<br />");
    //     logAction( $logAction, "message: ".$error[ 'message']."<br />");
    //   }
    // }
    handleError("Error while creating ticket template (Name: $input->TicketTemplateName).");
  }
  else {
    logAction( $logAction, "Ticket template (Name: $input->TicketTemplateName) was successfully deleted." );
    http_response_code( 204 );
  }
	
  sqlsrv_free_stmt( $sqlData );
  sqlsrv_close( $databaseConnection );
}

function handleError( $msg ) {
  logAction( $logAction, $msg );
  http_response_code(500);
  die();
}