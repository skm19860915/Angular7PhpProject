<?php

// Function for logging actions in the system

if (!function_exists('logAction'))
{
    function logAction($argumentAction, $argumentMessage)
    {
        // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

        // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));

        // Step 1: Insert Record into Logging

        // Action and Table/View
        //$sqlStatement  = "INSERT INTO [" . DB_TABLE_LOGGING . "] ";
        $sqlStatement  = "insert into wheventdb.tblLogging ";
        
        // Columns
        $sqlStatement .= "( ";
            $sqlStatement .= "Action, ";
            $sqlStatement .= "ActionDescription ";
            $sqlStatement .= ") ";

        // Values
        $sqlStatement .= "VALUES ( ";
            $sqlStatement .= DB_SINGLE_QUOTE . $argumentAction . DB_SINGLE_QUOTE . ", ";
            $sqlStatement .= DB_SINGLE_QUOTE . $argumentMessage . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= "); ";

        // Add the logging record 
        $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
        
        // Error checking in the SQL processing
        if( $sqlStatus === false ) 
        {
            var_dump(http_response_code(500));
            die( print_r( sqlsrv_errors(), true));
        }
    }
}
?>
