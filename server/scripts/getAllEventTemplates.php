<?php
header("Access-Control-Allow-Origin: *");

/**
 * getAllEventTemplates.php
 */

include 'databaseConstants.php';
include_once('addLogMessage.php');
$logAction = 'GET EVENT TEMPLATES';

$options = array("UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$con = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate
if ($con === false) {
  handleError($logAction, "Error while accessing the database. ");
} 
else {
  $sql = "SELECT tblEventTemplates.*, tblEventTemplatesEvents.* FROM ";
  $sql .= DB_TABLE_EVENT_TEMPLATES . " INNER JOIN " . DB_TABLE_EVENT_TEMPLATE_EVENTS;
  $sql .= " ON tblEventTemplates.EventTemplateName = tblEventTemplatesEvents.RelativeEventTemplateName ";

  $sqlRes = sqlsrv_query($con, $sql);
  if (!$sqlRes) {
    handleError($logAction, "Error while accessing the database: " . sqlsrv_errors());
  } 
  else {
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

function handleError($action, $msg)
{
  logAction($action, $msg);
  http_response_code(500);
  die();
}
