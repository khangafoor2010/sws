<?php
/**
 * 
 */
class DriverPayments
{

	function drivers_group_transactions_list($param){
		$status=false;
		$message=null;
		$response=[];
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
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;
		//----fethc all drivers
		$q="SELECT  `transaction_group_id`, `transaction_group_added_on`, `transaction_group_added_by`, `transaction_group_status`,(SELECT SUM(ROUND((SELECT SUM(ROUND(`payment_paid_amount`,2)) FROM `driver_payments_paid` WHERE `payment_paid_transaction_id_fk`=`transaction_id`),2)) FROM `driver_transactions` WHERE `transaction_group_id_fk`=`transaction_group_id`) AS `group_transaction_total` FROM `driver_transactions_groups` WHERE `transaction_group_status`='ACT'";
//----Apply Filters starts

		if(isset($param['id']) && $param['id']!=""){
			$id=mysqli_real_escape_string($GLOBALS['con'],$param['id']);
			$q.=" AND transaction_group_id LIKE '%$id%'";
		}		
//-----Apply fitlers ends





		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $batch";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		$counter=$from;


		$list=[];
		while($rows=mysqli_fetch_assoc($qEx)){
			$row=[];
			$row['sr_no']=++$counter;
			$row['eid']=$Enc->safeurlen($rows['transaction_group_id']);
			$row['id']=$rows['transaction_group_id'];
			$row['amount']=($rows['group_transaction_total']!=NULL)?$rows['group_transaction_total']:0;
			$added_user=$Users->user_basic_details($rows['transaction_group_added_by']);
			$row['added_by_user_code']=$added_user['user_code'];
			$row['added_by_user_name']=$added_user['user_name'];
			$row['added_on_date']=dateFromDbToFormat($rows['transaction_group_added_on']);
			$row['added_on_time']=date('H:i',$rows['transaction_group_added_on']);
			$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['transaction_group_added_on']);
			array_push($list, $row);


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

	function drivers_transactions_list($param){
		$status=false;
		$message=null;
		$response=[];
		$batch=500;
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
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;
		//----fethc all drivers
		$q="SELECT `transaction_id`, `transaction_group_id_fk`,(SELECT SUM(ROUND(`payment_paid_amount`,2))FROM `driver_payments_paid` WHERE `payment_paid_transaction_id_fk`=`transaction_id` AND `payment_paid_status`='ACT') AS `transaction_amount_sum`, `transaction_driver_id_fk`, `transaction_payment_mode_id_fk`, `transaction_reference`, `transaction_added_on`, `transaction_added_by`, `driver_code`,`driver_name_first`,`driver_id`,`mode_name` FROM `driver_transactions` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_transactions`.`transaction_driver_id_fk`LEFT JOIN `payment_modes` ON `payment_modes`.`mode_id`=`driver_transactions`.`transaction_payment_mode_id_fk` WHERE `transaction_status`='ACT'";//----Apply Filters starts

		if(isset($param['id']) && $param['id']!=""){
			$id=mysqli_real_escape_string($GLOBALS['con'],$param['id']);
			$q.=" AND transaction_id LIKE '%$id%'";
		}
		if(isset($param['group_transaction_id']) && $param['group_transaction_id']!=""){
			$group_transaction_id=mysqli_real_escape_string($GLOBALS['con'],$param['group_transaction_id']);
			$q.=" AND transaction_group_id_fk LIKE '$group_transaction_id'";
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q.=" AND transaction_driver_id_fk LIKE '$driver_id'";
		}						
//-----Apply fitlers ends





		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $batch";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		$counter=$from;


		$list=[];
		while($rows=mysqli_fetch_assoc($qEx)){
			$row=[];
			$row['sr_no']=++$counter;
			$row['eid']=$Enc->safeurlen($rows['transaction_id']);
			$row['id']=$rows['transaction_id'];
			$row['driver_eid']=$Enc->safeurlen($rows['driver_id']);
			$row['driver_code']=$rows['driver_code'];
			$row['driver_name']=$rows['driver_name_first'];
			$row['amount']=($rows['transaction_amount_sum']==null)?0:$rows['transaction_amount_sum'];
			$row['transaction_group_id']=$rows['transaction_group_id_fk'];
			$added_user=$Users->user_basic_details($rows['transaction_added_by']);
			$row['added_by_user_code']=$added_user['user_code'];
			$row['added_by_user_name']=$added_user['user_name'];
			$row['added_on_date']=dateFromDbToFormat($rows['transaction_added_on']);
			$row['added_on_time']=date('H:i',$rows['transaction_added_on']);
			$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['transaction_added_on']);
			array_push($list, $row);


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
		$r['message']=$param;
		$r['response']=$response;
		return $r;
	}










	function drivers_payments_list($param){
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
		
		
		$q="SELECT `driver_code`,`driver_name_first`,`driver_name_middle`,`driver_name_last`,`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`,(SELECT SUM(ROUND(`payment_paid_amount`,2)) FROM `driver_payments_paid` WHERE `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`=`payment_id`) AS `payment_paid_amount`, `payment_added_on`, `payment_added_by`, `trip_id_fk`, `payment_trip_driver_id_fk`, `payment_parameter_type_id_fk`,`payment_remarks`,`parameter_name` FROM `driver_payments` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_payments`.`payment_driver_id_fk` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`driver_payments`.`payment_parameter_type_id_fk` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`driver_payments`.`payment_trip_detail_id_fk`  WHERE `payment_status`='ACT'";


//----Apply Filters starts
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q.=" AND `payment_driver_id_fk`='$driver_id'";
		}

		if(isset($param['pay_status']) && $param['pay_status']!=""){
			$pay_status=mysqli_real_escape_string($GLOBALS['con'],$param['pay_status']);
			$q.=" AND `payment_pay_status`='$pay_status'";
		}


		if(isset($param['created_date_from']) && isValidDateFormat($param['created_date_from'])){
			$created_date_from=strtotime($param['created_date_from']);
			$q .=" AND payment_added_on >='$created_date_from'";
		}
		if(isset($param['created_date_to']) && isValidDateFormat($param['created_date_to'])){
			$created_date_to=strtotime($param['created_date_to']." +1 days");
			$q .=" AND payment_added_on <='$created_date_to'";
		}
		/*if(isset($param['paid_date_from']) && isValidDateFormat($param['paid_date_from'])){
			$paid_date_from=strtotime($param['paid_date_from']);
			$q .=" AND payment_paid_on >='$paid_date_from'";
		}
		if(isset($param['paid_date_to']) && isValidDateFormat($param['paid_date_to'])){
			$paid_date_to=strtotime($param['paid_date_to']." +1 days");
			$q .=" AND payment_paid_on <='$paid_date_to'";
		}*/

//----//Apply Filters starts
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'id':
				$q .=" ORDER BY `payment_id` ASC";
				break;
				case 'driver_code':
				$q .=" ORDER BY `driver_code` ASC";
				break;
				default:
				$q .=" ORDER BY `payment_id` ASC";
				break;
			}
		}else{
			$q .=" ORDER BY `payment_id` ASC";	
		}



		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

//--- if report view is send return all rows but set a limit
		if(isset($param['report_view']) && $param['report_view']==true){
			if($totalRows>5000){
				$message="Only 5000 or less rows can be exported at a time";
				goto invalidrange;
			}
		}else{
			$q .=" limit $from, $range";
		}

		


		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		$counter=$from;
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['sr_no']=++$counter;
			$row['id']=$rows['payment_id'];
			$row['eid']=$Enc->safeurlen($rows['payment_id']);
			$row['driver_eid']=$Enc->safeurlen($rows['driver_code']);
			$row['driver_code']=$rows['driver_code'];

			$driver_name_first=($rows['driver_name_first']!="")?$rows['driver_name_first']:'';
			$driver_name_middle=($rows['driver_name_middle']!="")?' '.$rows['driver_name_middle']:'';
			$driver_name_last=($rows['driver_name_first']!="")?' '.$rows['driver_name_last']:'';
			$row['driver_name']=$driver_name_first.$driver_name_middle.$driver_name_last;
			$row['category']=$rows['payment_category'];
			$row['type']=$rows['payment_type'];
			$row['amount']=$rows['payment_amount'];
			$row['amount_paid']=($rows['payment_paid_amount']==null)?0:$rows['payment_paid_amount'];

			$row['paid_details']=($this->drivers_payments_paid_list(array('payment_id'=>$rows['payment_id'])))['response']['list'];
			$row['balance']=round(($row['amount']-$row['amount_paid']),2);
			$row['trip_id']=($rows['trip_id_fk']==null)?'':$rows['trip_id_fk'];
			$row['trip_eid']=($rows['payment_parameter_type_id_fk'])?'':$Enc->safeurlen($rows['trip_id_fk']);
			$row['parameter_name']=($rows['parameter_name']==null)?'':$rows['parameter_name'];
			//$row['status']=$rows['payment_pay_status'];
			$row['remarks']=$rows['payment_remarks'];
			$added_user=$Users->user_basic_details($rows['payment_added_by']);
			$row['added_by_user_code']=$added_user['user_code'];
			$row['added_by_user_name']=$added_user['user_name'];
			$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['payment_added_on']);
			
			//$paid_user=$Users->user_basic_details($rows['payment_paid_by']);
			//$row['paid_by_user_code']=$paid_user['user_code'];
			//$row['paid_by_user_name']=$paid_user['user_name'];
			//$row['paid_on_datetime']=dateTimeFromDbTimestamp($rows['payment_paid_on']);

			if(($rows['payment_category']=='DEDUCTION' || $rows['payment_category']=='EARNING' || $rows['payment_category']=='REIMBURSEMENT') && $row['amount_paid']=='0'){
				$row['edit_earning_and_deduction']=true;
			}else{
				$row['edit_earning_and_deduction']=false;
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

	function driver_payments_list($param){
		$r=[];
		$r['status']=false;
		$r['message']=null;
		$r['response']=null;		
		if(isset($param['driver_eid']) && isset($param['driver_eid'])!=""){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$param['driver_id']=$Enc->safeurlde($param['driver_eid']);
			$r=$this->drivers_payments_list($param);
		}
		return $r;				
	}

	function drivers_payments_paid_list($param){
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
		
		
		$q="SELECT  `payment_paid_id`, `payment_paid_payment_id_fk`, `payment_paid_amount`, `payment_paid_on`, `payment_paid_by`, `payment_paid_transaction_id_fk`, `payment_paid_status` FROM `driver_payments_paid` LEFT JOIN `driver_payments` ON `driver_payments`.`payment_id`=`driver_payments_paid`.`payment_paid_payment_id_fk` WHERE `payment_paid_status`='ACT'";


//----Apply Filters starts
		if(isset($param['transaction_id']) && $param['transaction_id']!=""){
			$transaction_id=mysqli_real_escape_string($GLOBALS['con'],$param['transaction_id']);
			$q.=" AND `payment_paid_transaction_id_fk`='$transaction_id'";
		}

		if(isset($param['payment_id']) && $param['payment_id']!=""){
			$payment_id=mysqli_real_escape_string($GLOBALS['con'],$param['payment_id']);
			$q.=" AND `payment_paid_payment_id_fk`='$payment_id'";
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q.=" AND `payment_driver_id_fk`='$driver_id'";
		}

//----//Apply Filters starts
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'id':
				$q .=" ORDER BY `payment_paid_id` ASC";
				break;
				case 'driver_code':
				$q .=" ORDER BY `driver_code` ASC";
				break;
				default:
				$q .=" ORDER BY `payment_paid_id` ASC";
				break;
			}
		}else{
			$q .=" ORDER BY `payment_paid_id` ASC";	
		}



		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

//--- if report view is send return all rows but set a limit
		if(isset($param['report_view']) && $param['report_view']==true){
			if($totalRows>5000){
				$message="Only 5000 or less rows can be exported at a time";
				goto invalidrange;
			}
		}else{
			$q .=" limit $from, $range";
		}

		


		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		$counter=$from;
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['sr_no']=++$counter;
			$row['id']=$rows['payment_paid_id'];
			$row['eid']=$Enc->safeurlen($rows['payment_paid_id']);
			$row['amount_paid']=($rows['payment_paid_amount']==null)?0:$rows['payment_paid_amount'];
			$row['payment_id']=$rows['payment_paid_payment_id_fk'];
			$row['transection_id']=$rows['payment_paid_transaction_id_fk'];
			$paid_user=$Users->user_basic_details($rows['payment_paid_by']);
			$row['paid_by_user_code']=$paid_user['user_code'];
			$row['paid_by_user_name']=$paid_user['user_name'];
			$row['paid_on_datetime']=dateTimeFromDbTimestamp($rows['payment_paid_on']);			


			/*$driver_name_first=($rows['driver_name_first']!="")?$rows['driver_name_first']:'';
			$driver_name_middle=($rows['driver_name_middle']!="")?' '.$rows['driver_name_middle']:'';
			$driver_name_last=($rows['driver_name_first']!="")?' '.$rows['driver_name_last']:'';
			$row['driver_name']=$driver_name_first.$driver_name_middle.$driver_name_last;
			$row['category']=$rows['payment_category'];
			$row['type']=$rows['payment_type'];
			$row['amount']=$rows['payment_amount'];
			
			$row['balance']=$row['amount']-$row['amount_paid'];
			$row['trip_id']=($rows['trip_id_fk']==null)?'':$rows['trip_id_fk'];
			$row['trip_eid']=($rows['payment_parameter_type_id_fk'])?'':$Enc->safeurlen($rows['trip_id_fk']);
			$row['parameter_name']=($rows['parameter_name']==null)?'':$rows['parameter_name'];
			//$row['status']=$rows['payment_pay_status'];
			$row['remarks']=$rows['payment_remarks'];
			$added_user=$Users->user_basic_details($rows['payment_added_by']);
			$row['added_by_user_code']=$added_user['user_code'];
			$row['added_by_user_name']=$added_user['user_name'];
			$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['payment_added_on']);
			*/


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
		$r['message']=$param;
		$r['response']=$response;
		return $r;	
	}


	function transaction_details($param){
		$r=[];
		$r['status']=false;
		$r['message']=null;
		$r['response']=null;		
		if(isset($param['eid']) && isset($param['eid'])!=""){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$param['transaction_id']=$Enc->safeurlde($param['eid']);
			$r=$this->drivers_payments_paid_list($param);
		}
		return $r;				
	}



	function all_drivers_payment_status($param){
		$status=false;
		$message=null;
		$response=[];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;



		//----fethc all drivers
		$drivers_q=mysqli_query($GLOBALS['con'],"SELECT `auto`, `driver_id`, `driver_code`,`driver_name_first`,`driver_settlement_status` FROM `drivers` WHERE `driver_status`='ACT' ORDER BY `driver_code`");
		$list=[];
		//---------loop through drivers and fetch their incentives trip wise 
		while($drivers=mysqli_fetch_assoc($drivers_q)){
			$driver=[];
			$driver['driver_eid']=$Enc->safeurlen($drivers['driver_id']);
			$driver['driver_code']=$drivers['driver_code'];
			$driver['driver_name']=$drivers['driver_name_first'];
			$driver['driver_settlement_status']=$drivers['driver_settlement_status'];


			//-------------get details
			$get_payable_total=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT SUM(ROUND(`payment_amount`,2)) AS `total_payable` FROM `driver_payments` WHERE `payment_status`='ACT' AND `payment_driver_id_fk`='".$drivers['driver_id']."'"));
			$get_paid_total=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT SUM(ROUND(`payment_paid_amount`,2)) AS `total_paid` FROM `driver_payments_paid` LEFT JOIN `driver_payments` ON `driver_payments`.`payment_id`=`driver_payments_paid`.`payment_paid_payment_id_fk` WHERE  `payment_paid_status`='ACT' AND `payment_driver_id_fk`='".$drivers['driver_id']."'"));			
			$driver['total_payable']=($get_payable_total['total_payable']==NULL)?0:$get_payable_total['total_payable'];
			$driver['total_paid']=($get_paid_total['total_paid']==NULL)?0:$get_paid_total['total_paid'];


			$driver['balance']=ROUND(($driver['total_payable']-$driver['total_paid']),2);

			array_push($list, $driver);
			
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

	function all_drivers_payble_list($param){
		$status=false;
		$message=null;
		$response=[];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;



		//----fethc all drivers
		$drivers_q=mysqli_query($GLOBALS['con'],"SELECT `auto`, `driver_id`, `driver_code`,`driver_settlement_status`,`driver_name_first` FROM `drivers` WHERE `driver_status`='ACT' ORDER BY `driver_code`");
		$list=[];
		//---------loop through drivers and fetch their incentives trip wise 
		while($drivers=mysqli_fetch_assoc($drivers_q)){
			$driver=[];
			$driver['driver_eid']=$Enc->safeurlen($drivers['driver_id']);
			$driver['driver_code']=$drivers['driver_code'];
			$driver['driver_name']=$drivers['driver_name_first'];
			$driver['driver_settlement_status']=$drivers['driver_settlement_status'];


			$fetch_payments_q="SELECT `payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`, `payment_amount`,`payment_added_by`,(SELECT SUM(ROUND(`payment_paid_amount`,2)) AS `total_paid` FROM `driver_payments_paid` WHERE  `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`=`payment_id`) AS `totol_paid`, `trip_id_fk`, `payment_trip_driver_id_fk` FROM `driver_payments` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`driver_payments`.`payment_trip_detail_id_fk` WHERE `payment_status`='ACT' AND `payment_driver_id_fk`='".$drivers['driver_id']."'";

			$fetch_payments_qEx=mysqli_query($GLOBALS['con'],$fetch_payments_q);
			$driver['results']=mysqli_num_rows($fetch_payments_qEx);
			$payments_list=[];
			while ($payments_rows=mysqli_fetch_assoc($fetch_payments_qEx)) {
				$payments_row=[];
				$payments_row['payment_eid']=$Enc->safeurlen($payments_rows['payment_id']);
				$payments_row['trip_id']=($payments_rows['trip_id_fk']==null)?'':$payments_rows['trip_id_fk'];
				$payments_row['amount']=$payments_rows['payment_amount'];
				$payments_row['amount_paid']=($payments_rows['totol_paid']==null)?'0':$payments_rows['totol_paid'];
				$payments_row['balance']=round($payments_row['amount']-$payments_row['amount_paid'],3);
				$payments_row['category']=$payments_rows['payment_category'];
				$payments_row['type']=$payments_rows['payment_type'];
				include_once APPROOT.'/models/masters/Users.php';
				$Users=new Users;
				$added_user=$Users->user_basic_details($payments_rows['payment_added_by']);
				$payments_row['added_by_user_code']=$added_user['user_code'];
				$payments_row['added_by_user_name']=$added_user['user_name'];

				if($payments_row['balance']!=0){
					array_push($payments_list, $payments_row);
				}
				
			}
			$driver['payments_list']=$payments_list;
			if(count($payments_list)>0){
				array_push($list, $driver);
			}

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


	function group_transaction_details($param){
		$status=false;
		$message=null;
		$response=null;

		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$group_transaction_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$group_transaction_id=$Enc->safeurlde($param['details_for_eid']);
				}else{
					$message="Please enter details_for_eid";
				}
				break;	


				default:
				$message="Please provide valid details_for parameter";
				break;
			}

		}else{
			$dataValidation=false;
			$InvalidDataMessage="Please provide details_for parameter";
			goto ValidationChecker;
		}


		ValidationChecker:

		if($dataValidation){

	///---------fetch transaction for the group transaction
			$details=[];
			$transactions_list=[];
			$get_transactions_list_q=mysqli_query($GLOBALS['con'],"SELECT `transaction_id`, `transaction_group_id_fk`, `transaction_amount`, `transaction_driver_id_fk`, `transaction_payment_mode_id_fk`, `transaction_reference`, `transaction_added_on`, `transaction_added_by`, `transaction_status`,`driver_id`,`driver_name_first`,`driver_code`,`mode_name` FROM `driver_transactions` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_transactions`.`transaction_driver_id_fk` LEFT JOIN `payment_modes` ON `payment_modes`.`mode_id`=`driver_transactions`.`transaction_payment_mode_id_fk` WHERE `transaction_status`='ACT' AND `transaction_group_id_fk`='$group_transaction_id'");
			while($rows=mysqli_fetch_assoc($get_transactions_list_q)){
				$row=[];
				$row['id']=$rows['transaction_id'];
				$row['eid']=$Enc->safeurlen($rows['transaction_id']);
				$row['driver_code']=$rows['driver_code'];
				$row['driver_name']=$rows['driver_name_first'];
				$row['amount']=$rows['transaction_amount'];
				$row['transaction_reference']=$rows['transaction_reference'];
				$row['payment_mode']=$rows['mode_name'];
				include_once APPROOT.'/models/masters/Users.php';
				$Users=new Users;
				$added_user=$Users->user_basic_details($rows['transaction_added_by']);
				$row['added_by_user_code']=$added_user['user_code'];
				$row['added_by_user_name']=$added_user['user_name'];
				$row['added_on_date']=dateFromDbToFormat($rows['transaction_added_on']);
				$row['added_on_time']=date('H:i',$rows['transaction_added_on']);
				$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['transaction_added_on']);
				array_push($transactions_list, $row);
				
			}
			$details['transactions-list']=$transactions_list;
			$response['details']=$details;

		}


		

		
		$status=true;

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	


	}


	function monthy_incentives_all_drivers($param){
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
		if(isset($param['trips_month']) && isset($param['trips_month'])!=""){
			$trips_month=mysqli_real_escape_string($GLOBALS['con'],$param['trips_month']);
	//$q.=" AND DATE_FORMAT(`trip_start_date`, '%M-%y')='$trips_month'";
		}else{
			$InvalidDataMessage="Please proved trips month";
			$dataValidation=false;
			goto ValidationChecker;
		}



		ValidationChecker:
		if($dataValidation){


		//----fethc all drivers
			$drivers_q=mysqli_query($GLOBALS['con'],"SELECT `auto`, `driver_id`, `driver_code`,`driver_name_first` FROM `drivers` WHERE `driver_status`='ACT' ORDER BY `driver_code`");
			$list=[];
		//---------loop through drivers and fetch their incentives trip wise 
			while($drivers=mysqli_fetch_assoc($drivers_q)){
				$driver=[];
				$driver['driver_eid']=$Enc->safeurlen($drivers['driver_id']);
				$driver['driver_code']=$drivers['driver_code'];
				$driver['driver_name']=$drivers['driver_name_first'];

				$fetch_incentives_q="SELECT DATE_FORMAT(`trip_end_date`, '%M-%y') AS `incentive_month`,`trip_driver_incentives`,`trip_id_fk`,trip_driver_id FROM `trip_drivers` LEFT JOIN `trip_details` ON `trip_details`.`trip_detail_id`=`trip_drivers`.`trip_driver_trip_detail_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_details`.`trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_driver_incentives_status`='$incentive_status' AND `trip_driver_driver_id_fk`='".$drivers['driver_id']."' AND DATE_FORMAT(`trip_end_date`, '%M-%y')='$trips_month'";

				$fetch_incentives_qEx=mysqli_query($GLOBALS['con'],$fetch_incentives_q);
				$incentives_list=[];
				while ($incentive_rows=mysqli_fetch_assoc($fetch_incentives_qEx)) {
					$incentive_row=[];
					$incentive_row['incentive_eid']=$Enc->safeurlen($incentive_rows['trip_driver_id']);
					$incentive_row['trip_id']=$incentive_rows['trip_id_fk'];
					$incentive_row['trip_eid']=$Enc->safeurlen($incentive_rows['trip_id_fk']);;
					$incentive_row['amount']=ROUND($incentive_rows['trip_driver_incentives'],2);
					$incentive_row['month']=$incentive_rows['incentive_month'];
					array_push($incentives_list, $incentive_row);
				}
				$driver['incentives_list']=$incentives_list;
				array_push($list, $driver);
			}

			
			$response['list']=$list;
			if(count($list)>0){
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













	function make_drivers_group_transaction($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0125', USER_PRIV)){
			if(isset($param['transactions_array'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$USERID=USER_ID;
				$time=time();

				$dataValidation=true;
				$InvalidDataMessage="";
//----- check if payment array is empty
				if(is_array($param['transactions_array'])){

				}else{
					$dataValidation=false;
					$InvalidDataMessage="Please provide transaction list in array format";
					goto ValidationChecker;	
				}

//---------loop through payment array sent for
				$senetized_array=[];
				foreach ($param['transactions_array'] as $pA) {
					//---check if driver eid is send or not
					if(isset($pA['driver_eid'])){
						$driver_id=$Enc->safeurlde($pA['driver_eid']);
					}else{
						$dataValidation=false;
						$InvalidDataMessage="Driver eid is missing for one or more transactions";
						goto ValidationChecker;

					}

//-----------check if the settlement status is on
					if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `driver_id` FROM `drivers` WHERE `driver_settlement_status`='ON' AND `driver_id`='$driver_id'"))!=1){
						$dataValidation=false;
						$InvalidDataMessage="Settlement status is OFF for one or more Drivers";
						goto ValidationChecker;

					}


					//---check if payments list  is send or not

					if(isset($pA['payments_list'])){
						$payments_list=$pA['payments_list'];
						if(!is_array($payments_list)){
							$dataValidation=false;
							$InvalidDataMessage="Please provide payments list in array form";
							goto ValidationChecker;						
						}
					}else{
						$dataValidation=false;
						$InvalidDataMessage="Please provide payments list";
						goto ValidationChecker;

					}

					if(isset($pA['payments_mode_id']) && $pA['payments_mode_id']!=""){
						$payments_mode_id=$pA['payments_mode_id'];
						include_once APPROOT.'/models/masters/PaymentModes.php';
						$PaymentModes=new PaymentModes;
						if(!$PaymentModes->isValidId($payments_mode_id)){
							$InvalidDataMessage="Invalid payment mode id";
							$list_valid=false;
							goto ValidationChecker;
						}
					}else{
						$dataValidation=false;
						$InvalidDataMessage="Please provide payment mode id";
						goto ValidationChecker;

					}

					$payments_notes="";
					if(isset($pA['payments_notes'])){
						$payments_notes=mysqli_real_escape_string($GLOBALS['con'],$pA['payments_notes']);
					}



					//----------loop through sent payment list
					$transaction_amount=0;

					$payments_list_senetized=[];
					foreach ($payments_list as $pl) {

						if(isset($pl['amount_paynow'])){
							if(!is_numeric($pl['amount_paynow'])){
								$dataValidation=false;
								$InvalidDataMessage="Please provide valid paynow amount";
								goto ValidationChecker;							
							}

						}else{
							$dataValidation=false;
							$InvalidDataMessage="Payment eid is missing for one or more entries";
							goto ValidationChecker;					
						}
						if(isset($pl['payment_eid'])){
							$payment_id=$Enc->safeurlde($pl['payment_eid']);



							$payment_details_q=mysqli_query($GLOBALS['con'],"SELECT `payment_id`,`payment_type`,`payment_amount`,(SELECT SUM(ROUND(`payment_paid_amount`,2)) AS `total_paid` FROM `driver_payments_paid` WHERE  `payment_paid_status`='ACT' AND `payment_paid_payment_id_fk`=`payment_id`) AS `totol_paid` FROM `driver_payments` WHERE `payment_id`='$payment_id' AND `payment_status`='ACT'");

							if(mysqli_num_rows($payment_details_q)==1){
								$payment_details= mysqli_fetch_assoc($payment_details_q);

								$payments_row['amount']=$payment_details['payment_amount'];
								$payments_row['amount_paid']=($payment_details['totol_paid']==null)?'0':$payment_details['totol_paid'];


							//----Restrict over paid or over deduction
								$balance=round(($payments_row['amount']-$payments_row['amount_paid']),2);

								if($balance>=0){
									if($pl['amount_paynow']<0 || $pl['amount_paynow']>$balance){
										$dataValidation=false;
										$InvalidDataMessage="Paying amount should not exceed balance A".$balance.$pl['amount_paynow'];
										goto ValidationChecker;						
									}								
								}else{
									if($pl['amount_paynow']>0 || $pl['amount_paynow']<$balance){
										$dataValidation=false;
										$InvalidDataMessage="Paying amount should not exceed balance B".$balance.$pl['amount_paynow'];
										goto ValidationChecker;						
									}								
								}				


								$transaction_amount+=$pl['amount_paynow'];
								if($payments_list)
									array_push($payments_list_senetized,array('payment_id' => $payment_details['payment_id'],'amount_paynow'=> $pl['amount_paynow']));
							}

						}else{
							$dataValidation=false;
							$InvalidDataMessage="Payment eid is missing for one or more entries";
							goto ValidationChecker;	
						}



						


					}

					if(count($payments_list_senetized)>0){
						$item=[];
						$item['amount']=$transaction_amount;
						$item['driver_id']=$driver_id;
						$item['payments_list']=$payments_list_senetized;
						$item['payments_notes']=$payments_notes;
						$item['payments_mode_id']=$payments_mode_id;
						if($transaction_amount>0){
							array_push($senetized_array, $item);
						}else{
							$dataValidation=false;
							$InvalidDataMessage="Transaction Sum can't be less than 0".$transaction_amount;
							goto ValidationChecker;									
						}


					}



				}


				ValidationChecker:

				if($dataValidation){



					$make_transaction=true;
					$insert_payment_paid_status=true;

					//-------make an entry in drivers transaction group table
					$get_driver_transaction_group_id=mysqli_query($GLOBALS['con'],"SELECT `transaction_group_id` FROM `driver_transactions_groups` ORDER BY `auto` DESC LIMIT 1");
					$get_driver_transaction_group_id=(mysqli_num_rows($get_driver_transaction_group_id)==1)?(mysqli_fetch_assoc($get_driver_transaction_group_id)['transaction_group_id']):'00000000';

					$get_driver_transaction_group_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
					if($get_driver_transaction_group_id_prefix==substr($get_driver_transaction_group_id,0,6)){
						$new_driver_transaction_group_id=$get_driver_transaction_group_id_prefix.sprintf('%04d',(intval(substr($get_driver_transaction_group_id,6))));
					}else{
						$new_driver_transaction_group_id=$get_driver_transaction_group_id_prefix.'0000';
					}
					$new_driver_transaction_group_id++;
					$gross_total=ROUND(array_sum(array_column($senetized_array, 'amount')),2);
					$make_transaction_group=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_transactions_groups`( `transaction_group_id`, `transaction_group_added_on`, `transaction_group_added_by`, `transaction_group_status`) VALUES ('$new_driver_transaction_group_id','$time','$USERID','ACT')");

					if($make_transaction_group){




						foreach ($senetized_array as $sa) {


					//-------make an entry in drivers transaction table
							$get_driver_transaction_id=mysqli_query($GLOBALS['con'],"SELECT `transaction_id` FROM `driver_transactions` ORDER BY `auto` DESC LIMIT 1");
							$get_driver_transaction_id=(mysqli_num_rows($get_driver_transaction_id)==1)?(mysqli_fetch_assoc($get_driver_transaction_id)['transaction_id']):'00000000';

							$get_driver_transaction_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
							if($get_driver_transaction_id_prefix==substr($get_driver_transaction_id,0,6)){
								$new_driver_transaction_id=$get_driver_transaction_id_prefix.sprintf('%04d',(intval(substr($get_driver_transaction_id,6))));
							}else{
								$new_driver_transaction_id=$get_driver_transaction_id_prefix.'0000';
							}
							$new_driver_transaction_id++;
							$make_transaction=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_transactions`(`transaction_id`,`transaction_group_id_fk`, `transaction_driver_id_fk`, `transaction_payment_mode_id_fk`, `transaction_reference`,`transaction_added_on`, `transaction_added_by`,  `transaction_status`) VALUES ('$new_driver_transaction_id','$new_driver_transaction_group_id','".$sa['driver_id']."','".$sa['payments_mode_id']."','".$sa['payments_notes']."','$time','$USERID','ACT')");

						//-------if transaction created successfuly, now loop through payment array approve the same aginst transaction id

							if($make_transaction){


						///---------insert driver payment entry
								$get_driver_payment_paid_id=mysqli_query($GLOBALS['con'],"SELECT `payment_paid_id` FROM `driver_payments_paid` ORDER BY `auto` DESC LIMIT 1");
								$get_driver_payment_paid_id=(mysqli_num_rows($get_driver_payment_paid_id)==1)?(mysqli_fetch_assoc($get_driver_payment_paid_id)['payment_paid_id']):'00000000';

								$get_driver_payment_paid_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
								if($get_driver_payment_paid_id_prefix==substr($get_driver_payment_paid_id,0,6)){
									$new_driver_payment_paid_id=$get_driver_payment_paid_id_prefix.sprintf('%04d',(intval(substr($get_driver_payment_paid_id,6))+1));
								}else{
									$new_driver_payment_paid_id=$get_driver_payment_paid_id_prefix.'0000';
								}


								foreach ($sa['payments_list'] as $pl) {
									$new_driver_payment_paid_id++;
									$insert_payment_paid=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments_paid`(`payment_paid_id`, `payment_paid_payment_id_fk`, `payment_paid_amount`, `payment_paid_on`, `payment_paid_by`, `payment_paid_transaction_id_fk`, `payment_paid_status`) VALUES ('$new_driver_payment_paid_id','".$pl['payment_id']."','".$pl['amount_paynow']."','$time','$USERID','$new_driver_transaction_id','ACT')");
									if(!$insert_payment_paid){
										$insert_payment_paid_status=false;
										$message=SOMETHING_WENT_WROG.'C';
									}
								}
							}else{
								$make_transaction=false;
							}

						}

						if($make_transaction && $insert_payment_paid_status){
							$status=true;
							$message="Transaction made successfuly";
						}else{
							$message=SOMETHING_WENT_WROG.'B';
						}

					}else{
						$message=SOMETHING_WENT_WROG.'A';
					}



				}else{
					$message=$InvalidDataMessage;
				}



			}else{
				$message="Please provide payments list";
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





























/*	function make_drivers_group_transaction($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){
			if(isset($param['payments_array'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$USERID=USER_ID;
				$time=time();

				$dataValidation=true;
				$InvalidDataMessage="";
//----- check if payment array is empty
				if(is_array($param['payments_array'])){

				}else{
					$dataValidation=false;
					$InvalidDataMessage="Please provide payments list in array format";
					goto ValidationChecker;	
				}

//---------loop through payment array sent for
				$senetized_array=[];
				$total_transaction_amount=0;
				foreach ($param['payments_array'] as $pA) {
					$payment_id=$Enc->safeurlde($pA['payment_eid']);
					$payment_details_q=mysqli_query($GLOBALS['con'],"SELECT `payment_id`,`payment_type`,`payment_amount` FROM `driver_payments` WHERE `payment_id`='$payment_id' AND `payment_pay_status`='UNPAID' AND `payment_status`='ACT'");
					if(mysqli_num_rows($payment_details_q)==1){
						$payment_details= mysqli_fetch_assoc($payment_details_q);
						array_push($senetized_array, $payment_id);

						if($payment_details['payment_type']=='DR'){
							$total_transaction_amount=$total_transaction_amount-($payment_details['payment_amount']);
						}else{
							$total_transaction_amount=$total_transaction_amount+($payment_details['payment_amount']);							
						}
						
					}else{
						$dataValidation=false;
						$InvalidDataMessage="One or more invalid payment eid";
						goto ValidationChecker;
					}

				}


				ValidationChecker:

				if($dataValidation){

					//-------make an entry in drivers transaction table
					$get_driver_transaction_id=mysqli_query($GLOBALS['con'],"SELECT `transaction_id` FROM `driver_transactions` ORDER BY `auto` DESC LIMIT 1");
					$get_driver_transaction_id=(mysqli_num_rows($get_driver_transaction_id)==1)?(mysqli_fetch_assoc($get_driver_transaction_id)['transaction_id']):'00000000';

					$get_driver_transaction_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
					if($get_driver_transaction_id_prefix==substr($get_driver_transaction_id,0,6)){
						$new_driver_transaction_id=$get_driver_transaction_id_prefix.sprintf('%04d',(intval(substr($get_driver_transaction_id,6))+1));
					}else{
						$new_driver_transaction_id=$get_driver_transaction_id_prefix.'0000';
					}
					$new_driver_transaction_id++;
					$make_transaction=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_transactions`(`transaction_id`, `transaction_amount`,`transaction_added_on`, `transaction_added_by`,  `transaction_status`) VALUES ('$new_driver_transaction_id','$total_transaction_amount','$time','$USERID','ACT')");

						//-------if transaction created successfuly, now loop through payment array approve the same aginst transaction id
					$update=true; 
					if($make_transaction){
						foreach ($senetized_array as $sa) {
							$update=mysqli_query($GLOBALS['con'],"UPDATE `driver_payments` SET `payment_pay_status`='PAID',`payment_paid_on`='$time',`payment_paid_by`='$USERID',`payment_driver_transaction_id_fk`='$new_driver_transaction_id' WHERE `payment_id`='$sa'");
							if(!$update){
								$message=SOMETHING_WENT_WROG;
							}
						}
					}


					if($make_transaction && $update){
						$status=true;
						$message="Transaction made successfuly";
					}else{
						$message=SOMETHING_WENT_WROG;
					}


				}else{
					$message=$InvalidDataMessage;
				}



			}else{
				$message="Please provide payments list";
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





	/*function balance_status_all_drivers($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q=mysqli_query($GLOBALS['con'],"SELECT `driver_id`,`driver_code`,`driver_name_first`,sum(ROUND(`transection_amount_cr`,2)) AS `total_cr`,sum(ROUND(`transection_amount_dr`,2)) AS `total_dr`,sum(ROUND(`transection_amount_cr`,2))-sum(ROUND(`transection_amount_dr`,2)) AS `balance` FROM `driver_statement`  LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_statement`.`transection_driver_id_fk` WHERE `transection_status`='ACT' GROUP BY`transection_driver_id_fk`");
		$list=[];
		while ($rows=mysqli_fetch_assoc($q)) {
			$row=[];
			$row['driver_eid']=$Enc->safeurlen($rows['driver_id']);
			$row['driver_code']=$rows['driver_code'];
			$row['driver_name']=$rows['driver_name_first'];
			$row['payble']=$rows['total_cr'];
			$row['paid']=$rows['total_dr'];
			$row['balance']=$rows['balance'];
			array_push($list, $row);
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
	}*/
	
	


	
	/*function monthy_incentives_all_drivers($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		
		$incentive_status='HOLD';
		if(isset($param['status']) && $param['status']=='MOVED'){
			$incentive_status='MOVED';
		}
		
		$q="SELECT `driver_id`,`driver_code`,`driver_name_first`,`trip_driver_incentives`,DATE_FORMAT(`trip_start_date`, '%M-%y') AS `incentive_month`,`trip_driver_incentives_status`,`trip_id` FROM `trip_drivers`  LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_driver_incentives_status`='$incentive_status'";
if(isset($param['trips_month']) && isset($param['trips_month'])!=""){
	$trips_month=mysqli_real_escape_string($GLOBALS['con'],$param['trips_month']);
	$q.=" AND DATE_FORMAT(`trip_start_date`, '%M-%y')='$trips_month'";
}
		$q.="  ORDER BY `driver_code`";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['driver_eid']=$Enc->safeurlen($rows['driver_id']);
			$row['driver_code']=$rows['driver_code'];
			$row['driver_name']=$rows['driver_name_first'];
			$row['trip_id']=$rows['trip_id'];
			$row['amount']=$rows['trip_driver_incentives'];
			$row['month']=$rows['incentive_month'];
			$row['status']=$rows['trip_driver_incentives_status'];
			array_push($list, $row);
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
*/

	/*
	function driver_incentives_statement($param){
		$status=false;
		$message=null;
		$response=null;
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
			$q="SELECT `trip_driver_incentives`,`trip_id`,`trip_driver_incentives_status` FROM `trip_drivers`  LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_driver_driver_id_fk`='$driver_id' AND `trip_approval_status_id_fk`='APPROVED'";


			if(isset($param['sort_by'])){
				switch ($param['sort_by']) {
					case 'trip_id':
					$q .=" ORDER BY `trip_id` DESC";
					break;	
					default:
					$q .=" ORDER BY `trip_id` DESC";
					break;
				}
			}else{
				$q .=" ORDER BY `trip_id` DESC";	
			}



			$qEx=mysqli_query($GLOBALS['con'],$q);
			$list=[];
			while ($rows=mysqli_fetch_assoc($qEx)) {
				$row=[];
				$row['trip_id']=$rows['trip_id'];
				$row['trip_driver_incentives']=$rows['trip_driver_incentives'];
				$row['status']=$rows['trip_driver_incentives_status'];
				array_push($list, $row);
			}

			$response=[];
			$response['list']=$list;
			if(count($list)>0){
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
*/
/*
	function monthy_incentives_all_drivers_move($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0128', USER_PRIV)){
			if(isset($param['move_incentive_array'])){
				$move_incentive_array=$param['move_incentive_array'];
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;


				$USERID=USER_ID;
				$time=time();


				$moved=true;
				$added_to_driver_statement=true;
				foreach ($move_incentive_array as $al) {

					$driver_id=$Enc->safeurlde($al['driver_eid']);
					$month=$al['month'];
						///---fetch all the matching records
					$fetch_matching=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, DATE_FORMAT(`trip_start_date`, '%M-%y') AS `incentive_month` FROM `trip_drivers`  LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_driver_incentives_status`='HOLD' AND `trip_driver_driver_id_fk`='$driver_id' AND DATE_FORMAT(`trip_start_date`, '%M-%y')='$month'");
						///--/fethc all the matching records

					while ($matching=mysqli_fetch_assoc($fetch_matching)) {
						$trip_driver_id=$matching['trip_driver_id'];
						$move=mysqli_query($GLOBALS['con'],"UPDATE `trip_drivers` SET `trip_driver_incentives_status`='MOVED' WHERE `trip_driver_id`='$trip_driver_id'");
						if(!$move){
							$moved=false;
						}
					}


					//---move incentive to driver satement
					$get_month_incentive= mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT sum(ROUND(`trip_driver_incentives`,2)) AS `total_incentives` FROM `trip_drivers`  LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_approval_status_id_fk`='APPROVED' AND `trip_driver_incentives_status`='MOVED' AND DATE_FORMAT(`trip_start_date`, '%M-%y')='$month' AND `trip_driver_driver_id_fk`='$driver_id'"));
										///---------insert transection
					$get_driver_old_statement_record_q=mysqli_query($GLOBALS['con'],"SELECT `transection_id` FROM `driver_statement` ORDER BY `auto` DESC LIMIT 1");
					$get_driver_old_statement_record=mysqli_fetch_assoc($get_driver_old_statement_record_q);
					$get_driver_transection_id=(mysqli_num_rows($get_driver_old_statement_record_q)==1)?$get_driver_old_statement_record['transection_id']:'00000000';

					$get_driver_transection_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
					if($get_driver_transection_id_prefix==substr($get_driver_transection_id,0,6)){
						$new_driver_transection_id=$get_driver_transection_id_prefix.sprintf('%04d',(intval(substr($get_driver_transection_id,6))+1));
					}else{
						$new_driver_transection_id=$get_driver_transection_id_prefix.'0000';
					}


					$driver_old_balance_check_q=mysqli_query($GLOBALS['con']," SELECT transection_balance FROM `driver_statement` WHERE `transection_driver_id_fk`='$driver_id' ORDER BY `auto` DESC LIMIT 1 ");
					$driver_old_balance=(mysqli_num_rows($driver_old_balance_check_q)==1)?(mysqli_fetch_assoc($driver_old_balance_check_q)['transection_balance']):0;



					$driver_new_balance=$driver_old_balance+$get_month_incentive['total_incentives'];
					$insert_driver_statement=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_statement`(`transection_id`,`transection_driver_id_fk`, `transection_type`, `transection_description`, `transection_amount_cr`,`transection_amount_dr`,`transection_balance`, `transection_added_on`, `transection_status`, `transection_trip_id_fk`) VALUES ('$new_driver_transection_id','$driver_id','CR','Incentive of  $month','".$get_month_incentive['total_incentives']."','0','$driver_new_balance','$time','ACT','')");
					if(!$insert_driver_statement){
						$added_to_driver_statement=false;
					}
				}

				if($moved && $added_to_driver_statement){
					$status=true;
					$message="Moved to driver statement successfuly";
				}else{
					$message=SOMETHING_WENT_WROG;
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

	}*/

	function move_trips_incentive($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0128', USER_PRIV)){
			if(isset($param['move_incentive_array'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$USERID=USER_ID;
				$time=time();
				$executionMessage="";
				$execution=true;
				foreach ($param['move_incentive_array'] as $al) {
					$trip_driver_id=$Enc->safeurlde($al['incentive_eid']);

					//----fetch details of incentive for the trip_driver_id;
					$get_trip_driver_details_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_detail_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_incentives` FROM `trip_drivers` WHERE `trip_driver_incentives_status`='HOLD' AND `trip_driver_id`='$trip_driver_id' AND `trip_driver_status`='ACT'");
					if(mysqli_num_rows($get_trip_driver_details_q)==1){
						$trip_driver_details=mysqli_fetch_assoc($get_trip_driver_details_q);
						$trip_driver_id=$trip_driver_details['trip_driver_id'];
						$trip_driver_driver_id_fk=$trip_driver_details['trip_driver_driver_id_fk'];
						$trip_driver_trip_detail_id=$trip_driver_details['trip_driver_trip_detail_id_fk'];
						$trip_driver_incentives=$trip_driver_details['trip_driver_incentives'];
						
						//--update status from HOLD to MOVED
						$update_status=mysqli_query($GLOBALS['con'],"UPDATE `trip_drivers` SET `trip_driver_incentives_status`='MOVED',`trip_driver_incentives_moved_on`='$time',`trip_driver_incentives_moved_by`='$USERID' WHERE `trip_driver_id`='$trip_driver_id'");


						if(!$update_status){
							$executionMessage=SOMETHING_WENT_WROG.' step 01';
							$execution=false;
							goto executionChecker;			
						}


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
						$new_driver_payment_id++;


						$insert_driver_statement=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments`(`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`, `payment_added_on`, `payment_added_by`, `payment_trip_detail_id_fk`, `payment_trip_driver_id_fk`,`payment_status`) VALUES ('$new_driver_payment_id','$trip_driver_driver_id_fk','TRIP-INCENTIVE','CR','$trip_driver_incentives','$time','$USERID','$trip_driver_trip_detail_id','$trip_driver_id','ACT')");
						if(!$insert_driver_statement){
							$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);
							$execution=false;
							goto executionChecker;			
						}
					}
				}
				executionChecker:
				if($execution){
					$status=true;
					$message="Moved to driver statement successfuly";
				}else{
					$message=$executionMessage;
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



/*
	function driver_statement($param){
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

			$q="SELECT `transection_id`, `driver_name_first`, `driver_name_middle`, `driver_name_last`,`driver_code`, `transection_type`, `transection_description`, `transection_amount_cr`, `transection_amount_dr`, `transection_balance`, `transection_added_on` FROM `driver_statement` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`driver_statement`.`transection_driver_id_fk` WHERE `transection_status`='ACT' AND `transection_driver_id_fk`='$driver_id'";

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
				$row['date']=date('Y-m-d',$rows['transection_added_on']);
				$row['id']=$rows['transection_id'];
				$row['type']=$rows['transection_type'];
				$row['description']=$rows['transection_description'];
				$row['amount_cr']=$rows['transection_amount_cr'];
				$row['amount_dr']=$rows['transection_amount_dr'];
				$row['balance']=$rows['transection_balance'];
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
				$message="No records found id";
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

*/
/*

	function make_group_payments($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){
			if(isset($param['group_eid_list'])){
				$group_eid_list=$param['group_eid_list'];
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;					
				$USERID=USER_ID;
				$time=time();
				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;
			//--check if the  approval list is valid
				$list_valid=true;
				$payment_array=[];
				foreach ($group_eid_list as $al) {
					//-----validate driver id in all list items
					if(isset($al['driver_eid'])){
						$driver_id=$Enc->safeurlde($al['driver_eid']);
						if(!$Drivers->isValidId($driver_id)){
							$InvalidDataMessage="Invalid driver eid";
							$list_valid=false;
							goto ValidationChecker;
						}


					}else{
						$InvalidDataMessage="Driver eid missing in one or more list items";
						$list_valid=false;
						goto ValidationChecker;						
					}
					//-----/validate driver id in all list items

					//-----validate driver payment in all list items
					if(isset($al['payment'])){
						if(!preg_match("/^[0-9.]{1,}$/",$al['payment'])){
							$InvalidDataMessage="Invalid driver payment in one or more list items";
							$list_valid=false;
							goto ValidationChecker;
						}


					}else{
						$InvalidDataMessage="Invalid driver payment missing in one or more list items";
						$list_valid=false;
						goto ValidationChecker;						
					}
					//-----//validate driver payment in all list items
					array_push($payment_array, array('driver_id' =>$driver_id ,'payment'=> $al['payment']));

				}


				ValidationChecker:
				if($list_valid){


					///---------insert transection
					$get_driver_transection_id=mysqli_query($GLOBALS['con'],"SELECT `transection_id`,`transection_balance` FROM `driver_statement` ORDER BY `auto` DESC LIMIT 1");
					$get_driver_transection_id=(mysqli_num_rows($get_driver_transection_id)==1)?(mysqli_fetch_assoc($get_driver_transection_id)['transection_id']):'00000000';

					$get_driver_transection_id_prefix=date("ymd");
					//---if last trip id is from old month than change the prefix with current month and start counting from 1
					if($get_driver_transection_id_prefix==substr($get_driver_transection_id,0,6)){
						$new_driver_transection_id=$get_driver_transection_id_prefix.sprintf('%04d',(intval(substr($get_driver_transection_id,6))+1));
					}else{
						$new_driver_transection_id=$get_driver_transection_id_prefix.'0000';
					}

					$payment_added=true;
					foreach ($payment_array as $al) {
						if($al['payment']!=0){
							$new_driver_transection_id++;
							$driver_old_balance_check=mysqli_query($GLOBALS['con']," SELECT transection_balance FROM `driver_statement` WHERE `transection_driver_id_fk`='".$al['driver_id']."' ORDER BY `auto` DESC LIMIT 1 ");
							$driver_old_balance=(mysqli_num_rows($driver_old_balance_check)==1)?mysqli_fetch_assoc($driver_old_balance_check)['transection_balance']:0;

							$driver_new_balance=$driver_old_balance-intval($al['payment']);

							$add_payment=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_statement`(`transection_id`,`transection_driver_id_fk`, `transection_type`, `transection_description`, `transection_amount_cr`, `transection_amount_dr`,`transection_balance`, `transection_added_on`,`transection_added_by`, `transection_status`) VALUES ('$new_driver_transection_id','".$al['driver_id']."','DR','Payment of trip','0','".$al['payment']."','$driver_new_balance','$time','$USERID','ACT')");
							if(!$add_payment){
								$payment_added=false;
							}

							if($payment_added){
								$status=true;
								$message="Payment added successfuly";
							}
						}
					}

				}else{
					$message=$InvalidDataMessage;
				}



			}else{
				$message="Please Provide group eid list";
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
	function add_earnings_and_deductions($param){
		$status=false;
		$message=null;
		$response=[];
		if(in_array('P0141', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;



			$dataValidation=true;
			$InvalidDataMessage="";
			if(isset($param['driver_eid'])){
				$driver_id=$Enc->safeurlde($param['driver_eid']);
				include_once APPROOT.'/models/masters/Drivers.php';
				$Drivers=new Drivers;

				if(!$Drivers->isValidId($driver_id)){
					$InvalidDataMessage="Invalid driver id";
					$dataValidation=false;
					goto ValidationChecker;
				}				
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide driver eid";
				goto ValidationChecker;					
			}

			if(isset($param['parameter_id'])){
				$parameter_id=$param['parameter_id'];

				include_once APPROOT.'/models/masters/SalaryParameters.php';
				$SalaryParameters=new SalaryParameters;

				if(!$SalaryParameters->isValidId($parameter_id)){
					$InvalidDataMessage="Invalid parameter id";
					$dataValidation=false;
					goto ValidationChecker;
				}


			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide parameter id";	
				goto ValidationChecker;			
			}

			if(isset($param['amount'])){
				if(is_numeric($param['amount'])){
					$amount=abs(round($param['amount'],2));
				}else{
					$InvalidDataMessage="Invalid salary parameter amount";
					$dataValidation=false;
					goto ValidationChecker;							
				}			
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide amount";
				goto ValidationChecker;				
			}						

			$remarks="";
			if(isset($param['remarks'])){
				$remarks=mysqli_real_escape_string($GLOBALS['con'],$param['remarks']);
			}

			ValidationChecker:
			if($dataValidation){
				$USERID=USER_ID;
				$time=time();
				//------get salary parameter type
				$parameter_type_q=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT `parameter_type_id_fk` FROM `salary_parameters` WHERE `parameter_status`='ACT' AND `parameter_id`='$parameter_id'"));

				$category=$parameter_type_q['parameter_type_id_fk'];
				//--------set type of parameter to be added base on the category
				switch ($category) {
					case 'EARNING':
					$type='CR';
					break;
					case 'DEDUCTION':
					$type='DR';
					break;
					case 'REIMBURSEMENT':
					$type='CR';
					break;												
					
					default:
					$type="";
					break;
				}

				$amount=($type=='DR')?'-'.$amount:$amount;

				//---------add parameter to driver payment

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


				$insert_driver_statement=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_payments`(`payment_id`, `payment_driver_id_fk`, `payment_category`, `payment_type`,`payment_amount`, `payment_added_on`, `payment_added_by`,  `payment_parameter_type_id_fk`,`payment_status`,`payment_remarks`) VALUES ('$new_driver_payment_id','$driver_id','$category','$type','$amount','$time','$USERID','$parameter_id','ACT','$remarks')");
				if($insert_driver_statement){
					$status=true;
					$message="Added successfuly";
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

	function earnings_and_deductions_details($param){
		$status=false;
		$message=null;
		$response=[];
		if(isset($param['eid'])){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$payment_id=$Enc->safeurlde($param['eid']);
			$q=mysqli_query($GLOBALS['con'],"SELECT `payment_amount`,`payment_parameter_type_id_fk`,`payment_remarks` FROM `driver_payments` WHERE `payment_id`='$payment_id' AND `payment_category` IN ('REIMBURSEMENT','EARNING','DEDUCTION') AND `payment_status`='ACT'");

			if(mysqli_num_rows($q)==1){
				$status=true;
				$result=mysqli_fetch_assoc($q);
				$row=[];
				$row['amount']=abs($result['payment_amount']);
				$row['parameter_id']=$result['payment_parameter_type_id_fk'];
				$row['remarks']=$result['payment_remarks'];
				$response['details']=$row;
			}else{
				$message="No records found";
			}

		}else{
			$message="Please provide eid";
		}



		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}

	function earnings_and_deductions_update($param){
		$status=false;
		$message=null;
		$response=[];
		if(in_array('P0142', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;



			$dataValidation=true;
			$InvalidDataMessage="";
			if(isset($param['update_eid'])){
				$update_id=$Enc->safeurlde($param['update_eid']);			
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide update eid";
				goto ValidationChecker;					
			}


		  //------------chekc if valid earning or deduction exists;
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `payment_id` FROM `driver_payments` WHERE `payment_id`='$update_id' AND `payment_category` IN ('REIMBURSEMENT','EARNING','DEDUCTION') AND `payment_status`='ACT'"))!=1){
				$dataValidation=false;
				$InvalidDataMessage="Please provide valid id";
				goto ValidationChecker;	
			}
			//------allow deletion only if no payment is paid/deduction for this payment

			//---get total number of payments made for this payments

			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `payment_paid_id` FROM `driver_payments_paid` WHERE `payment_paid_payment_id_fk`='$update_id'"))>0){
				$dataValidation=false;
				$InvalidDataMessage="This payments can't be updated as one or more payments has been made for this payment";
				goto ValidationChecker;				
			}



			if(isset($param['parameter_id'])){
				$parameter_id=$param['parameter_id'];

				include_once APPROOT.'/models/masters/SalaryParameters.php';
				$SalaryParameters=new SalaryParameters;

				if(!$SalaryParameters->isValidId($parameter_id)){
					$InvalidDataMessage="Invalid parameter id";
					$dataValidation=false;
					goto ValidationChecker;
				}


			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide parameter id";	
				goto ValidationChecker;			
			}

			if(isset($param['amount'])){
				if(is_numeric($param['amount'])){
					$amount=abs(round($param['amount'],2));
				}else{
					$InvalidDataMessage="Invalid salary parameter amount";
					$dataValidation=false;
					goto ValidationChecker;							
				}			
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide amount";
				goto ValidationChecker;				
			}						

			$remarks="";
			if(isset($param['remarks'])){
				$remarks=mysqli_real_escape_string($GLOBALS['con'],$param['remarks']);
			}

			ValidationChecker:
			if($dataValidation){
				$USERID=USER_ID;
				$time=time();
				//------get salary parameter type
				$parameter_type_q=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT `parameter_type_id_fk` FROM `salary_parameters` WHERE `parameter_status`='ACT' AND `parameter_id`='$parameter_id'"));

				$category=$parameter_type_q['parameter_type_id_fk'];
				//--------set type of parameter to be added base on the category
				switch ($category) {
					case 'EARNING':
					$type='CR';
					break;
					case 'DEDUCTION':
					$type='DR';
					break;
					case 'REIMBURSEMENT':
					$type='CR';
					break;												
					
					default:
					$type="";
					break;
				}

				//---------add parameter to driver payment

				$update=mysqli_query($GLOBALS['con'],"UPDATE `driver_payments` SET `payment_amount`='$amount',`payment_category`='$category',`payment_type`='$type',  `payment_parameter_type_id_fk`='$parameter_id',`payment_updated_on`='$time', `payment_updated_by`='$USERID',`payment_remarks`='$remarks' WHERE `payment_id`='$update_id'");
				if($update){
					$status=true;
					$message="Updated successfuly";
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


	function earnings_and_deductions_delete($param){
		$status=false;
		$message=null;
		$response=[];
		if(in_array('P0143', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;



			$dataValidation=true;
			$InvalidDataMessage="";
			if(isset($param['delete_eid'])){
				$delete_id=$Enc->safeurlde($param['delete_eid']);			
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide delete eid";
				goto ValidationChecker;					
			}


		  //------------chekc if valid earning or deduction exists;
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `payment_id` FROM `driver_payments` WHERE `payment_id`='$delete_id' AND `payment_category` IN ('REIMBURSEMENT','EARNING','DEDUCTION') AND `payment_status`='ACT'"))!=1){
				$dataValidation=false;
				$InvalidDataMessage="Please provide valid id";
				goto ValidationChecker;	
			}
			//------allow deletion only if no payment is paid/deduction for this payment

			//---get total number of payments made for this payments

			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `payment_paid_id` FROM `driver_payments_paid` WHERE `payment_paid_payment_id_fk`='$delete_id'"))>0){
				$dataValidation=false;
				$InvalidDataMessage="This payments can't be deleted as one or more payments has been made for this payment";
				goto ValidationChecker;				
			}



			ValidationChecker:
			if($dataValidation){
				$USERID=USER_ID;
				$time=time();

				//---------add parameter to driver payment

				$delete=mysqli_query($GLOBALS['con'],"UPDATE `driver_payments` SET `payment_deleted_on`='$time', `payment_deleted_by`='$USERID',`payment_status`='DEL' WHERE `payment_id`='$delete_id'");
				if($delete){
					$status=true;
					$message="Deleted successfuly";
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

}
?>