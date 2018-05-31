<?php
		/* header('Location:hire.php');
		exit; */
/* die('hi'); */
if(isset($_POST['action']) && $_POST['action'] == 'send_contact_info'){
	$name = $_POST['name'];
	$email = $_POST['email'];
	$email_subject = $_POST['subject'];
	$email_body = $_POST['message'];
	
	/* // To send HTML mail, the Content-type header must be set */

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	/* // Create email headers */
	$headers .= 'From: '.$email."\r\n".
		'Reply-To: '.$email."\r\n" .
		'X-Mailer: PHP/' . phpversion();

	/* // Compose a simple HTML email message */
	$message = '<html><body>';
	$message .= '<b>Email&nbsp;&nbsp;:&nbsp;&nbsp;</b>'.$email.'<br/>';
	$message .= '<b>Name&nbsp;&nbsp;:&nbsp;&nbsp;</b>'.$name.'<br/>';
	$message .= '<b>Message&nbsp;&nbsp;:&nbsp;&nbsp;</b>'.$email_body.'<br/>';
	$message .= '</body></html>';
	$to = 'sushilkrpro@gmail.com';
	/* // send email */
	$mailsent = mail($to,$email_subject,$message,$headers);
	if(isset($mailsent)){
		header('Location:index.html');
		exit;
	}else{
		echo 'there is an error.';
		exit;
	}
}	



if(isset($_POST['send_email_with_attachment']) && $_POST['send_email_with_attachment'] == 1){
	/* $my_file = $_FILES["file1_input"]["name"];
	$my_path = "upload/"; */

	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$fullName = $first_name.' '.$last_name;
	$contactno = $_POST['phone'];
	$country = $_POST['country'];
	$email_from = $_POST['email'];
	$city = $_POST['city'];
	$address = $_POST['address'];
	$position = $_POST['position'];
	$message = $_POST['message'];
	
	 move_uploaded_file($_FILES["file1_input"]["tmp_name"],
      "upload/" . $_FILES["file1_input"]["name"]); 
	  
	$upload_folder = 'upload/';
	$name_of_uploaded_file = $_FILES["file1_input"]["name"];
	$path_of_uploaded_file = $upload_folder . $name_of_uploaded_file;
	$tmp_path = $_FILES["file1"]["tmp_name"];

	if(is_uploaded_file($tmp_path))
	{
	  if(!copy($tmp_path,$path_of_uploaded_file))
	  {
		$errors .= '\n error while copying the uploaded file';
	  }
	}
	
	
	$filename = $name_of_uploaded_file;
    $path = $upload_folder;
    $file = $path . "/" . $filename;
	
    require_once 'class.phpmailer.php';
	$mail = new PHPMailer();
	// Now you only need to add the necessary stuff
	 
	// HTML body
	 
	$body = "<b>Details</b> <br/>";
	$body .= "<b>Name : </b>$fullName<br/>";
	$body .= "<b>Email : </b>$email_from<br/>";
	$body .= "<b>Contactno : </b>$contactno<br/>";
	$body .= "<b>Country : </b>$country<br/>";
	$body .= "<b>City : </b>$city<br/>" ;
	$body .= "<b>Address : </b>$address<br/>" ;
	$body .= "<b>Message : </b>$message<br/><br/>" ;
	 
	// And the absolute required configurations for sending HTML with attachement
	 
	$mail->AddAddress("kapoor.amit15@gmail.com", "My-webpage Website");
	$mail->Subject = "Query For Hiring ";
	$mail->FromName = $fullName;
	$mail->IsHTML(true);
	$mail->MsgHTML($body);
	$mail->AddAttachment($file);
	if(!$mail->Send()) {
		$_SESSION['mail_success'] = 'There is server error. please check later.';
        header('Location:hire.php');
		exit();
	}else{
		session_start();
		$_SESSION['mail_success'] = 'Your mail has been sent successfully.';
		header('Location:hire.php');
		exit();
	}
}


	
    