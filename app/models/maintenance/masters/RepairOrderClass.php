<?php
class RepairOrderClass
{
	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `class_id` from `sm_repair_order_class` WHERE `class_id`='$id' AND `class_id_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	} 

	function repair_order_class_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `class_id`, `class_name` FROM `sm_repair_order_class` WHERE `class_id_status`='ACT' order by `class_id`");
		$list=[];
		while ($rows=mysqli_fetch_assoc($get)) {
			$row=[];
			$row['id']=$rows['class_id'];
			$row['eid']=$Enc->safeurlen($rows['class_id']);
			$row['name']=$rows['class_name'];
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
/*
	function repair_order_class_addnew($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0088', USER_PRIV)){

			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `class_id` FROM `sm_repair_order_class` WHERE `class_id_status`='ACT' AND `class_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){
				 	///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `class_id` FROM `sm_repair_order_class` ORDER BY `class_auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['class_id'])+1:0;
					///-----//Generate New Unique Id

					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_class`(`class_id`, `class_name`, `class_id_status`,`class_added_on`,`class_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
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

	function repair_order_class_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `class_id`, `class_name` FROM `sm_repair_order_class` WHERE `class_id_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND class_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND class_id='$details_for_eid'";
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
				$row['name']=$rows['class_name'];
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

	function repair_order_class_update($param){
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
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `class_id` FROM `sm_repair_order_class` WHERE `class_id_status`='ACT' AND `class_name`='$name' AND NOT `class_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_class` SET `class_name`='$name',`class_updated_on`='$time',`class_updated_by`='$USERID' WHERE `class_id`='$update_id'");
					if($update){
						$status=true;
						$message="Updated Successfuly";
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Already Exists";
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

	function repair_order_class_delete($param){
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
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `class_id` FROM `sm_repair_order_class` WHERE `class_id`='$delete_eid' AND NOT `class_id_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_class` SET `class_id_status`='DLT',`class_deleted_on`='$time',`class_deleted_by`='$USERID' WHERE `class_id`='$delete_eid'");
					
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

	*/
}
?>