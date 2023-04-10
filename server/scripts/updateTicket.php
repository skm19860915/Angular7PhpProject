<?php

header("Access-Control-Allow-Origin: *");
session_start();

/**
 * updateTicket.php
 * updates an existing ticket in the system.
 * 
 * @param ticket the ticket obj
 */

include 'databaseConstants.php';  
include_once 'addLogMessage.php';
$logAction = "UPDATE TICKET";
$input = json_decode(file_get_contents('php://input', true));

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) {
  logAction($logAction, "Unable to connect to database.");
  http_response_code(500);
  die();
}
// Input parameter check
else if (!isset($input->TicketBatchNumber)) {
  logAction($logAction, "Input parameters invalid.");
  http_response_code(422);
  die();
} else {

  logAction($logAction, "User is updating tblTicketMaster: Batch # " . $input->TicketBatchNumber . " / Event # " . $input->AssociatedEventNumber);

  $modifiedBy = $_SESSION["user"];

  if (empty($modifiedBy)) {   
    $modifiedBy="jbucar@walterhav.com"; 
  }

  $modifiedDate = date_format(new DateTime(), 'Y-m-d H:i:s');

  // [1] Update Record in tblTicketsMaster
  $sqlStatement  = "UPDATE [" . DB_TABLE_TICKET_MASTER . "] ";
  $sqlStatement .= "SET ";
  $sqlStatement .= "TicketBatchStatus = " . DB_SINGLE_QUOTE . $input->TicketBatchStatus . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "ModifiedBy = " . DB_SINGLE_QUOTE . $modifiedBy . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "ModifiedDateTime = " . DB_SINGLE_QUOTE . $modifiedDate . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "AvailableTo = " . DB_SINGLE_QUOTE . $input->AvailableTo . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "AutoApprove = " . DB_SINGLE_QUOTE . $input->AutoApprove . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "AssociatedEventNumber = " . $input->AssociatedEventNumber . ", ";
  $sqlStatement .= "AssociatedVenueNumber = " . $input->AssociatedVenueNumber . ", ";
  $sqlStatement .= "AssociatedTicketCategory = " . DB_SINGLE_QUOTE . $input->AssociatedTicketCategory . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "AssociatedSeatType = " . DB_SINGLE_QUOTE . $input->AssociatedSeatType . DB_SINGLE_QUOTE . ", ";
  $sqlStatement .= "FoodOrdering = " . DB_SINGLE_QUOTE . $input->FoodOrdering . DB_SINGLE_QUOTE . " ";
  $sqlStatement .= "WHERE ( ";
  $sqlStatement .= "TicketBatchNumber = " . $input->TicketBatchNumber . " ";
  $sqlStatement .= "); "; 
      
  $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
    
  // Error checking in the SQL processing
  if ($sqlData === false) {
    handleError($logAction, "Error while updating ticket (". $input->TicketBatchNumber ." in MASTER table for Event #" . $input->AssociatedEventNumber);
  } else {
       
    logAction($logAction, "User successfully updated tblTicketMaster: Batch #" . $input->TicketBatchNumber . " / Event #" . $input->AssociatedEventNumber);
            
    // [2] Update tblTicketsDetail record
      
    $sqlStatement  = "UPDATE [" . DB_TABLE_TICKET_DETAIL . "] ";
    $sqlStatement .= "SET ";
    $sqlStatement .= "Status = " . DB_SINGLE_QUOTE . $input->Status . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "ModifiedBy = " . DB_SINGLE_QUOTE . $modifiedBy . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "ModifiedDateTime = " . DB_SINGLE_QUOTE . $modifiedDate . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "TicketDeliveryType = " . DB_SINGLE_QUOTE . $input->TicketDeliveryType . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "Section = " . DB_SINGLE_QUOTE . $input->Section . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "Row = " . DB_SINGLE_QUOTE . $input->Row . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "Seat = " . DB_SINGLE_QUOTE . $input->Seat . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= "FoodOrdering = " . DB_SINGLE_QUOTE . $input->FoodOrdering . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "WHERE ( ";
    $sqlStatement .= "TicketRecordNumber = " . $input->TicketRecordNumber . " ";
    $sqlStatement .= "); ";
          
    // Execute the insert
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    // Error checking in the SQL processing
    if ($sqlData === false) {
      handleError($logAction, "Error while updating ticket (". $input->TicketBatchNumber ." in DETAIL table for Event #" . $input->AssociatedEventNumber);
    } else {
        
      logAction($logAction, "User successfully updated tblTicketDetail Ticket #" . $input->AssociatedTicketRecordNumber . " / Event #" . $input->AssociatedEventNumber);
      logAction("UPDATE TICKET DETAIL", $logMessage);

      // Mail Notification stuff
      //  $input->MailFrom, $input->ReplyTo,  $input->MailTo, $input->Subject, $input->MessageBody
          
      $htmlMessageBody = "<b>Automated Email Notification from Walter | Haverfield Events Ticketing System</b></br></br>";
      $htmlMessageBody .= "<b>The following ticket has been updated in the system:</b></br></br>";
      $htmlMessageBody .= "<b>For Event #: </b>" . $input->AssociatedEventNumber . "</br>";
      $htmlMessageBody .= "<b>Section: </b>" . $input->Section . "</br>";
      $htmlMessageBody .= "<b>Row: </b>" . $input->Row . "</br>";
      $htmlMessageBody .= "<b>Seat: </b>" . $input->Seat . "</br>";
      $htmlMessageBody .= "<b>Status: </b>" . $input->TicketBatchStatus . "</br>";
      $htmlMessageBody .= "<b>AutoApprove: </b>" . $input->AutoApprove . "</br>";
      $htmlMessageBody .= "<b>Available To Level: </b>" . $input->AvailableTo . "</br>";
      $htmlMessageBody .= "<b>Modified By: </b>" . $modifiedBy . "</br></br>";
      // $htmlMessageBody .= "<a href='https://ticketing.walterhav.com/v2' target='_blank'>https://ticketing.walterhav.com/v2</a></br></br>";
              
      $mailNotice = array('MailFrom' => ETS_EMAIL_DEFAULT_FROM_ADDRESS, 
                          'ReplyTo' => ETS_EMAIL_DEFAULT_FROM_ADDRESS,
                          'MailTo' => ETS_EMAIL_NOTIFICATION_ADDRESS . ',jabucar@aol.com',
                          'Subject' => 'New Ticket Created for Event #' . $input->AssociatedEventNumber . ' (Section: ' . $input->Section . ' Row: ' . $input->Row . ' Seat: ' . $input->Seat . ')',
                          'MessageBody' => $htmlMessageBody);

      $mailNoticejson = json_encode($mailNotice);

      include 'sendEmailNotification.php';
      sendEmailTicketsUpdated($mailNoticejson);             

      // Notification Emails
      logAction($logAction, "Updated ticket notice sent FROM " . ETS_EMAIL_DEFAULT_FROM_ADDRESS . " TO " . $createdBy);
    }
  }

  sqlsrv_free_stmt( $sqlData);
  sqlsrv_close($databaseConnection);
}