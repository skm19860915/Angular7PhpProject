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

  $sql = "SELECT " .DB_TABLE_TEMPLATES. ".*, " .DB_TABLE_TICKETS_IN_TEMPLATES. ".* FROM ";
  $sql .= DB_TABLE_TEMPLATES. " INNER JOIN " . DB_TABLE_TICKETS_IN_TEMPLATES;
  $sql .= " ON " .DB_TABLE_TEMPLATES. ".Id = " .DB_TABLE_TICKETS_IN_TEMPLATES. ".TemplateId";

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
