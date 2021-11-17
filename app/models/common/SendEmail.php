<?php
/**
 * 
 */
class SendEmail
{
		function send_to_multi($param){
		$status=false;
		$message='message from send mail';
		$response=null;

		$InvalidDataMessage="";
		$dataValidation=true;
		//-----------check if recivers list is send
		if(isset($param['recipient'])){
			$recipient=$param['recipient'];


			if(count($recipient)<1){
				$InvalidDataMessage="please provide atleast one recipient";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide recipient list";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		$subject="";

		if(isset($param['subject'])){
			$subject=senetize_input($param['subject']);
		}


		$body="";

		if(isset($param['body']) && $param['body']!=""){
			$body=$param['body'];
		}else{
			$InvalidDataMessage="empty email body";
			$dataValidation=false;
			goto ValidationChecker;			
		}


		ValidationChecker:

		if($dataValidation){
			
			define ('EMAIL','agile@sigealogistics.com');
			define ('PASS','Admin@786#');

			include_once APPROOT.'/models/common/PhpMailer.php';
			$mail = new PHPMailer;

			//$mail->SMTPDebug = 4;                               // Enable verbose debug output

			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = 'test.sigealogistics.com';  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = EMAIL;                 // SMTP username
			$mail->Password = PASS;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to

			$mail->setFrom(EMAIL, 'Agile');

			foreach ($recipient as $rec) {
				$mail->addAddress($rec); 
			}

		    // Add a recipient
			$mail->addAddress('freon.agile@gmail.com','ADMIN');               // Name is optional
			$mail->addReplyTo(EMAIL, 'Information');
			//$mail->addCC('cc@example.com');
			//$mail->addBCC('bcc@example.com');
			//$mail->addAttachment($file_genrated);         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$message= 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
				$status=true;
				$message='Message has been sent';
			}



		}else{
			$message=$InvalidDataMessage;
		}

		ExecutionChecker:
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}
	
	function send($param){
		$status=false;
		$message='message from send mail';
		$response=null;

		$InvalidDataMessage="";
		$dataValidation=true;
		//-----------check if recivers list is send
		if(isset($param['recipient'])){
			$recipient=$param['recipient'];


					if(!filter_var($recipient, FILTER_VALIDATE_EMAIL)){
						$InvalidDataMessage="Invalid email";
						$dataValidation=false;
						goto ValidationChecker;	
					}

		}else{
			$InvalidDataMessage="Please provide recipient list";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		$subject="";

		if(isset($param['subject'])){
			$subject=senetize_input($param['subject']);
		}


		$body="";

		if(isset($param['body']) && $param['body']!=""){
			$body=$param['body'];
		}else{
			$InvalidDataMessage="empty email body";
			$dataValidation=false;
			goto ValidationChecker;			
		}


		ValidationChecker:

		if($dataValidation){
			
			define ('EMAIL','agile@sigealogistics.com');
			define ('PASS','Admin@786#');

			include_once APPROOT.'/models/common/PhpMailer.php';
			$mail = new PHPMailer;

			//$mail->SMTPDebug = 4;                               // Enable verbose debug output

			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = 'test.sigealogistics.com';  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = EMAIL;                 // SMTP username
			$mail->Password = PASS;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to

			$mail->setFrom(EMAIL, 'Agile');
			$mail->addAddress($recipient);     // Add a recipient
			$mail->addAddress('freon.agile@gmail.com','ADMIN');               // Name is optional
			$mail->addReplyTo(EMAIL, 'Information');
			//$mail->addCC('cc@example.com');
			//$mail->addBCC('bcc@example.com');
			//$mail->addAttachment($file_genrated);         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				$message= 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
				$status=true;
				$message='Message has been sent';
			}



}else{
	$message=$InvalidDataMessage;
}

ExecutionChecker:
return ['status'=>$status,'message'=>$message,'response'=>$response];
}
/*

	function send($param){
		$status=false;
		$message='message from send mail';
		$response=null;

		$InvalidDataMessage="";
		$dataValidation=true;
		//-----------check if recivers list is send
		if(isset($param['receivers'])){
			$receivers=$param['receivers'];
		}else{
			$InvalidDataMessage="Please provide receivers list";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		//-----------check if recivers list is an array
		if(!is_array($receivers)){
			$InvalidDataMessage="Please provide receivers ";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		//-----------check if recivers list contains atleast one recivers
		if(count($receivers)<1){
			$InvalidDataMessage="Please provide atleast one receiver";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		
		if(count($receivers)<1){
			$InvalidDataMessage="Please provide atleast one receiver";
			$dataValidation=false;
			goto ValidationChecker;		
		}



		ValidationChecker:

		if($dataValidation){
			$message="ok send mail";
		}else{
			$message=$InvalidDataMessage;
		}

		ExecutionChecker:
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}*/

}
?>