<?php
// getTickets.php
header("Access-Control-Allow-Origin: *");

include 'databaseConstants.php';

$options = array("UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$conn = sqlsrv_connect(DB_SERVERNAME, $options);

$SQL = "select X.*, Y.DeliveryType, Y.Name ";
$SQL .= "from dbo.viewAllTickets as X, (select A.AssociatedTicketBatchNumber, B.DeliveryType, B.CreatedBy as Name from dbo.tblTicketsDetail as A left join dbo.DeliveryTypes as B on A.DeliveryTypeId = B.Id) as Y ";
$SQL .= "where X.TicketBatchNumber = Y.AssociatedTicketBatchNumber ";
if (isset($_GET['EventID'])) {
  $SQL .= "and (AssociatedEventNumber = '".$_GET['EventID']."') ";
}
$SQL .= "order by AssociatedEventNumber,AssociatedTicketCategory,AssociatedSeatType,Section,Row,Seat";

if ($conn === false) 
	die("<pre>".print_r(sqlsrv_errors(), true));

$stmt = sqlsrv_query( $conn, $SQL);

if( $stmt === false ) {
  echo "Error in executing query.</br>";
  die( print_r( sqlsrv_errors(), true));
}

/* Process results */
$json = array();

do {
  while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

    // Remove whitespaces from records
    // WORKAROUND
    // SQL Result or SQL Data must provide records w/o whitespaces
    $row["AssociatedSeatType"] = trim($row["AssociatedSeatType"]);
    $row["AssociatedTicketCategory"] = trim($row["AssociatedTicketCategory"]);
    $row["CreatedBy"] = trim($row["CreatedBy"]);
    $row["Row"] = trim($row["Row"]);
    $row["Seat"] = trim($row["Seat"]);
    $row["Section"] = trim($row["Section"]);
    $row["Status"] = trim($row["Status"]);
    $row["TicketBatchStatus"] = trim($row["TicketBatchStatus"]);

    // Lets retrieve all the requestors or assignees of tickets that are in a PENDING REQUEST or ASSIGNED State
    // I will loop thru and remove all CANCELLED status
        
    $row["RequestorOrAssigneeName"]="RequestorAssignee Empty";
    $row["GuestNames"]="GuestName Empty";
    $row["GuestCompanies"]="GuestCompany Empty";

    $json[] = $row;
  }
} while (sqlsrv_next_result($stmt));

header ("Content-type: application/json;");
echo json_encode ($json);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

?>
