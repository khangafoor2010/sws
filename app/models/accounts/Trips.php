<?php
/**
 *
 */
class Trips
{
	function trips_quick_totals($param)
	{
		$status=false;
		$message=null;
		$response=null;
		
		$res=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT 
			(SELECT COUNT(`trip_id`) FROM `trips` WHERE `trip_status`='ACT') as `total`,
			(SELECT COUNT(`trip_id`) FROM `trips` WHERE `trip_status`='ACT' and `trip_approval_status_id_fk`='PENDING') as `waiting_approval`,
			(SELECT COUNT(`trip_id`) FROM `trips` WHERE `trip_status`='ACT' and `trip_approval_status_id_fk`='APPROVED') as `approved`"));

		$response['total']=$res['total'];
		$response['waiting_approval']=$res['waiting_approval'];
		$response['approved']=$res['approved'];
	
		$status=true;
		return ['status'=>$status,'message'=>$message,'response'=>$response];
	}

	function trips_list($param){
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

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `trip_id`,`trip_detail_id`,`truck_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`,`trip_added_on`,`trip_added_by`,`trip_approved_on`,`trip_approved_by`,(SELECT SUM(ROUND(`trip_salary_parameter_amount`,2)) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_detail_id_fk`=`trip_id` AND `parameter_type_id_fk`='REIMBURSEMENT') AS `trip_salary_parameters_reimbursement` ,(SELECT SUM(ROUND(`trip_salary_parameter_amount`,2)) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_detail_id_fk`=`trip_id` AND `parameter_type_id_fk`='EARNING') AS `trip_salary_parameters_earning`,(SELECT SUM(ROUND(`trip_salary_parameter_amount`,2)) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_detail_id_fk`=`trip_id` AND `parameter_type_id_fk`='DEDUCTION') AS `trip_salary_parameters_deduction` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trip_details`.`trip_truck_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trip_details`.`trip_driver_group_id_fk`  WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT'";



//----Apply Filters starts

		if(isset($param['driver_id']) && $param['driver_id']!=''){
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q.=" AND (SELECT COUNT(`trip_driver_id`) FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`=`trip_detail_id` AND `trip_driver_driver_id_fk`='$driver_id')>0";
		}
//

		///------date rage filter

		///------date rage filter
		if(isset($param['start_date_from']) && isValidDateFormat($param['start_date_from'])){
			$start_date_from=date('Y-m-d', strtotime($param['start_date_from']));
			$q .=" AND trip_start_date >='$start_date_from'";
		}

		if(isset($param['start_date_to']) && isValidDateFormat($param['start_date_to'])){
			$start_date_to=date('Y-m-d', strtotime($param['start_date_to']));
			$q .=" AND trip_start_date <='$start_date_to'";
		}

		if(isset($param['approval_status_id']) && $param['approval_status_id']!=''){
			$approval_status_id=mysqli_real_escape_string($GLOBALS['con'],$param['approval_status_id']);
			$q.=" AND trip_approval_status_id_fk='$approval_status_id'";
		}
		if(isset($param['trip_id']) && $param['trip_id']!=""){
			$trip_id=mysqli_real_escape_string($GLOBALS['con'],$param['trip_id']);
			$q.=" AND trip_id='$trip_id'";
		}
		if(isset($param['truck_code']) && $param['truck_code']!=""){
			$truck_code=mysqli_real_escape_string($GLOBALS['con'],$param['truck_code']);
			$q.=" AND truck_code LIKE '%$truck_code%'";
		}		
//-----Apply fitlers ends
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'id':
				$q .=" ORDER BY `trip_id` ASC";
				break;
				case 'truck_code':
				$q .=" ORDER BY `truck_code` ASC";
				break;
				case 'driver_group_name':
				$q .=" ORDER BY `group_name` ASC";
				break;
				case 'approval_status':
				$q .=" ORDER BY `trip_approval_status_id_fk` ASC";
				break;
				case 'trip_start_date':
				$q .=" ORDER BY `trip_start_date` ASC";
				break;		
				default:
				$q .=" ORDER BY `trip_id` ASC";
				break;
			}
		}else{
			$q .=" ORDER BY `truck_code` ASC";	
		}




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $batch";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		$counter=$from;

		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;

		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['sr_no']=++$counter;
			$row['id']=$rows['trip_id'];
			$row['eid']=$Enc->safeurlen($rows['trip_id']);
			$row['truck_code']=$rows['truck_code'];
			$row['truck_eid']=$Enc->safeurlen($rows['truck_id']);
			$row['miles']=$rows['trip_total_miles'];
			$row['ppm']=$rows['trip_ppm'];
			$row['payout']=ROUND($rows['trip_ppm']*$rows['trip_total_miles'],2);
			$row['incentive_rate']=$rows['trip_incentive_per_mile'];
			$row['incentive']=$rows['trip_incentive'];
			$row['salary_parameters_earning']=($rows['trip_salary_parameters_earning']==null)?0:$rows['trip_salary_parameters_earning'];
			$row['salary_parameters_reimbursement']=($rows['trip_salary_parameters_reimbursement']==null)?0:$rows['trip_salary_parameters_reimbursement'];
			$row['salary_parameters_deduction']=($rows['trip_salary_parameters_deduction']==null)?0:$rows['trip_salary_parameters_deduction'];
			$row['driver_group_name']=$rows['group_name'];
			$row['start_date']=dateFromDbToFormat($rows['trip_start_date']);
			$row['end_date']=dateFromDbToFormat($rows['trip_end_date']);
			$row['approval_status']=$rows['trip_approval_status_id_fk'];

			$added_user=$Users->user_basic_details($rows['trip_added_by']);
			$row['added_by_user_code']=$added_user['user_code'];
			$row['added_by_user_name']=$added_user['user_name'];
			$row['added_on_date']=dateFromDbToFormat($rows['trip_added_on']);
			$row['added_on_time']=date('H:i',$rows['trip_added_on']);
			$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['trip_added_on']);

			$approved_by_user=$Users->user_basic_details($rows['trip_approved_by']);
			$row['approved_by_user_code']=$approved_by_user['user_code'];
			$row['approved_on_datetime']=dateTimeFromDbTimestamp($rows['trip_approved_on']);
		//$row['trip_payment_status']=($rows['total_paid_payment_for_trip']>0)?'TRIP-PAID':'TRIP-NOT-PAID';


//-------fetch stops
			$trip_stops=$this->get_trip_stops_records(array('trip_detail_id'=>$rows['trip_detail_id']));
			$row['trip_stops_names']=$trip_stops['response']['stops_names'];
//------/fetch stops



//---fetch trip_drivers records
			$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `driver_code`,`driver_id`, `driver_name_first` FROM `trip_drivers`  LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk`  WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_detail_id_fk`='".$rows['trip_detail_id']."'");
			$trip_drivers_array=[];

			$is_filtered_driver_exists=false;
			while ($td=mysqli_fetch_assoc($fetch_drivers)) {
				$tdr=[];
				$tdr['driver_eid']=$Enc->safeurlen($td['driver_id']);
				$tdr['driver_code']=$td['driver_code'];
				$tdr['driver_name']=$td['driver_name_first'];
				array_push($trip_drivers_array, $tdr);

//-------apply driver id filter
				if(isset($param['driver_id']) && $param['driver_id']!=""){
					if($param['driver_id']==$td['driver_id']){
						$is_filtered_driver_exists=true;
					}
				}else{
					$is_filtered_driver_exists=true;
				}
//-------/apply driver id filter			

			}
			$row['trip_drivers']=$trip_drivers_array;
//---/fetch trip_drivers records

		//-------if trip has filtered driver as true, only than insert the record. otherwise driver id filter won't work
			if($is_filtered_driver_exists){
				array_push($list,$row);
			} 

		}
		$response=[];
		$response['total']=$totalRows;
		$response['totalRows']=$totalRows;
		$response['totalPages']=ceil($totalRows/$batch);
		$response['currentPage']=$page;
		$response['resultFrom']=$from+1;
		$response['resultUpto']=$from+$batch;
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


	function get_trip_stops_records($param){
		$status=false;
		$message=null;
		$response=[];

	//---fetch trip_stop records
		$response['stops_names']="";
		$response['list']="";
		if(isset($param['trip_detail_id'])){

			$fetch_trip_stops=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id`, `trip_stop_trip_detail_id`,`stop_type_name`, `trip_stop_date_time`, `trip_stop_miles_driven`, `city`.`location_name` AS `city_name`,`state`.`location_mini_code` AS `state_mini_code`, `trip_stop_status` FROM `trip_stops` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_stops`.`trip_stop_trip_detail_id` LEFT JOIN `trip_stop_types` ON `trip_stop_types`.`stop_type_id`=`trip_stops`.`trip_stop_type_id_fk` LEFT JOIN `locations` AS `city` ON `city`.`location_id`=`trip_stops`.`trip_stop_location_id` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` WHERE `trip_stop_status`='ACT' AND `trip_stop_trip_detail_id`='".$param['trip_detail_id']."' ORDER BY `trip_stop_id`");
			$list=[];
			$stop_names=[];
			while ($ts=mysqli_fetch_assoc($fetch_trip_stops)) {
				$tsr=[];
				$tsr['stop_type_name']=$ts['stop_type_name'];
				$tsr['date']=dateFromDbToFormat($ts['trip_stop_date_time']);
				$tsr['miles']=$ts['trip_stop_miles_driven'];
				$tsr['location']=$ts['city_name'].', '.$ts['state_mini_code'];
				array_push($list, $tsr);
				array_push($stop_names, $tsr['location']);
			}
			$response['list']=$list;
			$response['stops_names']=implode(' - ', $stop_names);

		}else{
			$message="Please provide trip id";
		}

//---/fetch trip_stop records
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}


	function trips_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `trip_id`,`trip_detail_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`,`trip_added_on`,`added`.`user_name` AS `added_by_user_name`,`added`.`user_code` AS `added_by_user_code` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trip_details`.`trip_truck_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trip_details`.`trip_driver_group_id_fk` LEFT JOIN `utab` AS `added` ON `added`.`user_id`=`trips`.`trip_added_by` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$trip_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND trip_id='$trip_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$trip_id=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND trip_id='$trip_id'";
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
				$row['id']=$rows['trip_id'];
				$row['eid']=$Enc->safeurlen($rows['trip_id']);
				$row['truck_code']=$rows['truck_code'];
				$row['miles']=$rows['trip_total_miles'];
				$row['ppm']=$rows['trip_ppm'];
				$row['incentive_per_mile']=$rows['trip_incentive_per_mile'];
				$row['incentive']=$rows['trip_incentive'];
				$row['driver_group_name']=$rows['group_name'];
				$row['start_date']=dateFromDbToFormat($rows['trip_start_date']);
				$row['end_date']=dateFromDbToFormat($rows['trip_end_date']);
				$row['approval_status']=$rows['trip_approval_status_id_fk'];
				$row['added_by_user_code']=$rows['added_by_user_code'];
				$row['added_by_user_name']=$rows['added_by_user_name'];
				$row['added_on_date']=date('m/d/Y',$rows['trip_added_on']);
				$row['added_on_time']=date('H:i',$rows['trip_added_on']);
				$row['is_paid']=$this->is_paid($rows['trip_detail_id']);








//---fetch trip_stop records
				$trip_stops=$this->get_trip_stops_records(array('trip_detail_id'=>$rows['trip_detail_id']));
				$row['trip_stops']=$trip_stops['response']['list'];
//---/fetch trip_stop records

//---fetch trip_drivers records
				$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `driver_id`,`driver_code`, `driver_name_first`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`, `trip_driver_remarks` FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk`  WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_detail_id_fk`='".$rows['trip_detail_id']."'");
				$trip_drivers_array=[];
				while ($td=mysqli_fetch_assoc($fetch_drivers)) {
					$tdr=[];
					$tdr['driver_eid']=$Enc->safeurlen($td['driver_id']);
					$tdr['driver_code']=$td['driver_code'];
					$tdr['driver_name']=$td['driver_name_first'];
					$tdr['miles']=$td['trip_driver_mile'];
					$tdr['pay_per_mile']=$td['trip_driver_pay_per_mile'];
					$tdr['basic_earnings']=$td['trip_driver_basic_earnings'];
					$tdr['remarks']=$td['trip_driver_remarks'];

					$net_earnings=0;
					$net_earnings+=floatval($tdr['basic_earnings']);				

					$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$rows['trip_detail_id'] ,'driver_id'=>$td['driver_id'] ));
					$tdr['salary_parameters']=$salary_parameters;
					$tdr['net_earnings']=$net_earnings+(floatval($salary_parameters['net_impact']));
					$tdr['incentive']=$td['trip_driver_incentives'];
					array_push($trip_drivers_array, $tdr);
				}	
				$row['trip_drivers']=$trip_drivers_array;
//---/fetch trip_drivers records






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

	function is_paid($trip_detail_id){
		//---------it check if any payment has been made or not for this trip detail id
		$get_payment_paid_entries=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `payment_id` FROM `driver_payments` LEFT JOIN `driver_payments_paid` ON `driver_payments_paid`.`payment_paid_payment_id_fk`=`driver_payments`.`payment_id` WHERE `payment_status`='ACT' AND `payment_paid_status`='ACT' AND `payment_trip_detail_id_fk`='$trip_detail_id'"));
		if($get_payment_paid_entries>0){
			return true;
		}else{
			return false;
		}
	}


	function driver_salary_parameter($param){

		//--------- salary parameters
		$salary_parameter_types_array=[];
		$net_impact=0;
		$fetch_salary_parameters_types=mysqli_query($GLOBALS['con'],"SELECT `parameter_type_id`, `parameter_type_impact`, `parameter_type_status` FROM `salary_parameter_types` ORDER BY `parameter_type_id` DESC");
		while($slpt_rows=mysqli_fetch_assoc($fetch_salary_parameters_types)){
			$s_p_t_row=[];
			$s_p_t_row['type']=$slpt_rows['parameter_type_id'];
			$s_p_t_row['impact']=$slpt_rows['parameter_type_impact'];

						//------fetch parameters
			$q="SELECT `trip_salary_parameter_id`, `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`, `trip_salary_parameter_status`,`parameter_name` FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id`  WHERE `trip_salary_parameter_status`='ACT' ";

			if(isset($param['driver_id'])){
				$q .=" AND `trip_salary_parameter_driver_id_fk`='".$param['driver_id']."' ";
			}
			if(isset($param['trip_detail_id'])){
				$q .=" AND `trip_salary_parameter_trip_detail_id_fk`='".$param['trip_detail_id']."'";
			} 

			$q.=" AND `parameter_type_id_fk`='".$s_p_t_row['type']."'";
			$salary_parameters=[];
			$fetch_salary_parameters=mysqli_query($GLOBALS['con'],$q);
			while ($slp_rows=mysqli_fetch_assoc($fetch_salary_parameters)) {
				$slp_row=[];
				$slp_row['name']=$slp_rows['parameter_name'];
				$slp_row['amount']=round($slp_rows['trip_salary_parameter_amount'],2);
				array_push($salary_parameters, $slp_row);
			}
			$s_p_t_row['sum']=array_sum(array_column($salary_parameters, 'amount'));

					//--- Add/Subtrcact total of parameter  to net earnings
			if($slpt_rows['parameter_type_impact']=='NEGTIVE'){
				$net_impact= ($net_impact - floatval($s_p_t_row['sum']));
			}elseif ($slpt_rows['parameter_type_impact']=='POSITIVE') {
				$net_impact= ($net_impact + floatval($s_p_t_row['sum']));
			}
					//--- / Add/Subtrcact total of parameter  to net earnings

			$s_p_t_row['parameters']=$salary_parameters;
			array_push($salary_parameter_types_array, $s_p_t_row);
		}
//---------/salary parameters
		$return=[];
		$return['list']=$salary_parameter_types_array;
		$return['net_impact']=$net_impact;
		return $return;
	}

	function drivers_total_trips_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=500;
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

		$q="SELECT COUNT(`trip_driver_id`) AS `total_trips_by_driver`, `trip_driver_driver_id_fk`,SUM(`trip_driver_mile`) AS `total_miles_driven`, `driver_id`,`driver_code`, CONCAT(`driver_name_first`,' ', `driver_name_middle`,' ', `driver_name_last`) AS `driver_name` FROM `trip_drivers` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_details`.`trip_id_fk` WHERE `trip_approval_status_id_fk`='APPROVED'";


		$q.="  GROUP BY `trip_driver_driver_id_fk`";
		if(isset($param['sort_by'])){

			switch ($param['sort_by']) {
				case 'none':
				$q .=" ORDER BY `truck_code` ASC";
				break;		
				default:
				$q .=" ORDER BY `trip_driver_driver_id_fk` ASC";
				break;
			}
		}else{
			$q .=" ORDER BY `trip_driver_driver_id_fk` ASC";	
		}




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		$counter=$from;
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['sr_no']=++$counter;
			$row['driver_eid']=$Enc->safeurlen($rows['driver_id']);
			$row['driver_name']=$rows['driver_name'];
			$row['driver_code']=$rows['driver_code'];
			$row['total_trips_by_driver']=$rows['total_trips_by_driver'];
			$row['total_miles_driven']=$rows['total_miles_driven'];


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

	function driver_all_trips_list($param){
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

		$ValidRequest=true;

		//----------check if the valid driver id is send or not
		if(isset($param['driver_eid'])){
			$driver_id=$Enc->safeurlde($param['driver_eid']);
		}else{
			$InvalidRequestMessage="Please provide driver eid";
			$ValidRequest=false;
			goto ValidationChecker;			
		}
		//---------/check if the valid driver id is send or not

		ValidationChecker:
		if($ValidRequest){

			$q="SELECT `trip_id`,`trip_detail_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`, `driver_id`,`driver_code`, `driver_name_first`,`driver_name_middle`,`driver_name_last`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks` FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_details`.`trip_id_fk` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trip_details`.`trip_truck_id_fk` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trip_details`.`trip_driver_group_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_detail_status`='ACT'  AND `trip_driver_driver_id_fk`='$driver_id'  AND `trip_approval_status_id_fk`='APPROVED'";


$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
$q .=" limit $from, $range";
$qEx=mysqli_query($GLOBALS['con'],$q);

$list=[];
$counter=$from;
$driver_details=[];
while ($rows=mysqli_fetch_assoc($qEx)) {
	$driver_details['driver_code']=$rows['driver_code'];
	$driver_details['driver_name']=$rows['driver_name_first'].' '.$rows['driver_name_middle'].' '.$rows['driver_name_last'];
	$row=[];
	$row['sr_no']=++$counter;
	$row['id']=$rows['trip_id'];
	$row['eid']=$Enc->safeurlen($rows['trip_id']);
	$row['date']=dateFromDbToFormat($rows['trip_start_date']);
	$row['start_date']=dateFromDbToFormat($rows['trip_start_date']);
	$row['end_date']=dateFromDbToFormat($rows['trip_end_date']);

//---fetch trip_stop records
	$trip_stops=$this->get_trip_stops_records(array('trip_detail_id'=>$rows['trip_detail_id']));
	$row['trip_stops']=$trip_stops['response'];
//---/fetch trip_stop records


	$row['truck_code']=$rows['truck_code'];
	$row['miles']=$rows['trip_driver_mile'];
	$row['pay_per_mile']=$rows['trip_driver_pay_per_mile'];
	$row['basic_earnings']=$rows['trip_driver_basic_earnings'];
	$row['net_earnings']=$rows['trip_driver_net_earnings'];
	$row['incentive']=$rows['trip_driver_incentives'];
	array_push($list, $row);
}

$response=[];
$response['total']=$totalRows;
$response['totalRows']=$totalRows;
$response['totalPages']=ceil($totalRows/$batch);
$response['currentPage']=$page;
$response['resultFrom']=$from+1;
$response['resultUpto']=$range;
$response['list']=$list;
$response['driver_details']=$driver_details;
if(count($list)>0){
	$status=true;
}else{
	$message="No records found";
}


}else{
	$message=$InvalidRequestMessage;
}


$r=[];
$r['status']=$status;
$r['message']=$message;
$r['response']=$response;
return $r;	
}


function driver_trip_details($param){
	$status=false;
	$message=null;
	$response=[];

	$dataValidation=true;
	$InvalidDataMessage="";
	if(!isset($param['driver_eid'])){
		$dataValidation=false;
		$InvalidDataMessage="Please provide driver eid";		
	}

	if(!isset($param['trip_eid'])){
		$dataValidation=false;
		$InvalidDataMessage="Please prover trip eid";
	}

	if($dataValidation){

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$driver_id=$Enc->safeurlde($param['driver_eid']);
		$trip_id=$Enc->safeurlde($param['trip_eid']);

		$q="SELECT `trip_id`,`trip_detail_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`, `driver_id`,`driver_code`, `driver_name_first`,`driver_name_middle`,`driver_name_last`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks` FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_details`.`trip_id_fk` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trip_details`.`trip_truck_id_fk` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trip_details`.`trip_driver_group_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_id`='$trip_id' AND `trip_driver_driver_id_fk`='$driver_id'";

		$get=mysqli_query($GLOBALS['con'],$q);
		if(mysqli_num_rows($get)==1){
			$status=true;
			$rows=mysqli_fetch_assoc($get);
			$row=[];
			$row['driver_code']=$rows['driver_code'];
			$row['driver_name']=$rows['driver_name_first'].' '.$rows['driver_name_middle'].' '.$rows['driver_name_last'];
			$row['trip_id']=$rows['trip_id'];
			$row['truck_code']=$rows['truck_code'];
			$row['trip_total_miles']=$rows['trip_total_miles'];
			$row['pay_per_mile']=$rows['trip_ppm'];
			$row['trip_total_incentive']=$rows['trip_incentive'];
			$row['driver_group_name']=$rows['group_name'];
			$row['start_date']=dateFromDbToFormat($rows['trip_start_date']);
			$row['end_date']=dateFromDbToFormat($rows['trip_end_date']);
			$row['approval_status']=$rows['trip_approval_status_id_fk'];
			$row['driver_miles']=$rows['trip_driver_mile'];
			$row['pay_per_mile']=$rows['trip_driver_pay_per_mile'];
			$row['incentive_per_mile']=$rows['trip_incentive_per_mile'];
			$row['basic_earnings']=$rows['trip_driver_basic_earnings'];
			$row['driver_incentive']=$rows['trip_driver_incentives'];

//---fetch trip_stop records
			$trip_stops=$this->get_trip_stops_records(array('trip_detail_id'=>$rows['trip_detail_id']));
			$row['trip_stops']=$trip_stops['response'];
//---/fetch trip_stop records


//---fetch salary parameter records
			$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$rows['trip_detail_id'] ,'driver_id'=>$driver_id ));
			$row['salary_parameters']=$salary_parameters;
			$row['net_earnings']=$rows['trip_driver_basic_earnings']+$salary_parameters['net_impact'];
//---/fetch salary parameter records

			$response['details']=$row;
		}else{
			$message="No records found";
		}			
	}else{
		$message=$InvalidDataMessage;
	}


	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;	
}



function trips_details_for_updation($param){
	$status=false;
	$message=null;
	$response=null;
	$details_for="";
	$runQuery=false;
	if(isset($param['trip_eid'])){
		
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$trip_id=$Enc->safeurlde($param['trip_eid']);
		$query="SELECT `trip_id`,`trip_detail_id`, `trip_truck_id_fk`,`trip_driver_group_id_fk`,`trip_ppm_plan_group_id`,`trip_ppm`,`trip_incentive_per_mile` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id`  WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_id`='$trip_id'";


		$response=[];
		$get=mysqli_query($GLOBALS['con'],$query);
		if(mysqli_num_rows($get)==1){
			$status=true;
			$rows=mysqli_fetch_assoc($get);
			$row=[];
			$row['id']=$rows['trip_id'];
			$row['eid']=$Enc->safeurlen($rows['trip_id']);
			$row['truck_id']=$rows['trip_truck_id_fk'];
			$row['driver_group_id']=$rows['trip_driver_group_id_fk'];
			$row['ppm_plan_id']=$rows['trip_ppm_plan_group_id'];
			$row['incentive_per_mile']=$rows['trip_incentive_per_mile'];



			function salary_parameters_of_driver_for_trip($driver_id,$trip_detail_id){
				$get_parameters_q=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`  FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_detail_id_fk`='$trip_detail_id' AND  `trip_salary_parameter_status`='ACT' AND  `trip_salary_parameter_driver_id_fk`='$driver_id'");
				$parameters_list=[];
				while ($get_parameters_rows=mysqli_fetch_assoc($get_parameters_q)) {
					$get_parameters_row=[];
					$get_parameters_row['parameter_id']=$get_parameters_rows['trip_salary_parameter_parameter_id'];
					$get_parameters_row['amount']=$get_parameters_rows['trip_salary_parameter_amount'];
					array_push($parameters_list, $get_parameters_row);
				}
				return $parameters_list;
			}


//---fetch trip_drivers records
			$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_driver_id_fk`,`trip_driver_remarks` FROM `trip_drivers`   WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_detail_id_fk`='".$rows['trip_detail_id']."'");
			$trip_drivers_array=[];
			$drivers=[];
			while ($td=mysqli_fetch_assoc($fetch_drivers)) {
				$driver=[];
				$driver['id']=$td['trip_driver_driver_id_fk'];
				$driver['remarks']=$td['trip_driver_remarks'];
				$driver['salary_parameters']=salary_parameters_of_driver_for_trip($td['trip_driver_driver_id_fk'],$rows['trip_detail_id']);
				array_push($drivers,$driver);
			}

//---/fetch trip_drivers records

			$row['driver_a']=$drivers[0];

			if(isset($drivers[1])){
				$row['driver_b']=$drivers[1];
			}

			





//---fetch trip_stop records
			$fetch_trip_stops=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id`, `trip_stop_date_time`,`trip_stop_type_id_fk`,`trip_stop_location_id` ,`trip_stop_miles_driven` FROM `trip_stops`  WHERE `trip_stop_status`='ACT' AND `trip_stop_trip_detail_id`='".$rows['trip_detail_id']."' ORDER BY `trip_stop_id`");
			$list=[];
			$stop_names=[];
			while ($ts=mysqli_fetch_assoc($fetch_trip_stops)) {
				$tsr=[];
				$tsr['stop_eid']=$ts['trip_stop_id'];
				$tsr['stop_date']=dateFromDbToFormat($ts['trip_stop_date_time']);
				$tsr['stop_type_id']=$ts['trip_stop_type_id_fk'];
				$tsr['stop_miles']=$ts['trip_stop_miles_driven'];
				$tsr['stop_location_id']=$ts['trip_stop_location_id'];
				array_push($list, $tsr);
			}
			$row['stops_list']=$list;
//---/fetch trip_stop records

			$response['details']=$row;
		}else{
			$message="No records found";
		} 				
	}else{
		$message="Please provide trip eid";
	}

	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;	


}




/*

function trips_update($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0121', USER_PRIV)){

			//$message=REQUIRE_NECESSARY_FIELDS;
		$USERID=USER_ID;
		$time=time();
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['update_eid'])){
			$update_id=$Enc->safeurlde($param['update_eid']);

				//------check if all payments of trip are unpaid

			$get_all_paid_payments=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT COUNT(`payment_id`) AS `total_paids` FROM `driver_payments` WHERE `payment_status`='ACT' AND `payment_category`='TRIP-EARNINGS' AND `payment_trip_id_fk`='$update_id' AND `payment_pay_status`='PAID'"));
			if($get_all_paid_payments['total_paids']>0){
				$InvalidDataMessage="This trip can't be updated";
				$dataValidation=false;
				goto ValidationChecker;				
			}


		}else{
			$InvalidDataMessage="Please provide trip eid";
			$dataValidation=false;
			goto ValidationChecker;
		}






		if(isset($param['truck_id'])){
			$truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['truck_id']);

			include_once APPROOT.'/models/masters/Trucks.php';
			$Trucks=new Trucks;

			if(!$Trucks->isValidId($truck_id)){
				$InvalidDataMessage="Invalid truck value";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide truck id";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['ppm_plan_id'])){
			$ppm_plan_id=mysqli_real_escape_string($GLOBALS['con'],$param['ppm_plan_id']);

			include_once APPROOT.'/models/masters/DriverPpmPlans.php';
			$DriverPpmPlans=new DriverPpmPlans;

			if(!$DriverPpmPlans->isValidId($ppm_plan_id)){
				$InvalidDataMessage="Invalid ppm plan id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide ppm plan id";
			$dataValidation=false;
			goto ValidationChecker;
		}			



		if(isset($param['pay_per_mile'])){
			$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);
			if(!preg_match("/^[0-9.]{1,}$/",$pay_per_mile)){
				$InvalidDataMessage="Please provide valid pay per mile";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide pay per mile";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['incentive_rate'])){
			$incentive_rate=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_rate']);
			if(!preg_match("/^[0-9.]{1,}$/",$incentive_rate)){
				$InvalidDataMessage="Please provide valid incentive rate";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide valid incentive rate";
			$dataValidation=false;
			goto ValidationChecker;
		}





////----------validate stops

		if(isset($param['stops'])){
			$stops=$param['stops'];

			$stops_array_senetized=[];
			foreach ($stops as $stop) {
				$stop_item_senetized=[];
						//--validate stop date
				if(isset($stop['stop_date'])){
					if(isValidDateFormat($stop['stop_date'])){
						$stop_date=date('Y-m-d', strtotime($stop['stop_date']));
						$stop_item_senetized['stop_date']=$stop_date;

							///---------restrict the future date selection
						if($stop_date>date('Y-m-d')){
							$InvalidDataMessage="Futute date not allowed in any stop";
							$dataValidation=false;
							goto ValidationChecker;									
						}
							///---------/restrict the future date selection

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


				///-----check if all the stop dates are in ascending order
				if(!isset($set_date)){
					$set_date=$stop_date;
				}else{
						//--check if the new date is greater or equal to the old set date
						//--if greater than setdate equal to new date for next time check
						//--else throw error
					if($stop_date<$set_date){
						$InvalidDataMessage="Please provide valid stop date order";
						$dataValidation=false;
						goto ValidationChecker;	
					}
				}

						//----validate stop type id
				if(isset($stop['stop_type_id'])){
					$stop_type_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_type_id']);

					include_once APPROOT.'/models/masters/TripStopTypes.php';
					$TripStopTypes=new TripStopTypes;

					if($TripStopTypes->isValidId($stop_type_id)){
						$stop_item_senetized['stop_type_id']=$stop_type_id;

					}else{
						$InvalidDataMessage="Invalid stop type value";
						$dataValidation=false;
						goto ValidationChecker;
					}

				}else{
					$InvalidDataMessage="Please provide stop type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop type id


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
					$InvalidDataMessage="Please provide stop location id".$stop['stop_location_id'];
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop location id


					//----validdate stop mile
				if(isset($stop['stop_mile'])){
					$stop_mile=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_mile']);

					if(preg_match("/^[0-9]{1,}$/",$stop['stop_mile'])){
						$stop_item_senetized['stop_mile']=$stop_mile;
					}else{
						$InvalidDataMessage="Please provide valid stop mile";
						$dataValidation=false;
						goto ValidationChecker;								
					}

				}else{
					$InvalidDataMessage="Please provide stop mile";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate stop mile
				array_push($stops_array_senetized, $stop_item_senetized);
			}
		}

		if(count($stops_array_senetized)>=2){
			$total_miles=array_sum(array_column($stops_array_senetized,'stop_mile'));



		}else{
			$InvalidDataMessage="Please provide atleast two stop";
			$dataValidation=false;
			goto ValidationChecker;
		}

////---------//-validate stops



		$start_date=min(array_column($stops_array_senetized, 'stop_date'));
		$end_date=max(array_column($stops_array_senetized, 'stop_date'));



//-----check datetime clashing of truck with existiong records
//-----it will prevent the repeative use truck	

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `trips` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<='$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>='$end_date')) AND `trip_truck_id_fk`='$truck_id'  AND NOT `trip_id`='$update_id'");
		if(mysqli_num_rows($check_truck_clashing_q)>0){
			$InvalidDataMessage="Trip period clashing. Truck ID has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of truck with existiong records






			///-----------drivers section calcuations
		$driver_group_id=0;
		if(isset($param['driver_group_id'])){
			$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

			include_once APPROOT.'/models/masters/DriverGroups.php';
			$DriverGroups=new DriverGroups;

			if(!$DriverGroups->isValidId($driver_group_id)){
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide driver group id";
			$dataValidation=false;
			goto ValidationChecker;
		}

				//---driver A should is required in both the case so check if same is the passed;


//----------Calculate miles, basic earning & incentive for drivers;

		if($driver_group_id=='SOLO'){
			$driver_miles=floatval($total_miles);
			$driver_incentive=floatval($incentive_rate*$driver_miles);
		}elseif ($driver_group_id=='TEAM') {
			$driver_miles=floatval($total_miles)/2;
			$driver_incentive=round((floatval($incentive_rate*$driver_miles)),2);
		}



			///-----create array in of driver details for final insert in database
		$drivers_array=[];
		$salary_parameters_array=[];
		if(isset($param['driver_a'])){
			$driver_a_details=[];
					///---check if all the details of driver A ar send or not
			$driver_a=$param['driver_a'];
			if(isset($driver_a['id'])){
				$driver_a_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['id']);

				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;

				if(!$Drivers->isValidId($driver_a_details['id'])){
					$InvalidDataMessage="Invalid driver A";
					$dataValidation=false;
					goto ValidationChecker;
				}





//-----check datetime clashing of driver a with existiong records
//-----it will prevent the repeative use driver a		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
				$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`  FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<='$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>='$end_date')) AND`trip_driver_driver_id_fk`='".$driver_a_details['id']."' AND NOT `trip_id`='$update_id'");
				if(mysqli_num_rows($check_truck_clashing_q)>0){
					$InvalidDataMessage="Trip period clashing. Driver A has been already used for this period";
					$dataValidation=false;
					goto ValidationChecker;			
				}
//-----/check datetime clashing of driver a with existiong records








				$driver_a_details['miles']=$driver_miles;
				$driver_a_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
				$driver_a_details['incentive']=$driver_incentive;

				$driver_a_details['remarks']="";
				if(isset($driver_a['remarks'])){
					$driver_a_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['remarks']);
				}

				array_push($drivers_array, $driver_a_details);	

////-----------driver a salary parameters
				if(isset($param['driver_a']['salary_parameters'])){
					$salary_parameters_a=$param['driver_a']['salary_parameters'];
					foreach ($salary_parameters_a as $salary_parameters_a) {
						$salary_parameter=[];
						$salary_parameter['driver_id']=$driver_a['id'];
						if(isset($salary_parameters_a['parameter_id'])){
							$parameter_id_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_id']);
						//---get parameter details
							$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_a'");
							if(mysqli_num_rows($pm_d_q)==1){
								$pm_d_result=mysqli_fetch_assoc($pm_d_q);
								$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
							}else{
								$InvalidDataMessage="Invalid salary parameter id";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($salary_parameters_a['parameter_amount'])){
							$parameter_amount_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_amount']);
						//---get parameter details
							if(is_numeric($parameter_amount_a)){
								$salary_parameter['parameter_amount']=round($parameter_amount_a,2);
							}else{
								$InvalidDataMessage="Invalid salary parameter amount";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}
						array_push($salary_parameters_array, $salary_parameter);
					}
				}
////-----------//driver a salary parameters

			}else{
				$InvalidDataMessage="One or more field of driver a ar missing";
				$dataValidation=false;
				goto ValidationChecker;						
			}



		}else{
			$InvalidDataMessage="Please provide driver A details";
			$dataValidation=false;
			goto ValidationChecker;
		}

			//--if the group id is 2 (Team) than record of second driver is also required
		if($driver_group_id=='TEAM'){
			if(isset($param['driver_b'])){


					///---check if all the details of driver A ar send or not
				$driver_b=$param['driver_b'];
				if(isset($driver_b['id'])){

					$driver_b_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['id']);

					include_once APPROOT.'/models/masters/Drivers.php';
					$Drivers=new Drivers;

					if(!$Drivers->isValidId($driver_b_details['id'])){
						$InvalidDataMessage="Invalid driver B";
						$dataValidation=false;
						goto ValidationChecker;
					}




//-----check datetime clashing of driver b with existiong records
//-----it will prevent the repeative use driver b		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
					$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`  FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND`trip_driver_driver_id_fk`='".$driver_b_details['id']."'");
					if(mysqli_num_rows($check_truck_clashing_q)>0){
						$InvalidDataMessage="Trip period clashing. Driver B has been already used for this period";
						$dataValidation=false;
						goto ValidationChecker;			
					}
//-----/check datetime clashing of driver b with existiong records






					$driver_b_details['miles']=$driver_miles;
					$driver_b_details['pay_per_mile']=$pay_per_mile;
					$driver_b_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
					$driver_b_details['incentive']=$driver_incentive;
					$driver_b_details['remarks']="";
					if(isset($driver_b['remarks'])){
						$driver_b_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['remarks']);
					}						
					array_push($drivers_array, $driver_b_details);


////-----------driver  b salary parameters
					if(isset($param['driver_b']['salary_parameters'])){
						$salary_parameters_b=$param['driver_b']['salary_parameters'];
						foreach ($salary_parameters_b as $salary_parameters_b) {
							$salary_parameter=[];
							$salary_parameter['driver_id']=$driver_b['id'];
						//--validate stop date
							if(isset($salary_parameters_b['parameter_id'])){
								$parameter_id_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_id']);
						//---get parameter details
								$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_b'");
								if(mysqli_num_rows($pm_d_q)==1){
									$pm_d_result=mysqli_fetch_assoc($pm_d_q);
									$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
								}else{
									$InvalidDataMessage="Invalid salary parameter id";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}


							if(isset($salary_parameters_b['parameter_amount'])){
								$parameter_amount_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_amount']);
						//---get parameter details
								if(is_numeric($parameter_amount_b)){
									$salary_parameter['parameter_amount']=$parameter_amount_b;
								}else{
									$InvalidDataMessage="Invalid salary parameter amount";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}
							array_push($salary_parameters_array, $salary_parameter);
						}

					}
////-----------//driver b salary parameters





				}else{
					$InvalidDataMessage="One or more field of driver B are missing";
					$dataValidation=false;
					goto ValidationChecker;						
				}

			}else{
				$InvalidDataMessage="Please provide driver B details";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}
			///-----------//drivers section calcuations


			//-----data validation ends
		ValidationChecker:
		if($dataValidation){
					//$message="validation is ok, You may proceed";

					///------------Add new trip
			$total_incentive=$incentive_rate*$total_miles;





			$updateTrip=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_truck_id_fk`='$truck_id', `trip_driver_group_id_fk`='$driver_group_id',`trip_total_miles`='$total_miles',`trip_ppm_plan_group_id`='$ppm_plan_id',`trip_ppm`='$pay_per_mile',`trip_incentive_per_mile`='$incentive_rate',`trip_incentive`='$total_incentive',`trip_start_date`='$start_date', `trip_end_date`='$end_date',`trip_approval_status_id_fk`='PENDING',`trip_updated_on`='$time', `trip_updated_by`='$USERID' WHERE `trip_id`='$update_id'");
					//-----------add new trip	
			echo mysqli_error($GLOBALS['con']);
			if($updateTrip){



//-------Delete old saved Stops,Salary paramters, drivers
				mysqli_query($GLOBALS['con'],"DELETE FROM `driver_payments` WHERE `payment_category`='TRIP-EARNINGS' AND`payment_pay_status`='UNPAID' AND `payment_trip_id_fk`='$update_id'");
				mysqli_query($GLOBALS['con'],"DELETE FROM `trip_drivers` WHERE `trip_driver_trip_id_fk`='$update_id'");
				mysqli_query($GLOBALS['con'],"DELETE FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$update_id'");
				mysqli_query($GLOBALS['con'],"DELETE FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_id_fk`='$update_id'");







					///---------insert stops
				$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

				$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
				$stop_inserted=true;
				foreach ($stops_array_senetized as $stop_row) {
					$next_stop_id++;
					$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$update_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
					if(!$insertStop){
						$stop_inserted=false;
					}
				}
					///---------//insert stops






					///---------insert salary parameters
				$last_trip_salary_parameter_id=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

				$next_trip_salary_parameter_id=(mysqli_num_rows($last_trip_salary_parameter_id)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id)['trip_salary_parameter_id']:1000000;

					///-----//Generate New Unique Id
				$trip_salary_parameter_inserted=true;
				foreach ($salary_parameters_array as $sa_pr) {
					$next_trip_salary_parameter_id++;
					$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$update_id','".$sa_pr['driver_id']."','".$sa_pr['parameter_id']."','".$sa_pr['parameter_amount']."','ACT')");
					if(!$insert_trip_salary_parameter){
						$trip_salary_parameter_inserted=false;
					}
				}
					///---------//insert salary parameters




					///---------insert trip drivers
				$get_trip_driver_id=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

				$next_trip_driver_id=(mysqli_num_rows($get_trip_driver_id)==1)?mysqli_fetch_assoc($get_trip_driver_id)['trip_driver_id']:1000000;

					///-----//Generate New Unique Id
				$driver_inserted=true;
				foreach ($drivers_array as $dA) {
					$next_trip_driver_id++;


					$net_earnings=0;
					$net_earnings+=floatval($dA['basic_earnings']);				

					$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$update_id ,'driver_id'=>$dA['id'] ));
					$net_earnings=$net_earnings+(floatval($salary_parameters['net_impact']));




					$insertDrivers=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers`( `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_status`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`,`trip_driver_net_earnings`,`trip_driver_incentives_status`,`trip_driver_remarks`) VALUES ('$next_trip_driver_id','$update_id','".$dA['id']."','ACT','".$dA['miles']."','$pay_per_mile','".$dA['basic_earnings']."','".$dA['incentive']."','$net_earnings','HOLD','".$dA['remarks']."')");
					if(!$insertDrivers){
						$driver_inserted=false;
					}
				}
					///---------//insert trip drivers

				if($driver_inserted && $stop_inserted && $trip_salary_parameter_inserted){
					$message="Trip updated successfuly";
					$status=true;
				}else{
					$message=SOMETHING_WENT_WROG.$messageDumyA;
				}

			}else{
				$message=SOMETHING_WENT_WROG;
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
*/


function trips_update($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0121', USER_PRIV)){

			//$message=REQUIRE_NECESSARY_FIELDS;
		$USERID=USER_ID;
		$time=time();


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		if(isset($param['update_eid'])){
			$update_id=$Enc->safeurlde($param['update_eid']);

				//------check if all payments of trip are unpaid

			$get_trip_detail_q=mysqli_query($GLOBALS['con'],"SELECT trip_detail_id FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('PENDING','CANCELLED','REJECTED') AND `trip_detail_status`='ACT' AND `trip_id`='$update_id'");
			if(mysqli_num_rows($get_trip_detail_q)==1){
				$trip_detail_id=mysqli_fetch_assoc($get_trip_detail_q)['trip_detail_id'];
			}else{
				$InvalidDataMessage="Approved trip can't be updated";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide trip eid";
			$dataValidation=false;
			goto ValidationChecker;
		}
		if(isset($param['truck_id'])){
			$truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['truck_id']);

			include_once APPROOT.'/models/masters/Trucks.php';
			$Trucks=new Trucks;

			if(!$Trucks->isValidId($truck_id)){
				$InvalidDataMessage="Invalid truck value";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide truck id";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['ppm_plan_id'])){
			$ppm_plan_id=mysqli_real_escape_string($GLOBALS['con'],$param['ppm_plan_id']);

			include_once APPROOT.'/models/masters/DriverPpmPlans.php';
			$DriverPpmPlans=new DriverPpmPlans;

			if(!$DriverPpmPlans->isValidId($ppm_plan_id)){
				$InvalidDataMessage="Invalid ppm plan id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide ppm plan id";
			$dataValidation=false;
			goto ValidationChecker;
		}			



		if(isset($param['pay_per_mile'])){
			$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);
			if(!preg_match("/^[0-9.]{1,}$/",$pay_per_mile)){
				$InvalidDataMessage="Please provide valid pay per mile";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide pay per mile";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['incentive_rate'])){
			$incentive_rate=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_rate']);
			if(!preg_match("/^[0-9.]{1,}$/",$incentive_rate)){
				$InvalidDataMessage="Please provide valid incentive rate";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide valid incentive rate";
			$dataValidation=false;
			goto ValidationChecker;
		}





////----------validate stops

		if(isset($param['stops'])){
			$stops=$param['stops'];

			$stops_array_senetized=[];
			foreach ($stops as $stop) {
				$stop_item_senetized=[];
						//--validate stop date
				if(isset($stop['stop_date'])){
					if(isValidDateFormat($stop['stop_date'])){
						$stop_date=date('Y-m-d', strtotime($stop['stop_date']));
						$stop_item_senetized['stop_date']=$stop_date;

							///---------restrict the future date selection
						if($stop_date>date('Y-m-d')){
							$InvalidDataMessage="Futute date not allowed in any stop";
							$dataValidation=false;
							goto ValidationChecker;									
						}
							///---------/restrict the future date selection

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


				///-----check if all the stop dates are in ascending order
				if(!isset($set_date)){
					$set_date=$stop_date;
				}else{
						//--check if the new date is greater or equal to the old set date
						//--if greater than setdate equal to new date for next time check
						//--else throw error
					if($stop_date<$set_date){
						$InvalidDataMessage="Please provide valid stop date order";
						$dataValidation=false;
						goto ValidationChecker;	
					}
					$set_date=$stop_date;
				}

						//----validate stop type id
				if(isset($stop['stop_type_id'])){
					$stop_type_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_type_id']);

					include_once APPROOT.'/models/masters/TripStopTypes.php';
					$TripStopTypes=new TripStopTypes;

					if($TripStopTypes->isValidId($stop_type_id)){
						$stop_item_senetized['stop_type_id']=$stop_type_id;

					}else{
						$InvalidDataMessage="Invalid stop type value";
						$dataValidation=false;
						goto ValidationChecker;
					}

				}else{
					$InvalidDataMessage="Please provide stop type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop type id


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
					$InvalidDataMessage="Please provide stop location id".$stop['stop_location_id'];
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop location id


					//----validdate stop mile
				if(isset($stop['stop_mile'])){
					$stop_mile=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_mile']);

					if(preg_match("/^[0-9]{1,}$/",$stop['stop_mile'])){
						$stop_item_senetized['stop_mile']=$stop_mile;
					}else{
						$InvalidDataMessage="Please provide valid stop mile";
						$dataValidation=false;
						goto ValidationChecker;								
					}

				}else{
					$InvalidDataMessage="Please provide stop mile";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate stop mile
				array_push($stops_array_senetized, $stop_item_senetized);
			}
		}

		if(count($stops_array_senetized)>=2){
			$total_miles=array_sum(array_column($stops_array_senetized,'stop_mile'));



		}else{
			$InvalidDataMessage="Please provide atleast two stop";
			$dataValidation=false;
			goto ValidationChecker;
		}

////---------//-validate stops



		$start_date=min(array_column($stops_array_senetized, 'stop_date'));
		$end_date=max(array_column($stops_array_senetized, 'stop_date'));



//-----check datetime clashing of truck with existiong records
//-----it will prevent the repeative use truck	

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod

		if($this->are_truck_dates_clashing(array('truck_id'=>$truck_id,'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Truck ID has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of truck with existiong records






			///-----------drivers section calcuations
		$driver_group_id=0;
		if(isset($param['driver_group_id'])){
			$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

			include_once APPROOT.'/models/masters/DriverGroups.php';
			$DriverGroups=new DriverGroups;

			if(!$DriverGroups->isValidId($driver_group_id)){
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide driver group id";
			$dataValidation=false;
			goto ValidationChecker;
		}

				//---driver A should is required in both the case so check if same is the passed;


//----------Calculate miles, basic earning & incentive for drivers;

		if($driver_group_id=='SOLO'){
			$driver_miles=floatval($total_miles);
			$driver_incentive=floatval($incentive_rate*$driver_miles);
		}elseif ($driver_group_id=='TEAM') {
			$driver_miles=floatval($total_miles)/2;
			$driver_incentive=round((floatval($incentive_rate*$driver_miles)),2);
		}



			///-----create array in of driver details for final insert in database
		$drivers_array=[];
		$salary_parameters_array=[];
		if(isset($param['driver_a'])){
			$driver_a_details=[];
					///---check if all the details of driver A ar send or not
			$driver_a=$param['driver_a'];
			if(isset($driver_a['id'])){
				$driver_a_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['id']);

				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;

				if(!$Drivers->isValidId($driver_a_details['id'])){
					$InvalidDataMessage="Invalid driver A";
					$dataValidation=false;
					goto ValidationChecker;
				}





//-----check datetime clashing of driver a with existiong records
//-----it will prevent the repeative use driver a		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_a_details['id'],'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Driver A has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver a with existiong records








				$driver_a_details['miles']=$driver_miles;
				$driver_a_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
				$driver_a_details['incentive']=$driver_incentive;

				$driver_a_details['remarks']="";
				if(isset($driver_a['remarks'])){
					$driver_a_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['remarks']);
				}

				array_push($drivers_array, $driver_a_details);	

////-----------driver a salary parameters
				if(isset($param['driver_a']['salary_parameters'])){
					$salary_parameters_a=$param['driver_a']['salary_parameters'];
					foreach ($salary_parameters_a as $salary_parameters_a) {
						$salary_parameter=[];
						$salary_parameter['driver_id']=$driver_a['id'];
						if(isset($salary_parameters_a['parameter_id'])){
							$parameter_id_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_id']);
						//---get parameter details
							$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_a'");
							if(mysqli_num_rows($pm_d_q)==1){
								$pm_d_result=mysqli_fetch_assoc($pm_d_q);
								$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
							}else{
								$InvalidDataMessage="Invalid salary parameter id";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($salary_parameters_a['parameter_amount'])){
							$parameter_amount_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_amount']);
						//---get parameter details
							if(is_numeric($parameter_amount_a)){
								$salary_parameter['parameter_amount']=round($parameter_amount_a,2);
							}else{
								$InvalidDataMessage="Invalid salary parameter amount";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}
						array_push($salary_parameters_array, $salary_parameter);
					}
				}
////-----------//driver a salary parameters

			}else{
				$InvalidDataMessage="One or more field of driver a ar missing";
				$dataValidation=false;
				goto ValidationChecker;						
			}



		}else{
			$InvalidDataMessage="Please provide driver A details";
			$dataValidation=false;
			goto ValidationChecker;
		}

			//--if the group id is 2 (Team) than record of second driver is also required
		if($driver_group_id=='TEAM'){
			if(isset($param['driver_b'])){


					///---check if all the details of driver A ar send or not
				$driver_b=$param['driver_b'];
				if(isset($driver_b['id'])){

					$driver_b_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['id']);

					include_once APPROOT.'/models/masters/Drivers.php';
					$Drivers=new Drivers;

					if(!$Drivers->isValidId($driver_b_details['id'])){
						$InvalidDataMessage="Invalid driver B";
						$dataValidation=false;
						goto ValidationChecker;
					}




//-----check datetime clashing of driver b with existiong records
//-----it will prevent the repeative use driver b		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_b_details['id'],'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Driver B has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver b with existiong records






					$driver_b_details['miles']=$driver_miles;
					$driver_b_details['pay_per_mile']=$pay_per_mile;
					$driver_b_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
					$driver_b_details['incentive']=$driver_incentive;
					$driver_b_details['remarks']="";
					if(isset($driver_b['remarks'])){
						$driver_b_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['remarks']);
					}						
					array_push($drivers_array, $driver_b_details);


////-----------driver  b salary parameters
					if(isset($param['driver_b']['salary_parameters'])){
						$salary_parameters_b=$param['driver_b']['salary_parameters'];
						foreach ($salary_parameters_b as $salary_parameters_b) {
							$salary_parameter=[];
							$salary_parameter['driver_id']=$driver_b['id'];
						//--validate stop date
							if(isset($salary_parameters_b['parameter_id'])){
								$parameter_id_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_id']);
						//---get parameter details
								$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_b'");
								if(mysqli_num_rows($pm_d_q)==1){
									$pm_d_result=mysqli_fetch_assoc($pm_d_q);
									$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
								}else{
									$InvalidDataMessage="Invalid salary parameter id";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}


							if(isset($salary_parameters_b['parameter_amount'])){
								$parameter_amount_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_amount']);
						//---get parameter details
								if(is_numeric($parameter_amount_b)){
									$salary_parameter['parameter_amount']=$parameter_amount_b;
								}else{
									$InvalidDataMessage="Invalid salary parameter amount";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}
							array_push($salary_parameters_array, $salary_parameter);
						}

					}
////-----------//driver b salary parameters





				}else{
					$InvalidDataMessage="One or more field of driver B are missing";
					$dataValidation=false;
					goto ValidationChecker;						
				}

			}else{
				$InvalidDataMessage="Please provide driver B details";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}
			///-----------//drivers section calcuations


			//-----data validation ends
		ValidationChecker:
		if($dataValidation){
					//$message="validation is ok, You may proceed";

				///-----Generate New Unique Id


			$executionMessage="";
			$execution=true;	


					///------------Add new trip
			$total_incentive=$incentive_rate*$total_miles;
			$updateTripStatus=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='PENDING',`trip_updated_on`='$time', `trip_updated_by`='$USERID' WHERE `trip_id`='$update_id'");

			if(!$updateTripStatus){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;			
			}

			$old_trip_detail_id=$trip_detail_id;


			//---inactivate old detail id
			$inactivate_trip_detail_id=mysqli_query($GLOBALS['con'],"UPDATE `trip_details` SET `trip_detail_status`='DEL',`trip_detail_deleted_on`='$time',`trip_detail_deleted_by`='$USERID' WHERE `trip_detail_id`='$old_trip_detail_id'");
			if(!$inactivate_trip_detail_id){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;	
			}

			//---/inactivate old detail id


//---insert new trip detail id
			$last_trip_detail_id=mysqli_query($GLOBALS['con'],"SELECT `trip_detail_id` FROM `trip_details` ORDER BY `auto` DESC LIMIT 1");
			if(mysqli_num_rows($last_trip_detail_id)>0){
				$last_trip_detail_id_b=mysqli_fetch_assoc($last_trip_detail_id)['trip_detail_id'];
				if($last_trip_detail_id_b==""){
					$new_trip_detail_id=10001;
				}else{
					$new_trip_detail_id=$last_trip_detail_id_b+1;
				}
			}else{
				$new_trip_detail_id=10001;
			}


			$insert_trip_detials=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_details`(`trip_detail_id`, `trip_id_fk`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`, `trip_detail_status`, `trip_detail_added_on`, `trip_detail_added_by`) VALUES ('$new_trip_detail_id','$update_id','$truck_id','$driver_group_id','$total_miles','$ppm_plan_id','$pay_per_mile','$incentive_rate','$total_incentive','$start_date','$end_date','ACT','$time','$USERID')");


			if(!$insert_trip_detials){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;	
			}




//-------update trip stops

//-------dump trip stop records to trip stops logs table
			$dump_trip_stop_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO trip_stops_logs (`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`)SELECT `trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status` FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$old_trip_detail_id'");
			if(!$dump_trip_stop_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 04'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip stop records to trip stops logs table


//-------insert stops
			$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

			$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
			$stop_inserted=true;
			foreach ($stops_array_senetized as $stop_row) {
				$next_stop_id++;
				$insert_new_stops=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$new_trip_detail_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
				if(!$insert_new_stops){
					$executionMessage=SOMETHING_WENT_WROG.' step 05'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert stops




//-------delete old stops records from live table
			$last_stop_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

			$last_stop_id_not_to_delete=(mysqli_num_rows($last_stop_id_not_to_delete)==1)?mysqli_fetch_assoc($last_stop_id_not_to_delete)['trip_stop_id']:1000000;
			$delete_old_stops=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$old_trip_detail_id' AND NOT `trip_stop_id`='$last_stop_id_not_to_delete'");
			if(!$dump_trip_stop_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 06'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old stops records from live table

//-------/update trip stops





//-------update trip salary parameters

//-------dump trip salary parameters records to trip salary parameters logs table
			$dump_trip_salary_parameters_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO trip_salary_parameters_logs ( `trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`, `trip_salary_parameter_status`, `trip_salary_parameter_added_by`)SELECT `trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`, `trip_salary_parameter_status`, `trip_salary_parameter_added_by` FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_detail_id_fk`='$old_trip_detail_id'");
			if(!$dump_trip_salary_parameters_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 07'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip salary parameters records to trip salary parameters logs table
//-------insert salary parameters new records 
			$last_trip_salary_parameter_id=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

			$next_trip_salary_parameter_id=(mysqli_num_rows($last_trip_salary_parameter_id)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id)['trip_salary_parameter_id']:1000000;

					///-----//Generate New Unique Id
			$trip_salary_parameter_inserted=true;
			foreach ($salary_parameters_array as $sa_pr) {
				$next_trip_salary_parameter_id++;
				$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$new_trip_detail_id','".$sa_pr['driver_id']."','".$sa_pr['parameter_id']."','".$sa_pr['parameter_amount']."','ACT')");
				if(!$insert_trip_salary_parameter){
					$executionMessage=SOMETHING_WENT_WROG.' step 08'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert salary parameters new records 


//-------delete old salary parameters records from live table

			$last_trip_salary_parameter_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

			$last_trip_salary_parameter_id_not_to_delete=(mysqli_num_rows($last_trip_salary_parameter_id_not_to_delete)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id_not_to_delete)['trip_salary_parameter_id']:1000000;

			$delete_old_salary_parameters=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_detail_id_fk`='$old_trip_detail_id' AND NOT `trip_salary_parameter_id`='$last_trip_salary_parameter_id_not_to_delete'");
			if(!$delete_old_salary_parameters){
				$executionMessage=SOMETHING_WENT_WROG.' step 09'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old salary parameters records from live table

//-------/update trip salary parameters





//-------update trip driver records

//-------dump trip drivers records to trip drivers logs table
			$dump_trip_driver_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers_logs`(`trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_incentives_status`, `trip_driver_incentives_moved_on`, `trip_driver_incentives_moved_by`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks`, `trip_driver_status`, `trip_driver_remarks`) SELECT `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_incentives_status`, `trip_driver_incentives_moved_on`, `trip_driver_incentives_moved_by`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks`, `trip_driver_status`, `trip_driver_remarks` FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`='$old_trip_detail_id'");
			if(!$dump_trip_driver_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 10'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip drivers records to trip drivers logs table



//-------insert trip drivers new records
			$get_trip_driver_id=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

			$next_trip_driver_id=(mysqli_num_rows($get_trip_driver_id)==1)?mysqli_fetch_assoc($get_trip_driver_id)['trip_driver_id']:1000000;

					///-----//Generate New Unique Id
			$driver_inserted=true;
			foreach ($drivers_array as $dA) {
				$next_trip_driver_id++;


				$net_earnings=0;
				$net_earnings+=floatval($dA['basic_earnings']);				

				$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$new_trip_detail_id ,'driver_id'=>$dA['id'] ));
				$net_earnings=$net_earnings+(floatval($salary_parameters['net_impact']));




				$insertDrivers=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers`( `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_status`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`,`trip_driver_net_earnings`,`trip_driver_incentives_status`,`trip_driver_remarks`) VALUES ('$next_trip_driver_id','$new_trip_detail_id','".$dA['id']."','ACT','".$dA['miles']."','$pay_per_mile','".$dA['basic_earnings']."','".$dA['incentive']."','$net_earnings','HOLD','".$dA['remarks']."')");
				if(!$insertDrivers){
					$executionMessage=SOMETHING_WENT_WROG.' step 11'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert trip drivers new records


//-------delete old driver records from live table
			$get_trip_driver_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

			$get_trip_driver_id_not_to_delete=(mysqli_num_rows($get_trip_driver_id_not_to_delete)==1)?mysqli_fetch_assoc($get_trip_driver_id_not_to_delete)['trip_driver_id']:1000000;
			$delete_old_trip_drivers=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`='$old_trip_detail_id' AND NOT `trip_driver_id`='$get_trip_driver_id_not_to_delete'");
			if(!$delete_old_trip_drivers){
				$executionMessage=SOMETHING_WENT_WROG.' step 12'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old driver records from live table



//-------/update trip driver records

			executionChecker:
			if($execution){
				$message="Trip updated successfuly";
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
	return $r;

}

function are_truck_dates_clashing($param){
	$start_date=$param['start_date'];
	$end_date=$param['end_date'];

	$q="SELECT `trip_id` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date') OR (`trip_start_date`>='$start_date' AND `trip_end_date`<='$end_date' AND NOT '$start_date'='$end_date')) AND `trip_truck_id_fk`='".$param['truck_id']."'";
	if(isset($param['trip_detail_id'])){
		$q.=" AND NOT `trip_detail_id`='".$param['trip_detail_id']."'";
	}
	$qEx=mysqli_query($GLOBALS['con'],$q);
	if(mysqli_num_rows($qEx)>0){
		return true;
	}else{
		return false;
	}
}


function are_driver_dates_clashing($param){
	$start_date=$param['start_date'];
	$end_date=$param['end_date'];

	$q="SELECT `trip_driver_id` FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date') OR (`trip_start_date`>='$start_date' AND `trip_end_date`<='$end_date' AND NOT '$start_date'='$end_date')) AND`trip_driver_driver_id_fk`='".$param['driver_id']."'";
	if(isset($param['trip_detail_id'])){
		$q.=" AND NOT `trip_driver_trip_detail_id_fk`='".$param['trip_detail_id']."'";
	}
	$qEx=mysqli_query($GLOBALS['con'],$q);
	if(mysqli_num_rows($qEx)>0){
		return true;
	}else{
		return false;
	}
}


function trips_resettle($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0149', USER_PRIV)){

		$USERID=USER_ID;
		$time=time();


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		if(isset($param['update_eid'])){
			$update_id=$Enc->safeurlde($param['update_eid']);

				//------check if all payments of trip are unpaid

			$get_trip_detail_q=mysqli_query($GLOBALS['con'],"SELECT trip_detail_id FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_detail_status`='ACT' AND `trip_id`='$update_id'");
			if(mysqli_num_rows($get_trip_detail_q)==1){
				$trip_detail_id=mysqli_fetch_assoc($get_trip_detail_q)['trip_detail_id'];
				if($this->is_paid($trip_detail_id)==false){
					$InvalidDataMessage="This trip can't be resettled ";
					$dataValidation=false;
					goto ValidationChecker;					
				}
			}else{
				$InvalidDataMessage="This trip can't be resettled";
				$dataValidation=false;
				goto ValidationChecker;
			}


		}else{
			$InvalidDataMessage="Please provide trip eid";
			$dataValidation=false;
			goto ValidationChecker;
		}
		if(isset($param['truck_id'])){
			$truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['truck_id']);

			include_once APPROOT.'/models/masters/Trucks.php';
			$Trucks=new Trucks;

			if(!$Trucks->isValidId($truck_id)){
				$InvalidDataMessage="Invalid truck value";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide truck id";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['ppm_plan_id'])){
			$ppm_plan_id=mysqli_real_escape_string($GLOBALS['con'],$param['ppm_plan_id']);

			include_once APPROOT.'/models/masters/DriverPpmPlans.php';
			$DriverPpmPlans=new DriverPpmPlans;

			if(!$DriverPpmPlans->isValidId($ppm_plan_id)){
				$InvalidDataMessage="Invalid ppm plan id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide ppm plan id";
			$dataValidation=false;
			goto ValidationChecker;
		}			



		if(isset($param['pay_per_mile'])){
			$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);
			if(!preg_match("/^[0-9.]{1,}$/",$pay_per_mile)){
				$InvalidDataMessage="Please provide valid pay per mile";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide pay per mile";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['incentive_rate'])){
			$incentive_rate=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_rate']);
			if(!preg_match("/^[0-9.]{1,}$/",$incentive_rate)){
				$InvalidDataMessage="Please provide valid incentive rate";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide valid incentive rate";
			$dataValidation=false;
			goto ValidationChecker;
		}





////----------validate stops

		if(isset($param['stops'])){
			$stops=$param['stops'];

			$stops_array_senetized=[];
			foreach ($stops as $stop) {
				$stop_item_senetized=[];
						//--validate stop date
				if(isset($stop['stop_date'])){
					if(isValidDateFormat($stop['stop_date'])){
						$stop_date=date('Y-m-d', strtotime($stop['stop_date']));
						$stop_item_senetized['stop_date']=$stop_date;

							///---------restrict the future date selection
						if($stop_date>date('Y-m-d')){
							$InvalidDataMessage="Futute date not allowed in any stop";
							$dataValidation=false;
							goto ValidationChecker;									
						}
							///---------/restrict the future date selection

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


				///-----check if all the stop dates are in ascending order
				if(!isset($set_date)){
					$set_date=$stop_date;
				}else{
						//--check if the new date is greater or equal to the old set date
						//--if greater than setdate equal to new date for next time check
						//--else throw error
					if($stop_date<$set_date){
						$InvalidDataMessage="Please provide valid stop date order";
						$dataValidation=false;
						goto ValidationChecker;	
					}
					$set_date=$stop_date;
				}

						//----validate stop type id
				if(isset($stop['stop_type_id'])){
					$stop_type_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_type_id']);

					include_once APPROOT.'/models/masters/TripStopTypes.php';
					$TripStopTypes=new TripStopTypes;

					if($TripStopTypes->isValidId($stop_type_id)){
						$stop_item_senetized['stop_type_id']=$stop_type_id;

					}else{
						$InvalidDataMessage="Invalid stop type value";
						$dataValidation=false;
						goto ValidationChecker;
					}

				}else{
					$InvalidDataMessage="Please provide stop type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop type id


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
					$InvalidDataMessage="Please provide stop location id".$stop['stop_location_id'];
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop location id


					//----validdate stop mile
				if(isset($stop['stop_mile'])){
					$stop_mile=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_mile']);

					if(preg_match("/^[0-9]{1,}$/",$stop['stop_mile'])){
						$stop_item_senetized['stop_mile']=$stop_mile;
					}else{
						$InvalidDataMessage="Please provide valid stop mile";
						$dataValidation=false;
						goto ValidationChecker;								
					}

				}else{
					$InvalidDataMessage="Please provide stop mile";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate stop mile
				array_push($stops_array_senetized, $stop_item_senetized);
			}
		}

		if(count($stops_array_senetized)>=2){
			$total_miles=array_sum(array_column($stops_array_senetized,'stop_mile'));



		}else{
			$InvalidDataMessage="Please provide atleast two stop";
			$dataValidation=false;
			goto ValidationChecker;
		}

////---------//-validate stops



		$start_date=min(array_column($stops_array_senetized, 'stop_date'));
		$end_date=max(array_column($stops_array_senetized, 'stop_date'));



//-----check datetime clashing of truck with existiong records
//-----it will prevent the repeative use truck	

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		
		if($this->are_truck_dates_clashing(array('truck_id'=>$truck_id,'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Truck ID has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of truck with existiong records






			///-----------drivers section calcuations
		$driver_group_id=0;
		if(isset($param['driver_group_id'])){
			$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

			include_once APPROOT.'/models/masters/DriverGroups.php';
			$DriverGroups=new DriverGroups;

			if(!$DriverGroups->isValidId($driver_group_id)){
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide driver group id";
			$dataValidation=false;
			goto ValidationChecker;
		}

				//---driver A should is required in both the case so check if same is the passed;


//----------Calculate miles, basic earning & incentive for drivers;

		if($driver_group_id=='SOLO'){
			$driver_miles=floatval($total_miles);
			$driver_incentive=floatval($incentive_rate*$driver_miles);
		}elseif ($driver_group_id=='TEAM') {
			$driver_miles=floatval($total_miles)/2;
			$driver_incentive=round((floatval($incentive_rate*$driver_miles)),2);
		}



			///-----create array in of driver details for final insert in database
		$drivers_array=[];
		$salary_parameters_array=[];
		if(isset($param['driver_a'])){
			$driver_a_details=[];
					///---check if all the details of driver A ar send or not
			$driver_a=$param['driver_a'];
			if(isset($driver_a['id'])){
				$driver_a_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['id']);

				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;

				if(!$Drivers->isValidId($driver_a_details['id'])){
					$InvalidDataMessage="Invalid driver A";
					$dataValidation=false;
					goto ValidationChecker;
				}





//-----check datetime clashing of driver a with existiong records
//-----it will prevent the repeative use driver a		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_a_details['id'],'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Driver A has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver a with existiong records








				$driver_a_details['miles']=$driver_miles;
				$driver_a_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
				$driver_a_details['incentive']=$driver_incentive;

				$driver_a_details['remarks']="";
				if(isset($driver_a['remarks'])){
					$driver_a_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['remarks']);
				}

				array_push($drivers_array, $driver_a_details);	

////-----------driver a salary parameters
				if(isset($param['driver_a']['salary_parameters'])){
					$salary_parameters_a=$param['driver_a']['salary_parameters'];
					foreach ($salary_parameters_a as $salary_parameters_a) {
						$salary_parameter=[];
						$salary_parameter['driver_id']=$driver_a['id'];
						if(isset($salary_parameters_a['parameter_id'])){
							$parameter_id_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_id']);
						//---get parameter details
							$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_a'");
							if(mysqli_num_rows($pm_d_q)==1){
								$pm_d_result=mysqli_fetch_assoc($pm_d_q);
								$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
							}else{
								$InvalidDataMessage="Invalid salary parameter id";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($salary_parameters_a['parameter_amount'])){
							$parameter_amount_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_amount']);
						//---get parameter details
							if(is_numeric($parameter_amount_a)){
								$salary_parameter['parameter_amount']=round($parameter_amount_a,2);
							}else{
								$InvalidDataMessage="Invalid salary parameter amount";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}
						array_push($salary_parameters_array, $salary_parameter);
					}
				}
////-----------//driver a salary parameters

			}else{
				$InvalidDataMessage="One or more field of driver a ar missing";
				$dataValidation=false;
				goto ValidationChecker;						
			}



		}else{
			$InvalidDataMessage="Please provide driver A details";
			$dataValidation=false;
			goto ValidationChecker;
		}

			//--if the group id is 2 (Team) than record of second driver is also required
		if($driver_group_id=='TEAM'){
			if(isset($param['driver_b'])){


					///---check if all the details of driver A ar send or not
				$driver_b=$param['driver_b'];
				if(isset($driver_b['id'])){

					$driver_b_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['id']);

					include_once APPROOT.'/models/masters/Drivers.php';
					$Drivers=new Drivers;

					if(!$Drivers->isValidId($driver_b_details['id'])){
						$InvalidDataMessage="Invalid driver B";
						$dataValidation=false;
						goto ValidationChecker;
					}




//-----check datetime clashing of driver b with existiong records
//-----it will prevent the repeative use driver b		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_b_details['id'],'start_date'=>$start_date,'end_date'=>$end_date,'trip_detail_id'=>$trip_detail_id))){
			$InvalidDataMessage="Trip period clashing. Driver B has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver b with existiong records






					$driver_b_details['miles']=$driver_miles;
					$driver_b_details['pay_per_mile']=$pay_per_mile;
					$driver_b_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
					$driver_b_details['incentive']=$driver_incentive;
					$driver_b_details['remarks']="";
					if(isset($driver_b['remarks'])){
						$driver_b_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['remarks']);
					}						
					array_push($drivers_array, $driver_b_details);


////-----------driver  b salary parameters
					if(isset($param['driver_b']['salary_parameters'])){
						$salary_parameters_b=$param['driver_b']['salary_parameters'];
						foreach ($salary_parameters_b as $salary_parameters_b) {
							$salary_parameter=[];
							$salary_parameter['driver_id']=$driver_b['id'];
						//--validate stop date
							if(isset($salary_parameters_b['parameter_id'])){
								$parameter_id_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_id']);
						//---get parameter details
								$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_b'");
								if(mysqli_num_rows($pm_d_q)==1){
									$pm_d_result=mysqli_fetch_assoc($pm_d_q);
									$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
								}else{
									$InvalidDataMessage="Invalid salary parameter id";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}


							if(isset($salary_parameters_b['parameter_amount'])){
								$parameter_amount_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_amount']);
						//---get parameter details
								if(is_numeric($parameter_amount_b)){
									$salary_parameter['parameter_amount']=$parameter_amount_b;
								}else{
									$InvalidDataMessage="Invalid salary parameter amount";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}
							array_push($salary_parameters_array, $salary_parameter);
						}

					}
////-----------//driver b salary parameters





				}else{
					$InvalidDataMessage="One or more field of driver B are missing";
					$dataValidation=false;
					goto ValidationChecker;						
				}

			}else{
				$InvalidDataMessage="Please provide driver B details";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}
			///-----------//drivers section calcuations


			//-----data validation ends
		ValidationChecker:
		if($dataValidation){
					//$message="validation is ok, You may proceed";

				///-----Generate New Unique Id


			$executionMessage="";
			$execution=true;	


					///------------Add new trip
			$total_incentive=$incentive_rate*$total_miles;
			$updateTripStatus=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_updated_on`='$time', `trip_updated_by`='$USERID' WHERE `trip_id`='$update_id'");

			if(!$updateTripStatus){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;			
			}

			$old_trip_detail_id=$trip_detail_id;


			//---inactivate old detail id
			$inactivate_trip_detail_id=mysqli_query($GLOBALS['con'],"UPDATE `trip_details` SET `trip_detail_status`='DEL',`trip_detail_deleted_on`='$time',`trip_detail_deleted_by`='$USERID' WHERE `trip_detail_id`='$old_trip_detail_id'");
			if(!$inactivate_trip_detail_id){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;	
			}

			//---/inactivate old detail id


//---insert new trip detail id
			$last_trip_detail_id=mysqli_query($GLOBALS['con'],"SELECT `trip_detail_id` FROM `trip_details` ORDER BY `auto` DESC LIMIT 1");
			if(mysqli_num_rows($last_trip_detail_id)>0){
				$last_trip_detail_id_b=mysqli_fetch_assoc($last_trip_detail_id)['trip_detail_id'];
				if($last_trip_detail_id_b==""){
					$new_trip_detail_id=10001;
				}else{
					$new_trip_detail_id=$last_trip_detail_id_b+1;
				}
			}else{
				$new_trip_detail_id=10001;
			}


			$insert_trip_detials=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_details`(`trip_detail_id`, `trip_id_fk`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`, `trip_detail_status`, `trip_detail_added_on`, `trip_detail_added_by`) VALUES ('$new_trip_detail_id','$update_id','$truck_id','$driver_group_id','$total_miles','$ppm_plan_id','$pay_per_mile','$incentive_rate','$total_incentive','$start_date','$end_date','ACT','$time','$USERID')");


			if(!$insert_trip_detials){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;	
			}




//-------update trip stops

//-------dump trip stop records to trip stops logs table
			$dump_trip_stop_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO trip_stops_logs (`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`)SELECT `trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status` FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$old_trip_detail_id'");
			if(!$dump_trip_stop_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 04'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip stop records to trip stops logs table


//-------insert stops
			$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

			$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
			$stop_inserted=true;
			foreach ($stops_array_senetized as $stop_row) {
				$next_stop_id++;
				$insert_new_stops=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$new_trip_detail_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
				if(!$insert_new_stops){
					$executionMessage=SOMETHING_WENT_WROG.' step 05'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert stops




//-------delete old stops records from live table
			$last_stop_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

			$last_stop_id_not_to_delete=(mysqli_num_rows($last_stop_id_not_to_delete)==1)?mysqli_fetch_assoc($last_stop_id_not_to_delete)['trip_stop_id']:1000000;
			$delete_old_stops=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$old_trip_detail_id' AND NOT `trip_stop_id`='$last_stop_id_not_to_delete'");
			if(!$dump_trip_stop_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 06'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old stops records from live table

//-------/update trip stops





//-------update trip salary parameters

//-------dump trip salary parameters records to trip salary parameters logs table
			$dump_trip_salary_parameters_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO trip_salary_parameters_logs ( `trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`, `trip_salary_parameter_status`, `trip_salary_parameter_added_by`)SELECT `trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`, `trip_salary_parameter_amount`, `trip_salary_parameter_status`, `trip_salary_parameter_added_by` FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_detail_id_fk`='$old_trip_detail_id'");
			if(!$dump_trip_salary_parameters_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 07'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip salary parameters records to trip salary parameters logs table

//-------insert salary parameters new records 
			$last_trip_salary_parameter_id=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

			$next_trip_salary_parameter_id=(mysqli_num_rows($last_trip_salary_parameter_id)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id)['trip_salary_parameter_id']:1000000;

					///-----//Generate New Unique Id
			$trip_salary_parameter_inserted=true;
			foreach ($salary_parameters_array as $sa_pr) {
				$next_trip_salary_parameter_id++;
				$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$new_trip_detail_id','".$sa_pr['driver_id']."','".$sa_pr['parameter_id']."','".$sa_pr['parameter_amount']."','ACT')");
				if(!$insert_trip_salary_parameter){
					$executionMessage=SOMETHING_WENT_WROG.' step 08'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert salary parameters new records 


//-------delete old salary parameters records from live table

			$last_trip_salary_parameter_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

			$last_trip_salary_parameter_id_not_to_delete=(mysqli_num_rows($last_trip_salary_parameter_id_not_to_delete)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id_not_to_delete)['trip_salary_parameter_id']:1000000;

			$delete_old_salary_parameters=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_salary_parameters` WHERE `trip_salary_parameter_trip_detail_id_fk`='$old_trip_detail_id' AND NOT `trip_salary_parameter_id`='$last_trip_salary_parameter_id_not_to_delete'");
			if(!$delete_old_salary_parameters){
				$executionMessage=SOMETHING_WENT_WROG.' step 09'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old salary parameters records from live table

//-------/update trip salary parameters





//-------update trip driver records

//-------dump trip drivers records to trip drivers logs table
			$dump_trip_driver_records_to_logs=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers_logs`(`trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_incentives_status`, `trip_driver_incentives_moved_on`, `trip_driver_incentives_moved_by`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks`, `trip_driver_status`, `trip_driver_remarks`) SELECT `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_incentives_status`, `trip_driver_incentives_moved_on`, `trip_driver_incentives_moved_by`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks`, `trip_driver_status`, `trip_driver_remarks` FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`='$old_trip_detail_id'");
			if(!$dump_trip_driver_records_to_logs){
				$executionMessage=SOMETHING_WENT_WROG.' step 10'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}
//-------dump trip drivers records to trip drivers logs table



//-------insert trip drivers new records
			$get_trip_driver_id=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

			$next_trip_driver_id=(mysqli_num_rows($get_trip_driver_id)==1)?mysqli_fetch_assoc($get_trip_driver_id)['trip_driver_id']:1000000;

					///-----//Generate New Unique Id
			$driver_inserted=true;
			$driver_new_payments_array=[];
			foreach ($drivers_array as $dA) {
				$next_trip_driver_id++;


				$net_earnings=0;
				$net_earnings+=floatval($dA['basic_earnings']);				

				$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$new_trip_detail_id ,'driver_id'=>$dA['id'] ));
				$net_earnings=$net_earnings+(floatval($salary_parameters['net_impact']));


				array_push($driver_new_payments_array, array('driver_id'=>$dA['id'],'amount'=>$net_earnings,'trip_driver_id'=>$next_trip_driver_id));
				$insertDrivers=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers`( `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_status`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`,`trip_driver_net_earnings`,`trip_driver_incentives_status`,`trip_driver_remarks`) VALUES ('$next_trip_driver_id','$new_trip_detail_id','".$dA['id']."','ACT','".$dA['miles']."','$pay_per_mile','".$dA['basic_earnings']."','".$dA['incentive']."','$net_earnings','HOLD','".$dA['remarks']."')");
				if(!$insertDrivers){
					$executionMessage=SOMETHING_WENT_WROG.' step 11'.mysqli_error($GLOBALS['con']);
					$execution=false;
					goto executionChecker;
				}
			}
//-------/insert trip drivers new records


//-------delete old driver records from live table
			$get_trip_driver_id_not_to_delete=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

			$get_trip_driver_id_not_to_delete=(mysqli_num_rows($get_trip_driver_id_not_to_delete)==1)?mysqli_fetch_assoc($get_trip_driver_id_not_to_delete)['trip_driver_id']:1000000;
			$delete_old_trip_drivers=mysqli_query($GLOBALS['con'],"DELETE FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`='$old_trip_detail_id' AND NOT `trip_driver_id`='$get_trip_driver_id_not_to_delete'");
			if(!$delete_old_trip_drivers){
				$executionMessage=SOMETHING_WENT_WROG.' step 12'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}			
//-------/delete old driver records from live table




//-------delete unpaid payment records
			$delete_unpaid_records=mysqli_query($GLOBALS['con'],"DELETE FROM `driver_payments` WHERE (SELECT COUNT(`payment_paid_id`) FROM `driver_payments_paid` WHERE `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`=`payment_id`)=0 AND `payment_trip_detail_id_fk`='$old_trip_detail_id'");
			if(!$delete_unpaid_records){
				$executionMessage=SOMETHING_WENT_WROG.' step 13'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;
			}	
//-------/delete unpaid payment records




//----loop through new driver array
			$new_drivers_id_array=[]; 
			foreach ($driver_new_payments_array as $dnpa) {
				array_push($new_drivers_id_array, $dnpa['driver_id']);
				$check_driver_old_record=mysqli_query($GLOBALS['con'],"SELECT `payment_id`, `payment_amount`,`payment_trip_driver_id_fk` FROM `driver_payments` WHERE `payment_driver_id_fk`='".$dnpa['driver_id']."' AND `payment_status`='ACT' AND  `payment_category`='TRIP-EARNINGS' AND `payment_trip_detail_id_fk`='$old_trip_detail_id'");






						///---------insert driver payment entry
				$get_driver_payment_id=mysqli_query($GLOBALS['con'],"SELECT `payment_id` FROM `driver_payments` ORDER BY `auto` DESC LIMIT 1");
				$get_driver_payment_id=(mysqli_num_rows($get_driver_payment_id)==1)?(mysqli_fetch_assoc($get_driver_payment_id)['payment_id']):'00000000';

				$get_driver_payment_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
				if($get_driver_payment_id_prefix==substr($get_driver_payment_id,0,6)){
					$new_driver_payment_id=$get_driver_payment_id_prefix.sprintf('%04d',(intval(substr($get_driver_payment_id,6))));
				}else{
					$new_driver_payment_id=$get_driver_payment_id_prefix.'0000';
				}
///---------/insert driver payment entry







				if(mysqli_num_rows($check_driver_old_record)==1){
					$driver_old_payment_detail=mysqli_fetch_assoc($check_driver_old_record);
					$update_old_record=mysqli_query($GLOBALS['con'],"UPDATE `driver_payments` SET `payment_amount`='".$dnpa['amount']."',`payment_trip_detail_id_fk`='$new_trip_detail_id',`payment_trip_driver_id_fk`='".$dnpa['trip_driver_id']."' WHERE `payment_id`='".$driver_old_payment_detail['payment_id']."'");

					if(!$update_old_record){
						$executionMessage=SOMETHING_WENT_WROG.' step 14'.mysqli_error($GLOBALS['con']);
						$execution=false;
						goto executionChecker;
					}	

				}else{

					$new_driver_payment_id++;
					$make_statment=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments`(`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`, `payment_added_on`, `payment_added_by`, `payment_trip_detail_id_fk`, `payment_trip_driver_id_fk`,`payment_status`) VALUES ('$new_driver_payment_id','".$dnpa['driver_id']."','TRIP-EARNINGS','CR','".$dnpa['amount']."','$time','$USERID','$new_trip_detail_id','".$dnpa['trip_driver_id']."','ACT')");

					if(!$make_statment){
						$executionMessage=SOMETHING_WENT_WROG.' step 15'.mysqli_error($GLOBALS['con']);
						$execution=false;
						goto executionChecker;
					}					
				}


			}

			/* Now loop through old records if any driver id exists in old records but not in new. than consider it as overpaid entry */

			$get_old_records_of_payment_q=mysqli_query($GLOBALS['con'],"SELECT `payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`, `payment_amount` FROM `driver_payments` WHERE `payment_status`='ACT' AND  `payment_category`='TRIP-EARNINGS' AND `payment_trip_detail_id_fk`='$old_trip_detail_id'");
			while ($gorop=mysqli_fetch_assoc($get_old_records_of_payment_q)) {

				if(!in_array($gorop['payment_driver_id_fk'],$new_drivers_id_array)){
					$new_driver_payment_id++;
					$overpaid_amount=0-$gorop['payment_amount'];
					$make_over_payment=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments`(`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`, `payment_added_on`, `payment_added_by`, `payment_status`) VALUES ('$new_driver_payment_id','".$gorop['payment_driver_id_fk']."','TRIP-OVERPAID','DR','$overpaid_amount','$time','$USERID','ACT')");
					if(!$make_over_payment){
						$executionMessage=SOMETHING_WENT_WROG.' step 16'.mysqli_error($GLOBALS['con']);
						$execution=false;
						goto executionChecker;
					}				
				}
			}


//-------/update trip driver records

			executionChecker:
			if($execution){
				$message="Trip resettled successfuly";
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
	return $r;

}



function trips_add_new($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0119', USER_PRIV)){

			//$message=REQUIRE_NECESSARY_FIELDS;
		$USERID=USER_ID;
		$time=time();


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";


		if(isset($param['truck_id'])){
			$truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['truck_id']);

			include_once APPROOT.'/models/masters/Trucks.php';
			$Trucks=new Trucks;

			if(!$Trucks->isValidId($truck_id)){
				$InvalidDataMessage="Invalid truck value";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide truck id";
			$dataValidation=false;
			goto ValidationChecker;
		}


		if(isset($param['ppm_plan_id'])){
			$ppm_plan_id=mysqli_real_escape_string($GLOBALS['con'],$param['ppm_plan_id']);

			include_once APPROOT.'/models/masters/DriverPpmPlans.php';
			$DriverPpmPlans=new DriverPpmPlans;

			if(!$DriverPpmPlans->isValidId($ppm_plan_id)){
				$InvalidDataMessage="Invalid ppm plan id";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide ppm plan id";
			$dataValidation=false;
			goto ValidationChecker;
		}			



		if(isset($param['pay_per_mile'])){
			$pay_per_mile=mysqli_real_escape_string($GLOBALS['con'],$param['pay_per_mile']);
			if(!preg_match("/^[0-9.]{1,}$/",$pay_per_mile)){
				$InvalidDataMessage="Please provide valid pay per mile";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide pay per mile";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['incentive_rate'])){
			$incentive_rate=mysqli_real_escape_string($GLOBALS['con'],$param['incentive_rate']);
			if(!preg_match("/^[0-9.]{1,}$/",$incentive_rate)){
				$InvalidDataMessage="Please provide valid incentive rate";
				$dataValidation=false;
				goto ValidationChecker;						
			}


		}else{
			$InvalidDataMessage="Please provide valid incentive rate";
			$dataValidation=false;
			goto ValidationChecker;
		}





////----------validate stops

		if(isset($param['stops'])){
			$stops=$param['stops'];

			$stops_array_senetized=[];
			foreach ($stops as $stop) {
				$stop_item_senetized=[];
						//--validate stop date
				if(isset($stop['stop_date'])){
					if(isValidDateFormat($stop['stop_date'])){
						$stop_date=date('Y-m-d', strtotime($stop['stop_date']));
						$stop_item_senetized['stop_date']=$stop_date;

							///---------restrict the future date selection
						if($stop_date>date('Y-m-d')){
							$InvalidDataMessage="Futute date not allowed in any stop";
							$dataValidation=false;
							goto ValidationChecker;									
						}
							///---------/restrict the future date selection

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


				///-----check if all the stop dates are in ascending order
				if(!isset($set_date)){
					$set_date=$stop_date;
				}else{
						//--check if the new date is greater or equal to the old set date
						//--if greater than setdate equal to new date for next time check
						//--else throw error
					if($stop_date<$set_date){
						$InvalidDataMessage="Please provide valid stop date order";
						$dataValidation=false;
						goto ValidationChecker;	
					}
					$set_date=$stop_date;
				}

						//----validate stop type id
				if(isset($stop['stop_type_id'])){
					$stop_type_id=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_type_id']);

					include_once APPROOT.'/models/masters/TripStopTypes.php';
					$TripStopTypes=new TripStopTypes;

					if($TripStopTypes->isValidId($stop_type_id)){
						$stop_item_senetized['stop_type_id']=$stop_type_id;

					}else{
						$InvalidDataMessage="Invalid stop type value";
						$dataValidation=false;
						goto ValidationChecker;
					}

				}else{
					$InvalidDataMessage="Please provide stop type id";
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop type id


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
					$InvalidDataMessage="Please provide stop location id".$stop['stop_location_id'];
					$dataValidation=false;
					goto ValidationChecker;
				}
						//----/validate stop location id


					//----validdate stop mile
				if(isset($stop['stop_mile'])){
					$stop_mile=mysqli_real_escape_string($GLOBALS['con'],$stop['stop_mile']);

					if(preg_match("/^[0-9]{1,}$/",$stop['stop_mile'])){
						$stop_item_senetized['stop_mile']=$stop_mile;
					}else{
						$InvalidDataMessage="Please provide valid stop mile";
						$dataValidation=false;
						goto ValidationChecker;								
					}

				}else{
					$InvalidDataMessage="Please provide stop mile";
					$dataValidation=false;
					goto ValidationChecker;
				}
					//----/validate stop mile
				array_push($stops_array_senetized, $stop_item_senetized);
			}
		}

		if(count($stops_array_senetized)>=2){
			$total_miles=array_sum(array_column($stops_array_senetized,'stop_mile'));



		}else{
			$InvalidDataMessage="Please provide atleast two stop";
			$dataValidation=false;
			goto ValidationChecker;
		}

////---------//-validate stops



		$start_date=min(array_column($stops_array_senetized, 'stop_date'));
		$end_date=max(array_column($stops_array_senetized, 'stop_date'));



//-----check datetime clashing of truck with existiong records
//-----it will prevent the repeative use truck	

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod

		if($this->are_truck_dates_clashing(array('truck_id'=>$truck_id,'start_date'=>$start_date,'end_date'=>$end_date))){
			$InvalidDataMessage="Trip period clashing. Truck ID has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of truck with existiong records






			///-----------drivers section calcuations
		$driver_group_id=0;
		if(isset($param['driver_group_id'])){
			$driver_group_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_group_id']);

			include_once APPROOT.'/models/masters/DriverGroups.php';
			$DriverGroups=new DriverGroups;

			if(!$DriverGroups->isValidId($driver_group_id)){
				$InvalidDataMessage="Please provide driver group id";
				$dataValidation=false;
				goto ValidationChecker;	
			}

		}else{
			$InvalidDataMessage="Please provide driver group id";
			$dataValidation=false;
			goto ValidationChecker;
		}

				//---driver A should is required in both the case so check if same is the passed;


//----------Calculate miles, basic earning & incentive for drivers;

		if($driver_group_id=='SOLO'){
			$driver_miles=floatval($total_miles);
			$driver_incentive=floatval($incentive_rate*$driver_miles);
		}elseif ($driver_group_id=='TEAM') {
			$driver_miles=floatval($total_miles)/2;
			$driver_incentive=round((floatval($incentive_rate*$driver_miles)),2);
		}



			///-----create array in of driver details for final insert in database
		$drivers_array=[];
		$salary_parameters_array=[];
		if(isset($param['driver_a'])){
			$driver_a_details=[];
					///---check if all the details of driver A ar send or not
			$driver_a=$param['driver_a'];
			if(isset($driver_a['id'])){
				$driver_a_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['id']);

				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;

				if(!$Drivers->isValidId($driver_a_details['id'])){
					$InvalidDataMessage="Invalid driver A";
					$dataValidation=false;
					goto ValidationChecker;
				}





//-----check datetime clashing of driver a with existiong records
//-----it will prevent the repeative use driver a		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_a_details['id'],'start_date'=>$start_date,'end_date'=>$end_date))){
			$InvalidDataMessage="Trip period clashing. Driver A has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver a with existiong records








				$driver_a_details['miles']=$driver_miles;
				$driver_a_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
				$driver_a_details['incentive']=$driver_incentive;

				$driver_a_details['remarks']="";
				if(isset($driver_a['remarks'])){
					$driver_a_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_a['remarks']);
				}

				array_push($drivers_array, $driver_a_details);	

////-----------driver a salary parameters
				if(isset($param['driver_a']['salary_parameters'])){
					$salary_parameters_a=$param['driver_a']['salary_parameters'];
					foreach ($salary_parameters_a as $salary_parameters_a) {
						$salary_parameter=[];
						$salary_parameter['driver_id']=$driver_a['id'];
						if(isset($salary_parameters_a['parameter_id'])){
							$parameter_id_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_id']);
						//---get parameter details
							$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_a'");
							if(mysqli_num_rows($pm_d_q)==1){
								$pm_d_result=mysqli_fetch_assoc($pm_d_q);
								$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
							}else{
								$InvalidDataMessage="Invalid salary parameter id";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}


						if(isset($salary_parameters_a['parameter_amount'])){
							$parameter_amount_a=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_a['parameter_amount']);
						//---get parameter details
							if(is_numeric($parameter_amount_a)){
								$salary_parameter['parameter_amount']=round($parameter_amount_a,2);
							}else{
								$InvalidDataMessage="Invalid salary parameter amount";
								$dataValidation=false;
								goto ValidationChecker;							
							}
						//---/get parameter details
						}else{
							$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
							$dataValidation=false;
							goto ValidationChecker;
						}
						array_push($salary_parameters_array, $salary_parameter);
					}
				}
////-----------//driver a salary parameters

			}else{
				$InvalidDataMessage="One or more field of driver a ar missing";
				$dataValidation=false;
				goto ValidationChecker;						
			}



		}else{
			$InvalidDataMessage="Please provide driver A details";
			$dataValidation=false;
			goto ValidationChecker;
		}

			//--if the group id is 2 (Team) than record of second driver is also required
		if($driver_group_id=='TEAM'){
			if(isset($param['driver_b'])){


					///---check if all the details of driver A ar send or not
				$driver_b=$param['driver_b'];
				if(isset($driver_b['id'])){

					$driver_b_details['id']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['id']);

					include_once APPROOT.'/models/masters/Drivers.php';
					$Drivers=new Drivers;

					if(!$Drivers->isValidId($driver_b_details['id'])){
						$InvalidDataMessage="Invalid driver B";
						$dataValidation=false;
						goto ValidationChecker;
					}




//-----check datetime clashing of driver b with existiong records
//-----it will prevent the repeative use driver b		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
		if($this->are_driver_dates_clashing(array('driver_id'=>$driver_b_details['id'],'start_date'=>$start_date,'end_date'=>$end_date))){
			$InvalidDataMessage="Trip period clashing. Driver B has been already used for this period";
			$dataValidation=false;
			goto ValidationChecker;			
		}
//-----/check datetime clashing of driver b with existiong records






					$driver_b_details['miles']=$driver_miles;
					$driver_b_details['pay_per_mile']=$pay_per_mile;
					$driver_b_details['basic_earnings']=round((floatval($driver_miles)*floatval($pay_per_mile)),2);
					$driver_b_details['incentive']=$driver_incentive;
					$driver_b_details['remarks']="";
					if(isset($driver_b['remarks'])){
						$driver_b_details['remarks']=mysqli_real_escape_string($GLOBALS['con'],$driver_b['remarks']);
					}						
					array_push($drivers_array, $driver_b_details);


////-----------driver  b salary parameters
					if(isset($param['driver_b']['salary_parameters'])){
						$salary_parameters_b=$param['driver_b']['salary_parameters'];
						foreach ($salary_parameters_b as $salary_parameters_b) {
							$salary_parameter=[];
							$salary_parameter['driver_id']=$driver_b['id'];
						//--validate stop date
							if(isset($salary_parameters_b['parameter_id'])){
								$parameter_id_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_id']);
						//---get parameter details
								$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id_b'");
								if(mysqli_num_rows($pm_d_q)==1){
									$pm_d_result=mysqli_fetch_assoc($pm_d_q);
									$salary_parameter['parameter_id']=$pm_d_result['parameter_id'];
								}else{
									$InvalidDataMessage="Invalid salary parameter id";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter id in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}


							if(isset($salary_parameters_b['parameter_amount'])){
								$parameter_amount_b=mysqli_real_escape_string($GLOBALS['con'],$salary_parameters_b['parameter_amount']);
						//---get parameter details
								if(is_numeric($parameter_amount_b)){
									$salary_parameter['parameter_amount']=$parameter_amount_b;
								}else{
									$InvalidDataMessage="Invalid salary parameter amount";
									$dataValidation=false;
									goto ValidationChecker;							
								}
						//---/get parameter details
							}else{
								$InvalidDataMessage="Please provide parameter amount in driver a salary parameters";
								$dataValidation=false;
								goto ValidationChecker;
							}
							array_push($salary_parameters_array, $salary_parameter);
						}

					}
////-----------//driver b salary parameters





				}else{
					$InvalidDataMessage="One or more field of driver B are missing";
					$dataValidation=false;
					goto ValidationChecker;						
				}

			}else{
				$InvalidDataMessage="Please provide driver B details";
				$dataValidation=false;
				goto ValidationChecker;
			}
		}
			///-----------//drivers section calcuations


			//-----data validation ends
		ValidationChecker:
		if($dataValidation){

			$execution=true;
			$executionMessage='';

				///-----Generate New Unique Id
			$last_trip_id=mysqli_query($GLOBALS['con'],"SELECT `trip_id` FROM `trips` ORDER BY `auto` DESC LIMIT 1");
			if(mysqli_num_rows($last_trip_id)>0){
				$last_trip_id_b=mysqli_fetch_assoc($last_trip_id)['trip_id'];
				if($last_trip_id_b==""){
					$new_trip_id=10001;
				}else{
					$new_trip_id=$last_trip_id_b+1;
				}
			}else{
				$new_trip_id=10001;
			}
					///------------Add new trip
			$total_incentive=$incentive_rate*$total_miles;
			$insertTrip=mysqli_query($GLOBALS['con'],"INSERT INTO `trips`(`trip_id`, `trip_approval_status_id_fk`, `trip_status`, `trip_added_on`, `trip_added_by`) VALUES ('$new_trip_id','PENDING','ACT','$time','$USERID')");



			if(!$insertTrip){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;			
			}








			$last_trip_detail_id=mysqli_query($GLOBALS['con'],"SELECT `trip_detail_id` FROM `trip_details` ORDER BY `auto` DESC LIMIT 1");
			if(mysqli_num_rows($last_trip_detail_id)>0){
				$last_trip_detail_id_b=mysqli_fetch_assoc($last_trip_detail_id)['trip_detail_id'];
				if($last_trip_detail_id_b==""){
					$new_trip_detail_id=10001;
				}else{
					$new_trip_detail_id=$last_trip_detail_id_b+1;
				}
			}else{
				$new_trip_detail_id=10001;
			}


			$insert_trip_detials=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_details`(`trip_detail_id`, `trip_id_fk`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`, `trip_detail_status`, `trip_detail_added_on`, `trip_detail_added_by`) VALUES ('$new_trip_detail_id','$new_trip_id','$truck_id','$driver_group_id','$total_miles','$ppm_plan_id','$pay_per_mile','$incentive_rate','$total_incentive','$start_date','$end_date','ACT','$time','$USERID')");

			if(!$insert_trip_detials){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;			
			}

					///---------insert stops
				$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

				$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
				foreach ($stops_array_senetized as $stop_row) {
					$next_stop_id++;
					$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_detail_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$new_trip_detail_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
			if(!$insertStop){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;			
			}
				}
					///---------//insert stops






					///---------insert salary parameters
				$last_trip_salary_parameter_id=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

				$next_trip_salary_parameter_id=(mysqli_num_rows($last_trip_salary_parameter_id)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id)['trip_salary_parameter_id']:1000000;

					///-----//Generate New Unique Id
				foreach ($salary_parameters_array as $sa_pr) {
					$next_trip_salary_parameter_id++;
					$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_detail_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$new_trip_detail_id','".$sa_pr['driver_id']."','".$sa_pr['parameter_id']."','".$sa_pr['parameter_amount']."','ACT')");
			if(!$insert_trip_salary_parameter){
				$executionMessage=SOMETHING_WENT_WROG.' step 04';
				$execution=false;
				goto executionChecker;			
			}
				}
					///---------//insert salary parameters




					///---------insert trip drivers
				$get_trip_driver_id=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id` FROM `trip_drivers` ORDER BY `auto` DESC LIMIT 1");

				$next_trip_driver_id=(mysqli_num_rows($get_trip_driver_id)==1)?mysqli_fetch_assoc($get_trip_driver_id)['trip_driver_id']:1000000;

					///-----//Generate New Unique Id
				foreach ($drivers_array as $dA) {
					$next_trip_driver_id++;


					$net_earnings=0;
					$net_earnings+=floatval($dA['basic_earnings']);				

					$salary_parameters=$this->driver_salary_parameter(array('trip_detail_id' =>$new_trip_detail_id ,'driver_id'=>$dA['id'] ));
					$net_earnings=$net_earnings+(floatval($salary_parameters['net_impact']));




					$insertDrivers=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers`( `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_status`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`,`trip_driver_net_earnings`,`trip_driver_incentives_status`,`trip_driver_remarks`) VALUES ('$next_trip_driver_id','$new_trip_detail_id','".$dA['id']."','ACT','".$dA['miles']."','$pay_per_mile','".$dA['basic_earnings']."','".$dA['incentive']."','$net_earnings','HOLD','".$dA['remarks']."')");
			if(!$insertDrivers){
				$executionMessage=SOMETHING_WENT_WROG.' step 05';
				$execution=false;
				goto executionChecker;			
			}
				}
					///---------//insert trip drivers
				executionChecker:
				if($execution){
					$message="Trip Created Successfuly";
					$status=true;
					include_once APPROOT.'/models/common/Enc.php';
					$Enc=new Enc;
					$response['new_eid']=$Enc->safeurlen($new_trip_id);
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
	return $r;

}


































/*
function update_driver_trip_earnings($param){
		$status=false;
		$message=null;
		$response=null;

		$dataValidation=true;
		$InvalidDataMessage="";

						if(isset($param['driver_id'])){
							$driver_id=mysqli_fetch_assoc($GLOBALS['con'],$param['driver_id']);
						}else{
							$dataValidation=false;
							$InvalidDataMessage="Please provide driver id";
							goto ValidationChecker;
						}
						if(isset($param['trip_id'])){
							$trip_id=mysqli_fetch_assoc($GLOBALS['con'],$param['trip_id']);
						}else{
							$dataValidation=false;
							$InvalidDataMessage="Please provide trip id";
							goto ValidationChecker;
						}

						ValidationChecker:
						if($dataValidation){
							$driver_salary_parameter=$this->driver_salary_parameter(array('driver_id' => $driver_id,'trip_id' => $trip_id));

							///---------fethc basic earnings
							$basic_earnings=0; 
							$fetch_basic_earnings=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_basic_earnings` FROM `trip_drivers` WHERE `trip_driver_driver_id_fk`='$driver_id' AND `trip_driver_trip_id_fk`='$trip_id'");
							if(mysqli_num_rows($fetch_basic_earnings>0)){
								$result=mysqli_query($fetch_basic_earnings);
								$basic_earnings=floatval($result['trip_driver_basic_earnings'])+$basic_earnings;
							}
							///---------/fethc basic earnings


							$net_earnings=$basic_earnings+floatval($driver_salary_parameter['net_impact']);
							///---------update drive trip earnings
							$update=mysqli_query($GLOBALS['con'],"UPDATE `trip_drivers` SET `trip_driver_net_earnings`='$net_earnings' WHERE `trip_driver_driver_id_fk`='$driver_id' AND `trip_driver_trip_id_fk`='$trip_id'");
							///--------/update drive trip earnings
							if($update){
								$status=true;
							}
						}


		$r=[];
		$r['status']=$status;
		$r['message']=$message; 
}
*/










function trips_approve($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0123', USER_PRIV)){


		if(isset($param['approve_eid_list'])){
			$approve_eid_list=$param['approve_eid_list'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$USERID=USER_ID;
			$time=time();

			$InvalidDataMessage="";
			$dataValidation=true;
			//goto ValidationChecker;	


			//--check if the  approval list is valid
			$approve_id_list_senetized=[];
			foreach ($approve_eid_list as $al) {
				$check_id=$Enc->safeurlde($al);
				$validate_id_q=mysqli_query($GLOBALS['con'],"SELECT `trip_id`,`trip_detail_id` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_id`='$check_id' AND `trip_approval_status_id_fk`='PENDING' AND `trip_detail_status`='ACT' AND `trip_status`='ACT'");
				if(mysqli_num_rows($validate_id_q)==1){
					$validate_id_result=mysqli_fetch_assoc($validate_id_q);
					$validate_id_result_row=[];
					$validate_id_result_row['trip_id']=$validate_id_result['trip_id'];
					$validate_id_result_row['trip_detail_id']=$validate_id_result['trip_detail_id'];
					array_push($approve_id_list_senetized,$validate_id_result_row);

				}else{
					$InvalidDataMessage="One or more trip eid is invalid";
					$dataValidation=true;
					goto ValidationChecker;
				}

			}

			ValidationChecker:
			if($dataValidation){

				$approve_check=true;
				foreach ($approve_id_list_senetized as $ails) {
					$approve_id=$Enc->safeurlde($al);
					$approve=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='APPROVED',`trip_approved_on`='$time',`trip_approved_by`='$USERID' WHERE `trip_id`='".$ails['trip_id']."'");





					//------------insert driver payment of this trip to their statement
					$get_drivers_earnings=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`,`trip_driver_driver_id_fk`, `trip_driver_net_earnings` FROM `trip_drivers` WHERE `trip_driver_trip_detail_id_fk`='".$ails['trip_detail_id']."'");


						///---------insert driver payment entry
					$get_driver_payment_id=mysqli_query($GLOBALS['con'],"SELECT `payment_id` FROM `driver_payments` ORDER BY `auto` DESC LIMIT 1");
					$get_driver_payment_id=(mysqli_num_rows($get_driver_payment_id)==1)?(mysqli_fetch_assoc($get_driver_payment_id)['payment_id']):'00000000';

					$get_driver_payment_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
					if($get_driver_payment_id_prefix==substr($get_driver_payment_id,0,6)){
						$new_driver_payment_id=$get_driver_payment_id_prefix.sprintf('%04d',(intval(substr($get_driver_payment_id,6))+1));
					}else{
						$new_driver_payment_id=$get_driver_payment_id_prefix.'0000';
					}


					$insert_trip_payment_entry=true;
					while ($gde=mysqli_fetch_assoc($get_drivers_earnings)) {
						$new_driver_payment_id++;

						$gde['trip_driver_net_earnings']=ROUND($gde['trip_driver_net_earnings'],2);
						$make_statment=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments`(`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`, `payment_added_on`, `payment_added_by`, `payment_trip_detail_id_fk`, `payment_trip_driver_id_fk`,`payment_status`) VALUES ('$new_driver_payment_id','".$gde['trip_driver_driver_id_fk']."','TRIP-EARNINGS','CR','".$gde['trip_driver_net_earnings']."','$time','$USERID','".$ails['trip_detail_id']."','".$gde['trip_driver_id']."','ACT')");
						if(!$make_statment){
							$insert_trip_payment_entry=false;
						}
					}

					///---------/insert driver payment entry

					//----------//insert driver payment of this trip to their statement


					if($approve && $insert_trip_payment_entry){
					}else{
						$approve_check=false;
					}
					
				}

				if($approve_check){
					$status=true;
					$message="Approved Successfuly";
				}

			}else{
				$message=$InvalidDataMessage;
			}

		}else{
			$message="Please Provide approval eid ";
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






function trips_reject($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0123', USER_PRIV)){


		if(isset($param['reject_eid_list'])){
			$reject_eid_list=$param['reject_eid_list'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;


			$USERID=USER_ID;
			$time=time();

			//--check if the  reject list is valid
			$list_valid=true;
			foreach ($reject_eid_list as $al) {
				$check_id=$Enc->safeurlde($al);
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `trip_id` FROM `trips` WHERE `trip_id`='$check_id' AND `trip_approval_status_id_fk`='PENDING'"))!=1){
					$list_valid=false;
				}

			}

			if($list_valid){

				$approve_check=true;
				foreach ($reject_eid_list as $al) {
					$reject_id=$Enc->safeurlde($al);
					$approve=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='REJECTED',`trip_approved_on`='$time',`trip_approved_by`='$USERID' WHERE `trip_id`='$reject_id'");
				}

				if($approve_check){
					$status=true;
					$message="Rejected Successfuly";
				}

			}else{
				$message="One or more id's are invalid in list";
			}

		}else{
			$message="Please provide reject eid";
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

function driver_trip_parameters_details($param){
	$status=false;
	$message=null;
	$response=[];

	$dataValidation=true;
	$InvalidDataMessage="";
	if(!isset($param['driver_eid'])){
		$dataValidation=false;
		$InvalidDataMessage="Please provide driver eid";		
	}

	if(!isset($param['trip_eid'])){
		$dataValidation=false;
		$InvalidDataMessage="Please prover trip eid";
	}

	if($dataValidation){

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$driver_id=$Enc->safeurlde($param['driver_eid']);
		$trip_id=$Enc->safeurlde($param['trip_eid']);

		$q="SELECT `trip_salary_parameter_id`,`trip_salary_parameter_amount`,`trip_salary_parameter_parameter_id`,`parameter_name` FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id`  WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_driver_id_fk`='$driver_id' AND `trip_salary_parameter_trip_id_fk`='$trip_id' ";

		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while($rows=mysqli_fetch_assoc($qEx)){
			$row=[];
			$row['trip_salary_parameter_eid']=$Enc->safeurlen($rows['trip_salary_parameter_id']);
			$row['parameter_id']=$rows['trip_salary_parameter_parameter_id'];
			$row['name']=$rows['parameter_name'];
			$row['amount']=$rows['trip_salary_parameter_amount'];
			array_push($list, $row);
		}

		$response['list']=$list;
		$status=true;			
	}else{
		$message=$InvalidDataMessage;
	}

	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;	
}
*/


/*
function update_trip_earnings($trip_id){
	//-------get basic details of trip

	$trip_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_group_id_fk`, `trip_ppm`, `trip_incentive_per_mile` FROM `trips` WHERE `trip_id`='$trip_id'");

	if(mysqli_num_rows($trip_q)==1){
		$trip=mysqli_fetch_assoc($trip_q);
		$pay_per_mile=$trip['trip_ppm'];
		$driver_group_id=$trip['trip_driver_group_id_fk'];
		$incentive_rate=$trip['trip_incentive_per_mile'];
		

///--get the totol miles of trip
		$total_miles_q=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT SUM(`trip_stop_miles_driven`) AS total_miles FROM `trip_stops` WHERE `trip_stop_trip_detail_id`='$trip_id' AND `trip_stop_status`='ACT'"));
		$driver_miles=$total_miles=$total_miles_q['total_miles'];

		if($driver_group_id=='TEAM'){
			$driver_miles=floatval($total_miles)/2;
		}

		$incentive=round((floatval($incentive_rate)*floatval($driver_miles)),2);
		$basic_earnings=round((floatval($pay_per_mile)*floatval($driver_miles)),2);
//--get drivers of trip
		$trip_drivers=mysqli_query($GLOBALS['con'],"SELECT `auto`, `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`  FROM `trip_drivers` WHERE  `trip_driver_status`='ACT' AND  `trip_driver_trip_id_fk`='$trip_id'");


		while ($rows=mysqli_fetch_assoc($trip_drivers)) {
			$trip_driver_id=$rows['trip_driver_id'];
			$driver_id=$rows['trip_driver_driver_id_fk'];
			$salary_parameters=$this->driver_salary_parameter(array('trip_id' =>$trip_id ,'driver_id'=>$driver_id ));
			$net_earnings=round(($basic_earnings+(floatval($salary_parameters['net_impact']))),2);
			mysqli_query($GLOBALS['con'],"UPDATE `trip_drivers` SET `trip_driver_mile`='$driver_miles',`trip_driver_pay_per_mile`='$pay_per_mile',`trip_driver_basic_earnings`='$basic_earnings',`trip_driver_net_earnings`='$net_earnings',`trip_driver_incentives`='$incentive' WHERE `trip_driver_id`='$trip_driver_id'");

		}




	}
}
*/







function trips_quick_list($param){

	$q="SELECT `trip_id` FROM `trips` WHERE `trip_status`='ACT'";
//----Apply Filters starts

	if(isset($param['approval_status_id'])){
		$approval_status_id=mysqli_real_escape_string($GLOBALS['con'],$param['approval_status_id']);
		$q.=" AND trip_approval_status_id_fk='$approval_status_id'";
	}
//-----Apply fitlers ends

	$q .=" ORDER BY `trip_id` ASC";

	$qEx=mysqli_query($GLOBALS['con'],$q);

	$list=[];

	while ($rows=mysqli_fetch_assoc($qEx)) {
		$row=[];
		$row['id']=$rows['trip_id'];
		array_push($list,$row);
	}
	$response['list']=$list; 		
	$r=[];
	$r['status']=true;
	$r['message']=null;
	$r['response']=$response;
	return $r;	
}

function trips_months_list($param){
	$status=false;
	$message=null;
	$response=null;
	$list=[];
	$q=mysqli_query($GLOBALS['con'],"SELECT DATE_FORMAT(`trip_end_date`, '%M-%y') AS `trip_month` FROM `trip_details` WHERE `trip_detail_status`='ACT' GROUP BY `trip_month` ORDER BY `trip_end_date`");
	while ($rows=mysqli_fetch_assoc($q)){
		array_push($list, $rows['trip_month']);
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

function trips_cancel($param){
	$status=false;
	$message=null;
	$response=null;
	if(in_array('P0144', USER_PRIV)){


		if(isset($param['cancel_eid'])){
			$cancel_eid=$param['cancel_eid'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;


			$USERID=USER_ID;
			$time=time();
			$InvalidDataMessage="";
			$dataValidation=true;
			//--check if the  approval list is valid
			$cancel_id=$Enc->safeurlde($param['cancel_eid']);
			$trip_detail_q=mysqli_query($GLOBALS['con'],"SELECT `trip_id`,`trip_detail_id` FROM `trips` LEFT JOIN `trip_details` ON `trip_details`.`trip_id_fk`=`trips`.`trip_id` WHERE `trip_status`='ACT' AND `trip_detail_status`='ACT' AND `trip_id`='$cancel_id'");
			if(mysqli_num_rows($trip_detail_q)==1){
				$trip_details=mysqli_fetch_assoc($trip_detail_q);
				$cancel_trip_id=$trip_details['trip_id'];
				$cancel_trip_detail_id=$trip_details['trip_detail_id'];
				if(($this->is_paid($cancel_trip_detail_id))==true){
					$InvalidDataMessage="This trip can't be cancelled";
					$dataValidation=false;
					goto ValidationChecker;						
				}
			}else{
				$InvalidDataMessage="Invalid trip eid";
				$dataValidation=false;
				goto ValidationChecker;	
			}
			ValidationChecker:

			if($dataValidation){
//-----delete approved unpaid payment records				
				$delete_approved_payments=mysqli_query($GLOBALS['con'],"UPDATE `driver_payments` SET `payment_status`='DEL' WHERE `payment_trip_detail_id_fk`='$cancel_trip_detail_id' AND `payment_category`='TRIP-EARNINGS'");
				if($delete_approved_payments){
					$cancel_trip=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='CANCELLED',`trip_cancelled_on`='$time',`trip_cancelled_by`='$USERID',`trip_canelled_remark`='' WHERE `trip_id`='$cancel_trip_id'");
					if($cancel_trip){
						$status=true;
						$message="Cancelled successfuly";
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=SOMETHING_WENT_WROG;
				}
			}else{
				$message=$InvalidDataMessage;
			}




		}else{
			$message="Please provide cancel eid ";
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