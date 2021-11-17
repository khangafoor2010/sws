<?php
/**
 * 
 */
class DaDriverPayments
{
	function list_of_incentives($param){
		$status=false;
		$message=null;
		$response=[];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		
		$incentive_status='HOLD';
		if(isset($param['status']) && $param['status']=='MOVED'){
			$incentive_status='MOVED';
		}

		//-----data validation starts
 		///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";




		ValidationChecker:
		if($dataValidation){


				$fetch_incentives_q="SELECT DATE_FORMAT(`trip_end_date`, '%M-%y') AS `incentive_month`,`trip_driver_incentives`,`trip_id_fk`,trip_driver_id FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_details`.`trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_driver_incentives_status`='$incentive_status' AND `trip_driver_driver_id_fk`='".DRIVER_ID."'";

		if(isset($param['trips_month']) && isset($param['trips_month'])!=""){
			$trips_month=mysqli_real_escape_string($GLOBALS['con'],$param['trips_month']);
	$q.=" AND DATE_FORMAT(`trip_start_date`, '%M-%y')='$trips_month'";
		}

				$fetch_incentives_qEx=mysqli_query($GLOBALS['con'],$fetch_incentives_q);
				$incentives_list=[];
				while ($incentive_rows=mysqli_fetch_assoc($fetch_incentives_qEx)) {
					$incentive_row=[];
					$incentive_row['trip_id']=$incentive_rows['trip_id_fk'];
					$incentive_row['amount']=ROUND($incentive_rows['trip_driver_incentives'],2);
					$incentive_row['month']=$incentive_rows['incentive_month'];
					array_push($incentives_list, $incentive_row);
				}

			
			$response['list']=$incentives_list;
			if(count($incentives_list)>0){
				$status=true;
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
	function list_of_transactions($param){
	include_once APPROOT.'/models/accounts/DriverPayments.php';
	$DriverPayments=new DriverPayments;
	return $DriverPayments->drivers_transactions_list(array('driver_id'=>DRIVER_ID));
	}	

	function list_of_payments_paid($param){
	include_once APPROOT.'/models/accounts/DriverPayments.php';
	$DriverPayments=new DriverPayments;
	return $DriverPayments->drivers_payments_paid_list(array_merge(array('driver_id'=>DRIVER_ID),$param));
	}

	function list_of_settlements($param){
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
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;
		
		
		$q="SELECT `payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`,(SELECT SUM(ROUND(`payment_paid_amount`,2)) FROM `driver_payments_paid` WHERE `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`=`payment_id`) AS `payment_paid_amount`, `payment_added_on`, `payment_added_by`, `trip_id_fk`, `payment_trip_driver_id_fk`, `payment_parameter_type_id_fk`,`payment_remarks`,`parameter_name` FROM `driver_payments` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`driver_payments`.`payment_parameter_type_id_fk` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`driver_payments`.`payment_trip_detail_id_fk`  WHERE `payment_status`='ACT'  AND `payment_driver_id_fk`='".DRIVER_ID."'  ORDER BY `payment_id` ASC";


		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

$q .=" limit $from, $range";

		


		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		$counter=$from;
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['sr_no']=++$counter;
			$row['id']=$rows['payment_id'];
			$row['eid']=$Enc->safeurlen($rows['payment_id']);
			$row['category']=$rows['payment_category'];
			$row['type']=$rows['payment_type'];
			$row['amount']=$rows['payment_amount'];
			$row['amount_paid']=($rows['payment_paid_amount']==null)?0:$rows['payment_paid_amount'];

			//$row['paid_details']=($this->drivers_payments_paid_list(array('payment_id'=>$rows['payment_id'])))['response']['list'];
			$row['balance']=$row['amount']-$row['amount_paid'];
			$row['trip_id']=($rows['trip_id_fk']==null)?'':$rows['trip_id_fk'];
			$row['trip_eid']=($rows['payment_parameter_type_id_fk'])?'':$Enc->safeurlen($rows['trip_id_fk']);
			$row['parameter_name']=($rows['parameter_name']==null)?'':$rows['parameter_name'];
			//$row['status']=$rows['payment_pay_status'];
			$row['remarks']=$rows['payment_remarks'];
			
			$row['payments_paid_list']=[];
			$get_paid_list=mysqli_query($GLOBALS['con'],"SELECT `auto`, `payment_paid_id`, `payment_paid_payment_id_fk`, `payment_paid_amount`, `payment_paid_on`, `payment_paid_by`, `payment_paid_transaction_id_fk`, `payment_paid_status` FROM `driver_payments_paid` WHERE `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`='".$rows['payment_id']."'");
			while ($gpl=mysqli_fetch_assoc($get_paid_list)) {
				array_push($row['payments_paid_list'],
					array(
						'paid_amount' => $gpl['payment_paid_amount'], 
						'transaction_id' => $gpl['payment_paid_transaction_id_fk'],
						'paid_on_date'=>date('d-M-Y',$gpl['payment_paid_on']), 
						'paid_on_datetime'=>dateTimeFromDbTimestamp($gpl['payment_paid_on']) 
					)
				);
			}

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
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		} 		


		invalidrange:
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}


/*
	function details_of_trip($param){

	$status=false;
	$message=null;
	$response=[];	
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;
	$driver_eid=$Enc->safeurlen(DRIVER_ID);
	include_once APPROOT.'/models/accounts/Trips.php';
	if(isset($param['trip_id'])){
	$Trips=new Trips;
	$trip_eid=$Enc->safeurlen($param['trip_id']);
	$result=$Trips->driver_trip_details(array('driver_eid'=>$driver_eid,'trip_eid'=>$trip_eid));
	if($result['status']){
		$response=$result['response'];
		$status=true;
	}		
}else{
	$message="Please provide trip id";
}


	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;	

	}*/
}

?>


