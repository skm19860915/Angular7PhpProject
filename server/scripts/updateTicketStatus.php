<?php

header("Access-Control-Allow-Origin: *");

// updateTicketStatus.php
//	  Updates the Ticket Status to the specified value.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 3/9/2018         JAB                 Initial creation

include 'databaseConstants.php';
include 'addLogMessage.php';

$json = json_decode(file_get_contents('php://input',true));

$argumentTicketRecordNumber = $json->TicketRecordNumber;
$argumentNewStatus =  $json->Status;

// Check to make sure arguments have been passed in, if not error out.

if ($argumentTicketRecordNumber == '')	
{ die(500);	}

if ($argumentNewStatus == '')	
{	die(500);	}

// Connect to database
  $databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
  $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
  if ($databaseConnection === false) 
    die(500);

// Update Ticket Status

$logMessage = "Updating Ticket Status for Ticket # " . $argumentTicketRecordNumber . " to  " . $argumentNewStatus  . "";
  logAction("UPDATE TICKET STATUS", $logMessage);

// Action and Table/View
$sqlStatement  = "BEGIN TRAN ";
  $sqlStatement .= "SELECT [Status] FROM " . DB_TABLE_TICKET_DETAIL . " WITH (UPDLOCK) ";
  $sqlStatement .= "WHERE [TicketRecordNumber]=" . $argumentTicketRecordNumber  . "; ";
  
  $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_DETAIL . " SET [Status]=" . DB_SINGLE_QUOTE . $argumentNewStatus . DB_SINGLE_QUOTE . " ";
  $sqlStatement .= "WHERE [TicketRecordNumber]=" . $argumentTicketRecordNumber  . "; ";

  $sqlStatement .= "COMMIT TRAN";

// Execute the insert
$sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

if( $sqlData === false )
  {
      $logMessage = "ERROR: Updating Ticket Status for Ticket # " . $argumentTicketRecordNumber . " to  " . $argumentNewStatus  . "";
        logAction("UPDATE TICKET STATUS", $logMessage);
      sqlsrv_free_stmt( $sqlData) ;
      sqlsrv_close($databaseConnection);
      die(500);
  }

$logMessage = "Updated Ticket Status for Ticket # " . $argumentTicketRecordNumber . " to  " . $argumentNewStatus  . "";
  logAction("UPDATE TICKET STATUS", $logMessage);
  
sqlsrv_free_stmt( $sqlData);
sqlsrv_close($databaseConnection);

?>
