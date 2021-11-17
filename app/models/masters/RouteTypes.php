<?php
/**
 *
 */
 class RouteTypes
 {


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `route_type_id` from `route_types` WHERE `route_type_id`='$id' AND `route_type_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}


function route_types_add_new($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0108', USER_PRIV)){


 			if(isset($param['name'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$USERID=USER_ID;
 				$time=time();


			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `route_type_id` FROM `route_types` WHERE `route_type_status`='ACT' AND `route_type_name`='$name'");
 				if(mysqli_num_rows($codeRows)<1){

				 	///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `route_type_id` FROM `route_types` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['route_type_id'])+1:0;
					///-----//Generate New Unique Id


 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `route_types`(`route_type_id`, `route_type_name`, `route_type_status`, `route_type_added_on`, `route_type_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
 					if($insert){
 						$status=true;
 						$message="Added Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Route type already exists";
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

 	function route_types_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `route_type_id`, `route_type_name` FROM `route_types` WHERE `route_type_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND route_type_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND route_type_id='$details_for_eid'";
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
 				$row['name']=$rows['route_type_name'];
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

 	function route_types_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `route_type_id`, `route_type_name` FROM `route_types` WHERE `route_type_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['route_type_id'];
 			$row['eid']=$Enc->safeurlen($rows['route_type_id']);
 			$row['name']=$rows['route_type_name'];
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


 	function route_types_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0110', USER_PRIV)){


 			if(isset($param['name']) && isset($param['update_eid'])){

 				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$update_id=$Enc->safeurlde($param['update_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `route_type_id` FROM `route_types` WHERE `route_type_status`='ACT' AND `route_type_name`='$name' AND NOT `route_type_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"UPDATE `route_types` SET `route_type_name`='$name',`route_type_updated_on`='$time',`route_type_updated_by`='$USERID' WHERE `route_type_id`='$update_id'");
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

 	function route_types_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0111', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `route_type_id` FROM `route_types` WHERE `route_type_id`='$delete_eid' AND NOT `route_type_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `route_types` SET `route_type_status`='DLT',`route_type_deleted_on`='$time',`route_type_deleted_by`='$USERID' WHERE `route_type_id`='$delete_eid'");
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