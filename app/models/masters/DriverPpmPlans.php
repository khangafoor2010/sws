<?php
/**
 *
 */
 class DriverPpmPlans
 {


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `ppm_plan_id` from `driver_ppm_groups` WHERE `ppm_plan_id`='$id' AND `ppm_plan_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}


function driver_ppm_plans_add_new($param){
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

				}else{
					$InvalidDataMessage="Invalid pay per mile value";
						$dataValidation=false;
				}

				$incentive_per_mile="";
				if(isset($param['incentive_per_mile']) && $param['incentive_per_mile']!=""){
					$incentive_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_per_mile']);

					if (!preg_match("/^[0-9]{0,}$/",$incentive_per_mile)){
						$InvalidDataMessage="Invalid incentive per mile value";
						$dataValidation=false;
					}

				}else{
					$InvalidDataMessage="Invalid incentive per mile value";
						$dataValidation=false;
				}




			$driver_group_id=0;
			if(isset($param['driver_group_id'])){
				$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

				include_once APPROOT.'/models/masters/DriverGroups.php';
				$DriverGroups=new DriverGroups;

				if(!$DriverGroups->isValidId($driver_group_id)){
					$InvalidDataMessage="Invalid driver group value";
					$dataValidation=false;
				}else{

					///--------driver section calculations are based on id 1/2 checck if sent id belogs to any of these
					if(!in_array($driver_group_id, array('1','2'))){
						$InvalidDataMessage="Please provide driver group id";
						$dataValidation=false;						
					}
				}

			}else{
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
			}





			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `ppm_plan_id` FROM `driver_ppm_groups` WHERE `ppm_plan_status`='ACT' AND `ppm_plan_name`='$name' AND `ppm_plan_driver_group_id_fk`='$driver_group_id'");
 				if(mysqli_num_rows($codeRows)<1){
 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `ppm_plan_id` FROM `driver_ppm_groups` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['ppm_plan_id'])+1:1;
					///-----//Generate New Unique Id


 					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_ppm_groups`(`ppm_plan_id`, `ppm_plan_name`,`ppm_plan_ppm`,`ppm_plan_incentive_per_mile`,`ppm_plan_driver_group_id_fk`, `ppm_plan_status`, `ppm_plan_added_on`, `ppm_plan_added_by`) VALUES ('$next_id','$name','$pay_per_mile','$incentive_per_mile','$driver_group_id','ACT','$time','$USERID')");
 					if($insert){
 						$status=true;
 						$message="Added Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Plan name already exists";
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

 	function driver_ppm_plans_details($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		$details_for="";
 		$runQuery=false;
 		if(isset($param['details_for'])){
 			$details_for=$param['details_for'];
 			include_once APPROOT.'/models/common/Enc.php';
 			$Enc=new Enc;
 			
 			$query="SELECT `ppm_plan_id`, `ppm_plan_name`,`ppm_plan_ppm`,`ppm_plan_incentive_per_mile`,`group_name`,`ppm_plan_driver_group_id_fk` FROM `driver_ppm_groups` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`driver_ppm_groups`.`ppm_plan_driver_group_id_fk` WHERE `ppm_plan_status`='ACT'";

 			//--check, against what is the detail asked
 			switch ($details_for) {
 				case 'id':
 				if(isset($param['details_for_id'])){
 					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
 					$query .=" AND ppm_plan_id='$details_for_id'";
 					$runQuery=true;
 				}else{
 					$message="Please enter details_for_id";
 				}
 				break;	

 				case 'eid':
 				if(isset($param['details_for_eid'])){
 					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
 					$query .=" AND ppm_plan_id='$details_for_eid'";
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
	 			$row['name']=$rows['ppm_plan_name'];
	 			$row['pay_per_mile']=$rows['ppm_plan_ppm'];
	 			$row['incentive_per_mile']=$rows['ppm_plan_incentive_per_mile'];
	 			$row['driver_group_id']=$rows['ppm_plan_driver_group_id_fk'];
	 			$row['driver_group_name']=$rows['group_name'];
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

 	function driver_ppm_plans_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$q="SELECT `ppm_plan_id`, `ppm_plan_name`,`ppm_plan_ppm`,`ppm_plan_incentive_per_mile`,`group_name` FROM `driver_ppm_groups` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`driver_ppm_groups`.`ppm_plan_driver_group_id_fk` WHERE `ppm_plan_status`='ACT'";

//----Apply Filters starts


		if(isset($param['driver_group_id']) && $param['driver_group_id']!=""){
			$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);
			$q .=" AND ppm_plan_driver_group_id_fk='$driver_group_id'";
		}


 		$list=[];
 		$qEx=mysqli_query($GLOBALS['con'],$q);
 		while ($rows=mysqli_fetch_assoc($qEx)) {
 			$row=[];
 			$row['id']=$rows['ppm_plan_id'];
 			$row['eid']=$Enc->safeurlen($rows['ppm_plan_id']);
 			$row['name']=$rows['ppm_plan_name'];
 			$row['ppm']=$rows['ppm_plan_ppm'];
 			$row['incentive_per_mile']=$rows['ppm_plan_incentive_per_mile'];
 			$row['driver_group_name']=$rows['group_name'];
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


 	function driver_ppm_plans_update($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		if(in_array('PADMIN', USER_PRIV)){


 			if(isset($param['name']) && isset($param['update_eid'])){

 				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
 								include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);	
 				$USERID=USER_ID;
 				$time=time();
				
				$pay_per_mile="";
				if(isset($param['pay_per_mile']) && $param['pay_per_mile']!=""){
					$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);

					if (!preg_match("/^[0-9]{0,}$/",$pay_per_mile)){
						$InvalidDataMessage="Invalid pay per mile value";
						$dataValidation=false;
					}

				}else{
					$InvalidDataMessage="Invalid pay per mile value";
						$dataValidation=false;
				}


				$incentive_per_mile="";
				if(isset($param['incentive_per_mile']) && $param['incentive_per_mile']!=""){
					$incentive_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_per_mile']);

					if (!preg_match("/^[0-9]{0,}$/",$incentive_per_mile)){
						$InvalidDataMessage="Invalid incentive per mile value";
						$dataValidation=false;
					}

				}else{
					$InvalidDataMessage="Invalid incentive per mile value";
						$dataValidation=false;
				}



			$driver_group_id=0;
			if(isset($param['driver_group_id'])){
				$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

				include_once APPROOT.'/models/masters/DriverGroups.php';
				$DriverGroups=new DriverGroups;

				if(!$DriverGroups->isValidId($driver_group_id)){
					$InvalidDataMessage="Invalid driver group value";
					$dataValidation=false;
				}else{

					///--------driver section calculations are based on id 1/2 checck if sent id belogs to any of these
					if(!in_array($driver_group_id, array('1','2'))){
						$InvalidDataMessage="Please provide driver group id";
						$dataValidation=false;						
					}
				}

			}else{
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
			}





			//--check if the code exists
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `ppm_plan_id` FROM `driver_ppm_groups` WHERE `ppm_plan_status`='ACT' AND `ppm_plan_name`='$name' AND `ppm_plan_driver_group_id_fk`='$driver_group_id' AND NOT `ppm_plan_id`='$update_id'");
 				if(mysqli_num_rows($codeRows)<1){

 					$update=mysqli_query($GLOBALS['con'],"UPDATE `driver_ppm_groups` SET `ppm_plan_name`='$name',`ppm_plan_ppm`='$pay_per_mile',`ppm_plan_incentive_per_mile`='$incentive_per_mile',`ppm_plan_driver_group_id_fk`='$driver_group_id', `ppm_plan_status`='ACT', `ppm_plan_updated_on`='$time', `ppm_plan_updated_by`='$USERID' WHERE `ppm_plan_id`='$update_id'");
 					if($update){
 						$status=true;
 						$message="Updated Successfuly";	
 					}else{
 						$message=SOMETHING_WENT_WROG;
 					}
 				}else{
 					$message="Plan name already exists";
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

 	function driver_ppm_plans_delete($param){
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
 				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `ppm_plan_id` FROM `driver_ppm_groups` WHERE `ppm_plan_id`='$delete_eid' AND NOT `ppm_plan_status`='DLT'");
 				if(mysqli_num_rows($codeRows)==1){
 					$delete=mysqli_query($GLOBALS['con'],"UPDATE `driver_ppm_groups` SET `ppm_plan_status`='DLT',`ppm_plan_deleted_on`='$time',`ppm_plan_deleted_by`='$USERID' WHERE `ppm_plan_id`='$delete_eid'");
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