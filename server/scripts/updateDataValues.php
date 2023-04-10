<?php

header("Access-Control-Allow-Origin: *");
session_start();

// updateDataValues.php
//	  This script includes all the functions that updates various data values as requested by the controlling scripts.
//
// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// ----------------------------------------------------------------
// 05/06/2019       Samuel Leicht       Added 'AssociatedTicketDeliveryType'
// 05/02/2019       Samuel Leicht       Added 'AssignmentNotes'
// 03/09/2018       JAB                 Initial creation
//
//
// Functions:                                                       Returns:
//
//  updateEventStatus(passInEventNumber)                               Current Status in tblEvents or 500 if an error.
//
//  updateTicketBatchStatus(passInTicketBatchNumber)                   Current Status in tblTicketMaster or 500 if an error.
//  updateTicketStatus(passInTicketRecordNumber)                       Current Status in tblTicketDetail or 500 if an error.
//
//  updateTicketRequestStatus(passInTicketRequestNumber)               Current Status In tblTicketRequests or 500 if an error.
//  updateTicketAssignmentStatus(passInTicketAssignmentNumber)         Current Status in tblTicketAssignments or 500 if an error.
//
//  updateSystemUser(passInUserJson)                                   

include 'databaseConstants.php';
include_once('addLogMessage.php');

function updateTicketRequestStatus($passInTicketRequestNumber, $newTicketRequestStatus)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT [Status] FROM " . DB_TABLE_TICKET_REQUESTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [RequestNumber]=" . $passInTicketRequestNumber  . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET [Status]="  . DB_SINGLE_QUOTE . $newTicketRequestStatus . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= "WHERE [RequestNumber]=" . $passInTicketRequestNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {  
                die(500);
            }
               
        sqlsrv_free_stmt($sqlStatus);
        
        return "OK";
}

function updateTicketAssignmentStatusAdmin($json)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die(500);
    
    $dateModified = date_format(new DateTime(), 'Y-m-d H:i:s');
    $dateModifiedBy = $_SESSION["user"];
    
    $dateAction = date_format(new DateTime(), 'Y-m-d H:i:s');
    $dateActionBy = $_SESSION["user"];

    $newTicketAssignmentStatus = $json->Status;

    $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status | " . $dateModified . " " . $dateModifiedBy . " " . $dateAction . " " . $dateActionBy . "";
        logAction("UPD TKT ASSIGN STATUS", $logMessage);

    if ($newTicketAssignmentStatus == 'CANCELLED')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to CANCELLED";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
                
                $dateActionBy = $_SESSION["user"];
                    $sqlActionSet = " [stepCancelledDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepCancelledBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";            
        }

    if ($newTicketAssignmentStatus == 'AWAITING ADMIN')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to AWAITING ADMIN";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
            
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepInitialCreatedDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepInitialCreatedBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
        }
    
    if ($newTicketAssignmentStatus == 'TICKETS READY FOR DELIVERY')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to TICKETS READY FOR DELIVERY";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
        
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepTicketsReadyDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepTicketsReadyBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
     }
    
    if ($newTicketAssignmentStatus == 'TICKETS DELIVERED')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to TICKETS DELIVERED";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
    
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepTicketsDeliveredDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepTicketsDeliveredBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
        }
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT ";
            $sqlStatement .= "* ";
                
        $sqlStatement .= "FROM " . DB_TABLE_TICKET_ASSIGNMENTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [AssignmentNumber]=" . $json->AssignmentNumber  . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_ASSIGNMENTS . " ";
            
            $sqlStatement .= "SET ";
                $sqlStatement .= "[Status]=" . DB_SINGLE_QUOTE . $newTicketAssignmentStatus . DB_SINGLE_QUOTE . " ";
                $sqlStatement .= ", ". $sqlActionSet . " ";

            $sqlStatement .= "WHERE [AssignmentNumber]=" .  $json->AssignmentNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

        $logMessage = "SQL: User " . $_SESSION["user"] . " is updating ticket assignment status";
            logAction("UPD TKT ASSIGN STATUS", $logMessage);

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {
                $logMessage = "SQL Error: User " .$_SESSION["user"] . " is updating ticket assignment status";
                    logAction("UPD TKT ASSIGN STATUS", $logMessage);
                    die(500);
            }
               
        sqlsrv_free_stmt($sqlStatus);        
}

function updateTicketAssignmentStatus($passInTicketAssignmentNumber, $newTicketAssignmentStatus)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die(500);
    
    $dateModified = date_format(new DateTime(), 'Y-m-d H:i:s');
    $dateModifiedBy = $_SESSION["user"];
    
    $dateAction = date_format(new DateTime(), 'Y-m-d H:i:s');
    $dateActionBy = $_SESSION["user"];

    $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status | " . $dateModified . " " . $dateModifiedBy . " " . $dateAction . " " . $dateActionBy . "";
        logAction("UPD TKT ASSIGN STATUS", $logMessage);

    if ($newTicketAssignmentStatus == 'CANCELLED')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to CANCELLED";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
                
                $dateActionBy = $_SESSION["user"];
                    $sqlActionSet = " [stepCancelledDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepCancelledBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";            
        }

    if ($newTicketAssignmentStatus == 'AWAITING ADMIN')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to AWAITING ADMIN";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
            
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepInitialCreatedDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepInitialCreatedBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
        }
    
    if ($newTicketAssignmentStatus == 'TICKETS READY FOR DELIVERY')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to TICKETS READY FOR DELIVERY";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
        
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepTicketsReadyDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepTicketsReadyBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
     }
    
    if ($newTicketAssignmentStatus == 'TICKETS DELIVERED')
        {
            $logMessage = "User " .$_SESSION["user"] . " is updating ticket assignment status to TICKETS DELIVERED";
                logAction("UPD TKT ASSIGN STATUS", $logMessage);
    
            $dateActionBy = $_SESSION["user"];
                $sqlActionSet = " [stepTicketsDeliveredDateTime]=" . DB_SINGLE_QUOTE . $dateAction . DB_SINGLE_QUOTE . ", [stepTicketsDeliveredBy]=" . DB_SINGLE_QUOTE . $dateActionBy . DB_SINGLE_QUOTE . "";   
        }
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT ";
            $sqlStatement .= "* ";
                
        $sqlStatement .= "FROM " . DB_TABLE_TICKET_ASSIGNMENTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [AssignmentNumber]=" . $passInTicketAssignmentNumber  . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_ASSIGNMENTS . " ";
            
            $sqlStatement .= "SET ";
                $sqlStatement .= "[Status]=" . DB_SINGLE_QUOTE . $newTicketAssignmentStatus . DB_SINGLE_QUOTE . " ";
                $sqlStatement .= ", ". $sqlActionSet . " ";

            $sqlStatement .= "WHERE [AssignmentNumber]=" . $passInTicketAssignmentNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

        $logMessage = "SQL: User " .$_SESSION["user"] . " is updating ticket assignment status";
            logAction("UPD TKT ASSIGN STATUS", $logMessage);

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {
                $logMessage = "SQL Error: User " .$_SESSION["user"] . " is updating ticket assignment status";
                    logAction("UPD TKT ASSIGN STATUS", $logMessage);
                    echo "<br />SQL " . $sqlStatement . "";
                    die( print_r( sqlsrv_errors(), true) );
            }
               
        sqlsrv_free_stmt($sqlStatus);        
}

function updateTicketStatus($passInTicketRecordNumber, $newTicketRecordStatus)
{
    $logAction = "UPDATE TICKET STATUS";

    // Connect to database
    $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
    $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
    if ($databaseConnection === false) {
      logAction( $logAction, "Cannot connect to database." );
      http_response_code( 500 );
      die();
    }
        
    // Action and Table/View
    $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_DETAIL . " SET Status="  . DB_SINGLE_QUOTE . $newTicketRecordStatus . DB_SINGLE_QUOTE . " ";
    $sqlStatement .= "WHERE TicketRecordNumber=" . $passInTicketRecordNumber;

    // Execute the query
    $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
    if ($sqlStatus === false) {   
      logAction( $logAction, "Error while updating status of ticket $passInTicketRecordNumber" );
      http_response_code( 500 );
      die();
    }
               
    sqlsrv_free_stmt($sqlStatus);
    return true;
}

function updateTicketRequest($json) {
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT [TicketRequestNumber] FROM " . DB_TABLE_TICKET_REQUESTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [TicketRequestNumber]=" . $json->TicketRequestNumber . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_REQUESTS . " SET [Status]="  . DB_SINGLE_QUOTE . $newTicketRecordStatus . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= "WHERE [TicketRecordNumber]=" . $passInTicketRecordNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                echo "<br />SQL Statement 3:<br />" . $sqlStatement . "";
                die( print_r( sqlsrv_errors(), true) );
            }
               
        sqlsrv_free_stmt($sqlStatus);
        
        return "OK";
}

function updateTicketRequestBy($json)
{
    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT [RequestNumber] FROM " . DB_TABLE_TICKET_REQUESTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [RequestNumber]=" . $json->AssociatedTicketRequestNumber . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_REQUESTS. " SET [RequestedBy]="  . DB_SINGLE_QUOTE . $json->TransferToUser->userAccount . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= "WHERE [RequestNumber]=" . $json->AssociatedTicketRequestNumber . "; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                echo "<br />SQL Statement 3:<br />" . $sqlStatement . "";
                die( print_r( sqlsrv_errors(), true) );
            }
               
        sqlsrv_free_stmt($sqlStatus);
        
        return "OK";
}

function updateTicketAssignmentTo($json)
{
    // Pass in JSON object of the Ticket Assignment data
        // * Will only update the GuestAssociatedFirmAttorney to the TransferToUser value

    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        
    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        $sqlStatement .= "SELECT [AssignmentNumber] FROM " . DB_TABLE_TICKET_ASSIGNMENTS . " WITH (UPDLOCK) ";
            $sqlStatement .= "WHERE [AssignmentNumber]=" . $json->AssignmentNumber . "; ";

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_ASSIGNMENTS . " SET ";
            
            // Columns to Update
            $sqlStatement .= "[GuestAssociatedFirmAttorney]="  . DB_SINGLE_QUOTE . $json->TransferToUser->userAccount . DB_SINGLE_QUOTE . " ";
            
            // Where            
            $sqlStatement .= "WHERE [AssignmentNumber]=" . $json->AssignmentNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                echo "<br />SQL Statement 3:<br />" . $sqlStatement . "<br />";
                die( print_r( sqlsrv_errors(), true) );
            }
               
        sqlsrv_free_stmt($sqlStatus);    

}

function updateTicketAssignment($json)
{
    // Pass in JSON object that represents the whole Ticket Assignment record.
        // Updates ALL values in the Asssignment to the JSON values.

    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die("<pre>".print_r(sqlsrv_errors(), true));
        // Update Statements

        $formattedAssignmentNotes = str_replace( "'", "''", $json->AssignmentNotes );

            $sqlStatement .= "UPDATE " . DB_TABLE_TICKET_ASSIGNMENTS . " SET ";
            
            // Columns to Update
            $sqlStatement .= "[Status]=" . DB_SINGLE_QUOTE . $json->Status . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[AssociatedTicketRecordNumber]=" . $json->AssociatedTicketRecordNumber . "";
            $sqlStatement .= ",[AssociatedTicketRequestNumber]=" . $json->AssociatedTicketRequestNumber . "";
            $sqlStatement .= ",[AssociatedReasonCode]=" . DB_SINGLE_QUOTE . $json->AssociatedReasonCode . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[AssociatedTicketDeliveryType]=" . DB_SINGLE_QUOTE . $json->AssociatedTicketDeliveryType . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestAssociatedFirmAttorney]=" . DB_SINGLE_QUOTE . $json->GuestAssociatedFirmAttorney . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestClientNumber]=" . DB_SINGLE_QUOTE . $json->GuesetClientNumber . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestCompany]=" . DB_SINGLE_QUOTE . $json->GuestCompany . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestName]=" . DB_SINGLE_QUOTE . $json->GuestName . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestPhoneNumber]=" . DB_SINGLE_QUOTE . $json->GuestPhoneNumber . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[GuestEmail]=" . DB_SINGLE_QUOTE . $json->GuestEmail . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= ",[AssignmentNotes]=" . DB_SINGLE_QUOTE . $formattedAssignmentNotes . DB_SINGLE_QUOTE . " ";
            $sqlStatement .= ",[stepReviewDateTime]=" . DB_SINGLE_QUOTE . $json->stepReviewDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepReviewBy]=" . DB_SINGLE_QUOTE . $json->stepReviewBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderNeededDateTime]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderNeededDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderNeededBy]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderNeededBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderEnteredDateTime]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderEnteredDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderEnteredBy]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderEnteredBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderNeedsRevisionDateTime]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderNeedsRevisionDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderNeedsRevisionBy]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderNeedsRevisionBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderApprovedDateTime]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderApprovedDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderApprovedBy]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderApprovedBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderSentToVenueDateTime]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderSentToVenueDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepFoodOrderSentToVenueBy]=" . DB_SINGLE_QUOTE . $json->stepFoodOrderSentToVenueBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepTicketsReadyDateTime]=" . DB_SINGLE_QUOTE . $json->stepTicketsReadyDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepTicketsReadyBy]=" . DB_SINGLE_QUOTE . $json->stepTicketsReadyBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepTicketsDeliveredDateTime]=" . DB_SINGLE_QUOTE . $json->stepTicketsDeliveredDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepTicketsDeliveredBy]=" . DB_SINGLE_QUOTE . $json->stepTicketsDeliveredBy . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepCancelledDateTime]=" . DB_SINGLE_QUOTE . $json->stepCancelledDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepCancelledby]=" . DB_SINGLE_QUOTE . $json->stepCancelledby . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepInitialCreatedDateTime]=" . DB_SINGLE_QUOTE . $json->stepInitialCreatedDateTime . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[stepInitialCreatedBy]=" . DB_SINGLE_QUOTE . $json->stepInitialCreatedBy . DB_SINGLE_QUOTE . "";
            
            // Where            
            $sqlStatement .= "WHERE [AssignmentNumber]=" . $json->AssignmentNumber  . "; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
       
        if ($sqlStatus === false)
            {   
                echo "<br />SQL Statement 3:<br />" . $sqlStatement . "<br />";
                die( print_r( sqlsrv_errors(), true) );
            }
               
        sqlsrv_free_stmt($sqlStatus);    

}

function updateSystemUser($json)
{
    // Pass in JSON object that represents the whole Ticket Assignment record.
        // Updates ALL values in the Asssignment to the JSON values.

    // Connect to database
        $options = array(  "UID" => DB_USERNAME,  "PWD" => DB_PASSWORD,  "Database" => DB_NAME);
        $databaseConnection = sqlsrv_connect(DB_SERVERNAME, $options);

    // If we cannot get a connection lets terminate.
        if ($databaseConnection === false) 
            die(500);
    
        $logMessage = "Updating user account (inside function) for " . $json->userAccount . " to " . $json->userStatus . "";
            logAction("UPDATE USER", $logMessage);

    // Action and Table/View
        $sqlStatement  = "BEGIN TRAN ";

        /* // Action 
        $sqlStatement .= "SELECT ";

        // Columns
        $sqlStatement .= "[userAccount] ";
        $sqlStatement .= ",[userName] ";
        $sqlStatement .= ",[userStatus] ";
        $sqlStatement .= ",[memberOfEveryone] ";
        $sqlStatement .= ",[memberOfAssociate] ";
        $sqlStatement .= ",[memberOfPartner] ";
        $sqlStatement .= ",[memberOfSectionHead] ";
        $sqlStatement .= ",[memberOfTicketAdministration] ";
        $sqlStatement .= ",[memberOfSystemAdministration] ";
		
        // From 
            $sqlStatement .= "FROM " . DB_TABLE_SYSTEM_USERS . " WITH (UPDLOCK) "; 

        // Where
            $sqlStatement .= "WHERE [userAccount]=" . DB_SINGLE_QUOTE . $json->userAccount . DB_SINGLE_QUOTE . "; */

        // Update Statements

            $sqlStatement .= "UPDATE " . DB_TABLE_SYSTEM_USERS . " SET ";
            
            // Columns to Update
            $sqlStatement .= "[userName]=" . DB_SINGLE_QUOTE . $json->userName . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[userStatus]=" . DB_SINGLE_QUOTE . $json->userStatus . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[memberOfEveryone]=" . DB_SINGLE_QUOTE . $json->memberOfEveryone . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[memberOfAssociate]=" . DB_SINGLE_QUOTE . $json->memberOfAssociate . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[memberOfPartner]=" . DB_SINGLE_QUOTE . $json->memberOfPartner . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[memberOfSectionHead]=" . DB_SINGLE_QUOTE . $json->memberOfSectionHead . DB_SINGLE_QUOTE . "";
            $sqlStatement .= ",[memberOfTicketAdministration]=" . DB_SINGLE_QUOTE . $json->memberOfTicketAdministration . DB_SINGLE_QUOTE . "";
			$sqlStatement .= ",[memberOfSystemAdministration]=" . DB_SINGLE_QUOTE . $json->memberOfSystemAdministration . DB_SINGLE_QUOTE . " ";
            
            // Where            
            $sqlStatement .= "WHERE [userAccount]='" . $json->userAccount . "'; ";

        $sqlStatement .= "COMMIT TRAN";

    // Execute the update
 
       $sqlStatus = sqlsrv_query( $databaseConnection, $sqlStatement);
   
        if ($sqlStatus === false)
            {   
                $logMessage = "ERROR Updating user account (inside ususa) for " . $json->userAccount . " to " . $json->userStatus . "";
                    logAction("UPDATE USER ERR", $logMessage);
        
                die(500);
            }
   
		$logMessage = "Updated user account (inside function) for " . $json->userAccount . " to " . $json->userStatus . "";
			logAction("UPDATE USER", $logMessage);

        sqlsrv_free_stmt($sqlStatus);
       
}