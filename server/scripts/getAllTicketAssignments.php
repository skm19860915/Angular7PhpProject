<?php

/**
 * getAllTicketAssignments.php
 *  used to return a list of all the ticket assignments in the system
 *
 * @return array of all ticket assignments
 */

header("Access-Control-Allow-Origin: *");

include 'databaseConstants.php';

$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$conn = sqlsrv_connect(DB_SERVERNAME, $options);

if ($conn === false)
	die("<pre>".print_r(sqlsrv_errors(), true));

// $json = json_decode(file_get_contents('php://input',true));

  // Lets lets get the data

    // Action
    $sqlStatement  = "SELECT ";

      // Columns
      $sqlStatement .=
      "RTRIM(TA.AssignmentNumber) AS AssignmentNumber,
      RTRIM(TA.ClientMatter) AS ClientMatter,
      RTRIM(TA.Status) AS Status,
      RTRIM(TA.AssociatedTicketRecordNumber) AS AssociatedTicketRecordNumber,
      RTRIM(TA.AssociatedTicketRequestNumber) AS AssociatedTicketRequestNumber,
      RTRIM(TA.AssociatedReasonCode) AS AssociatedReasonCode,
      RTRIM(TA.GuestAssociatedFirmAttorney) AS GuestAssociatedFirmAttorney,
      RTRIM(TA.GuestClientNumber) AS GuestClientNumber,
      RTRIM(TA.GuestCompany) AS GuestCompany,
      RTRIM(TA.GuestName) AS GuestName,
      RTRIM(TA.GuestPhoneNumber) AS GuestPhoneNumber,
      RTRIM(TA.GuestEmail) AS GuestEmail,
      TA.AssignmentNotes AS AssignmentNotes,
      TA.AssociatedTicketDeliveryType,
      RTRIM(TA.stepInitialCreatedDateTime) AS stepInitialCreatedDateTime,
      RTRIM(TA.stepInitialCreatedBy) AS stepInitialCreatedBy,
      RTRIM(TD.Section) As Section,
      RTRIM(TD.Row) AS Row,
      RTRIM(TD.Seat) AS Seat,
      TD.FoodOrdering,
      RTRIM(TM.AssociatedSeatType) AS AssociatedSeatType,
      TM.AssociatedVenueNumber,
      E.EventNumber,
      RTRIM(E.EventDescription) AS EventDescription,
      E.EventDateTime,
      RTRIM(TA.stepReviewDateTime) AS stepReviewDateTime,
      RTRIM(TA.stepReviewBy) AS stepReviewBy,
      RTRIM(TA.stepFoodOrderNeededDateTime) AS stepFoodOrderNeededDateTime,
      RTRIM(TA.stepFoodOrderNeededBy) AS stepFoodOrderNeededBy,
      RTRIM(TA.stepFoodOrderEnteredDateTime) AS stepFoodOrderEnteredDateTime,
      RTRIM(TA.stepFoodOrderEnteredBy) AS stepFoodOrderEnteredBy,
      RTRIM(TA.stepFoodOrderNeedsRevisionDateTime) AS stepFoodOrderNeedsRevisionDateTime,
      RTRIM(TA.stepFoodOrderNeedsRevisionBy) AS stepFoodOrderNeedsRevisionBy,
      RTRIM(TA.stepFoodOrderApprovedDateTime) AS stepFoodOrderApprovedDateTime,
      RTRIM(TA.stepFoodOrderApprovedBy) AS stepFoodOrderApprovedBy,
      RTRIM(TA.stepFoodOrderSentToVenueDateTime) AS stepFoodOrderSentToVenueDateTime,
      RTRIM(TA.stepFoodOrderSentToVenueBy) AS stepFoodOrderSentToVenueBy,
      RTRIM(TA.stepTicketsReadyDateTime) AS stepTicketsReadyDateTime,
      RTRIM(TA.stepTicketsReadyBy) AS stepTicketsReadyBy,
      RTRIM(TA.stepTicketsDeliveredDateTime) AS stepTicketsDeliveredDateTime,
      RTRIM(TA.stepTicketsDeliveredBy) AS stepTicketsDeliveredBy,
      RTRIM(TA.stepCancelledDateTime) AS stepCancelledDateTime,
      RTRIM(TA.stepCancelledBy) AS stepCancelledBy,
      RTRIM(R.Status) AS RequestStatus,
      RTRIM(R.RequestedBy) AS RequestedBy,
      RTRIM(R.RequestDateTime) As RequestDateTime,
      RTRIM(R.CreatedBy) AS CreatedBy,
      RTRIM(R.CreatedDateTime) As CreatedDateTime,


    CASE	WHEN RTRIM(TA.Status) = 'AWAITING ADMIN' THEN stepInitialCreatedDateTime
          WHEN RTRIM(TA.Status) = 'TICKETS READY FOR DELIVERY' THEN stepTicketsReadyDateTime
          WHEN RTRIM(TA.Status) = 'TICKETS DELIVERED' THEN stepTicketsDeliveredDateTime
          WHEN RTRIM(TA.Status) = 'CANCELLED' THEN stepCancelledDateTime
          ELSE '' END AS currentStepDateTime

    FROM    dbo.tblTicketAssignments AS TA
                  INNER JOIN dbo.tblTicketsDetail AS TD ON TA.AssociatedTicketRecordNumber = TD.TicketRecordNumber
                  INNER JOIN dbo.tblTicketsMaster AS TM ON TD.AssociatedTicketBatchNumber = TM.TicketBatchNumber
                  INNER JOIN dbo.tblEvents AS E ON TM.AssociatedEventNumber = E.EventNumber
                  INNER JOIN dbo.tblTicketRequests R ON TA.AssociatedTicketRequestNumber = R.RequestNumber ";


      // $sqlStatement .= "* " ;

      /*
      $sqlStatement .= "TA.AssignmentNumber, ";
      $sqlStatement .= "RTRIM(TA.Status) AS TA.Status, ";
      $sqlStatement .= "TA.AssociatedTicketRecordNumber, ";
      $sqlStatement .= "TA.AssociatedTicketRequestNumber, ";
      $sqlStatement .= "TA.AssociatedReasonCode, ";
      $sqlStatement .= "TA.GuestAssociatedFirmAttorney, ";
      $sqlStatement .= "TA.GuestClientNumber, ";
      $sqlStatement .= "TA.GuestCompany, ";
      $sqlStatement .= "TA.GuestName, ";
      $sqlStatement .= "TA.GuestPhoneNumber, ";
      $sqlStatement .= "TA.GuestEmail, ";
      $sqlStatement .= "TA.Section, ";
      $sqlStatement .= "TA.Row, ";
      $sqlStatement .= "TA.Seat, ";
      $sqlStatement .= "TA.FoodOrdering, ";
      $sqlStatement .= "TA.AssociatedSeatType, ";
      $sqlStatement .= "TA.EventDescription, ";
      $sqlStatement .= "TA.EventDateTime, ";
      $sqlStatement .= "TA.stepReviewDateTime, ";
      $sqlStatement .= "TA.stepReviewBy, ";
      $sqlStatement .= "TA.stepFoodOrderNeededDateTime, ";
      $sqlStatement .= "TA.stepFoodOrderNeededBy, ";
      $sqlStatement .= "TA.stepFoodOrderEnteredDateTime, ";
      $sqlStatement .= "TA.stepFoodOrderEnteredBy, ";
      $sqlStatement .= "TA.stepFoodOrderNeedsRevisionDateTime, ";
      $sqlStatement .= "TA.stepFoodOrderNeedsRevisionBy, ";
      $sqlStatement .= "TA.stepFoodOrderApprovedDateTime, ";
      $sqlStatement .= "TA.stepFoodOrderApprovedBy, ";
      $sqlStatement .= "TA.stepFoodOrderSentToVenueDateTime, ";
      $sqlStatement .= "TA.stepFoodOrderSentToVenueBy, ";
      $sqlStatement .= "TA.stepTicketsReadyDateTime, ";
      $sqlStatement .= "TA.stepTicketsReadyBy, ";
      $sqlStatement .= "TA.stepTicketsDeliveredDateTime, ";
      $sqlStatement .= "TA.stepTicketsDeliveredBy, ";
      $sqlStatement .= "TA.stepCancelledDateTime, ";
      $sqlStatement .= "TA.stepCancelledBy, ";
      $sqlStatement .= "TA.stepInitialCreatedDateTime, ";
      $sqlStatement .= "TA.stepInitialCreatedBy, ";
      $sqlStatement .= "R.Status AS RequestStatus, ";
      $sqlStatement .= "R.RequestedBy AS RequestedBy, ";
      $sqlStatement .= "R.CreatedBy AS CreatedBy ";


      // From

      $sqlStatement .= "FROM viewAllTicketAssignmentsWithDetail TA ";
        $sqlStatement .=    "INNER JOIN ";
          $sqlStatement .=  " dbo.tblTicketRequests R ON TA.AssociatedTicketRequestNumber = R.RequestNumber ";

      // Where

      // Order By

      // $sqlStatement .= "ORDER BY AssignmentNumber ";

      $sqlStatement .= "ORDER BY ";
        $sqlStatement .= " TA.EventDateTime DESC, ";
        $sqlStatement .= " TA.EventDescription, ";
        $sqlStatement .= " TA.AssociatedSeatType, ";
        $sqlStatement .= " TA.Section, ";
        $sqlStatement .= " TA.Row, ";
        $sqlStatement .= " TA.Seat ";
      */

      $sqlData = sqlsrv_query( $conn, $sqlStatement);

      // echo "<br /><br />SQL: " . $sqlStatement . "";

if( $sqlData === false )
{
     die( print_r( sqlsrv_errors(), true));
}



$json = array();

do  {

      while ($row = sqlsrv_fetch_array($sqlData, SQLSRV_FETCH_ASSOC))
      {
        $json[] = $row;
      }

    } while ( sqlsrv_next_result($sqlData) );

header ( "Content-type: application/json;" );
  echo json_encode ( $json );


sqlsrv_free_stmt( $sqlData);
sqlsrv_close($conn);

?>
