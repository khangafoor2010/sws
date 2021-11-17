<?php

class RepairOrders
{

	function isValidId($id)
	{
		return (mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `ro_id` from `sm_repair_orders` WHERE `ro_id`='".senetize_input($id)."'"))==1);
	}

	function repair_order_status_update($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";


		if(isset($param['eid']) && $param['eid']!=""){
			$id=$Enc->safeurlde($param['eid']);
			if(!$this->isValidId($id)){
				$InvalidDataMessage="Invalid repair order eid";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide repair order eid";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['status']) && $param['status']!=""){
			$status=senetize_input($param['status']);
			if(!in_array($status, ['OPEN','CLOSED'])){
				$InvalidDataMessage="Invalid repair";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide status";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		
		ValidationChecker:

		if($dataValidation)
		{
			$execution=true;
			$time=time();
			$USERID=USER_ID;

			$insert=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_orders` SET `ro_status_id_fk`='$status',`ro_status_updated_on`='$time',`ro_status_updated_by`='$USERID' WHERE `ro_id`='$id'");

			if(!$insert){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

				///---------//insert issue
			executionChecker:
			if($execution){
				$status=true;
				$message="Status updated successfuly";
			}

		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}
	function repair_order_follow_up_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page']))
		{
			$page=intval(senetize_input($param['page']));
		}
		if($page<1)
		{
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;



		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";


		if(isset($param['repair_order_eid']) && $param['repair_order_eid']!=""){
			$repair_order_id=$Enc->safeurlde($param['repair_order_eid']);
			if(!$this->isValidId($repair_order_id)){
				$InvalidDataMessage="Invalid repair order id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide repair order id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		ValidationChecker:

		if($dataValidation){
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `follow_up_description`, `follow_up_next_datetime`, `follow_up_id_status`,`follow_up_added_on`,`follow_up_added_by` FROM `sm_repair_order_follow_ups` WHERE `follow_up_id_status`='ACT' AND `follow_up_repair_order_id_fk`='$repair_order_id' ORDER BY `follow_up_added_on` DESC");

			$list=[];
			while ($rows=mysqli_fetch_assoc($qEx)) 
			{	

				include_once APPROOT.'/models/masters/Users.php';
				$Users=new Users;
				$added_user=$Users->user_basic_details($rows['follow_up_added_by']);
				$added_by_user_code=$added_user['user_code'];
				$added_on_datetime=dateTimeFromDbTimestamp($rows['follow_up_added_on']);
				array_push($list,[
					'description'=>$rows['follow_up_description'],
					'follow_up_next_date'=>dateFromDbToFormat($rows['follow_up_next_datetime']),
					'added_by_user_code'=>$added_by_user_code,
					'added_on_datetime'=>$added_on_datetime
				]);

			}
			$response['list']=$list;
			if(count($list)>0)
			{
				$status=true;
			}
			else
			{
				$message="No records found";
			}

		}else{
			$message=$InvalidDataMessage;
		}

		return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}


	function add_follow_ups($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";


		if(isset($param['repair_order_eid']) && $param['repair_order_eid']!=""){
			$repair_order_id=$Enc->safeurlde($param['repair_order_eid']);
			if(!$this->isValidId($repair_order_id)){
				$InvalidDataMessage="Invalid repair order id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide repair order id";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		if(isset($param['follow_up_next_date']) && $param['follow_up_next_date']!=""){
			if(isValidDateFormat(senetize_input($param['follow_up_next_date']))){
				$follow_up_next_date=date('Y-m-d', strtotime($param['follow_up_next_date']));

				//-----check if future date is selected
				if($follow_up_next_date<date('Y-m-d')){
					$InvalidDataMessage="Next follow date can't be any past date";
					$dataValidation=false;
					goto ValidationChecker;					
				}

			}else{
				$InvalidDataMessage="Invalid next follow up date";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide next follow up date";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['description']) && $param['description']!=""){
			$description=senetize_input($param['description']);

		}else{
			$InvalidDataMessage="Please provide description";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		ValidationChecker:

		if($dataValidation)
		{
			$execution=true;
			$time=time();
			$USERID=USER_ID;
 					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `follow_up_id` FROM `sm_repair_order_follow_ups` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['follow_up_id'])+1:1;
					///-----//Generate New Unique Id

			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_follow_ups`( `follow_up_id`,`follow_up_repair_order_id_fk`, `follow_up_description`, `follow_up_next_datetime`, `follow_up_id_status`, `follow_up_added_on`, `follow_up_added_by`) VALUES ('$next_id','$repair_order_id','$description' ,'$follow_up_next_date','ACT','$time','$USERID')");

			if(!$insert){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

			//update last follow up in repair order main table=

			$update_last_follow_up=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_orders` SET `ro_last_follow_up`='$description',`ro_next_follow_up_datetime`='$follow_up_next_date' WHERE `ro_id`='$repair_order_id'");


			if(!$update_last_follow_up){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step B'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}
				///---------//insert issue
			executionChecker:
			if($execution){
				$status=true;
				$message="Added Successfuly";
			}

		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}



	function repair_order_update($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";

		if(!in_array('P0229', USER_PRIV)){
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['update_eid']) && $param['update_eid']!=""){
			$update_id=$Enc->safeurlde($param['update_eid']);

				//-------validate update id
			$get_detail_q=mysqli_query($GLOBALS['con'],"SELECT  `ro_id`, `ro_detail_sr_no` FROM `sm_repair_orders` WHERE `ro_id`='$update_id' AND `ro_id_status`='ACT'");
			if(mysqli_num_rows($get_detail_q)==1){

				///-------calculate new detail sr number to be updated
				$new_detail_number=mysqli_fetch_assoc($get_detail_q)['ro_detail_sr_no']+1;
			}else{
				$InvalidDataMessage="Invalid eid";
				$dataValidation=false;
				goto ValidationChecker;				
			}

		}else{
			$InvalidDataMessage="Please provide update eid";
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
		if(isset($param['unit_type_id']) && $param['unit_type_id']!=""){
			$unit_type_id=senetize_input($param['unit_type_id']);
			include_once APPROOT.'/models/masters/Vehicles.php';
			$Vehicles=new Vehicles;

			if(!$Vehicles->isValidId($unit_type_id)){
				$InvalidDataMessage="Invalid unit type id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide unit type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['unit_id']) && $param['unit_id']!=""){
			$unit_id=senetize_input($param['unit_id']);

			if($unit_type_id=='TRUCK'){
				include_once APPROOT.'/models/masters/Trucks.php';
				$Trucks=new Trucks;

				if(!$Trucks->isValidId($unit_id)){
					$InvalidDataMessage="Invalid unit id".$unit_type_id;
					$dataValidation=false;
					goto ValidationChecker;
				}	
			}elseif ($unit_type_id=='TRAILER') {
				include_once APPROOT.'/models/masters/Trailers.php';
				$Trailers=new Trailers;

				if(!$Trailers->isValidId($unit_id)){
					$InvalidDataMessage="Invalid unit id";
					$dataValidation=false;
					goto ValidationChecker;
				}
			}else{
				$InvalidDataMessage="Invalid unit id b".$unit_id;
				$dataValidation=false;
				goto ValidationChecker;

			}


		}else{
			$InvalidDataMessage="Please provide unit id";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=senetize_input($param['driver_id']);
			include_once APPROOT.'/models/masters/Drivers.php';
			$Drivers=new Drivers;

			if(!$Drivers->isValidId($driver_id)){
				$InvalidDataMessage="Invalid driver A";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide driver id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['type_id']) && $param['type_id']!=""){
			$type_id=senetize_input($param['type_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderType.php';
			$RepairOrderType=new RepairOrderType;

			if(!$RepairOrderType->isValidId($type_id)){
				$InvalidDataMessage="Invalid repair type";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['stage_id']) && $param['stage_id']!=""){
			$stage_id=senetize_input($param['stage_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderStage.php';
			$RepairOrderStage=new RepairOrderStage;

			if(!$RepairOrderStage->isValidId($stage_id)){
				$InvalidDataMessage="Invalid repair stage id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide stage id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['start_date']) && $param['start_date']!=""){
			if(isValidDateFormat(senetize_input($param['start_date']))){
				$start_date=date('Y-m-d', strtotime($param['start_date']));
			}else{
				$InvalidDataMessage="Invalid start date";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide start date";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['start_time']) && $param['start_time']!=""){
			if(isValidTimeFormat(senetize_input($param['start_time']))){
				$start_time=date('H:i', strtotime($param['start_time']));
			}else{
				$InvalidDataMessage="Invalid start time";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide start time";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		


		$contact_person=(isset($param['contact_person']))?senetize_input($param['contact_person']):'';
		$contact_no=(isset($param['contact_no']))?senetize_input($param['contact_no']):'';



				//-----data validation ends

				////----------validate issue
		if(isset($param['issues']))
		{
			$issues=$param['issues'];
			$issues_array_senetized=[];
			foreach ($issues as $issue) 
			{
					//----validate category
				if(isset($issue['category_id'])){
					include_once APPROOT.'/models/maintenance/masters/RepairOrderCategory.php';
					$RepairOrderCategory=new RepairOrderCategory;

					if(!$RepairOrderCategory->isValidId(senetize_input($issue['category_id']))){
						$InvalidDataMessage="Invalid issue category id";
						$dataValidation=false;
						goto ValidationChecker;
					}
					
				}else{
					$InvalidDataMessage="Please provide category id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate category



					//----validate criticality level
				if(isset($issue['criticality_level_id'])){
					include_once APPROOT.'/models/maintenance/masters/RepairOrderCriticalityLevel.php';
					$RepairOrderCriticalityLevel=new RepairOrderCriticalityLevel;

					if(!$RepairOrderCriticalityLevel->isValidId(senetize_input($issue['criticality_level_id']))){
						$InvalidDataMessage="Invalid issue category id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide criticality level id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate criticality level



					//----validdate job work
				if(isset($issue['job_work_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWork.php';
					$JobWork=new JobWork;
					if(!$JobWork->isValidId(senetize_input($issue['job_work_id']))){
						$InvalidDataMessage="Invalid issue job work id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide job work id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate job work



					//----validdate issue reported
				if(isset($issue['issue_reported'])){
					$issue_reported=senetize_input($issue['issue_reported']);
					$issue_array_senetized['issue_reported']=$issue_reported;
				}else{
					$InvalidDataMessage="Please provide issue reported";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate issue reported

					//----validdate issue description
				if(isset($issue['issue_description'])){
					$issue_description=senetize_input($issue['issue_description']);
					$issue_array_senetized['issue_description']=$issue_description;
				}else{
					$InvalidDataMessage="Please provide issue description";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validdate issue description
				array_push($issues_array_senetized,[
					'category_id'=>senetize_input($issue['category_id']),
					'criticality_level_id'=>senetize_input($issue['criticality_level_id']),
					'job_work_id'=>senetize_input($issue['job_work_id']),
					'issue_reported'=>senetize_input($issue['issue_reported']),
					'issue_description'=>senetize_input($issue['issue_description']),
				]);
			}
		}else{
			$InvalidDataMessage="Please provide issues list";
			$dataValidation=false;
			goto ValidationChecker;			
		}

		ValidationChecker:

		if($dataValidation)
		{
			$execution=true;
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"
				UPDATE `sm_repair_orders` SET 
				`ro_detail_sr_no`='$new_detail_number', `ro_class_id_fk`='$class_id', `ro_vehicle_type_id_fk`='$unit_type_id', `ro_vehicle_id_fk`='$unit_id', `ro_driver_id_fk`='$driver_id', `ro_type_id_fk`='$type_id', `ro_stage_id_fk`='$stage_id', `ro_start_date`='$start_date', `ro_start_time`='$start_time',`ro_contact_person`='$contact_person', `ro_contact_number`='$contact_no', `ro_id_status`='ACT', `ro_updated_on`='$time', `ro_updated_by`='$USERID' WHERE `ro_id`='$update_id'");

			if(!$update){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

						///-----Generate New Unique Id
			$last_issue_id=mysqli_query($GLOBALS['con'],"SELECT `issue_id` FROM `sm_repair_order_issues` ORDER BY `auto` DESC LIMIT 1");
			$next_issue_id=(mysqli_num_rows($last_issue_id)==1)?mysqli_fetch_assoc($last_issue_id)['issue_id']:0;

						///-----//Generate New Unique Id

					//-----insert issues list
			foreach ($issues_array_senetized as $is) 
			{
				$next_issue_id++;
				$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_issues`(`issue_id`, `issue_category_id_fk`, `issue_criticality_level_id_fk`, `issue_job_work_id_fk`, `issue_reported`, `issue_description`, `issue_repair_order_id_fk`, `issue_repair_order_detail_id_fk`) VALUES ('$next_issue_id','".$is['category_id']."','".$is['criticality_level_id']."','".$is['job_work_id']."','".$is['issue_reported']."','".$is['issue_description']."','$update_id','$new_detail_number')");
				if(!$insertStop)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step B'.mysqli_error($GLOBALS['con']);
					goto executionChecker;
				}
			}



			///---------delete the old issues

			$delete=mysqli_query($GLOBALS['con'],"DELETE FROM `sm_repair_order_issues` WHERE `issue_repair_order_id_fk`='$update_id' AND NOT `issue_repair_order_detail_id_fk`='$new_detail_number'");
				if(!$delete)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step C'.mysqli_error($GLOBALS['con']);
					goto executionChecker;
				}
					///---------//insert issue
			executionChecker:
			if($execution){
				$status=true;
				$message="Updated Successfuly";
			}

		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$unit_type_id];
	}

	function repair_order_details($param)
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
			$qEx=mysqli_query($GLOBALS['con'],"SELECT 
				`ro_id`, `ro_detail_sr_no`, `ro_class_id_fk`, `ro_vehicle_type_id_fk`,ro_vehicle_id_fk,  
				CASE `ro_vehicle_type_id_fk` 
				WHEN 'TRUCK' THEN `truck_code` 
				when 'TRAILER' THEN `trailer_code`
				END AS `vehicle_code`,

				CASE `ro_vehicle_type_id_fk` 
				WHEN 'TRUCK' THEN `truck_vin_number` 
				when 'TRAILER' THEN `trailer_vin_number`
				END AS `vehicle_vin_number`,
				`driver_id`,`driver_code`,`driver_name_first`,`ro_stage_id_fk`,`ro_type_id_fk`, `type_name`, `stage_name`, `ro_start_date`, `ro_start_time`, `ro_end_date`, `ro_end_time`, `ro_contact_person`, `ro_contact_number`, `ro_status_id_fk`

				FROM `sm_repair_orders`
				LEFT JOIN `drivers` on `sm_repair_orders`.`ro_driver_id_fk`=`drivers`.`driver_id`
				LEFT JOIN `sm_repair_order_type`  on `sm_repair_orders`.`ro_type_id_fk`=`sm_repair_order_type`.`type_id`
				LEFT JOIN `sm_repair_order_stage` on `sm_repair_orders`.`ro_stage_id_fk`=`sm_repair_order_stage`.`stage_id`
				LEFT JOIN `sm_repair_order_status` on `sm_repair_orders`.`ro_status_id_fk`=`sm_repair_order_status`.`status_id`
				LEFT JOIN `trucks`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trucks`.`truck_id`
				LEFT JOIN `trailers`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trailers`.`trailer_id`
				WHERE `sm_repair_orders`.`ro_id_status`='ACT' AND `ro_id`='$id'");
			$details='';
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$res=mysqli_fetch_assoc($qEx);
				//get issues list
				$issue_list_q=mysqli_query($GLOBALS['con'],"SELECT  `issue_category_id_fk`,`category_name`, `issue_criticality_level_id_fk`,`job_work_name`, `issue_job_work_id_fk`, `issue_reported`, `issue_description`  FROM `sm_repair_order_issues` LEFT JOIN `sm_repair_order_category` ON `sm_repair_order_issues`.`issue_category_id_fk`=`sm_repair_order_category`.`category_id` LEFT JOIN `sm_job_work` ON `sm_repair_order_issues`.`issue_job_work_id_fk`=`sm_job_work`.`job_work_id` WHERE `issue_repair_order_id_fk`='".$res['ro_id']."'");
				$issue_list=[];
				while ($res_il=mysqli_fetch_assoc($issue_list_q)) {
					array_push($issue_list,[
						'category'=>$res_il['category_name'],
						'category_id'=>$res_il['issue_category_id_fk'],
						'criticality_level_id'=>$res_il['issue_criticality_level_id_fk'],
						'job_work'=>$res_il['job_work_name'],
						'job_work_id'=>$res_il['issue_job_work_id_fk'],
						'issue_reported'=>$res_il['issue_reported'],
						'issue_description'=>$res_il['issue_description'],
					]);
				}

				$details=[
					'id'=>$res['ro_id'],
					'eid'=>$Enc->safeurlen($res['ro_id']),
					'class'=>$res['ro_class_id_fk'],
					'vehicle_type'=>$res['ro_vehicle_type_id_fk'],
					'vehicle_id'=>$res['ro_vehicle_id_fk'],				
					'vehicle_code'=>$res['vehicle_code'],
					'vehicle_vin_number'=>$res['vehicle_vin_number'],
					'driver_id'=>$res['driver_id'],
					'driver_code'=>$res['driver_code'],
					'driver_name'=>$res['driver_name_first'],
					'type_id'=>$res['ro_type_id_fk'],
					'type'=>$res['type_name'],
					'stage_id'=>$res['ro_stage_id_fk'],
					'stage'=>$res['stage_name'],
					'status'=>$res['ro_status_id_fk'],
					'contact_person'=>$res['ro_contact_person'],
					'contact_number'=>$res['ro_contact_number'],
					'start_date'=>dateFromDbToFormat($res['ro_start_date']),
					'start_time'=>timeFromDbTime($res['ro_start_time']),
					'end_date'=>dateFromDbToFormat($res['ro_end_date']),
					'end_time'=>timeFromDbTime($res['ro_end_time']),
					'issue_list'=>$issue_list
				];

				$response['details']=$details;
			}else{
				$message="Invalid eid";
			}
		}else{
			$message=$InvalidDataMessage;
		}
		
		return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}

	
	function repair_order_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page']))
		{
			$page=intval(senetize_input($param['page']));
		}
		if($page<1)
		{
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT 
		`ro_id`, `ro_detail_sr_no`, `ro_class_id_fk`, `ro_vehicle_type_id_fk`,  
		CASE `ro_vehicle_type_id_fk` 
		WHEN 'TRUCK' THEN `truck_code` 
		when 'TRAILER' THEN `trailer_code`
		END AS `vehicle_code`,
		`driver_code`,`driver_name_first`, `type_name`, `stage_name`, `ro_start_date`, `ro_start_time`, `ro_end_date`, `ro_end_time`, `ro_contact_person`, `ro_contact_number`, `ro_status_id_fk`,`ro_added_by`,`ro_added_on`, `ro_last_follow_up`, `ro_next_follow_up_datetime`

		FROM `sm_repair_orders`
		LEFT JOIN `drivers` on `sm_repair_orders`.`ro_driver_id_fk`=`drivers`.`driver_id`
		LEFT JOIN `sm_repair_order_type`  on `sm_repair_orders`.`ro_type_id_fk`=`sm_repair_order_type`.`type_id`
		LEFT JOIN `sm_repair_order_stage` as `E` on `sm_repair_orders`.`ro_stage_id_fk`=`E`.`stage_id`
		LEFT JOIN `sm_repair_order_status` on `sm_repair_orders`.`ro_status_id_fk`=`sm_repair_order_status`.`status_id`
		LEFT JOIN `trucks`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trucks`.`truck_id`
		LEFT JOIN `trailers`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trailers`.`trailer_id`
		WHERE `sm_repair_orders`.`ro_id_status`='ACT' ";

		////---------------Apply filters

		if(isset($param['class_id']) && $param['class_id']!=""){
			$q .=" AND `ro_class_id_fk` ='".senetize_input($param['class_id'])."'";
		}
		if(isset($param['id']) && $param['id']!=""){
			$id=senetize_input($param['id']);
			$q .=" AND `ro_id` LIKE '%$id%'";
		}
		if(isset($param['vehicle_type']) && $param['vehicle_type']!=""){
			$q .=" AND `ro_vehicle_type_id_fk` ='".senetize_input($param['vehicle_type'])."'";

			//--unit id filter will work only if unit type is also selected
			if(isset($param['vehicle_id']) && $param['vehicle_id']!=""){
				$q .=" AND `ro_vehicle_id_fk` ='".senetize_input($param['vehicle_id'])."'";
			}
		}

		if(isset($param['status_id']) && $param['status_id']!=""){
			$q .=" AND `ro_status_id_fk` ='".senetize_input($param['status_id'])."'";
		}
		if(isset($param['type_id']) && $param['type_id']!=""){
			$q .=" AND `ro_type_id_fk` ='".senetize_input($param['type_id'])."'";
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$q .=" AND `driver_id` ='".senetize_input($param['driver_id'])."'";
		}
		if(isset($param['stage_id']) && $param['stage_id']!=""){
			$q .=" AND `stage_id` ='".senetize_input($param['stage_id'])."'";
		}


		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'id':
				$q .=" ORDER BY `ro_id`";
				break;		
				default:
				$q .=" ORDER BY `ro_added_on` DESC";
				break;
			}
		}else{
			$q .=" ORDER BY `ro_added_on` DESC";	
		}
		
		
		////---------------/Apply filters

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) 
		{	

			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;
			$added_user=$Users->user_basic_details($rows['ro_added_by']);
			$added_by_user_code=$added_user['user_code'];
			$added_on_datetime=dateTimeFromDbTimestamp($rows['ro_added_on']);
			array_push($list,[
				'id'=>$rows['ro_id'],
				'eid'=>$Enc->safeurlen($rows['ro_id']),
				'class'=>$rows['ro_class_id_fk'],
				'vehicle_type'=>$rows['ro_vehicle_type_id_fk'],
				'vehicle_code'=>$rows['vehicle_code'],
				'driver_code'=>$rows['driver_code'],
				'driver_name'=>$rows['driver_name_first'],
				'type'=>$rows['type_name'],
				'stage'=>$rows['stage_name'],
				'status'=>$rows['ro_status_id_fk'],
				'start_date'=>dateFromDbToFormat($rows['ro_start_date']),
				'end_date'=>dateFromDbToFormat($rows['ro_end_date']),
				'last_follow_up'=>$rows['ro_last_follow_up'],
				'next_follow_up_datetime'=>dateFromDbDatetime($rows['ro_next_follow_up_datetime']),
				'added_by_user_code'=>$added_by_user_code,
				'added_on_datetime'=>$added_on_datetime
			]);

		}
		$response=[];
		$response['total']=$totalRows;
		$response['totalRows']=$totalRows;
		$response['totalPages']=ceil($totalRows/$batch);
		$response['currentPage']=$page;
		$response['resultFrom']=$from+1;
		$response['resultUpto']=$range;
		$response['list']=$list;
		if(count($list)>0)
		{
			$status=true;
		}
		else
		{
			$message="No records found";
		} 		
		return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}

	function repair_order_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";

		if(!in_array('P0227', USER_PRIV)){
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
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
		if(isset($param['unit_type_id']) && $param['unit_type_id']!=""){
			$unit_type_id=senetize_input($param['unit_type_id']);
			include_once APPROOT.'/models/masters/Vehicles.php';
			$Vehicles=new Vehicles;

			if(!$Vehicles->isValidId($unit_type_id)){
				$InvalidDataMessage="Invalid unit type id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide unit type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['unit_id']) && $param['unit_id']!=""){
			$unit_id=senetize_input($param['unit_id']);

			if($unit_type_id=='TRUCK'){
				include_once APPROOT.'/models/masters/Trucks.php';
				$Trucks=new Trucks;

				if(!$Trucks->isValidId($unit_id)){
					$InvalidDataMessage="Invalid unit id".$unit_type_id;
					$dataValidation=false;
					goto ValidationChecker;
				}	
			}elseif ($unit_type_id=='TRAILER') {
				include_once APPROOT.'/models/masters/Trailers.php';
				$Trailers=new Trailers;

				if(!$Trailers->isValidId($unit_id)){
					$InvalidDataMessage="Invalid unit id";
					$dataValidation=false;
					goto ValidationChecker;
				}
			}else{
				$InvalidDataMessage="Invalid unit id b".$unit_id;
				$dataValidation=false;
				goto ValidationChecker;

			}





		}else{
			$InvalidDataMessage="Please provide unit id";
			$dataValidation=false;
			goto ValidationChecker;		
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=senetize_input($param['driver_id']);
			include_once APPROOT.'/models/masters/Drivers.php';
			$Drivers=new Drivers;

			if(!$Drivers->isValidId($driver_id)){
				$InvalidDataMessage="Invalid driver A";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide driver id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['type_id']) && $param['type_id']!=""){
			$type_id=senetize_input($param['type_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderType.php';
			$RepairOrderType=new RepairOrderType;

			if(!$RepairOrderType->isValidId($type_id)){
				$InvalidDataMessage="Invalid repair type";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide type id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['stage_id']) && $param['stage_id']!=""){
			$stage_id=senetize_input($param['stage_id']);
			include_once APPROOT.'/models/maintenance/masters/RepairOrderStage.php';
			$RepairOrderStage=new RepairOrderStage;

			if(!$RepairOrderStage->isValidId($stage_id)){
				$InvalidDataMessage="Invalid repair stage id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide stage id";
			$dataValidation=false;
			goto ValidationChecker;		
		}


		if(isset($param['start_date']) && $param['start_date']!=""){
			if(isValidDateFormat(senetize_input($param['start_date']))){
				$start_date=date('Y-m-d', strtotime($param['start_date']));
			}else{
				$InvalidDataMessage="Invalid start date";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide start date";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['start_time']) && $param['start_time']!=""){
			if(isValidTimeFormat(senetize_input($param['start_time']))){
				$start_time=date('H:i', strtotime($param['start_time']));
			}else{
				$InvalidDataMessage="Invalid start time";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide start time";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		


		$contact_person=(isset($param['contact_person']))?senetize_input($param['contact_person']):'';
		$contact_no=(isset($param['contact_no']))?senetize_input($param['contact_no']):'';


		$next_follow_up_date='0000-00-00';
		$follow_up_description="";
		if(isset($param['next_follow_up_date']) && $param['next_follow_up_date']!=""){
			if(isValidDateFormat(senetize_input($param['next_follow_up_date']))){
				$next_follow_up_date=date('Y-m-d', strtotime($param['next_follow_up_date']));
			}else{
				$InvalidDataMessage="Invalid next follow up date";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}
		$follow_up_description=(isset($param['follow_up_description']))?senetize_input($param['follow_up_description']):'';

				//-----data validation ends

				////----------validate issue
		if(isset($param['issues']))
		{
			$issues=$param['issues'];
			$issues_array_senetized=[];
			foreach ($issues as $issue) 
			{
					//----validate category
				if(isset($issue['category_id'])){
					include_once APPROOT.'/models/maintenance/masters/RepairOrderCategory.php';
					$RepairOrderCategory=new RepairOrderCategory;

					if(!$RepairOrderCategory->isValidId(senetize_input($issue['category_id']))){
						$InvalidDataMessage="Invalid issue category id";
						$dataValidation=false;
						goto ValidationChecker;
					}
					
				}else{
					$InvalidDataMessage="Please provide category id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate category



					//----validate criticality level
				if(isset($issue['criticality_level_id'])){
					include_once APPROOT.'/models/maintenance/masters/RepairOrderCriticalityLevel.php';
					$RepairOrderCriticalityLevel=new RepairOrderCriticalityLevel;

					if(!$RepairOrderCriticalityLevel->isValidId(senetize_input($issue['criticality_level_id']))){
						$InvalidDataMessage="Invalid issue category id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide criticality level id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate criticality level



					//----validdate job work
				if(isset($issue['job_work_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWork.php';
					$JobWork=new JobWork;
					if(!$JobWork->isValidId(senetize_input($issue['job_work_id']))){
						$InvalidDataMessage="Invalid issue job work id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide job work id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate job work



					//----validdate issue reported
				if(isset($issue['issue_reported'])){
					$issue_reported=senetize_input($issue['issue_reported']);
					$issue_array_senetized['issue_reported']=$issue_reported;
				}else{
					$InvalidDataMessage="Please provide issue reported";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate issue reported

					//----validdate issue description
				if(isset($issue['issue_description'])){
					$issue_description=senetize_input($issue['issue_description']);
					$issue_array_senetized['issue_description']=$issue_description;
				}else{
					$InvalidDataMessage="Please provide issue description";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validdate issue description
				array_push($issues_array_senetized,[
					'category_id'=>senetize_input($issue['category_id']),
					'criticality_level_id'=>senetize_input($issue['criticality_level_id']),
					'job_work_id'=>senetize_input($issue['job_work_id']),
					'issue_reported'=>senetize_input($issue['issue_reported']),
					'issue_description'=>senetize_input($issue['issue_description']),
				]);
			}
		}else{
			$InvalidDataMessage="Please provide issues list";
			$dataValidation=false;
			goto ValidationChecker;			
		}

		ValidationChecker:

		if($dataValidation)
		{
			$execution=true;
			$time=time();
			$USERID=USER_ID;
 					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `ro_id` FROM `sm_repair_orders` WHERE `ro_class_id_fk`='$class_id' ORDER BY `ro_auto` DESC LIMIT 1");
			$get_last_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['ro_id']):'000000000';


			switch ($class_id) {
				case 'SCHEDULE':
				$get_ro_id_prefix_b='SC'.date("y");
				break;
				case 'UNSCHEDULE':
				$get_ro_id_prefix_b='UN'.date("y");
				break;						
				default:
							# code...
				break;
			}

			if(substr($get_last_id,2,2)==date("y")){
				$next_id=$get_ro_id_prefix_b.sprintf('%05d',(intval(substr($get_last_id,4))+1));
			}else{
				$next_id=$get_ro_id_prefix_b.'00001';
			}


					///-----//Generate New Unique Id
			$insert=mysqli_query($GLOBALS['con'],"
				INSERT INTO `sm_repair_orders`
				(`ro_id`,`ro_detail_sr_no`, `ro_class_id_fk`, `ro_vehicle_type_id_fk`, `ro_vehicle_id_fk`, `ro_driver_id_fk`, `ro_type_id_fk`, `ro_stage_id_fk`, `ro_start_date`, `ro_start_time`,`ro_contact_person`, `ro_contact_number`,`ro_last_follow_up`,`ro_next_follow_up_datetime`, `ro_id_status`,`ro_status_id_fk`, `ro_added_on`, `ro_added_by`) 
				VALUES ('$next_id',1,'$class_id','$unit_type_id','$unit_id','$driver_id','$type_id','$stage_id','$start_date','$start_time','$contact_person','$contact_no','$follow_up_description','$next_follow_up_date','ACT','OPEN','$time','$USERID')");

			if(!$insert){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

						///-----Generate New Unique Id
			$last_issue_id=mysqli_query($GLOBALS['con'],"SELECT `issue_id` FROM `sm_repair_order_issues` ORDER BY `auto` DESC LIMIT 1");
			$next_issue_id=(mysqli_num_rows($last_issue_id)==1)?mysqli_fetch_assoc($last_issue_id)['issue_id']:0;

						///-----//Generate New Unique Id

					//-----insert issues list
			foreach ($issues_array_senetized as $is) 
			{
				$next_issue_id++;
				$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_issues`(`issue_id`, `issue_category_id_fk`, `issue_criticality_level_id_fk`, `issue_job_work_id_fk`, `issue_reported`, `issue_description`, `issue_repair_order_id_fk`, `issue_repair_order_detail_id_fk`) VALUES ('$next_issue_id','".$is['category_id']."','".$is['criticality_level_id']."','".$is['job_work_id']."','".$is['issue_reported']."','".$is['issue_description']."','$next_id',1)");
				if(!$insertStop)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step B'.mysqli_error($GLOBALS['con']);
					goto executionChecker;
				}
			}


				//-------if next follow up date OR current follow is added then create a follow up entry against this Repair Order

			if($follow_up_description!="" && $next_follow_up_date!="0000-00-00"){
 					///-----Generate New Unique Id
				$get_last_fu_id=mysqli_query($GLOBALS['con'],"SELECT `follow_up_id` FROM `sm_repair_order_follow_ups` ORDER BY `auto` DESC LIMIT 1");
				$next_fu_id=(mysqli_num_rows($get_last_fu_id)==1)?(mysqli_fetch_assoc($get_last_fu_id)['follow_up_id'])+1:1;
					///-----//Generate New Unique Id

				$insert_follow_up=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_follow_ups`( `follow_up_id`,`follow_up_repair_order_id_fk`, `follow_up_description`, `follow_up_next_datetime`, `follow_up_id_status`, `follow_up_added_on`, `follow_up_added_by`) VALUES ('$next_fu_id','$next_id','$follow_up_description' ,'$next_follow_up_date','ACT','$time','$USERID')");

				if(!$insert_follow_up)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step C'.mysqli_error($GLOBALS['con']);
					goto executionChecker;
				}
			}




					///---------//insert issue
		executionChecker:
		if($execution){
			$status=true;
			$message="Added Successfuly";
		}

	}else{
		$message=$InvalidDataMessage;
	}
	return ['status'=>$status,'message'=>$message,'response'=>$unit_type_id];
}
	function repair_order_delete($param)
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


		if(!in_array('P0230', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_orders` SET `ro_id_status`='DEL',`ro_deleted_on`='$time',`ro_deleted_by`='$USERID' WHERE `ro_id`='$delete_id'");
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
	/*
	function isValidId($id)
	{
		$id=senetize_input($id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `ro_id` from `sm_repair_orders` WHERE `ro_id`='$id'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}



	function repair_order_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0008', USER_PRIV))
		{
			if(isset($param['order_class_id']) && $param['order_class_id']!="")
			{
				$USERID=USER_ID;
				$time=time();
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
			    //-----data validation starts
				$dataValidation=true;
				$InvalidDataMessage="";

				$order_date_raw=(isset($param['order_date']))?senetize_input($param['order_date']):'00/00/0000';
				$order_date=isValidDateFormat($order_date_raw)?date('Y-m-d', strtotime($order_date_raw)):'0000-00-00';

				$order_class_id=0;
				if(isset($param['order_class_id']) && $param['order_class_id']!=""){
					$order_class_id=senetize_input($param['order_class_id']);
				}

				$order_status_id=0;
				if(isset($param['order_status_id']) && $param['order_status_id']!=""){
					$order_status_id=senetize_input($param['order_status_id']);
				}

				$order_driver_id=0;
				if(isset($param['order_driver_id']) && $param['order_driver_id']!=""){
					$order_driver_id=senetize_input($param['order_driver_id']);
				}

				$order_type_id=0;
				if(isset($param['order_type_id']) && $param['order_type_id']!=""){
					$order_type_id=senetize_input($param['order_type_id']);
				}

				$order_stage_id=0;
				if(isset($param['order_stage_id']) && $param['order_stage_id']!=""){
					$order_stage_id=senetize_input($param['order_stage_id']);
				}

				$order_start_date_raw=(isset($param['order_start_date']))?senetize_input($param['order_start_date']):'00/00/0000';
				$order_start_date=isValidDateFormat($order_start_date_raw)?date('Y-m-d', strtotime($order_start_date_raw)):'0000-00-00';		

				$order_start_time="00:00";
				if(isset($param['order_start_time']) && $param['order_start_time']!=""){
					$order_start_time=senetize_input($param['order_start_time']);
				}

				$order_end_date_raw=(isset($param['order_end_date']))?senetize_input($param['order_end_date']):'00/00/0000';
				$order_end_date=isValidDateFormat($order_end_date_raw)?date('Y-m-d', strtotime($order_end_date_raw)):'0000-00-00';

				$order_end_time="00:00";
				if(isset($param['order_end_time']) && $param['order_end_time']!=""){
					$order_end_time=senetize_input($param['order_end_time']);
				}

				$order_unitype_id=0;
				if(isset($param['order_unitype_id']) && $param['order_unitype_id']!=""){
					$order_unitype_id=senetize_input($param['order_unitype_id']);
				}

				$order_unit_no=0;
				if(isset($param['order_unit_no']) && $param['order_unit_no']!=""){
					$order_unit_no=senetize_input($param['order_unit_no']);
				}

				$order_refdoctype_id=0;
				if(isset($param['order_refdoctype_id']) && $param['order_refdoctype_id']!=""){
					$order_refdoctype_id=senetize_input($param['order_refdoctype_id']);
				}

				$order_refdoc_no=0;
				if(isset($param['order_refdoc_no']) && $param['order_refdoc_no']!=""){
					$order_refdoc_no=senetize_input($param['order_refdoc_no']);
				}

				$order_contact_person=0;
				if(isset($param['order_contact_person']) && $param['order_contact_person']!=""){
					$order_contact_person=senetize_input($param['order_contact_person']);
				}

				$order_contact_no=0;
				if(isset($param['order_contact_no']) && $param['order_contact_no']!=""){
					$order_contact_no=senetize_input($param['order_contact_no']);
				}
				//-----data validation ends

				////----------validate issue
				if(isset($param['stops'])){
					$stops=json_decode($param['stops'],true);
					$stops_array_senetized=[];
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['category_id']))
						{
							$category_id=senetize_input($stop['category_id']);
							$stop_item_senetized['category_id']=$category_id;
						}
						else
						{
							$InvalidDataMessage="Please provide category";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['criticality_level_id']))
						{
							$criticality_level_id=senetize_input($stop['criticality_level_id']);
							$stop_item_senetized['criticality_level_id']=$criticality_level_id;
						}
						else
						{
							$InvalidDataMessage="Please provide criticality level";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['job_work_id']))
						{
							$job_work_id=senetize_input($stop['job_work_id']);
							$stop_item_senetized['job_work_id']=$job_work_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['issue_reported']))
						{
							$issue_reported=senetize_input($stop['issue_reported']);
							$stop_item_senetized['issue_reported']=$issue_reported;
						}
						else
						{
							$InvalidDataMessage="Please provide issue reported";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['issue_description']))
						{
							$issue_description=senetize_input($stop['issue_description']);
							$stop_item_senetized['issue_description']=$issue_description;
						}
						else
						{
							$InvalidDataMessage="Please provide issue description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation){
 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `ro_id` FROM `sm_repair_orders` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['ro_id'])+1:0;
					///-----//Generate New Unique Id

					$insertheader=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_orders`(`ro_id`, `order_date`, `doctype_id`, `status_id`, `asset_id`, `driver_id`, `type_id`, `stage_id`, `start_date`, `start_time`, `end_date`, `end_time`, `class_id`, `contactperson`, `contactnumber`, `refdocname`, `refdocno`,`added_by`,`added_on`,`status`) VALUES ('$next_id','$order_date','$order_unitype_id','$order_status_id','$order_unit_no','$order_driver_id','$order_type_id','$order_stage_id','$order_start_date','$order_start_time','$order_end_date','$order_end_time','$order_class_id','$order_contact_person','$order_contact_no','$order_refdoctype_id','$order_refdoc_no',$USERID,$time,'ACT')");

					if($insertheader)
					{
						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `sm_repair_orders` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['ro_id']:100000;
						
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_detail`(`ro_id`, `category_id_fk`, `criticalitylevel_id_fk`, `jobwork_id_fk`, `issue_reported`, `issue_description`) VALUES ('$next_id','".$stop_row['category_id']."','".$stop_row['criticality_level_id']."','".$stop_row['job_work_id']."','".$stop_row['issue_reported']."','".$stop_row['issue_description']."')");
							if(!$insertStop)
							{
								$stop_inserted=false;
							}
						}
						///---------//insert issue

						if($stop_inserted)
						{
							$status=true;
							$message=count($stops_array_senetized);
						}
						else
						{
							$message=SOMETHING_WENT_WROG;
						}
					}
					else
					{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}
			}
			else
			{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}
		else
		{
			$message=NOT_AUTHORIZED_MSG;
		}
return ['status'=>$status,'message'=>$message,'response'=>$response];
	}

	

	function repair_order_update($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0010', USER_PRIV))
		{
			if(isset($param['update_eid']) && isset($param['update_eid']))
			{
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			    //-----data validation starts
 			    ///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;

				$dataValidation=true;
				$InvalidDataMessage="";

				$order_date_raw=(isset($param['order_date']))?senetize_input($param['order_date']):'00/00/0000';
				$order_date=isValidDateFormat($order_date_raw)?date('Y-m-d', strtotime($order_date_raw)):'0000-00-00';

				$order_class_id=0;
				if(isset($param['order_class_id']) && $param['order_class_id']!=""){
					$order_class_id=senetize_input($param['order_class_id']);
				}

				$order_status_id=0;
				if(isset($param['order_status_id']) && $param['order_status_id']!=""){
					$order_status_id=senetize_input($param['order_status_id']);
				}

				$order_driver_id=0;
				if(isset($param['order_driver_id']) && $param['order_driver_id']!=""){
					$order_driver_id=senetize_input($param['order_driver_id']);
				}

				$order_type_id=0;
				if(isset($param['order_type_id']) && $param['order_type_id']!=""){
					$order_type_id=senetize_input($param['order_type_id']);
				}

				$order_stage_id=0;
				if(isset($param['order_stage_id']) && $param['order_stage_id']!=""){
					$order_stage_id=senetize_input($param['order_stage_id']);
				}

				$order_start_date_raw=(isset($param['order_start_date']))?senetize_input($param['order_start_date']):'00/00/0000';
				$order_start_date=isValidDateFormat($order_start_date_raw)?date('Y-m-d', strtotime($order_start_date_raw)):'0000-00-00';		

				$order_start_time="00:00";
				if(isset($param['order_start_time']) && $param['order_start_time']!=""){
					$order_start_time=senetize_input($param['order_start_time']);
				}

				$order_end_date_raw=(isset($param['order_end_date']))?senetize_input($param['order_end_date']):'00/00/0000';
				$order_end_date=isValidDateFormat($order_end_date_raw)?date('Y-m-d', strtotime($order_end_date_raw)):'0000-00-00';

				$order_end_time="00:00";
				if(isset($param['order_end_time']) && $param['order_end_time']!=""){
					$order_end_time=senetize_input($param['order_end_time']);
				}

				$order_unitype_id=0;
				if(isset($param['order_unitype_id']) && $param['order_unitype_id']!=""){
					$order_unitype_id=senetize_input($param['order_unitype_id']);
				}

				$order_unit_no=0;
				if(isset($param['order_unit_no']) && $param['order_unit_no']!=""){
					$order_unit_no=senetize_input($param['order_unit_no']);
				}

				$order_refdoctype_id=0;
				if(isset($param['order_refdoctype_id']) && $param['order_refdoctype_id']!=""){
					$order_refdoctype_id=senetize_input($param['order_refdoctype_id']);
				}

				$order_refdoc_no=0;
				if(isset($param['order_refdoc_no']) && $param['order_refdoc_no']!=""){
					$order_refdoc_no=senetize_input($param['order_refdoc_no']);
				}

				$order_contact_person=0;
				if(isset($param['order_contact_person']) && $param['order_contact_person']!=""){
					$order_contact_person=senetize_input($param['order_contact_person']);
				}

				$order_contact_no=0;
				if(isset($param['order_contact_no']) && $param['order_contact_no']!=""){
					$order_contact_no=senetize_input($param['order_contact_no']);
				}

				//-----data validation ends

				////----------validate issue
				$stops_array_senetized=[];
				if(isset($param['stops'])){
					$stops=json_decode($param['stops'],true);
					
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['category_id']))
						{
							$category_id=senetize_input($stop['category_id']);
							$stop_item_senetized['category_id']=$category_id;
						}
						else
						{
							$InvalidDataMessage="Please provide category";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['criticality_level_id']))
						{
							$criticality_level_id=senetize_input($stop['criticality_level_id']);
							$stop_item_senetized['criticality_level_id']=$criticality_level_id;
						}
						else
						{
							$InvalidDataMessage="Please provide criticality level";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['job_work_id']))
						{
							$job_work_id=senetize_input($stop['job_work_id']);
							$stop_item_senetized['job_work_id']=$job_work_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['issue_reported']))
						{
							$issue_reported=senetize_input($stop['issue_reported']);
							$stop_item_senetized['issue_reported']=$issue_reported;
						}
						else
						{
							$InvalidDataMessage="Please provide issue reported";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['issue_description']))
						{
							$issue_description=senetize_input($stop['issue_description']);
							$stop_item_senetized['issue_description']=$issue_description;
						}
						else
						{
							$InvalidDataMessage="Please provide issue description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation){
			    //--check if the code exists
					$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_repair_orders` SET `order_date`='$order_date',`doctype_id`='$order_unitype_id',`status_id`='$order_status_id',`asset_id`='$order_unit_no',`driver_id`='$order_driver_id',`type_id`='$order_type_id',`stage_id`='$order_stage_id',`start_date`='$order_start_date',`start_time`='$order_start_time',`end_date`='$order_end_date',`end_time`='$order_end_time',`class_id`='$order_class_id',`contactperson`='$order_contact_person',`contactnumber`='$order_contact_no',`refdocname`='$order_refdoctype_id',`refdocno`='$order_refdoc_no' WHERE `ro_id`='$update_id'");

					if($update)
					{
						$stop_deleted=true;
						$delete=mysqli_query($GLOBALS['con'],"DELETE FROM `sm_repair_order_detail` WHERE `ro_id`='$update_id'");
							if(!$delete)
							{
								$stop_deleted=false;
							}

						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `tab_repairorder_detail` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['ro_id']:100000;
						
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row1) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_detail`(`ro_id`, `category_id_fk`, `criticalitylevel_id_fk`, `jobwork_id_fk`, `issue_reported`, `issue_description`) VALUES ('$update_id','".$stop_row1['category_id']."','".$stop_row1['criticality_level_id']."','".$stop_row1['job_work_id']."','".$stop_row1['issue_reported']."','".$stop_row1['issue_description']."')");
							if(!$insertStop)
							{
								$stop_inserted=false;
							}
						}
						///---------//insert issue

						if($stop_inserted)
						{
							$status=true;
							$message="Updated Successfuly";
						}
						else
						{
							$message="SOMETHING_WENT_WROG 1";
						}
					}
					else
					{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}
			}
			else
			{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}
		else
		{
			$message=NOT_AUTHORIZED_MSG;
		}
return ['status'=>$status,'message'=>$message,'response'=>$response];
}*/
}
?>