<?php
/**
 *
 */
class SalaryParameters
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `parameter_id` from `salary_parameters` WHERE `parameter_id`='$id' AND `parameter_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function salary_parameters_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0130', USER_PRIV)){

			$USERID=USER_ID;
			$time=time();
			$dataValidation=true;
			$InvalidDataMessage="";


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide name";
				goto ValidationChecker;
			}

			if(isset($param['parameter_type_id'])){
				$parameter_type_id=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['parameter_type_id']));
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide parameter type id";
				goto ValidationChecker;
			}


 			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `salary_parameters` WHERE `parameter_name`='$name' AND `parameter_type_id_fk`='$parameter_type_id'");
			if(mysqli_num_rows($codeRows)>0){
				$dataValidation=false;
				$InvalidDataMessage="Name already exists";
				goto ValidationChecker;					
			}
			ValidationChecker:
			if($dataValidation){

				
 									 	///-----Generate New Unique Id
				$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` ORDER BY `auto` DESC LIMIT 1");
				$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['parameter_id'])+1:1;
					///-----//Generate New Unique Id

				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `salary_parameters`(`parameter_id`, `parameter_name`, `parameter_type_id_fk`, `parameter_status`, `parameter_added_on`, `parameter_added_by`, `parameter_updated_by`, `parameter_deleted_by`) VALUES ('$next_id','$name','$parameter_type_id','ACT','$time','$USERID','0','0')");
				if($insert){
					$status=true;
					$message="Added Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
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

 	function salary_parameters_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `parameter_id`, `parameter_name`, `parameter_type_id_fk` FROM `salary_parameters` WHERE `parameter_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND parameter_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND parameter_id='$details_for_eid'";
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
 				$row['name']=$rows['parameter_name'];
 				$row['parameter_type_id']=$rows['parameter_type_id_fk'];
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

 	function salary_parameters_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `auto`, `parameter_id`, `parameter_name`, `parameter_type_id_fk`, `parameter_status` FROM `salary_parameters` WHERE `parameter_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['parameter_id'];
 			$row['eid']=$Enc->safeurlen($rows['parameter_id']);
 			$row['name']=$rows['parameter_name'];
 			$row['parameter_type_id']=$rows['parameter_type_id_fk'];
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


 	function salary_parameters_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0132', USER_PRIV)){




			$USERID=USER_ID;
			$time=time();
			$dataValidation=true;
			$InvalidDataMessage="";
			include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;

			if(isset($param['update_eid'])){
				$update_id=$Enc->safeurlde($param['update_eid']);
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide update eid";
				goto ValidationChecker;
			}


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide name";
				goto ValidationChecker;
			}

			if(isset($param['parameter_type_id'])){
				$parameter_type_id=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['parameter_type_id']));
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide parameter type id";
				goto ValidationChecker;
			}


 			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `salary_parameters` WHERE `parameter_name`='$name' AND `parameter_type_id_fk`='$parameter_type_id' AND NOT `parameter_id`='$update_id'");
			if(mysqli_num_rows($codeRows)>0){
				$dataValidation=false;
				$InvalidDataMessage="Name already exists";
				goto ValidationChecker;					
			}
			ValidationChecker:
			if($dataValidation){

				$insert=mysqli_query($GLOBALS['con'],"UPDATE `salary_parameters` SET `parameter_name`='$name', `parameter_type_id_fk`='$parameter_type_id', `parameter_updated_on`='$time',`parameter_updated_by`='$USERID' WHERE `parameter_id`='$update_id'");
				if($insert){
					$status=true;
					$message="Updated Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
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

 	function salary_parameters_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0133', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$delete_eid' AND NOT `parameter_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `salary_parameters` SET `parameter_status`='DLT',`parameter_deleted_on`='$time',`parameter_deleted_by`='$USERID' WHERE `parameter_id`='$delete_eid'");
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
 }
 ?>