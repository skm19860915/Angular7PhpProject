<?php
header("Access-Control-Allow-Origin: *");

// getAllEventVenues.php
//		Used to return a list of all the valid Venues in the system
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

$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$conn = sqlsrv_connect(DB_SERVERNAME, $options);

if ($conn === false) 
	die("<pre>".print_r(sqlsrv_errors(), true));

/* SQL Statement */
$tsql = "SELECT * FROM " . DB_TABLE_EVENT_VENUES . "";
$stmt = sqlsrv_query( $conn, $tsql);

// If an error, lets stop the script
if( $stmt === false ) {
     echo "Error in executing query.</br>";
     die( print_r( sqlsrv_errors(), true));
}

/* Process results */
$json = array();

do {
     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
     $json[] = $row;     
     }
} while ( sqlsrv_next_result($stmt) );

header ( "Content-type: application/json;" );
  echo json_encode ( $json );


sqlsrv_free_stmt( $stmt);
sqlsrv_close($conn);

?>