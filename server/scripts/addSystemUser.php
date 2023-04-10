<?php

header("Access-Control-Allow-Origin: *");

// addSystemUser.php
//		Used to add a new user to the System
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
include 'addLogMessage.php';
    
// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
	die("<pre>".print_r(sqlsrv_errors(), true));

$json = json_decode(file_get_contents('php://input',true));

//
// Steps needed to accomplish in the addSystemUser process
//
// 1) Insert Record into Ticket Requests
// 2) Send email notifications

    // Step 1: Insert Record into System Users
  
        // Log
        $logMessage = "New User " . $json->userAccount . " is being added to the System | Pre Add ";
            logAction("ADD SYSTEM USER", $logMessage);
   
        // Action and Table/View
        $sqlStatement  = "INSERT INTO [" . DB_TABLE_SYSTEM_USERS . "] ";
        
        // Columns
        $sqlStatement .= "( ";
            $sqlStatement .= "[userAccount] ";
            $sqlStatement .= ",[userName] ";
            $sqlStatement .= ",[userStatus] ";
            $sqlStatement .= ",[memberOfEveryone] ";
            $sqlStatement .= ",[memberOfAssociate] ";
            $sqlStatement .= ",[memberOfPartner] ";
            $sqlStatement .= ",[memberOfSectionHead] ";
            $sqlStatement .= ",[memberOfTicketAdministration] ";
            $sqlStatement .= ",[memberOfSystemAdministration] ) ";
       
        // Values
        $sqlStatement .= "VALUES ( ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->userAccount . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->userName . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->userStatus . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfEveryone . DB_SINGLE_QUOTE .  ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfAssociate . DB_SINGLE_QUOTE .  ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfPartner . DB_SINGLE_QUOTE .  ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfSectionHead . DB_SINGLE_QUOTE .  ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfTicketAdministration . DB_SINGLE_QUOTE . ", ";
			$sqlStatement .= DB_SINGLE_QUOTE . $json->memberOfSystemAdministration . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= "); ";
    
        $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
         
		// Error checking in the SQL processing

        if( $sqlStatus === false ) 
        {
            echo "Error in executing query.</br>";
            echo "SQL: " . $sqlStatement . " </br>";

            // Log
            $logMessage = "An Error occurred adding New User " . $json->userAccount . " to the system  | ERROR on Add ";
                logAction("ADD SYSTEM USER", $logMessage);
            
            var_dump(http_response_code(500));
            die( print_r( sqlsrv_errors(), true));
        }

        // Log successful message
        $logMessage = "New User " . $json->userAccount . " has been added to the system. | Post Add ";
            logAction("ADD SYSTEM USER", $logMessage);
    
    sqlsrv_close($databaseConnection);

?>
