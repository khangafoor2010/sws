<?php

class RepairOrderStage
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `stage_id` from `sm_repair_order_stage` WHERE `stage_id`='$id' AND `stage_id_status`='ACT'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	function repair_order_stage_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$dataValidation=true;
		$InvalidDataMessage="";
		if(!in_array('P0197', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
			$name=senetize_input($param['name']);
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `stage_id` FROM `sm_repair_order_stage` WHERE `stage_id_status`='ACT' AND `stage_name`='$name'"))>0)
			{
				$InvalidDataMessage="$name already exists";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide name";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;
					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `stage_id` FROM `sm_repair_order_stage` ORDER BY `stage_auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['stage_id'])+1:0;
					///-----//Generate New Unique Id

			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_stage`(`stage_id`,`stage_name`,`stage_id_status`,`stage_added_on`,`stage_added_by`) VALUES ('$next_id','$name','ACT','$time','$USERID')");
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

	function repair_order_stage_details($param)
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

			$qEx=mysqli_query($GLOBALS['con'],"SELECT `stage_id`,`stage_name` FROM `sm_repair_order_stage` WHERE `stage_id_status`='ACT' AND `stage_id`='$id'");
			$details='';
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$res=mysqli_fetch_assoc($qEx);
				$details=[
					'id'=>$res['stage_id'],
					'eid'=>$Enc->safeurlen($res['stage_id']),
					'name'=>$res['stage_name'],
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





	function repair_order_stage_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `stage_auto`, `stage_id`, `stage_name`FROM `sm_repair_order_stage`  WHERE `stage_id_status`='ACT' ORDER BY `stage_name`");

		$list=[];
		while ($rows=mysqli_fetch_assoc($get))
		{
			array_push($list,[
				'id'=>$rows['stage_id'],
				'eid'=>$Enc->safeurlen($rows['stage_id']),
				'name'=>$rows['stage_name'],
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


	function repair_order_stage_update($param)
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


		if(!in_array('P0199', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
			$name=senetize_input($param['name']);
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `stage_id` FROM `sm_repair_order_stage` WHERE `stage_id_status`='ACT' AND `stage_name`='$name' AND NOT `stage_id`='$update_id'"))>0)
			{
				$InvalidDataMessage="$name already exists";
				$dataValidation=false;
				goto ValidationChecker;	

			}

		}else{
			$InvalidDataMessage="Please provide name";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_stage` SET `stage_name`='$name',`stage_updated_on`='$time',`stage_updated_by`='$USERID' WHERE `stage_id`='$update_id'");
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


	function repair_order_stage_delete($param)
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


		if(!in_array('P0200', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_stage` SET `stage_id_status`='DEL',`stage_deleted_on`='$time',`stage_deleted_by`='$USERID' WHERE `stage_id`='$delete_id'");
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