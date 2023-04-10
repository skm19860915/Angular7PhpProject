<?php

header("Access-Control-Allow-Origin: *");

// getAllTicketRequestsWithTicketDetail.php
//		Returns all ticket requests with seat information and status
//
// Walter | Haverfield
//
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------
// 3/9/2018         JAB                 Initial creation

include 'databaseConstants.php';

$options = array("UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$conn = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
if ($conn === false)
die("<pre>".print_r(sqlsrv_errors(), true));

$json = json_decode(file_get_contents('php://input',true));

// Action
$sqlStatement  = "SELECT ";

// Columns
$sqlStatement .= "dbo.tblTicketRequests.RequestNumber, ";
$sqlStatement .= "dbo.tblTicketRequests.ClientMatter, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.Status) AS statusOfTicketRequest, ";
$sqlStatement .= "dbo.tblTicketRequests.AssociatedTicketRecordNumber, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.RequestedBy) AS RequestedBy, ";
$sqlStatement .= "dbo.tblTicketRequests.RequestDateTime, ";
$sqlStatement .= "dbo.tblTicketRequests.CreatedDateTime, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.CreatedBy) AS CreatedBy, ";
$sqlStatement .= "dbo.tblTicketRequests.ModifiedDateTime, ";
$sqlStatement .= "dbo.tblTicketRequests.CreatedDateTime, ";
$sqlStatement .= "dbo.tblTicketRequests.RequestNotes, ";
$sqlStatement .= "dbo.tblTicketRequests.AssociatedTicketDeliveryType, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.CreatedBy) AS CreatedBy, ";
$sqlStatement .= "dbo.tblTicketRequests.ModifiedDateTime, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.ModifiedBy) AS ModifiedBy, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.ModifiedBy) AS ModifiedBy,";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.ReasonCode) AS ReasonCode, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.FirmAttorneyForGuest) AS FirmAttorneyForGuest, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.AssociatedGuestClientNumber) AS AssociatedGuestClientNumber, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.GuestCompany) AS GuestCompany, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.GuestName) AS GuestName, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.GuestEmail) AS GuestEmail, ";
$sqlStatement .= "RTRIM(dbo.tblTicketRequests.GuestPhoneNumber) AS GuestPhoneNumber, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsDetail.Status) AS statusOfTicket, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsDetail.Section) AS Section, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsDetail.Row) AS Row, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsDetail.Seat) AS Seat, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsDetail.FoodOrdering) AS FoodOrdering, ";
$sqlStatement .= "dbo.tblTicketsMaster.TicketBatchNumber, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsMaster.TicketBatchStatus) AS TicketBatchStatus, ";
$sqlStatement .= "dbo.tblTicketsMaster.AvailableTo AS AvailableTo, ";
$sqlStatement .= "dbo.tblTicketsMaster.AutoApprove AS AutoApprove, ";
$sqlStatement .= "dbo.tblTicketsMaster.AssociatedEventNumber, ";
$sqlStatement .= "dbo.tblTicketsMaster.AssociatedVenueNumber, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsMaster.AssociatedTicketCategory) AS AssociatedTicketCategory, ";
$sqlStatement .= "RTRIM(dbo.tblTicketsMaster.AssociatedSeatType) AS AssociatedSeatType, ";
$sqlStatement .= "dbo.tblEvents.EventNumber, ";
$sqlStatement .= "RTRIM(dbo.tblEvents.EventDescription) AS EventDescription, ";
$sqlStatement .= "dbo.tblEvents.EventDateTime, ";
$sqlStatement .= "dbo.tblEvents.EventNotes AS EventNotes, ";
$sqlStatement .= "RTRIM(dbo.tblEvents.AssociatedTicketCategory) AS TicketCategory, ";
$sqlStatement .= "dbo.tblEvents.AssociatedVenueNumber AS VenueNumber, ";
$sqlStatement .= "RTRIM(dbo.tblEvents.Status) AS EventStatus, ";
$sqlStatement .= "RTRIM(dbo.tblEventVenues.Status) AS EventVenueStatus, ";
$sqlStatement .= "RTRIM(dbo.tblEventVenues.Name) AS VenueName ";

// From
$sqlStatement .= "FROM dbo.tblTicketRequests INNER JOIN ";
$sqlStatement .= "dbo.tblTicketsDetail ON dbo.tblTicketRequests.AssociatedTicketRecordNumber = dbo.tblTicketsDetail.TicketRecordNumber INNER JOIN ";
$sqlStatement .= "dbo.tblTicketsMaster ON dbo.tblTicketsDetail.AssociatedTicketBatchNumber = dbo.tblTicketsMaster.TicketBatchNumber INNER JOIN ";
$sqlStatement .= "dbo.tblEvents ON dbo.tblTicketsMaster.AssociatedEventNumber = dbo.tblEvents.EventNumber INNER JOIN ";
$sqlStatement .= "dbo.tblEventVenues ON dbo.tblEvents.AssociatedVenueNumber = dbo.tblEventVenues.VenueNumber ";

// Order By
$sqlStatement .= "ORDER BY ";
$sqlStatement .= " dbo.tblEvents.EventDateTime DESC, ";
$sqlStatement .= " dbo.tblTicketsMaster.AssociatedTicketCategory, ";
$sqlStatement .= " dbo.tblEvents.EventDescription, ";
$sqlStatement .= " dbo.tblTicketsDetail.Section, ";
$sqlStatement .= " dbo.tblTicketsDetail.Row, ";
$sqlStatement .= " dbo.tblTicketsDetail.Seat ";

$sqlData = sqlsrv_query( $conn, $sqlStatement);

if( $sqlData === false ) {
     echo "Error in executing query.</br>";
     die( print_r( sqlsrv_errors(), true));
}

/* Process results */
$json = array();

do {
     while ($row = sqlsrv_fetch_array($sqlData, SQLSRV_FETCH_ASSOC)) {
     $json[] = $row;
     }
} while ( sqlsrv_next_result($sqlData) );

header ( "Content-type: application/json;" );
  echo json_encode ( $json );


sqlsrv_free_stmt( $sqlData );
sqlsrv_close($conn);

?>
