<?php
header("Access-Control-Allow-Origin: *");

// getTicketRequestsForTicket.php
// Used to return a list of all the ticket requests for a specific ticket
//
// Walter | Haverfield
// 
// Author:  Samuel Leicht
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 04/28/2019       Samuel Leicht       Initial creation

include 'databaseConstants.php';
include_once('addLogMessage.php');
$logAction = 'GET TICKET REQUESTS FOR TICKET';

function getTicketRequestsForTicket( $ticketRecordNumber ) {
  
  $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
  $con = sqlsrv_connect(DB_SERVERNAME, $options);
  
  // If we cannot get a connection lets terminate.
  if ( $con === false ) {
    handleError( "Error while accessing the database. ");
  }
  else {
  
    $sql = "SELECT RequestNumber FROM ";
    $sql .= DB_TABLE_TICKET_REQUESTS;
    $sql .= " WHERE AssociatedTicketRecordNumber = '". $ticketRecordNumber ."'";
    $sql .= " AND Status != 'CANCELLED'";
    $sqlRes = sqlsrv_query( $con, $sql );

    if( ! $sqlRes ) {
      handleError( "Error while accessing the database: " . sqlsrv_errors() );
    }
    else {

      $res = array();

      while( $row = sqlsrv_fetch_array( $sqlRes, SQLSRV_FETCH_ASSOC ) ) {
        array_push( $res, $row );
      }

      sqlsrv_free_stmt( $sqlRes );

      return $res;
    }
  }
}

function handleError( $msg ) {
  logAction( $logAction, $msg );
  http_response_code(500);
  die();
}