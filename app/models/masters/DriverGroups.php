<?php
/**
 *
 */
 class DriverGroups
 {


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `group_id` from `driver_groups` WHERE `group_id`='$id' AND `group_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

/*
function driver_groups_add_new($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('PADMIN', USER_PRIV)){


 			if(isset($param['name'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$USERID=USER_ID;
 				$time=time();
				
				$pay_per_mile="";
				if(isset($param['pay_per_mile']) && $param['pay_per_mile']!=""){
					$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);

					if (!preg_match("/^[0-9]{0,}$/",$pay_per_mile)){
						$InvalidDataMessage="Invalid pay per mile value";
						$dataValidation=false;
					}

				}

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `driver_groups` WHERE `group_status`='ACT' AND `group_name`='$name'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_groups`( `group_name`,`group_pay_per_mile`, `group_status`, `group_added_on`, `group_added_by`) VALUES ('$name','$pay_per_mile','ACT','$time','$USERID')");
 					if($insert){
 						$status=true;
 						$message="Added Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Company name already exists";
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
 	function driver_groups_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `group_id`, `group_name`,`group_pay_per_mile` FROM `driver_groups` WHERE `group_status`='ACT'";

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
 				$row['pay_per_mile']=$rows['group_pay_per_mile'];
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
*/
 	function driver_groups_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `group_id`, `group_name`,`group_pay_per_mile` FROM `driver_groups` WHERE `group_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['group_id'];
 			$row['eid']=$Enc->safeurlen($rows['group_id']);
 			$row['name']=$rows['group_name'];
 			$row['pay_per_mile']=$rows['group_pay_per_mile'];
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
 	function driver_groups_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('PADMIN', USER_PRIV)){


 			if(isset($param['name']) && isset($param['update_eid'])){

 				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				


 				$pay_per_mile="";
				if(isset($param['pay_per_mile']) && $param['pay_per_mile']!=""){
					$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);

					if (!preg_match("/^[0-9]{0,}$/",$pay_per_mile)){
						$InvalidDataMessage="Invalid pay per mile value";
						$dataValidation=false;
					}

				}


 				$update_id=$Enc->safeurlde($param['update_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `driver_groups` WHERE `group_status`='ACT' AND `group_name`='$name' AND NOT `group_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"UPDATE `driver_groups` SET `group_name`='$name',`group_pay_per_mile`='$pay_per_mile',`group_updated_on`='$time',`group_updated_by`='$USERID' WHERE `group_id`='$update_id'");
 					if($insert){
 						$status=true;
 						$message="Updated Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Company name already exists";
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

 	function driver_groups_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('PADMIN', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `group_id` FROM `driver_groups` WHERE `group_id`='$delete_eid' AND NOT `group_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `driver_groups` SET `group_status`='DLT',`group_deleted_on`='$time',`group_deleted_by`='$USERID' WHERE `group_id`='$delete_eid'");
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

 	}*/
 }
 ?>