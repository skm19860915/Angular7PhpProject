<?php

header("Access-Control-Allow-Origin: *");

// sendEmailNotification.php
//		Used to send all the specific email notifications in the system.
//      Also contains the routines to send Outlook iCAL invitations for users as well.
//
//      Function and Paramaters:
//
//          - TicketAssignmentApproved()
//          - TicketAssignmentCancelled()
//          - TicketAssignmentChanged()
//  
//          - TicketRequestCancelled()
//          - TicketRequestCreated()
//          - TicketRequestNotApproved()
//
//          - TicketToEventAdded()
//          - TicketStatusUpdate()
//          - TicketRequestStatusUpdate()
//          - TicketApprovalStatusUpdate()
//
//          - sendIcalEvent(from name,from address,to name, to address, start time, end time, subject description, location)
//          - sendTheEmail($From, $To, $ReplyTo,$Subject,$Message)

// Walter | Haverfield
// 
// Author:  Joseph Bucar
//
// Revision History
//
// Date:            By:                 Summary of Changes:
// -----------------------------------------------------------------------------------------------------------------------------------------------
// 3/9/2018         JAB                 Initial creation

require '../vendor/phpmailer/PHPMailerAutoload.php';

include('mailConstants.php');
include('addLogMessage.php');

function sendIcalEvent($from_name, $from_address, $to_name, $to_address, $startTime, $endTime, $subject, $description, $location)
{
        $domain = 'walterhav.com';
    
        //Create Email Headers
        $mime_boundary = "----Meeting Booking----".MD5(TIME());
    
        $headers = "From: ".$from_name." <".$from_address.">\n";
        $headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
        $headers .= "Content-class: urn:content-classes:calendarmessage\n";
        
        //Create Email Body (HTML)
        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= "<html>\n";
        $message .= "<body>\n";
        $message .= '<p>Dear '.$to_name.',</p>';
        $message .= '<p>'.$description.'</p>';
        $message .= "</body>\n";
        $message .= "</html>\n";
        $message .= "--$mime_boundary\r\n";
    
        $ical = 'BEGIN:VCALENDAR' . "\r\n" .
        'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
        'VERSION:2.0' . "\r\n" .
        'METHOD:REQUEST' . "\r\n" .
        'BEGIN:VTIMEZONE' . "\r\n" .
        'TZID:Eastern Time' . "\r\n" .
        'BEGIN:STANDARD' . "\r\n" .
        'DTSTART:20091101T020000' . "\r\n" .
        'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
        'TZOFFSETFROM:-0400' . "\r\n" .
        'TZOFFSETTO:-0500' . "\r\n" .
        'TZNAME:EST' . "\r\n" .
        'END:STANDARD' . "\r\n" .
        'BEGIN:DAYLIGHT' . "\r\n" .
        'DTSTART:20090301T020000' . "\r\n" .
        'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
        'TZOFFSETFROM:-0500' . "\r\n" .
        'TZOFFSETTO:-0400' . "\r\n" .
        'TZNAME:EDST' . "\r\n" .
        'END:DAYLIGHT' . "\r\n" .
        'END:VTIMEZONE' . "\r\n" .	
        'BEGIN:VEVENT' . "\r\n" .
        'ORGANIZER;CN="'.$from_name.'":MAILTO:'.$from_address. "\r\n" .
        'ATTENDEE;CN="'.$to_name.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:'.$to_address. "\r\n" .
        'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
        'UID:'.date("Ymd\TGis", strtotime($startTime)).rand()."@".$domain."\r\n" .
        'DTSTAMP:'.date("Ymd\TGis"). "\r\n" .
        'DTSTART;TZID="Eastern Time":'.date("Ymd\THis", strtotime($startTime)). "\r\n" .
        'DTEND;TZID="Eastern Time":'.date("Ymd\THis", strtotime($endTime)). "\r\n" .
        'TRANSP:OPAQUE'. "\r\n" .
        'SEQUENCE:1'. "\r\n" .
        'SUMMARY:' . $subject . "\r\n" .
        'LOCATION:' . $location . "\r\n" .
        'CLASS:PUBLIC'. "\r\n" .
        'PRIORITY:5'. "\r\n" .
        'BEGIN:VALARM' . "\r\n" .
        'TRIGGER:-PT15M' . "\r\n" .
        'ACTION:DISPLAY' . "\r\n" .
        'DESCRIPTION:Reminder' . "\r\n" .
        'END:VALARM' . "\r\n" .
        'END:VEVENT'. "\r\n" .
        'END:VCALENDAR'. "\r\n";
        $message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST'."\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $ical;
    
        $mailsent = mail($to_address, $subject, $message, $headers);
    
        return ($mailsent)?(true):(false);
}

function sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage)
{
  
    $mail = new PHPMailer;
		
	$mail->isSMTP();                                      	    // Set mailer to use SMTP
        $mail->Host = "WHMAIL03.WALTERHAV.COM";                 // Specify main and backup SMTP servers
        // $mail->SMTPDebug = 2;
		$mail->SMTPAuth = true;                                 // Enable SMTP authentication
		$mail->Username = "wheventticketing@walterhav.com";         // SMTP username
		$mail->Password = "Tickets@1301";                       // SMTP password
		$mail->Port = 25;                                       // TCP port to connect to
		// $mail->isHTML(true);                                    // Set email format to HTML
		$mail->CharSet = 'UTF-8';                               // Fixes encoding problems

    $mail->setFrom('wheventticketing@walterhav.com','Event Ticketing System');

    // RECIPIENTS

		$sendToAddresses = explode(",", $argumentMailTo);
            foreach($sendToAddresses as $sendToAddress)
            {
                $mail->AddAddress($sendToAddress); //set destination address
            }

        $logMessage = "Attempt to send email to " . $sendToAddresses . "";
            logAction("SEND EMAIL", $logMessage);
      
    // E-MAIL PARAMETERS

        // Lets append a notice to the email to not reply to this email
        $argumentMessage = "<b>** Do not reply to this message as this mailbox is not monitored. **</b><br /><br >" . $argumentMessage;

        $mail->Subject=$argumentSubject; //set subject
        $mail->MsgHTML('' . $argumentMessage . '');
        // $mail->Body="This is a test message"; // $argumentMessage; //set body content
		$mail->AltBody = 'No HTML Body Goes here';
    
    // SENDING PROCESS
	
	if(!$mail->Send())
        {
            $logMessage = "Attempt to send email thru sendEmailNotification failed.  From Server " . EXCHANGE_SERVER_NAME . " From: " . $argumentFrom . " | To: " . $argumentMailTo . " | Subject: " . $argumentSubject . " | Body: " . $argumentMessage . " | User: " . ETS_EMAIL_DEFAULT_FROM_ACCOUNT . " (" . ETS_EMAIL_DEFAULT_FROM_PASSWORD . ")" . " | Error: " . $mail->ErrorInfo;
            
					logAction("SEND EMAIL FAILED", $logMessage);
				
               return 'NOT SENT';
        }
    else
        {
			$logMessage = "Email notification sent thru Server " . EXCHANGE_SERVER_NAME . " | From: " . $argumentFrom . " | To: " . $argumentMailTo . " | Subject: " . $argumentSubject . "";
					logAction("SEND EMAIL NOTIFICATION", $logMessage);
				
               return true;
        }
}

// ===================================================================================================
// Ticket Assignment emails
// -------------------------

function sendEmailTicketsAssigned($argumentMailTo, $argumentTicketRequestNumber, $argumentSeatsDetail)
{
    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
        {	echo "ERROR1021: No Mail To Parameter Specified."; die();	}

    if ($argumentTicketRequestNumber == '')	
        {	echo "ERROR1022: No Request Number Parameter Specified."; die();	}
    
    if ($argumentSeatsDetail == '')	
        {	echo "ERROR1023: No Seats Detail Specified."; die();	}
            
    // Mail settings
        $argumentFrom = "jbucar@walterhav.com";
            $argumentReplyTo = $mailFrom;
                $argumentSubject = "Event Ticketing System: Tickets have been assigned to you to use from Ticket Request #" . $argumentTicketRequestNumber . ".";
                        $argumentMessage = "This is a notification that Tickets have been assigned to you.";

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo, $argumentSubject, $argumentMessage);
          
}

function sendEmailTicketAssignmentTransferredFrom($argumentMailTo, $argumentTicketRecordNumber)
{

    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
    {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}

if ($argumentTicketRecordNumber == '')	
    {	echo "ERROR1002: No Ticket Record Number Parameter Specified."; die();	}
    
// Mail settings
    $argumentFrom = "jbucar@walterhav.com";
        $argumentReplyTo = $mailFrom;
            $argumentSubject = "Event Ticketing System: Your Ticket Assignment Record for Ticket #" . $argumentTicketRecordNumber . " has been transferred.";
                    $argumentMessage = "Your ticket assignment has been transferred to another user and the Ticket Request has also been updated with that users information.";

// Call the mail send function
$mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
       

}

function sendEmailTicketAssignmentTransferredTo($argumentMailTo, $argumentTicketRecordNumber)
{

    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
    {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}

if ($argumentTicketRecordNumber == '')	
    {	echo "ERROR1002: No Ticket Record Number Parameter Specified."; die();	}
    
// Mail settings
    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
            $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": A Ticket Assignment for Ticket # " . $argumentTicketRecordNumber . " has been transferred to you.";
                    $argumentMessage = "The ticket assignment has been transferred to you for your use and Ticket Request has also been updated with your information.";
                    
// Call the mail send function
$mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
    
}

function sendEmailTicketAssignmentCancelled($argumentMailTo, $argumentTicketAssignmentNumber)
{
    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
        {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}

    if ($argumentTicketAssignmentNumber == '')	
        {	echo "ERROR1002: No Assignment Number Parameter Specified."; die();	}
        
    // Mail settings
        $argumentFrom = "jbucar@walterhav.com";
            $argumentReplyTo = $mailFrom;
                $argumentSubject = "Event Ticketing System: Ticket Assignment #" . $argumentTicketAssignmentNumber . " has been cancelled..";
                        $argumentMessage = "This is a notification that a Ticket Assignment has been cancelled.";

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
         
}

function sendEmailTicketAssignmentsInit($argumentMailTo)
{
    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
        {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}
        
    // Mail settings
    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
            $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": Ticket Assignments Reinitialize has been executed.";
                $argumentMessage = "This is a notification that the Ticket Assignments have been cleaned and the Ticket Detail Statuses updated to Available.";

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
           
}

// ===================================================================================================
// Ticket Request emails
// ---------------------

function sendEmailTicketRequestCreated($argumentsMai)
{
     // Parse arguments to send mail:
    //  $json->MailFrom, $json->ReplyTo,  $json->MailTo, $json->Subject, $json->MessageBody

    $json = json_decode($argumentsMail);
    
    // Check to make sure arguments have been passed in, if not error out.
            
    // Mail settings

    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentMailTo = $json->MailTo;
            $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
                $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": " . $json->Subject . "";
                    $argumentMessage = $json->MessageBody;

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo, $argumentSubject, $argumentMessage);
           
}

function sendEmailTicketRequestCancelled($argumentMailTo, $argumentTicketRequestNumber)
{
    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
        {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}

    if ($argumentTicketRequestNumber == '')	
        {	echo "ERROR1002: No Request Number Parameter Specified."; die();	}
        
    // Mail settings
        $argumentFrom = "jbucar@walterhav.com";
            $argumentReplyTo = $argumentFrom;
                $argumentSubject = "Event Ticketing System: Ticket Request #" . $argumentTicketRequestNumber . " has been cancelled..";
                        $argumentMessage = "This is a notification that a Ticket Request has been cancelled.";

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
         
}

function sendEmailTicketRequestsInit($argumentMailTo)
{
    // Check to make sure arguments have been passed in, if not error out.

    if ($argumentMailTo == '')	
        {	echo "ERROR1001: No Mail To Parameter Specified."; die();	}
        
    // Mail settings
    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
            $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": Ticket Request Reinitialize has been executed.";
                $argumentMessage = "This is a notification that the Ticket Requests have been cleaned and the Ticket Detail Statuses updated to Available.";

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo,$argumentSubject,$argumentMessage);
         
}


// ===================================================================================================
// Event Ticket Maintenance emails
// -------------------------------

function sendEmailTicketsCreatedForEvent($argumentsMail)
{
    /// Parse arguments to send mail:
    //  $json->MailFrom, $json->ReplyTo,  $json->MailTo, $json->Subject, $json->MessageBody

    $json = json_decode($argumentsMail);
    
    // Check to make sure arguments have been passed in, if not error out.
            
    // Mail settings

    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentMailTo = $json->MailTo;
            $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
                $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": " . $json->Subject . "";
                    $argumentMessage = $json->MessageBody;

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo, $argumentSubject, $argumentMessage);
           
}

function sendEmailTicketsUpdated($argumentsMail)
{
    /// Parse arguments to send mail:
    //  $json->MailFrom, $json->ReplyTo,  $json->MailTo, $json->Subject, $json->MessageBody

    $json = json_decode($argumentsMail);
    
    // Check to make sure arguments have been passed in, if not error out.
            
    // Mail settings

    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentMailTo = "" . ETS_EMAIL_NOTIFICATION_ADDRESS . "";
            $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
                $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": " . $json->Subject . "";
                    $argumentMessage = $json->MessageBody;

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo, $argumentSubject, $argumentMessage);
           
}

// ===================================================================================================
// Event Maintenance emails
// -------------------------

function sendEmailEventCreated($argumentsMail)
{
    // Parse arguments to send mail:
    //  $json->MailFrom, $json->ReplyTo,  $json->MailTo, $json->Subject, $json->MessageBody

    $json = json_decode($argumentsMail);
    
    // Check to make sure arguments have been passed in, if not error out.
            
    // Mail settings

    $argumentFrom = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
        $argumentMailTo = $json->MailTo;
            $argumentReplyTo = "" . ETS_EMAIL_DEFAULT_FROM_ADDRESS . "";
                $argumentSubject = "" . ETS_EMAIL_SUBJECT_PREPEND . ": " . $json->Subject . "";
                    $argumentMessage = $json->MessageBody;

    // Call the mail send function
    $mailStatus = sendTheEmail($argumentFrom, $argumentMailTo, $argumentReplyTo, $argumentSubject, $argumentMessage);
          
}


?>
