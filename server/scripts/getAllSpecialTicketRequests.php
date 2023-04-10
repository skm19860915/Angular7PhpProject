<?php
header("Access-Control-Allow-Origin: *");

// getAllSpecialTicketRequests.php
//		Used to return a list of all the speicla ticket requests in the system
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

$sqlConnectionOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
	$sqlConnection = sqlsrv_connect(DB_SERVERNAME, $sqlConnectionOptions);

if ($sqlConnection === false) 
	die(500);

// Build SQL Query Statement

$sqlStatementText = "SELECT * FROM " . DB_TABLE_SPECIAL_TICKET_REQUESTS . " ORDER BY RequestedBy, RequestDateTime DESC";
	$sqlStatementResults = sqlsrv_query( $sqlConnection, $sqlStatementText);

if( $sqlStatementResults === false ) {
     die(500);
}

/* Process results */
$json = array();
do 
	{
		while ($row = sqlsrv_fetch_array($sqlStatementResults, SQLSRV_FETCH_ASSOC))
			{	$json[] = $row; }
	 
	} 	while ( sqlsrv_next_result($sqlStatementResults) );

header ( "Content-type: application/json;" );
  echo json_encode ( $json );

sqlsrv_free_stmt( $sqlStatementResults );
sqlsrv_close( $sqlConnection );

?>