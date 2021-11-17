<?php
/**
 *
 */
 class VehiclesModels
 {

 		function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `model_id` from `vehicle_models` WHERE `model_id`='$id' AND `model_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}
function vehicles_models_add_new($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0058', USER_PRIV)){


 			if(isset($param['name']) && isset($param['vehicle_id']) && isset($param['maker_id'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$vehicle_id=mysqli_real_escape_string($GLOBALS['con'],$param['vehicle_id']);
 				$maker_id=mysqli_real_escape_string($GLOBALS['con'],$param['maker_id']);
 				$USERID=USER_ID;
 				$time=time();


			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` WHERE `model_status`='ACT' AND `model_name`='$name' AND `model_maker_id_fk`='$maker_id' AND `model_vehicle_id_fk`='$vehicle_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					 ///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['model_id'])+1:0;
					///-----//Generate New Unique Id
 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_models`(`model_id`, `model_name`, `model_maker_id_fk`, `model_vehicle_id_fk`, `model_status`, `model_added_on`, `model_added_by`) VALUES ('$next_id','$name','$maker_id','$vehicle_id','ACT','$time','$USERID')");
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

 	function vehicles_models_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `model_id`, `model_name`,`maker_id`,`maker_name`,`vehicle_name`,`vehicle_id` FROM `vehicle_models` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`vehicle_models`.`model_maker_id_fk` LEFT JOIN `vehicles` ON `vehicles`.`vehicle_id`=`vehicle_models`.`model_vehicle_id_fk` WHERE `model_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND model_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND model_id='$details_for_eid'";
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
	 			$row['id']=$rows['model_id'];
	 			$row['eid']=$Enc->safeurlen($rows['model_id']);
	 			$row['name']=$rows['model_name'];
	 			$row['maker_id']=$rows['maker_id'];
	 			$row['maker']=$rows['maker_name'];
	 			$row['vehicle_id']=$rows['vehicle_id'];
	 			$row['vehicle']=$rows['vehicle_name'];
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

 	function vehicles_models_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 				$batch=50;
		$page=1;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$q="SELECT `model_id`, `model_name`,`maker_name`,`vehicle_name` FROM `vehicle_models` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`vehicle_models`.`model_maker_id_fk` LEFT JOIN `vehicles` ON `vehicles`.`vehicle_id`=`vehicle_models`.`model_vehicle_id_fk` WHERE `model_status`='ACT'";

////------------apply filter
		if(isset($param['maker_id']) && $param['maker_id']!=""){
 	$maker_id=mysqli_real_escape_string($GLOBALS['con'],$param['maker_id']);
 	$q .=" AND `model_maker_id_fk`='$maker_id' ";
}

////-------------apply filter


		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		//$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
 		while ($rows=mysqli_fetch_assoc($qEx)) {
 			$row=[];
 			$row['id']=$rows['model_id'];
 			$row['eid']=$Enc->safeurlen($rows['model_id']);
 			$row['name']=$rows['model_name'];
 			$row['maker']=$rows['maker_name'];
 			$row['vehicle']=$rows['vehicle_name'];
 			array_push($list,$row);
 		}
		$response=[];
		$response['total']=$totalRows;
		$response['totalRows']=$totalRows;
		$response['totalPages']=ceil($totalRows/$batch);
		$response['currentPage']=$page;
		$response['resultFrom']=$from+1;
		$response['resultUpto']=$range;
		$response['list']=$list;
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		}
 		$r['status']=$status;
 		$r['message']=$message;
 		$r['response']=$response;
 		return $r;	
 	}


 	function vehicles_models_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0060', USER_PRIV)){


 			if(isset($param['name']) && isset($param['vehicle_id']) && isset($param['maker_id'])){
 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 				$vehicle_id=mysqli_real_escape_string($GLOBALS['con'],$param['vehicle_id']);
 				$maker_id=mysqli_real_escape_string($GLOBALS['con'],$param['maker_id']);
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$update_id=$Enc->safeurlde($param['update_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` WHERE `model_status`='ACT' AND `model_name`='$name'  AND `model_maker_id_fk`='$maker_id' AND `model_vehicle_id_fk`='$vehicle_id' AND NOT `model_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					$insert=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_models` SET `model_name`='$name',`model_maker_id_fk`='$maker_id',`model_vehicle_id_fk`='$vehicle_id',`model_updated_on`='$time',`model_updated_by`='$USERID' WHERE `model_id`='$update_id'");
 					if($insert){
 						$status=true;
 						$message="Updated Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Model name already exists";
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

 	function vehicles_models_delete($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('P0061', USER_PRIV)){


 			if(isset($param['delete_eid'])){
 				include_once APPROOT.'/models/common/Enc.php';
 				$Enc=new Enc;
 				
 				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
 				$USERID=USER_ID;
 				$time=time();

			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` WHERE `model_id`='$delete_eid' AND NOT `model_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_models` SET `model_status`='DLT',`model_deleted_on`='$time',`model_deleted_by`='$USERID' WHERE `model_id`='$delete_eid'");
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