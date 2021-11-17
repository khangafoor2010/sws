<?php
/**

 */
class VehiclesConditions
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `condition_id` from `vehicle_conditions` WHERE `condition_id`='$id' AND `condition_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	} 	
function vehicles_conditions_add_new($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P28', USER_PRIV)){


		if(isset($param['name'])){
			$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
			$USERID=USER_ID;
			$time=time();


			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `condition_id` FROM `vehicle_conditions` WHERE `condition_status`='ACT' AND `condition_name`='$name'");
			if(mysqli_num_rows($codeRows)<1){
				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_conditions`( `condition_name`, `condition_status`, `condition_added_on`, `condition_added_by`) VALUES ('$name','ACT','$time','$USERID')");
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

function vehicles_conditions_details($param){
	$status=false;
	$message=null;
	$response=null;
	$details_for="";
	$runQuery=false;
	if(isset($param['details_for'])){
		$details_for=$param['details_for'];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$query="SELECT `condition_id`, `condition_name` FROM `vehicle_conditions` WHERE `condition_status`='ACT'";

 			//--check, against what is the detail asked
		switch ($details_for) {
			case 'id':
			if(isset($param['details_for_id'])){
				$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
				$query .=" AND condition_id='$details_for_id'";
				$runQuery=true;
			}else{
				$message="Please enter details_for_id";
			}
			break;	

			case 'eid':
			if(isset($param['details_for_eid'])){
				$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
				$query .=" AND condition_id='$details_for_eid'";
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
			$row['name']=$rows['condition_name'];
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

function vehicles_conditions_list($param){
	$status=false;
	$message=null;
	$response=null;
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;

	$get=mysqli_query($GLOBALS['con'],"SELECT `condition_id`, `condition_name` FROM `vehicle_conditions` WHERE `condition_status`='ACT'");
	$list=[];
	while ($rows=mysqli_fetch_assoc($get)) {
		$row=[];
		$row['id']=$rows['condition_id'];
		$row['eid']=$Enc->safeurlen($rows['condition_id']);
		$row['name']=$rows['condition_name'];
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


function vehicles_conditions_update($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P30', USER_PRIV)){


		if(isset($param['name']) && isset($param['update_eid'])){

			$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$update_id=$Enc->safeurlde($param['update_eid']);				
			$USERID=USER_ID;
			$time=time();

			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `condition_id` FROM `vehicle_conditions` WHERE `condition_status`='ACT' AND `condition_name`='$name' AND NOT `condition_id`='$update_id'");
			if(mysqli_num_rows($codeRows)<1){
				$insert=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_conditions` SET `condition_name`='$name',`condition_updated_on`='$time',`condition_updated_by`='$USERID' WHERE `condition_id`='$update_id'");
				if($insert){
					$status=true;
					$message="Updated Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
			}else{
				$message="Condition name already exists";
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

function vehicles_conditions_delete($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P31', USER_PRIV)){


		if(isset($param['delete_eid'])){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$delete_eid=$Enc->safeurlde($param['delete_eid']);				
			$USERID=USER_ID;
			$time=time();

			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `condition_id` FROM `vehicle_conditions` WHERE `condition_id`='$delete_eid' AND NOT `condition_status`='DLT'");
			if(mysqli_num_rows($codeRows)==1){
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_conditions` SET `condition_status`='DLT',`condition_deleted_on`='$time',`condition_deleted_by`='$USERID' WHERE `condition_id`='$delete_eid'");
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