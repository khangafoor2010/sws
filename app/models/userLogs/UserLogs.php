

<?php

/**
 * 
 */
class UserLogs
{


	function set_new_password($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['token']) && $param['token']!=""){
			$token=senetize_input($param['token']);
			$time=time();
			$val_q=mysqli_query($GLOBALS['con'],"SELECT `token_user_id_fk`,`token_expiry_on`, `token_status` FROM `user_password_reset_token` WHERE `token`='$token'");
			//--------------validate token
			if(mysqli_num_rows($val_q)==1){
				$token_dtl=mysqli_fetch_assoc($val_q);


				if($time>$token_dtl['token_expiry_on']){
				$InvalidDataMessage="Link has been expired";
				$dataValidation=false;
				goto ValidationChecker;

				}

				if($token_dtl['token_status']=='USED'){
				$InvalidDataMessage="Link has been used";
				$dataValidation=false;
				goto ValidationChecker;

				}			
				if($token_dtl['token_status']!='ACTIVE'){
				$InvalidDataMessage="Link has been de-activated";
				$dataValidation=false;
				goto ValidationChecker;

				}

				$user_id=$token_dtl['token_user_id_fk'];

			}else{
				$InvalidDataMessage="invalid token";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide token";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		if(isset($param['new_password']) && $param['new_password']!=""){
			$new_password=senetize_input($param['new_password']);
		}else{
			$InvalidDataMessage="Please provide new password";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		ValidationChecker:
		if($dataValidation){
			$executionMessage="";
			$execution=true;
			$new_password=password_hash($new_password, PASSWORD_DEFAULT);
			$set_new_password_q=mysqli_query($GLOBALS['con'],"UPDATE `utab` SET `user_password`='$new_password' WHERE `user_id`='$user_id'");
			if($set_new_password_q){
				$status=true;
				$message="Password updated successfully";
			}else{
				$message=SOMETHING_WENT_WROG;
			}

		}else{
			$message=$InvalidDataMessage;
		}

		ExecutionChecker:
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}




	function forget_password($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['username']) && $param['username']!=""){
			$username=senetize_input($param['username']);
		}else{
			$InvalidDataMessage="Please provide username";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		ValidationChecker:
		if($dataValidation){
			$executionMessage="";
			$execution=true;
			//----------validate username
			$get_user_q=mysqli_query($GLOBALS['con'],"SELECT `user_id`,`user_email` FROM `utab` WHERE user_status='ACT' AND `user_code`='$username'  AND `user_type`='USR'");

			if(mysqli_num_rows($get_user_q)==1){
				$res=mysqli_fetch_assoc($get_user_q);
				$user_id=$res['user_id'];
				$time=time();
				$expiry=$time+3600;
		    	$token=$Enc->safeuseren($time.'-'.$user_id);

				$user_email=$Enc->dec_mail($res['user_email']);
				if($user_email==false){
					$message="No email id is linked to this account";
					$execution=false;
					goto ExecutionChecker;	
				}

				///---------create token for password reset
				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `user_password_reset_token`( `token_user_id_fk`, `token`, `token_added_on`, `token_expiry_on`, `token_status`) VALUES ('$user_id','$token','$time','$expiry','ACTIVE')");
				if(!$insert){
					$message=SOMETHING_WENT_WROG.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto ExecutionChecker;
				}


$body="<html>
<head>
	<style type='text/css'>
		body{

			background:#f1f1f1;
		}
		html{
			font-family: calibri
		}
		main{
			width: 90%;
			max-width: 800px;
			margin:auto;
			border-radius: 8px;
			overflow: hidden;
			border:3px solid #042854;
		}
		.head{
			background:#042854;
			color: white;
			text-align: center;
			padding: 10px;
		}
		.footer{
			padding: 7px;
			text-align: center;
			background:#042854;
			color: white;
		}
	</style>		
</head>
<body>
	<main>
		<section class='head'>
			<h1>Freon Group</h1>
		</section>

		<section style='padding: 14px'>
			<p>A request to reset password was received from your Agile Account</p>

		<br><br>
		<a style='font-weight: bolder;' href='https://agile.sigealogistics.com/login-set-new-password?token=$token'>Use this link to reset your password.</a>
		<br><br><br>
		<p>
			Note: This link is valid for 30 minutes from the time it was sent to you and can be used to change your password only once.
		</p>
		<p>
			IMP: If you have not initiated this request, report it to us immediately.
		</p>
		<br>
		Support
		For any support with respect to your relationship with us you can always contact us directly using the following Information.
		<br><br>
		<i><b>Technical Support:</b></i><br>
		email: freon.agile@gmail.com
		<br><br><br><br><br>		
		</section>


		<section class='footer'> www.freongroup.com</section>
	</main>

</body>
</html>";




				include_once APPROOT.'/models/common/SendEmail.php';
				$SendEmail=new SendEmail;
				$pa=[
					'recipient'=>$user_email,
					'subject'=>'Agile - Reset Password',
					'body'=>$body,
				];

				$EmailRes=$SendEmail->send($pa);
				if($EmailRes['status']){
					$status=true;
					$message="A link to reset password has been sent on your registered email.";
				}else{
					$message=$EmailRes['message'];
				}

			}else{
				$message="Invalid username";
				$execution=false;
				goto ExecutionChecker;	
			}
		}else{
			$message=$InvalidDataMessage;
		}

		ExecutionChecker:
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}

	function login($param){

		$status=false;
		$message=null;
		$response=null;

		//check if username is set
		if(isset($param['username'])){

			//check if password is set
			if(isset($param['password'])){
				$password=mysqli_real_escape_string($GLOBALS['con'],$param['password']);

				include_once APPROOT.'/models/common/Enc.php';
				$Enc= new Enc;

				$username=mysqli_real_escape_string($GLOBALS['con'],$param['username']);
				//------------check for employee credential
				if($username!=''){
					$getcandi="SELECT `user_id`,`user_password`,`user_name`,`user_type` FROM `utab` WHERE user_status='ACT' AND `user_code`=? ";
					if($stmt = 	mysqli_prepare($GLOBALS['con'], $getcandi)){
						mysqli_stmt_bind_param($stmt, "s", $username);
						mysqli_stmt_execute($stmt);
						$results=mysqli_stmt_get_result($stmt);
						$rows= mysqli_num_rows($results);

						if($rows=='1'){

							$result=mysqli_fetch_assoc($results);
							if(password_verify(mysqli_real_escape_string($GLOBALS['con'],$param['password']), $result['user_password'])){		
								$curusrid=$result['user_id'];


								$priv_array='';
								if($result['user_type']=='ADM'){
									$get_priv_admin=mysqli_query($GLOBALS['con'],"SELECT `ro_code` FROM `roles` WHERE `ro_status`='AC'");
									$priv_array='PADMIN';
									while ($get_priv_admin_result=mysqli_fetch_assoc($get_priv_admin)) {
										$priv_array.=','.$get_priv_admin_result['ro_code'];
									}
								}else{

									//--get privilages
									$get_priv=mysqli_query($GLOBALS['con'],"SELECT `group_roles` FROM `users_roles_groups_junction` LEFT JOIN `roles_groups` ON `roles_groups`.`group_id`=`users_roles_groups_junction`.`urgj_group_roles_id_fk` WHERE `urgj_user_id_fk`='$curusrid' AND `urgj_status`='ACT'");
									while ($get_priv_result=mysqli_fetch_assoc($get_priv)) {
										if($get_priv_result['group_roles']!=""){
											$priv_array.=','.$get_priv_result['group_roles'];
										}

									}
								}


								//--/get privilages

								//------Get Profile id
								//$profileQ=mysqli_query($GLOBALS['con'],"SELECT `prof_id` FROM `profile` WHERE prof_user_id_fk='$curusrid' AND prof_status='AC'");
								$time=time();
								$message=$priv_array;
								$status=true;
								$whoim='';
								$whoim .=$curusrid;
								$whoim .=','.$time;
								$response['userkey']=$key=$Enc->safeuseren($whoim);
								$response['username']=$result['user_name'];
								$response['userPriv']=$priv_array;

								mysqli_query($GLOBALS['con'],"INSERT INTO `ulogs`(`ulog_user_id_fk`, `ulog_key`, `ulog_add_time`, `ulog_status`) VALUES ('$curusrid','$key','$time','ACT')");	


							}else{
								$message='Invalid Password';
								$status=false;
							}

						}else{
							$message="Invalid user";
						}		
						/////user has been verfied now direct him to profile as per account type

					}else{
						$message='Invalid Mobile Number';
						$status=false;
					}

				}

			}else{
				$message="Please provide password";
			}			

		}else{
			$message="Please provide username";
		}

		$r=[];	
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;

		return $r;
	}

}

?>