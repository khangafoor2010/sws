<?php
/**
 *
 *//*
class TrucksModels
{

	private $vehicle_db_ref=null;
	function __construct(){
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` FROM `vehicles` WHERE `vehicle_db_ref_key`='TRUCK' AND `vehicle_status`='ACT'"))==1){
			$this->vehicle_db_ref='1';
			echo $this->vehicle_db_ref;
		}
	}
	function trucks_models_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P58', USER_PRIV)){


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` WHERE `model_status`='ACT' AND `model_vehicle_id_fk`='1' AND `model_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){

 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['model_id'])+1:0;
					///-----//Generate New Unique Id

					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `vehicle_models`( `model_id`,`model_name`,`model_vehicle_id_fk`, `model_status`, `model_added_on`, `model_added_by`) VALUES ('$next_id','$name','1','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
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

	function trucks_models_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `model_id`, `model_name`,`maker_name` FROM `vehicle_models` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`vehicle_models`.`model_maker_id_fk` WHERE `model_status`='ACT' AND `model_vehicle_id_fk`='1'";

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
				$row['name']=$rows['model_name'];
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

	function trucks_models_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `model_id`, `model_name`,`maker_name` FROM `vehicle_models` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`vehicle_models`.`model_maker_id_fk` WHERE `model_status`='ACT' AND `model_vehicle_id_fk`='1'
			");
		$list=[];
		while ($rows=mysqli_fetch_assoc($get)) {
			$row=[];
			$row['id']=$rows['model_id'];
			$row['eid']=$Enc->safeurlen($rows['model_id']);
			$row['name']=$rows['model_name'];
			$row['maker']=$rows['maker_name'];
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


	function trucks_models_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P60', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `model_id` FROM `vehicle_models` WHERE `model_status`='ACT' AND `model_name`='$name' AND NOT `model_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"UPDATE `vehicle_models` SET `model_name`='$name',`model_updated_on`='$time',`model_updated_by`='$USERID' WHERE `model_id`='$update_id'");
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

	function trucks_models_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P61', USER_PRIV)){


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