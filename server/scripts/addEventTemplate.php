<?php

header("Access-Control-Allow-Origin: *");
session_start();

include_once('databaseConstants.php');
include_once('addLogMessage.php');

$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);
$input = json_decode( file_get_contents( 'php://input', true ) );
$logAction = "ADD EVENT TEMPLATE";
$sqlStatement = "";

if ( $databaseConnection === false ) 
{
  logAction( $logAction, "Unable to connect to database." );
  http_response_code(500);
  die();
}
else if (!isset( $input->EventTemplateName )) 
{ 
  logAction( $logAction, "Input parameters invalid." );
  http_response_code(422);
  die();
}
else 
{
  logAction( $logAction, "Starting Event Template Creation (Name: $input->EventTemplateName).");

  $sqlStatement .= "INSERT INTO [" . DB_TABLE_EVENT_TEMPLATES . "] ";
  $sqlStatement .= "(EventTemplateName) ";
  $sqlStatement .= "VALUES ( ";
  $sqlStatement .= DB_SINGLE_QUOTE . $input->EventTemplateName . DB_SINGLE_QUOTE;
  $sqlStatement .= "); ";
     
  // Execute the insert
  $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
  // Error checking in the SQL processing
  if( $sqlData === false ) 
  { 
    if ( ($errors = sqlsrv_errors() ) != null) {
      foreach( $errors as $error ) {
        logAction( $logAction, "SQLSTATE: ".$error[ 'SQLSTATE']."<br />");
        logAction( $logAction, "code: ".$error[ 'code']."<br />");
        logAction( $logAction, "message: ".$error[ 'message']."<br />");
      }
    }
    handleError($logAction, "1. Error while creating event template (Name: $input->EventTemplateName).");
  }
  else 
  {
    $sqlStatement = "";

    // Continue with event creation
    foreach ($input->events as $event) {
      $sqlStatement .= "INSERT INTO [" . DB_TABLE_EVENT_TEMPLATE_EVENTS . "] ";
      $sqlStatement .= "( ";
      $sqlStatement .= "RelativeEventTemplateName";
      if (isset( $event->Status )) {
        $sqlStatement .= ",";
        $sqlStatement .= "Status";
      }
      if (isset( $event->RelativeCategory )) {
        $sqlStatement .= ",";
        $sqlStatement .= "RelativeCategory";
      }
      if (isset( $event->RelativeVenueNumber )) {
        $sqlStatement .= ",";
        $sqlStatement .= "RelativeVenueNumber";
      }
      if (isset( $event->TitleDescription )) {
        $sqlStatement .= ",";
        $sqlStatement .= "TitleDescription";
      }
      if (isset( $event->DateAndTime )) {
        $sqlStatement .= ",";
        $sqlStatement .= "DateAndTime";
      }
      $sqlStatement .= ") ";

      $sqlStatement .= "VALUES ( ";

      $sqlStatement .= DB_SINGLE_QUOTE . $input->EventTemplateName . DB_SINGLE_QUOTE;
      if (isset( $event->Status )) {
        $sqlStatement .= ",";
        if(strcmp($event->Status, "AVAILABLE") == 0){
          $sqlStatement .= 1;
        }
        else{
          $sqlStatement .= 0;
        }
      }
      if (isset( $event->RelativeCategory )) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $event->RelativeCategory . DB_SINGLE_QUOTE;
      }
      if (isset( $event->RelativeVenueNumber )) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $event->RelativeVenueNumber . DB_SINGLE_QUOTE;
      }
      if (isset( $event->TitleDescription )) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $event->TitleDescription . DB_SINGLE_QUOTE;
      }
      if (isset( $event->DateAndTime )) {
        $sqlStatement .= ",";
        $sqlStatement .= DB_SINGLE_QUOTE . $event->DateAndTime . DB_SINGLE_QUOTE;
      }
      $sqlStatement .= "); ";
    }

    // Execute the insert
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
  
    // Error checking in the SQL processing
    if( $sqlData === false ) { 
      handleError($logAction, "2. Error while creating event template (Name: $input->EventTemplateName).");
    }
    else {
      logAction( $logAction, "Event template (Name: $input->EventTemplateName) was successfully created." );
      http_response_code( 201 );
    }
	
    sqlsrv_free_stmt( $sqlData );
    sqlsrv_close( $databaseConnection );
  }
}

function handleError($action, $msg ) {
  logAction($action, $msg);
  http_response_code(500);
  die();
}