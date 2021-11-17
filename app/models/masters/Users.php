<?php
/**
 *
 */
class Users
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `user_id` from `utab` WHERE `user_id`='$id' AND `user_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}

	function user_quick_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q="SELECT `user_id`, `user_code`, `user_name`,`status_type_id_fk` FROM `utab` LEFT JOIN `employee_status` ON `employee_status`.`status_id`=`utab`.`user_status_id_fk` WHERE `user_type`='USR' AND `user_status`='ACT'";

		if(isset($param['status_id']) && $param['status_id']!=""){
			$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q.=" AND `user_status_id_fk`='$status_id'";
		}else{
			$status_id="NOT";
		}

		if(isset($param['status_ids']) && $param['status_ids']!=""){
			$status_ids=explode(',', senetize_input($param['status_ids']));
			$status_ids = implode('\', \'', $status_ids);
			//$status_id = "'" . $names . "'"; 
			$q.=" AND  `status_type_id_fk` IN ('$status_ids')";
		}




		$q.=" ORDER BY `user_code`";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($res=mysqli_fetch_assoc($qEx)) {
			array_push($list, [
				'id'=>$res['user_id'],
				'eid'=>$Enc->safeurlen($res['user_id']),
				'code'=>$res['user_code'],
				'name'=>$res['user_name']
			]);

		}
		$response=[];
		$response['list']=$list;
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		} 		

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function user_basic_details($id){
		$user_code="";
		$user_name="";
		$user_id="";
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		$q=mysqli_query($GLOBALS['con'],"SELECT `user_id`,`user_name`,`user_code` from `utab` WHERE `user_id`='$id' AND `user_status`='ACT'");
		if(mysqli_num_rows($q)==1){
			$result=mysqli_fetch_assoc($q);
			$user_code=$result['user_code'];
			$user_name=$result['user_name'];
			$user_id=$result['user_id'];
		}
		$r=[];
		$r['user_code']=$user_code;
		$r['user_name']=$user_name;
		$r['user_id']=$user_id;
		return $r;
	}	



	function users_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0003', USER_PRIV)){


			if(isset($param['code']) && isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$code=mysqli_real_escape_string($GLOBALS['con'],$param['code']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";



				if(isset($param['password']) && $param['password']!=""){
					$password=mysqli_real_escape_string($GLOBALS['con'],$param['password']);
				}else{
					$dataValidation=false;
					$InvalidDataMessage="Please provide password";
				}

				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/EmployeesStatus.php';
					$EmployeesStatus=new EmployeesStatus;

					if(!$EmployeesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}


				$mobile_country_code_id=0;
				if(isset($param['mobile_country_code_id']) && $param['mobile_country_code_id']!=""){
					$mobile_country_code_id=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_country_code_id']);

					include_once APPROOT.'/models/masters/MobileCountryCodes.php';
					$MobileCountryCodes=new MobileCountryCodes;

					if(!$MobileCountryCodes->isValidId($mobile_country_code_id)){
						$InvalidDataMessage="Invalid country code";
						$dataValidation=false;
					}

				}

				$mobile_number="";
				if(isset($param['mobile_number']) && $param['mobile_number']!=""){
					$mobile_number=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_number']);

					if(!isValidMobileNumber($mobile_number)){
						$InvalidDataMessage="Invalid mobile number";
						$dataValidation=false;
					}
					$mobile_number=$Enc->enc_mob($mobile_number);

				}

				$email="";
				if(isset($param['email']) && $param['email']!=""){
					$email=mysqli_real_escape_string($GLOBALS['con'],$param['email']);

					if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
						$InvalidDataMessage="Invalid email";
						$dataValidation=false;
					}
					$email=$Enc->enc_mail($email);

				}

			//--check if the code exists
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `user_id` FROM `utab` WHERE `user_status`='ACT' AND `user_code`='$code'"))>0){
					$InvalidDataMessage="user code already used";
					$dataValidation=false;			
				}


				if($dataValidation){


 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `user_id` FROM `utab` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['user_id'])+1:1;
					///-----//Generate New Unique Id

					$password=password_hash($password, PASSWORD_DEFAULT);

					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `utab`(`user_id`, `user_code`, `user_name`, `user_password`, `user_mobile`, `user_mobile_country_code_id_fk`, `user_email`, `user_type`, `user_status_id_fk`, `user_status`, `user_added_on`, `user_added_by`) VALUES ('$next_id','$code','$name','$password','$mobile_number','$mobile_country_code_id','$email','USR','$status_id','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}

	function users_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0003', USER_PRIV)){


			if(isset($param['code']) && isset($param['name']) && $param['update_eid']){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$code=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['code']));

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();
				$update_id=$Enc->safeurlde($param['update_eid']);
			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";


				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/EmployeesStatus.php';
					$EmployeesStatus=new EmployeesStatus;

					if(!$EmployeesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}


				$mobile_country_code_id=0;
				if(isset($param['mobile_country_code_id']) && $param['mobile_country_code_id']!=""){
					$mobile_country_code_id=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_country_code_id']);

					include_once APPROOT.'/models/masters/MobileCountryCodes.php';
					$MobileCountryCodes=new MobileCountryCodes;

					if(!$MobileCountryCodes->isValidId($mobile_country_code_id)){
						$InvalidDataMessage="Invalid country code";
						$dataValidation=false;
					}

				}

				$mobile_number="";
				if(isset($param['mobile_number']) && $param['mobile_number']!=""){
					$mobile_number=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_number']);

					if(!isValidMobileNumber($mobile_number)){
						$InvalidDataMessage="Invalid mobile number";
						$dataValidation=false;
					}
					$mobile_number=$Enc->enc_mob($mobile_number);

				}

				$email="";
				if(isset($param['email']) && $param['email']!=""){
					$email=mysqli_real_escape_string($GLOBALS['con'],$param['email']);

					if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
						$InvalidDataMessage="Invalid email";
						$dataValidation=false;
					}
					$email=$Enc->enc_mail($email);

				}

			//--check if the code exists
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `user_id` FROM `utab` WHERE `user_status`='ACT' AND `user_code`='$code' AND NOT `user_id`='$update_id'"))>0){
					$InvalidDataMessage="user code already used";
					$dataValidation=false;			
				}


				if($dataValidation){


					$update=mysqli_query($GLOBALS['con'],"UPDATE `utab` SET `user_code`='$code', `user_name`='$name', `user_mobile`='$mobile_number', `user_mobile_country_code_id_fk`='$mobile_country_code_id', `user_email`='$email', `user_status_id_fk`='$status_id', `user_updated_on`='$time', `user_updated_by`='$USERID' WHERE `user_id`='$update_id'");
					if($update){
						$status=true;
						$message="Updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}


	function users_password_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){


			if($param['update_eid']){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();
				$update_id=$Enc->safeurlde($param['update_eid']);
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
					$update=mysqli_query($GLOBALS['con'],"UPDATE `utab` SET `user_password`='$password' WHERE `user_id`='$update_id'");
					if($update){
						$status=true;
						$message="Password updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$param['update_eid'];
		$r['response']=$response;
		return $r;

	}






	function users_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `user_id`, `user_code`, `user_name`, `user_password`, `user_mobile`, `mobile_country_code`,`user_mobile_country_code_id_fk`,`user_email`,`user_status_id_fk`, `user_type`, `user_priv`, `user_status_id_fk`, `user_added_on`, `user_added_by`, `user_updated_on`, `user_updated_by`, `user_status` FROM `utab` LEFT JOIN `mobile_country_codes` ON `mobile_country_codes`.`mobile_country_code_id`=`utab`.`user_mobile_country_code_id_fk` WHERE `user_type`='USR'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND user_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND user_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	


				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['eid']=$Enc->safeurlen($rows['user_id']);
				$row['code']=$rows['user_code'];
				$row['name']=$rows['user_name'];
				$row['mobile']=$Enc->dec_mob($rows['user_mobile']);
				$row['mobile_cc']=$rows['mobile_country_code'];
				$row['email']=$Enc->dec_mail($rows['user_email']);
				$row['mobile_country_code_id']=$rows['user_mobile_country_code_id_fk'];
				$row['status_id']=$rows['user_status_id_fk'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	


	}	

	function users_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q="SELECT `user_id`, `user_code`, `user_name`,`user_mobile`, `mobile_country_code`,`user_email`, `status_name` FROM `utab` LEFT JOIN `mobile_country_codes` ON `mobile_country_codes`.`mobile_country_code_id`=`utab`.`user_mobile_country_code_id_fk` LEFT JOIN `employee_status` ON `employee_status`.`status_id`=`utab`.`user_status_id_fk` WHERE `user_type`='USR' AND `user_status`='ACT'";

		if(isset($param['status_id']) && $param['status_id']!=""){
			$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q.=" AND `user_status_id_fk`='$status_id'";
		}else{
			$status_id="NOT";
		}
$q.=" ORDER BY `user_code`";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['user_id'];
			$row['eid']=$Enc->safeurlen($rows['user_id']);
			$row['code']=$rows['user_code'];
			$row['name']=$rows['user_name'];
			$row['mobile']=(($Enc->dec_mob($rows['user_mobile']))==false)?"":$Enc->dec_mob($rows['user_mobile']);
			$row['mobile_cc']=$rows['mobile_country_code'];
			$row['email']=(($Enc->dec_mail($rows['user_email']))==false)?"":$Enc->dec_mail($rows['user_email']);
			$row['status']=$rows['status_name'];

			$get_roles_group_for_user_q=mysqli_query($GLOBALS['con'],"SELECT `group_name` FROM `users_roles_groups_junction` LEFT JOIN `roles_groups`  ON `users_roles_groups_junction`.`urgj_group_roles_id_fk`=`roles_groups`.`group_id`  WHERE `urgj_status`='ACT' AND `urgj_user_id_fk`='".$rows['user_id']."'");
			$roles_group_array=[];
			while ($group_roles_array_row=mysqli_fetch_assoc($get_roles_group_for_user_q)) {
				array_push($roles_group_array, $group_roles_array_row['group_name']);
			}
			$row['roles_group']=$roles_group_array;



			array_push($list,$row);
		}
		$response=[];
		$response['list']=$list;
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		} 		

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function assign_users_roles_groups($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0138', USER_PRIV)){


			if(isset($param['user_eid'])){
				
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();

				$user_id=$Enc->safeurlde($param['user_eid']);
				$roles_groups_id_array=[];
				if(isset($param['roles_groups_eid_list'])){
					$roles_groups_eid_list=$param['roles_groups_eid_list'];
					foreach ($roles_groups_eid_list as $eid) {
						array_push($roles_groups_id_array, $Enc->safeurlde($eid));
					}
				}


				$roles_groups_id_array_string=implode(', ', $roles_groups_id_array);
				//------------firstly delete those existing junctions which are not send in updated list
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `users_roles_groups_junction` SET `urgj_status`='DEL', `urgj_deleted_by`='$USERID' WHERE `urgj_user_id_fk`='$user_id' AND NOT `urgj_group_roles_id_fk` IN (".$roles_groups_id_array_string.")");

				$insert=true;///statrt $insert variable with true value. during insert if any error occur change it to false
				foreach ($roles_groups_id_array as $roles_group_id) {

						//-----check if the send user id is allready assigned
					if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT auto FROM `users_roles_groups_junction` WHERE `urgj_group_roles_id_fk`='$roles_group_id' AND `urgj_user_id_fk`='$user_id' AND `urgj_status`='ACT'"))<1){
						$insert_new=mysqli_query($GLOBALS['con'],"INSERT INTO `users_roles_groups_junction`(`urgj_user_id_fk`, `urgj_group_roles_id_fk`, `urgj_status`, `urgj_added_by`) VALUES ('$user_id','$roles_group_id','ACT','$USERID')");
						if(!$insert_new){
							$message=SOMETHING_WENT_WROG;
						}
					}

				}

				if($insert){
					$status=true;
					$message="Assigned Successfuly";
				}


			}else{
				$message="Please provide user eid ";
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}


}
?>