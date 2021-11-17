<?php
class ExpressLoads
{

	function isValidId($id){

		$id=senetize_input($id);

		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `express_load_id` from `d_express_loads` WHERE `express_load_id`='$id' AND `express_load_id_status`='ACT'"))==1){

			return true;

		}else{

			return false;

		}

	}
	private function  update_shipper_info_from_stops_to_loads_table($express_load_id){

		$message='';

		$eld_id_q=mysqli_query($GLOBALS['con'],"SELECT `el_stop_date`,`el_stop_location_id_fk` FROM `d_express_load_details` LEFT JOIN `d_express_load_stops` ON `d_express_load_details`.`eld_id`=`d_express_load_stops`.`el_stop_express_load_detail_id_fk` WHERE `el_stop_category`='SHIPPER' AND `eld_express_load_id_fk`='$express_load_id'");

		if($eld_id_q){

			if(mysqli_num_rows($eld_id_q)==1){
				$s=mysqli_fetch_assoc($eld_id_q);

				$update=mysqli_query($GLOBALS['con'],"UPDATE `d_express_loads` SET `express_load_shipper_date`='".$s['el_stop_date']."',`express_load_shipper_location_id`='".$s['el_stop_location_id_fk']."' WHERE `express_load_id`='$express_load_id'");
				if($update){
					$message='updated';
				}else{
					$message='STEP B'.mysqli_error($GLOBALS['con']);
				}
			}

		}else{
			$message='STEP A'.mysqli_error($GLOBALS['con']);
		}

		return $message;

	}	
	private function  update_consignee_info_from_stops_to_loads_table($express_load_id){

		$message='';

		$eld_id_q=mysqli_query($GLOBALS['con'],"SELECT `el_stop_date`,`el_stop_location_id_fk` FROM `d_express_load_details` LEFT JOIN `d_express_load_stops` ON `d_express_load_details`.`eld_id`=`d_express_load_stops`.`el_stop_express_load_detail_id_fk` WHERE `el_stop_category`='CONSIGNEE' AND `eld_express_load_id_fk`='$express_load_id'");

		if($eld_id_q){

			if(mysqli_num_rows($eld_id_q)==1){
				$s=mysqli_fetch_assoc($eld_id_q);

				$update=mysqli_query($GLOBALS['con'],"UPDATE `d_express_loads` SET `express_load_consignee_date`='".$s['el_stop_date']."',`express_load_consignee_location_id`='".$s['el_stop_location_id_fk']."' WHERE `express_load_id`='$express_load_id'");
				if($update){
					$message='updated';
				}else{
					$message='STEP B'.mysqli_error($GLOBALS['con']);
				}
			}

		}else{
			$message='STEP A'.mysqli_error($GLOBALS['con']);
		}

		return $message;

	}



	function load_status_wise_total($param){

		$status=false;

		$message=null;

		$response=null;

		$qEx=mysqli_query($GLOBALS['con'],"SELECT `load_status_id`,`load_status_name`, COUNT(`express_load_id`) AS `total` FROM `d_mas_load_status` LEFT JOIN `d_express_loads` ON `d_mas_load_status`.`load_status_id`=`d_express_loads`.`express_load_status_id_fk` WHERE `express_load_id_status`='ACT' GROUP BY `load_status_id` ORDER BY `load_status_name`");

		$list=[];

		while ($rows=mysqli_fetch_assoc($qEx)) {

			array_push($list,[
				'load_status_id'=>$rows['load_status_id'],
				'load_status_name'=>$rows['load_status_name'],
				'total_express_loads'=>$rows['total'],
			]);

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



	function express_loads_opr_info_history($param){

		$status=false;

		$message=null;

		$response=[];

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;

		if(isset($param['eid']) && $param['eid']!=""){
			$id=$Enc->safeurlde($param['eid']);
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `express_load_id`,`express_load_status_id_fk`, `truck_id`,`truck_code`,`driver_id`,`driver_code`,`driver_name_first`,`driver_name_middle`,`driver_name_last`,`express_load_opr_info_updated_on`,`express_load_opr_info_updated_by` FROM `logs_d_express_loads_operation_info` LEFT JOIN `trucks` ON `logs_d_express_loads_operation_info`.`express_load_truck_id_fk`=`trucks`.`truck_id` LEFT JOIN `drivers` ON `logs_d_express_loads_operation_info`.`express_load_driver_id_fk`=`drivers`.`driver_id` WHERE `express_load_id`='$id' ORDER BY `express_load_id`");
		}else{
			$message="Please provide eid";
		}


		$list=[];

		include_once APPROOT.'/models/masters/Users.php';

		$Users=new Users;

		while ($rows=mysqli_fetch_assoc($qEx)) {

			$updated_by_user=$Users->user_basic_details($rows['express_load_opr_info_updated_by']);

			$stops_detail=$this->get_express_load_stop_records(['express_load_detail_id'=>$rows['eld_id']]);
			$driver_name_first=($rows['driver_name_first']!=null)?$rows['driver_name_first']:'';
			$driver_name_middle=($rows['driver_name_middle']!=null)?$rows['driver_name_middle']:'';
			$driver_name_last=($rows['driver_name_last']!=null)?$rows['driver_name_last']:'';
			$driver_name=$driver_name_first.' '.$driver_name_middle.' '.$driver_name_last;

			array_push($list,[
				'updated_by_user_code'=>$updated_by_user['user_code'],
				'update_on_datetime'=>dateTimeFromDbTimestamp($rows['express_load_opr_info_updated_on']),
				'alloted_driver_id'=>($rows['driver_id']!=null)?$rows['driver_id']:'',
				'alloted_driver_code'=>($rows['driver_code']!=null)?$rows['driver_code']:'',
				'alloted_driver_name'=>$driver_name,
				'alloted_truck_id'=>($rows['truck_id']!=null)?$rows['truck_id']:'',
				'alloted_truck_code'=>($rows['truck_code']!=null)?$rows['truck_code']:''
			]);

		}

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



	function express_loads_update_booking_info($param){

		$status=false;

		$message=null;

		$response=null;

		$confirm=null;
		$confirmMessage="";

		if(in_array('P0170', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";

			if(isset($param['update_eid'])){

				$update_id=$Enc->safeurlde($param['update_eid']);

			}else{

				$InvalidDataMessage="Please provide update eid";

				$dataValidation=false;

				goto ValidationChecker;

			}

			$booked_by_id="";
			if(isset($param['booked_by_id']) && $param['booked_by_id']!=""){

				$booked_by_id=senetize_input($param['booked_by_id']);

				include_once APPROOT.'/models/masters/Users.php';

				$Users=new Users;

				if(!$Users->isValidId($booked_by_id)){

					$InvalidDataMessage="Invalid booked by id";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}

			if(isset($param['is_pending_rc']) && $param['is_pending_rc']!=""){

				$is_pending_rc=(to_boolean($param['is_pending_rc']))?1:0;


			}else{

				$InvalidDataMessage="Please provide pending R/C status";

				$dataValidation=false;

				goto ValidationChecker;

			}




			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';

				$update_exp_load_opr_info=mysqli_query($GLOBALS['con'],"UPDATE `d_express_loads` SET `express_load_booked_by_id_fk`='$booked_by_id',`express_load_is_pending_rc`='$is_pending_rc',`express_load_book_info_updated_on`='$time',`express_load_book_info_updated_by`='$USERID' WHERE  `express_load_id`='$update_id'");

				if(!$update_exp_load_opr_info){

					$executionMessage=SOMETHING_WENT_WROG.' step 01';

					$execution=false;

					goto executionChecker;		

				}

				//----create logs of new express load operation info
				$insert_logs_exp_booking_info=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_express_loads_booking_info`(`express_load_id`, `express_load_booked_by_id_fk`, `express_load_is_pending_rc`, `express_load_book_info_updated_on`, `express_load_book_info_updated_by`) VALUES('$update_id','$booked_by_id','$is_pending_rc','$time','$USERID')");

				if(!$insert_logs_exp_booking_info){

					$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}




				executionChecker:

				if($execution){

					$message="Updated Successfuly";
					$status=true;

				}else{

					$message=$executionMessage;

				}



			}else{

				$message=$InvalidDataMessage;

			}



		}else{

			$message=NOT_AUTHORIZED_MSG;

		}

		$r=[];

		$r['status']=$status;

		$r['message']=$message;

		$r['response']=$response;

		$r['confirm']=$param;
		$r['confirm_message']=$confirmMessage;
		return $r;



	}


	function express_loads_update_operation_info($param){

		$status=false;

		$message=null;

		$response=null;

		$confirm=null;
		$confirmMessage="";

		if(in_array('P0170', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";

			if(isset($param['update_eid'])){

				$update_id=$Enc->safeurlde($param['update_eid']);

			}else{

				$InvalidDataMessage="Please provide update eid";

				$dataValidation=false;

				goto ValidationChecker;

			}




			$status_id="";
			if(isset($param['status_id']) && $param['status_id']!=""){

				$status_id=senetize_input($param['status_id']);

				include_once APPROOT.'/models/dispatch/LoadStatus.php';

				$LoadStatus=new LoadStatus;

				if(!$LoadStatus->isValidId($status_id)){

					$InvalidDataMessage="Invalid status id";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}
			$truck_id="";
			if(isset($param['truck_id']) && $param['truck_id']!=""){

				$truck_id=senetize_input($param['truck_id']);

				include_once APPROOT.'/models/masters/Trucks.php';

				$Trucks=new Trucks;

				if(!$Trucks->isValidId($truck_id)){

					$InvalidDataMessage="Invalid truck id";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}

			include_once APPROOT.'/models/masters/Drivers.php';

			$Drivers=new Drivers;

			$driver_id="";
			if(isset($param['driver_id']) && $param['driver_id']!=""){

				$driver_id=senetize_input($param['driver_id']);



				if(!$Drivers->isValidId($driver_id)){

					$InvalidDataMessage="Invalid driver id";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}

			$is_team_driver=0;
			$driver_b_id=0;
			if(isset($param['is_team_driver']) && $param['is_team_driver']!="" && to_boolean($param['is_team_driver'])==true){
				$is_team_driver=1;
				if(isset($param['driver_b_id']) && $param['driver_b_id']!=""){
					$driver_b_id=senetize_input($param['driver_b_id']);

					if(!$Drivers->isValidId($driver_b_id)){

						$InvalidDataMessage="Invalid driver b id";

						$dataValidation=false;

						goto ValidationChecker;

					}
				}
			}





			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';

				$update_exp_load_opr_info=mysqli_query($GLOBALS['con'],"UPDATE `d_express_loads` SET `express_load_status_id_fk`='$status_id',`express_load_is_team_driver`='$is_team_driver',`express_load_driver_b_id_fk`='$driver_b_id',`express_load_driver_id_fk`='$driver_id',`express_load_truck_id_fk`='$truck_id',`express_load_opr_info_updated_on`='$time',`express_load_opr_info_updated_by`='$USERID' WHERE  `express_load_id`='$update_id'");

				if(!$update_exp_load_opr_info){

					$executionMessage=SOMETHING_WENT_WROG.' step 01';

					$execution=false;

					goto executionChecker;		

				}

				//----create logs of new express load operation info
				$insert_logs_exp_load_opr_info=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_express_loads_operation_info`(`express_load_id`, `express_load_status_id_fk`, `express_load_truck_id_fk`, `express_load_driver_id_fk`, `express_load_opr_info_updated_on`, `express_load_opr_info_updated_by`) VALUES('$update_id','$status_id','$truck_id','$driver_id','$time','$USERID')");

				if(!$insert_logs_exp_load_opr_info){

					$executionMessage=SOMETHING_WENT_WROG.' step 02';

					$execution=false;

					goto executionChecker;		

				}




				executionChecker:

				if($execution){

					$message="Updated Successfuly";
					$status=true;

				}else{

					$message=$executionMessage;

				}



			}else{

				$message=$InvalidDataMessage;

			}



		}else{

			$message=NOT_AUTHORIZED_MSG;

		}

		$r=[];

		$r['status']=$status;

		$r['message']=$message;

		$r['response']=$response;

		$r['confirm']=$param;
		$r['confirm_message']=$confirmMessage;
		return $r;



	}


	private function get_express_load_stop_records($param){
		$stops=[];
		$shipper=[];
		$consignee=[];
		if(isset($param['express_load_detail_id'])){
			$fetch_el_stops=mysqli_query($GLOBALS['con'],"SELECT `el_stop_id`, `el_stop_express_load_detail_id_fk`, `el_stop_type`,`el_stop_category`, `el_stop_appointment_type`, `el_stop_datetime_tbd`, `el_stop_date`, `el_stop_time_from`, `el_stop_time_to`, `el_stop_location_id_fk`, `el_stop_id_status` , `city`.`location_name` AS `city_name`,`state`.`location_mini_code` AS `state_mini_code` FROM `d_express_load_stops` LEFT JOIN `locations` AS `city` ON `city`.`location_id`=`d_express_load_stops`.`el_stop_location_id_fk` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` WHERE `el_stop_express_load_detail_id_fk`='".senetize_input($param['express_load_detail_id'])."'");

			while($s_rows=mysqli_fetch_assoc($fetch_el_stops)){

				$row=[

					'category'=>$s_rows['el_stop_category'],
					'type'=>$s_rows['el_stop_type'],

					'appointment_type'=>$s_rows['el_stop_appointment_type'],

					'date'=>dateFromDbDatetime($s_rows['el_stop_date']),

					'time_from'=>timeFromDbTime($s_rows['el_stop_time_from']),
					'time_to'=>timeFromDbTime($s_rows['el_stop_time_to']),

					'datetime_tbd'=>$s_rows['el_stop_datetime_tbd'],

					'stop_type'=>$s_rows['el_stop_type'],

					'location'=>$s_rows['city_name'].', '.$s_rows['state_mini_code'],

					'location_id'=>$s_rows['el_stop_location_id_fk']

				];

				if($s_rows['el_stop_category']=='SHIPPER'){
					$shipper=$row;
				}elseif($s_rows['el_stop_category']=='CONSIGNEE'){
					$consignee=$row;
				}else{
					array_push($stops,$row);
				}
				

			}

		}

		return ['shipper'=>$shipper,'consignee'=>$consignee,'stops'=>$stops];

	}

	function express_loads_list($param){

		$status=false;

		$message=null;

		$response=null;

		$batch=5000;

		$page=1;

		if(isset($param['page'])){

			$page=intval(senetize_input($param['page']));



		}

		if($page<1){

			$page=1;

		}

		$from=$batch*($page-1);

		$range=$batch*$page;



		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;



		$q="SELECT `express_load_id`, `express_load_added_on`, `express_load_added_by`, `eld_id`, `eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_rate`, `eld_added_on`, `eld_added_by`,`customer_id`,`customer_code`,`customer_name`,`express_load_status_id_fk`, `truck_id`,`truck_code`,`driver_b`.`driver_id` AS `driver_b_id`,`driver_b`.`driver_code` AS `driver_b_code`,`driver_b`.`driver_name_first`  AS `driver_b_name_first`,`driver_b`.`driver_name_middle`  AS `driver_b_name_middle`,`driver_b`.`driver_name_last`  AS `driver_b_name_last`,`driver_a`.`driver_id` AS `driver_a_id`,`driver_a`.`driver_code` AS `driver_a_code`,`driver_a`.`driver_name_first`  AS `driver_a_name_first`,`driver_a`.`driver_name_middle`  AS `driver_a_name_middle`,`driver_a`.`driver_name_last`  AS `driver_a_name_last`,`express_load_is_team_driver`,`shipper_state`.`location_region_id_fk` AS `shipper_region_id`,`region_name` AS `shipper_region`,`express_load_booked_by_id_fk`,`express_load_is_pending_rc` FROM `d_express_loads` LEFT JOIN `d_express_load_details` ON `d_express_loads`.`express_load_id`=`d_express_load_details`.`eld_express_load_id_fk` LEFT JOIN `customers` ON  `customers`.`customer_id`=`d_express_load_details`.`eld_customer_id` LEFT JOIN `trucks` ON `d_express_loads`.`express_load_truck_id_fk`=`trucks`.`truck_id` LEFT JOIN `drivers` AS `driver_a` ON `d_express_loads`.`express_load_driver_id_fk`=`driver_a`.`driver_id` LEFT JOIN `drivers` AS `driver_b` ON `d_express_loads`.`express_load_driver_b_id_fk`=`driver_b`.`driver_id` LEFT JOIN `locations` AS `shipper_city`   ON `d_express_loads`.`express_load_shipper_location_id`=`shipper_city`.`location_id` LEFT JOIN `locations` AS `shipper_state` ON `shipper_city`.`location_state_id_fk`=`shipper_state`.`location_id` LEFT JOIN `location_regions` ON `shipper_state`.`location_region_id_fk`=`location_regions`.`region_id` WHERE `express_load_id_status`='ACT' AND `eld_id_status`='ACT'";

//----Apply Filters starts





		if(isset($param['common_search']) && $param['common_search']!=""){

			$common_search=senetize_input($param['common_search']);

			$q .=" AND (`express_load_id` LIKE '%$common_search%' OR `eld_po` LIKE '%$common_search%' OR `customer_code` LIKE '%$common_search%' OR `customer_name` LIKE '%$common_search%')";

		}
		
		if(isset($param['status_id']) && $param['status_id']!=""){
			$status_id=explode(',', senetize_input($param['status_id']));
			$status_id = implode('\', \'', $status_id);
			//$status_id = "'" . $names . "'"; 
			$q.=" AND  `express_load_status_id_fk` IN ('$status_id')";
		}
		if(isset($param['region_id']) && $param['region_id']!=""){
			$region_id=senetize_input($param['region_id']); 
			$q.=" AND  `shipper_state`.`location_region_id_fk`='$region_id'";
		}

		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=senetize_input($param['driver_id']); 
			$q.=" AND  (`express_load_driver_id_fk`='$driver_id' OR `express_load_driver_b_id_fk`='$driver_id')";
		}

		if(isset($param['is_team_driver']) && $param['is_team_driver']!=""){
			$is_team_driver=senetize_input($param['is_team_driver']); 
			$q.=" AND `express_load_is_team_driver`='$is_team_driver'";
		}

		if(isset($param['truck_id']) && $param['truck_id']!=""){
			$truck_id=senetize_input($param['truck_id']); 
			$q.=" AND  `express_load_truck_id_fk`='$truck_id'";
		}
		
		if(isset($param['shipper_date_from']) && isValidDateFormat($param['shipper_date_from'])){
			$shipper_date_from=date('Y-m-d', strtotime($param['shipper_date_from']));
			$q .=" AND `express_load_shipper_date` >='$shipper_date_from'";
		}

		if(isset($param['shipper_date_to']) && isValidDateFormat($param['shipper_date_to'])){
			$shipper_date_to=date('Y-m-d', strtotime($param['shipper_date_to']));
			$q .=" AND `express_load_shipper_date` <='$shipper_date_to'";
		}

		if(isset($param['consignee_date_from']) && isValidDateFormat($param['consignee_date_from'])){
			$consignee_date_from=date('Y-m-d', strtotime($param['consignee_date_from']));
			$q .=" AND `express_load_consignee_date` >='$consignee_date_from'";
		}

		if(isset($param['consignee_date_to']) && isValidDateFormat($param['consignee_date_to'])){
			$consignee_date_to=date('Y-m-d', strtotime($param['consignee_date_to']));
			$q .=" AND `express_load_consignee_date` <='$consignee_date_to'";
		}
//-----Apply fitlers ends





		if(isset($param['sort_by'])){

			switch ($param['sort_by']) {

				case 'express_load_id':

				$q .=" ORDER BY `express_load_id`";

				break;

				case 'shipper_date':

				$q .=" ORDER BY `express_load_shipper_date`";

				break;

				case 'consignee_date':

				$q .=" ORDER BY `express_load_consignee_date`";

				break;

				case 'customer_code':

				$q .=" ORDER BY `customer_code`";

				break;

				case 'region_name':

				$q .=" ORDER BY `region_name`";

				break;
				case 'trailer_type':

				$q .=" ORDER BY `eld_trailer_type`";

				break;				

				default:

				$q .=" ORDER BY `express_load_id`";

				break;

			}

		}else{

			$q .=" ORDER BY `express_load_id`";	

		}









		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

		$q .=" limit $from, $range";

		$qEx=mysqli_query($GLOBALS['con'],$q);



		$list=[];

		include_once APPROOT.'/models/masters/Users.php';

		$Users=new Users;

		while ($rows=mysqli_fetch_assoc($qEx)) {

			$added_by_user=$Users->user_basic_details($rows['express_load_added_by']);
			$booked_by_user=$Users->user_basic_details($rows['express_load_booked_by_id_fk']);

			$stops_detail=$this->get_express_load_stop_records(['express_load_detail_id'=>$rows['eld_id']]);
			$driver_name_first=($rows['driver_a_name_first']!=null)?$rows['driver_a_name_first']:'';
			$driver_name_middle=($rows['driver_a_name_middle']!=null)?$rows['driver_a_name_middle']:'';
			$driver_name_last=($rows['driver_a_name_last']!=null)?$rows['driver_a_name_last']:'';
			$driver_name=$driver_name_first.' '.$driver_name_middle.' '.$driver_name_last;

			
			$driver_b_name_first=($rows['driver_b_name_first']!=null)?$rows['driver_b_name_first']:'';
			$driver_b_name_middle=($rows['driver_b_name_middle']!=null)?$rows['driver_b_name_middle']:'';
			$driver_b_name_last=($rows['driver_b_name_last']!=null)?$rows['driver_b_name_last']:'';
			$driver_b_name=$driver_b_name_first.' '.$driver_b_name_middle.' '.$driver_b_name_last;


			
			$el_list=[

				'id'=>$rows['express_load_id'],

				'eid'=>$Enc->safeurlen($rows['express_load_id']),

				'customer_eid'=>$Enc->safeurlen($rows['customer_id']),

				'customer_code'=>$rows['customer_code'],

				'customer_name'=>$rows['customer_name'],

				'po_number'=>$rows['eld_po'],

				'trailer_type'=>$rows['eld_trailer_type'],

				'temperature_to_maintain'=>$rows['eld_temperature_to_maintain'],

				'rate'=>$rows['eld_rate'],
				
				'status_id'=>$rows['express_load_status_id_fk'],
				'booked_by_user_id'=>$booked_by_user['user_id'],
				'booked_by_user_code'=>$booked_by_user['user_code'],
				'pending_rc'=>($rows['express_load_is_pending_rc']==1)?true:false,

				'added_by_user_code'=>$added_by_user['user_code'],

				'added_on_datetime'=>dateTimeFromDbTimestamp($rows['express_load_added_on']),

				'shipper'=>$stops_detail['shipper'],
				'consignee'=>$stops_detail['consignee'],
				'stops'=>$stops_detail['stops'],
				'shipper_region'=>($rows['shipper_region']!=null)?$rows['shipper_region']:'',
				'is_team_driver'=>($rows['express_load_is_team_driver']==1)?true:false,
				'alloted_driver_id'=>($rows['driver_a_id']!=null)?$rows['driver_a_id']:'',
				'alloted_driver_code'=>($rows['driver_a_code']!=null)?$rows['driver_a_code']:'',
				'alloted_driver_name'=>$driver_name,
				'alloted_driver_b_id'=>($rows['driver_b_id']!=null)?$rows['driver_b_id']:'',
				'alloted_driver_b_code'=>($rows['driver_b_code']!=null)?$rows['driver_b_code']:'',
				'alloted_driver_b_name'=>$driver_b_name,
				'alloted_truck_id'=>($rows['truck_id']!=null)?$rows['truck_id']:'',
				'alloted_truck_code'=>($rows['truck_code']!=null)?$rows['truck_code']:''

			];



			//--fetch stops of express loads

			array_push($list,$el_list);

			//--/fetch stops of express loads

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

		$r['message']=$param;

		$r['response']=$response;

		return $r;	

	}







	function express_loads_add_new($param){

		$status=false;

		$message=null;

		$response=null;
		$confirm="";
		$confirmMessage="";

		if(in_array('P0168', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";





			if(isset($param['customer_id'])){

				$customer_id=senetize_input($param['customer_id']);

				include_once APPROOT.'/models/dispatch/Customers.php';

				$Customers=new Customers;



				if(!$Customers->isValidId($customer_id)){

					$InvalidDataMessage="Invalid customer id";

					$dataValidation=false;

					goto ValidationChecker;

				}

				

			}else{

				$InvalidDataMessage="Please provide customer id";

				$dataValidation=false;

				goto ValidationChecker;

			}

			

			if(isset($param['po_number'])){

				$po_number=senetize_input($param['po_number']);

				//---check duplicacy of po_number
				$validate_po=mysqli_query($GLOBALS['con'],"SELECT  `express_load_id` FROM `d_express_loads` LEFT JOIN `d_express_load_details` ON `d_express_loads`.`express_load_id`=`d_express_load_details`.`eld_express_load_id_fk` WHERE `eld_id_status`='ACT' AND `express_load_id_status`='ACT' AND `eld_po`='$po_number'");
				if(mysqli_num_rows($validate_po)>0){

			///---check if duplicate PO numbers is set as true;
					if(isset($param['allow_duplicate_po_number']) && to_boolean($param['allow_duplicate_po_number'])==true){

					}else{
						$po_number_list="";
						while($pon=mysqli_fetch_assoc($validate_po)){
							$po_number_list.=$pon['express_load_id'].", ";
						}

						$InvalidDataMessage="CONFIRM";
						$confirm="ALLOW DUPLICATE PO NUMBER";
						$confirmMessage="Load ".$po_number_list." for this PO number has been already created. Do you want to create new one ?";
						$dataValidation=false;

						goto ValidationChecker;	
					}


				}

				//---check duplicacy of po_number





			}else{

				$InvalidDataMessage="Please provide po_number";

				$dataValidation=false;

				goto ValidationChecker;

			}



			if(isset($param['trailer_type']) && $param['trailer_type']!=""){

				

				if(in_array($param['trailer_type'],['DRY','REEFER'])){

					$trailer_type=senetize_input($param['trailer_type']);

				}else{

					$InvalidDataMessage="Invalid reefer type";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide reefer type";

				$dataValidation=false;

				goto ValidationChecker;			

			}			

			$temperature_to_maintain='';
			$reefer_mode='';

			if($trailer_type=='REEFER'){

				if(isset($param['temperature_to_maintain']) && $param['temperature_to_maintain']!=""){

					$temperature_to_maintain=senetize_input($param['temperature_to_maintain']);

					if(!preg_match("/^[0-9.-]{1,}$/",$temperature_to_maintain)){

						$InvalidDataMessage="Please provide valid temperature to maintain";

						$dataValidation=false;

						goto ValidationChecker;						

					}

				}else{

					$InvalidDataMessage="Please provide temperature to maintain";

					$dataValidation=false;

					goto ValidationChecker;			

				}
				if(isset($param['reefer_mode']) && $param['reefer_mode']!=""){
					$reefer_mode=senetize_input($param['reefer_mode']);

					if(!in_array($reefer_mode,['START/STOP','CONTINUE'])){

						$InvalidDataMessage="Please provide valid reefer mode";

						$dataValidation=false;

						goto ValidationChecker;						

					}
				}else{

					$InvalidDataMessage="Please provide reefer mode";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}else{

				$InvalidDataMessage="Please provide temperature to maintain";

				$dataValidation=false;

				goto ValidationChecker;			

			}		

			

			$address_line=(isset($param['address_line']))?senetize_input($param['address_line']):'';



			include_once APPROOT.'/models/masters/Locations.php';

			$Locations=new Locations;





			if(isset($param['rate']) && $param['rate']!=""){

				$rate=senetize_input($param['rate']);

				if(!preg_match("/^[0-9.]{1,}$/",$rate)){

					$InvalidDataMessage="Please provide valid rate";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}else{

				$InvalidDataMessage="Please provide rate";

				$dataValidation=false;

				goto ValidationChecker;			

			}				









////----------validate stops



			if(isset($param['stops'])){

				$stops=$param['stops'];



				$stops_array_senetized=[];
				$total_shipper_stops=0;
				$total_consignee_stops=0;
				$rowd='';
				foreach ($stops as $stop) {
					$stop_item_senetized=[];

					if(isset($stop['stop_category']) && $stop['stop_category']!=''){

						$stop_category=senetize_input($stop['stop_category']);
						$rowd.=$stop_category.',';
						if($stop_category=='SHIPPER'){
							$total_shipper_stops++;
						}elseif ($stop_category=='CONSIGNEE') {
							$total_consignee_stops++;
						}

						if(!in_array($stop_category,['SHIPPER','CONSIGNEE','STOP'])){

							$InvalidDataMessage="Invalid stop category";

							$dataValidation=false;

							goto ValidationChecker;

						}
						$stop_item_senetized['stop_category']=$stop_category;

					}else{

						$InvalidDataMessage="Please provide stop category";

						$dataValidation=false;

						goto ValidationChecker;

					}



						//----validate stop type id

					if(isset($stop['stop_type']) && $stop['stop_type']!=''){

						$stop_type=senetize_input($stop['stop_type']);

						$stop_item_senetized['stop_type']=$stop_type;

						if(!in_array($stop_type,['PICK','DROP'])){

							$InvalidDataMessage="Invalid stop type";

							$dataValidation=false;

							goto ValidationChecker;

						}

						if($stop_category=='SHIPPER'){
							$stop_item_senetized['stop_series']=1;
							$stop_item_senetized['stop_type']='PICK';
						}elseif ($stop_category=='CONSIGNEE') {
							$stop_item_senetized['stop_series']=2;
							$stop_item_senetized['stop_type']='DROP';
						}else{
							$stop_item_senetized['stop_series']=3;
							$stop_item_senetized['stop_type']=$stop_type;
						}



					}else{

						$InvalidDataMessage="Please provide stop type";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate stop type id



						//----validate appointment type id

					if(isset($stop['appointment_type']) && $stop['appointment_type']!=''){

						$appointment_type=senetize_input($stop['appointment_type']);

						$stop_item_senetized['appointment_type']=$appointment_type;



						if(!in_array($appointment_type,['FCFS','FIRM'])){

							$InvalidDataMessage="Invalid appointment type";

							$dataValidation=false;

							goto ValidationChecker;



						}



					}else{

						$InvalidDataMessage="Please provide appointment type";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate appointment type id









						//----validate stop location id

					if(isset($stop['stop_location_id'])){

						$stop_location_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_location_id']);



						include_once APPROOT.'/models/masters/Locations.php';

						$Locations=new Locations;



						if($Locations->isValidId($stop_location_id)){

							$stop_item_senetized['stop_location_id']=$stop_location_id;

						}else{

							$InvalidDataMessage="Invalid stop location value";

							$dataValidation=false;

							goto ValidationChecker;							

						}



					}else{

						$InvalidDataMessage="Please provide stop location id";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate stop location id







					if(isset($stop['stop_datetime_tbd']) && $stop['stop_datetime_tbd']!=""){



						if(in_array($stop['stop_datetime_tbd'],['YES','NO'])){

							$stop_datetime_tbd=$stop_item_senetized['stop_datetime_tbd']=$stop['stop_datetime_tbd'];

						}else{

							$InvalidDataMessage="Please provide valid stope tbd value";

							$dataValidation=false;

							goto ValidationChecker;	

						}			

					}else{

						$InvalidDataMessage="Please provide stop tbd";

						$dataValidation=false;

						goto ValidationChecker;

					}



					$stop_date='0000-00-00';//--initialize defalut stop date

					$stop_time='00-00';	//--initialize defalut stop time





					if($stop_datetime_tbd=='NO'){

				//--validate stop date

						if(isset($stop['stop_date'])){

							if(isValidDateFormat($stop['stop_date'])){

								$stop_date=date('Y-m-d', strtotime($stop['stop_date']));

							}else{

								$InvalidDataMessage="Please provide valid stop date";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop date";

							$dataValidation=false;

							goto ValidationChecker;

						}

				//--/validate stop date

				//--validate stop time

						if(isset($stop['stop_time_from'])){

							if(isValidTimeFormat($stop['stop_time_from'])){

								$stop_time_from=date('H:i', strtotime($stop['stop_time_from']));



							}else{

								$InvalidDataMessage="Please provide valid stop time from";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop time from";

							$dataValidation=false;

							goto ValidationChecker;

						}

						if(isset($stop['stop_time_to'])){

							if(isValidTimeFormat($stop['stop_time_to'])){

								$stop_time_to=date('H:i', strtotime($stop['stop_time_to']));



							}else{

								$InvalidDataMessage="Please provide valid stop time to";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop time to";

							$dataValidation=false;

							goto ValidationChecker;

						}




				//--/validate stop time										

					}elseif($stop_datetime_tbd=='YES'){

						if(isset($stop['stop_date'])){

							if(isValidDateFormat($stop['stop_date'])){

								$stop_date=date('Y-m-d', strtotime($stop['stop_date']));

							}

						}

						if(isset($stop['stop_time_to'])){
							if(isValidTimeFormat($stop['stop_time_to'])){
								$stop_time_to=date('H:i', strtotime($stop['stop_time_to']));
							}

						}

						if(isset($stop['stop_time_from'])){
							if(isValidTimeFormat($stop['stop_time_from'])){
								$stop_time_from=date('H:i', strtotime($stop['stop_time_from']));
							}

						}

					}
					$stop_item_senetized['stop_date']=$stop_date;
					$stop_item_senetized['stop_time_from']=$stop_time_from;
					$stop_item_senetized['stop_time_to']=$stop_time_to;

					array_push($stops_array_senetized, $stop_item_senetized);

				}

			}

				//--check if exactly one SHIPPER and one CONSINEE row is sent
			if($total_shipper_stops!=1){
				$InvalidDataMessage="Please provide one  shipper category row";
				$dataValidation=false;
				goto ValidationChecker;
			}
			if($total_consignee_stops!=1){
				$InvalidDataMessage="Please provide one  consignee category row".$rowd;
				$dataValidation=false;
				goto ValidationChecker;
			}

//---re-arrange the order of stops make Shipper as first record, Consinee as second and 
			$stop_series = array_column($stops_array_senetized, 'stop_series');

			array_multisort($stop_series, $stops_array_senetized);

////---------//-validate stops











			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';





			//-----Insert base ID of express load

 				///-----Generate New Unique Id

				$get_old_id_el=mysqli_query($GLOBALS['con'],"SELECT `express_load_id` FROM `d_express_loads` ORDER BY `auto` DESC LIMIT 1");

				$get_old_id_el=(mysqli_num_rows($get_old_id_el)==1)?(mysqli_fetch_assoc($get_old_id_el)['express_load_id']):'EL000000';

				$next_id_el='EL'.sprintf('%06d',(intval(substr($get_old_id_el,2))+1));

				///-----//Generate New Unique Id				

				$insert_express_load_id=mysqli_query($GLOBALS['con'],"INSERT INTO `d_express_loads`(`express_load_id`, `express_load_id_status`, `express_load_added_on`, `express_load_added_by`) VALUES ('$next_id_el','ACT','$time','$USERID')");

				if(!$insert_express_load_id){

					$executionMessage=SOMETHING_WENT_WROG.' step 01';

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert base ID of express load







			//-----Insert express load details



				$get_old_id_eld=mysqli_query($GLOBALS['con'],"SELECT `eld_id` FROM `d_express_load_details` ORDER BY `auto` DESC LIMIT 1");

				$get_old_id_eld=(mysqli_num_rows($get_old_id_eld)==1)?(mysqli_fetch_assoc($get_old_id_eld)['eld_id']):'0';

				$next_id_eld=$get_old_id_eld+1;



				$insert_express_load_details=mysqli_query($GLOBALS['con'],"INSERT INTO `d_express_load_details`(`eld_id`,`eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_reefer_mode`,`eld_rate`, `eld_id_status`, `eld_added_on`, `eld_added_by`) VALUES ('$next_id_eld','$next_id_el','$customer_id','$po_number','$trailer_type','$temperature_to_maintain','$reefer_mode','$rate','ACT','$time','$USERID')");

				if(!$insert_express_load_details){

					$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert express load details







			//-----Insert express load stops



				$get_old_id_eld_stop=mysqli_query($GLOBALS['con'],"SELECT `el_stop_id` FROM `d_express_load_stops` ORDER BY `auto` DESC LIMIT 1");

				$next_id_eld_stop=(mysqli_num_rows($get_old_id_eld_stop)==1)?(mysqli_fetch_assoc($get_old_id_eld_stop)['el_stop_id']):'0';



				foreach ($stops_array_senetized as $sr) {

					$next_id_eld_stop++;

					$insert_express_load_stop=mysqli_query($GLOBALS['con'],"INSERT INTO `d_express_load_stops`(`el_stop_id`,`el_stop_express_load_detail_id_fk`, `el_stop_type`,`el_stop_category`, `el_stop_appointment_type`, `el_stop_datetime_tbd`, `el_stop_date`, `el_stop_time_from`, `el_stop_time_to`, `el_stop_location_id_fk`) VALUES ('$next_id_eld_stop','$next_id_eld','".$sr['stop_type']."','".$sr['stop_category']."','".$sr['appointment_type']."','".$sr['stop_datetime_tbd']."','".$sr['stop_date']."','".$sr['stop_time_from']."','".$sr['stop_time_to']."','".$sr['stop_location_id']."')");

					if(!$insert_express_load_stop){

						$executionMessage=SOMETHING_WENT_WROG.' step 03'.mysqli_error($GLOBALS['con']);

						$execution=false;

						goto executionChecker;		

					}	

				}
			//-----/Insert express load stops

				executionChecker:

				if($execution){
					$status=true;

					$message="Added Successfuly";
					$this->update_shipper_info_from_stops_to_loads_table($next_id_el);
					$this->update_consignee_info_from_stops_to_loads_table($next_id_el);
				}else{

					$message=$executionMessage;

				}



			}else{

				$message=$InvalidDataMessage;

			}



		}else{

			$message=NOT_AUTHORIZED_MSG;

		}

		$r=[];

		$r['status']=$status;

		$r['message']=$message;


		$r['confirm']=$confirm;
		$r['confirm_message']=$confirmMessage;
		$r['response']=$response;
		return $r;



	}

	function express_loads_update($param){

		$status=false;

		$message=null;

		$response=null;

		$confirm="";
		$confirmMessage="";

		if(in_array('P0170', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";





			if(isset($param['update_eid'])){

				$update_id=$Enc->safeurlde($param['update_eid']);

			}else{

				$InvalidDataMessage="Please provide update eid";

				$dataValidation=false;

				goto ValidationChecker;

			}





			if(isset($param['customer_id'])){

				$customer_id=senetize_input($param['customer_id']);

				include_once APPROOT.'/models/dispatch/Customers.php';

				$Customers=new Customers;



				if(!$Customers->isValidId($customer_id)){

					$InvalidDataMessage="Invalid customer id";

					$dataValidation=false;

					goto ValidationChecker;

				}

				

			}else{

				$InvalidDataMessage="Please provide customer id";

				$dataValidation=false;

				goto ValidationChecker;

			}

			
			if(isset($param['po_number'])){

				$po_number=senetize_input($param['po_number']);

				//---check duplicacy of po_number
				$validate_po=mysqli_query($GLOBALS['con'],"SELECT  `express_load_id` FROM `d_express_loads` LEFT JOIN `d_express_load_details` ON `d_express_loads`.`express_load_id`=`d_express_load_details`.`eld_express_load_id_fk` WHERE `eld_id_status`='ACT' AND `express_load_id_status`='ACT' AND `eld_po`='$po_number'");
				if(mysqli_num_rows($validate_po)>0){

			///---check if duplicate PO numbers is set as true;
					if(isset($param['allow_duplicate_po_number']) && to_boolean($param['allow_duplicate_po_number'])==true){

					}else{
						$po_number_list='';
						while($pon=mysqli_fetch_assoc($validate_po)){
							$po_number_list.=$pon['express_load_id'].", ";
						}

						$InvalidDataMessage="CONFIRM";
						$confirm="ALLOW DUPLICATE PO NUMBER";
						$confirmMessage="Load ".$po_number_list." for this PO number has been already created. Do you want to create new one ?";
						$dataValidation=false;

						goto ValidationChecker;	
					}


				}

				//---check duplicacy of po_number





			}else{

				$InvalidDataMessage="Please provide po_number";

				$dataValidation=false;

				goto ValidationChecker;

			}



			if(isset($param['trailer_type']) && $param['trailer_type']!=""){

				

				if(in_array($param['trailer_type'],['DRY','REEFER'])){

					$trailer_type=senetize_input($param['trailer_type']);

				}else{

					$InvalidDataMessage="Invalid reefer type";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide reefer type";

				$dataValidation=false;

				goto ValidationChecker;			

			}			

			$temperature_to_maintain='';
			$reefer_mode='';

			if($trailer_type=='REEFER'){

				if(isset($param['temperature_to_maintain']) && $param['temperature_to_maintain']!=""){

					$temperature_to_maintain=senetize_input($param['temperature_to_maintain']);

					if(!preg_match("/^[0-9.-]{1,}$/",$temperature_to_maintain)){

						$InvalidDataMessage="Please provide valid temperature to maintain";

						$dataValidation=false;

						goto ValidationChecker;						

					}

				}else{

					$InvalidDataMessage="Please provide temperature to maintain";

					$dataValidation=false;

					goto ValidationChecker;			

				}
				if(isset($param['reefer_mode']) && $param['reefer_mode']!=""){
					$reefer_mode=senetize_input($param['reefer_mode']);

					if(!in_array($reefer_mode,['START/STOP','CONTINUE'])){

						$InvalidDataMessage="Please provide valid reefer mode";

						$dataValidation=false;

						goto ValidationChecker;						

					}
				}else{

					$InvalidDataMessage="Please provide reefer mode";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}		


			$address_line=(isset($param['address_line']))?senetize_input($param['address_line']):'';



			include_once APPROOT.'/models/masters/Locations.php';

			$Locations=new Locations;





			if(isset($param['rate']) && $param['rate']!=""){

				$rate=senetize_input($param['rate']);





				if(!preg_match("/^[0-9.]{1,}$/",$rate)){

					$InvalidDataMessage="Please provide valid rate";

					$dataValidation=false;

					goto ValidationChecker;						

				}





			}else{

				$InvalidDataMessage="Please provide rate";

				$dataValidation=false;

				goto ValidationChecker;			

			}				









////----------validate stops



			if(isset($param['stops'])){

				$stops=$param['stops'];



				$stops_array_senetized=[];
				$total_shipper_stops=0;
				$total_consignee_stops=0;

				foreach ($stops as $stop) {

					$stop_item_senetized=[];



					if(isset($stop['stop_category']) && $stop['stop_category']!=''){

						$stop_category=senetize_input($stop['stop_category']);
						if($stop_category=='SHIPPER'){
							$total_shipper_stops++;
						}elseif ($stop_category=='CONSIGNEE') {
							$total_consignee_stops++;
						}

						if(!in_array($stop_category,['SHIPPER','CONSIGNEE','STOP'])){

							$InvalidDataMessage="Invalid stop category";

							$dataValidation=false;

							goto ValidationChecker;

						}
						$stop_item_senetized['stop_category']=$stop_category;

					}else{

						$InvalidDataMessage="Please provide stop category";

						$dataValidation=false;

						goto ValidationChecker;

					}



						//----validate stop type id
					if(isset($stop['stop_type']) && $stop['stop_type']!=''){

						$stop_type=senetize_input($stop['stop_type']);

						$stop_item_senetized['stop_type']=$stop_type;

						if(!in_array($stop_type,['PICK','DROP'])){

							$InvalidDataMessage="Invalid stop type";

							$dataValidation=false;

							goto ValidationChecker;

						}

						if($stop_category=='SHIPPER'){
							$stop_item_senetized['stop_series']=1;
							$stop_item_senetized['stop_type']='PICK';
						}elseif ($stop_category=='CONSIGNEE') {
							$stop_item_senetized['stop_series']=2;
							$stop_item_senetized['stop_type']='DROP';
						}else{
							$stop_item_senetized['stop_series']=3;
							$stop_item_senetized['stop_type']=$stop_type;
						}



					}else{

						$InvalidDataMessage="Please provide stop type";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate stop type id



						//----validate appointment type id

					if(isset($stop['appointment_type']) && $stop['appointment_type']!=''){

						$appointment_type=senetize_input($stop['appointment_type']);

						$stop_item_senetized['appointment_type']=$appointment_type;



						if(!in_array($appointment_type,['FCFS','FIRM'])){

							$InvalidDataMessage="Invalid appointment type";

							$dataValidation=false;

							goto ValidationChecker;



						}



					}else{

						$InvalidDataMessage="Please provide appointment type";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate appointment type id









						//----validate stop location id

					if(isset($stop['stop_location_id'])){

						$stop_location_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_location_id']);



						include_once APPROOT.'/models/masters/Locations.php';

						$Locations=new Locations;



						if($Locations->isValidId($stop_location_id)){

							$stop_item_senetized['stop_location_id']=$stop_location_id;

						}else{

							$InvalidDataMessage="Invalid stop location value";

							$dataValidation=false;

							goto ValidationChecker;							

						}



					}else{

						$InvalidDataMessage="Please provide stop location id";

						$dataValidation=false;

						goto ValidationChecker;

					}

						//----/validate stop location id






					if(isset($stop['stop_datetime_tbd']) && $stop['stop_datetime_tbd']!=""){



						if(in_array($stop['stop_datetime_tbd'],['YES','NO'])){

							$stop_datetime_tbd=$stop_item_senetized['stop_datetime_tbd']=$stop['stop_datetime_tbd'];

						}else{

							$InvalidDataMessage="Please provide valid stope tbd value";

							$dataValidation=false;

							goto ValidationChecker;	

						}			

					}else{

						$InvalidDataMessage="Please provide stop tbd";

						$dataValidation=false;

						goto ValidationChecker;

					}



					$stop_date='0000-00-00';//--initialize defalut stop date

					$stop_time='00-00';	//--initialize defalut stop time





					if($stop_datetime_tbd=='NO'){

				//--validate stop date

						if(isset($stop['stop_date'])){

							if(isValidDateFormat($stop['stop_date'])){

								$stop_date=date('Y-m-d', strtotime($stop['stop_date']));

							}else{

								$InvalidDataMessage="Please provide valid stop date";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop date";

							$dataValidation=false;

							goto ValidationChecker;

						}

				//--/validate stop date

				//--validate stop time

						if(isset($stop['stop_time_from'])){

							if(isValidTimeFormat($stop['stop_time_from'])){

								$stop_time_from=date('H:i', strtotime($stop['stop_time_from']));



							}else{

								$InvalidDataMessage="Please provide valid stop time from";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop time from";

							$dataValidation=false;

							goto ValidationChecker;

						}

						if(isset($stop['stop_time_to'])){

							if(isValidTimeFormat($stop['stop_time_to'])){

								$stop_time_to=date('H:i', strtotime($stop['stop_time_to']));



							}else{

								$InvalidDataMessage="Please provide valid stop time to";

								$dataValidation=false;

								goto ValidationChecker;							

							}

						}else{

							$InvalidDataMessage="Please provide stop time to";

							$dataValidation=false;

							goto ValidationChecker;

						}




				//--/validate stop time										

					}elseif($stop_datetime_tbd=='YES'){

						if(isset($stop['stop_date'])){

							if(isValidDateFormat($stop['stop_date'])){

								$stop_date=date('Y-m-d', strtotime($stop['stop_date']));

							}

						}

						if(isset($stop['stop_time_to'])){
							if(isValidTimeFormat($stop['stop_time_to'])){
								$stop_time_to=date('H:i', strtotime($stop['stop_time_to']));
							}

						}

						if(isset($stop['stop_time_from'])){
							if(isValidTimeFormat($stop['stop_time_from'])){
								$stop_time_from=date('H:i', strtotime($stop['stop_time_from']));
							}

						}

					}
					$stop_item_senetized['stop_date']=$stop_date;
					$stop_item_senetized['stop_time_from']=$stop_time_from;
					$stop_item_senetized['stop_time_to']=$stop_time_to;
					array_push($stops_array_senetized, $stop_item_senetized);

				}

			}


				//--check if exactly one SHIPPER and one CONSINEE row is sent
			if($total_shipper_stops!=1){
				$InvalidDataMessage="Please provide one  shipper category row";
				$dataValidation=false;
				goto ValidationChecker;
			}
			if($total_consignee_stops!=1){
				$InvalidDataMessage="Please provide one  consignee category row".$rowd;
				$dataValidation=false;
				goto ValidationChecker;
			}



////---------//-validate stops











			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';







				$update_last_update=mysqli_query($GLOBALS['con'],"UPDATE `d_express_loads` SET `express_load_updated_on`='$time',`express_load_updated_by`='$USERID' WHERE  `express_load_id`='$update_id'");

				if(!$update_last_update){

					$executionMessage=SOMETHING_WENT_WROG.' step 01';

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert base ID of express load







				//---deactivate old express details

				$deactivate_old_detail=mysqli_query($GLOBALS['con'],"UPDATE `d_express_load_details` SET `eld_id_status`='DEL',`eld_deleted_on`='$time',`eld_deleted_by`='$USERID' WHERE `eld_express_load_id_fk`='$update_id' AND `eld_id_status`='ACT'");



				if(!$deactivate_old_detail){

					$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;	

				}





			//-----Insert express load new details



				$get_old_id_eld=mysqli_query($GLOBALS['con'],"SELECT `eld_id` FROM `d_express_load_details` ORDER BY `auto` DESC LIMIT 1");

				$get_old_id_eld=(mysqli_num_rows($get_old_id_eld)==1)?(mysqli_fetch_assoc($get_old_id_eld)['eld_id']):'0';

				$next_id_eld=$get_old_id_eld+1;



				$insert_express_load_details=mysqli_query($GLOBALS['con'],"INSERT INTO `d_express_load_details`(`eld_id`,`eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_reefer_mode`,`eld_rate`, `eld_id_status`, `eld_added_on`, `eld_added_by`) VALUES ('$next_id_eld','$update_id','$customer_id','$po_number','$trailer_type','$temperature_to_maintain','$reefer_mode','$rate','ACT','$time','$USERID')");

				if(!$insert_express_load_details){

					$executionMessage=SOMETHING_WENT_WROG.' step 03'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert express load details







			//-----Insert express load stops



				$get_old_id_eld_stop=mysqli_query($GLOBALS['con'],"SELECT `el_stop_id` FROM `d_express_load_stops` ORDER BY `auto` DESC LIMIT 1");

				$next_id_eld_stop=(mysqli_num_rows($get_old_id_eld_stop)==1)?(mysqli_fetch_assoc($get_old_id_eld_stop)['el_stop_id']):'0';



				foreach ($stops_array_senetized as $sr) {

					$next_id_eld_stop++;

					$insert_express_load_stop=mysqli_query($GLOBALS['con'],"INSERT INTO `d_express_load_stops`(`el_stop_id`,`el_stop_express_load_detail_id_fk`, `el_stop_type`,`el_stop_category`, `el_stop_appointment_type`, `el_stop_datetime_tbd`, `el_stop_date`, `el_stop_time_from`, `el_stop_time_to`, `el_stop_location_id_fk`) VALUES ('$next_id_eld_stop','$next_id_eld','".$sr['stop_type']."','".$sr['stop_category']."','".$sr['appointment_type']."','".$sr['stop_datetime_tbd']."','".$sr['stop_date']."','".$sr['stop_time_from']."','".$sr['stop_time_to']."','".$sr['stop_location_id']."')");

					if(!$insert_express_load_stop){

						$executionMessage=SOMETHING_WENT_WROG.' step 04'.mysqli_error($GLOBALS['con']);

						$execution=false;

						goto executionChecker;		

					}	

				}



			//-----/Insert express load stops





				///-----create logs of deleted data

				$this->create_logs_of_express_load_details();







				executionChecker:

				if($execution){
					$status=true;
					$message="Updated Successfuly";
					$this->update_shipper_info_from_stops_to_loads_table($update_id);
					$this->update_consignee_info_from_stops_to_loads_table($update_id);

				}else{

					$message=$executionMessage;

				}



			}else{

				$message=$InvalidDataMessage;

			}



		}else{

			$message=NOT_AUTHORIZED_MSG;

		}

		$r=[];

		$r['status']=$status;

		$r['message']=$message;

		$r['response']=$response;

		$r['confirm']=$confirm;
		$r['confirm_message']=$confirmMessage;
		return $r;



	}





	





	protected function create_logs_of_express_load_details()

	{

		$status=true;

		$message=null;

	//------this function will fetch all the records from express load details table that are deleted and dump this record for into log table to

	//------this action is needed just to remove deleted recoreds from real tables



	//---firstly get the last record in express load table stops table

	//---avoid deletion of these. otherwise it impact auto generated id system

		$get_last_id_eld_q=mysqli_query($GLOBALS['con'],"SELECT `eld_id` FROM `d_express_load_details` ORDER BY `auto` DESC LIMIT 1");

		if(mysqli_num_rows($get_last_id_eld_q)==1){

			$get_last_id_eld=mysqli_fetch_assoc($get_last_id_eld_q)['eld_id'];

			$del_records=mysqli_query($GLOBALS['con'],"SELECT `eld_id` FROM `d_express_load_details` WHERE `eld_id_status`='DEL' AND NOT `eld_id`='$get_last_id_eld'");



			while($res=mysqli_fetch_assoc($del_records)){

				$eld_id=$res['eld_id'];

		//check if the last id in stops table is not belongs to this load

				$get_last_id_stops_q=mysqli_query($GLOBALS['con'],"SELECT `el_stop_express_load_detail_id_fk` FROM `d_express_load_stops` ORDER BY `auto` DESC LIMIT 1");

				if(mysqli_num_rows($get_last_id_stops_q)==1){

					if(mysqli_fetch_assoc($get_last_id_stops_q)['el_stop_express_load_detail_id_fk']!=$eld_id){



				//---dump real data of el stops table to logs table

						$dump_stops=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_express_load_stops` (`el_stop_id`, `el_stop_express_load_detail_id_fk`, `el_stop_type`, `el_stop_category`, `el_stop_appointment_type`, `el_stop_datetime_tbd`, `el_stop_date`, `el_stop_time_from`, `el_stop_time_to`, `el_stop_location_id_fk`, `el_stop_id_status``)

							SELECT `el_stop_id`, `el_stop_express_load_detail_id_fk`, `el_stop_type`, `el_stop_category`, `el_stop_appointment_type`, `el_stop_datetime_tbd`, `el_stop_date`, `el_stop_time_from`, `el_stop_time_to`, `el_stop_location_id_fk`, `el_stop_id_status`` FROM `d_express_load_stops`

							WHERE `el_stop_express_load_detail_id_fk`='$eld_id'");

					if($dump_stops){ ///---if record's dumping executes successfuly del the real recrods

					mysqli_query($GLOBALS['con'],"DELETE FROM `d_express_load_stops`

						WHERE `el_stop_express_load_detail_id_fk`='$eld_id'");



				}



				//---dump real data of ex load details table to logs table	



				$dump_details=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_express_load_details` (`eld_id`, `eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_reefer_mode`, `eld_rate`, `eld_id_status`, `eld_added_on`, `eld_added_by`, `eld_deleted_on`, `eld_deleted_by`)

					SELECT `eld_id`, `eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_reefer_mode`, `eld_rate`, `eld_id_status`, `eld_added_on`, `eld_added_by`, `eld_deleted_on`, `eld_deleted_by` FROM `d_express_load_details`

					WHERE `eld_id`='$eld_id'");

					if($dump_details){ ///---if record's dumping executes successfuly del the real recrods

					mysqli_query($GLOBALS['con'],"DELETE FROM `d_express_load_details`

						WHERE `eld_id`='$eld_id'");



				}					



			}

		}





	}			

}

return array('status' => $status,'message'=>$message );



}

function express_loads_details($param){

	$status=false;

	$message=null;

	$response=[];

	if(in_array('P0169', USER_PRIV)){

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;

		$dataValidation=true;

		$InvalidDataMessage="";



		if(isset($param['eid']) &&  $param['eid']!=""){

			$customer_id=$Enc->safeurlde($param['eid']);

		}else{

			$dataValidation=false;

			$InvalidDataMessage="Please provide cusetomer eid";

			goto ValidationChecker;

		}

		ValidationChecker:

		if($dataValidation){

			$q=mysqli_query($GLOBALS['con'],"SELECT `express_load_id`, `express_load_added_on`, `express_load_added_by`, `eld_id`, `eld_express_load_id_fk`, `eld_customer_id`, `eld_po`, `eld_trailer_type`, `eld_temperature_to_maintain`,`eld_reefer_mode`,`eld_rate`, `eld_added_on`, `eld_added_by`,`customer_id`,`customer_code`,`customer_name`,`express_load_status_id_fk`,`express_load_truck_id_fk`, `express_load_is_team_driver`, `express_load_driver_id_fk`, `express_load_driver_b_id_fk`,`truck_id`,`truck_code`,`driver_b`.`driver_id` AS `driver_b_id`,`driver_b`.`driver_code` AS `driver_b_code`,`driver_b`.`driver_name_first`  AS `driver_b_name_first`,`driver_b`.`driver_name_middle`  AS `driver_b_name_middle`,`driver_b`.`driver_name_last`  AS `driver_b_name_last`,`driver_a`.`driver_id` AS `driver_a_id`,`driver_a`.`driver_code` AS `driver_a_code`,`driver_a`.`driver_name_first`  AS `driver_a_name_first`,`driver_a`.`driver_name_middle`  AS `driver_a_name_middle`,`driver_a`.`driver_name_last`  AS `driver_a_name_last` FROM `d_express_loads` LEFT JOIN `d_express_load_details` ON `d_express_loads`.`express_load_id`=`d_express_load_details`.`eld_express_load_id_fk` LEFT JOIN `customers` ON  `customers`.`customer_id`=`d_express_load_details`.`eld_customer_id` LEFT JOIN `trucks` ON `d_express_loads`.`express_load_truck_id_fk`=`trucks`.`truck_id` LEFT JOIN `drivers` AS `driver_a` ON `d_express_loads`.`express_load_driver_id_fk`=`driver_a`.`driver_id` LEFT JOIN `drivers` AS `driver_b` ON `d_express_loads`.`express_load_driver_b_id_fk`=`driver_b`.`driver_id` WHERE `express_load_id_status`='ACT' AND `eld_id_status`='ACT' AND `express_load_id`='$customer_id'");

			if(mysqli_num_rows($q)==1){

				$status=true;

				$rows=mysqli_fetch_assoc($q);

				include_once APPROOT.'/models/masters/Users.php';

				$Users=new Users;

				$added_by_user=$Users->user_basic_details($rows['express_load_added_by']);
				$stops_detail=$this->get_express_load_stop_records(['express_load_detail_id'=>$rows['eld_id']]);

				$driver_name_first=($rows['driver_a_name_first']!=null)?$rows['driver_a_name_first']:'';
				$driver_name_middle=($rows['driver_a_name_middle']!=null)?$rows['driver_a_name_middle']:'';
				$driver_name_last=($rows['driver_a_name_last']!=null)?$rows['driver_a_name_last']:'';
				$driver_name=$driver_name_first.' '.$driver_name_middle.' '.$driver_name_last;


				$driver_b_name_first=($rows['driver_b_name_first']!=null)?$rows['driver_b_name_first']:'';
				$driver_b_name_middle=($rows['driver_b_name_middle']!=null)?$rows['driver_b_name_middle']:'';
				$driver_b_name_last=($rows['driver_b_name_last']!=null)?$rows['driver_b_name_last']:'';
				$driver_b_name=$driver_b_name_first.' '.$driver_b_name_middle.' '.$driver_b_name_last;


				$response['details']=[

					'id'=>$rows['express_load_id'],

					'eid'=>$Enc->safeurlen($rows['express_load_id']),

					'customer_id'=>$rows['customer_id'],

					'customer_eid'=>$Enc->safeurlen($rows['customer_id']),

					'customer_code'=>$rows['customer_code'],

					'customer_name'=>$rows['customer_name'],

					'po_number'=>$rows['eld_po'],

					'trailer_type'=>$rows['eld_trailer_type'],

					'temperature_to_maintain'=>$rows['eld_temperature_to_maintain'],
					'reefer_mode'=>$rows['eld_reefer_mode'],

					'rate'=>$rows['eld_rate'],
					'status_id'=>$rows['express_load_status_id_fk'],
					'is_team_driver'=>($rows['express_load_is_team_driver']==1)?true:false,
					'alloted_driver_id'=>($rows['driver_a_id']!=null)?$rows['driver_a_id']:'',
					'alloted_driver_code'=>($rows['driver_a_code']!=null)?$rows['driver_a_code']:'',
					'alloted_driver_name'=>$driver_name,
					'alloted_driver_b_id'=>($rows['driver_b_id']!=null)?$rows['driver_b_id']:'',
					'alloted_driver_b_code'=>($rows['driver_b_code']!=null)?$rows['driver_b_code']:'',
					'alloted_driver_b_name'=>$driver_b_name,
					'alloted_truck_id'=>($rows['truck_id']!=null)?$rows['truck_id']:'',
					'alloted_truck_code'=>($rows['truck_code']!=null)?$rows['truck_code']:'',
					'shipper'=>$stops_detail['shipper'],
					'consignee'=>$stops_detail['consignee'],
					'stops'=>$stops_detail['stops']

				];











			}else{

				$message="Invalid customer eid";

			}

		}else{

			$message=$InvalidDataMessage;

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



/*



	function trucks_delete($param){

		$status=false;

		$message=null;

		$response=null;

		if(in_array('P0021', USER_PRIV)){





			if(isset($param['delete_eid'])){

				include_once APPROOT.'/models/common/Enc.php';

				$Enc=new Enc;



				$delete_eid=$Enc->safeurlde($param['delete_eid']);				

				$USERID=USER_ID;

				$time=time();



			//--check if the code exists

				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `truck_id` FROM `trucks` WHERE `truck_id`='$delete_eid' AND NOT `truck_status`='DLT'");

				if(mysqli_num_rows($codeRows)==1){

					$delete=mysqli_query($GLOBALS['con'],"UPDATE `trucks` SET `truck_status`='DLT',`truck_deleted_on`='$time',`truck_deleted_by`='$USERID' WHERE `truck_id`='$delete_eid'");

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



	}*/





}

?>