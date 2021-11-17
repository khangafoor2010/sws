<?php
/**
 * 
 */
class Profile
{
	
	function reset_password($param){
		$status=false;
		$message=null;
		$response=null;
		$USERID=USER_ID;
		$time=time();

					//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['new_password'])){
				$new_password=mysqli_real_escape_string($GLOBALS['con'],$param['new_password']);
		}else{
			$InvalidDataMessage="Please provide truck id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		ValidationChecker:
		if($dataValidation){
			//------------update password
			$new_password=password_hash($new_password, PASSWORD_DEFAULT);
			$update=mysqli_query($GLOBALS['con'],"UPDATE `utab` SET `user_password`='$new_password' WHERE `user_id`='$USERID' AND `user_status`='ACT'");
			if($update){
				$status=true;
				$message="Password updated successfully";
			}else{
				$message=SOMETHING_WENT_WROG;
			}
			//-----------/update password
		}else{
			$message=$InvalidDataMessage;
		}



		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}
}
?>