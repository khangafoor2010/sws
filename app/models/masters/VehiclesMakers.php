<?php
/**
 *
 */
class VehiclesMakers
{


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `maker_id` from `vehicle_makers` WHERE `maker_id`='$id' AND `maker_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}
	function vehicles_makers_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0053', USER_PRIV)){


			if(isset($param['name'])){


				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));

 				//---validate vehicle id
				$vehicles_senetized_array=[];
				if(isset($param['vehicles_id'])){
					$vehicles_id_array=explode(',', mysqli_real_escape_string($GLOBALS['con'],$param['vehicles_id']));
					foreach ($vehicles_id_array as $vehicles_id_array) {
						$validateVehicleId=mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` FROM `vehicles` WHERE `vehicle_id`='$vehicles_id_array' AND `vehicle_status`='ACT'");
						if(mysqli_num_rows($validateVehicleId)==1){
							array_push($vehicles_senetized_array,mysqli_fetch_assoc($validateVehicleId)['vehicle_id']);
						}
					}
				}
 				//---//validate vehicle id
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `maker_id` FROM `vehicle_makers` WHERE `maker_status`='ACT' AND `maker_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){

				 	///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `maker_id` FROM `vehicle_makers` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['maker_id'])+1:1;
					///-----//Generate New Unique Id

					$insert_maker=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_makers`(`maker_id`, `maker_name`, `maker_status`, `maker_added_on`, `maker_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");

					if($insert_maker){


				 	///-----Generate New Unique Id
						$get_last_id_junction=mysqli_query($GLOBALS['con'],"SELECT `junction_id` FROM `vehicle_maker_junction` ORDER BY `auto` DESC LIMIT 1");
						$next_id_junction=(mysqli_num_rows($get_last_id_junction)==1)?(mysqli_fetch_assoc($get_last_id_junction)['junction_id']):1;
					///-----//Generate New Unique Id

						$junction_added=true;
						foreach ($vehicles_senetized_array as $via) {
							$next_id_junction++;
							$insert_junction=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_maker_junction`(`junction_id`, `junction_vehicle_id_fk`, `junction_maker_id_fk`, `junction_status`) VALUES ('$next_id_junction','".$via."','$next_id','ACT')");
							if(!$insert_junction){
								$junction_added=false;
							}
						}


							//----if maker is added  and vehicles array also added return true
						if($junction_added && $insert_maker){
							$status=true;
							$message="Added Successfuly";
						}else{
							$message=SOMETHING_WENT_WROG;
						}




					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Maker name already exists";
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

	function vehicles_makers_details($param){
		$status=false;
		$message=null;
		$response=[];
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;


			$dataValidation=true;
			$InvalidDataMessage="";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
				break;	

				case 'eid':
				$details_for_id=$Enc->safeurlde($param['details_for_eid']);
				break;	
				default:
				$dataValidation=false;
				$InvalidDataMessage="Please provide valid details_for parameter";
				goto ValidationCheck;
				break;
			}

			ValidationCheck:

			if($dataValidation){
				$q=mysqli_query($GLOBALS['con'],"SELECT `maker_id`,`maker_name` FROM `vehicle_makers` WHERE `maker_status`='ACT' AND `maker_id`='$details_for_id'");
				if(mysqli_num_rows($q)==1){
					$result=mysqli_fetch_assoc($q);


//-----get vehicle names for this maker id
					$get_vehicles=mysqli_query($GLOBALS['con'],"SELECT `vehicle_id`,  `vehicle_name` FROM `vehicles` JOIN `vehicle_maker_junction` ON `vehicle_maker_junction`.`junction_vehicle_id_fk`=`vehicles`.`vehicle_id` WHERE `junction_maker_id_fk`='".$result['maker_id']."' AND `junction_status`='ACT'");
					$vehicles_array=[];
					while ($veh_rows=mysqli_fetch_assoc($get_vehicles)) {
						$veh_row=[];
						$veh_row['id']=$veh_rows['vehicle_id'];
						$veh_row['name']=$veh_rows['vehicle_name'];
						array_push($vehicles_array,$veh_row);
					}
//----/get vehicle names for this maker id

					$row=[];
					$row['id']=$result['maker_id'];
					$row['eid']=$Enc->safeurlen($result['maker_id']);
					$row['name']=$result['maker_name'];
					$row['makes']=$vehicles_array;
					$response['details']=$row;
					$status=true;
				}else{
					$message="No records found";
				}
			}else{
				$message=$InvalidDataMessage;
			}




		}else{
			$message="Please provide details_for parameter";
		}

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	


	}	

	function vehicles_makers_list($param){
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

		$q="SELECT `maker_id`,`maker_name` FROM `vehicle_makers` WHERE `maker_status`='ACT'";





		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `maker_name`";
				break; 				 						
				default:
				$q .=" ORDER BY `auto`";
				break;
			}
		}else{
			$q .=" ORDER BY `auto`";	
		}





		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);		
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {



//-----get vehicle names for this maker id
			$get_vehicles=mysqli_query($GLOBALS['con'],"SELECT `vehicle_id`,  `vehicle_name` FROM `vehicles` JOIN `vehicle_maker_junction` ON `vehicle_maker_junction`.`junction_vehicle_id_fk`=`vehicles`.`vehicle_id` WHERE `junction_maker_id_fk`='".$rows['maker_id']."' AND `junction_status`='ACT'");
			$vehicles_array=[];
			while ($veh_rows=mysqli_fetch_assoc($get_vehicles)) {
				$veh_row=[];
				$veh_row['id']=$veh_rows['vehicle_id'];
				$veh_row['name']=$veh_rows['vehicle_name'];
				array_push($vehicles_array,$veh_row);
			}
//----/get vehicle names for this maker id



			$row=[];
			$row['id']=$rows['maker_id'];
			$row['eid']=$Enc->safeurlen($rows['maker_id']);
			$row['name']=$rows['maker_name'];
			$row['makes']=$vehicles_array;
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

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}


	function vehicles_makers_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0055', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$update_id=$Enc->safeurlde($param['update_eid']);			
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));

 				//---validate vehicle id
				$vehicles_senetized_array=[];
				if(isset($param['vehicles_id'])){
					$vehicles_id_array=explode(',', mysqli_real_escape_string($GLOBALS['con'],$param['vehicles_id']));
					foreach ($vehicles_id_array as $vehicles_id_array) {
						$validateVehicleId=mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` FROM `vehicles` WHERE `vehicle_id`='$vehicles_id_array' AND `vehicle_status`='ACT'");
						if(mysqli_num_rows($validateVehicleId)==1){
							array_push($vehicles_senetized_array,mysqli_fetch_assoc($validateVehicleId)['vehicle_id']);
						}
					}
				}
 				//---//validate vehicle id
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `maker_id` FROM `vehicle_makers` WHERE `maker_status`='ACT' AND `maker_name`='$name' AND NOT `maker_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){

					$update_maker=mysqli_query($GLOBALS['con'],"UPDATE vehicle_makers SET `maker_name`='$name', `maker_updated_on`='$time', `maker_updated_by`='$USERID' WHERE `maker_id`='$update_id'");

					if($update_maker){


				 	///-----Generate New Unique Id
						$get_last_id_junction=mysqli_query($GLOBALS['con'],"SELECT `junction_id` FROM `vehicle_maker_junction` ORDER BY `auto` DESC LIMIT 1");
						$next_id_junction=(mysqli_num_rows($get_last_id_junction)==1)?(mysqli_fetch_assoc($get_last_id_junction)['junction_id']):1;
					///-----//Generate New Unique Id
						$delete_old_junction_rows=mysqli_query($GLOBALS['con'],"DELETE FROM `vehicle_maker_junction` WHERE  `junction_maker_id_fk`='$update_id'");
						$junction_added=true;
						foreach ($vehicles_senetized_array as $via) {							
							$next_id_junction++;
							$insert_junction=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_maker_junction`(`junction_id`, `junction_vehicle_id_fk`, `junction_maker_id_fk`, `junction_status`) VALUES ('$next_id_junction','".$via."','$update_id','ACT')");
							if(!$insert_junction){
								$junction_added=false;
							}
						}


							//----if maker is added  and vehicles array also added return true
						if($junction_added && $update_maker){
							$status=true;
							$message="Updated Successfuly";
						}else{
							$message=SOMETHING_WENT_WROG;
						}




					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Maker name already exists";
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

	function vehicles_makers_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0056', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `maker_id` FROM `vehicle_makers` WHERE `maker_id`='$delete_eid' AND NOT `maker_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_makers` SET `maker_status`='DLT',`maker_deleted_on`='$time',`maker_deleted_by`='$USERID' WHERE `maker_id`='$delete_eid'");
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