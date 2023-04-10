<?php

header("Access-Control-Allow-Origin: *");
session_start();

// cancelSpecialTicketRequest.php
//		Used to cancel an special ticket request in the system.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 04/26/2018        Samuel Leicht       Clean Up
// 03/09/2018         JAB                 Initial creation

include_once('databaseConstants.php');
include_once('mailConstants.php');
include_once('addLogMessage.php');
include_once('sendEmailNotification.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
    die(500);

$json = json_decode(file_get_contents('php://input',true));

$logMessage = "Starting Special Ticket Request Cancel";
    logAction("CANCEL SPECIAL REQUEST", $logMessage);

// Update the status on the request to cancel it.

    //  Transaction 
    $sqlStatement  = "BEGIN TRAN ";
   
        // Update Action
            $sqlStatement .= "UPDATE ";
        
            // Update Source
            $sqlStatement .= "[" . DB_TABLE_SPECIAL_TICKET_REQUESTS . "] ";
        
        // Set
            $sqlStatement .= "SET ";
            
            // Set Columns 
			$sqlStatement .= "[Status] = 'CANCELLED' ";
				
            // Set Where
            $sqlStatement .= "WHERE [SpecialRequestNumber]=" . $json->SpecialRequestNumber  . "; ";
            
    // Go ahead and commit the transaction
    $sqlStatement .= "COMMIT TRAN";
     
    // Execute the insert
	$sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
     // Error checking in the SQL processing
 
     if( $sqlData === false ) 
		{
			die(500);
        }
   
    // Notification Emails
   
    $logMessage = "Cancelled Special Ticket Request";
        logAction("SPECIAL REQUEST CANCEL", $logMessage);

    // Mail Notification stuff
        //  $json->MailFrom, $json->ReplyTo,  $json->MailTo, $json->Subject, $json->MessageBody
     
    /* $htmlMessageBody = "<b>Email Notification from Walter | Haverfield Event Ticketing System</b></br></br>";
        $htmlMessageBody .= "<b>The following event has been added to the system:</b></br></br>";
        $htmlMessageBody .= "<b>Event Name: </b>" . $json->EventDescription . "</br>";
        $htmlMessageBody .= "<b>Event Date: </b>" . $json->EventDateTime . "</br>";
        $htmlMessageBody .= "<b>Ticket Category: </b>" . $json->AssociatedTicketCategory . "</br>";
        $htmlMessageBody .= "<b>Created By: </b>" . $_SESSION["user"] . "</br></br>";
        $htmlMessageBody .= "<b>Event Notes: </b>" . $json->EventNotes . "</br></br>";
        $htmlMessageBody .= "<b>Link To Internal Ticketing System: </b></br>";
        $htmlMessageBody .= "&#10233; <a href='https://ticketing.walterhav.com' target='_blank'>https://ticketing.walterhav.com</a></br></br>";
        
    $mailNotice = array(    'MailFrom' => ETS_EMAIL_DEFAULT_FROM_ADDRESS, 
                            'ReplyTo' => ETS_EMAIL_DEFAULT_FROM_ADDRESS,
                            'MailTo' => 'jbucar@walterhav.com',
                            'Subject' => 'New Event Created for ' . $json->EventDescription . ' @ ' . $json->EventDateTime . '',
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
?>
