<?php
/**
 *
 */
 class LeaseCompanies
 {

 	 function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `lease_company_id` from `lease_companies` WHERE `lease_company_id`='$id' AND `lease_company_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}
function lease_companies_add_new($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0043', USER_PRIV)){


 			if(isset($param['name'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$USERID=USER_ID;
 				$time=time();


			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `lease_company_id` FROM `lease_companies` WHERE `lease_company_status`='ACT' AND `lease_company_name`='$name'");
 				if(mysqli_num_rows($codeRows)<1){

 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `lease_company_id` FROM `lease_companies` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['lease_company_id'])+1:1;
					///-----//Generate New Unique Id


 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `lease_companies`(`lease_company_id`, `lease_company_name`, `lease_company_status`, `lease_company_added_on`, `lease_company_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
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

 	function lease_companies_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `lease_company_id`, `lease_company_name` FROM `lease_companies` WHERE `lease_company_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND lease_company_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND lease_company_id='$details_for_eid'";
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
 				$row['name']=$rows['lease_company_name'];
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

 	function lease_companies_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `lease_company_id`, `lease_company_name` FROM `lease_companies` WHERE `lease_company_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['lease_company_id'];
 			$row['eid']=$Enc->safeurlen($rows['lease_company_id']);
 			$row['name']=$rows['lease_company_name'];
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


 	function lease_companies_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0045', USER_PRIV)){


 			if(isset($param['name']) && isset($param['update_eid'])){

 				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$update_id=$Enc->safeurlde($param['update_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `lease_company_id` FROM `lease_companies` WHERE `lease_company_status`='ACT' AND `lease_company_name`='$name' AND NOT `lease_company_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"UPDATE `lease_companies` SET `lease_company_name`='$name',`lease_company_updated_on`='$time',`lease_company_updated_by`='$USERID' WHERE `lease_company_id`='$update_id'");
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

 	function lease_companies_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0046', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `lease_company_id` FROM `lease_companies` WHERE `lease_company_id`='$delete_eid' AND NOT `lease_company_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `lease_companies` SET `lease_company_status`='DLT',`lease_company_deleted_on`='$time',`lease_company_deleted_by`='$USERID' WHERE `lease_company_id`='$delete_eid'");
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