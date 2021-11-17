<?php

/**
 * 
 */
class Validation
{

	function is_valid_api_token($token){
		$status=false;
		$message=null;
		$response=[];
		if($token!=""){

			$token=mysqli_real_escape_string($GLOBALS['con'],$token);
			$q=mysqli_query($GLOBALS['con'],"SELECT `token_id` FROM `api_o_tokens` LEFT JOIN `api_o_consumers` ON `api_o_tokens`.`token_consumer_id_fk`=`api_o_consumers`.`aoc_id`  WHERE `token`='$token' AND `token_id_status`='ACT' AND `aoc_id_status`='ACT'");
			if(mysqli_num_rows($q)==1){
				$result=mysqli_fetch_assoc($q);
			  		//---enter a call record in api call counter
				$datetime=date('Y-m-d H:i:s');
			
				mysqli_query($GLOBALS['con'],"INSERT INTO `api_o_token_call_counter`( `call_token_id_fk`, `call_datetime`) VALUES ('".$result['token_id']."','$datetime')");


				$status=true;
				$response['token_id']=$result['token_id'];
			}else{
				$status=false;
				$message='Invalid token';
			}
		}else{
			$message='invalid api token';
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}
	
	function is_valid_app($appId){

		$r=[];
		if($appId=='myappkey'){
			$r['status']=true;
			$r['message']="Valid";			
		}else{
			$r['status']=false;
			$r['message']="Invalid App";
		}

		return $r;
	}


	function is_valid_user($key){
		$status=false;
		$message=null;
		$response=[];
		if($key!=""){
			$key=mysqli_real_escape_string($GLOBALS['con'],$key);
			$Q=mysqli_query($GLOBALS['con'],"SELECT `ulog_id`, `ulog_user_id_fk`,`user_type` FROM `ulogs` LEFT JOIN `utab` ON `utab`.`user_id`=`ulogs`.`ulog_user_id_fk` WHERE `ulog_key`='$key' AND `ulog_status`='ACT'");
			if(mysqli_num_rows($Q)==1){
				$result=mysqli_fetch_assoc($Q);
				$status=true;
				$curusrid=$response['user_id']=$result['ulog_user_id_fk'];






								//--get privilages
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






				$response['user_priv']=$priv_array;
			}else{
				$status=false;
				$message='Invalid key';
			}
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}
	
	
	function is_valid_driver($key){
		$status=false;
		$message=null;
		$response=[];
		if($key!=""){
			$key=mysqli_real_escape_string($GLOBALS['con'],$key);
			$Q=mysqli_query($GLOBALS['con'],"SELECT `log_driver_id_fk` FROM `driver_logs` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_logs`.`log_driver_id_fk` WHERE `log_key`='$key' AND `log_status`='ACT' AND `driver_status`='ACT'");
			if(mysqli_num_rows($Q)==1){
				$result=mysqli_fetch_assoc($Q);
				$status=true;
				$response['driver_id']=$result['log_driver_id_fk'];
			}else{
				$status=false;
				$message='Invalid key';
			}
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}

}

?>