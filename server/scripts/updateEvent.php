<?php

header("Access-Control-Allow-Origin: *");
//error_reporting(E_ALL);
session_start();


// updateEvent.php
//	  Updates the passed in Event record
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

// Function shim for sending output to consol

//if (!function_exists('debug_to_console'))
//{
//    function debug_to_console( $data )
//    {
//        $output = $data;
//        if ( is_array( $output ) )
//            $output = implode( ',', $output)//;

//        echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
//    }
//}

$json = json_decode(file_get_contents('php://input',true));
    
    $newEventDate = (string)$json->updatedFormattedEventDateTime;
    
//$newEventDate = datetime::createfromformat('Y-m-d H:i:s',$json->formattedEventDateTime);
    // $newEventDate = date_format($json->formattedEventDateTime, 'Y-m-d H:i:s');

// Check to make sure arguments have been passed in, if not error out.

if ($json->EventNumber == '')	
{	echo "ERROR0001: No Event Number Specified."; die();	}

// Connect to database
  $databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
  $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
  if ($databaseConnection === false) 
    die(500);

$newModifiedDate = date_format(new DateTime(), 'Y-m-d H:i:s');
// $newEventDate = date(DATE_ISO8601, strtotime( $json->formattedEventDateTime ));

$logMessage = "Updating Event # " . $json->EventNumber . " " . $json->EventDescription . " @ " . $newModifiedDate . " by " . $_SESSION["user"] . "";
    logAction("UPDATE EVENT", $logMessage);

// Update Event Information

    //  Transaction 
    $sqlStatement  = "BEGIN TRAN ";

        // Select Action
    
        $sqlStatement .= "SELECT ";
        
         // Columns
            $sqlStatement .= "";
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
            $sqlStatement .= " ";
        
        // From
            $sqlStatement .= "FROM ";
                $sqlStatement .= "[" . DB_TABLE_EVENTS . "] WITH (UPDLOCK) ";
        
        // Where

            $sqlStatement .= "WHERE [EventNumber]=" . $json->EventNumber  . "; ";
  
        // Update Action
            $sqlStatement .= "UPDATE ";
        
            // Update Source
            $sqlStatement .= "[" . DB_TABLE_EVENTS . "] ";
        
        // Set
            $sqlStatement .= "SET ";
            
            // Set Columns 
            $sqlStatement .= "[EventDescription]="  . DB_SINGLE_QUOTE . $json->EventDescription . DB_SINGLE_QUOTE . ", ";
            // $sqlStatement .= "[EventDateTime]=" . DB_SINGLE_QUOTE . $json->formattedEventDateTime . DB_SINGLE_QUOTE ", ";
            $sqlStatement .= "[EventDateTime]=" . DB_SINGLE_QUOTE . $newEventDate . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= "[EventNotes]="  . DB_SINGLE_QUOTE . $json->EventNotes . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= "[AssociatedTicketCategory]="  . DB_SINGLE_QUOTE . $json->AssociatedTicketCategory . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= "[AssociatedVenueNumber]=" . $json->AssociatedVenueNumber . ", ";
            $sqlStatement .= "[Status]="  . DB_SINGLE_QUOTE . $json->Status . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= "[ModifiedDateTime]="  . DB_SINGLE_QUOTE . $newModifiedDate . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= "[ModifiedBy]="  . DB_SINGLE_QUOTE . $_SESSION["user"] . DB_SINGLE_QUOTE . " ";
            
            // Set Where
            $sqlStatement .= "WHERE [EventNumber]=" . $json->EventNumber  . "; ";
            
    // Go ahead and commit the transaction
    $sqlStatement .= "COMMIT TRAN";
        
    // Execute the update
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

    // Error Handling
    if( $sqlData === false )
    {
        $logMessage = "SQL Eerror for Updated Event # " . $json->EventNumber . " " . $json->EventDescription . "";
            logAction("UPDATE EVENT ERROR", $logMessage);    
    
        sqlsrv_close($databaseConnection);
        die(500);
    }

$logMessage = "Updated Event # " . $json->EventNumber . " " . $json->EventDescription . "";
    logAction("UPDATE EVENT", $logMessage);    

// sqlsrv_free_stmt( $sqlStatement );
sqlsrv_close( $databaseConnection );

?>