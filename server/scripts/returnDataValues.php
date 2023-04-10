<?php

header("Access-Control-Allow-Origin: *");

// returnDataValues.php
//	  This script includes all the functions that return various data values as requested by the controlling scripts.
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
//
//
// Functions:                                                       Returns:
//
//  returnEventStatus(passInEventNumber)                               Current Status in tblEvents or 500 if an error.
//
//  returnTicketBatchStatus(passInTicketBatchNumber)                   Current Status in tblTicketMaster or 500 if an error.
//  returnTicketStatus(passInTicketRecordNumber)                       Current Status in tblTicketDetail or 500 if an error.
//
//  returnTicketRequestStatus(passInTicketRequestNumber)               Current Status In tblTicketRequests or 500 if an error.
//  returnTicketAssignmentStatus(passInTicketAssignmentNumber)         Current Status in tblTicketAssignments or 500 if an error.
//
//    

include 'databaseConstants.php';

function returnEventStatus($passInEventNumber)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
        // Action

        $sqlStatement  = "SELECT ";
       
        // Columns
            $sqlStatement .= "[EventNumber], ";
            $sqlStatement .= "RTRIM([EventDescription]) AS EventDescription, ";
            $sqlStatement .= "RTRIM([Status]) AS Status ";
        
        // From
        $sqlStatement .= "FROM ";
            $sqlStatement .= "[" . DB_TABLE_EVENTS . "] ";

        // Where Limits
        $sqlStatement .= "WHERE ( ";
          $sqlStatement .= " [EventNumber]=" . $passInEventNumber . " );";
      
        $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                die( print_r( sqlsrv_errors(), true) );
            }
       
        while( $row = sqlsrv_fetch_array( $sqlStatus, SQLSRV_FETCH_ASSOC) )
            {
                $returnStatus = $row['Status'];
            }
        
        sqlsrv_free_stmt($sqlStatus);
        
        return $returnStatus;
}

function returnTicketBatchStatus($passInTicketBatchNumber)
{

    // Connect to database
     $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
     $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
     if ($databaseConnection === false) 
         die("<pre>".print_r(sqlsrv_errors(), true));
     
     // Action

     $sqlStatement  = "SELECT ";
    
     // Columns
         $sqlStatement .= "[TicketBatchNumber], ";
         $sqlStatement .= "RTRIM([TicketBatchStatus]) AS TicketBatchStatus ";
     
     // From
     $sqlStatement .= "FROM ";
         $sqlStatement .= "[" . DB_TABLE_TICKET_MASTER . "] ";

     // Where Limits
     $sqlStatement .= "WHERE ( ";
       $sqlStatement .= " [TicketBatchNumber]=" . $passInBatchNumber . " );";
   
     $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
    
     if ($sqlStatus === false)
         {   
            die( var_dump(http_response_code(500)));
         }
    
     while( $row = sqlsrv_fetch_array( $sqlStatus, SQLSRV_FETCH_ASSOC) )
         {
             $returnStatus = $row['TicketBatchStatus'];
         }
     
     sqlsrv_free_stmt($sqlStatus);
     
     return $returnStatus;
}

function returnTicketStatus($passInTicketStatus)
{

}

function returnTicketRequestStatus($passInTicketRequestNumber)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
        // Action

        $sqlStatement  = "SELECT ";
       
        // Columns
            $sqlStatement .= "[RequestNumber], ";
            $sqlStatement .= "RTRIM([Status]) AS Status ";
        
        // From
        $sqlStatement .= "FROM ";
            $sqlStatement .= "[" . DB_TABLE_TICKET_REQUESTS . "] ";

        // Where Limits
        $sqlStatement .= "WHERE ( ";
          $sqlStatement .= " [RequestNumber]=" . $passInTicketRequestNumber . " );";
      
        $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                echo "Error in executing query.</br>";
                echo "SQL: " . $sqlStatement . " </br>";
                var_dump(http_response_code(500));
                // sqlsrv_free_stmt( $sqlStatus) ;
                sqlsrv_close($databaseConnection);
                die( print_r( sqlsrv_errors(), true) );
            }
       
        while( $row = sqlsrv_fetch_array( $sqlStatus, SQLSRV_FETCH_ASSOC) )
            {
                $returnStatus = $row['Status'];
            }
        
        sqlsrv_free_stmt($sqlStatus);
        
        return $returnStatus;
}

function returnTicketAssignmentStatus($passInTicketAssignmentNumber)
{
    // Connect to database
    $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

// If we cannot get a connection lets terminate.
    if ($databaseConnection === false) 
        die("<pre>".print_r(sqlsrv_errors(), true));
    
    // Action

    $sqlStatement  = "SELECT ";
   
    // Columns
        $sqlStatement .= "[AssignmentNumber], ";
        $sqlStatement .= "RTRIM([Status]) AS Status ";
    
    // From
    $sqlStatement .= "FROM ";
        $sqlStatement .= "[" . DB_TABLE_TICKET_ASSIGNMENTS . "] ";

    // Where Limits
    $sqlStatement .= "WHERE ( ";
      $sqlStatement .= " [AssignmentNumber]=" . $passInTicketAssignmentNumber . " );";
  
    $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
   
    if ($sqlStatus === false)
        {   
            die( print_r( sqlsrv_errors(), true) );
        }
   
    while( $row = sqlsrv_fetch_array( $sqlStatus, SQLSRV_FETCH_ASSOC) )
        {
            $returnStatus = $row['Status'];
        }
    
    sqlsrv_free_stmt($sqlStatus);
    
    return $returnStatus;
}

?>
