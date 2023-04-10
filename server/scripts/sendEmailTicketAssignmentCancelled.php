<?php
header("Access-Control-Allow-Origin: *");

// sendEmailTicketAssignmentCancelled.php
//		Used to send a email notice to a user that their ticket assignment has been cancelled.
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

require '../vendor/phpmailer/PHPMailerAutoload.php';
include("mailConstants.php");

$argumentMailTo = $_GET['MailTo'];
    $argumentAssignmentNumber = $_GET['AssignmentNumber'];

// Check to make sure arguments have been passed in, if not error out.

if ($argumentMailTo == '')	
	{	echo "ERROR0001: No Mail To Parameter Specified."; die();	}

if ($argumentAssignmentNumber == '')	
	{	echo "ERROR0002: No Assignment Number Parameter Specified."; die();	}

// Set up the mail arguments

$mailTo = $argumentMailTo;
    $mailFrom = "jabucar@huntinglake.com";        
        $mailSubject = "Event Ticketing System: Ticket Assignment #" + $argumentAssignmentNumber + " has been cancelled.";
            $mailMessage = "Hello! This is a test email message.   Had this been an actual email you would get information on the ticket assignment that was cancelled.";

            $mail = new PHPMailer();    
            $mail->IsHTML(true);
            $mail->SetFrom($mailFrom);
            $mail->AddReplyTo('jbucar@walterhav.com'); //set from & reply-to headers
            $mail->AddAddress($mailTo); //set destination address
            
            $mail->Subject=$mailSubject; //set subject
            $mail->Body=$mailMessage; //set body content
            
            $mail->AltBody = "Can't see this message? Please view in HTML\n\n";
            $mail->Send();
           
    if(!$mail->Send())
        {
                echo "Mail not sent";
        }
    else
        {
                echo "Mail sent";
        }
?>