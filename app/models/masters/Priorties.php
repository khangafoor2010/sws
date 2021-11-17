<?php
/**
 *
 */
 class Priorties
 {


	function isValidId($id ,$priority_type=""){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		$q="SELECT `priority_id` from `priorities` WHERE `priority_id`='$id' AND `priority_status`='ACT' ";
		if($priority_type!=''){
			$priority_type=senetize_input($priority_type);
			$q.=" AND `priority_for_id_fk`='$priority_type'";
		}
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],$q))==1){
			return true;
		}else{
			return false;
		}
	}


function priorities_add_new($param){
 		$status=false;
 		$message=null;
 		$response=null;
 			if(isset($param['name'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$USERID=USER_ID;
 				$time=time();


			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `priority_id` FROM `priorities` WHERE `priority_status`='ACT' AND `priority_name`='$name'");
 				if(mysqli_num_rows($codeRows)<1){

 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `priority_id` FROM `priorities` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['priority_id'])+1:0;
					///-----//Generate New Unique Id

 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `priorities`(`priority_id`, `priority_name`, `priority_status`, `company_added_on`, `company_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
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

 		$r=[];
 		$r['status']=$status;
 		$r['message']=$message;
 		$r['response']=$response;
 		return $r;

 	}

 	function priorities_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `priority_id`, `priority_name` FROM `priorities` WHERE `priority_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND priority_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND priority_id='$details_for_eid'";
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
 				$row['name']=$rows['company_name'];
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

 	function priorities_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$q="SELECT `priority_id`, `priority_name`,`priority_for_id_fk` FROM `priorities` WHERE `priority_status`='ACT'";
 		if(isset($param['priority_for']) && $param['priority_for']!=''){
 			$priority_for_id=senetize_input($param['priority_for']);
 			$q.=" AND priority_for_id_fk='$priority_for_id'";
 		}

 		$q.=" ORDER BY `priority_name`";
 		$get=mysqli_query($GLOBALS['con'],$q);
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['priority_id'];
 			$row['eid']=$Enc->safeurlen($rows['priority_id']);
 			$row['name']=$rows['priority_name'];
 			$row['priority_for']=$rows['priority_for_id_fk'];
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


 	function priorities_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0035', USER_PRIV)){


 			if(isset($param['name']) && isset($param['update_eid'])){

 				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$update_id=$Enc->safeurlde($param['update_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `priority_id` FROM `priorities` WHERE `priority_status`='ACT' AND `priority_name`='$name' AND NOT `priority_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"UPDATE `priorities` SET `priority_name`='$name',`company_updated_on`='$time',`company_updated_by`='$USERID' WHERE `priority_id`='$update_id'");
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

 	function priorities_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0036', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `priority_id` FROM `priorities` WHERE `priority_id`='$delete_eid' AND NOT `priority_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `priorities` SET `priority_status`='DLT',`company_deleted_on`='$time',`company_deleted_by`='$USERID' WHERE `priority_id`='$delete_eid'");
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