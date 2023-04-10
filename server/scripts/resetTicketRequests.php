<?php

header("Access-Control-Allow-Origin: *");

// resetTicketRequests.php
//		Deletes all ticket requests and sets all ticket statuses back to available.
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
    
// Connect to database
$options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
if ($databaseConnection === false) 
	die("<pre>".print_r(sqlsrv_errors(), true));

// Delete all ticket requests
   
        // Action and Table/View
        $sqlStatement  = "DELETE FROM [" . DB_TABLE_TICKET_REQUESTS . "];";
            
        $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement );

        // Error checking in the SQL processing

        if( $sqlStatus === false ) 
        {
            echo "Error in executing query.</br>";
            echo "SQL: " . $sqlStatement . " </br>";
            var_dump(http_response_code(500));
            die( print_r( sqlsrv_errors(), true));
        }

    // Step 2: Reset all ticket status to AVAILABLE

        // Action and Table/View
        
            $sqlStatement  = "BEGIN TRAN ";
           
                $argumentNewStatus = 'AVAILABLE';
                
                $sqlStatement .= "SELECT [Status] FROM " . DB_TABLE_TICKET_DETAIL . " WITH (UPDLOCK); ";
                
                $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_DETAIL . " SET [Status]="  . DB_SINGLE_QUOTE . $argumentNewStatus . DB_SINGLE_QUOTE . "; ";
            
            $sqlStatement .= "COMMIT TRAN";

            // Execute the insert
            $sqlData = sqlsrv_query( $databaseConnection, $sqlStatement);

            if( $sqlData === false )
            {
                echo "Error in executing query.</br>";
                echo "SQL: " . $sqlStatement . " </br>";
                var_dump(http_response_code(500));
                // sqlsrv_free_stmt( $sqlData) ;
                sqlsrv_close($databaseConnection);
                die( print_r( sqlsrv_errors(), true));
            }

        // Step 2: Send notification email(s)

            include 'sendEmailNotification.php';
                $sendEmailTo = "jbucar@walterhav.com";
                sendEmailTicketRequestsInit($sendEmailTo,9999);
    
    sqlsrv_close($databaseConnection);

    var_dump(http_response_code(204));

?>
