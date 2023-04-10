<?php
header("Access-Control-Allow-Origin: *");

// getAllTicketTemplates.php
// Used to return a list of all ticket template tickets
//
// Walter | Haverfield
// 
// Author:  Samuel Leicht
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 07/01/2019       Samuel Leicht       Initial creation

include 'databaseConstants.php';
include_once('addLogMessage.php');
$logAction = 'GET TICKET TEMPLATE TICKETS';

$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$con = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate
if ( $con === false ) {
  handleError( "Error while accessing the database. ");
}
else {

  $sql = "SELECT * FROM ";
  $sql .= DB_TABLE_TICKET_TEMPLATE_TICKETS;
  
  $sqlRes = sqlsrv_query( $con, $sql );

  if( ! $sqlRes ) {
    handleError( "Error while accessing the database: " . sqlsrv_errors() );
  }
  else {

    $jsonRes = array();

    do {
      while( $row = sqlsrv_fetch_array( $sqlRes, SQLSRV_FETCH_ASSOC ) ) {
        $jsonRes[] = $row;
      }
    } while ( sqlsrv_next_result($sqlRes));

    header ( "Content-type: application/json;" );
    echo json_encode ($jsonRes);
  }

  sqlsrv_free_stmt ($sqlRes);
  sqlsrv_close ($con);
}

function handleError( $msg ) {
  logAction( $logAction, $msg );
  http_response_code(500);
  die();
}