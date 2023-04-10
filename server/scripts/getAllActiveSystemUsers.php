<?php

// getAllActiveSystemUsers.php
//		Passes back a json object with all system users and memberships that are active
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

header("Access-Control-Allow-Origin: *");
include 'databaseConstants.php';  

// Connect to database
$databaseOptions = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
$databaseConnection = sqlsrv_connect(DB_SERVERNAME, $databaseOptions);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
    die("<pre>".print_r(sqlsrv_errors(), true));

// ---------------------------------------------------

    // Action
    $sqlStatement  =        "SELECT RTRIM(userAccount) AS userAccount, ";
        $sqlStatement .=        "RTRIM(userName) AS userName, ";
        $sqlStatement .=        "RTRIM(userStatus) AS userStatus, ";
        $sqlStatement .=        "memberOfEveryone,";
        $sqlStatement .=        "memberOfAssociate,";
        $sqlStatement .=        "memberOfPartner,";
        $sqlStatement .=        "memberOfSectionHead,";
        $sqlStatement .=        "memberOfTicketAdministration, ";
		$sqlStatement .=        "memberOfSystemAdministration ";
        $sqlStatement .=    "FROM " . DB_TABLE_SYSTEM_USERS . " ";
        $sqlStatement .=    "WHERE userStatus='ACTIVE' ";
        $sqlStatement .=    "ORDER BY userAccount;";
  
    // Execute the SQL
    $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);
 
        // Error checking in the SQL processing
 
        if( $sqlData === false ) 
           {
              die(500);
           }
 
        // Go ahead and process our results
        
        $json = array();
 
        do 
            {
                while ($row = sqlsrv_fetch_array($sqlData, SQLSRV_FETCH_ASSOC))
                    {
                        $json[] = $row;     
                    }
            }   while ( sqlsrv_next_result($sqlData) );
              
        header ( "Content-type: application/json;" );
            echo json_encode ( $json );
         
      sqlsrv_free_stmt( $sqlData);
      sqlsrv_close($databaseConnection);
 
?>
