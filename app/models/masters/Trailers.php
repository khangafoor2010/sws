<?php
/**
 *
 */
class Trailers
{


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `trailer_id` from `trailers` WHERE `trailer_id`='$id' AND `trailer_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function trailers_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0023', USER_PRIV)){


			if(isset($param['code']) && $param['code']!=""){
				$code=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['code']));
				$USERID=USER_ID;
				$time=time();


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";


				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/VehiclesStatus.php';
					$VehiclesStatus=new VehiclesStatus;

					if(!$VehiclesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid condition status value";
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

				$maker_id=0;
				if(isset($param['maker_id']) && $param['maker_id']!=""){
					$maker_id=mysqli_real_escape_string($GLOBALS['con'],$param['maker_id']);

					include_once APPROOT.'/models/masters/VehiclesMakers.php';
					$VehiclesMakers=new VehiclesMakers;

					if(!$VehiclesMakers->isValidId($maker_id)){
						$InvalidDataMessage="Invalid maker value";
						$dataValidation=false;
					}

				}

				$model_id=0;
				if(isset($param['model_id']) && $param['model_id']!=""){
					$model_id=mysqli_real_escape_string($GLOBALS['con'],$param['model_id']);

					include_once APPROOT.'/models/masters/VehiclesModels.php';
					$VehiclesModels=new VehiclesModels;

					if(!$VehiclesModels->isValidId($model_id)){
						$InvalidDataMessage="Invalid model value";
						$dataValidation=false;
					}

				}
				$body_type=(isset($param['body_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['body_type']):'';

				$reefer_company_id=0;
				if(isset($param['reefer_company_id']) && $param['reefer_company_id']!=""){
					$reefer_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['reefer_company_id']);

					include_once APPROOT.'/models/masters/ReeferCompanies.php';
					$ReeferCompanies=new ReeferCompanies;

					if(!$ReeferCompanies->isValidId($reefer_company_id)){
						$InvalidDataMessage="$reefer_id.Invalid reefer company value";
						$dataValidation=false;
					}

				}
				
				
				$make_year="";
				if(isset($param['make_year']) && $param['make_year']!=""){
					$make_year=mysqli_real_escape_string($GLOBALS['con'],$param['make_year']);

					if (!preg_match("/^[0-9]{4}$/",$make_year)){
						$InvalidDataMessage="Invalid make year";
						$dataValidation=false;
					}

				}


				$vin_number=(isset($param['vin_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['vin_number']):'0';
				


				$licence_tag_no=(isset($param['licence_tag_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['licence_tag_no']):'0';

				$licence_state_id=0;
				if(isset($param['licence_state_id']) && $param['licence_state_id']!=""){
					$licence_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['licence_state_id']);

					include_once APPROOT.'/models/masters/Locations.php';
					$Locations=new Locations;

					if(!$Locations->isValidLocationStateId($licence_state_id)){
						$InvalidDataMessage="Invalid state value";
						$dataValidation=false;
					}
				}


				$licence_tag_expiery_raw=(isset($param['licence_tag_expiery']))?mysqli_real_escape_string($GLOBALS['con'],$param['licence_tag_expiery']):'00/00/0000';

				$licence_tag_expiery=isValidDateFormat($licence_tag_expiery_raw)?date('Y-m-d', strtotime($licence_tag_expiery_raw)):'0000-00-00';



				$ownership_type_id=0;
				if(isset($param['ownership_type_id']) && $param['ownership_type_id']!=""){
					$ownership_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['ownership_type_id']);

					include_once APPROOT.'/models/masters/VehiclesOwnershipTypes.php';
					$VehiclesOwnershipTypes=new VehiclesOwnershipTypes;

					if(!$VehiclesOwnershipTypes->isValidId($ownership_type_id)){
						$InvalidDataMessage="Invalid Ownership type value";
						$dataValidation=false;
					}
				}


				$lease_company_id=0;
				if(isset($param['lease_company_id']) && $param['lease_company_id']!=""){
					$lease_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['lease_company_id']);

					include_once APPROOT.'/models/masters/LeaseCompanies.php';
					$LeaseCompanies=new LeaseCompanies;

					if(!$LeaseCompanies->isValidId($lease_company_id)){
						$InvalidDataMessage="Invalid lease company value";
						$dataValidation=false;
					}
				}


				$lease_ref_no=(isset($param['lease_ref_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['lease_ref_no']):'';



				$lease_expiry_date_raw=(isset($param['lease_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['lease_expiry_date']):'00/00/0000';

				$lease_expiry_date=isValidDateFormat($lease_expiry_date_raw)?date('Y-m-d', strtotime($lease_expiry_date_raw)):'0000-00-00';


				$insurance_status=(isset($param['insurance_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_status']):'';
				$insurance_status=($insurance_status=='Active' || $insurance_status=='Inactive')?$insurance_status:"";



				$insurance_company_id=0;
				if(isset($param['insurance_company_id']) && $param['insurance_company_id']!=""){
					$insurance_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['insurance_company_id']);

					include_once APPROOT.'/models/masters/InsuranceCompanies.php';
					$InsuranceCompanies=new InsuranceCompanies;

					if(!$InsuranceCompanies->isValidId($insurance_company_id)){
						$InvalidDataMessage="Invalid insurance company value";
						$dataValidation=false;
					}
				}

				$insurance_start_date_raw=(isset($param['insurance_start_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_start_date']):'00/00/0000';

				$insurance_start_date=isValidDateFormat($insurance_start_date_raw)?date('Y-m-d', strtotime($insurance_start_date_raw)):'0000-00-00';


				$insurance_expiry_date_raw=(isset($param['insurance_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_expiry_date']):'00/00/0000';

				$insurance_expiry_date=isValidDateFormat($insurance_expiry_date_raw)?date('Y-m-d', strtotime($insurance_expiry_date_raw)):'0000-00-00';

				$liability_status=(isset($param['liability_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['liability_status']):'';
				$liability_status=($liability_status=='Yes' || $liability_status=='No')?$liability_status:"";

				$pd_value=(isset($param['pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['pd_value']):'';

				$new_pd_value=(isset($param['new_pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['new_pd_value']):'';

				$loss_pay_info=(isset($param['loss_pay_info']))?mysqli_real_escape_string($GLOBALS['con'],$param['loss_pay_info']):'';

				
				$device_company_id=0;
				if(isset($param['device_company_id']) && $param['device_company_id']!=""){
					$device_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['device_company_id']);
					include_once APPROOT.'/models/masters/DeviceCompanies.php';
					$DeviceCompanies=new DeviceCompanies;

					if(!$DeviceCompanies->isValidId($device_company_id)){
						$InvalidDataMessage="Invalid device company value";
						$dataValidation=false;
					}
				}

				$device_serial_no=(isset($param['device_serial_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['device_serial_no']):'';

				$engine_hours_update_type=(isset($param['engine_hours_update_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['engine_hours_update_type']):'';
				$engine_hours_update_type=($engine_hours_update_type=='Auto' || $engine_hours_update_type=='Manual')?$engine_hours_update_type:"Manual";



				//-----data validation ends



				if($dataValidation){
			//--check if the code exists
					$codeRows=mysqli_query($GLOBALS['con'],"SELECT `trailer_id` FROM `trailers` WHERE `trailer_status`='ACT' AND `trailer_code`='$code'");
					if(mysqli_num_rows($codeRows)<1){
				 	///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `trailer_id` FROM `trailers` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['trailer_id'])+1:0;
					///-----//Generate New Unique Id

						$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `trailers`(`trailer_id`,`trailer_code`, `trailer_status_id_fk`, `trailer_company_id_fk`, `trailer_make_id_fk`, `trailer_model_id_fk`,`trailer_body_type`,`trailer_reefer_company_id_fk`, `trailer_make_year`, `trailer_vin_number`, `trailer_licence_tag_no`, `trailer_licence_state_id`, `trailer_licence_tag_expiry_date`, `trailer_ownership_type_id_fk`, `trailer_lease_company_id_fk`, `trailer_lease_ref_no`, `trailer_lease_expiry_date`,`trailer_insurance_status`, `trailer_insurance_company_id_fk`, `trailer_insurance_start_date`, `trailer_insurance_expiry_date`, `trailer_pd_value`, `trailer_new_pd_value`, `trailer_loss_pay_info`, `trailer_device_company_id_fk`, `trailer_device_serial_no`,`trailer_engine_hours_update_type`, `trailer_status`, `trailer_added_on`, `trailer_added_by`) VALUES ('$next_id','$code','$status_id','$company_id','$maker_id','$model_id','$body_type','$reefer_company_id','$make_year','$vin_number','$licence_tag_no','$licence_state_id','$licence_tag_expiery','$ownership_type_id','$lease_company_id','$lease_ref_no','$lease_expiry_date','$insurance_status','$insurance_company_id','$insurance_start_date','$insurance_expiry_date','$pd_value','$new_pd_value','$loss_pay_info','$device_company_id','$device_serial_no','$engine_hours_update_type','ACT','$time','$USERID')");
						if($insert){
							$status=true;
							$message="Added Successfuly";	
						}else{
							$message=SOMETHING_WENT_WROG;
						}
					}else{
						$message="Trailer ID already exists";
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

	function trailers_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `trailer_id`,`trailer_code`,`company_id`,`company_name`,`trailer_make_year`, `trailer_vin_number`,`trailer_licence_tag_no`,`model_id`,`model_name`,`maker_id`,`trailer_body_type`,`reefer_company_id`,`reefer_company_name`,`maker_name`,`status_id`,`status_name`,`location_id` AS `state_id`,`location_name` AS `state_name`,`trailer_licence_tag_expiry_date`,`ownership_type_id`,`ownership_type_name`,`lease_company_id`,`trailer_lease_ref_no`,`lease_company_name`,`trailer_lease_expiry_date`,`device_company_id`,`device_company_name`, `trailer_device_serial_no`,`ownership_type_deleted_by`,`trailer_insurance_status`,`insurance_company_id`,`insurance_company_name`,`trailer_insurance_start_date`,`trailer_insurance_expiry_date`,  `trailer_pd_value`, `trailer_new_pd_value`, `trailer_loss_pay_info`,`trailer_engine_hours_update_type` FROM `trailers` LEFT JOIN `companies` ON `companies`.`company_id`=`trailers`.`trailer_company_id_fk` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`trailers`.`trailer_make_id_fk` LEFT JOIN `vehicle_models` ON `vehicle_models`.`model_id`=`trailers`.`trailer_model_id_fk` LEFT JOIN `locations` ON `locations`.`location_id`=`trailers`.`trailer_licence_state_id`LEFT JOIN `vehicle_status` ON `vehicle_status`.`status_id`=`trailers`.`trailer_status_id_fk` LEFT JOIN `lease_companies` ON `lease_companies`.`lease_company_id`=`trailers`.`trailer_lease_company_id_fk`LEFT JOIN `device_companies` ON `device_companies`.`device_company_id`=`trailers`.`trailer_device_company_id_fk` LEFT JOIN `reefer_companies` ON `reefer_companies`.`reefer_company_id`=`trailers`.`trailer_reefer_company_id_fk` LEFT JOIN `vehicle_ownership_types` ON `vehicle_ownership_types`.`ownership_type_id`=`trailers`.`trailer_ownership_type_id_fk` LEFT JOIN `insurance_companies` ON `insurance_companies`.`insurance_company_id`=`trailers`.`trailer_insurance_company_id_fk`  WHERE `trailer_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND trailer_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND trailer_id='$details_for_eid'";
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
				$row['eid']=$Enc->safeurlen($rows['trailer_id']);
				$row['status_id']=$rows['status_id'];
				$row['status']=$rows['status_name'];
				$row['code']=$rows['trailer_code'];
				$row['company_id']=$rows['company_id'];
				$row['company']=$rows['company_name'];
				$row['model_id']=$rows['model_id'];
				$row['model']=$rows['model_name'];
				$row['make']=$rows['maker_name'];
				$row['maker_id']=$rows['maker_id'];
				$row['body_type']=$rows['trailer_body_type'];
				$row['reefer_company_id']=$rows['reefer_company_id'];
				$row['reefer_company']=$rows['reefer_company_name'];
				$row['make_year']=$rows['trailer_make_year'];
				$row['vin']=$rows['trailer_vin_number'];
				$row['licence_tag_no']=$rows['trailer_licence_tag_no'];
				$row['licence_state_id']=$rows['state_id'];
				$row['licence_state']=$rows['state_name']!=null?$rows['state_name']:"";
				$row['licence_tag_expiry_date']=dateFromDbToFormat($rows['trailer_licence_tag_expiry_date']);

				$row['ownership_type_id']=$rows['ownership_type_id']!=null?$rows['ownership_type_id']:"";
				$row['ownership_type']=$rows['ownership_type_name']!=null?$rows['ownership_type_name']:"";
				if($rows['ownership_type_name']=='LEASE' || $rows['ownership_type_name']=='lease' || $rows['ownership_type_name']=='Lease'){
					$row['lease_company_id']=$rows['lease_company_id'];
					$row['lease_company']=$rows['lease_company_name'];
					$row['lease_ref_no']=$rows['trailer_lease_ref_no'];
					$row['lease_expiry_date']=dateFromDbToFormat($rows['trailer_lease_expiry_date']); 

				}else{
					$row['lease_company_id']="";
					$row['lease_company']="";
					$row['lease_ref_no']="";
					$row['lease_expiry_date']="";		
				}

				$row['insurance_status']=$rows['trailer_insurance_status'];
				if($rows['trailer_insurance_status']=='Active'){
					$row['insurance_company_id']=$rows['insurance_company_id'];
					$row['insurance_company_name']=$rows['insurance_company_name'];
					$row['insurance_start_date']=dateFromDbToFormat($rows['trailer_insurance_start_date']); 
					$row['insurance_expiry_date']=dateFromDbToFormat($rows['trailer_insurance_expiry_date']);
				}else{
					$row['insurance_company_id']="";
					$row['insurance_company_name']="";
					$row['insurance_start_date']=""; 
					$row['insurance_expiry_date']="";				
				}


				$row['pd_value']=$rows['trailer_pd_value'];
				$row['new_pd_value']=$rows['trailer_new_pd_value'];
				$row['loss_pay_info']=$rows['trailer_loss_pay_info'];
				
				$row['device_company_id']=$rows['device_company_id']!=null?$rows['device_company_id']:"";
				$row['device_company_name']=$rows['device_company_name']!=null?$rows['device_company_name']:"";
				$row['device_serial_no']=$rows['trailer_device_serial_no'];
				$row['engine_hours_update_type']=$rows['trailer_engine_hours_update_type'];

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

	function trailers_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=1000;
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

		$q="SELECT `trailer_id`,`trailer_code`,`company_name`,`trailer_make_year`, `trailer_vin_number`,`trailer_licence_tag_no`,`model_name`,`maker_name`,`status_name`,`location_name` AS `state_name`,`trailer_licence_tag_expiry_date`,`ownership_type_name`,`trailer_lease_ref_no`,`lease_company_name`,`trailer_lease_expiry_date`,`device_company_name`, `trailer_device_serial_no`,`ownership_type_deleted_by`,`trailer_insurance_status`,`insurance_company_name`,`trailer_insurance_start_date`,`trailer_insurance_expiry_date`,  `trailer_pd_value`, `trailer_new_pd_value`, `trailer_loss_pay_info` FROM `trailers` LEFT JOIN `companies` ON `companies`.`company_id`=`trailers`.`trailer_company_id_fk` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`trailers`.`trailer_make_id_fk` LEFT JOIN `vehicle_models` ON `vehicle_models`.`model_id`=`trailers`.`trailer_model_id_fk` LEFT JOIN `locations` ON `locations`.`location_id`=`trailers`.`trailer_licence_state_id`LEFT JOIN `vehicle_status` ON `vehicle_status`.`status_id`=`trailers`.`trailer_status_id_fk` LEFT JOIN `lease_companies` ON `lease_companies`.`lease_company_id`=`trailers`.`trailer_lease_company_id_fk`LEFT JOIN `device_companies` ON `device_companies`.`device_company_id`=`trailers`.`trailer_device_company_id_fk` LEFT JOIN `vehicle_ownership_types` ON `vehicle_ownership_types`.`ownership_type_id`=`trailers`.`trailer_ownership_type_id_fk` LEFT JOIN `insurance_companies` ON `insurance_companies`.`insurance_company_id`=`trailers`.`trailer_insurance_company_id_fk`  WHERE `trailer_status`='ACT'";



//----Apply Filters starts


		if(isset($param['status_id']) && $param['status_id']!=""){
			$trailer_status_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q .=" AND trailer_status_id_fk='$trailer_status_id_fk'";
		}

		if(isset($param['company_id']) && $param['company_id']!=""){
			$company_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['company_id']);
			$q .=" AND trailer_company_id_fk='$company_id_fk'";
		}
		if(isset($param['lease_company_id']) && $param['lease_company_id']!=""){
			$lease_company_id_fk=mysqli_real_escape_string($GLOBALS['con'],$param['lease_company_id']);
			$q .=" AND trailer_lease_company_id_fk='$lease_company_id_fk'";
		}


		if(isset($param['ownership_type']) && $param['ownership_type']!=""){
			$ownership_type=mysqli_real_escape_string($GLOBALS['con'],$param['ownership_type']);
			$q .=" AND trailer_ownership_type_id_fk='$ownership_type'";
		}

//-----Apply fitlers ends




		$order_by_type='ASC';
		if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){
			$order_by_type='DESC';
		}
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'code':
				$q .=" ORDER BY `trailer_code`";
				break;
				case 'condition':
				$q .=" ORDER BY `status_name`";
				break;		
				case 'company':
				$q .=" ORDER BY `company_name`";
				break;		
				default:
				$q .=" ORDER BY `trailer_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `trailer_id`";	
		}




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['trailer_id'];
			$row['eid']=$Enc->safeurlen($rows['trailer_id']);
			$row['status']=$rows['status_name'];
			$row['code']=$rows['trailer_code'];
			$row['company']=$rows['company_name'];
			$row['model']=$rows['model_name'];
			$row['make']=$rows['maker_name'];
			$row['make_year']=$rows['trailer_make_year'];
			$row['vin']=$rows['trailer_vin_number'];
			$row['licence_tag_no']=$rows['trailer_licence_tag_no'];
			$row['licence_state']=$rows['state_name']!=null?$rows['state_name']:"";
			$row['licence_tag_expiry_date']=dateFromDbToFormat($rows['trailer_licence_tag_expiry_date']);
			
			$row['ownership_type']=$rows['ownership_type_name']!=null?$rows['ownership_type_name']:"";
			if($rows['ownership_type_name']=='LEASE' || $rows['ownership_type_name']=='lease' || $rows['ownership_type_name']=='Lease'){
				$row['leasing_company']=$rows['lease_company_name'];
				$row['leasing_ref_no']=$rows['trailer_lease_ref_no'];
				$row['leasing_expiry']=dateFromDbToFormat($rows['trailer_lease_expiry_date']); 

			}else{
				$row['leasing_company']="";
				$row['leasing_ref_no']="";
				$row['leasing_expiery']="";		
			}

			$row['insurance_status']=$rows['trailer_insurance_status'];
			if($rows['trailer_insurance_status']=='Active'){
				$row['insurance_company_name']=$rows['insurance_company_name'];
				$row['insurance_start_date']=dateFromDbToFormat($rows['trailer_insurance_start_date']); 
				$row['insurance_expiry_date']=dateFromDbToFormat($rows['trailer_insurance_expiry_date']);
			}else{
				$row['insurance_company_name']="";
				$row['insurance_start_date']=""; 
				$row['insurance_expiry_date']="";				
			}

			$row['pd_value']=$rows['trailer_pd_value'];
			$row['new_pd_value']=$rows['trailer_new_pd_value'];
			$row['loss_pay_info']=$rows['trailer_loss_pay_info'];





			$row['device_company_name']=$rows['device_company_name']!=null?$rows['device_company_name']:"";
			$row['device_serial_no']=$rows['trailer_device_serial_no'];

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


	function trailers_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0025', USER_PRIV)){


			if(isset($param['code']) && isset($param['update_eid'])){

				$code=mysqli_real_escape_string($GLOBALS['con'],$param['code']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();




			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
				$dataValidation=true;
				$InvalidDataMessage="";


				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/VehiclesStatus.php';
					$VehiclesStatus=new VehiclesStatus;

					if(!$VehiclesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid condition status value";
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

				$maker_id=0;
				if(isset($param['maker_id']) && $param['maker_id']!=""){
					$maker_id=mysqli_real_escape_string($GLOBALS['con'],$param['maker_id']);

					include_once APPROOT.'/models/masters/VehiclesMakers.php';
					$VehiclesMakers=new VehiclesMakers;

					if(!$VehiclesMakers->isValidId($maker_id)){
						$InvalidDataMessage="Invalid maker value";
						$dataValidation=false;
					}

				}

				$model_id=0;
				if(isset($param['model_id']) && $param['model_id']!=""){
					$model_id=mysqli_real_escape_string($GLOBALS['con'],$param['model_id']);

					include_once APPROOT.'/models/masters/VehiclesModels.php';
					$VehiclesModels=new VehiclesModels;

					if(!$VehiclesModels->isValidId($model_id)){
						$InvalidDataMessage="Invalid model value";
						$dataValidation=false;
					}

				}
				$body_type=(isset($param['body_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['body_type']):'';

				$reefer_company_id=0;
				if(isset($param['reefer_company_id']) && $param['reefer_company_id']!=""){
					$reefer_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['reefer_company_id']);

					include_once APPROOT.'/models/masters/ReeferCompanies.php';
					$ReeferCompanies=new ReeferCompanies;

					if(!$ReeferCompanies->isValidId($reefer_company_id)){
						$InvalidDataMessage="$reefer_id.Invalid reefer company value";
						$dataValidation=false;
					}

				}
				
				
				$make_year="";
				if(isset($param['make_year']) && $param['make_year']!=""){
					$make_year=mysqli_real_escape_string($GLOBALS['con'],$param['make_year']);

					if (!preg_match("/^[0-9]{4}$/",$make_year)){
						$InvalidDataMessage="Invalid make year";
						$dataValidation=false;
					}

				}


				$vin_number=(isset($param['vin_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['vin_number']):'0';
				


				$licence_tag_no=(isset($param['licence_tag_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['licence_tag_no']):'0';

				$licence_state_id=0;
				if(isset($param['licence_state_id']) && $param['licence_state_id']!=""){
					$licence_state_id=mysqli_real_escape_string($GLOBALS['con'],$param['licence_state_id']);

					include_once APPROOT.'/models/masters/Locations.php';
					$Locations=new Locations;

					if(!$Locations->isValidLocationStateId($licence_state_id)){
						$InvalidDataMessage="Invalid state value";
						$dataValidation=false;
					}
				}


				$licence_tag_expiry_raw=(isset($param['licence_tag_expiry']))?mysqli_real_escape_string($GLOBALS['con'],$param['licence_tag_expiry']):'00/00/0000';

				$licence_tag_expiry=isValidDateFormat($licence_tag_expiry_raw)?date('Y-m-d', strtotime($licence_tag_expiry_raw)):'0000-00-00';



				$ownership_type_id=0;
				if(isset($param['ownership_type_id']) && $param['ownership_type_id']!=""){
					$ownership_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['ownership_type_id']);

					include_once APPROOT.'/models/masters/VehiclesOwnershipTypes.php';
					$VehiclesOwnershipTypes=new VehiclesOwnershipTypes;

					if(!$VehiclesOwnershipTypes->isValidId($ownership_type_id)){
						$InvalidDataMessage="Invalid Ownership type value";
						$dataValidation=false;
					}
				}


				$lease_company_id=0;
				if(isset($param['lease_company_id']) && $param['lease_company_id']!=""){
					$lease_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['lease_company_id']);

					include_once APPROOT.'/models/masters/LeaseCompanies.php';
					$LeaseCompanies=new LeaseCompanies;

					if(!$LeaseCompanies->isValidId($lease_company_id)){
						$InvalidDataMessage="Invalid lease company value";
						$dataValidation=false;
					}
				}


				$lease_ref_no=(isset($param['lease_ref_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['lease_ref_no']):'';



				$lease_expiry_date_raw=(isset($param['lease_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['lease_expiry_date']):'00/00/0000';

				$lease_expiry_date=isValidDateFormat($lease_expiry_date_raw)?date('Y-m-d', strtotime($lease_expiry_date_raw)):'0000-00-00';


				$insurance_status=(isset($param['insurance_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_status']):'';
				$insurance_status=($insurance_status=='Active' || $insurance_status=='Inactive')?$insurance_status:"";



				$insurance_company_id=0;
				if(isset($param['insurance_company_id']) && $param['insurance_company_id']!=""){
					$insurance_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['insurance_company_id']);

					include_once APPROOT.'/models/masters/InsuranceCompanies.php';
					$InsuranceCompanies=new InsuranceCompanies;

					if(!$InsuranceCompanies->isValidId($insurance_company_id)){
						$InvalidDataMessage="Invalid insurance company value";
						$dataValidation=false;
					}
				}

				$insurance_start_date_raw=(isset($param['insurance_start_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_start_date']):'00/00/0000';

				$insurance_start_date=isValidDateFormat($insurance_start_date_raw)?date('Y-m-d', strtotime($insurance_start_date_raw)):'0000-00-00';


				$insurance_expiry_date_raw=(isset($param['insurance_expiry_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['insurance_expiry_date']):'00/00/0000';

				$insurance_expiry_date=isValidDateFormat($insurance_expiry_date_raw)?date('Y-m-d', strtotime($insurance_expiry_date_raw)):'0000-00-00';

				$liability_status=(isset($param['liability_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['liability_status']):'';
				$liability_status=($liability_status=='Yes' || $liability_status=='No')?$liability_status:"";

				$pd_value=(isset($param['pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['pd_value']):'';

				$new_pd_value=(isset($param['new_pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['new_pd_value']):'';

				$loss_pay_info=(isset($param['loss_pay_info']))?mysqli_real_escape_string($GLOBALS['con'],$param['loss_pay_info']):'';

				
				$device_company_id=0;
				if(isset($param['device_company_id']) && $param['device_company_id']!=""){
					$device_company_id=mysqli_real_escape_string($GLOBALS['con'],$param['device_company_id']);
					include_once APPROOT.'/models/masters/DeviceCompanies.php';
					$DeviceCompanies=new DeviceCompanies;

					if(!$DeviceCompanies->isValidId($device_company_id)){
						$InvalidDataMessage="Invalid device company value";
						$dataValidation=false;
					}
				}

				$device_serial_no=(isset($param['device_serial_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['device_serial_no']):'';

				$engine_hours_update_type=(isset($param['engine_hours_update_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['engine_hours_update_type']):'';
				$engine_hours_update_type=($engine_hours_update_type=='Auto' || $engine_hours_update_type=='Manual')?$engine_hours_update_type:"Manual";

				if($dataValidation){
			//--check if the code exists
					

$update=mysqli_query($GLOBALS['con'],"UPDATE `trailers` SET `trailer_code`='$code', `trailer_status_id_fk`='$status_id', `trailer_company_id_fk`='$company_id', `trailer_make_id_fk`='$maker_id', `trailer_model_id_fk`='$model_id',`trailer_body_type`='$body_type',`trailer_reefer_company_id_fk`='$reefer_company_id', `trailer_make_year`='$make_year', `trailer_vin_number`='$vin_number', `trailer_licence_tag_no`='$licence_tag_no', `trailer_licence_state_id`='$licence_state_id', `trailer_licence_tag_expiry_date`='$licence_tag_expiry', `trailer_ownership_type_id_fk`='$ownership_type_id', `trailer_lease_company_id_fk`='$lease_company_id', `trailer_lease_ref_no`='$lease_ref_no', `trailer_lease_expiry_date`='$lease_expiry_date',`trailer_insurance_status`='$insurance_status', `trailer_insurance_company_id_fk`='$insurance_company_id', `trailer_insurance_start_date`='$insurance_start_date', `trailer_insurance_expiry_date`='$insurance_expiry_date', `trailer_pd_value`='$pd_value', `trailer_new_pd_value`='$new_pd_value', `trailer_loss_pay_info`='$loss_pay_info', `trailer_device_company_id_fk`='$device_company_id', `trailer_device_serial_no`='$device_serial_no',`trailer_engine_hours_update_type`='$engine_hours_update_type', `trailer_updated_on`='$time', `trailer_updated_by`='$USERID' WHERE `trailer_id`='$update_id'");
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

	function trailers_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0026', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `trailer_id` FROM `trailers` WHERE `trailer_id`='$delete_eid' AND NOT `trailer_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `trailers` SET `trailer_status`='DLT',`trailer_deleted_on`='$time',`trailer_deleted_by`='$USERID' WHERE `trailer_id`='$delete_eid'");
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

	}


}
?>