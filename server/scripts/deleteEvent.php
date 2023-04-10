<?php

header("Access-Control-Allow-Origin: *");
session_start();


// deleteEvent.php
//	  Deletes the passed in Event record
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
    
 // Connect to database
  $databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
  $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
  if ($databaseConnection === false) 
    die(500);

$logMessage = "Deleting Event # " . $json->EventNumber . " " . $json->EventDescription . " by " . $_SESSION["user"] . "";
    logAction("DELETE EVENT", $logMessage);

// DELETE Event Information

    //  Transaction 
    $sqlStatement  = "BEGIN TRAN ";
   
    // Select Action
    $sqlStatement .= "DELETE FROM [". DB_TABLE_EVENTS ."] ";
        
    // Select the record to delete
    $sqlStatement .= "WHERE ( ";
    $sqlStatement .= "EventNumber = " . $json->EventNumber . " ";
    $sqlStatement .= "); "; 
 
    // Commit Transaction
    $sqlStatement .= "COMMIT TRAN";
    
    // Execute the update
    $sqlData = sqlsrv_query($databaseConnection, $sqlStatement);
    
    // Error Handling
    if( $sqlData === false )
    {
        $logMessage = "SQL Error for Delete Event # " . $json->EventNumber . " " . $json->EventDescription . "";
            logAction("DELETE EVENT ERROR", $logMessage);    
    
        sqlsrv_close($databaseConnection);
        die(500);
    }

$logMessage = "Deleted Event # " . $json->EventNumber . " " . $json->EventDescription . "";
    logAction("DELETE EVENT", $logMessage);    

// sqlsrv_free_stmt( $sqlStatement );
sqlsrv_close( $databaseConnection );

?>