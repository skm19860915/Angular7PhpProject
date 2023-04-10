<?php

header("Access-Control-Allow-Origin: *");
session_start();

// deleteTicket.php
//		Used to delete ticket information in the system.
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
include_once 'addLogMessage.php';


// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
    die(500);

$json = json_decode(file_get_contents('php://input',true));

// ---------------------------------------------------
// Workflow of Ticket Creation
//
// 1) Create a record in the tblTicketsMaster from the JSON passed in 
//    from the first JSON record and capture the created batch number.
//
// 2) Add record to tblTicketDetail
//
// 3) Send admin notification that tickets were created.
// ----------------------------------------------------

$logMessage = "User is deleting tblTicketMaster: Batch # " . $json->TicketBatchNumber . " / Event # " . $json->AssociatedEventNumber . "";
    logAction("DELETE TICKET", $logMessage);

$modifiedBy = $_SESSION["user"];

    if (empty($modifiedBy))
        {   $modifiedBy="jbucar@walterhav.com"; }


$modifiedDate = date_format(new DateTime(), 'Y-m-d H:i:s');

// [1] Update Record in tblTicketsMaster

    // Start Transaction
    $sqlStatement  = "BEGIN TRAN ";

    // Action
    $sqlStatement  .= "DELETE FROM [" . DB_TABLE_TICKET_MASTER . "] ";
    
     // Select the record to delete
     $sqlStatement .= "WHERE ( ";
        $sqlStatement .= "TicketBatchNumber = " . $json->TicketBatchNumber . " ";
     $sqlStatement .= "); "; 
    
     // Commit Transaction
     $sqlStatement .= "COMMIT TRAN";
     
        $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
        
        // Error checking in the SQL processing
        if( $sqlData === false ) 
            {
                // echo $sqlStatement;
                $logMessage = "SQL ERROR deleting tblTicketMaster: Batch # " . $json->TicketBatchNumber . " / Event # " . $json->AssociatedEventNumber . "";
                    logAction("DELETE TICKET DETAIL ERROR", $logMessage);
                die(500);
            }
       
         $logMessage = "User successfully deleted tblTicketMaster: Batch # " . $json->TicketBatchNumber . " / Event # " . $json->AssociatedEventNumber . "";
            logAction("DELETE TICKET", $logMessage);
        
// [2] Delete tblTicketsDetail record

    $logMessage = "User is delete tblTicketDetail: Ticket # " . $json->TicketRecordNumber . " / Event # " . $json->AssociatedEventNumber . "";
        logAction("DELETE TICKET DETAIL", $logMessage);
  
    // Start Transaction
    $sqlStatement  = "BEGIN TRAN ";
  
        // Action
        $sqlStatement  .= "DELETE FROM [" . DB_TABLE_TICKET_DETAIL . "] ";
      
        // Select the record to delete
        $sqlStatement .= "WHERE ( ";
        $sqlStatement .= "TicketRecordNumber = " . $json->TicketRecordNumber . " ";
        $sqlStatement .= "); "; 
     
       // Commit Transaction
       $sqlStatement .= "COMMIT TRAN";
       
            // Execute SQL
            $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
   
            // Error checking in the SQL processing
            if( $sqlData === false ) 
            {
                // echo $sqlStatement;
                $logMessage = "SQL ERROR deleting tblTicketDetail Ticket # / " . $json->TicketRecordNumber . " / Event # " . $json->AssociatedEventNumber . "";
                    logAction("DELETE TICKET DETAIL ERROR", $logMessage);
                die(500);
            }
    
        $logMessage = "User successfully deleted tblTicketDetail Ticket # / " . $json->TicketRecordNumber . " / Event # " . $json->AssociatedEventNumber . "";
            logAction("DELETE TICKET DETAIL", $logMessage);

    include 'sendEmailNotification.php';
            // sendEmailTicketsUpdated($mailNoticejson);             
        
    sqlsrv_free_stmt( $sqlData);
    sqlsrv_close($databaseConnection);

?>
