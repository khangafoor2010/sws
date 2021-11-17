<?php

class RepairOrderType
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `type_id` from `sm_repair_order_type` WHERE `type_id`='$id' AND `type_id_status`='ACT'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	function repair_order_type_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$dataValidation=true;
		$InvalidDataMessage="";
		if(!in_array('P0191', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
			$name=senetize_input($param['name']);
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `type_id` FROM `sm_repair_order_type` WHERE `type_id_status`='ACT' AND `type_name`='$name'"))>0)
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
		if(isset($param['class_id']) && $param['class_id']!=""){
			$class_id=senetize_input($param['class_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderClass.php';
			$RepairOrderClass=new RepairOrderClass;
			if(!$RepairOrderClass->isValidId($param['class_id'])){
				$InvalidDataMessage="Invalid class id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide class id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;
					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `type_id` FROM `sm_repair_order_type` ORDER BY `type_auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['type_id'])+1:0;
					///-----//Generate New Unique Id

			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_type`(`type_id`,`type_name`,`type_class_id_fk`,`type_id_status`,`type_added_on`,`type_added_by`) VALUES ('$next_id','$name','$class_id','ACT','$time','$USERID')");
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

	function repair_order_type_details($param)
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

			$qEx=mysqli_query($GLOBALS['con'],"SELECT `type_id`,`type_name`,`type_class_id_fk` FROM `sm_repair_order_type` WHERE `type_id_status`='ACT' AND `type_id`='$id'");
			$details=[];
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$res=mysqli_fetch_assoc($qEx);
				$details=[
					'id'=>$res['type_id'],
					'eid'=>$Enc->safeurlen($res['type_id']),
					'name'=>$res['type_name'],
					'class_id'=>$res['type_class_id_fk'],
				];
			}else{
				$message="Invalid eid";
			}
		}else{
			$message=$InvalidDataMessage;
		}
		$response['details']=$details;
		return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}





	function repair_order_type_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `type_auto`, `type_id`, `type_name`, `class_name` FROM `sm_repair_order_type` LEFT JOIN `sm_repair_order_class` ON `sm_repair_order_type`.`type_class_id_fk`=`sm_repair_order_class`.`class_id` WHERE `type_id_status`='ACT' ORDER BY `type_name`");

		$list=[];
		while ($rows=mysqli_fetch_assoc($get))
		{
			array_push($list,[
				'id'=>$rows['type_id'],
				'eid'=>$Enc->safeurlen($rows['type_id']),
				'name'=>$rows['type_name'],
				'class_name'=>$rows['class_name'],
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


	function repair_order_type_update($param)
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


		if(!in_array('P0193', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
			$name=senetize_input($param['name']);
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `type_id` FROM `sm_repair_order_type` WHERE `type_id_status`='ACT' AND `type_name`='$name' AND NOT `type_id`='$update_id'"))>0)
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
		if(isset($param['class_id']) && $param['class_id']!=""){
			$class_id=senetize_input($param['class_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderClass.php';
			$RepairOrderClass=new RepairOrderClass;
			if(!$RepairOrderClass->isValidId($param['class_id'])){
				$InvalidDataMessage="Invalid class id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide class id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_type` SET `type_name`='$name',`type_class_id_fk`='$class_id',`type_updated_on`='$time',`type_updated_by`='$USERID' WHERE `type_id`='$update_id'");
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


	function repair_order_type_delete($param)
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


		if(!in_array('P0194', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_order_type` SET `type_id_status`='DEL',`type_deleted_on`='$time',`type_deleted_by`='$USERID' WHERE `type_id`='$delete_id'");
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