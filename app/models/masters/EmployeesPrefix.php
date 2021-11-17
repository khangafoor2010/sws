<?php
class EmployeesPrefix
{
	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `prefix_id` from `employee_prefix` WHERE `prefix_id`='$id' AND `prefix_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	} 	
	function employees_prefix_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0088', USER_PRIV)){


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `prefix_id` FROM `employee_prefix` WHERE `prefix_status`='ACT' AND `prefix_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){

				 					 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `prefix_id` FROM `employee_prefix` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['prefix_id'])+1:0;
					///-----//Generate New Unique Id


					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `employee_prefix`(`prefix_id`, `prefix_name`, `prefix_status`, `prefix_added_on`, `prefix_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
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

	function employees_prefix_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `prefix_id`, `prefix_name` FROM `employee_prefix` WHERE `prefix_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND prefix_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND prefix_id='$details_for_eid'";
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
				$row['name']=$rows['prefix_name'];
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

	function employees_prefix_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `prefix_id`, `prefix_name` FROM `employee_prefix` WHERE `prefix_status`='ACT'");
		$list=[];
		while ($rows=mysqli_fetch_assoc($get)) {
			$row=[];
			$row['id']=$rows['prefix_id'];
			$row['eid']=$Enc->safeurlen($rows['prefix_id']);
			$row['name']=$rows['prefix_name'];
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


	function employees_prefix_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0090', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `prefix_id` FROM `employee_prefix` WHERE `prefix_status`='ACT' AND `prefix_name`='$name' AND NOT `prefix_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"UPDATE `employee_prefix` SET `prefix_name`='$name',`prefix_updated_on`='$time',`prefix_updated_by`='$USERID' WHERE `prefix_id`='$update_id'");
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

	function employees_prefix_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0091', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `prefix_id` FROM `employee_prefix` WHERE `prefix_id`='$delete_eid' AND NOT `prefix_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `employee_prefix` SET `prefix_status`='DLT',`prefix_deleted_on`='$time',`prefix_deleted_by`='$USERID' WHERE `prefix_id`='$delete_eid'");
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