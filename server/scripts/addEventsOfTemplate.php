<?php

header("Access-Control-Allow-Origin: *");

include_once('databaseConstants.php');
include_once('addLogMessage.php');

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
    die(500);

$json = json_decode(file_get_contents('php://input',true));

$logMessage = "Starting Event Add For " . $json->TitleDescription . "";
    logAction("ADD EVENT", $logMessage);

    // Start Transaction
    $sqlStatement  = "BEGIN TRAN ";

    // Action
    $sqlStatement  .= "INSERT INTO [" . DB_TABLE_EVENTS . "] ";
    
    // Columns
    $sqlStatement .= "( ";
    $sqlStatement .= "EventDescription, ";
    $sqlStatement .= "EventDateTime, ";
    $sqlStatement .= "EventNotes, ";
    $sqlStatement .= "AssociatedTicketCategory,";
    $sqlStatement .= "AssociatedVenueNumber,";
    $sqlStatement .= "Status, ";
    $sqlStatement .= "CreatedDateTime, ";
    $sqlStatement .= "CreatedBy, ";
    $sqlStatement .= "ModifiedDateTime,";
    $sqlStatement .= "ModifiedBy";
    $sqlStatement .= ") ";
    
    // Values
    $sqlStatement .= "VALUES ( ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->TitleDescription . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->DateAndTime . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->EventNotes . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->RelativeCategory . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= $json->RelativeVenueNumber . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->Status . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->CreatedDateTime . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->CreatedBy . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->ModifiedDateTime . DB_SINGLE_QUOTE . ", ";
    $sqlStatement .= DB_SINGLE_QUOTE . $json->ModifiedBy . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "); "; 

    // Commit Transaction
    $sqlStatement .= "COMMIT TRAN";
     
    // Execute the insert
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    // Error checking in the SQL processing
    if( $sqlData === false ) 
    {
        die(500);
    }

    /* Process results */
    $json = array();

    do 
    {
        while ($row = sqlsrv_fetch_array($sqlData, SQLSRV_FETCH_ASSOC))
            {
                $json[] = $row;     
            }
    }   while ( sqlsrv_next_result($sqlData) );
           
    // Notification Emails
   
    $logMessage = "Added Event " . $json->TitleDescription . "";
        logAction("ADD EVENT", $logMessage);

    $argumentMailTo = 'jbucar@walterhav.com';
        
        include 'sendEmailNotification.php';
            // sendEmailEventCreated($argumentMailTo, $argumentEventNumber);    
        
    sqlsrv_free_stmt( $sqlData);
    sqlsrv_close($databaseConnection);
?>
