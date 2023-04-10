<?php

header("Access-Control-Allow-Origin: *");
session_start();

/**
 * addTicket.php
 * adds a ticket to an event in the system.
 * 
 * @param ticket the ticket obj
 */

// ---------------------------------------------------
// Workflow of Ticket Creation
//
// 1) Create a record in the tblTicketsMaster from the JSON passed in 
//    from the first JSON record and capture the created batch number.
//
// 2) Add record to tblTicketDetail
//
// 3) Send admin notification that tickets were created.
// ---------------------------------------------------

include 'databaseConstants.php';
include_once 'addLogMessage.php';
$logAction = "ADD TICKET";
$input = json_decode(file_get_contents('php://input', true));

// Connect to database
$databaseOptions = array("UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) {
  logAction($logAction, "Unable to connect to database.");
  http_response_code(500);
  die();
}
// Input parameter check
else if (!isset($input->AssociatedEventNumber)) {
  logAction($logAction, "Input parameters invalid.");
  http_response_code(422);
  die();
} else {

  $logMessage = "User is adding a new ticket for Event # " . $input->AssociatedEventNumber . "";
  $createdBy = $_SESSION["user"];
  $createdDate = date_format(new DateTime(), 'Y-m-d H:i:s');

  // [1] Insert Record in tblTicketsMaster

  $sqlStatement  .= "INSERT INTO [" . DB_TABLE_TICKET_MASTER . "] ";
  $sqlStatement .= "( ";
  $sqlStatement .= "TicketBatchStatus, ";
  $sqlStatement .= "CreatedBy, ";
  $sqlStatement .= "CreatedDateTime, ";
  $sqlStatement .= "AvailableTo, ";
  $sqlStatement .= "AutoApprove, ";
  $sqlStatement .= "AssociatedEventNumber,";
  $sqlStatement .= "AssociatedVenueNumber,";
  $sqlStatement .= "AssociatedTicketCategory,";
  $sqlStatement .= "AssociatedSeatType,";
  $sqlStatement .= "FoodOrdering";
  $sqlStatement .= ") ";
  $sqlStatement .= "VALUES ( ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->TicketBatchStatus . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $createdBy . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $createdDate . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AvailableTo . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AutoApprove . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedEventNumber . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedVenueNumber . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedTicketCategory . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->AssociatedSeatType . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->FoodOrdering . DB_SINGLE_QUOTE;
  $sqlStatement .= "); ";

  $sqlStatement .= "SELECT SCOPE_IDENTITY() AS addedBatchNumber; ";

  $sqlData = sqlsrv_query($databaseConnection, $sqlStatement);

  // Error checking in the SQL processing
  if ($sqlData === false) {
    if (($errors = sqlsrv_errors()) != null) {
      foreach ($errors as $error) {
        logAction($logAction, "SQLSTATE: " . $error['SQLSTATE'] . "<br />");
        logAction($logAction, "code: " . $error['code'] . "<br />");
        logAction($logAction, "message: " . $error['message'] . "<br />");
      }
    }
    handleError($logAction, "Error while creating new ticket in MASTER table for Event # " . $input->AssociatedEventNumber);
  } else {
    
    $workBatchNumber = getAddedBatchNumber($sqlData);
    logAction($locAction, "User added Batch # " . $workBatchNumber . " new ticket for Event # " . $input->AssociatedEventNumber . "");

    // [2] Insert tblTicketsDetail records

    $sqlStatement  = "INSERT INTO [" . DB_TABLE_TICKET_DETAIL . "] ";
    $sqlStatement .= "( ";
    $sqlStatement .= "AssociatedTicketBatchNumber, ";
    $sqlStatement .= "TicketDeliveryType,";
    $sqlStatement .= "Status, ";
    $sqlStatement .= "CreatedBy, ";
    $sqlStatement .= "CreatedDateTime, ";
    $sqlStatement .= "Section, ";
    $sqlStatement .= "Row,";
    $sqlStatement .= "Seat,";
    $sqlStatement .= "FoodOrdering ";
    $sqlStatement .= ") ";
    $sqlStatement .= "VALUES ( ";
    $sqlStatement .= $workBatchNumber . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->TicketDeliveryType . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->TicketStatus . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $createdBy . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $createdDate . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->Section . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->Row . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->Seat . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $input->FoodOrdering . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "); ";

    // Execute the insert
    $sqlData = sqlsrv_query($databaseConnection, $sqlStatement);

    // Error checking in the SQL processing
    if ($sqlData === false) {
      if (($errors = sqlsrv_errors()) != null) {
        foreach ($errors as $error) {
          logAction($logAction, "SQLSTATE: " . $error['SQLSTATE'] . "<br />");
          logAction($logAction, "code: " . $error['code'] . "<br />");
          logAction($logAction, "message: " . $error['message'] . "<br />");
        }
      }
      handleError($logAction, "Error while creating ticket " . $workBatchNumber . " for Event # " . $input->AssociatedEventNumber);
    } else {

      // Notification Emails
      $argumentEventNumber = $input->AssociatedEventNumber;
      $argumentMailTo = 'jbucar@walterhav.com,sschultheis@walterhav.com';

      include 'sendEmailNotification.php';
      sendEmailTicketsCreatedForEvent($argumentMailTo, $workBatchNumber);

      logAction($locAction, "User FINISHED adding a new ticket for Event # " . $input->AssociatedEventNumber);
      http_response_code(201);
    }
  }

  sqlsrv_free_stmt($sqlData);
  sqlsrv_close($databaseConnection);
}

function handleError($logAction, $msg)
{
  logAction($logAction, $msg);
  http_response_code(500);
  die();
}

function getAddedBatchNumber($queryID)
{
  sqlsrv_next_result($queryID);
  sqlsrv_fetch($queryID);
  return sqlsrv_get_field($queryID, 0);
}
