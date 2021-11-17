<?php

class PreventiveMaintenance
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `pm_id` from `sm_preventive_maintenance` WHERE `pm_id`='$id' AND `pm_id_status`='ACT'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function preventive_maintenance_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$dataValidation=true;
		$InvalidDataMessage="";
		if(!in_array('P0217', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}
		if(isset($param['job_work_id']) && $param['job_work_id']!=""){
			$job_work_id=senetize_input($param['job_work_id']);
			include_once APPROOT.'/models/maintenance/masters/JobWork.php';
			$JobWork=new JobWork;
			if(!$JobWork->isValidId($param['job_work_id'])){
				$InvalidDataMessage="Invalid job work id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide job work id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['mode']) && $param['mode']!=""){
			$mode=senetize_input($param['mode']);
			if(!in_array($mode, ['DAYS','MILES','HOURS']))
			{
				$InvalidDataMessage="Invalid mode";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide mode";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['mode']) && $param['mode']!=""){
			$mode=senetize_input($param['mode']);
			if(!in_array($mode, ['DAYS','MILES','HOURS']))
			{
				$InvalidDataMessage="Invalid mode";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide mode";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['value']) && $param['value']!=""){
			$value=senetize_input($param['value']);
			if(!validate_int($value))
			{
				$InvalidDataMessage="Invalid value";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide value";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['advance_alert']) && $param['advance_alert']!=""){
			$advance_alert=senetize_input($param['advance_alert']);
			if(!validate_int($advance_alert))
			{
				$InvalidDataMessage="Invalid advance alert";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide advance alert";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['vehicle_type_id']) && $param['vehicle_type_id']!=""){
			$vehicle_type_id=senetize_input($param['vehicle_type_id']);
			include_once APPROOT.'/models/masters/Vehicles.php';
			$Vehicles=new Vehicles;
			if(!$Vehicles->isValidId($param['vehicle_type_id'])){
				$InvalidDataMessage="Invalid  id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide vehicle type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


//---check duplicacy 
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` WHERE `pm_vehicle_type_id`='$vehicle_type_id' AND `pm_job_work_id_fk`='$job_work_id'"))>0){
					$InvalidDataMessage="This Preventive Maintenance already exists";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;
					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` ORDER BY `pm_auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['pm_id'])+1:0;
					///-----//Generate New Unique Id

			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_preventive_maintenance`(`pm_id`,`pm_job_work_id_fk`,`pm_value`, `pm_advance_alert`, `pm_mode`, `pm_vehicle_type_id`,`pm_id_status`,`pm_added_on`,`pm_added_by`) VALUES ('$next_id','$job_work_id','$value','$advance_alert','$mode','$vehicle_type_id','ACT','$time','$USERID')");
			if($insert)
			{
				$status=true;
				$message="Added Successfuly";	
			}else
			{
				$message=SOMETHING_WENT_WROG;
			}
		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];

	}

	function preventive_maintenance_details($param)
	{
		$status=false;
		$message=null;
		$response=[];
		$dataValidation=true;
		$InvalidDataMessage="";
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		if(isset($param['eid']) && $param['eid']!=""){
			$id=$Enc->safeurlde($param['eid']);
		}else{
			$InvalidDataMessage="Please provide eid";
			$dataValidation=false;
			goto ValidationChecker;	
		}

		ValidationChecker:
		if($dataValidation){

			$qEx=mysqli_query($GLOBALS['con'],"SELECT `pm_auto`, `pm_id`,`pm_job_work_id_fk`, `job_work_name`,`pm_value`, `pm_advance_alert`, `pm_mode`,`pm_vehicle_type_id` FROM `sm_preventive_maintenance` LEFT JOIN `sm_job_work` ON `sm_preventive_maintenance`.`pm_job_work_id_fk`=`sm_job_work`.`job_work_id`  WHERE `pm_id_status`='ACT' AND `pm_id`='$id'");
			$details='';
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$res=mysqli_fetch_assoc($qEx);
				$details=[
					'id'=>$res['pm_id'],
					'eid'=>$Enc->safeurlen($res['pm_id']),
					'job_work_id'=>$res['pm_job_work_id_fk'],
					'job_work_name'=>$res['job_work_name'],
					'value'=>$res['pm_value'],
					'mode'=>$res['pm_mode'],
					'advance_alert'=>$res['pm_advance_alert'],
					'vehicle_type_id'=>$res['pm_vehicle_type_id'],
				];
			}else{
				$message="Invalid eid";
			}
			$response['details']=$details;
		}else{
			$message=$InvalidDataMessage;
		}
		
		return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}





	function preventive_maintenance_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `pm_auto`, `pm_id`, `job_work_name`,`pm_value`, `pm_advance_alert`, `pm_mode`,`pm_vehicle_type_id` FROM `sm_preventive_maintenance` LEFT JOIN `sm_job_work` ON `sm_preventive_maintenance`.`pm_job_work_id_fk`=`sm_job_work`.`job_work_id`  WHERE `job_work_id_status`='ACT' AND `pm_id_status`='ACT' ORDER BY `job_work_name`");

		$list=[];
		while ($rows=mysqli_fetch_assoc($get))
		{
			array_push($list,[
				'id'=>$rows['pm_id'],
				'eid'=>$Enc->safeurlen($rows['pm_id']),
				'name'=>$rows['job_work_name'],
				'value'=>$rows['pm_value'],
				'advance_alert'=>$rows['pm_advance_alert'],
				'mode'=>$rows['pm_mode'],
				'vehicle_type_id'=>$rows['pm_vehicle_type_id']
			]);
		}
		$response=[];
		$response['list']=$list;
		if(count($list)>0)
		{
			$status=true;
		}
		else
		{
			$message="No records found";
		} 		

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}


	function preventive_maintenance_update($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$dataValidation=true;
		$InvalidDataMessage="";

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		if(isset($param['update_eid']) && $param['update_eid']!=""){
			$update_id=$Enc->safeurlde($param['update_eid']);
		}else{
			$InvalidDataMessage="Please provide update eid";
			$dataValidation=false;
			goto ValidationChecker;	
		}


		if(!in_array('P0219', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['job_work_id']) && $param['job_work_id']!=""){
			$job_work_id=senetize_input($param['job_work_id']);
			include_once APPROOT.'/models/maintenance/masters/JobWork.php';
			$JobWork=new JobWork;
			if(!$JobWork->isValidId($param['job_work_id'])){
				$InvalidDataMessage="Invalid job work id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide job work id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['mode']) && $param['mode']!=""){
			$mode=senetize_input($param['mode']);
			if(!in_array($mode, ['DAYS','MILES','HOURS']))
			{
				$InvalidDataMessage="Invalid mode";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide mode";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['mode']) && $param['mode']!=""){
			$mode=senetize_input($param['mode']);
			if(!in_array($mode, ['DAYS','MILES','HOURS']))
			{
				$InvalidDataMessage="Invalid mode";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide mode";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['value']) && $param['value']!=""){
			$value=senetize_input($param['value']);
			if(!validate_int($value))
			{
				$InvalidDataMessage="Invalid value";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide value";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['advance_alert']) && $param['advance_alert']!=""){
			$advance_alert=senetize_input($param['advance_alert']);
			if(!validate_int($advance_alert))
			{
				$InvalidDataMessage="Invalid advance alert";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide advance alert";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['vehicle_type_id']) && $param['vehicle_type_id']!=""){
			$vehicle_type_id=senetize_input($param['vehicle_type_id']);
			include_once APPROOT.'/models/masters/Vehicles.php';
			$Vehicles=new Vehicles;
			if(!$Vehicles->isValidId($param['vehicle_type_id'])){
				$InvalidDataMessage="Invalid  id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide vehicle type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


//---check duplicacy 
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` WHERE `pm_vehicle_type_id`='$vehicle_type_id' AND `pm_job_work_id_fk`='$job_work_id' AND NOT `pm_id`='$update_id'"))>0){
					$InvalidDataMessage="This Preventive Maintenance already exists";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_preventive_maintenance` SET `pm_job_work_id_fk`='$job_work_id',`pm_value`='$value', `pm_advance_alert`='$advance_alert', `pm_mode`='$mode', `pm_vehicle_type_id`='$vehicle_type_id',`pm_updated_on`='$time',`pm_updated_by`='$USERID' WHERE `pm_id`='$update_id'");
			if($update)
			{
				$status=true;
				$message="Updated Successfuly";	
			}else
			{
				$message=SOMETHING_WENT_WROG;
			}
		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];

	}


	function preventive_maintenance_delete($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$dataValidation=true;
		$InvalidDataMessage="";

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		if(isset($param['delete_eid']) && $param['delete_eid']!=""){
			$delete_id=$Enc->safeurlde($param['delete_eid']);
		}else{
			$InvalidDataMessage="Please provide delete eid";
			$dataValidation=false;
			goto ValidationChecker;	
		}


		if(!in_array('P0220', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_preventive_maintenance` SET `pm_id_status`='DEL',`pm_deleted_on`='$time',`pm_deleted_by`='$USERID' WHERE `pm_id`='$delete_id'");
			if($update)
			{
				$status=true;
				$message="Deleted Successfuly";	
			}else
			{
				$message=SOMETHING_WENT_WROG;
			}
		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];

	}

}
?>