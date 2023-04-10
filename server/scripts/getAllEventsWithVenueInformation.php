<?php
header("Access-Control-Allow-Origin: *");

// getAllEventsWithVenueInformation.php
//		Returns all events in the system with detailed venue information
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

// Lets lets get the data
  
    // Action
      $sqlStatement = "SELECT event.*, venue.Name ";
        $sqlStatement .= "FROM dbo.tblEvents event LEFT JOIN dbo.tblEventVenues venue ON event.AssociatedVenueNumber = venue.VenueNumber ";
         $sqlStatement .= "WHERE event.EventDateTime >= Convert(Date, GetDate(), 101) ";
		  $sqlStatement .= "ORDER BY event.AssociatedTicketCategory, event.EventDateTime";
         
		
      $stmt = sqlsrv_query( $conn, $sqlStatement);

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
