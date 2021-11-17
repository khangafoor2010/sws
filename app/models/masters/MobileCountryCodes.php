<?php
class MobileCountryCodes
{
	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id` from `mobile_country_codes` WHERE `mobile_country_code_id`='$id' AND `mobile_country_code_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	} 	
function mobile_country_codes_add_new($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0098', USER_PRIV)){


		if(isset($param['name'])){
			$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
			$USERID=USER_ID;
			$time=time();


			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id` FROM `mobile_country_codes` WHERE `mobile_country_code_status`='ACT' AND `mobile_country_code`='$name'");
			if(mysqli_num_rows($codeRows)<1){

				 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id` FROM `mobile_country_codes` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['mobile_country_code_id'])+1:0;
					///-----//Generate New Unique Id

				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `mobile_country_codes`(`mobile_country_code_id`, `mobile_country_code`, `mobile_country_code_status`, `mobile_country_code_added_on`, `mobile_country_code_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
				if($insert){
					$status=true;
					$message="Added Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
			}else{
				$message="Code already exists";
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

function mobile_country_codes_details($param){
	$status=false;
	$message=null;
	$response=null;
	$details_for="";
	$runQuery=false;
	if(isset($param['details_for'])){
		$details_for=$param['details_for'];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$query="SELECT `mobile_country_code_id`, `mobile_country_code` FROM `mobile_country_codes` WHERE `mobile_country_code_status`='ACT'";

 			//--check, against what is the detail asked
		switch ($details_for) {
			case 'id':
			if(isset($param['details_for_id'])){
				$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
				$query .=" AND mobile_country_code_id='$details_for_id'";
				$runQuery=true;
			}else{
				$message="Please enter details_for_id";
			}
			break;	

			case 'eid':
			if(isset($param['details_for_eid'])){
				$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
				$query .=" AND mobile_country_code_id='$details_for_eid'";
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
			$row['name']=$rows['mobile_country_code'];
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

function mobile_country_codes_list($param){
	$status=false;
	$message=null;
	$response=null;
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;

	$get=mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id`, `mobile_country_code` FROM `mobile_country_codes` WHERE `mobile_country_code_status`='ACT'");
	$list=[];
	while ($rows=mysqli_fetch_assoc($get)) {
		$row=[];
		$row['id']=$rows['mobile_country_code_id'];
		$row['eid']=$Enc->safeurlen($rows['mobile_country_code_id']);
		$row['name']=$rows['mobile_country_code'];
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


function mobile_country_codes_update($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0100', USER_PRIV)){


		if(isset($param['name']) && isset($param['update_eid'])){

			$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$update_id=$Enc->safeurlde($param['update_eid']);				
			$USERID=USER_ID;
			$time=time();

			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id` FROM `mobile_country_codes` WHERE `mobile_country_code_status`='ACT' AND `mobile_country_code`='$name' AND NOT `mobile_country_code_id`='$update_id'");
			if(mysqli_num_rows($codeRows)<1){
				$insert=mysqli_query($GLOBALS['con'],"UPDATE `mobile_country_codes` SET `mobile_country_code`='$name',`mobile_country_code_updated_on`='$time',`mobile_country_code_updated_by`='$USERID' WHERE `mobile_country_code_id`='$update_id'");
				if($insert){
					$status=true;
					$message="Updated Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
			}else{
				$message="Code already exists";
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

function mobile_country_codes_delete($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0101', USER_PRIV)){


		if(isset($param['delete_eid'])){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$delete_eid=$Enc->safeurlde($param['delete_eid']);				
			$USERID=USER_ID;
			$time=time();

			//--check if the code exists
			$codeRows=mysqli_query($GLOBALS['con'],"SELECT `mobile_country_code_id` FROM `mobile_country_codes` WHERE `mobile_country_code_id`='$delete_eid' AND NOT `mobile_country_code_status`='DLT'");
			if(mysqli_num_rows($codeRows)==1){
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `mobile_country_codes` SET `mobile_country_code_status`='DLT',`mobile_country_code_deleted_on`='$time',`mobile_country_code_deleted_by`='$USERID' WHERE `mobile_country_code_id`='$delete_eid'");
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