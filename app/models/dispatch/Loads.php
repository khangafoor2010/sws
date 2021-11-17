<?php

/**

 *

 */

class Loads

{

	function loads_list($param){

		$status=false;
		$message=null;
		$response=null;
		$batch=50;
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
		$q="SELECT `load_id`, `load_added_on`, `load_added_by`, `load_customer_id_fk`, `load_po`, `load_trailer_type`, `load_temperature_to_maintain`,`load_rate`,`load_bill_of_lading`, `load_added_on`, `load_added_by`,`customer_id`,`customer_code`,`customer_name` FROM `d_loads` LEFT JOIN `customers` ON  `customers`.`customer_id`=`d_loads`.`load_customer_id_fk` WHERE `load_id_status`='ACT' AND `load_id_status`='ACT'";
//----Apply Filters starts


		if(isset($param['common_search']) && $param['common_search']!=""){

			$common_search=senetize_input($param['common_search']);

			$q .=" AND (`load_id` LIKE '%$common_search%' OR `load_po` LIKE '%$common_search%' OR `customer_code` LIKE '%$common_search%' OR `customer_name` LIKE '%$common_search%')";

		}

//-----Apply fitlers ends

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'load_id':
				$q .=" ORDER BY `load_id`";
				break;

				case 'po_number':
				$q .=" ORDER BY `load_po`";
				break;

				case 'customer_code':
				$q .=" ORDER BY `customer_code`";
				break;

				case 'trailer_type':
				$q .=" ORDER BY `load_trailer_type`";
				break;					

				default:
				$q .=" ORDER BY `load_id`";
				break;
			}

		}else{

			$q .=" ORDER BY `load_id`";	

		}


		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

		$q .=" limit $from, $range";

		$qEx=mysqli_query($GLOBALS['con'],$q);



		$list=[];

		include_once APPROOT.'/models/masters/Users.php';

		$Users=new Users;

		while ($rows=mysqli_fetch_assoc($qEx)) {

			$added_by_user=$Users->user_basic_details($rows['load_added_by']);

			$stops=$this->get_load_stop_records(['load_id'=>$rows['load_id']]);

			$el_list=[

				'id'=>$rows['load_id'],

				'eid'=>$Enc->safeurlen($rows['load_id']),

				'customer_eid'=>$Enc->safeurlen($rows['customer_id']),

				'customer_code'=>$rows['customer_code'],

				'customer_name'=>$rows['customer_name'],

				'po_number'=>$rows['load_po'],

				'rate'=>$rows['load_rate'],

				'trailer_type'=>$rows['load_trailer_type'],

				'temperatrue_to_maintain'=>$rows['load_temperature_to_maintain'],

				'added_by_user_code'=>$added_by_user['user_code'],

				'added_on_datetime'=>dateTimeFromDbTimestamp($rows['load_added_on']),

				'shipper'=>$stops['shipper'],
				'consignee'=>$stops['consignee'],

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

		$r['message']=$q;

		$r['response']=$response;

		return $r;	

	}


	function loads_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		$confirm="";
		$confirmMessage="";

		if(in_array('P0173', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";

			if(isset($param['express_load_id']) && $param['express_load_id']!=""){
				$express_load_id=senetize_input($param['express_load_id']);
				include_once APPROOT.'/models/dispatch/ExpressLoads.php';
				$ExpressLoads=new ExpressLoads;

				if(!$ExpressLoads->isValidId($express_load_id)){
					$InvalidDataMessage="Invalid express load id";
					$dataValidation=false;
					goto ValidationChecker;
				}				

			}else{
				$InvalidDataMessage="Please provide express load id";
				$dataValidation=false;
				goto ValidationChecker;
			}




			if(isset($param['customer_id']) && $param['customer_id']!=""){

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


			$po_number="";
			if(isset($param['po_number']) && $param['po_number']!=""){

				$po_number=senetize_input($param['po_number']);

		//---check duplicacy of po_number
				$validate_po=mysqli_query($GLOBALS['con'],"SELECT `load_id` FROM `d_loads` WHERE `load_po`='$po_number' AND `load_id_status`='ACT'");
				if(mysqli_num_rows($validate_po)>0){

			///---check if duplicate PO numbers is set as true;
					if(isset($param['allow_duplicate_po_number']) && to_boolean($param['allow_duplicate_po_number'])==true){

					}else{
						$po_number_list;
						while($pon=mysqli_fetch_assoc($validate_po)){
							$po_number_list.=$pon['ld_load_id_fk'].", ";
						}

						$InvalidDataMessage="CONFIRM";
						$confirm="ALLOW DUPLICATE PO NUMBER";
						$confirmMessage="Load ".$po_number_list." for this PO number has been already created. Do you want to create new one ?";
						$dataValidation=false;

						goto ValidationChecker;	
					}

				}

		//---check duplicacy of po_number
			}

			$rate=0;
			if(isset($param['rate']) && $param['rate']!=""){

				$rate=senetize_input($param['rate']);

				if(!preg_match("/^[0-9.]{1,}$/",$rate)){

					$InvalidDataMessage="Please provide valid rate";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}

			$bill_of_lading=0;
			if(isset($param['bill_of_lading']) && $param['bill_of_lading']!=""){
				$bill_of_lading=senetize_input($param['rate']);
			}

			$trailer_type="";
			if(isset($param['trailer_type']) && $param['trailer_type']!=""){

				if(in_array($param['trailer_type'],['DRY','REEFER'])){

					$trailer_type=senetize_input($param['trailer_type']);

				}else{
					$InvalidDataMessage="Invalid reefer type";
					$dataValidation=false;
					goto ValidationChecker;					
				}

			}			

			$temperature_to_maintain='';

			if($trailer_type=='REEFER'){

				if(isset($param['temperature_to_maintain']) && $param['temperature_to_maintain']!=""){
					$temperature_to_maintain=senetize_input($param['temperature_to_maintain']);
					if(!preg_match("/^[0-9.-]{1,}$/",$temperature_to_maintain)){

						$InvalidDataMessage="Please provide valid temperature to maintain";

						$dataValidation=false;

						goto ValidationChecker;						

					}

				}		

			}

			$commodity_type_id=NULL;
			if(isset($param['commodity_type_id']) && $param['commodity_type_id']!=""){

				$commodity_type_id=senetize_input($param['commodity_type_id']);

				include_once APPROOT.'/models/dispatch/CommodityTypes.php';

				$CommodityTypes=new CommodityTypes;
				if(!$CommodityTypes->isValidId($commodity_type_id)){
					$InvalidDataMessage="Invalid commodity id";
					$dataValidation=false;
					goto ValidationChecker;

				}

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

					if(isset($stop['category']) && $stop['category']!=''){

						$category=senetize_input($stop['category']);
						if($category=='SHIPPER'){
							$total_shipper_stops++;
						}elseif ($category=='CONSIGNEE') {
							$total_consignee_stops++;
						}

						if(!in_array($category,['SHIPPER','CONSIGNEE','STOP'])){

							$InvalidDataMessage="Invalid stop category";

							$dataValidation=false;

							goto ValidationChecker;

						}
						$stop_item_senetized['category']=$category;

					}else{

						$InvalidDataMessage="Please provide stop category";

						$dataValidation=false;

						goto ValidationChecker;

					}



				//----validate stop type id

					if(isset($stop['type']) && $stop['type']!=''){

						$type=senetize_input($stop['type']);

						$stop_item_senetized['type']=$type;

						if(!in_array($type,['PICK','DROP'])){

							$InvalidDataMessage="Invalid stop type";

							$dataValidation=false;

							goto ValidationChecker;

						}

						if($category=='SHIPPER'){
							$stop_item_senetized['stop_series']=1;
							$stop_item_senetized['stop_type']='PICK';
						}elseif ($category=='CONSIGNEE') {
							$stop_item_senetized['stop_series']=2;
							$stop_item_senetized['stop_type']='DROP';
						}else{
							$stop_item_senetized['stop_series']=3;
							$stop_item_senetized['stop_type']=$type;
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

					if(isset($stop['address_id'])){

						$address_id=senetize_input($stop['address_id']);

						include_once APPROOT.'/models/masters/LocationAddresses.php';

						$LocationAddresses=new LocationAddresses;

						if($LocationAddresses->isValidId($address_id)){

							$stop_item_senetized['address_id']=$address_id;

						}else{

							$InvalidDataMessage="Invalid stop address id";

							$dataValidation=false;

							goto ValidationChecker;							

						}


					}else{

						$InvalidDataMessage="Please provide stop address id";

						$dataValidation=false;

						goto ValidationChecker;

					}

				//----/validate stop location id


			$stop_date='0000-00-00';//--initialize defalut stop date
			$time_from='00:00:00';	//--initialize defalut stop time
			$time_to='00:00:00';	//--initialize defalut stop time



			if(isset($stop['date'])){

				if(isValidDateFormat($stop['date'])){

					$date=date('Y-m-d', strtotime($stop['date']));

				}else{

					$InvalidDataMessage="Please provide valid stop date";

					$dataValidation=false;

					goto ValidationChecker;							

				}

			}

		//--validate stop time

			if(isset($stop['time_from'])){
				if(isValidTimeFormat($stop['time_from'])){
					$time_from=date('H:i', strtotime($stop['time_from']));
				}else{
					$InvalidDataMessage="Please provide valid stop time from";
					$dataValidation=false;
					goto ValidationChecker;							
				}
			}

			if(isset($stop['time_to'])){
				if(isValidTimeFormat($stop['time_to'])){
					$time_to=date('H:i', strtotime($stop['time_to']));
				}else{
					$InvalidDataMessage="Please provide valid stop time to";
					$dataValidation=false;
					goto ValidationChecker;							
				}
			}

		//--/validate stop time

			$stop_item_senetized['date']=$stop_date;
			$stop_item_senetized['time_from']=$time_from;
			$stop_item_senetized['time_to']=$time_to;



			$case_count=0;
			if(isset($stop['case_count']) && $stop['case_count']!=""){

				$case_count=senetize_input($stop['case_count']);

				if(!preg_match("/^[0-9.]{1,}$/",$case_count)){

					$InvalidDataMessage="Please provide valid case count";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}
			$stop_item_senetized['case_count']=$case_count;
			$pallet_count=0;
			if(isset($stop['pallet_count']) && $stop['pallet_count']!=""){

				$pallet_count=senetize_input($stop['pallet_count']);

				if(!preg_match("/^[0-9.]{1,}$/",$pallet_count)){

					$InvalidDataMessage="Please provide valid pallet count";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}
			$stop_item_senetized['pallet_count']=$pallet_count;


			$stop_item_senetized['pick_up_number']=(isset($stop['pick_up_number']) && $stop['pick_up_number']!="")?senetize_input($stop['pick_up_number']):'';
			$stop_item_senetized['confirm_number']=(isset($stop['confirm_number']) && $stop['confirm_number']!="")?senetize_input($stop['confirm_number']):'';
			$stop_item_senetized['special_instructions']=(isset($stop['special_instructions']) && $stop['special_instructions']!="")?senetize_input($stop['special_instructions']):'';

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

		$get_old_id_l=mysqli_query($GLOBALS['con'],"SELECT `load_id` FROM `d_loads` ORDER BY `auto` DESC LIMIT 1");

		$get_old_id_l=(mysqli_num_rows($get_old_id_l)==1)?(mysqli_fetch_assoc($get_old_id_l)['load_id']):'L000000';

		$next_id_l='L'.sprintf('%06d',(intval(substr($get_old_id_l,1))+1));

		///-----//Generate New Unique Id				
	//-----Insert express load details


		$insert_express_load_details=mysqli_query($GLOBALS['con'],"INSERT INTO `d_loads`(`load_id`, `load_customer_id_fk`, `load_po`, `load_rate`, `load_bill_of_lading`, `load_commodity_type_id_fk`, `load_trailer_type`, `load_temperature_to_maintain`, `load_id_status`, `load_added_on`, `load_added_by`) VALUES ('$next_id_l','$customer_id','$po_number','$rate','$bill_of_lading','$commodity_type_id','$trailer_type','$temperature_to_maintain','ACT','$time','$USERID')");

		if(!$insert_express_load_details){

			$executionMessage=SOMETHING_WENT_WROG.' step 01'.mysqli_error($GLOBALS['con']);

			$execution=false;

			goto executionChecker;		

		}

	//-----/Insert express load details







	//-----Insert express load stops

		$get_old_id_stop=mysqli_query($GLOBALS['con'],"SELECT `l_stop_id` FROM `d_load_stops` ORDER BY `auto` DESC LIMIT 1");

		$next_id_stop=(mysqli_num_rows($get_old_id_stop)==1)?(mysqli_fetch_assoc($get_old_id_stop)['l_stop_id']):'0';

		foreach ($stops_array_senetized as $sr) {
			$next_id_stop++;
			$insert_express_load_stop=mysqli_query($GLOBALS['con'],"INSERT INTO `d_load_stops`( `l_stop_id`, `l_stop_load_id_fk`, `l_stop_type`, `l_stop_category`, `l_stop_address_id_fk`, `l_stop_appointment_type`, `l_stop_date`, `l_stop_time_from`, `l_stop_time_to`, `l_stop_pick_up_number`, `l_stop_confirm_number`, `l_stop_case_count`, `l_stop_pallet_count`, `l_stop_special_instructions`, `l_stop_id_status`,`l_stop_added_on`,`l_stop_added_by`) VALUES ('$next_id_stop','$next_id_l','".$sr['type']."','".$sr['category']."','".$sr['address_id']."','".$sr['appointment_type']."','".$sr['date']."','".$sr['time_from']."','".$sr['time_to']."','".$sr['pick_up_number']."','".$sr['confirm_number']."','".$sr['case_count']."','".$sr['pallet_count']."','".$sr['special_instructions']."','ACT','$time','$USERID')");

			if(!$insert_express_load_stop){
				$executionMessage=SOMETHING_WENT_WROG.' step 03'.mysqli_error($GLOBALS['con']);
				$execution=false;
				goto executionChecker;		
			}	
		}

	//-----/Insert express load stops
		executionChecker:

		if($execution){
			$message="Added Successfuly";
			$status=true;
			$response['new_eid']=$Enc->safeurlen($next_id_l);

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








private function get_load_stop_records($param){

	$stops=[];
	$shipper=[];
	$consignee=[];

	if(isset($param['load_id'])){

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$fetch_el_stops=mysqli_query($GLOBALS['con'],"SELECT `l_stop_id`,`l_stop_category`, `l_stop_type`, `l_stop_appointment_type`, `l_stop_date` , `l_stop_time_from` , `l_stop_time_to`, `l_stop_address_id_fk`, `l_stop_id_status`,`l_stop_pick_up_number`,`l_stop_confirm_number`,`l_stop_case_count`,`l_stop_pallet_count`,`l_stop_special_instructions`,`loc_id`, `loc_name`, `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`,`loc_full_address`,`l_stop_special_instructions`  FROM `d_load_stops` LEFT JOIN `location_addresses` ON `location_addresses`.`loc_id`=`d_load_stops`.`l_stop_address_id_fk` WHERE `l_stop_load_id_fk`='".senetize_input($param['load_id'])."'");

		while($s_rows=mysqli_fetch_assoc($fetch_el_stops)){

			$row=[
				'eid'=>$Enc->safeurlen($s_rows['l_stop_id']),
				'category'=>$s_rows['l_stop_category'],
				'type'=>$s_rows['l_stop_type'],
				'appointment_type'=>$s_rows['l_stop_appointment_type'],
				//'datetime'=>$s_rows['l_stop_datetime'],
				'date'=>dateFromDbDatetime($s_rows['l_stop_date']),
				'time_from'=>timeFromDbTime($s_rows['l_stop_time_from']),
				'time_to'=>timeFromDbTime($s_rows['l_stop_time_to']),
				'stop_type'=>$s_rows['l_stop_type'],
				'location_id'=>$s_rows['loc_id'],
				'location_name'=>$s_rows['loc_name'],
				'location_address_line'=>$s_rows['loc_address_line'],
				'location_city'=>$s_rows['loc_city'],
				'location_state'=>$s_rows['loc_state'],
				'location_zipcode'=>$s_rows['loc_zipcode'],
				'location_full_address'=>$s_rows['loc_full_address'],
				'pick_up_number'=>$s_rows['l_stop_pick_up_number'],
				'confirm_number'=>$s_rows['l_stop_confirm_number'],
				'case_count'=>$s_rows['l_stop_case_count'],
				'pallet_count'=>$s_rows['l_stop_pallet_count'],
				'special_instructions'=>$s_rows['l_stop_special_instructions'],

			];
			if($s_rows['l_stop_category']=='SHIPPER'){
				$shipper=$row;
			}elseif($s_rows['l_stop_category']=='CONSIGNEE'){
				$consignee=$row;
			}else{
				array_push($stops,$row);
			}

		}

	}
	return ['shipper'=>$shipper,'consignee'=>$consignee,'stops'=>$stops];
}

function stop_information_update($param){

	$status=false;

	$message=null;

	$response=null;
	$confirm=null;
	$confirmMessage=null;

	if(in_array('P0175', USER_PRIV)){

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
				//----validate stop type id

		//--get stop type 
		$gst=mysqli_query($GLOBALS['con'],"SELECT `l_stop_category` FROM `d_load_stops` WHERE `l_stop_id`='$update_id' AND `l_stop_id_status`='ACT'");
		if(mysqli_num_rows($gst)==1){
			$stop_category=mysqli_fetch_assoc($gst)['l_stop_category'];

		}else{
			$InvalidDataMessage="Invalid stop eid".$update_id;

			$dataValidation=false;

			goto ValidationChecker;			
		}

		if($stop_category=='SHIPPER'){
			$stop_type='PICK';
		}elseif($stop_category=='CONSIGNEE'){
			$stop_type='DROP';
		}else{
			if(isset($param['type']) && $param['type']!=''){

				$type=senetize_input($param['type']);

				if(!in_array($type,['PICK','DROP'])){

					$InvalidDataMessage="Invalid stop type";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}else{

				$InvalidDataMessage="Please provide stop type";

				$dataValidation=false;

				goto ValidationChecker;

			}
		}




				//----/validate stop type id



				//----validate appointment type id

		if(isset($param['appointment_type']) && $param['appointment_type']!=''){

			$appointment_type=senetize_input($param['appointment_type']);

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

		if(isset($param['address_id'])){

			$address_id=senetize_input($param['address_id']);

			include_once APPROOT.'/models/masters/LocationAddresses.php';

			$LocationAddresses=new LocationAddresses;

			if(!$LocationAddresses->isValidId($address_id)){
				$InvalidDataMessage="Invalid stop address id";

				$dataValidation=false;

				goto ValidationChecker;

			}

		}else{

			$InvalidDataMessage="Please provide stop address id";

			$dataValidation=false;

			goto ValidationChecker;

		}

				//----/validate stop location id


			$date='0000-00-00';//--initialize defalut stop date
			$time_from='00:00:00';	//--initialize defalut stop time
			$time_to='00:00:00';	//--initialize defalut stop time



			if(isset($param['date'])){

				if(isValidDateFormat($param['date'])){

					$date=date('Y-m-d', strtotime($param['date']));

				}else{

					$InvalidDataMessage="Please provide valid stop date";

					$dataValidation=false;

					goto ValidationChecker;							

				}

			}

		//--validate stop time

			if(isset($param['time_from'])){
				if(isValidTimeFormat($param['time_from'])){
					$time_from=date('H:i', strtotime($param['time_from']));
				}else{
					$InvalidDataMessage="Please provide valid stop time from";
					$dataValidation=false;
					goto ValidationChecker;							
				}
			}
			if(isset($param['time_to'])){
				if(isValidTimeFormat($param['time_to'])){
					$time_to=date('H:i', strtotime($param['time_to']));
				}else{
					$InvalidDataMessage="Please provide valid stop time to";
					$dataValidation=false;
					goto ValidationChecker;							
				}
			}			

		//--/validate stop time

			$datetime=$stop_date.' '.$stop_time;



			$case_count=0;
			if(isset($param['case_count']) && $param['case_count']!=""){

				$case_count=senetize_input($param['case_count']);

				if(!preg_match("/^[0-9.]{1,}$/",$case_count)){

					$InvalidDataMessage="Please provide valid case count";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}
			$pallet_count=0;
			if(isset($param['pallet_count']) && $param['pallet_count']!=""){

				$pallet_count=senetize_input($param['pallet_count']);

				if(!preg_match("/^[0-9.]{1,}$/",$pallet_count)){

					$InvalidDataMessage="Please provide valid pallet count";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}

			$pick_up_number=(isset($param['pick_up_number']) && $param['pick_up_number']!="")?senetize_input($param['pick_up_number']):'';
			$confirm_number=(isset($param['confirm_number']) && $param['confirm_number']!="")?senetize_input($param['confirm_number']):'';
			$special_instructions=(isset($param['special_instructions']) && $param['special_instructions']!="")?senetize_input($param['special_instructions']):'';



			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';


//----create log of old details
				$create_log=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_load_stops` (`l_stop_id`, `l_stop_load_id_fk`, `l_stop_type`, `l_stop_category`, `l_stop_address_id_fk`, `l_stop_appointment_type`, `l_stop_date`, `l_stop_time_from`, `l_stop_time_to`, `l_stop_pick_up_number`, `l_stop_confirm_number`, `l_stop_case_count`, `l_stop_pallet_count`, `l_stop_special_instructions`, `l_stop_id_status`, `l_stop_added_on`, `l_stop_added_by`) SELECT `l_stop_id`, `l_stop_load_id_fk`, `l_stop_type`, `l_stop_category`, `l_stop_address_id_fk`, `l_stop_appointment_type`, `l_stop_date`, `l_stop_time_from`, `l_stop_time_to`, `l_stop_pick_up_number`, `l_stop_confirm_number`, `l_stop_case_count`, `l_stop_pallet_count`, `l_stop_special_instructions`, `l_stop_id_status`, `l_stop_added_on`, `l_stop_added_by` FROM `d_load_stops` WHERE `l_stop_id`='$update_id'");
				if(!$create_log){

					$executionMessage=SOMETHING_WENT_WROG.' step 01'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}
//----/create log of old details





				$update=mysqli_query($GLOBALS['con'],"UPDATE `d_load_stops` SET `l_stop_type`='$stop_type',`l_stop_address_id_fk`='$address_id',`l_stop_appointment_type`='$appointment_type',`l_stop_date`='$date',`l_stop_time_from`='$time_from',`l_stop_time_to`='$time_to',`l_stop_pick_up_number`='$pick_up_number',`l_stop_confirm_number`='$confirm_number',`l_stop_case_count`='$case_count',`l_stop_pallet_count`='$pallet_count',`l_stop_special_instructions`='$special_instructions',`l_stop_added_on`='$time',`l_stop_added_by`='$USERID' WHERE `l_stop_id`='$update_id'");
				if(!$update){

					$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert base ID of express load

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
		$r['confirm']=$confirm;
		$r['confirm_message']=$confirmMessage;

		return $r;

	}



	function load_information_update($param){

		$status=false;

		$message=null;

		$response=null;
		$confirm=null;
		$confirmMessage=null;

		if(in_array('P0175', USER_PRIV)){

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

			if(isset($param['customer_id']) && $param['customer_id']!=""){

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


			$po_number="";
			if(isset($param['po_number']) && $param['po_number']!=""){

				$po_number=senetize_input($param['po_number']);

		//---check duplicacy of po_number
				$validate_po=mysqli_query($GLOBALS['con'],"SELECT `load_id` FROM `d_loads` WHERE `load_po`='$po_number' AND `load_id_status`='ACT' AND NOT `load_id`='$update_id'");
				if(mysqli_num_rows($validate_po)>0){

			///---check if duplicate PO numbers is set as true;
					if(isset($param['allow_duplicate_po_number']) && to_boolean($param['allow_duplicate_po_number'])==true){

					}else{
						$po_number_list;
						while($pon=mysqli_fetch_assoc($validate_po)){
							$po_number_list.=$pon['ld_load_id_fk'].", ";
						}

						$InvalidDataMessage="CONFIRM";
						$confirm="ALLOW DUPLICATE PO NUMBER";
						$confirmMessage="Load ".$po_number_list." for ffffffffffthis PO number has been already created. Do you want to create new one ?";
						$dataValidation=false;

						goto ValidationChecker;	
					}

				}

		//---check duplicacy of po_number
			}

			$rate=0;
			if(isset($param['rate']) && $param['rate']!=""){

				$rate=senetize_input($param['rate']);

				if(!preg_match("/^[0-9.]{1,}$/",$rate)){

					$InvalidDataMessage="Please provide valid rate";

					$dataValidation=false;

					goto ValidationChecker;						

				}

			}

			$bill_of_lading=0;
			if(isset($param['bill_of_lading']) && $param['bill_of_lading']!=""){
				$bill_of_lading=senetize_input($param['rate']);
			}

			$trailer_type="";
			if(isset($param['trailer_type']) && $param['trailer_type']!=""){

				if(in_array($param['trailer_type'],['DRY','REEFER'])){

					$trailer_type=senetize_input($param['trailer_type']);

				}else{
					$InvalidDataMessage="Invalid reefer type";
					$dataValidation=false;
					goto ValidationChecker;					
				}

			}			

			$temperature_to_maintain='';

			if($trailer_type=='REEFER'){

				if(isset($param['temperature_to_maintain']) && $param['temperature_to_maintain']!=""){
					$temperature_to_maintain=senetize_input($param['temperature_to_maintain']);
					if(!preg_match("/^[0-9.-]{1,}$/",$temperature_to_maintain)){

						$InvalidDataMessage="Please provide valid temperature to maintain";

						$dataValidation=false;

						goto ValidationChecker;						

					}

				}		

			}

			$commodity_type_id=NULL;
			if(isset($param['commodity_type_id']) && $param['commodity_type_id']!=""){

				$commodity_type_id=senetize_input($param['commodity_type_id']);

				include_once APPROOT.'/models/dispatch/CommodityTypes.php';

				$CommodityTypes=new CommodityTypes;
				if(!$CommodityTypes->isValidId($commodity_type_id)){
					$InvalidDataMessage="Invalid commodity id";
					$dataValidation=false;
					goto ValidationChecker;

				}

			}		


			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

				$execution=true;

				$executionMessage='';



;

//----create log of old details
				$create_log=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_loads` (`load_id`, `load_express_load_id_fk`, `load_customer_id_fk`, `load_po`, `load_rate`, `load_bill_of_lading`, `load_commodity_type_id_fk`, `load_trailer_type`, `load_temperature_to_maintain`, `load_id_status`, `load_added_on`, `load_added_by`) SELECT `load_id`, `load_express_load_id_fk`, `load_customer_id_fk`, `load_po`, `load_rate`, `load_bill_of_lading`, `load_commodity_type_id_fk`, `load_trailer_type`, `load_temperature_to_maintain`, `load_id_status`, `load_added_on`, `load_added_by` FROM `d_loads` WHERE `load_id`='$update_id'");
				if(!$create_log){

					$executionMessage=SOMETHING_WENT_WROG.' step 01'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}
//----/create log of old details



				$update=mysqli_query($GLOBALS['con'],"UPDATE `d_loads` SET `load_customer_id_fk`='$customer_id', `load_po`='$po_number', `load_rate`='$rate', `load_bill_of_lading`='$bill_of_lading', `load_commodity_type_id_fk`='$commodity_type_id', `load_trailer_type`='$trailer_type', `load_temperature_to_maintain`='$temperature_to_maintain' WHERE `load_id`='$update_id'");
				if(!$update){

					$executionMessage=SOMETHING_WENT_WROG.' step 02'.mysqli_error($GLOBALS['con']);

					$execution=false;

					goto executionChecker;		

				}

			//-----/Insert base ID of express load

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
		$r['confirm']=$confirm;
		$r['confirm_message']=$confirmMessage;

		return $r;

	}











	protected function create_logs_of_load_details()

	{

		$status=true;

		$message=null;

	//------this function will fetch all the records from express load details table that are deleted and dump this record for into log table to

	//------this action is needed just to remove deleted recoreds from real tables



	//---firstly get the last record in express load table stops table

	//---avoid deletion of these. otherwise it impact auto generated id system

		$get_last_id_eld_q=mysqli_query($GLOBALS['con'],"SELECT `load_id` FROM `d_load_details` ORDER BY `auto` DESC LIMIT 1");

		if(mysqli_num_rows($get_last_id_eld_q)==1){

			$get_last_id_eld=mysqli_fetch_assoc($get_last_id_eld_q)['ld_id'];



			$del_records=mysqli_query($GLOBALS['con'],"SELECT `auto`, `load_id`, `load_load_id_fk`, `load_customer_id`, `load_po`, `load_trailer_type`, `load_temperatrue_to_maintain`, `load_rate`, `load_id_status`, `load_added_on`, `load_added_by`, `load_deleted_on`, `load_deleted_by` FROM `d_load_details` WHERE `load_id_status`='DEL' AND NOT `load_id`='$get_last_id_eld'");



			while($res=mysqli_fetch_assoc($del_records)){

				$eld_id=$res['ld_id'];



		//check if the last id in stops table is not belongs to this load

				$get_last_id_stops_q=mysqli_query($GLOBALS['con'],"SELECT `l_stop_load_detail_id_fk` FROM `d_load_stops` ORDER BY `auto` DESC LIMIT 1");

				if(mysqli_num_rows($get_last_id_stops_q)==1){

					if(mysqli_fetch_assoc($get_last_id_stops_q)['l_stop_load_detail_id_fk']!=$eld_id){



				//---dump real data of el stops table to logs table

						$dump_stops=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_load_stops` (`l_stop_id`, `l_stop_load_detail_id_fk`, `l_stop_type`, `l_stop_appointment_type`, `l_stop_datetime_tbd`, `l_stop_datetime`, `l_stop_location_id_fk`, `l_stop_id_status`)

							SELECT `l_stop_id`, `l_stop_load_detail_id_fk`, `l_stop_type`, `l_stop_appointment_type`, `l_stop_datetime_tbd`, `l_stop_datetime`, `l_stop_location_id_fk`, `l_stop_id_status` FROM `d_load_stops`

							WHERE `l_stop_load_detail_id_fk`='$eld_id'");

					if($dump_stops){ ///---if record's dumping executes successfuly del the real recrods

					mysqli_query($GLOBALS['con'],"DELETE FROM `d_load_stops`

						WHERE `l_stop_load_detail_id_fk`='$eld_id'");



				}



				//---dump real data of ex load details table to logs table	



				$dump_details=mysqli_query($GLOBALS['con'],"INSERT INTO `logs_d_load_details` (`load_id`, `load_load_id_fk`, `load_customer_id`, `load_po`, `load_trailer_type`, `load_temperatrue_to_maintain`, `load_rate`, `load_id_status`, `load_added_on`, `load_added_by`, `load_deleted_on`, `load_deleted_by`)

					SELECT `load_id`, `load_load_id_fk`, `load_customer_id`, `load_po`, `load_trailer_type`, `load_temperatrue_to_maintain`, `load_rate`, `load_id_status`, `load_added_on`, `load_added_by`, `load_deleted_on`, `load_deleted_by` FROM `d_load_details`

					WHERE `load_id`='$eld_id'");

					if($dump_details){ ///---if record's dumping executes successfuly del the real recrods

					mysqli_query($GLOBALS['con'],"DELETE FROM `d_load_details`

						WHERE `load_id`='$eld_id'");



				}					



			}

		}





	}			

}

return array('status' => $status,'message'=>$message );



}

function loads_details($param){

	$status=false;

	$message=null;

	$response=[];

	if(in_array('P0174', USER_PRIV)){

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;

		$dataValidation=true;

		$InvalidDataMessage="";



		if(isset($param['eid']) &&  $param['eid']!=""){

			$load_id=$Enc->safeurlde($param['eid']);

		}else{

			$dataValidation=false;

			$InvalidDataMessage="Please provide load eid";

			goto ValidationChecker;

		}

		ValidationChecker:

		if($dataValidation){

			$q=mysqli_query($GLOBALS['con'],"SELECT  `load_added_on`, `load_added_by`, `load_id`, `load_customer_id_fk`, `load_po`,`load_bill_of_lading`, `load_trailer_type`, `load_temperature_to_maintain`,`load_rate`, `load_added_on`, `load_added_by`,`customer_id`,`customer_code`,`customer_name`,`commodity_type_name`,`commodity_type_id` FROM `d_loads` LEFT JOIN `customers` ON  `customers`.`customer_id`=`d_loads`.`load_customer_id_fk` LEFT JOIN `d_commodity_types` ON  `d_loads`.`load_commodity_type_id_fk`=`d_commodity_types`.`commodity_type_id` WHERE `load_id_status`='ACT' AND `load_id_status`='ACT' AND `load_id`='$load_id'");

			if(mysqli_num_rows($q)==1){

				$status=true;

				$rows=mysqli_fetch_assoc($q);

				include_once APPROOT.'/models/masters/Users.php';

				$Users=new Users;

				$added_by_user=$Users->user_basic_details($rows['load_added_by']);
				$stop_details=$this->get_load_stop_records(['load_id'=>$rows['load_id']]);




				$response['details']=[

					'id'=>$rows['load_id'],

					'eid'=>$Enc->safeurlen($rows['load_id']),

					'customer_id'=>$rows['customer_id'],

					'customer_eid'=>$Enc->safeurlen($rows['customer_id']),

					'customer_code'=>$rows['customer_code'],

					'customer_name'=>$rows['customer_name'],

					'po_number'=>$rows['load_po'],

					'rate'=>$rows['load_rate'],
					
					'bill_of_lading'=>$rows['load_bill_of_lading'],

					'commodity_type'=>$rows['commodity_type_name'],
					
					'commodity_type_id'=>$rows['commodity_type_id'],

					'trailer_type'=>$rows['load_trailer_type'],

					'temperature_to_maintain'=>$rows['load_temperature_to_maintain'],
					
					'shipper'=>$stop_details['shipper'],

					'consignee'=>$stop_details['consignee'],
					'stops'=>$stop_details['stops'],

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

	$r['message']=$Enc->safeurlen('L000017');

	$r['response']=$response;

	return $r;	

}


function stop_details($param){

	$status=false;

	$message=null;

	$response=[];

	if(in_array('P0174', USER_PRIV)){

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;

		$dataValidation=true;

		$InvalidDataMessage="";



		if(isset($param['eid']) &&  $param['eid']!=""){

			$stop_id=$Enc->safeurlde($param['eid']);

		}else{

			$dataValidation=false;

			$InvalidDataMessage="Please provide stop eid".$stop_id;

			goto ValidationChecker;

		}

		ValidationChecker:

		if($dataValidation){


			$qEx=mysqli_query($GLOBALS['con'],"SELECT `l_stop_id`,`l_stop_category`, `l_stop_type`, `l_stop_appointment_type`, `l_stop_date`, `l_stop_time_from`, `l_stop_time_to`, `l_stop_address_id_fk`, `l_stop_id_status`,`l_stop_pick_up_number`,`l_stop_confirm_number`,`l_stop_case_count`,`l_stop_pallet_count`,`l_stop_special_instructions`,`loc_id`, `loc_name`, `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`,`loc_full_address`,`l_stop_special_instructions`  FROM `d_load_stops` LEFT JOIN `location_addresses` ON `location_addresses`.`loc_id`=`d_load_stops`.`l_stop_address_id_fk` WHERE `l_stop_id`='$stop_id'");
			if(mysqli_num_rows($qEx)==1){
				$status=true;
				$rs=mysqli_fetch_assoc($qEx);

				$response['details']=[
					'eid'=>$Enc->safeurlen($rs['l_stop_id']),
					'category'=>$rs['l_stop_category'],
					'type'=>$rs['l_stop_type'],
					'appointment_type'=>$rs['l_stop_appointment_type'],
					//'datetime'=>$rs['l_stop_datetime'],
					'date'=>dateFromDbDatetime($rs['l_stop_date']),
					'time_from'=>timeFromDbTime($rs['l_stop_time_from']),
					'time_to'=>timeFromDbTime($rs['l_stop_time_to']),
					'stop_type'=>$rs['l_stop_type'],
					'location_id'=>$rs['loc_id'],
					'location_name'=>$rs['loc_name'],
					'location_address_line'=>$rs['loc_address_line'],
					'location_city'=>$rs['loc_city'],
					'location_state'=>$rs['loc_state'],
					'location_zipcode'=>$rs['loc_zipcode'],
					'location_full_address'=>$rs['loc_full_address'],
					'pick_up_number'=>$rs['l_stop_pick_up_number'],
					'confirm_number'=>$rs['l_stop_confirm_number'],
					'case_count'=>$rs['l_stop_case_count'],
					'pallet_count'=>$rs['l_stop_pallet_count'],
					'special_instructions'=>$rs['l_stop_special_instructions'],
					'test'=>$stop_id,
				];

			}else{
				$message="Invalid stop eid";
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