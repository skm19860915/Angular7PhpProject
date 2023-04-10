<?php
header("Access-Control-Allow-Origin: *");

/**
 * getAllTicketTemplates.php
 * Used to get all ticket templates including their tickets from the system
 */

include 'databaseConstants.php';
include_once('addLogMessage.php');
$logAction = 'GET TICKET TEMPLATES';

$options = array("UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$con = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate
if ($con === false) {
  handleError("Error while accessing the database. ");
} else {

  $sql = "SELECT tblTicketTemplates.*, tblTicketTemplatesTickets.* FROM ";
  $sql .= DB_TABLE_TICKET_TEMPLATES . " INNER JOIN " . DB_TABLE_TICKET_TEMPLATE_TICKETS;
  $sql .= " ON tblTicketTemplates.TicketTemplateName = tblTicketTemplatesTickets.AssociatedTemplateName ";

  $sqlRes = sqlsrv_query($con, $sql);

  if (!$sqlRes) {
    handleError("Error while accessing the database: " . sqlsrv_errors());
  } else {

    $jsonRes = array();

    do {
      while ($row = sqlsrv_fetch_array($sqlRes, SQLSRV_FETCH_ASSOC)) {
        $jsonRes[] = $row;
      }
    } while (sqlsrv_next_result($sqlRes));

    header("Content-type: application/json;");
    echo json_encode($jsonRes);
  }

  sqlsrv_free_stmt($sqlRes);
  sqlsrv_close($con);
}

function handleError($msg)
{
  logAction($logAction, $msg);
  http_response_code(500);
  die();
}
