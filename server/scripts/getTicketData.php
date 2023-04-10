 <?php
header("Access-Control-Allow-Origin: *");

include 'databaseConstants.php';

$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$sqlConnection = sqlsrv_connect(DB_SERVERNAME, $options);

$argumentNumberToCheck = $_GET['WorkNumber'];
$argumentTicketDataNeeded = $_GET['DataNeeded'];

$dataNameTicketDetail = "dbo.viewAllTickets";
$dataNameTicketRequests = "dbo.tblTicketRequests";
$dataNameTicketAssignments = "dbo.tblTicketAssignments";
$dataNameTicketRequestsGuests = "dbo.tblTicketRequests";
$dataNameTicketAssignmentsGuests = "dbo.tblTicketAssignments";
	
/* Possible Ticket Data Needed Values */
/* TD = Ticket Detail, TR = Ticket Request Information, TA = Ticket Assignment Information, TRG = Ticket Request Guest Information, TAG = Ticket Assignment Guest Information */

if ($argumentNumberToCheck == '')	
	{	echo "ERROR0001: No work number specified."; die();	}

if ($argumentTicketDataNeeded == '')
	{	echo "ERROR0002: No ticket data code specified."; die();	}

if ($argumentTicketDataNeeded <> 'TD' AND $argumentTicketDataNeeded <> 'TRA' AND $argumentTicketDataNeeded <> 'TRS' AND $argumentTicketDataNeeded <> 'TAR'  AND $argumentTicketDataNeeded <> 'TAT' AND $argumentTicketDataNeeded <> 'TG' AND $argumentTicketDataNeeded <> 'TRG' AND $argumentTicketDataNeeded <> 'TAG')
	{	echo "ERROR0003: Invalid ticket data code specified."; die();	}

/* ------------------------- */
/* Ticket Detail Information */

if ($argumentTicketDataNeeded == 'TD')
{	
	$SQLStatement = "SELECT * FROM dbo.viewAllTickets WHERE TicketRecordNumber='$argumentNumberToCheck'";	
}	

/* -------------------------- */
/* Ticket Request Information */

	/* Ticket Request TRA - Return all requests for Ticket Number */
	if ($argumentTicketDataNeeded == 'TRA')
	{
		$SQLStatement = "SELECT * FROM $dataNameTicketRequests WHERE AssociatedTicketRecordNumber='$argumentNumberToCheck'";
		
	}
	/* Ticket Request TRS - Return only the specified request number */
	if ($argumentTicketDataNeeded == 'TRS')
	{
		$SQLStatement = "SELECT * FROM $dataNameTicketRequests WHERE RequestNumber='$argumentNumberToCheck'";
		
		echo $SQLStatement;
	}

/* ----------------------------- */
/* Ticket Assignment Information */

	/* Ticket Assignment TAR - Return all assignments for specified ticket request number */
	if ($argumentTicketDataNeeded == 'TAR')
	{	
		
		$SQLStatement = "SELECT * FROM $dataNameTicketAssignments WHERE AssociatedTicketRequestNumber='$argumentNumberToCheck'";
	}

	/* Ticket Assignment TAT -  Return all assignments for specified ticket number  */
	if ($argumentTicketDataNeeded == 'TAT')
	{	
		
		$SQLStatement = "SELECT * FROM $dataNameTicketAssignments WHERE AssociatedTicketRecordNumber='$argumentNumberToCheck'";
	}

/* Ticket Request Guest Information */
	
	/* Ticket Request Guest TRG - Return Request Guest Information for Request Number */
	if ($argumentTicketDataNeeded == 'TRG')
	{
		
		$SQLStatement = "SELECT RequestNumber,AssociatedTicketRecordNumber,ReasonCode,FirmAttorneyForGuest,GuestCompany,GuestName,GuestEmail,GuestPhoneNumber FROM $dataNameTicketRequestsGuests WHERE RequestNumber='$argumentNumberToCheck'"; 		
	}
	
	/* Ticket Assignment Guest TAG - Return Request Guest Information for Request Number */
	if ($argumentTicketDataNeeded == 'TAG')
	{
		
		$SQLStatement = "SELECT * FROM $dataNameTicketAssignmentsGuests WHERE AssociatedTicketRequestNumber='$argumentNumberToCheck'"; 		
	}	

/* Connect to SQL */

	if ($sqlConnection === false) 
		die("<pre>".print_r(sqlsrv_errors(), true));

	$sqlData = sqlsrv_query( $sqlConnection, $SQLStatement);
	
if( $sqlData === false ) {
     echo "ERROR9000: Error in executing query.</br>";
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

/* Clean up */

sqlsrv_free_stmt( $sqlData );
sqlsrv_close($sqlConnection);

?>
