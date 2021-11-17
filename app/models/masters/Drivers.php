<?php
/**
 *
 */
class Drivers
{
	function drivers_quick_list($param){

		$q="SELECT `driver_id`,`driver_code`,`driver_name_first`,`driver_name_middle`,`driver_name_last` FROM `drivers` WHERE `driver_status`='ACT'";

		$q .=" ORDER BY `driver_code` ASC";

		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
	include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['driver_id'];
			$row['eid']=$Enc->safeurlen($rows['driver_id']);
			$row['code']=$rows['driver_code'];
			$row['name']=$rows['driver_name_first'].' '.$rows['driver_name_middle'].' '.$rows['driver_name_last'];
			array_push($list,$row);
		}
		$response['list']=$list; 		
		$r=[];
		$r['status']=true;
		$r['message']=null;
		$r['response']=$response;
		return $r;	
	}

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `driver_id` from `drivers` WHERE `driver_id`='$id' AND `driver_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function drivers_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0008', USER_PRIV)){


			if(isset($param['code']) && $param['code']!=""){
				$code=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['code']));
				$USERID=USER_ID;
				$time=time();
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				include_once APPROOT.'/models/masters/Locations.php';
				$Locations=new Locations;

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";


				///check if duplicate truck id is being created
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `driver_id` FROM `drivers` WHERE `driver_status`='ACT' AND `driver_code`='$code' "))>0){
					$InvalidDataMessage="Driver ID already exists";
					$dataValidation=false;
				}





				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/EmployeesStatus.php';
					$EmployeesStatus=new EmployeesStatus;

					if(!$EmployeesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}

				$prefix_id=0;
				if(isset($param['prefix_id']) && $param['prefix_id']!=""){
					$prefix_id=mysqli_real_escape_string($GLOBALS['con'],$param['prefix_id']);

					include_once APPROOT.'/models/masters/EmployeesPrefix.php';
					$EmployeesPrefix=new EmployeesPrefix;

					if(!$EmployeesPrefix->isValidId($prefix_id)){
						$InvalidDataMessage="Invalid prefix";
						$dataValidation=false;
					}

				}				

				$name_first=(isset($param['name_first']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_first']):'0';
				$name_middle=(isset($param['name_middle']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_middle']):'0';
				$name_last=(isset($param['name_last']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_last']):'0';

				$dob="0000-00-00";
				if(isset($param['dob']) && $param['dob']!=""){
					if(isValidDateFormat($param['dob'])){
						$dob=date('Y-m-d', strtotime($param['dob']));
					}else{
						$InvalidDataMessage="Invalid date of birth";
						$dataValidation=false;						
					}
					
				}else{
					$InvalidDataMessage="Please provide date of birth";
					$dataValidation=false;
				}



				$mobile_country_code_id=0;
				if(isset($param['mobile_country_code_id']) && $param['mobile_country_code_id']!=""){
					$mobile_country_code_id=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_country_code_id']);

					include_once APPROOT.'/models/masters/MobileCountryCodes.php';
					$MobileCountryCodes=new MobileCountryCodes;

					if(!$MobileCountryCodes->isValidId($mobile_country_code_id)){
						$InvalidDataMessage="Invalid country code";
						$dataValidation=false;
					}

				}

				$mobile_number="";
				if(isset($param['mobile_number']) && $param['mobile_number']!=""){
					$mobile_number=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_number']);

					if(!isValidMobileNumber($mobile_number)){
						$InvalidDataMessage="Invalid mobile number";
						$dataValidation=false;
					}
					$mobile_number=$Enc->enc_mob($mobile_number);

				}


				$email="";
				if(isset($param['email']) && $param['email']!=""){
					$email=mysqli_real_escape_string($GLOBALS['con'],$param['email']);


						// Remove all illegal characters from email
					$email = filter_var($email, FILTER_SANITIZE_EMAIL);

				// Validate e-mail
					if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$InvalidDataMessage="Invalid email";
						$dataValidation=false;
					}
					$email=$Enc->enc_mail($email);

				}




				$address_line=(isset($param['address_line']))?mysqli_real_escape_string($GLOBALS['con'],$param['address_line']):'';

				$address_state_id=0;
				if(isset($param['address_state_id']) && $param['address_state_id']!=""){
					$address_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_state_id']);

					if(!$Locations->isValidLocationStateId($address_state_id)){
						$InvalidDataMessage="Invalid address state value";
						$dataValidation=false;
					}
				}

				$address_city_id=0;
				if(isset($param['address_city_id']) && $param['address_city_id']!=""){
					$address_city_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_city_id']);

					if(!$Locations->isValidLocationCityId($address_city_id)){
						$InvalidDataMessage="Invalid address city value";
						$dataValidation=false;
					}
				}

				$address_zipcode_id=0;
				if(isset($param['address_zipcode_id']) && $param['address_zipcode_id']!=""){
					$address_zipcode_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_zipcode_id']);

					if(!$Locations->isValidLocationZipId($address_zipcode_id)){
						$InvalidDataMessage="Invalid address city value";
						$dataValidation=false;
					}
				}

				$company_id=0;
				if(isset($param['company_id']) && $param['company_id']!=""){
					$company_id=mysqli_real_escape_string($GLOBALS['con'],$param['company_id']);

					include_once APPROOT.'/models/masters/Companies.php';
					$Companies=new Companies;

					if(!$Companies->isValidId($company_id)){
						$InvalidDataMessage="Invalid company value";
						$dataValidation=false;
					}

				}


				$date_of_joining_raw=(isset($param['date_of_joining']))?mysqli_real_escape_string($GLOBALS['con'],$param['date_of_joining']):'00/00/0000';

				$date_of_joining=isValidDateFormat($date_of_joining_raw)?date('Y-m-d', strtotime($date_of_joining_raw)):'0000-00-00';

				$route_type_id=0;
				if(isset($param['route_type_id']) && $param['route_type_id']!=""){
					$route_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['route_type_id']);

					include_once APPROOT.'/models/masters/RouteTypes.php';
					$RouteTypes=new RouteTypes;

					if(!$RouteTypes->isValidId($route_type_id)){
						$InvalidDataMessage="Invalid route type value";
						$dataValidation=false;
					}

				}

				$cdl_no=(isset($param['cdl_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_no']):'';





				$cdl_state_id=0;
				if(isset($param['cdl_state_id']) && $param['cdl_state_id']!=""){
					$cdl_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['cdl_state_id']);

					if(!$Locations->isValidLocationStateId($cdl_state_id)){
						$InvalidDataMessage="Invalid CDL state value";
						$dataValidation=false;
					}
				}




				$cdl_issue_date_raw=(isset($param['cdl_issue_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_issue_date']):'00/00/0000';

				$cdl_issue_date=isValidDateFormat($cdl_issue_date_raw)?date('Y-m-d', strtotime($cdl_issue_date_raw)):'0000-00-00';



				$cdl_expiry_date_raw=(isset($param['cdl_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_expiry_date']):'00/00/0000';

				$cdl_expiry_date=isValidDateFormat($cdl_expiry_date_raw)?date('Y-m-d', strtotime($cdl_expiry_date_raw)):'0000-00-00';



				$ssn_number=(isset($param['ssn_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['ssn_number']):'';



				$residency_id=0;
				if(isset($param['residency_id']) && $param['residency_id']!=""){
					$residency_id=mysqli_real_escape_string($GLOBALS['con'],$param['residency_id']);

					include_once APPROOT.'/models/masters/EmployeesResidency.php';
					$EmployeesResidency=new EmployeesResidency;

					if(!$EmployeesResidency->isValidId($residency_id)){
						$InvalidDataMessage="Invalid maker value";
						$dataValidation=false;
					}

				}


				$residency_expiry_date_raw=(isset($param['residency_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['residency_expiry_date']):'00/00/0000';

				$residency_expiry_date=isValidDateFormat($residency_expiry_date_raw)?date('Y-m-d', strtotime($residency_expiry_date_raw)):'0000-00-00';



				$medical_issue_date_raw=(isset($param['medical_issue_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['medical_issue_date']):'00/00/0000';

				$medical_issue_date=isValidDateFormat($medical_issue_date_raw)?date('Y-m-d', strtotime($medical_issue_date_raw)):'0000-00-00';



				$medical_expiry_date_raw=(isset($param['medical_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['medical_expiry_date']):'00/00/0000';

				$medical_expiry_date=isValidDateFormat($medical_expiry_date_raw)?date('Y-m-d', strtotime($medical_expiry_date_raw)):'0000-00-00';

				$gfr=(isset($param['gfr']))?mysqli_real_escape_string($GLOBALS['con'],$param['gfr']):'';


				$epn_enroll_status=(isset($param['epn_enroll_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['epn_enroll_status']):'';
				$epn_enroll_status=($epn_enroll_status=='Yes' || $epn_enroll_status=='No')?$epn_enroll_status:"";





				$last_annual_review_date_raw=(isset($param['last_annual_review_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['last_annual_review_date']):'00/00/0000';

				$last_annual_review_date=isValidDateFormat($last_annual_review_date_raw)?date('Y-m-d', strtotime($last_annual_review_date_raw)):'0000-00-00';



				$next_annual_review_date_raw=(isset($param['next_annual_review_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['next_annual_review_date']):'00/00/0000';

				$next_annual_review_date=isValidDateFormat($next_annual_review_date_raw)?date('Y-m-d', strtotime($next_annual_review_date_raw)):'0000-00-00';





				$assigned_truck_id=0;
				if(isset($param['assigned_truck_id']) && $param['assigned_truck_id']!=""){
					$assigned_truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['assigned_truck_id']);

					include_once APPROOT.'/models/masters/Trucks.php';
					$Trucks=new Trucks;

					if(!$Trucks->isValidId($assigned_truck_id)){
						$InvalidDataMessage="Invalid truck assigned";
						$dataValidation=false;
					}

				}

				$insurance_added_status=(isset($param['insurance_added_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_added_status']):'';
				$insurance_added_status=($insurance_added_status=='Yes' || $insurance_added_status=='No')?$insurance_added_status:"";
				
				$group_id=0;
				if(isset($param['group_id']) && $param['group_id']!=""){
					$group_id=mysqli_real_escape_string($GLOBALS['con'],$param['group_id']);

					include_once APPROOT.'/models/masters/DriverGroups.php';
					$DriverGroups=new DriverGroups;

					if(!$DriverGroups->isValidId($group_id)){
						$InvalidDataMessage="Invalid group value";
						$dataValidation=false;
					}

				}

				//-----data validation ends



				if($dataValidation){
 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `driver_id` FROM `drivers` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['driver_id'])+1:0;
					///-----//Generate New Unique Id

					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `drivers`(`driver_id`,`driver_code`, `driver_name_prefix_id_fk`, `driver_name_first`, `driver_name_middle`, `driver_name_last`, `driver_dob`, `driver_mobile_country_code_id_fk`, `driver_mobile_no`, `driver_email`, `driver_address_line`, `driver_address_state_id_fk`, `driver_address_city_id_fk`, `driver_address_zipcode_id_fk`,`driver_group_id_fk`,`driver_company_id_fk`, `driver_date_of_joining`, `driver_route_type_id_fk`, `driver_cdl_no`, `driver_cdl_state_id_fk`, `driver_cdl_issue_date`, `driver_cdl_expiry_date`, `driver_ssn_number`, `driver_residency_type_id_fk`, `driver_residency_expiry_date`, `driver_medical_issue_date`, `driver_medical_expiry_date`, `driver_gfr`, `driver_epn_enroll_status`,`driver_last_annual_review_date`, `driver_next_annual_review_date`,`driver_truck_assigned_id_fk`,`driver_insurance_added_status`, `driver_status_id_fk`, `driver_status`, `driver_added_on`, `driver_added_by`) VALUES ('$next_id','$code','$prefix_id','$name_first','$name_middle','$name_last','$dob','$mobile_country_code_id','$mobile_number','$email','$address_line','$address_state_id','$address_city_id','$address_zipcode_id','$group_id','$company_id','$date_of_joining','$route_type_id','$cdl_no','$cdl_state_id','$cdl_issue_date','$cdl_expiry_date','$ssn_number','$residency_id','$residency_expiry_date','$medical_issue_date','$medical_expiry_date','$gfr','$epn_enroll_status','$last_annual_review_date','$next_annual_review_date','$assigned_truck_id','$insurance_added_status','$status_id','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function drivers_details($param){
		$status=false;
		$message=null;
		$response=[];

		$dataValidation=true;
		$InvalidDataMessage="";
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		//----------check if the valid driver id is send or not
		if(isset($param['driver_eid'])){
			$driver_id=$Enc->safeurlde($param['driver_eid']);
		}else{
			$InvalidDataMessage="Please provide driver eid";
			$dataValidation=false;
			goto ValidationChecker;			
		}

		ValidationChecker:
		if($dataValidation){
			$q="SELECT `driver_id`, `driver_code`,`group_id`,`group_name`,`route_type_id`,`route_type_name`,`status_name`,`prefix_id`, `prefix_name`, `driver_name_first`, `driver_name_middle`, `driver_name_last`, `driver_dob`, `driver_mobile_no`,`mobile_country_code_id`, `mobile_country_code`, `driver_email`, `driver_address_line`, `address_states`.`location_id` AS `address_state_id`, `cdl_states`.`location_id` AS `cdl_state_id`,`location_cities`.`location_id` AS `address_city_id`, `location_zipcodes`.`location_id` AS `address_zipcode_id`,`address_states`.`location_name` AS `address_state_name`, `cdl_states`.`location_name` AS `cdl_state_name`,`location_cities`.`location_name` AS `address_city_name`, `location_zipcodes`.`location_name` AS `address_zipcode_name`,`company_id`,`company_name`, `driver_date_of_joining`, `driver_cdl_no`, `driver_cdl_state_id_fk`, `driver_cdl_issue_date`, `driver_cdl_expiry_date`, `driver_ssn_number`,`residency_id`, `residency_name`, `driver_residency_expiry_date`, `driver_medical_issue_date`,`driver_medical_expiry_date`,`driver_gfr`, `driver_epn_enroll_status`,`driver_last_annual_review_date`, `driver_next_annual_review_date`,`truck_id`, `truck_code`, `status_name`,`status_id`, `driver_status`, `driver_insurance_added_status`, `driver_added_by`, `driver_updated_on`, `driver_updated_by`, `driver_deleted_on`, `driver_deleted_by` FROM `drivers` LEFT JOIN `employee_prefix` ON `employee_prefix`.`prefix_id`=`drivers`.`driver_name_prefix_id_fk` LEFT JOIN `mobile_country_codes` ON `mobile_country_codes`.`mobile_country_code_id`=`drivers`.`driver_mobile_country_code_id_fk` LEFT JOIN `locations` AS `address_states` ON `address_states`.`location_id`=`drivers`.`driver_address_state_id_fk` LEFT JOIN `locations` AS `location_cities` ON `location_cities`.`location_id`=`drivers`.`driver_address_city_id_fk` LEFT JOIN `locations` AS `location_zipcodes` ON `location_zipcodes`.`location_id`=`drivers`.`driver_address_zipcode_id_fk` LEFT JOIN `employee_residency` ON `employee_residency`.`residency_id`=`drivers`.`driver_residency_type_id_fk` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`drivers`.`driver_truck_assigned_id_fk` LEFT JOIN `employee_status` ON `employee_status`.`status_id`=`drivers`.`driver_status_id_fk` LEFT JOIN `companies` ON `companies`.`company_id`=`drivers`.`driver_company_id_fk` LEFT JOIN `locations` AS `cdl_states` ON `cdl_states`.`location_id`=`drivers`.`driver_cdl_state_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`drivers`.`driver_group_id_fk` LEFT JOIN `route_types` ON `route_types`.`route_type_id`=`drivers`.`driver_route_type_id_fk` WHERE `driver_status`='ACT' AND `driver_id`='$driver_id'";
			$qEx=mysqli_query($GLOBALS['con'],$q);
				if(mysqli_num_rows($qEx)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($qEx);
				$row=[];
				$row['id']=$rows['driver_id'];
				$row['eid']=$Enc->safeurlen($rows['driver_id']);
				$row['status_id']=$rows['status_id'];
				$row['status']=$rows['status_name'];
				$row['code']=$rows['driver_code'];
				$row['prefix_id']=$rows['prefix_id'];
				$row['prefix']=$rows['prefix_name'];
				$row['group_id']=$rows['group_id'];
				$row['group']=$rows['group_name'];
				$name_middle=($rows['driver_name_middle'])?' '.$rows['driver_name_middle']:'';
				$name_last=($rows['driver_name_last'])?' '.$rows['driver_name_last']:'';
				$row['name']=$rows['driver_name_first'].$name_middle.$name_last;
				$row['name_first']=$rows['driver_name_first'];
				$row['name_middle']=$rows['driver_name_middle'];
				$row['name_last']=$rows['driver_name_last'];
				$row['dob']=dateFromDbToFormat($rows['driver_dob']);
				$row['mobile_number']=$Enc->dec_mob($rows['driver_mobile_no']);
				$row['mobile_country_code']=$rows['mobile_country_code'];
				$row['mobile_country_code_id']=$rows['mobile_country_code_id'];
				$row['mobile_number_display']='+'.$rows['mobile_country_code'].' '.$row['mobile_number'];
				$row['email']=$Enc->dec_mail($rows['driver_email']);
				$row['address_line']=$rows['driver_address_line'];
				$row['address_city_id']=$rows['address_city_id'];
				$row['address_city_name']=$rows['address_city_name'];
				$row['address_state_id']=$rows['address_state_id'];
				$row['address_state_name']=$rows['address_state_name'];
				$row['address_zipcode_id']=$rows['address_zipcode_id'];
				$row['address_zipcode_name']=$rows['address_zipcode_name'];
				$row['company_id']=$rows['company_id'];
				$row['company']=$rows['company_name'];
				$row['date_of_joining']=dateFromDbToFormat($rows['driver_date_of_joining']);
				$row['route_type_id']=$rows['route_type_id'];
				$row['route_type']=$rows['route_type_name'];
				$row['cdl_number']=$rows['driver_cdl_no'];
				$row['cdl_state_id']=$rows['cdl_state_id'];
				$row['cdl_state']=$rows['cdl_state_name'];
				$row['cdl_issue_date']=dateFromDbToFormat($rows['driver_cdl_issue_date']);
				$row['cdl_expiry_date']=dateFromDbToFormat($rows['driver_cdl_expiry_date']);
				$row['residency_id']=$rows['residency_id'];
				$row['residency_type']=$rows['residency_name'];
				$row['ssn_number']=$rows['driver_ssn_number'];




$ssn_string=$rows['driver_ssn_number'];
$ssn_length=strlen($ssn_string);
if($ssn_length>4){
	$strA= substr($ssn_string, -4, 4);
	$strB=str_repeat('*', $ssn_length-4);
	$ssn_enc_string=$strB.$strA;
}else{
	$ssn_enc_string=$ssn_string;
}







				$row['ssn_number_enc']=$ssn_enc_string;
				$row['residency_expiry_date']=dateFromDbToFormat($rows['driver_residency_expiry_date']);
				$row['medical_issue_date']=dateFromDbToFormat($rows['driver_medical_issue_date']);
				$row['medical_expiry_date']=dateFromDbToFormat($rows['driver_medical_expiry_date']);
				$row['gfr']=$rows['driver_gfr'];
				$row['epn_enroll_status']=$rows['driver_epn_enroll_status'];
				$row['last_annual_review_date']=dateFromDbToFormat($rows['driver_last_annual_review_date']);
				$row['next_annual_review_date']=dateFromDbToFormat($rows['driver_next_annual_review_date']);
				$row['assigned_truck_id']=$rows['truck_id'];
				$row['truck_code']=$rows['truck_code'];
				$row['insurance_added_status']=$rows['driver_insurance_added_status'];
				$row['status']=$rows['status_name'];
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
		$r['param']=$param;
		return $r;	
	}	

	function drivers_list($param){
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

		$q="SELECT `driver_id`, `driver_code`,`group_name`,`route_type_name`,`status_name`, `prefix_name`, `driver_name_first`, `driver_name_middle`, `driver_name_last`, `driver_dob`, `driver_mobile_no`, `mobile_country_code`, `driver_email`, `driver_address_line`, `address_states`.`location_name` AS `address_state_name`, `cdl_states`.`location_name` AS `cdl_state_name`,`location_cities`.`location_name` AS `address_city_name`, `location_zipcodes`.`location_name` AS `address_zipcode_name`,`company_name`, `driver_date_of_joining`, `driver_route_type_id_fk`, `driver_cdl_no`, `driver_cdl_state_id_fk`, `driver_cdl_issue_date`, `driver_cdl_expiry_date`, `driver_ssn_number`, `residency_name`, `driver_residency_expiry_date`, `driver_medical_issue_date`,`driver_medical_expiry_date`,`driver_gfr`, `driver_epn_enroll_status`,`driver_last_annual_review_date`, `driver_next_annual_review_date`, `truck_code`, `status_name`, `driver_status`, `driver_insurance_added_status`, `driver_added_by`, `driver_updated_on`, `driver_updated_by`, `driver_deleted_on`, `driver_deleted_by` FROM `drivers` LEFT JOIN `employee_prefix` ON `employee_prefix`.`prefix_id`=`drivers`.`driver_name_prefix_id_fk` LEFT JOIN `mobile_country_codes` ON `mobile_country_codes`.`mobile_country_code_id`=`drivers`.`driver_mobile_country_code_id_fk` LEFT JOIN `locations` AS `address_states` ON `address_states`.`location_id`=`drivers`.`driver_address_state_id_fk` LEFT JOIN `locations` AS `location_cities` ON `location_cities`.`location_id`=`drivers`.`driver_address_city_id_fk` LEFT JOIN `locations` AS `location_zipcodes` ON `location_zipcodes`.`location_id`=`drivers`.`driver_address_zipcode_id_fk` LEFT JOIN `employee_residency` ON `employee_residency`.`residency_id`=`drivers`.`driver_residency_type_id_fk` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`drivers`.`driver_truck_assigned_id_fk` LEFT JOIN `employee_status` ON `employee_status`.`status_id`=`drivers`.`driver_status_id_fk` LEFT JOIN `companies` ON `companies`.`company_id`=`drivers`.`driver_company_id_fk` LEFT JOIN `locations` AS `cdl_states` ON `cdl_states`.`location_id`=`drivers`.`driver_cdl_state_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`drivers`.`driver_group_id_fk` LEFT JOIN `route_types` ON `route_types`.`route_type_id`=`drivers`.`driver_route_type_id_fk` WHERE `driver_status`='ACT'";



//----Apply Filters starts




/*
		if(isset($param['ownership_type']) && $param['ownership_type']!=""){
			$ownership_type=mysqli_real_escape_string($GLOBALS['con'],$param['ownership_type']);
			$q .=" AND truck_ownership_type_id_fk='$ownership_type'";
		}*/

//-----Apply fitlers ends





		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name_first':
				$q .=" ORDER BY `name_first`";
				break;		
				default:
				$q .=" ORDER BY `driver_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `driver_id`";	
		}




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
		    $name_first=($rows['driver_name_first']!='')?$rows['driver_name_first']:'';
			$name_middle=($rows['driver_name_middle']!='')?' '.$rows['driver_name_middle']:'';
			$name_last=($rows['driver_name_last']!='')?' '.$rows['driver_name_last']:'';
			$row=[];
			$row['id']=$rows['driver_id'];
			$row['eid']=$Enc->safeurlen($rows['driver_id']);
			$row['status']=$rows['status_name'];
			$row['code']=$rows['driver_code'];
			$row['name_prfix']=$rows['prefix_name'];
			$row['group']=$rows['group_name'];
			$row['name']=$name_first.$name_middle.$name_last;
			$row['name_first']=$rows['driver_name_first'];
			$row['name_middle']=$rows['driver_name_middle'];
			$row['name_last']=$rows['driver_name_last'];
			$row['dob']=dateFromDbToFormat($rows['driver_dob']);
			$row['mobile_number']=$Enc->dec_mob($rows['driver_mobile_no']);
			$row['mobile_country_code']=$rows['mobile_country_code'];
			$row['mobile_number_display']='+'.$rows['mobile_country_code'].' '.$row['mobile_number'];
			$row['email']=($Enc->dec_mail($rows['driver_email'])==false)?'':$Enc->dec_mail($rows['driver_email']);
			$row['address']=$rows['driver_address_line'].', '.$rows['address_city_name'].', '.$rows['address_state_name'].', '.$rows['address_zipcode_name'];
			$row['company']=$rows['company_name'];
			$row['date_of_joining']=dateFromDbToFormat($rows['driver_date_of_joining']);
			$row['route_type']=$rows['route_type_name'];
			$row['cdl_number']=$rows['driver_cdl_no'];
			$row['cdl_state']=$rows['cdl_state_name'];
			$row['cdl_issue_date']=dateFromDbToFormat($rows['driver_cdl_issue_date']);
			$row['cdl_expiry_date']=dateFromDbToFormat($rows['driver_cdl_expiry_date']);
			$row['residency_type']=$rows['residency_name'];
			$row['ssn_number']=$rows['driver_ssn_number'];
			$row['residency_expiry_date']=dateFromDbToFormat($rows['driver_residency_expiry_date']);
			$row['medical_issue_date']=dateFromDbToFormat($rows['driver_medical_issue_date']);
			$row['medical_expiry_date']=dateFromDbToFormat($rows['driver_medical_expiry_date']);
			$row['gfr']=$rows['driver_gfr'];
			$row['epn_enroll_status']=$rows['driver_epn_enroll_status'];
			$row['last_annual_review_date']=dateFromDbToFormat($rows['driver_last_annual_review_date']);
			$row['next_annual_review_date']=dateFromDbToFormat($rows['driver_next_annual_review_date']);
			$row['truck_code']=$rows['truck_code'];
			$row['inusrance_added_status']=$rows['driver_insurance_added_status'];
			$row['status']=$rows['status_name'];
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





	function trucks_list_basic($param){
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

		$q="SELECT `truck_id`, `truck_code` FROM `trucks` LEFT JOIN `companies` ON `companies`.`company_id`=`trucks`.`truck_company_id_fk` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`trucks`.`truck_make_id_fk` LEFT JOIN `vehicle_models` ON `vehicle_models`.`model_id`=`trucks`.`truck_model_id_fk` LEFT JOIN `location_states` ON `location_states`.`state_id`=`trucks`.`truck_licence_state_id`LEFT JOIN `vehicle_status` ON `vehicle_status`.`status_id`=`trucks`.`truck_status_id_fk` LEFT JOIN `lease_companies` ON `lease_companies`.`lease_company_id`=`trucks`.`truck_lease_company_id_fk`LEFT JOIN `device_companies` ON `device_companies`.`device_company_id`=`trucks`.`truck_device_company_id_fk` LEFT JOIN `vehicle_ownership_types` ON `vehicle_ownership_types`.`ownership_type_id`=`trucks`.`truck_ownership_type_id_fk` LEFT JOIN `insurance_companies` ON `insurance_companies`.`insurance_company_id`=`trucks`.`truck_insurance_company_id_fk` LEFT JOIN `vehicle_colors` ON `vehicle_colors`.`color_id`=`trucks`.`truck_color_id_fk` WHERE `truck_status`='ACT'";



//----Apply Filters starts


		if(isset($param['status_id']) && $param['status_id']!=""){
			$truck_status_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q .=" AND truck_status_id_fk='$truck_status_id_fk'";
		}

		if(isset($param['company_id']) && $param['company_id']!=""){
			$company_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['company_id']);
			$q .=" AND truck_company_id_fk='$company_id_fk'";
		}
		if(isset($param['lease_company_id']) && $param['lease_company_id']!=""){
			$lease_company_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['lease_company_id']);
			$q .=" AND truck_lease_company_id_fk='$lease_company_id_fk'";
		}


		if(isset($param['ownership_type']) && $param['ownership_type']!=""){
			$ownership_type=mysqli_real_escape_string($GLOBALS['con'],$param['ownership_type']);
			$q .=" AND truck_ownership_type_id_fk='$ownership_type'";
		}

//-----Apply fitlers ends




		$order_by_type='ASC';
		if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){
			$order_by_type='DESC';
		}
		$q .=" ORDER BY `truck_code`";	
		




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['truck_id'];
			$row['code']=$rows['truck_code'];
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


	function drivers_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0010', USER_PRIV)){


			if(isset($param['code']) && isset($param['update_eid'])){

				$code=mysqli_real_escape_string($GLOBALS['con'],$param['code']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();
				include_once APPROOT.'/models/masters/Locations.php';
				$Locations=new Locations;

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";



				///check if duplicate truck id is being created
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `driver_id` FROM `drivers` WHERE `driver_status`='ACT' AND `driver_code`='$code' AND NOT `driver_id`='$update_id'"))>0){
					$InvalidDataMessage="Driver ID already exists";
					$dataValidation=false;
				}



				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/EmployeesStatus.php';
					$EmployeesStatus=new EmployeesStatus;

					if(!$EmployeesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}

				$prefix_id=0;
				if(isset($param['prefix_id']) && $param['prefix_id']!=""){
					$prefix_id=mysqli_real_escape_string($GLOBALS['con'],$param['prefix_id']);

					include_once APPROOT.'/models/masters/EmployeesPrefix.php';
					$EmployeesPrefix=new EmployeesPrefix;

					if(!$EmployeesPrefix->isValidId($prefix_id)){
						$InvalidDataMessage="Invalid prefix";
						$dataValidation=false;
					}

				}				

				$name_first=(isset($param['name_first']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_first']):'0';
				$name_middle=(isset($param['name_middle']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_middle']):'0';
				$name_last=(isset($param['name_last']))?mysqli_real_escape_string($GLOBALS['con'],$param['name_last']):'0';

				$dob="0000-00-00";
				if(isset($param['dob']) && isValidDateFormat($param['dob'])){
					$dob=date('Y-m-d', strtotime($param['dob']));
				}else{
					$InvalidDataMessage="Invalid date of birth";
					$dataValidation=false;
				}



				$mobile_country_code_id=0;
				if(isset($param['mobile_country_code_id']) && $param['mobile_country_code_id']!=""){
					$mobile_country_code_id=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_country_code_id']);

					include_once APPROOT.'/models/masters/MobileCountryCodes.php';
					$MobileCountryCodes=new MobileCountryCodes;

					if(!$MobileCountryCodes->isValidId($mobile_country_code_id)){
						$InvalidDataMessage="Invalid country code";
						$dataValidation=false;
					}

				}

				$mobile_number="";
				if(isset($param['mobile_number']) && $param['mobile_number']!=""){
					$mobile_number=mysqli_real_escape_string($GLOBALS['con'],$param['mobile_number']);

					if(!isValidMobileNumber($mobile_number)){
						$InvalidDataMessage="Invalid mobile number";
						$dataValidation=false;
					}
					$mobile_number=$Enc->enc_mob($mobile_number);

				}


				$email="";
				if(isset($param['email']) && $param['email']!=""){
					$email=mysqli_real_escape_string($GLOBALS['con'],$param['email']);


						// Remove all illegal characters from email
					$email = filter_var($email, FILTER_SANITIZE_EMAIL);

				// Validate e-mail
					if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$InvalidDataMessage="Invalid email";
						$dataValidation=false;
					}
					$email=$Enc->enc_mail($email);

				}




				$address_line=(isset($param['address_line']))?mysqli_real_escape_string($GLOBALS['con'],$param['address_line']):'';

				$address_state_id=0;
				if(isset($param['address_state_id']) && $param['address_state_id']!=""){
					$address_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_state_id']);

					if(!$Locations->isValidLocationStateId($address_state_id)){
						$InvalidDataMessage="Invalid address state value";
						$dataValidation=false;
					}
				}

				$address_city_id=0;
				if(isset($param['address_city_id']) && $param['address_city_id']!=""){
					$address_city_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_city_id']);

					if(!$Locations->isValidLocationCityId($address_city_id)){
						$InvalidDataMessage="Invalid address city value";
						$dataValidation=false;
					}
				}

				$address_zipcode_id=0;
				if(isset($param['address_zipcode_id']) && $param['address_zipcode_id']!=""){
					$address_zipcode_id=mysqli_real_escape_string($GLOBALS['con'],$param['address_zipcode_id']);

					if(!$Locations->isValidLocationZipId($address_zipcode_id)){
						$InvalidDataMessage="Invalid address city value";
						$dataValidation=false;
					}
				}

				$company_id=0;
				if(isset($param['company_id']) && $param['company_id']!=""){
					$company_id=mysqli_real_escape_string($GLOBALS['con'],$param['company_id']);

					include_once APPROOT.'/models/masters/Companies.php';
					$Companies=new Companies;

					if(!$Companies->isValidId($company_id)){
						$InvalidDataMessage="Invalid company value";
						$dataValidation=false;
					}

				}


				$date_of_joining_raw=(isset($param['date_of_joining']))?mysqli_real_escape_string($GLOBALS['con'],$param['date_of_joining']):'00/00/0000';

				$date_of_joining=isValidDateFormat($date_of_joining_raw)?date('Y-m-d', strtotime($date_of_joining_raw)):'0000-00-00';

				$route_type_id=0;
				if(isset($param['route_type_id']) && $param['route_type_id']!=""){
					$route_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['route_type_id']);

					include_once APPROOT.'/models/masters/RouteTypes.php';
					$RouteTypes=new RouteTypes;

					if(!$RouteTypes->isValidId($route_type_id)){
						$InvalidDataMessage="Invalid route type value";
						$dataValidation=false;
					}

				}

				$cdl_no=(isset($param['cdl_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_no']):'';





				$cdl_state_id=0;
				if(isset($param['cdl_state_id']) && $param['cdl_state_id']!=""){
					$cdl_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['cdl_state_id']);

					if(!$Locations->isValidLocationStateId($cdl_state_id)){
						$InvalidDataMessage="Invalid CDL state value";
						$dataValidation=false;
					}
				}




				$cdl_issue_date_raw=(isset($param['cdl_issue_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_issue_date']):'00/00/0000';

				$cdl_issue_date=isValidDateFormat($cdl_issue_date_raw)?date('Y-m-d', strtotime($cdl_issue_date_raw)):'0000-00-00';



				$cdl_expiry_date_raw=(isset($param['cdl_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['cdl_expiry_date']):'00/00/0000';

				$cdl_expiry_date=isValidDateFormat($cdl_expiry_date_raw)?date('Y-m-d', strtotime($cdl_expiry_date_raw)):'0000-00-00';



				$ssn_number=(isset($param['ssn_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['ssn_number']):'';



				$residency_id=0;
				if(isset($param['residency_id']) && $param['residency_id']!=""){
					$residency_id=mysqli_real_escape_string($GLOBALS['con'],$param['residency_id']);

					include_once APPROOT.'/models/masters/EmployeesResidency.php';
					$EmployeesResidency=new EmployeesResidency;

					if(!$EmployeesResidency->isValidId($residency_id)){
						$InvalidDataMessage="Invalid maker value";
						$dataValidation=false;
					}

				}


				$residency_expiry_date_raw=(isset($param['residency_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['residency_expiry_date']):'00/00/0000';

				$residency_expiry_date=isValidDateFormat($residency_expiry_date_raw)?date('Y-m-d', strtotime($residency_expiry_date_raw)):'0000-00-00';



				$medical_issue_date_raw=(isset($param['medical_issue_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['medical_issue_date']):'00/00/0000';

				$medical_issue_date=isValidDateFormat($medical_issue_date_raw)?date('Y-m-d', strtotime($medical_issue_date_raw)):'0000-00-00';



				$medical_expiry_date_raw=(isset($param['medical_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['medical_expiry_date']):'00/00/0000';

				$medical_expiry_date=isValidDateFormat($medical_expiry_date_raw)?date('Y-m-d', strtotime($medical_expiry_date_raw)):'0000-00-00';

				$gfr=(isset($param['gfr']))?mysqli_real_escape_string($GLOBALS['con'],$param['gfr']):'';


				$epn_enroll_status=(isset($param['epn_enroll_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['epn_enroll_status']):'';
				$epn_enroll_status=($epn_enroll_status=='Yes' || $epn_enroll_status=='No')?$epn_enroll_status:"";





				$last_annual_review_date_raw=(isset($param['last_annual_review_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['last_annual_review_date']):'00/00/0000';

				$last_annual_review_date=isValidDateFormat($last_annual_review_date_raw)?date('Y-m-d', strtotime($last_annual_review_date_raw)):'0000-00-00';



				$next_annual_review_date_raw=(isset($param['next_annual_review_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['next_annual_review_date']):'00/00/0000';

				$next_annual_review_date=isValidDateFormat($next_annual_review_date_raw)?date('Y-m-d', strtotime($next_annual_review_date_raw)):'0000-00-00';





				$assigned_truck_id=0;
				if(isset($param['assigned_truck_id']) && $param['assigned_truck_id']!=""){
					$assigned_truck_id=mysqli_real_escape_string($GLOBALS['con'],$param['assigned_truck_id']);

					include_once APPROOT.'/models/masters/Trucks.php';
					$Trucks=new Trucks;

					if(!$Trucks->isValidId($assigned_truck_id)){
						$InvalidDataMessage="Invalid truck assigned";
						$dataValidation=false;
					}

				}

				$insurance_added_status=(isset($param['insurance_added_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_added_status']):'';
				$insurance_added_status=($insurance_added_status=='Yes' || $insurance_added_status=='No')?$insurance_added_status:"";
				

				$group_id=0;
				if(isset($param['group_id']) && $param['group_id']!=""){
					$group_id=mysqli_real_escape_string($GLOBALS['con'],$param['group_id']);

					include_once APPROOT.'/models/masters/DriverGroups.php';
					$DriverGroups=new DriverGroups;

					if(!$DriverGroups->isValidId($group_id)){
						$InvalidDataMessage="Invalid group value";
						$dataValidation=false;
					}

				}

				//-----data validation ends



				if($dataValidation){
			//--check if the code exists
					

					$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_code`='$code',`driver_group_id_fk`='$group_id',`driver_name_prefix_id_fk`='$prefix_id',`driver_name_first`='$name_first',`driver_name_middle`='$name_middle',`driver_name_last`='$name_last',`driver_dob`='$dob',`driver_mobile_no`='$mobile_number',`driver_mobile_country_code_id_fk`='$mobile_country_code_id',`driver_email`='$email',`driver_address_line`='$address_line',`driver_address_state_id_fk`='$address_state_id',`driver_address_city_id_fk`='$address_city_id',`driver_address_zipcode_id_fk`='$address_zipcode_id',`driver_company_id_fk`='$company_id',`driver_date_of_joining`='$date_of_joining',`driver_route_type_id_fk`='$route_type_id',`driver_cdl_no`='$cdl_no',`driver_cdl_state_id_fk`='$cdl_state_id',`driver_cdl_issue_date`='$cdl_issue_date',`driver_cdl_expiry_date`='$cdl_expiry_date',`driver_ssn_number`='$ssn_number',`driver_residency_type_id_fk`='$residency_id',`driver_residency_expiry_date`='$residency_expiry_date',`driver_medical_issue_date`='$medical_issue_date',`driver_medical_expiry_date`='$medical_expiry_date',`driver_gfr`='$gfr',`driver_epn_enroll_status`='$epn_enroll_status',`driver_last_annual_review_date`='$last_annual_review_date',`driver_next_annual_review_date`='$next_annual_review_date',`driver_truck_assigned_id_fk`='$assigned_truck_id',`driver_insurance_added_status`='$insurance_added_status',`driver_status_id_fk`='$status_id',`driver_updated_on`='$time',`driver_updated_by`='$USERID' WHERE `driver_id`='$update_id'");
					if($update){
						$status=true;
						$message="Updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}

				}else{
					$message=$InvalidDataMessage;
				}




			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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



	function driver_password_reset($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0010', USER_PRIV)){


			if($param['update_eid']){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();
				$update_id=$Enc->safeurlde($param['update_eid']);
			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";

				if(isset($param['password']) && $param['password']!=""){
					$password=mysqli_real_escape_string($GLOBALS['con'],$param['password']);
				}else{
					$InvalidDataMessage="Please provide password";
					$dataValidation=false;
				}



				if($dataValidation){

					$password=password_hash($password, PASSWORD_DEFAULT);
					$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_password`='$password' WHERE `driver_id`='$update_id'");
					if($update){
						$status=true;
						$message="Password updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message=$InvalidDataMessage;
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function toggle_settlement_status($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0166', USER_PRIV)){

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$USERID=USER_ID;
			$time=time();

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['driver_eid'])){
				$driver_id=$Enc->safeurlde($param['driver_eid']);
			}


			if(isset($param['settlement_status'])){

				if($param['settlement_status']=='OFF' ||$param['settlement_status']=='ON'){
					$settlement_status=senetize_input($param['settlement_status']);
				}else{
					$InvalidDataMessage="Please provide valid settlement status";
					$dataValidation=false;
					goto ValidationChecker;
				}

				
			}else{
				$InvalidDataMessage="Please provide settlement status";
				$dataValidation=false;
				goto ValidationChecker;
			}

			ValidationChecker:

			if($dataValidation){

				$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_settlement_status`='$settlement_status' WHERE `driver_id`='$driver_id'");
				if($update){
					$status=true;
					$message="Updated Successfuly";	
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