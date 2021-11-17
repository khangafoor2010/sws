<?php

class WorkOrders
{

	function work_order_details($param)
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
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `wo_id`, `wo_detail_sr_no`,`ro_id`,`ro_status_id_fk`, `wo_date`,`ro_vehicle_type_id_fk`,`ro_vehicle_id_fk`,
				CASE `ro_vehicle_type_id_fk` 
				WHEN 'TRUCK' THEN `truck_code` 
				when 'TRAILER' THEN `trailer_code`
				END AS `vehicle_code`,
				CASE `ro_vehicle_type_id_fk` 
				WHEN 'TRUCK' THEN `truck_vin_number` 
				when 'TRAILER' THEN `trailer_vin_number`
				END AS `vehicle_vin_number`,

				`wo_vendor_id_fk`,`wo_vendor_state_id_fk`,`wo_vendor_city_id_fk`,`vendor_name`,`vendor_state`.`location_name` AS `vendor_state_name`,`vendor_city`.`location_name` AS `vendor_city_name`,`wo_vendor_contact_person`,`wo_vendor_contact_number`,`wo_vendor_email` , `wo_invoice_number`, `wo_payment_status`,`wo_payment_mode_id_fk`, `mode_name`, `wo_payment_ref_number`, `wo_payment_date`, `wo_payment_remarks`, `wo_engine_hours`, `wo_odometer_reading`, `wo_technician_comments`, `wo_amount`, `wo_id_status`, `wo_added_on`, `wo_added_by` 
				FROM `sm_work_orders` 
				LEFT JOIN `sm_repair_orders` ON `sm_work_orders`.`wo_repair_order_id_fk`=`sm_repair_orders`.`ro_id` LEFT JOIN `sm_vendor` ON `sm_work_orders`.`wo_vendor_id_fk`=`sm_vendor`.`vendor_id`
				LEFT JOIN `trucks`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trucks`.`truck_id`
				LEFT JOIN `trailers`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trailers`.`trailer_id`
				LEFT JOIN `payment_modes` ON `sm_work_orders`.`wo_payment_mode_id_fk`=`payment_modes`.`mode_id`
				LEFT JOIN `locations` AS `vendor_state` ON `sm_work_orders`.`wo_vendor_state_id_fk`=`vendor_state`.`location_id` 
				LEFT JOIN `locations` AS `vendor_city` ON `sm_work_orders`.`wo_vendor_city_id_fk`=`vendor_city`.`location_id` 
				WHERE `wo_id_status`='ACT' AND `wo_id`='$id'");
			$details='';
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$res=mysqli_fetch_assoc($qEx);
				//get issues list
				$jw_list_q=mysqli_query($GLOBALS['con'],"SELECT   `wojw_job_work_id_fk`,`job_work_name`, `wojw_job_work_type_id_fk`,`job_work_type_name`, `wojw_description`, `wojw_is_no_charge`, `wojw_warranty_type`, `wojw_warranty_period`, `wojw_quantity`, `wojw_rate`,`wojw_amount` FROM `sm_work_order_job_works` 
					LEFT JOIN `sm_job_work_type` ON `sm_work_order_job_works`.`wojw_job_work_type_id_fk`=`sm_job_work_type`.`job_work_type_id`
					LEFT JOIN `sm_job_work` ON `sm_work_order_job_works`.`wojw_job_work_id_fk`=`sm_job_work`.`job_work_id` WHERE `wojw_work_order_id_fk`='".$res['wo_id']."'");
				$job_works_list=[];
				while ($res_il=mysqli_fetch_assoc($jw_list_q)) {
					array_push($job_works_list,[
						'job_work_type_id'=>$res_il['wojw_job_work_type_id_fk'],
						'job_work_type_name'=>$res_il['job_work_type_name'],
						'job_work'=>$res_il['job_work_name'],
						'job_work_id'=>$res_il['wojw_job_work_id_fk'],
						'description'=>$res_il['wojw_description'],
						'is_no_charge'=>($res_il['wojw_is_no_charge']==2)?true:false,
						'warranty_type'=>$res_il['wojw_warranty_type'],
						'warranty_period'=>$res_il['wojw_warranty_period'],
						'quantity'=>$res_il['wojw_quantity'],
						'rate'=>$res_il['wojw_rate'],
						'amount'=>$res_il['wojw_amount'],
					]);
				}

				$details=[
				'id'=>$res['wo_id'],
				'eid'=>$Enc->safeurlen($res['wo_id']),
				'repair_order_id'=>$res['ro_id'],
				'repair_order_status'=>$res['ro_status_id_fk'],
				'date'=>dateFromDbToFormat($res['wo_date']),
				'vehicle_type'=>$res['ro_vehicle_type_id_fk'],
				'vehicle_code'=>$res['vehicle_code'],
				'vehicle_vin_number'=>$res['vehicle_vin_number'],
				'engine_hours'=>$res['wo_engine_hours'],
				'odometer_reading'=>$res['wo_odometer_reading'],
				'technician_comments'=>$res['wo_technician_comments'],
				'vendor_id'=>$res['wo_vendor_id_fk'],
				'vendor_name'=>$res['vendor_name'],
				'vendor_state_id'=>$res['wo_vendor_state_id_fk'],
				'vendor_state_name'=>$res['vendor_state_name'],
				'vendor_city_id'=>$res['wo_vendor_city_id_fk'],
				'vendor_city_name'=>$res['vendor_city_name'],
				'vendor_contact_person'=>$res['wo_vendor_contact_person'],
				'vendor_contact_number'=>$res['wo_vendor_contact_number'],
				'vendor_email'=>$res['wo_vendor_email'],
				'amount'=>$res['wo_amount'],
				'invoice_no'=>$res['wo_invoice_number'],
				'payment_status'=>$res['wo_payment_status'],
				'payment_mode_id'=>$res['wo_payment_mode_id_fk'],
				'payment_mode'=>$res['mode_name'],
				'payment_ref_no'=>$res['wo_payment_ref_number'],
				'payment_date'=>dateFromDbToFormat($res['wo_payment_date']),
				'payment_remarks'=>$res['wo_payment_remarks'],
				'job_works_list'=>$job_works_list
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


	function work_orders_list($param)
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

		$q="SELECT `wo_id`, `wo_detail_sr_no`,`ro_id`,`ro_status_id_fk`, `wo_date`,`ro_vehicle_type_id_fk`,
		CASE `ro_vehicle_type_id_fk` 
		WHEN 'TRUCK' THEN `truck_code` 
		when 'TRAILER' THEN `trailer_code`
		END AS `vehicle_code`,`vendor_name`,`vendor_state`.`location_name` AS `vendor_state_name`,`vendor_city`.`location_name` AS `vendor_city_name` , `wo_invoice_number`, `wo_payment_status`, `mode_name`, `wo_payment_ref_number`, `wo_payment_date`, `wo_payment_remarks`, `wo_engine_hours`, `wo_odometer_reading`, `wo_technician_comments`, `wo_amount`, `wo_id_status`, `wo_added_on`, `wo_added_by` 
		FROM `sm_work_orders` 
		LEFT JOIN `sm_repair_orders` ON `sm_work_orders`.`wo_repair_order_id_fk`=`sm_repair_orders`.`ro_id` LEFT JOIN `sm_vendor` ON `sm_work_orders`.`wo_vendor_id_fk`=`sm_vendor`.`vendor_id`
		LEFT JOIN `trucks`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trucks`.`truck_id`
		LEFT JOIN `trailers`  on `sm_repair_orders`.`ro_vehicle_id_fk`=`trailers`.`trailer_id`
		LEFT JOIN `payment_modes` ON `sm_work_orders`.`wo_payment_mode_id_fk`=`payment_modes`.`mode_id`
		LEFT JOIN `locations` AS `vendor_state` ON `sm_work_orders`.`wo_vendor_state_id_fk`=`vendor_state`.`location_id` 
		LEFT JOIN `locations` AS `vendor_city` ON `sm_work_orders`.`wo_vendor_city_id_fk`=`vendor_city`.`location_id` 
		WHERE `wo_id_status`='ACT'";

		////---------------Apply filters

		if(isset($param['id']) && $param['id']!=""){
			$q .=" AND `wo_id` LIKE '%".senetize_input($param['id'])."%' =''";
		}
		if(isset($param['vehicle_type']) && $param['vehicle_type']!=""){
			$q .=" AND `ro_vehicle_type_id_fk`='".senetize_input($param['vehicle_type'])."'";
		}
		if(isset($param['vehicle_id']) && $param['vehicle_id']!=""){
			$q .=" AND `ro_vehicle_id_fk`='".senetize_input($param['vehicle_id'])."'";
		}
		if(isset($param['repair_order_status']) && $param['repair_order_status']!=""){
			$q .=" AND `ro_status_id_fk`='".senetize_input($param['repair_order_status'])."'";
		}
		if(isset($param['vendor_id']) && $param['vendor_id']!=""){
			$q .=" AND `wo_vendor_id_fk`='".senetize_input($param['vendor_id'])."'";
		}

		if(isset($param['vendor_state_id']) && $param['vendor_state_id']!=""){
			$q .=" AND `wo_vendor_state_id_fk`='".senetize_input($param['vendor_state_id'])."'";
		}
		if(isset($param['vendor_city_id']) && $param['vendor_city_id']!=""){
			$q .=" AND `wo_vendor_city_id_fk`='".senetize_input($param['vendor_city_id'])."'";
		}
		if(isset($param['amount']) && $param['amount']!=""){
			$q .=" AND `wo_amount`='".senetize_input($param['amount'])."'";
		}		

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'id':
				$q .=" ORDER BY `wo_id`";
				break;		
				default:
				$q .=" ORDER BY `wo_id` DESC";
				break;
			}
		}else{
			$q .=" ORDER BY `wo_id` DESC";	
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
			$added_user=$Users->user_basic_details($rows['wo_added_by']);
			$added_by_user_code=$added_user['user_code'];
			$added_on_datetime=dateTimeFromDbTimestamp($rows['wo_added_on']);
			array_push($list,[
				'id'=>$rows['wo_id'],
				'eid'=>$Enc->safeurlen($rows['wo_id']),
				'repair_order_id'=>$rows['ro_id'],
				'repair_order_status'=>$rows['ro_status_id_fk'],
				'date'=>dateFromDbToFormat($rows['wo_date']),
				'vehicle_type'=>$rows['ro_vehicle_type_id_fk'],
				'vehicle_code'=>$rows['vehicle_code'],
				'engine_hours'=>$rows['wo_engine_hours'],
				'odometer_reading'=>$rows['wo_odometer_reading'],
				'vendor_name'=>$rows['vendor_name'],
				'vendor_state_name'=>$rows['vendor_state_name'],
				'vendor_city_name'=>$rows['vendor_city_name'],
				'amount'=>$rows['wo_amount'],
				'invoice_no'=>$rows['wo_invoice_number'],
				'payment_status'=>$rows['wo_payment_status'],
				'payment_mode'=>$rows['mode_name'],
				'payment_ref_no'=>$rows['wo_payment_ref_number'],
				'payment_date'=>dateFromDbToFormat($rows['wo_payment_date']),
				'payment_remarks'=>$rows['wo_payment_remarks'],
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

	function work_order_update($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";

		if(!in_array('P0234', USER_PRIV)){
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['update_eid']) && $param['update_eid']!=""){
			$update_id=$Enc->safeurlde($param['update_eid']);

				//-------validate update id
			$get_detail_q=mysqli_query($GLOBALS['con'],"SELECT  `wo_id`, `wo_id_status` FROM `sm_work_orders` WHERE `wo_id`='$update_id' AND `wo_id_status`='ACT'");
			if(mysqli_num_rows($get_detail_q)==1){

				///-------calculate new detail sr number to be updated
				$new_detail_number=mysqli_fetch_assoc($get_detail_q)['wo_detail_sr_no']+1;
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


		if(isset($param['repair_order_id']) && $param['repair_order_id']!=""){
			$repair_order_id=senetize_input($param['repair_order_id']);
			include_once APPROOT.'/models/maintenance/RepairOrders.php';
			$RepairOrders=new RepairOrders;
			if(!$RepairOrders->isValidId($param['repair_order_id'])){
				$InvalidDataMessage="Invalid repair order id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide repair order id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['date']) && $param['date']!=""){
			if(isValidDateFormat(senetize_input($param['date']))){
				$date=date('Y-m-d', strtotime($param['date']));
			}else{
				$InvalidDataMessage="Invalid date";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide date";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['odometer_reading']) && $param['odometer_reading']!=""){
			if(validate_int(senetize_input($param['odometer_reading']))){
				$odometer_reading=strtotime($param['odometer_reading']);
			}else{
				$InvalidDataMessage="Invalid odometer reading";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide odometer reading";
			$dataValidation=false;
			goto ValidationChecker;		
		}



		if(isset($param['engine_hours']) && $param['engine_hours']!=""){
			if(validate_int(senetize_input($param['engine_hours']))){
				$engine_hours=strtotime($param['engine_hours']);
			}else{
				$InvalidDataMessage="Invalid engine hours";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide engine hours";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		$technician_comments=(isset($param['technician_comments']))?senetize_input($param['technician_comments']):'';

		if(isset($param['vendor_id']) && $param['vendor_id']!=""){
			$vendor_id=senetize_input($param['vendor_id']);
			include_once APPROOT.'/models/maintenance/masters/Vendor.php';
			$Vendor=new Vendor;

			if(!$Vendor->isValidId($vendor_id)){
				$InvalidDataMessage="Invalid vendor id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide vendor id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		include_once APPROOT.'/models/masters/Locations.php';
		$Locations=new Locations;

		if(isset($param['vendor_state_id']) && $param['vendor_state_id']!=""){
			$vendor_state_id=senetize_input($param['vendor_state_id']);

			if(!$Locations->isValidLocationStateId($vendor_state_id)){
				$InvalidDataMessage="Invalid address state value";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide vendor state id";
			$dataValidation=false;
			goto ValidationChecker;
		}
		if(isset($param['vendor_city_id']) && $param['vendor_city_id']!=""){
			$vendor_city_id=senetize_input($param['vendor_city_id']);

			if(!$Locations->isValidLocationCityId($vendor_city_id)){
				$InvalidDataMessage="Invalid vendor city id";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide vendor city id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$vendor_contact_person=(isset($param['vendor_contact_person']))?senetize_input($param['vendor_contact_person']):'';
		$vendor_contact_number=(isset($param['vendor_contact_number']))?senetize_input($param['vendor_contact_number']):'';
		$vendor_email=(isset($param['vendor_email']))?senetize_input($param['vendor_email']):'';

		$invoice_no=(isset($param['invoice_no']))?senetize_input($param['invoice_no']):'';
		$payment_date=(isset($param['payment_date']))?senetize_input($param['payment_date']):'';

		if(isset($param['payment_mode_id']) && $param['payment_mode_id']!=""){
			$payment_mode_id=senetize_input($param['payment_mode_id']);
			include_once APPROOT.'/models/masters/PaymentModes.php';
			$PaymentModes=new PaymentModes;
			if(!$PaymentModes->isValidId($payment_mode_id)){
				$InvalidDataMessage="Invalid payment mode";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide payment mode";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['payment_status']) && $param['payment_status']!=""){
			$payment_status=senetize_input($param['payment_status']);

			if(!in_array($payment_status, ['PAID','CREDIT'])){
				$InvalidDataMessage="Invalid payment status";
				$dataValidation=false;
				goto ValidationChecker;			
			}
		}else{
			$InvalidDataMessage="Please provide vendor city id";
			$dataValidation=false;
			goto ValidationChecker;
		}
		$payment_ref_no=(isset($param['payment_ref_no']))?senetize_input($param['payment_ref_no']):'';
		$payment_remarks=(isset($param['payment_remarks']))?senetize_input($param['payment_remarks']):'';	


				//-----data validation ends

				////----------validate issue
		

		if(isset($param['job_works']))
		{
			$job_works=$param['job_works'];
			$job_works_array_senetized=[];
			foreach ($job_works as $job_work) 
			{

				if(isset($job_work['job_work_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWork.php';
					$JobWork=new JobWork;

					if(!$JobWork->isValidId(senetize_input($job_work['job_work_id']))){
						$InvalidDataMessage="Invalid issue job work id";
						$dataValidation=false;
						goto ValidationChecker;
					}
					
				}else{
					$InvalidDataMessage="Please provide job work id";
					$dataValidation=false;
					goto ValidationChecker;
				}




					//----validate criticality level
				if(isset($job_work['job_work_type_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWorkType.php';
					$JobWorkType=new JobWorkType;

					if(!$JobWorkType->isValidId(senetize_input($job_work['job_work_type_id']))){
						$InvalidDataMessage="Invalid issue job work type id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide job work type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate criticality level


					//----validdate issue description
				if(!isset($job_work['description'])){
					$InvalidDataMessage="Please provide description";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validdate issue description


				if(isset($job_work['is_no_charge']) && $job_work['is_no_charge']!=""){

				}else{
					$InvalidDataMessage="Please provide is no charge";
					$dataValidation=false;
					goto ValidationChecker;					
				}



				if(isset($job_work['warranty_type']) && $job_work['warranty_type']!=""){

					if(!in_array($job_work['warranty_type'], ['NONE','HOURS','DAYS','MILES'])){
						$InvalidDataMessage="Invalid warranty type";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide warranty type";
					$dataValidation=false;
					goto ValidationChecker;
				}

				if(isset($job_work['warranty_period']) && $job_work['warranty_period']!=""){

					if(!validate_int($job_work['warranty_period'])){
						$InvalidDataMessage="Invalid warranty period";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide warranty period";
					$dataValidation=false;
					goto ValidationChecker;
				}


				if(isset($job_work['quantity']) && $job_work['quantity']!=""){

					if(!validate_float($job_work['quantity'])){
						$InvalidDataMessage="Invalid quantity";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide quantity";
					$dataValidation=false;
					goto ValidationChecker;
				}

				if(isset($job_work['rate']) && $job_work['rate']!=""){

					if(!validate_float($job_work['rate'])){
						$InvalidDataMessage="Invalid quantity";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide quantity";
					$dataValidation=false;
					goto ValidationChecker;
				}

				array_push($job_works_array_senetized,[
					'job_work_id'=>senetize_input($job_work['job_work_id']),
					'job_work_type_id'=>senetize_input($job_work['job_work_type_id']),
					'description'=>senetize_input($job_work['description']),
					'is_no_charge'=>(to_boolean($job_work['is_no_charge'])==true)?1:0,
					'warranty_type'=>senetize_input($job_work['warranty_type']),
					'warranty_period'=>senetize_input($job_work['warranty_period']),
					'quantity'=>senetize_input($job_work['quantity']),
					'rate'=>senetize_input($job_work['rate']),
					'amount'=>round((senetize_input($job_work['quantity'])*senetize_input($job_work['rate'])),2)
				]);
			}
		}else{
			$InvalidDataMessage="Please provide job works";
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
				UPDATE `sm_work_orders` SET  `wo_detail_sr_no`='$new_detail_number', `wo_date`='$date', `wo_repair_order_id_fk`='$repair_order_id', `wo_vendor_id_fk`='$vendor_id', `wo_vendor_state_id_fk`='$vendor_state_id', `wo_vendor_city_id_fk`='$vendor_city_id', `wo_vendor_contact_person`='$vendor_contact_person', `wo_vendor_contact_number`='$vendor_contact_number', `wo_vendor_email`='$vendor_email', `wo_payment_mode_id_fk`='$payment_mode_id',`wo_payment_ref_number`='$payment_ref_no', `wo_invoice_number`='$invoice_no', `wo_payment_status`='$payment_status', `wo_payment_date`='$payment_date', `wo_payment_remarks`='$payment_remarks', `wo_engine_hours`='$engine_hours',`wo_odometer_reading`='$odometer_reading', `wo_technician_comments`='$technician_comments', `wo_updated_on`='$time', `wo_updated_by`='$USERID' WHERE `wo_id`='$update_id' AND `wo_id_status`='ACT'");

			if(!$update){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

						///-----Generate New Unique Id
			$last_job_work_id=mysqli_query($GLOBALS['con'],"SELECT `wojw_id` FROM `sm_work_order_job_works` ORDER BY `wojw_auto` DESC LIMIT 1");
			$next_job_work_id=(mysqli_num_rows($last_job_work_id)==1)?mysqli_fetch_assoc($last_job_work_id)['wojw_id']:0;

						///-----//Generate New Unique Id

					//-----insert issues list
			foreach ($job_works_array_senetized as $jw) 
			{
				$next_job_work_id++;
				$insertJobWork=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_work_order_job_works`(`wojw_id`, `wojw_work_order_id_fk`, `wojw_job_work_id_fk`, `wojw_job_work_type_id_fk`, `wojw_description`, `wojw_is_no_charge`, `wojw_warranty_type`, `wojw_warranty_period`, `wojw_quantity`, `wojw_rate`, `wojw_amount`, `wojw_work_order_detail_id_fk`) VALUES ('$next_job_work_id','$update_id','".$jw['job_work_id']."','".$jw['job_work_type_id']."','".$jw['description']."','".$jw['is_no_charge']."','".$jw['warranty_type']."','".$jw['warranty_period']."','".$jw['quantity']."','".$jw['rate']."','".$jw['amount']."','$new_detail_number')");
				if(!$insertJobWork)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step B'.mysqli_error($GLOBALS['con']);
					goto executionChecker;
				}
			}
					///---------//insert issue

			///---------delete the old issues

			$delete=mysqli_query($GLOBALS,"DELETE FROM `sm_work_order_job_works` WHERE `wojw_work_order_id_fk`='$update_id' AND NOT `wo_detail_sr_no`='$new_detail_number'");
				if(!$delete)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step C';
					goto executionChecker;
				}


			executionChecker:
			if($execution){
				$status=true;
				$message="Updated Successfuly";
			}

		}else{
			$message=$InvalidDataMessage;
		}
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}



	function work_order_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			    //-----data validation starts
		$dataValidation=true;
		$InvalidDataMessage="";

		if(!in_array('P0233', USER_PRIV)){
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}




		if(isset($param['repair_order_id']) && $param['repair_order_id']!=""){
			$repair_order_id=senetize_input($param['repair_order_id']);
			include_once APPROOT.'/models/maintenance/RepairOrders.php';
			$RepairOrders=new RepairOrders;
			if(!$RepairOrders->isValidId($param['repair_order_id'])){
				$InvalidDataMessage="Invalid repair order id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide repair order id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['date']) && $param['date']!=""){
			if(isValidDateFormat(senetize_input($param['date']))){
				$date=date('Y-m-d', strtotime($param['date']));
			}else{
				$InvalidDataMessage="Invalid date";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide date";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		if(isset($param['odometer_reading']) && $param['odometer_reading']!=""){
			if(validate_int(senetize_input($param['odometer_reading']))){
				$odometer_reading=strtotime($param['odometer_reading']);
			}else{
				$InvalidDataMessage="Invalid odometer reading";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide odometer reading";
			$dataValidation=false;
			goto ValidationChecker;		
		}



		if(isset($param['engine_hours']) && $param['engine_hours']!=""){
			if(validate_int(senetize_input($param['engine_hours']))){
				$engine_hours=strtotime($param['engine_hours']);
			}else{
				$InvalidDataMessage="Invalid engine hours";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide engine hours";
			$dataValidation=false;
			goto ValidationChecker;		
		}



		$technician_comments=(isset($param['technician_comments']))?senetize_input($param['technician_comments']):'';

		if(isset($param['vendor_id']) && $param['vendor_id']!=""){
			$vendor_id=senetize_input($param['vendor_id']);
			include_once APPROOT.'/models/maintenance/masters/Vendor.php';
			$Vendor=new Vendor;

			if(!$Vendor->isValidId($vendor_id)){
				$InvalidDataMessage="Invalid vendor id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide vendor id";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		include_once APPROOT.'/models/masters/Locations.php';
		$Locations=new Locations;

		if(isset($param['vendor_state_id']) && $param['vendor_state_id']!=""){
			$vendor_state_id=senetize_input($param['vendor_state_id']);

			if(!$Locations->isValidLocationStateId($vendor_state_id)){
				$InvalidDataMessage="Invalid address state value";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide vendor state id";
			$dataValidation=false;
			goto ValidationChecker;
		}
		if(isset($param['vendor_city_id']) && $param['vendor_city_id']!=""){
			$vendor_city_id=senetize_input($param['vendor_city_id']);

			if(!$Locations->isValidLocationCityId($vendor_city_id)){
				$InvalidDataMessage="Invalid vendor city id";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide vendor city id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$vendor_contact_person=(isset($param['vendor_contact_person']))?senetize_input($param['vendor_contact_person']):'';
		$vendor_contact_number=(isset($param['vendor_contact_number']))?senetize_input($param['vendor_contact_number']):'';
		$vendor_email=(isset($param['vendor_email']))?senetize_input($param['vendor_email']):'';

		$invoice_no=(isset($param['invoice_no']))?senetize_input($param['invoice_no']):'';
		$payment_date=(isset($param['payment_date']))?senetize_input($param['payment_date']):'';

		if(isset($param['payment_mode_id']) && $param['payment_mode_id']!=""){
			$payment_mode_id=senetize_input($param['payment_mode_id']);
			include_once APPROOT.'/models/masters/PaymentModes.php';
			$PaymentModes=new PaymentModes;
			if(!$PaymentModes->isValidId($payment_mode_id)){
				$InvalidDataMessage="Invalid payment mode";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}else{
			$InvalidDataMessage="Please provide payment mode";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['payment_status']) && $param['payment_status']!=""){
			$payment_status=senetize_input($param['payment_status']);

			if(!in_array($payment_status, ['PAID','CREDIT'])){
				$InvalidDataMessage="Invalid payment status";
				$dataValidation=false;
				goto ValidationChecker;			
			}
		}else{
			$InvalidDataMessage="Please provide vendor city id";
			$dataValidation=false;
			goto ValidationChecker;
		}
		$payment_ref_no=(isset($param['payment_ref_no']))?senetize_input($param['payment_ref_no']):'';
		$payment_remarks=(isset($param['payment_remarks']))?senetize_input($param['payment_remarks']):'';	


				//-----data validation ends

				////----------validate issue
		
		$total_amount=0;
		if(isset($param['job_works']))
		{
			$job_works=$param['job_works'];
			$job_works_array_senetized=[];
			foreach ($job_works as $job_work) 
			{

				if(isset($job_work['job_work_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWork.php';
					$JobWork=new JobWork;

					if(!$JobWork->isValidId(senetize_input($job_work['job_work_id']))){
						$InvalidDataMessage="Invalid issue job work id";
						$dataValidation=false;
						goto ValidationChecker;
					}
					
				}else{
					$InvalidDataMessage="Please provide job work id";
					$dataValidation=false;
					goto ValidationChecker;
				}




					//----validate criticality level
				if(isset($job_work['job_work_type_id'])){
					include_once APPROOT.'/models/maintenance/masters/JobWorkType.php';
					$JobWorkType=new JobWorkType;

					if(!$JobWorkType->isValidId(senetize_input($job_work['job_work_type_id']))){
						$InvalidDataMessage="Invalid issue job work type id";
						$dataValidation=false;
						goto ValidationChecker;
					}
				}else{
					$InvalidDataMessage="Please provide job work type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate criticality level


					//----validdate issue description
				if(!isset($job_work['description'])){
					$InvalidDataMessage="Please provide description";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validdate issue description


				if(isset($job_work['is_no_charge']) && $job_work['is_no_charge']!=""){

				}else{
					$InvalidDataMessage="Please provide is no charge";
					$dataValidation=false;
					goto ValidationChecker;					
				}



				if(isset($job_work['warranty_type']) && $job_work['warranty_type']!=""){

					if(!in_array($job_work['warranty_type'], ['NONE','HOURS','DAYS','MILES'])){
						$InvalidDataMessage="Invalid warranty type";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide warranty type";
					$dataValidation=false;
					goto ValidationChecker;
				}

				if(isset($job_work['warranty_period']) && $job_work['warranty_period']!=""){

					if(!validate_int($job_work['warranty_period'])){
						$InvalidDataMessage="Invalid warranty period";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide warranty period";
					$dataValidation=false;
					goto ValidationChecker;
				}


				if(isset($job_work['quantity']) && $job_work['quantity']!=""){

					if(!validate_float($job_work['quantity'])){
						$InvalidDataMessage="Invalid quantity";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide quantity";
					$dataValidation=false;
					goto ValidationChecker;
				}

				if(isset($job_work['rate']) && $job_work['rate']!=""){

					if(!validate_float($job_work['rate'])){
						$InvalidDataMessage="Invalid quantity";
						$dataValidation=false;
						goto ValidationChecker;			
					}
				}else{
					$InvalidDataMessage="Please provide quantity";
					$dataValidation=false;
					goto ValidationChecker;
				}

				$amount=round((senetize_input($job_work['quantity'])*senetize_input($job_work['rate'])),2);
				$total_amount+=$amount;

				array_push($job_works_array_senetized,[
					'job_work_id'=>senetize_input($job_work['job_work_id']),
					'job_work_type_id'=>senetize_input($job_work['job_work_type_id']),
					'description'=>senetize_input($job_work['description']),
					'is_no_charge'=>(to_boolean($job_work['is_no_charge'])==true)?1:0,
					'warranty_type'=>senetize_input($job_work['warranty_type']),
					'warranty_period'=>senetize_input($job_work['warranty_period']),
					'quantity'=>senetize_input($job_work['quantity']),
					'rate'=>senetize_input($job_work['rate']),
					'amount'=>$amount
				]);
			}
		}else{
			$InvalidDataMessage="Please provide job works";
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
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `wo_id` FROM `sm_work_orders` ORDER BY `auto` DESC LIMIT 1");
			$get_last_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['wo_id']):'0000000';

			$get_wo_id_prefix_b=date("y");
			if(substr($get_last_id,0,2)==$get_wo_id_prefix_b){
				$next_id=$get_wo_id_prefix_b.sprintf('%05d',(intval(substr($get_last_id,4))));
			}else{
				$next_id=$get_wo_id_prefix_b.'00001';
			}
			$next_id=$next_id+1;
					///-----//Generate New Unique Id
			$insert=mysqli_query($GLOBALS['con'],"
				INSERT INTO `sm_work_orders`(`wo_id`, `wo_detail_sr_no`, `wo_date`, `wo_repair_order_id_fk`, `wo_vendor_id_fk`, `wo_vendor_state_id_fk`, `wo_vendor_city_id_fk`, `wo_vendor_contact_person`, `wo_vendor_contact_number`, `wo_vendor_email`,`wo_amount`, `wo_payment_mode_id_fk`,`wo_payment_ref_number`, `wo_invoice_number`, `wo_payment_status`, `wo_payment_date`, `wo_payment_remarks`, `wo_odometer_reading`,`wo_engine_hours`, `wo_technician_comments`, `wo_id_status`, `wo_added_on`, `wo_added_by`) 
				VALUES ('$next_id',1,'$date','$repair_order_id','$vendor_id','$vendor_state_id','$vendor_city_id','$vendor_contact_person','$vendor_contact_number','$vendor_email','$total_amount','$payment_mode_id','$payment_ref_no','$invoice_no','$payment_status','$payment_date','$payment_remarks','$odometer_reading','$engine_hours','$technician_comments','ACT','$time','$USERID')");

			if(!$insert){
				$execution=false;
				$message=SOMETHING_WENT_WROG.' Step A'.mysqli_error($GLOBALS['con']);
				goto executionChecker;

			}

						///-----Generate New Unique Id
			$last_job_work_id=mysqli_query($GLOBALS['con'],"SELECT `wojw_id` FROM `sm_work_order_job_works` ORDER BY `wojw_auto` DESC LIMIT 1");
			$next_job_work_id=(mysqli_num_rows($last_job_work_id)==1)?mysqli_fetch_assoc($last_job_work_id)['wojw_id']:0;

						///-----//Generate New Unique Id

					//-----insert issues list
			foreach ($job_works_array_senetized as $jw) 
			{
				$next_job_work_id++;
				$insertJobWork=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_work_order_job_works`(`wojw_id`, `wojw_work_order_id_fk`, `wojw_job_work_id_fk`, `wojw_job_work_type_id_fk`, `wojw_description`, `wojw_is_no_charge`, `wojw_warranty_type`, `wojw_warranty_period`, `wojw_quantity`, `wojw_rate`, `wojw_amount`, `wojw_work_order_detail_id_fk`) VALUES ('$next_job_work_id','$next_id','".$jw['job_work_id']."','".$jw['job_work_type_id']."','".$jw['description']."','".$jw['is_no_charge']."','".$jw['warranty_type']."','".$jw['warranty_period']."','".$jw['quantity']."','".$jw['rate']."','".$jw['amount']."',1)");
				if(!$insertJobWork)
				{
					$execution=false;
					$message=SOMETHING_WENT_WROG.' Step B'.mysqli_error($GLOBALS['con']);
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
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}
	function work_order_delete($param)
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


		if(!in_array('P0235', USER_PRIV))
		{
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$time=time();
			$USERID=USER_ID;

			$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_work_orders` SET `wo_id_status`='DEL',`wo_deleted_on`='$time',`wo_deleted_by`='$USERID' WHERE `wo_id`='$delete_id'");
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
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `workorder_id` from `sm_work_order_header` WHERE `workorder_id`='$id'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function WorkOrderEntry_list($param)
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

		$q="SELECT `A`.`workorder_id` as `workorder_id`, `A`.`repairorder_id_fk` as `repairorder_id`, `A`.`workorder_date` as `workorder_date`, `G`.`vehicle_name` as `unittype_name`, case `A`.`unittype_id` when 1 then `truck_code` when 2 then `trailer_code` end as `asset_name`, `A`.`engine_reading` as `engine_reading`, `D`.`vendor_name` as `vendor_name`, `E`.`location_name` as `state_name`, `F`.`location_name` as `city_name`,`A`.`vendor_phone` as `vendor_phone`, `A`.`workorder_amount` as `workorder_amount`, `A`.`technician_comments` as `technician_comments` 
		    FROM `sm_work_order_header` as `A` left join `sm_repair_order_header` as `B` on `A`.`repairorder_id_fk`=`B`.`order_id` left join `sm_vendor_master` as `D` on `A`.`vendor_id`=`D`.`vendor_id` left join `locations` as `E` on `A`.`state_id`=`E`.`location_id` left join `locations` as `F` on `A`.`city_id`=`F`.`location_id` left join `vehicles` as `G` on `A`.`unittype_id`=`G`.`vehicle_id` left join `trucks` as `H` on `A`.`unit_id`=`H`.`truck_id` left join `trailers` as `I` on `A`.`unit_id`=`I`.`trailer_id` 
		    WHERE `A`.`status`='ACT'";

		if(isset($param['unit_type_id']) && $param['unit_type_id']!=""){
			$unit_type_id=senetize_input($param['unit_type_id']);
			$q .=" AND `A`.`unitytype_id` ='$unit_type_id'";
		}
		if(isset($param['unit_id']) && $param['unit_id']!=""){
			$unit_id=senetize_input($param['unit_id']);
			$q .=" AND `A`.`asset_id` ='$unit_id'";
		}

		if(isset($param['order_id']) && $param['order_id']!=""){
			$order_id=senetize_input($param['order_id']);
			$q .=" AND `A`.`order_id` LIKE '%$order_id%'";
		}
		
		if(isset($param['vendor_id']) && $param['vendor_id']!=""){
			$vendor_id=senetize_input($param['vendor_id']);
			$q .=" AND `A`.`vendor_id` ='$vendor_id'";
		}

		if(isset($param['state_id']) && $param['state_id']!=""){
			$state_id=senetize_input($param['state_id']);
			$q .=" AND `A`.`state_id` ='$state_id'";
		}

		if(isset($param['status_id']) && $param['status_id']!=""){
			$status_id=senetize_input($param['status_id']);
			$q .=" AND `A`.`status_id` ='$status_id'";
		}
		
		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) 
		{
			$row=[];
			$row['id']=$rows['workorder_id'];
			$row['eid']=$Enc->safeurlen($rows['workorder_id']);
			$row['repairorder_id']=$rows['repairorder_id'];
			$row['workorder_date']=dateFromDbToFormat($rows['workorder_date']);
			$row['unittype_name']=$rows['unittype_name'];
			$row['asset_name']=$rows['asset_name'];
			$row['engine_reading']=$rows['engine_reading'];
			$row['vendor_name']=$rows['vendor_name'];
			$row['state_name']=$rows['state_name'];
			$row['city_name']=$rows['city_name'];
			$row['vendor_phone']=$rows['vendor_phone'];
			$row['workorder_amount']=$rows['workorder_amount'];
			$row['technician_comments']=$rows['technician_comments'];
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

	function WorkOrderEntry_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0008', USER_PRIV))
		{

			if(isset($param['repairorder_id']) && $param['repairorder_id']!="")
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

				$repairorder_id=0;
				if(isset($param['repairorder_id']) && $param['repairorder_id']!="")
				{
					$repairorder_id=senetize_input($param['repairorder_id']);
				}

				$unittype_id=0;
				if(isset($param['unittype_id']) && $param['unittype_id']!="")
				{
					$unittype_id=senetize_input($param['unittype_id']);
				}

				$unit_id=0;
				if(isset($param['unit_id']) && $param['unit_id']!="")
				{
					$unit_id=senetize_input($param['unit_id']);
				}

				$enginereading=0;
				if(isset($param['enginereading']) && $param['enginereading']!="")
				{
					$enginereading=senetize_input($param['enginereading']);
				}

				$vendor_id=0;
				if(isset($param['vendor_id']) && $param['vendor_id']!="")
				{
					$vendor_id=senetize_input($param['vendor_id']);
				}

				$state_id=0;
				if(isset($param['state_id']) && $param['state_id']!="")
				{
					$state_id=senetize_input($param['state_id']);
				}

				$city_id=0;
				if(isset($param['city_id']) && $param['city_id']!="")
				{
					$city_id=senetize_input($param['city_id']);
				}

				$contact_person=0;
				if(isset($param['contact_person']) && $param['contact_person']!="")
				{
					$contact_person=senetize_input($param['contact_person']);
				}

				$contact_no=0;
				if(isset($param['contact_no']) && $param['contact_no']!="")
				{
					$contact_no=senetize_input($param['contact_no']);
				}

				$email_id=0;
				if(isset($param['email_id']) && $param['email_id']!="")
				{
					$email_id=senetize_input($param['email_id']);
				}

				$billingmethod_id=0;
				if(isset($param['billingmethod_id']) && $param['billingmethod_id']!="")
				{
					$billingmethod_id=senetize_input($param['billingmethod_id']);
				}

				$invoice_no=0;
				if(isset($param['invoice_no']) && $param['invoice_no']!="")
				{
					$invoice_no=senetize_input($param['invoice_no']);
				}

				$payment_date_raw=(isset($param['payment_date']))?senetize_input($param['payment_date']):'00/00/0000';
				$payment_date=isValidDateFormat($payment_date_raw)?date('Y-m-d', strtotime($payment_date_raw)):'0000-00-00';

				$paymentstatus_id=0;
				if(isset($param['paymentstatus_id']) && $param['paymentstatus_id']!="")
				{
					$paymentstatus_id=senetize_input($param['paymentstatus_id']);
				}

				$paymentnotes=0;
				if(isset($param['paymentnotes']) && $param['paymentnotes']!="")
				{
					$paymentnotes=senetize_input($param['paymentnotes']);
				}

				$techniciancomments=0;
				if(isset($param['techniciancomments']) && $param['techniciancomments']!="")
				{
					$techniciancomments=senetize_input($param['techniciancomments']);
				}

				$amount=0;
				if(isset($param['amount']) && $param['amount']!="")
				{
					$amount=senetize_input($param['amount']);
				}

				//-----data validation ends

				////----------validate issue
				if(isset($param['stops']))
				{
					$stops=json_decode($param['stops'],true);
					$stops_array_senetized=[];
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['jobworktype_id']))
						{
							$jobworktype_id=senetize_input($stop['jobworktype_id']);
							$stop_item_senetized['jobworktype_id']=$jobworktype_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work type";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['jobwork_id']))
						{
							$jobwork_id=senetize_input($stop['jobwork_id']);
							$stop_item_senetized['jobwork_id']=$jobwork_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['description']))
						{
							$description=senetize_input($stop['description']);
							$stop_item_senetized['description']=$description;
						}
						else
						{
							$InvalidDataMessage="Please provide description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['nocharge_id']))
						{
							$nocharge_id=senetize_input($stop['nocharge_id']);
							$stop_item_senetized['nocharge_id']=$nocharge_id;
						}
						else
						{
							$InvalidDataMessage="Please provide no charge";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['warranty_id']))
						{
							$warranty_id=senetize_input($stop['warranty_id']);
							$stop_item_senetized['warranty_id']=$warranty_id;
						}
						else
						{
							$InvalidDataMessage="Please provide warranty";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($stop['expiremiles']))
						{
							$expiremiles=senetize_input($stop['expiremiles']);
							$stop_item_senetized['expiremiles']=$expiremiles;
						}
						else
						{
							$InvalidDataMessage="Please provide expire miles";
							$dataValidation=false;
							goto ValidationChecker;
						}

						if(isset($stop['expiredays']))
						{
							$expiredays=senetize_input($stop['expiredays']);
							$stop_item_senetized['expiredays']=$expiredays;
						}
						else
						{
							$InvalidDataMessage="Please provide expire days";
							$dataValidation=false;
							goto ValidationChecker;
						}

						if(isset($stop['expirehours']))
						{
							$expirehours=senetize_input($stop['expirehours']);
							$stop_item_senetized['expirehours']=$expirehours;
						}
						else
						{
							$InvalidDataMessage="Please provide expire hours";
							$dataValidation=false;
							goto ValidationChecker;
						}

					//----/validate issue reported
						//----validdate issue description
						if(isset($stop['quantity']))
						{
							$quantity=senetize_input($stop['quantity']);
							$stop_item_senetized['quantity']=$quantity;
						}
						else
						{
							$InvalidDataMessage="Please provide quantity";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
					//----validdate issue description
						if(isset($stop['cost']))
						{
							$cost=senetize_input($stop['cost']);
							$stop_item_senetized['cost']=$cost;
						}
						else
						{
							$InvalidDataMessage="Please provide cost";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation)
				{
 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `workorder_id` FROM `sm_work_order_header` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['workorder_id'])+1:0;
					///-----//Generate New Unique Id

					$insertheader=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_work_order_header`(`workorder_id`, `workorder_date`, `repairorder_id_fk`, `unittype_id`, `unit_id`, `vendor_id`, `state_id`, `city_id`, `contactperson_name`, `vendor_phone`, `vendor_email`, `billing_method_id_fk`, `invoice_no`, `payment_status_id_fk`, `payment_date`, `payment_notes`, `engine_reading`, `technician_comments`, `workorder_amount`, `status`) VALUES ('$next_id','$order_date', '$repairorder_id', '$unittype_id', '$unit_id', '$vendor_id', '$state_id', '$city_id', '$contact_person', '$contact_no', '$email_id', '$billingmethod_id', '$invoice_no', '$paymentstatus_id', '$payment_date', '$paymentnotes', '$enginereading', '$techniciancomments','$amount','ACT')");

					if($insertheader)
					{
						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `tab_WorkOrderEntry_detail` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['order_id']:100000;
						
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `workorder_detail`(`workorder_id`, `jobworktype_id`, `jobwork_id`, `description`, `nocharge_id`, `warranty_id`, `expire_miles`, `expire_days`, `expire_hours`, `quantity`, `cost`) VALUES ('$next_id','".$stop_row['jobworktype_id']."','".$stop_row['jobwork_id']."','".$stop_row['description']."','".$stop_row['nocharge_id']."','".$stop_row['warranty_id']."','".$stop_row['expiremiles']."','".$stop_row['expiredays']."','".$stop_row['expirehours']."','".$stop_row['quantity']."','".$stop_row['cost']."')");
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
				}
				else
				{
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
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}

	function WorkOrderEntry_details($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for']))
		{
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$q="SELECT `workorder_id`, `workorder_date`, `repairorder_id_fk`, `unittype_id`, `unit_id`, `vendor_id`, `state_id`, `city_id`, `contactperson_name`, `vendor_phone`, `vendor_email`, `billing_method_id_fk`, `invoice_no`, `payment_status_id_fk`, `payment_date`, `payment_notes`, `engine_reading`, `technician_comments`, `workorder_amount` 
			FROM `sm_work_order_header` as `A`
			left join `trucks` as `H` on `A`.`unit_id`=`H`.`truck_id`
            left join `trailers` as `I` on `A`.`unit_id`=`I`.`trailer_id`
			WHERE `status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for)
			{
				case 'id':
				if(isset($param['details_for_id']))
				{
					$details_for_id=senetize_input($param['details_for_id']);
					$q .=" AND `workorder_id`='$details_for_id'";
					$runQuery=true;
				}
				else
				{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid']))
				{
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$q .=" AND `workorder_id`='$details_for_eid'";
					$runQuery=true;
				}
				else
				{
					$message="Please enter details_for_eid";
				}
				break;	

				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}
		else
		{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery)
		{
			$get=mysqli_query($GLOBALS['con'],$q);
			if(mysqli_num_rows($get)==1)
			{
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['workorder_id']=$rows['workorder_id'];
				$row['workorder_date']=dateFromDbToFormat($rows['workorder_date']);
				$row['repairorder_id']=$rows['repairorder_id_fk'];
				$row['unittype_id']=$rows['unittype_id'];
				$row['unit_id']=$rows['unit_id'];
				$row['vendor_id']=$rows['vendor_id'];
				$row['state_id']=$rows['state_id'];				
				$row['city_id']=$rows['city_id'];	
				$row['contactperson_name']=$rows['contactperson_name'];
				$row['vendor_phone']=$rows['vendor_phone'];
				$row['vendor_email']=$rows['vendor_email'];
				$row['billingmethod_id']=$rows['billing_method_id_fk'];
				$row['invoice_no']=$rows['invoice_no'];
				$row['paymentstatus_id']=$rows['payment_status_id_fk'];
				$row['payment_date']=dateFromDbToFormat($rows['payment_date']);
				$row['payment_notes']=$rows['payment_notes'];
				$row['engine_reading']=$rows['engine_reading'];
				$row['technician_comments']=$rows['technician_comments'];
				$row['amount']=$rows['workorder_amount'];
				
				//////////////////////////////////////////////////////////////

				$get_stops_q=mysqli_query($GLOBALS['con'],"SELECT `jobworktype_id`, `jobwork_id`, `description`, `nocharge_id`, `warranty_id`, `expire_miles`, `expire_days`, `expire_hours`, `quantity`, `cost` FROM `workorder_detail` WHERE `workorder_id`='".$rows['workorder_id']."'");

				$jobworklist=[];
				while ($rowslist=mysqli_fetch_assoc($get_stops_q)) 
				{
					$rowlist=[];
					$rowlist['jobworktype_id']=$rowslist['jobworktype_id'];
					$rowlist['jobwork_id']=$rowslist['jobwork_id'];
					$rowlist['description']=$rowslist['description'];
					$rowlist['nocharge_id']=$rowslist['nocharge_id'];
					$rowlist['warranty_id']=$rowslist['warranty_id'];
					$rowlist['expire_miles']=$rowslist['expire_miles'];
					$rowlist['expire_days']=$rowslist['expire_days'];
					$rowlist['expire_hours']=$rowslist['expire_hours'];
					$rowlist['quantity']=$rowslist['quantity'];
					$rowlist['cost']=$rowslist['cost'];
					array_push($jobworklist,$rowlist);
				}
				////////////////////////////////////////////////////////////
				
				$row['stop_list']=$jobworklist;
				$response['details']=$row;
			}
			else
			{
				$message="No records found";
			} 				
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}	

	function WorkOrderEntry_update($param)
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

				$unittype_id=0;
				if(isset($param['unittype_id']) && $param['unittype_id']!="")
				{
					$unittype_id=senetize_input($param['unittype_id']);
				}

				$unit_id=0;
				if(isset($param['unit_id']) && $param['unit_id']!="")
				{
					$unit_id=senetize_input($param['unit_id']);
				}

				$enginereading=0;
				if(isset($param['enginereading']) && $param['enginereading']!="")
				{
					$enginereading=senetize_input($param['enginereading']);
				}

				$vendor_id=0;
				if(isset($param['vendor_id']) && $param['vendor_id']!="")
				{
					$vendor_id=senetize_input($param['vendor_id']);
				}

				$state_id=0;
				if(isset($param['state_id']) && $param['state_id']!="")
				{
					$state_id=senetize_input($param['state_id']);
				}

				$city_id=0;
				if(isset($param['city_id']) && $param['city_id']!="")
				{
					$city_id=senetize_input($param['city_id']);
				}

				$contact_person=0;
				if(isset($param['contact_person']) && $param['contact_person']!="")
				{
					$contact_person=senetize_input($param['contact_person']);
				}

				$contact_no=0;
				if(isset($param['contact_no']) && $param['contact_no']!="")
				{
					$contact_no=senetize_input($param['contact_no']);
				}

				$email_id=0;
				if(isset($param['email_id']) && $param['email_id']!="")
				{
					$email_id=senetize_input($param['email_id']);
				}

				$billingmethod_id=0;
				if(isset($param['billingmethod_id']) && $param['billingmethod_id']!="")
				{
					$billingmethod_id=senetize_input($param['billingmethod_id']);
				}

				$invoice_no=0;
				if(isset($param['invoice_no']) && $param['invoice_no']!="")
				{
					$invoice_no=senetize_input($param['invoice_no']);
				}

				$payment_date_raw=(isset($param['payment_date']))?senetize_input($param['payment_date']):'00/00/0000';
				$payment_date=isValidDateFormat($payment_date_raw)?date('Y-m-d', strtotime($payment_date_raw)):'0000-00-00';

				$paymentstatus_id=0;
				if(isset($param['paymentstatus_id']) && $param['paymentstatus_id']!="")
				{
					$paymentstatus_id=senetize_input($param['paymentstatus_id']);
				}

				$paymentnotes=0;
				if(isset($param['paymentnotes']) && $param['paymentnotes']!="")
				{
					$paymentnotes=senetize_input($param['paymentnotes']);
				}

				$techniciancomments=0;
				if(isset($param['techniciancomments']) && $param['techniciancomments']!="")
				{
					$techniciancomments=senetize_input($param['techniciancomments']);
				}

				$amount=0;
				if(isset($param['amount']) && $param['amount']!="")
				{
					$amount=senetize_input($param['amount']);
				}

				//-----data validation ends

				////----------validate issue
				if(isset($param['stops']))
				{
					$stops=json_decode($param['stops'],true);
					$stops_array_senetized=[];
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['jobworktype_id']))
						{
							$jobworktype_id=senetize_input($stop['jobworktype_id']);
							$stop_item_senetized['jobworktype_id']=$jobworktype_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work type";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['jobwork_id']))
						{
							$jobwork_id=senetize_input($stop['jobwork_id']);
							$stop_item_senetized['jobwork_id']=$jobwork_id;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['description']))
						{
							$description=senetize_input($stop['description']);
							$stop_item_senetized['description']=$description;
						}
						else
						{
							$InvalidDataMessage="Please provide description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['nocharge_id']))
						{
							$nocharge_id=senetize_input($stop['nocharge_id']);
							$stop_item_senetized['nocharge_id']=$nocharge_id;
						}
						else
						{
							$InvalidDataMessage="Please provide no charge";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['warranty_id']))
						{
							$warranty_id=senetize_input($stop['warranty_id']);
							$stop_item_senetized['warranty_id']=$warranty_id;
						}
						else
						{
							$InvalidDataMessage="Please provide warranty";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($stop['expiremiles']))
						{
							$expiremiles=senetize_input($stop['expiremiles']);
							$stop_item_senetized['expiremiles']=$expiremiles;
						}
						else
						{
							$InvalidDataMessage="Please provide expire miles";
							$dataValidation=false;
							goto ValidationChecker;
						}

						if(isset($stop['expiredays']))
						{
							$expiredays=senetize_input($stop['expiredays']);
							$stop_item_senetized['expiredays']=$expiredays;
						}
						else
						{
							$InvalidDataMessage="Please provide expire days";
							$dataValidation=false;
							goto ValidationChecker;
						}

						if(isset($stop['expirehours']))
						{
							$expirehours=senetize_input($stop['expirehours']);
							$stop_item_senetized['expirehours']=$expirehours;
						}
						else
						{
							$InvalidDataMessage="Please provide expire hours";
							$dataValidation=false;
							goto ValidationChecker;
						}

					//----/validate issue reported
						//----validdate issue description
						if(isset($stop['quantity']))
						{
							$quantity=senetize_input($stop['quantity']);
							$stop_item_senetized['quantity']=$quantity;
						}
						else
						{
							$InvalidDataMessage="Please provide quantity";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
					//----validdate issue description
						if(isset($stop['cost']))
						{
							$cost=senetize_input($stop['cost']);
							$stop_item_senetized['cost']=$cost;
						}
						else
						{
							$InvalidDataMessage="Please provide cost";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation)
				{
			    //--check if the code exists
					$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_work_order_header` SET `workorder_date`='$order_date', `unittype_id`='$unittype_id', `unit_id`='$unit_id', `vendor_id`='$vendor_id', `state_id`='$state_id', `city_id`='$city_id', `contactperson_name`='$contact_person', `vendor_phone`='$contact_no', `vendor_email`='$email_id', `billing_method_id_fk`='$billingmethod_id', `invoice_no`='$invoice_no', `payment_status_id_fk`='$paymentstatus_id', `payment_date`='$payment_date', `payment_notes`='$paymentnotes', `engine_reading`='$enginereading', `technician_comments`='$techniciancomments', `workorder_amount`='$amount' WHERE `workorder_id`='$update_id'");

					if($update)
					{
						$stop_deleted=true;
						$delete=mysqli_query($GLOBALS['con'],"DELETE FROM `workorder_detail` WHERE `workorder_id`='$update_id'");
							if(!$delete)
							{
								$stop_deleted=false;
							}
						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `workorder_detail` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['order_id']:100000;
						
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `workorder_detail`(`workorder_id`, `jobworktype_id`, `jobwork_id`, `description`, `nocharge_id`, `warranty_id`, `expire_miles`, `expire_days`, `expire_hours`, `quantity`, `cost`) VALUES ('$update_id','".$stop_row['jobworktype_id']."','".$stop_row['jobwork_id']."','".$stop_row['description']."','".$stop_row['nocharge_id']."','".$stop_row['warranty_id']."','".$stop_row['expiremiles']."','".$stop_row['expiredays']."','".$stop_row['expirehours']."','".$stop_row['quantity']."','".$stop_row['cost']."')");
							if(!$insertStop)
							{
								$stop_inserted=false;
							}
						}
						///---------//insert issue

						if($stop_inserted)
						{
							$status=true;
							//$message="Updated Successfuly".count($stops_array_senetized);
							$message="Updated Successfuly";
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
				}
				else
				{
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
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}

	*/
}
?>