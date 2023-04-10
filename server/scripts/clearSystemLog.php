<?php

header("Access-Control-Allow-Origin: *");
session_start();

// clearSystemLog.php
//		Clears the system log.
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

include_once('databaseConstants.php');
include_once('addLogMessage.php');

$requestingUser = $_SESSION["user"];

$logMessage = "User " . $requestingUser . " is requesting to clear the system log.";
    logAction("CLEAR SYSTEM LOG", $logMessage);

$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $sqlConnection = sqlsrv_connect(DB_SERVERNAME, $options);

/* If no connection, exit and return error 500 */

if ($sqlConnection === false)
    {
        $logMessage = "User " . $requestingUser . " encountered a SQL connection error clearing the system log.";
        logAction("CLEAR SYSTEM LOG", $logMessage);
        die(500);
    }

/* SQL Statement */

$sqlStatement = "DELETE * FROM " . DB_TABLE_LOGGING . "";
    $sqlStatementStatus = sqlsrv_query( $sqlConnection, $sqlStatement );

// If an error, lets stop the script
if( $sqlStatementStatus === false )
    {
        $logMessage = "User " . $requestingUser . " encountered a SQL execution error while clearing the system log.";
        logAction("CLEAR SYSTEM LOG", $logMessage);
        die(500);
    }

/* Process results */
$json = array();

do {
     while ($row = sqlsrv_fetch_array($sqlStatementStatus, SQLSRV_FETCH_ASSOC)) {
     $json[] = $row;     
     }
} while ( sqlsrv_next_result($sqlStatementStatus) );

/* Encode Jason */
header ( "Content-type: application/json;" );
  echo json_encode ( $json );

  $logMessage = "User " . $requestingUser . " cleared the system log.";
  logAction("CLEAR SYSTEM LOG", $logMessage);
  
/* Clean up */
sqlsrv_free_stmt($sqlStatement);
sqlsrv_close($sqlConnection);

?>