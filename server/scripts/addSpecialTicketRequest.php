<?php

header("Access-Control-Allow-Origin: *");
session_start();

// addSpecialTicketRequest.php
// Used to add an special ticket request in the system.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 04/26/2019       Samuel Leicht       Bugfix special characters in description and title
// 03/09/2018       JAB                 Initial creation

include_once('databaseConstants.php');
include_once('mailConstants.php');
include_once('addLogMessage.php');
include_once('sendEmailNotification.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD SPECIAL REQUEST";

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

  $requestor = 'USER NOT SET';

  if( isset( $_SESSION["user"] ) ) {
    $requestor = $_SESSION["user"];  
  }

  $createdDate = date_format(new DateTime(), 'Y-m-d H:i:s');

  $formatRequestDescription = "[" . $createdDate . "] For ";
  $formatRequestDescription .= $input->RequestedBy . " By " . $requestor . ":\n";
  $formatRequestDescription .= str_replace( "'", "''", $input->RequestDescription );

  $formattedRequestTitle = str_replace( "'", "''", $input->RequestTitle );

  logAction( $logAction, "Starting Special Ticket Request Creation (Title: $formattedRequestTitle).");

  // Start Transaction
  $sqlStatement  = "BEGIN TRAN ";

  // Action
  $sqlStatement  .= "INSERT INTO [" . DB_TABLE_SPECIAL_TICKET_REQUESTS . "] ";
  
  // Columns
  $sqlStatement .= "( ";
  $sqlStatement .= "RequestedBy, ";
  $sqlStatement .= "RequestDateTime, ";
  $sqlStatement .= "CreatedBy, ";
  $sqlStatement .= "RequestTitle, ";
  $sqlStatement .= "RequestDescription, "; 
  $sqlStatement .= "Status ";
  $sqlStatement .= ") ";
		
  // Values
  $beginningStatus = "SUBMITTED";
	 
	$sqlStatement .= "VALUES ( ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->RequestedBy . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $createdDate . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->CreatedBy . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $formattedRequestTitle . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $formatRequestDescription . DB_SINGLE_QUOTE . " + CHAR(13) + CHAR(10), ";
  $sqlStatement .= DB_SINGLE_QUOTE . $beginningStatus . DB_SINGLE_QUOTE . " ";
  $sqlStatement .= "); "; 

  // Commit Transaction
  $sqlStatement .= "COMMIT TRAN";
     
  // Execute the insert
  $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
  // Error checking in the SQL processing
  if( $sqlData === false ) { 
    logAction( $logAction, "Error while creating special ticket request (Title: $formattedRequestTitle).");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Special ticket request (Title: $formattedRequestTitle) was successfully created." );
    http_response_code( 201 );
  }

    // Mail Notification stuff
        //  $input->MailFrom, $input->ReplyTo,  $input->MailTo, $input->Subject, $input->MessageBody
     
    /* $htmlMessageBody = "<b>Email Notification from Walter | Haverfield Event Ticketing System</b></br></br>";
        $htmlMessageBody .= "<b>The following event has been added to the system:</b></br></br>";
        $htmlMessageBody .= "<b>Event Name: </b>" . $input->EventDescription . "</br>";
        $htmlMessageBody .= "<b>Event Date: </b>" . $input->EventDateTime . "</br>";
        $htmlMessageBody .= "<b>Ticket Category: </b>" . $input->AssociatedTicketCategory . "</br>";
        $htmlMessageBody .= "<b>Created By: </b>" . $_SESSION["user"] . "</br></br>";
        $htmlMessageBody .= "<b>Event Notes: </b>" . $input->EventNotes . "</br></br>";
        $htmlMessageBody .= "<b>Link To Internal Ticketing System: </b></br>";
        $htmlMessageBody .= "&#10233; <a href='https://ticketing.walterhav.com' target='_blank'>https://ticketing.walterhav.com</a></br></br>";
        
    $mailNotice = array(    'MailFrom' => ETS_EMAIL_DEFAULT_FROM_ADDRESS, 
                            'ReplyTo' => ETS_EMAIL_DEFAULT_FROM_ADDRESS,
                            'MailTo' => 'jbucar@walterhav.com',
                            'Subject' => 'New Event Created for ' . $input->EventDescription . ' @ ' . $input->EventDateTime . '',
                            'MessageBody' => $htmlMessageBody
                        );

    $input_mailNotice = input_encode($mailNotice);

	$logMessage = "Starting Call to SendEmailNotification - sendEmailEventCreated Function";
        logAction("ADD EVENT", $logMessage);
		   
    sendEmailEventCreated($input_mailNotice);
    
	$logMessage = "Finished Call to SendEmailNotification - sendEmailEventCreated Function";
        logAction("ADD EVENT", $logMessage);
	*/
	
    sqlsrv_free_stmt( $sqlData );
    sqlsrv_close( $databaseConnection );
}
