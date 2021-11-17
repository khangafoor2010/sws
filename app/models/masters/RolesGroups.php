<?php
class RolesGroups
{

	function roles_groups_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0136', USER_PRIV)){


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `roles_groups` WHERE `group_status`='ACT' AND `group_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){

				 					 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `roles_groups` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['group_id'])+1:0;
					///-----//Generate New Unique Id


					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `roles_groups`(`group_id`, `group_name`, `group_status`, `group_added_on`, `group_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Name already exists";
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

	function roles_groups_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `group_id`, `group_name` FROM `roles_groups` WHERE `group_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND group_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND group_id='$details_for_eid'";
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
				$row['name']=$rows['group_name'];
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

	function roles_groups_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `group_id`, `group_name` FROM `roles_groups` WHERE `group_status`='ACT'");
		$list=[];
		while ($rows=mysqli_fetch_assoc($get)) {
			$row=[];
			$row['id']=$rows['group_id'];
			$row['eid']=$Enc->safeurlen($rows['group_id']);
			$row['name']=$rows['group_name'];
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


	function roles_groups_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0138', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `roles_groups` WHERE `group_status`='ACT' AND `group_name`='$name' AND NOT `group_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"UPDATE `roles_groups` SET `group_name`='$name',`group_updated_on`='$time',`group_updated_by`='$USERID' WHERE `group_id`='$update_id'");
					if($insert){
						$status=true;
						$message="Updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Prefix name already exists";
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

	function roles_groups_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0139', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `roles_groups` WHERE `group_id`='$delete_eid' AND NOT `group_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `roles_groups` SET `group_status`='DLT',`group_deleted_on`='$time',`group_deleted_by`='$USERID' WHERE `group_id`='$delete_eid'");
					if($delete){
						$status=true;
						$message="Deleted Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Invalid eid";
				}
			}else{
				$message="Please Provide delete_eid";
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

	function roles_groups_group_roles($param){
		$status=false;
		$message=null;
		$response=null;

		$response=[];
		if(isset($param['group_eid'])){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$group_id=$Enc->safeurlde($param['group_eid']);
			$q=mysqli_query($GLOBALS['con'],"SELECT `group_roles` FROM `roles_groups` WHERE `group_id`='$group_id' AND `group_status`='ACT'");
			if(mysqli_num_rows($q)==1){
				$status=true;
				$response['list']=explode(',', mysqli_fetch_assoc($q)['group_roles']);
			}
		}else{
			$message="Please provide group eid";
		}
		

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;		
	}

	function roles_groups_all_roles_list($param){

		$status=false;
		$message=null;
		$response=null;


		if(in_array('P0138', USER_PRIV)){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$Qa=mysqli_query($GLOBALS['con'],"SELECT `ro_code`, `ro_type`, `ro_name`, `ro_status`, `ro_a_type_id_fk`, `ro_b_type_id_fk` FROM `roles` WHERE `ro_status`='AC' AND `ro_type`='A'");
			$return_array=[];

			while ($rowsA=mysqli_fetch_assoc($Qa)) {
				$rowA=[];
				$rowA['eid']=$Enc->safeurlen($rowsA['ro_code']);
				$rowA['code']=$rowsA['ro_code'];
				$rowA['name']=$rowsA['ro_name'];




///--------fetch B type list 
				$Qb=mysqli_query($GLOBALS['con'],"SELECT `ro_code`, `ro_type`, `ro_name`, `ro_status`, `ro_b_type_id_fk` FROM `roles` WHERE `ro_status`='AC' AND `ro_type`='B' AND `ro_a_type_id_fk`='".$rowA['code']."'");
				$child=[];
				while ($rowsB=mysqli_fetch_assoc($Qb)) {
					$rowB=[];
					$rowB['eid']=$Enc->safeurlen($rowsB['ro_code']);
					$rowB['code']=$rowsB['ro_code'];
					$rowB['name']=$rowsB['ro_name'];




///--------fetch C type list 
					$Qc=mysqli_query($GLOBALS['con'],"SELECT `ro_code`, `ro_type`, `ro_name`, `ro_status`, `ro_b_type_id_fk` FROM `roles` WHERE `ro_status`='AC' AND `ro_type`='C' AND `ro_b_type_id_fk`='".$rowB['code']."'");
					$grand_child=[];
					while ($rowsC=mysqli_fetch_assoc($Qc)) {
						$rowC=[];
						$rowC['eid']=$Enc->safeurlen($rowsC['ro_code']);
						$rowC['code']=$rowsC['ro_code'];
						$rowC['name']=$rowsC['ro_name'];
						array_push($grand_child,$rowC);
					}
					$rowB['grand_child']=$grand_child;
///-------/fetch C type list

					array_push($child,$rowB);
				}
				$rowA['child']=$child;
///-------/fetch B type list

				array_push($return_array,$rowA);
			}
			$response['list']=$return_array;
			if(count($response['list'])>0){
				$status=true;
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
	function roles_groups_group_roles_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0138', USER_PRIV)){


			if(isset($param['group_eid']) && isset($param['roles_list'])){
				$roles_list=json_decode($param['roles_list'],true);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();
				$group_id=$Enc->safeurlde($param['group_eid']);
			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";



				$roles_array=[];
				$list_valid=true;
				foreach ($roles_list as $role_item) {
					$role_type_a=mysqli_real_escape_string($GLOBALS['con'],$role_item['data_a']);
					$role_type_b=mysqli_real_escape_string($GLOBALS['con'],$role_item['data_b']);
					$role_type_c=mysqli_real_escape_string($GLOBALS['con'],$role_item['data_c']);
					if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `auto`, `ro_code`, `ro_type`, `ro_name`, `ro_status`, `ro_b_type_id_fk` FROM `roles` WHERE `ro_a_type_id_fk`='$role_type_a' AND `ro_b_type_id_fk`='$role_type_b' AND `ro_code`='$role_type_c'"))==1){

						array_push($roles_array, $role_type_a);
						array_push($roles_array, $role_type_b);
						array_push($roles_array, $role_type_c);
					}else{
						$list_valid=false;
						goto invalidList;
					}

				}
				invalidList:

				if($list_valid){
					$roles_array=implode(',', array_unique($roles_array));
				}else{
					$dataValidation=false;
					$InvalidDataMessage="One or more role code is invalid";					
				}




			//--check if the code exists


				if($dataValidation){

					$update=mysqli_query($GLOBALS['con'],"UPDATE `roles_groups` SET `group_roles`='$roles_array' WHERE `group_id`='$group_id'");
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

	function roles_groups_assign_users($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0138', USER_PRIV)){


			if(isset($param['group_eid'])){
				
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();

				$group_id=$Enc->safeurlde($param['group_eid']);
				$users_id_array=[];
				if(isset($param['users_eid_list'])){
					$users_eid_list=$param['users_eid_list'];
					foreach ($users_eid_list as $eid) {
						array_push($users_id_array, $Enc->safeurlde($eid));
					}
				}


				$users_id_array_string=implode(', ', $users_id_array);
				//------------firstly delete those existing junctions which are not send in updated list
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `users_roles_groups_junction` SET `urgj_status`='DEL', `urgj_deleted_by`='$USERID' WHERE `urgj_group_roles_id_fk`='$group_id' AND NOT `urgj_user_id_fk` IN (".$users_id_array_string.")");

				$insert=true;///statrt $insert variable with true value. during insert if any error occur change it to false
				foreach ($users_id_array as $user_id) {

						//-----check if the send user id is allready assigned
					if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT auto FROM `users_roles_groups_junction` WHERE `urgj_group_roles_id_fk`='$group_id' AND `urgj_user_id_fk`='$user_id' AND `urgj_status`='ACT'"))<1){
					$insert_new=mysqli_query($GLOBALS['con'],"INSERT INTO `users_roles_groups_junction`(`urgj_user_id_fk`, `urgj_group_roles_id_fk`, `urgj_status`, `urgj_added_by`) VALUES ('$user_id','$group_id','ACT','$USERID')");
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
				$message="Please provide group eid ";
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

	function roles_groups_users_junction($param){
		

		$status=false;
		$message=null;
		$response=[];

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q="SELECT `urgj_user_id_fk`,`user_code`, `urgj_group_roles_id_fk`,`group_name` FROM `users_roles_groups_junction` LEFT JOIN `utab` ON `utab`.`user_id`=`users_roles_groups_junction`.`urgj_user_id_fk` LEFT JOIN `roles_groups` ON `roles_groups`.`group_id`=`users_roles_groups_junction`.`urgj_group_roles_id_fk` WHERE `urgj_status`='ACT'";

		if(isset($param['group_eid'])){
			$group_id=$Enc->safeurlde($param['group_eid']);
			$q.=" AND `urgj_group_roles_id_fk`='$group_id'";
		}
		if(isset($param['user_eid'])){
			$user_eid=$Enc->safeurlde($param['user_eid']);
			$q.=" AND `urgj_user_id_fk`='$user_eid'";
		}		

		$list=[];
		$qEx=mysqli_query($GLOBALS['con'],$q);
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['user_eid']=$Enc->safeurlen($rows['urgj_user_id_fk']);
			$row['group_eid']=$Enc->safeurlen($rows['urgj_group_roles_id_fk']);
			$row['user_code']=$rows['user_code'];
			$row['group_name']=$rows['group_name'];
			array_push($list,$row);
			$status=true;
		}
		$response['list']=$list;
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}

}
?>