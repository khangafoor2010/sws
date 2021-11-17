<?php

/**

 *

 */

class Customers

{





	function isValidId($id){

		$id=senetize_input($id);

		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `customer_id` from `customers` WHERE `customer_id`='$id' AND `customer_id_status`='ACT'"))==1){

			return true;

		}else{

			return false;

		}

	}







	function customers_quick_list($param){

			$status=false;

			$message=null;

			$response=null;



			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;



			$q="SELECT `customer_id`,`customer_code`, `customer_type_id`, `customer_name`, `customer_address_line`,`address_countries`.`location_name` AS `address_country_name`, `address_states`.`location_name` AS `address_state_name`,`location_cities`.`location_name` AS `address_city_name`, `location_zipcodes`.`location_name` AS `address_zipcode_name`, `customer_toll_free_number`, `customer_phone_number`, `customer_fax_phone_no`, `customer_company_email`, `customer_after_hours_email`, `customer_after_hours_phone_number`, `customer_load_notification_email`, `customer_dispatch_notes`, `customer_dispatcher_notice`, `customer_credit_status_id`, `customer_billing_fax_number`, `customer_billing_email`, `customer_net_term` FROM `customers` LEFT JOIN `locations` AS `address_countries` ON `address_countries`.`location_id`=`customers`.`customer_country_id_fk` LEFT JOIN `locations` AS `address_states` ON `address_states`.`location_id`=`customers`.`customer_state_id_fk` LEFT JOIN `locations` AS `location_cities` ON `location_cities`.`location_id`=`customers`.`customer_city_id_fk` LEFT JOIN `locations` AS `location_zipcodes` ON `location_zipcodes`.`location_id`=`customers`.`customer_zipcode_id_fk` WHERE `customer_id_status`='ACT'";







//----Apply Filters starts





			if(isset($param['type_id']) && $param['type_id']!=""){

				$q .=" AND `customer_type_id`='".senetize_input($param['type_id'])."'";

			}



//-----Apply fitlers ends









			$order_by_type='ASC';

			if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){

				$order_by_type='DESC';

			}

			if(isset($param['sort_by'])){

				switch ($param['sort_by']) {

					case 'type_id':

					$q .=" ORDER BY `type_id`";

					break;	

					default:

					$q .=" ORDER BY `customer_code`";

					break;

				}

			}else{

				$q .=" ORDER BY `customer_code`";	

			}



		$qEx=mysqli_query($GLOBALS['con'],$q);



			$list=[];

			while ($rows=mysqli_fetch_assoc($qEx)) {

				array_push($list,[

					'id'=>$rows['customer_id'],

					'eid'=>$Enc->safeurlen($rows['customer_id']),

					'code'=>$rows['customer_code'],

					'name'=>$rows['customer_name'],

					

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



	function customers_add_new($param){

		$status=false;

		$message=null;

		$response=null;

		if(in_array('P0162', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';

			$Enc=new Enc;

			$dataValidation=true;

			$InvalidDataMessage="";





			include_once APPROOT.'/models/masters/Companies.php';

			$Companies=new Companies;

			if(isset($param['terminal_id']) && $param['terminal_id']!=""){

				$terminal_id=senetize_input($param['terminal_id']);

				if(!$Companies->isValidId($terminal_id)){

					$InvalidDataMessage="Invalid address terminal id";

					$dataValidation=false;

					goto ValidationChecker;

				

				}



			}else{

				$InvalidDataMessage="Please provide terminal id";

				$dataValidation=false;

				goto ValidationChecker;			

			}	



			if(isset($param['code'])){

				$code=senetize_input($param['code']);



				//---check code duplicacy

				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `customer_code` FROM `customers` WHERE `customer_id_status`='ACT' AND `customer_code`='$code'"))>0){

					$InvalidDataMessage="Customer code already exists";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide customer code";

				$dataValidation=false;

				goto ValidationChecker;

			}

			if(isset($param['customer_name'])){

				$customer_name=senetize_input($param['customer_name']);

			}else{

				$InvalidDataMessage="Please provide customer name";

				$dataValidation=false;

				goto ValidationChecker;

			}

			if(isset($param['customer_type_id']) && $param['customer_type_id']!=""){

				$customer_type_id=senetize_input($param['customer_type_id']);

				if(in_array($customer_type_id,['BROKER','SHIPPER','3PL'])){

					$customer_type_id=senetize_input($param['customer_type_id']);

				}else{

					$InvalidDataMessage="Invalid customer type value";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide customer type id";

				$dataValidation=false;

				goto ValidationChecker;			

			}			



			$address_line=(isset($param['address_line']))?senetize_input($param['address_line']):'';



			include_once APPROOT.'/models/masters/Locations.php';

			$Locations=new Locations;



			$address_country_id="";

			if(isset($param['address_country_id']) && $param['address_country_id']!=""){

				$address_country_id=senetize_input($param['address_country_id']);



				if(!$Locations->isValidLocationCountryId($address_country_id)){

					$InvalidDataMessage="Invalid address Country value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_state_id="";

			if(isset($param['address_state_id']) && $param['address_state_id']!=""){

				$address_state_id=senetize_input($param['address_state_id']);



				if(!$Locations->isValidLocationStateId($address_state_id)){

					$InvalidDataMessage="Invalid address state value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_city_id="";

			if(isset($param['address_city_id']) && $param['address_city_id']!=""){

				$address_city_id=senetize_input($param['address_city_id']);



				if(!$Locations->isValidLocationCityId($address_city_id)){

					$InvalidDataMessage="Invalid address city value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_zipcode_id="";

			if(isset($param['address_zipcode_id']) && $param['address_zipcode_id']!=""){

				$address_zipcode_id=senetize_input($param['address_zipcode_id']);



				if(!$Locations->isValidLocationZipId($address_zipcode_id)){

					$InvalidDataMessage="Invalid address city value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}







			$dispatch_notes=(isset($param['dispatch_notes']))?senetize_input($param['dispatch_notes']):'';

			$dispatcher_notice=(isset($param['dispatcher_notice']))?senetize_input($param['dispatcher_notice']):'';



			$toll_free_number=(isset($param['toll_free_number']) && $param['toll_free_number']!="")?$Enc->enc_mob(senetize_input($param['toll_free_number'])):'';

			$phone_number=(isset($param['phone_number']) && $param['phone_number']!="")?$Enc->enc_mob(senetize_input($param['phone_number'])):'';

			$fax_phone_number=(isset($param['fax_phone_number']) && $param['fax_phone_number']!="")?$Enc->enc_mob(senetize_input($param['fax_phone_number'])):'';

			$company_email=(isset($param['email']) && $param['email']!="")?$Enc->enc_mail(senetize_input($param['email'])):'';

			$load_notification_email=(isset($param['load_notification_email']) && $param['load_notification_email']!="")?$Enc->enc_mail(senetize_input($param['load_notification_email'])):'';

			$after_hours_email=(isset($param['after_hours_email']) && $param['after_hours_email']!="")?$Enc->enc_mail(senetize_input($param['after_hours_email'])):'';

			$after_hours_phone_number=(isset($param['after_hours_phone_number']) && $param['after_hours_phone_number']!="")?$Enc->enc_mob(senetize_input($param['after_hours_phone_number'])):'';

			$billing_fax_number=(isset($param['billing_fax_number']) && $param['billing_fax_number']!="")?$Enc->enc_mob(senetize_input($param['billing_fax_number'])):'';

			$billing_email=(isset($param['billing_email']) && $param['billing_email']!="")?$Enc->enc_mail(senetize_input($param['billing_email'])):'';

							//-----data validation ends

			$net_terms="";

			if(isset($param['net_terms']) && $param['net_terms']!=""){

				$net_terms=senetize_input($param['net_terms']);

				if(!in_array($net_terms,['15','30'])){

					$InvalidDataMessage="Invalid net terms";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}





			$deliverable_receipt_option_id="";

			if(isset($param['deliverable_receipt_option_id']) && $param['deliverable_receipt_option_id']!=""){

				$deliverable_receipt_option_id=senetize_input($param['deliverable_receipt_option_id']);

				if(!in_array($deliverable_receipt_option_id,['EMAIL','EDI','FTP','API'])){

					$InvalidDataMessage="Invalid credit deliverable receipt option id";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}





			$credit_status_id="";

			if(isset($param['credit_status_id']) && $param['credit_status_id']!=""){

				$credit_status_id=senetize_input($param['credit_status_id']);

				if(!in_array($credit_status_id,['APPROVED','NOT-APPROVED'])){

					$InvalidDataMessage="Invalid credit status id";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}

			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;

 					///-----Generate New Unique Id

				$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `customer_id` FROM `customers` ORDER BY `auto` DESC LIMIT 1");

				$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['customer_id'])+1:1;

					///-----//Generate New Unique Id

				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `customers`(`customer_id`,`customer_terminal_id_fk`, `customer_code`, `customer_type_id`, `customer_name`, `customer_address_line`, `customer_city_id_fk`, `customer_state_id_fk`, `customer_zipcode_id_fk`, `customer_country_id_fk`, `customer_toll_free_number`, `customer_phone_number`, `customer_fax_phone_no`, `customer_company_email`, `customer_after_hours_email`, `customer_after_hours_phone_number`, `customer_load_notification_email`, `customer_dispatch_notes`, `customer_dispatcher_notice`,`customer_deliverable_receipt_option`, `customer_credit_status_id`, `customer_billing_fax_number`, `customer_billing_email`, `customer_net_term`, `customer_id_status`, `customer_added_on`, `customer_added_by`) VALUES ('$next_id','$terminal_id','$code','$customer_type_id','$customer_name','$address_line','$address_city_id','$address_state_id','$address_zipcode_id','$address_country_id','$toll_free_number','$phone_number','$fax_phone_number','$company_email','$after_hours_email','$after_hours_phone_number','$load_notification_email','$dispatch_notes','$dispatcher_notice','$deliverable_receipt_option_id','$credit_status_id','$billing_fax_number','$billing_email','$net_terms','ACT','$time','$USERID')");

				if($insert){

					$status=true;

					$message="Added Successfuly";	

				}else{

					$message=SOMETHING_WENT_WROG.mysqli_error($GLOBALS['con']);

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





function customers_update($param){

		$status=false;

		$message=null;

		$response=null;

		if(in_array('P0164', USER_PRIV)){

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



			include_once APPROOT.'/models/masters/Companies.php';

			$Companies=new Companies;

			if(isset($param['terminal_id']) && $param['terminal_id']!=""){

				$terminal_id=senetize_input($param['terminal_id']);

				if(!$Companies->isValidId($terminal_id)){

					$InvalidDataMessage="Invalid address terminal id";

					$dataValidation=false;

					goto ValidationChecker;

				

				}



			}else{

				$InvalidDataMessage="Please provide terminal id";

				$dataValidation=false;

				goto ValidationChecker;			

			}	



			if(isset($param['code'])){

				$code=senetize_input($param['code']);



				//---check code duplicacy

				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `customer_code` FROM `customers` WHERE `customer_id_status`='ACT' AND `customer_code`='$code' AND  NOT `customer_id`='$update_id'"))>0){

					$InvalidDataMessage="Customer code already exists";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide customer code";

				$dataValidation=false;

				goto ValidationChecker;

			}

			if(isset($param['customer_name'])){

				$customer_name=senetize_input($param['customer_name']);

			}else{

				$InvalidDataMessage="Please provide customer name";

				$dataValidation=false;

				goto ValidationChecker;

			}

			if(isset($param['customer_type_id']) && $param['customer_type_id']!=""){

				$customer_type_id=senetize_input($param['customer_type_id']);

				if(in_array($customer_type_id,['BROKER','SHIPPER','3PL'])){

					$customer_type_id=senetize_input($param['customer_type_id']);

				}else{

					$InvalidDataMessage="Invalid customer type value";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}else{

				$InvalidDataMessage="Please provide customer type id";

				$dataValidation=false;

				goto ValidationChecker;			

			}			



			$address_line=(isset($param['address_line']))?senetize_input($param['address_line']):'';



			include_once APPROOT.'/models/masters/Locations.php';

			$Locations=new Locations;



			$address_country_id="";

			if(isset($param['address_country_id']) && $param['address_country_id']!=""){

				$address_country_id=senetize_input($param['address_country_id']);



				if(!$Locations->isValidLocationCountryId($address_country_id)){

					$InvalidDataMessage="Invalid address Country value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_state_id="";

			if(isset($param['address_state_id']) && $param['address_state_id']!=""){

				$address_state_id=senetize_input($param['address_state_id']);



				if(!$Locations->isValidLocationStateId($address_state_id)){

					$InvalidDataMessage="Invalid address state value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_city_id="";

			if(isset($param['address_city_id']) && $param['address_city_id']!=""){

				$address_city_id=senetize_input($param['address_city_id']);



				if(!$Locations->isValidLocationCityId($address_city_id)){

					$InvalidDataMessage="Invalid address city value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}



			$address_zipcode_id="";

			if(isset($param['address_zipcode_id']) && $param['address_zipcode_id']!=""){

				$address_zipcode_id=senetize_input($param['address_zipcode_id']);



				if(!$Locations->isValidLocationZipId($address_zipcode_id)){

					$InvalidDataMessage="Invalid address city value";

					$dataValidation=false;

					goto ValidationChecker;

				}

			}







			$dispatch_notes=(isset($param['dispatch_notes']))?senetize_input($param['dispatch_notes']):'';

			$dispatcher_notice=(isset($param['dispatcher_notice']))?senetize_input($param['dispatcher_notice']):'';



			$toll_free_number=(isset($param['toll_free_number']) && $param['toll_free_number']!="")?$Enc->enc_mob(senetize_input($param['toll_free_number'])):'';

			$phone_number=(isset($param['phone_number']) && $param['phone_number']!="")?$Enc->enc_mob(senetize_input($param['phone_number'])):'';

			$fax_phone_number=(isset($param['fax_phone_number']) && $param['fax_phone_number']!="")?$Enc->enc_mob(senetize_input($param['fax_phone_number'])):'';

			$company_email=(isset($param['email']) && $param['email']!="")?$Enc->enc_mail(senetize_input($param['email'])):'';

			$load_notification_email=(isset($param['load_notification_email']) && $param['load_notification_email']!="")?$Enc->enc_mail(senetize_input($param['load_notification_email'])):'';

			$after_hours_email=(isset($param['after_hours_email']) && $param['after_hours_email']!="")?$Enc->enc_mail(senetize_input($param['after_hours_email'])):'';

			$after_hours_phone_number=(isset($param['after_hours_phone_number']) && $param['after_hours_phone_number']!="")?$Enc->enc_mob(senetize_input($param['after_hours_phone_number'])):'';

			$billing_fax_number=(isset($param['billing_fax_number']) && $param['billing_fax_number']!="")?$Enc->enc_mob(senetize_input($param['billing_fax_number'])):'';

			$billing_email=(isset($param['billing_email']) && $param['billing_email']!="")?$Enc->enc_mail(senetize_input($param['billing_email'])):'';

							//-----data validation ends

			$net_terms="";

			if(isset($param['net_terms']) && $param['net_terms']!=""){

				$net_terms=senetize_input($param['net_terms']);

				if(!in_array($net_terms,['15','30'])){

					$InvalidDataMessage="Invalid net terms";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}





			$deliverable_receipt_option_id="";

			if(isset($param['deliverable_receipt_option_id']) && $param['deliverable_receipt_option_id']!=""){

				$deliverable_receipt_option_id=senetize_input($param['deliverable_receipt_option_id']);

				if(!in_array($deliverable_receipt_option_id,['EMAIL','EDI','FTP','API'])){

					$InvalidDataMessage="Invalid credit deliverable receipt option id";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}





			$credit_status_id="";

			if(isset($param['credit_status_id']) && $param['credit_status_id']!=""){

				$credit_status_id=senetize_input($param['credit_status_id']);

				if(!in_array($credit_status_id,['APPROVED','NOT-APPROVED'])){

					$InvalidDataMessage="Invalid credit status id";

					$dataValidation=false;

					goto ValidationChecker;					

				}



			}

			ValidationChecker:



			if($dataValidation){

				$time=time();

				$USERID=USER_ID;



					///-----//Generate New Unique Id

				$insert=mysqli_query($GLOBALS['con'],"UPDATE `customers` SET  `customer_terminal_id_fk`='$terminal_id',`customer_code`='$code', `customer_type_id`='$customer_type_id', `customer_name`='$customer_name', `customer_address_line`='$address_line', `customer_city_id_fk`='$address_city_id', `customer_state_id_fk`='$address_state_id', `customer_zipcode_id_fk`='$address_zipcode_id', `customer_country_id_fk`='$address_country_id', `customer_toll_free_number`='$toll_free_number', `customer_phone_number`='$phone_number', `customer_fax_phone_no`='$fax_phone_number', `customer_company_email`='$company_email', `customer_after_hours_email`='$after_hours_email', `customer_after_hours_phone_number`='$after_hours_phone_number', `customer_load_notification_email`='$load_notification_email', `customer_dispatch_notes`='$dispatch_notes', `customer_dispatcher_notice`='$dispatcher_notice',`customer_deliverable_receipt_option`='$deliverable_receipt_option_id', `customer_credit_status_id`='$credit_status_id', `customer_billing_fax_number`='$billing_fax_number', `customer_billing_email`='$billing_email', `customer_net_term`='$net_terms', `customer_updated_on`='$time', `customer_updated_by`='$USERID' WHERE `customer_id`='$update_id'");

				if($insert){

					$status=true;

					$message="Updated Successfuly";	

				}else{

					$message=SOMETHING_WENT_WROG.mysqli_error($GLOBALS['con']);

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





	function customers_details($param){

		$status=false;

		$message=null;

		$response=[];

		if(in_array('P0163', USER_PRIV)){

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

				$q=mysqli_query($GLOBALS['con'],"SELECT `customer_id`,`customer_code`, `customer_type_id`, `customer_name`, `customer_address_line`,`address_countries`.`location_name` AS `address_country_name`,`address_countries`.`location_id` AS `address_country_id`, `address_states`.`location_name` AS `address_state_name`,`address_states`.`location_id` AS `address_state_id`,`location_cities`.`location_name` AS `address_city_name`,`location_cities`.`location_id` AS `address_city_id`, `location_zipcodes`.`location_name` AS `address_zipcode_name`,`location_zipcodes`.`location_id` AS `address_zipcode_id`, `customer_toll_free_number`, `customer_phone_number`, `customer_fax_phone_no`, `customer_company_email`, `customer_after_hours_email`, `customer_after_hours_phone_number`, `customer_load_notification_email`, `customer_dispatch_notes`, `customer_dispatcher_notice`,`customer_deliverable_receipt_option` ,`customer_credit_status_id`, `customer_billing_fax_number`, `customer_billing_email`, `customer_net_term`,`customer_terminal_id_fk` FROM `customers` LEFT JOIN `locations` AS `address_countries` ON `address_countries`.`location_id`=`customers`.`customer_country_id_fk` LEFT JOIN `locations` AS `address_states` ON `address_states`.`location_id`=`customers`.`customer_state_id_fk` LEFT JOIN `locations` AS `location_cities` ON `location_cities`.`location_id`=`customers`.`customer_city_id_fk` LEFT JOIN `locations` AS `location_zipcodes` ON `location_zipcodes`.`location_id`=`customers`.`customer_zipcode_id_fk` LEFT JOIN `companies` AS `companies` ON `companies`.`company_id`=`customers`.`customer_terminal_id_fk` WHERE `customer_id_status`='ACT' AND `customer_id`='$customer_id'");

				if(mysqli_num_rows($q)==1){

					$status=true;

					$rows=mysqli_fetch_assoc($q);

					$response['details']=[

					'id'=>$rows['customer_id'],

					'eid'=>$Enc->safeurlen($rows['customer_id']),

					'terminal_id'=>$rows['customer_terminal_id_fk'],

					'customer_type'=>$rows['customer_type_id'],

					'customer_code'=>$rows['customer_code'],

					'customer_name'=>$rows['customer_name'],

					'address_line'=>$rows['customer_address_line'],

					'address_country'=>$rows['address_country_name']!=null?$rows['address_country_name']:"",

					'address_country_id'=>$rows['address_country_id']!=null?$rows['address_country_id']:"",

					'address_state'=>$rows['address_state_name']!=null?$rows['address_state_name']:"",

					'address_state_id'=>$rows['address_state_id']!=null?$rows['address_state_id']:"",

					'address_city'=>$rows['address_city_name']!=null?$rows['address_city_name']:"",

					'address_city_id'=>$rows['address_city_id']!=null?$rows['address_city_id']:"",

					'address_zipcode'=>$rows['address_zipcode_name']!=null?$rows['address_zipcode_name']:"",

					'address_zipcode_id'=>$rows['address_zipcode_id']!=null?$rows['address_zipcode_id']:"",

					'address'=>$row['address']=$rows['customer_address_line'].', '.$rows['address_city_name'].', '.$rows['address_state_name'].', '.$rows['address_zipcode_name'],

					'toll_free_number'=>$Enc->dec_mob($rows['customer_toll_free_number']),

					'phone_number'=>$Enc->dec_mob($rows['customer_phone_number']),

					'fax_phone_no'=>$Enc->dec_mob($rows['customer_fax_phone_no']),

					'company_email'=>$Enc->dec_mail($rows['customer_company_email']),

					'after_hours_phone_number'=>$Enc->dec_mob($rows['customer_after_hours_phone_number']),

					'after_hours_email'=>$Enc->dec_mail($rows['customer_after_hours_email']),

					'load_notification_email'=>$Enc->dec_mail($rows['customer_load_notification_email']),

					'dispatch_notes'=>$rows['customer_dispatch_notes'],

					'dispatcher_notice'=>$rows['customer_dispatcher_notice'],

					'deliverable_receipt_option'=>$rows['customer_deliverable_receipt_option'],

					'credit_status_id'=>$rows['customer_credit_status_id'],

					'billing_fax_number'=>$Enc->dec_mob($rows['customer_billing_fax_number']),

					'billing_email'=>$Enc->dec_mail($rows['customer_billing_email']),

					'net_terms'=>$rows['customer_net_term'],

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







		function customers_list($param){

			$status=false;

			$message=null;

			$response=null;

			$batch=500;

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



			$q="SELECT `customer_id`,`customer_code`, `customer_type_id`, `customer_name`, `customer_address_line`,`address_countries`.`location_name` AS `address_country_name`, `address_states`.`location_name` AS `address_state_name`,`location_cities`.`location_name` AS `address_city_name`, `location_zipcodes`.`location_name` AS `address_zipcode_name`, `customer_toll_free_number`, `customer_phone_number`, `customer_fax_phone_no`, `customer_company_email`, `customer_after_hours_email`, `customer_after_hours_phone_number`, `customer_load_notification_email`, `customer_dispatch_notes`, `customer_dispatcher_notice`, `customer_credit_status_id`, `customer_billing_fax_number`, `customer_billing_email`, `customer_net_term` FROM `customers` LEFT JOIN `locations` AS `address_countries` ON `address_countries`.`location_id`=`customers`.`customer_country_id_fk` LEFT JOIN `locations` AS `address_states` ON `address_states`.`location_id`=`customers`.`customer_state_id_fk` LEFT JOIN `locations` AS `location_cities` ON `location_cities`.`location_id`=`customers`.`customer_city_id_fk` LEFT JOIN `locations` AS `location_zipcodes` ON `location_zipcodes`.`location_id`=`customers`.`customer_zipcode_id_fk` WHERE `customer_id_status`='ACT'";







//----Apply Filters starts





			if(isset($param['type_id']) && $param['type_id']!=""){

				$q .=" AND `customer_type_id`='".senetize_input($param['type_id'])."'";

			}



//-----Apply fitlers ends









			$order_by_type='ASC';

			if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){

				$order_by_type='DESC';

			}

			if(isset($param['sort_by'])){

				switch ($param['sort_by']) {

					case 'type_id':

					$q .=" ORDER BY `type_id`";

					break;	

					default:

					$q .=" ORDER BY `customer_code`";

					break;

				}

			}else{

				$q .=" ORDER BY `customer_code`";	

			}









			$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));

			$q .=" limit $from, $range";

			$qEx=mysqli_query($GLOBALS['con'],$q);



			$list=[];

			while ($rows=mysqli_fetch_assoc($qEx)) {

				array_push($list,[

					'id'=>$rows['customer_id'],

					'eid'=>$Enc->safeurlen($rows['customer_id']),

					'customer_type'=>$rows['customer_type_id'],

					'customer_code'=>$rows['customer_code'],

					'customer_name'=>$rows['customer_name'],

					'address_country'=>$rows['address_country_name']!=null?$rows['address_country_name']:"",

					'address_state'=>$rows['address_state_name']!=null?$rows['address_state_name']:"",

					'address_city'=>$rows['address_city_name']!=null?$rows['address_city_name']:"",

					'address_zipcode'=>$rows['address_zipcode_name']!=null?$rows['address_zipcode_name']:"",

					'address'=>$row['address']=$rows['customer_address_line'].', '.$rows['address_city_name'].', '.$rows['address_state_name'].', '.$rows['address_zipcode_name'],

					'toll_free_number'=>$Enc->dec_mob($rows['customer_toll_free_number']),

					'phone_number'=>$Enc->dec_mob($rows['customer_phone_number']),

					'fax_phone_no'=>$Enc->dec_mob($rows['customer_fax_phone_no']),

					'company_email'=>$Enc->dec_mail($rows['customer_company_email']),

					'after_hours_email'=>$Enc->dec_mail($rows['customer_after_hours_email']),

					'load_notification_email'=>$Enc->dec_mail($rows['customer_load_notification_email']),

					'dispatch_notes'=>$rows['customer_dispatch_notes'],

					'dispatcher_notice'=>$rows['customer_dispatcher_notice'],

					'credit_status_id'=>$rows['customer_credit_status_id'],

					'billing_fax_number'=>$Enc->dec_mob($rows['customer_billing_fax_number']),

					'billing_email'=>$Enc->dec_mail($rows['customer_billing_email']),

					'net_terms'=>$rows['customer_net_term'],

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