<?php

/**
 * 
 */
class DaDriverLogs
{
	
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
					$getcandi="SELECT `driver_id`, `driver_password` FROM `drivers` WHERE `driver_status`='ACT' AND `driver_code`=? ";
					if($stmt = 	mysqli_prepare($GLOBALS['con'], $getcandi)){
					   	mysqli_stmt_bind_param($stmt, "s", $username);
					   	mysqli_stmt_execute($stmt);
					   	$results=mysqli_stmt_get_result($stmt);
						$rows= mysqli_num_rows($results);
					
						if($rows=='1'){
						
							$result=mysqli_fetch_assoc($results);
							if(password_verify(mysqli_real_escape_string($GLOBALS['con'],$param['password']), $result['driver_password'])){		
								$curusrid=$result['driver_id'];
				

								//------Get Profile id
								//$profileQ=mysqli_query($GLOBALS['con'],"SELECT `prof_id` FROM `profile` WHERE prof_user_id_fk='$curusrid' AND prof_status='AC'");
									$time=time();
									$message='Logged in succesfuly';
									$status=true;
									$whoim='';
									$whoim .=$curusrid;
									$whoim .=','.$time;
									$response['driverKey']=$key=$Enc->safeuseren($whoim);
									$response['driverName']=$username;




									mysqli_query($GLOBALS['con'],"INSERT INTO `driver_logs`(`log_driver_id_fk`, `log_key`, `log_add_time`, `log_status`) VALUES ('$curusrid','$key','$time','ACT')");	
								

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