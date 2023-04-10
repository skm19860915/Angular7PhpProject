<?php

header("Access-Control-Allow-Origin: *");
session_start();

// addSpecialTicketRequestReply.php
// Used to add a special ticket request reply in the system.
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
$logAction = "ADD SPECIAL REQUEST REPLY";

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

  $replyDate = date_format( new DateTime(), 'Y-m-d H:i:s' );
  $formatReplyContent = str_replace( "'", "''", $input->ReplyText );

  if ( $input->ReplyBy <> $input->ReplyCreatedBy ) {	
    $formatReplyHeader = "[" . $replyDate . "] [REPLY] By " . $input->ReplyCreatedBy . " On Behalf Of " . $input->ReplyBy;	
  }
  else {	
    $formatReplyHeader = "[" . $replyDate . "] [REPLY] By " . $input->ReplyBy;
  }

  //  Transaction 
  $sqlStatement  = "BEGIN TRAN ";
   
  // Update Action
  $sqlStatement .= "UPDATE ";
        
  // Update Source
  $sqlStatement .= "[" . DB_TABLE_SPECIAL_TICKET_REQUESTS . "] ";
        
  // Set
  $sqlStatement .= "SET ";
            
  // Set Columns 
  $sqlStatement .= "[RequestDescription] = CONCAT([RequestDescription], CHAR(13) + CHAR(10) + ". DB_SINGLE_QUOTE . $formatReplyHeader . DB_SINGLE_QUOTE . " + CHAR(13) + CHAR(10) ";
  $sqlStatement .= " + ". DB_SINGLE_QUOTE . $formatReplyContent . DB_SINGLE_QUOTE . " + CHAR(13) + CHAR(10) ) ";
			
  // Set Where
  $sqlStatement .= "WHERE [SpecialRequestNumber]=" . $input->SpecialRequestNumber  . "; ";
            
  // Go ahead and commit the transaction
  $sqlStatement .= "COMMIT TRAN";
     
  // Execute the insert
	$sqlData = sqlsrv_query( $databaseConnection, $sqlStatement );
 
  // Error checking in the SQL processing
  if( $sqlData === false ) { 
    logAction( $logAction, "Error while adding special ticket request reply.");
    http_response_code( 500 );
  }
  else {
    logAction( $logAction, "Special ticket request reply was successfully created." );
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

    $json_mailNotice = json_encode($mailNotice);

	$logMessage = "Starting Call to SendEmailNotification - sendEmailEventCreated Function";
        logAction("ADD EVENT", $logMessage);
		   
    sendEmailEventCreated($json_mailNotice);
    
	$logMessage = "Finished Call to SendEmailNotification - sendEmailEventCreated Function";
        logAction("ADD EVENT", $logMessage);
	*/
	
	sqlsrv_free_stmt( $sqlData );
  sqlsrv_close( $databaseConnection );
}