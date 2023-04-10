<?php

header("Access-Control-Allow-Origin: *");
session_start();

/**
 * addTicketTemplate.php
 * Used to add a ticket template in the system
 * 
 * @param TicketTemplate template object (TicketTemplateName is a must)
 */

include_once('databaseConstants.php');
include_once('mailConstants.php');
include_once('addLogMessage.php');
// include_once('sendEmailNotification.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD TICKET TEMPLATE";

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

  logAction( $logAction, "Starting Ticket Template Creation (Name: $input->TicketTemplateName).");

  $sqlStatement  .= "INSERT INTO [" . DB_TABLE_TICKET_TEMPLATES . "] ";
  $sqlStatement .= "( ";
  $sqlStatement .= "TicketTemplateName";
  if (isset( $input->AssociatedVenueNumber ) ) {
    $sqlStatement .= ",";
    $sqlStatement .= "AssociatedVenueNumber";
  }
  if (isset( $input->AssociatedTicketCategory ) ) {
    $sqlStatement .= ",";
    $sqlStatement .= "AssociatedTicketCategory";
  }
  $sqlStatement .= ") ";
  
  // Values
  $sqlStatement .= "VALUES ( ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->TicketTemplateName . DB_SINGLE_QUOTE;
  if (isset( $input->AssociatedVenueNumber ) ) {
    $sqlStatement .= ",";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedVenueNumber . DB_SINGLE_QUOTE;
  }
  if (isset( $input->AssociatedTicketCategory ) ) {
    $sqlStatement .= ",";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedTicketCategory . DB_SINGLE_QUOTE;
  }
  
  $sqlStatement .= "); ";
     
  // Execute the insert
  $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
  // Error checking in the SQL processing
  if( $sqlData === false ) { 
    if ( ($errors = sqlsrv_errors() ) != null) {
      foreach( $errors as $error ) {
        logAction( $logAction, "SQLSTATE: ".$error[ 'SQLSTATE']."<br />");
        logAction( $logAction, "code: ".$error[ 'code']."<br />");
        logAction( $logAction, "message: ".$error[ 'message']."<br />");
      }
    }
    handleError("Error while creating ticket template (Name: $input->TicketTemplateName).");
  }
  else {

    $sqlStatement = "";

    // Continue with ticket creation
    foreach ($input->tickets as $ticket) {
      
      $sqlStatement .= "INSERT INTO [" . DB_TABLE_TICKET_TEMPLATE_TICKETS . "] ";
      $sqlStatement .= "( ";
      $sqlStatement .= "AssociatedTemplateName";
      if (isset( $ticket->AvailableTo ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "AvailableTo";
      }
      if (isset( $ticket->AssociatedDeliveryType ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "AssociatedDeliveryType";
      }
      if (isset( $ticket->AutoApprove ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "AutoApprove";
      }
      if (isset( $ticket->AssociatedSeatType ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "AssociatedSeatType";
      }
      if (isset( $ticket->Section ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "Section";
      }
      if (isset( $ticket->Row ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "Row";
      }
      if (isset( $ticket->Seat ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= "Seat";
      }
      $sqlStatement .= ") ";

      $sqlStatement .= "VALUES ( ";

      $sqlStatement .= DB_SINGLE_QUOTE . $input->TicketTemplateName . DB_SINGLE_QUOTE;
      if (isset( $ticket->AvailableTo ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->AvailableTo . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->AssociatedDeliveryType ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->AssociatedDeliveryType . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->AutoApprove ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->AutoApprove . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->AssociatedSeatType ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->AssociatedSeatType . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->Section ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->Section . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->Row ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->Row . DB_SINGLE_QUOTE;
      }
      if (isset( $ticket->Seat ) ) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $ticket->Seat . DB_SINGLE_QUOTE;
      }

      $sqlStatement .= "); ";
    }

    // Execute the insert
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
  
    // Error checking in the SQL processing
    if( $sqlData === false ) { 
      handleError("Error while creating ticket template (Name: $input->TicketTemplateName).");
    }
    else {
      logAction( $logAction, "Ticket template (Name: $input->TicketTemplateName) was successfully created." );
      http_response_code( 201 );
    }
	
    sqlsrv_free_stmt( $sqlData );
    sqlsrv_close( $databaseConnection );
  }
}

function handleError( $msg ) {
  logAction( $logAction, $msg );
  http_response_code(500);
  die();
}