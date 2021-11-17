<?php
/**
 * 
 */
class DaDriverSettings{
	
		function password_reset($param){
		$status=false;
		$message=null;
		$response=null;
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$DRIVER_ID=DRIVER_ID;
				$time=time();
			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";

				if(isset($param['password']) && $param['password']!=""){
					$password=mysqli_real_escape_string($GLOBALS['con'],$param['password']);
				}else{
					$InvalidDataMessage="Please provide password";
					$dataValidation=false;
				}



				if($dataValidation){

					$password=password_hash($password, PASSWORD_DEFAULT);
					$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_password`='$password' WHERE `driver_id`='$DRIVER_ID'");
					if($update){
						$status=true;
						$message="Password updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
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