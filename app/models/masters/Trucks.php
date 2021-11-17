<?php
/**
 *
 */
class Trucks
{


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `truck_id` from `trucks` WHERE `truck_id`='$id' AND `truck_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}


	function trucks_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0018', USER_PRIV)){


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
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}
				

				$authority_id=0;
				if(isset($param['$authority_id']) && $param['$authority_id']!=""){
					$authority_id=mysqli_real_escape_string($GLOBALS['con'],$param['$authority_id']);

					include_once APPROOT.'/models/masters/Companies.php';
					$Companies=new Companies;

					if(!$Companies->isValidId($authority_id)){
						$InvalidDataMessage="Invalid authority value";
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

				$color_id=0;
				if(isset($param['color_id']) && $param['color_id']!=""){
					$color_id=mysqli_real_escape_string($GLOBALS['con'],$param['color_id']);

					include_once APPROOT.'/models/masters/VehiclesColors.php';
					$VehiclesColors=new VehiclesColors;

					if(!$VehiclesColors->isValidId($color_id)){
						$InvalidDataMessage="Invalid color value";
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
				
				$group=(isset($param['group']))?mysqli_real_escape_string($GLOBALS['con'],$param['group']):'';

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


				$cargo_status=(isset($param['cargo_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['cargo_status']):'';
				$cargo_status=($cargo_status=='Yes' || $cargo_status=='No')?$cargo_status:"";	

				$pd_value=(isset($param['pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['pd_value']):'';

				$new_pd_value=(isset($param['new_pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['new_pd_value']):'';

				$loss_pay_info=(isset($param['loss_pay_info']))?mysqli_real_escape_string($GLOBALS['con'],$param['loss_pay_info']):'';

				$fhvut_status=(isset($param['fhvut_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['fhvut_status']):'';
				$fhvut_status=($fhvut_status=='Paid' || $fhvut_status=='Unpaid')?$fhvut_status:"";

				$fhvut_paid_date_raw=(isset($param['fhvut_paid_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['fhvut_paid_date']):'00/00/0000';

				$fhvut_paid_date=isValidDateFormat($fhvut_paid_date_raw)?date('Y-m-d', strtotime($fhvut_paid_date_raw)):'0000-00-00';

				$family_engine_number=(isset($param['family_engine_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['family_engine_number']):'';


				$oregon_permit_status=(isset($param['oregon_permit_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['oregon_permit_status']):'';
				$oregon_permit_status=($oregon_permit_status=='Yes' || $oregon_permit_status=='No')?$oregon_permit_status:"";

				$ifta_status=(isset($param['ifta_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['ifta_status']):'';
				$ifta_status=($ifta_status=='Yes' || $ifta_status=='No')?$ifta_status:"";

				$pifta_status=(isset($param['pifta_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['pifta_status']):'';
				$pifta_status=($pifta_status=='Yes' || $pifta_status=='No')?$pifta_status:"";

				$nm_status=(isset($param['nm_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['nm_status']):'';
				$nm_status=($nm_status=='Yes' || $nm_status=='No')?$nm_status:"";



				$pre_pass=(isset($param['pre_pass']))?mysqli_real_escape_string($GLOBALS['con'],$param['pre_pass']):'';

				$pre_pass_remark=(isset($param['pre_pass_remark']))?mysqli_real_escape_string($GLOBALS['con'],$param['pre_pass_remark']):'';

				$hut=(isset($param['hut']))?mysqli_real_escape_string($GLOBALS['con'],$param['hut']):'';
				$hut_remark=(isset($param['hut_remark']))?mysqli_real_escape_string($GLOBALS['con'],$param['hut_remark']):'';

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
				$device_dash_cam_no=(isset($param['device_dash_cam_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['device_dash_cam_no']):'';
				$halo=(isset($param['halo']))?mysqli_real_escape_string($GLOBALS['con'],$param['halo']):'';
				$odometer_update_type=(isset($param['odometer_update_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['odometer_update_type']):'';
				$odometer_update_type=($odometer_update_type=='Auto' || $odometer_update_type=='Manual')?$odometer_update_type:"Manual";



				//-----data validation ends



				if($dataValidation){
			//--check if the code exists
					$codeRows=mysqli_query($GLOBALS['con'],"SELECT `truck_id` FROM `trucks` WHERE `truck_status`='ACT' AND `truck_code`='$code'");
					if(mysqli_num_rows($codeRows)<1){


 					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `truck_id` FROM `trucks` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['truck_id'])+1:1;
					///-----//Generate New Unique Id
						$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `trucks`(`truck_id`,`truck_code`,`truck_group`, `truck_status_id_fk`, `truck_authority_id_fk`, `truck_company_id_fk`, `truck_make_id_fk`, `truck_model_id_fk`,`truck_color_id_fk`, `truck_make_year`, `truck_vin_number`, `truck_licence_tag_no`, `truck_licence_state_id`, `truck_licence_tag_expiry_date`, `truck_ownership_type_id_fk`, `truck_lease_company_id_fk`, `truck_lease_ref_no`, `truck_lease_expiry_date`,`truck_insurance_status`, `truck_insurance_company_id_fk`, `truck_insurance_start_date`, `truck_insurance_expiry_date`, `truck_liability_status`, `truck_cargo_status`, `truck_pd_value`, `truck_new_pd_value`, `truck_loss_pay_info`, `truck_fhvut_status`, `truck_fhvut_paid_date`, `truck_oregon_permit_status`, `truck_family_engine_number`, `truck_ifta_status`, `truck_pifta_status`, `truck_nm_status`, `truck_pre_pass`, `truck_pre_pass_remark`, `truck_hut`, `truck_hut_remark`, `truck_device_company_id_fk`, `truck_device_serial_no`, `truck_device_dash_cam_no`,`truck_halo`,`truck_odometer_update_type`, `truck_status`, `truck_added_on`, `truck_added_by`) VALUES ('$next_id','$code','$group','$status_id','$authority_id','$company_id','$maker_id','$model_id','$color_id','$make_year','$vin_number','$licence_tag_no','$licence_state_id','$licence_tag_expiery','$ownership_type_id','$lease_company_id','$lease_ref_no','$lease_expiry_date','$insurance_status','$insurance_company_id','$insurance_start_date','$insurance_expiry_date','$liability_status','$cargo_status','$pd_value','$new_pd_value','$loss_pay_info','$fhvut_status','$fhvut_paid_date','$oregon_permit_status','$family_engine_number','$ifta_status','$pifta_status','$nm_status','$pre_pass','$pre_pass_remark','$hut','$hut_remark','$device_company_id','$device_serial_no','$device_dash_cam_no','$halo','$odometer_update_type','ACT','$time','$USERID')");
						if($insert){
							$status=true;
							$message="Added Successfuly";	
						}else{
							$message=SOMETHING_WENT_WROG;
						}
					}else{
						$message="Truck ID already exists";
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

	function trucks_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$query="SELECT `truck_id`, `truck_code`,`truck_group`,`company_id`,`company_name`,`truck_make_year`,`color_id`,`color_name`, `truck_vin_number`,`truck_licence_tag_no`,`model_id`,`model_name`,`maker_id`,`maker_name`,`status_id`,`status_name`,`location_id` AS `state_id`,`location_name` AS `state_name`,`truck_licence_tag_expiry_date`,`ownership_type_id`,`ownership_type_name`,`truck_lease_ref_no`,`lease_company_id`,`lease_company_name`,`truck_lease_expiry_date`,`device_company_id`,`device_company_name`, `truck_device_serial_no`, `truck_device_dash_cam_no`,`truck_halo`,`truck_odometer_update_type`,`ownership_type_deleted_by`,`truck_insurance_status`,`insurance_company_id`,`insurance_company_name`,`truck_insurance_start_date`,`truck_insurance_expiry_date`, `truck_liability_status`, `truck_cargo_status`, `truck_pd_value`, `truck_new_pd_value`, `truck_loss_pay_info`, `truck_fhvut_status`, `truck_fhvut_paid_date`, `truck_oregon_permit_status`, `truck_family_engine_number`, `truck_ifta_status`, `truck_pifta_status`, `truck_nm_status`, `truck_pre_pass`, `truck_pre_pass_remark`, `truck_hut`, `truck_hut_remark` FROM `trucks` LEFT JOIN `companies` ON `companies`.`company_id`=`trucks`.`truck_company_id_fk` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`trucks`.`truck_make_id_fk` LEFT JOIN `vehicle_models` ON `vehicle_models`.`model_id`=`trucks`.`truck_model_id_fk` LEFT JOIN `locations` ON `locations`.`location_id`=`trucks`.`truck_licence_state_id`LEFT JOIN `vehicle_status` ON `vehicle_status`.`status_id`=`trucks`.`truck_status_id_fk` LEFT JOIN `lease_companies` ON `lease_companies`.`lease_company_id`=`trucks`.`truck_lease_company_id_fk`LEFT JOIN `device_companies` ON `device_companies`.`device_company_id`=`trucks`.`truck_device_company_id_fk` LEFT JOIN `vehicle_ownership_types` ON `vehicle_ownership_types`.`ownership_type_id`=`trucks`.`truck_ownership_type_id_fk` LEFT JOIN `insurance_companies` ON `insurance_companies`.`insurance_company_id`=`trucks`.`truck_insurance_company_id_fk` LEFT JOIN `vehicle_colors` ON `vehicle_colors`.`color_id`=`trucks`.`truck_color_id_fk` WHERE `truck_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND truck_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND truck_id='$details_for_eid'";
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
				$row['eid']=$Enc->safeurlen($rows['truck_id']);
				$row['status_id']=$rows['status_id'];
				$row['status']=$rows['status_name'];
				$row['code']=$rows['truck_code'];
				$row['group']=$rows['truck_group'];
				$row['company_id']=$rows['company_id'];
				$row['company']=$rows['company_name'];
				$row['model_id']=$rows['model_id'];
				$row['model']=$rows['model_name'];
				$row['make']=$rows['maker_name'];
				$row['maker_id']=$rows['maker_id'];
				$row['make_year']=$rows['truck_make_year'];
				$row['color_id']=$rows['color_id'];
				$row['color']=$rows['color_name'];
				$row['vin']=$rows['truck_vin_number'];
				$row['licence_tag_no']=$rows['truck_licence_tag_no'];
				$row['licence_state_id']=$rows['state_id'];
				$row['licence_state']=$rows['state_name']!=null?$rows['state_name']:"";
				$row['licence_tag_expiry_date']=dateFromDbToFormat($rows['truck_licence_tag_expiry_date']);

				$row['ownership_type_id']=$rows['ownership_type_id']!=null?$rows['ownership_type_id']:"";
				$row['ownership_type']=$rows['ownership_type_name']!=null?$rows['ownership_type_name']:"";
				if($rows['ownership_type_name']=='LEASE' || $rows['ownership_type_name']=='lease' || $rows['ownership_type_name']=='Lease'){
					$row['lease_company_id']=$rows['lease_company_id'];
					$row['lease_company']=$rows['lease_company_name'];
					$row['lease_ref_no']=$rows['truck_lease_ref_no'];
					$row['lease_expiry_date']=dateFromDbToFormat($rows['truck_lease_expiry_date']); 

				}else{
					$row['lease_company_id']="";
					$row['lease_company']="";
					$row['lease_ref_no']="";
					$row['lease_expiry_date']="";		
				}

				$row['insurance_status']=$rows['truck_insurance_status'];
				if($rows['truck_insurance_status']=='Active'){
					$row['insurance_company_id']=$rows['insurance_company_id'];
					$row['insurance_company_name']=$rows['insurance_company_name'];
					$row['insurance_start_date']=dateFromDbToFormat($rows['truck_insurance_start_date']); 
					$row['insurance_expiry_date']=dateFromDbToFormat($rows['truck_insurance_expiry_date']);
				}else{
					$row['insurance_company_id']="";
					$row['insurance_company_name']="";
					$row['insurance_start_date']=""; 
					$row['insurance_expiry_date']="";				
				}


				$row['liability_status']=$rows['truck_liability_status'];
				$row['cargo_status']=$rows['truck_cargo_status'];
				$row['pd_value']=$rows['truck_pd_value'];
				$row['new_pd_value']=$rows['truck_new_pd_value'];
				$row['loss_pay_info']=$rows['truck_loss_pay_info'];
				$row['fhvut_status']=$rows['truck_fhvut_status'];
				$row['fhvut_paid_date']=dateFromDbToFormat($rows['truck_fhvut_paid_date']);
				$row['oregon_permit_status']=$rows['truck_oregon_permit_status'];
				$row['family_engine_number']=$rows['truck_family_engine_number'];
				$row['ifta_status']=$rows['truck_ifta_status'];
				$row['pifta_status']=$rows['truck_pifta_status'];
				$row['nm_status']=$rows['truck_nm_status'];
				$row['pre_pass']=$rows['truck_pre_pass'];
				$row['pre_pass_remark']=$rows['truck_pre_pass_remark'];
				$row['hut']=$rows['truck_hut'];
				$row['hut_remark']=$rows['truck_hut_remark'];




				$row['device_company_id']=$rows['device_company_id']!=null?$rows['device_company_id']:"";
				$row['device_company_name']=$rows['device_company_name']!=null?$rows['device_company_name']:"";
				$row['device_serial_no']=$rows['truck_device_serial_no'];
				$row['device_dash_cam_no']=$rows['truck_device_dash_cam_no'];
				$row['halo']=$rows['truck_halo'];
				$row['odometer_update_type']=$rows['truck_odometer_update_type'];

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

	function trucks_list($param){
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

		$q="SELECT `truck_id`, `truck_code`,`companies`.`company_name` AS `company_name`,`authority`.`company_name` AS `authority_name`,`truck_make_year`, `truck_vin_number`,`truck_licence_tag_no`,`model_name`,`maker_name`,`location_name` AS `state_name`,`status_name`,`truck_licence_tag_expiry_date`,`ownership_type_name`,`truck_lease_ref_no`,`color_name`,`lease_company_name`,`truck_lease_expiry_date`,`device_company_name`, `truck_device_serial_no`, `truck_device_dash_cam_no`,`truck_halo`,`truck_odometer_update_type`,`ownership_type_deleted_by`,`truck_insurance_status`,`insurance_company_name`,`truck_insurance_start_date`,`truck_insurance_expiry_date`, `truck_liability_status`, `truck_cargo_status`, `truck_pd_value`, `truck_new_pd_value`, `truck_loss_pay_info`, `truck_fhvut_status`, `truck_fhvut_paid_date`, `truck_oregon_permit_status`, `truck_family_engine_number`, `truck_ifta_status`, `truck_pifta_status`, `truck_nm_status`, `truck_pre_pass`, `truck_pre_pass_remark`, `truck_hut`, `truck_hut_remark` FROM `trucks`LEFT JOIN `companies` AS `authority` ON `authority`.`company_id`=`trucks`.`truck_authority_id_fk` LEFT JOIN `companies` AS `companies` ON `companies`.`company_id`=`trucks`.`truck_company_id_fk` LEFT JOIN `vehicle_makers` ON `vehicle_makers`.`maker_id`=`trucks`.`truck_make_id_fk` LEFT JOIN `vehicle_models` ON `vehicle_models`.`model_id`=`trucks`.`truck_model_id_fk` LEFT JOIN `locations` ON `locations`.`location_id`=`trucks`.`truck_licence_state_id`LEFT JOIN `vehicle_status` ON `vehicle_status`.`status_id`=`trucks`.`truck_status_id_fk` LEFT JOIN `lease_companies` ON `lease_companies`.`lease_company_id`=`trucks`.`truck_lease_company_id_fk`LEFT JOIN `device_companies` ON `device_companies`.`device_company_id`=`trucks`.`truck_device_company_id_fk` LEFT JOIN `vehicle_ownership_types` ON `vehicle_ownership_types`.`ownership_type_id`=`trucks`.`truck_ownership_type_id_fk` LEFT JOIN `insurance_companies` ON `insurance_companies`.`insurance_company_id`=`trucks`.`truck_insurance_company_id_fk` LEFT JOIN `vehicle_colors` ON `vehicle_colors`.`color_id`=`trucks`.`truck_color_id_fk` WHERE `truck_status`='ACT'";



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
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'code':
				$q .=" ORDER BY `truck_code`";
				break;
				case 'condition':
				$q .=" ORDER BY `status_name`";
				break;		
				case 'company':
				$q .=" ORDER BY `company_name`";
				break;		
				default:
				$q .=" ORDER BY `truck_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `truck_id`";	
		}




		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['truck_id'];
			$row['eid']=$Enc->safeurlen($rows['truck_id']);
			$row['status']=$rows['status_name'];
			$row['code']=$rows['truck_code'];
			$row['authority']=$rows['authority_name'];
			$row['company']=$rows['company_name'];
			$row['model']=$rows['model_name'];
			$row['make']=$rows['maker_name'];
			$row['make_year']=$rows['truck_make_year'];
			$row['color']=$rows['color_name'];
			$row['vin']=$rows['truck_vin_number'];
			$row['licence_tag_no']=$rows['truck_licence_tag_no'];
			$row['licence_state']=$rows['state_name']!=null?$rows['state_name']:"";
			$row['licence_tag_expiry_date']=dateFromDbToFormat($rows['truck_licence_tag_expiry_date']);
			
			$row['ownership_type']=$rows['ownership_type_name']!=null?$rows['ownership_type_name']:"";
			if($rows['ownership_type_name']=='LEASE' || $rows['ownership_type_name']=='lease' || $rows['ownership_type_name']=='Lease'){
				$row['leasing_company']=$rows['lease_company_name'];
				$row['leasing_ref_no']=$rows['truck_lease_ref_no'];
				$row['leasing_expiry']=dateFromDbToFormat($rows['truck_lease_expiry_date']); 

			}else{
				$row['leasing_company']="";
				$row['leasing_ref_no']="";
				$row['leasing_expiery']="";		
			}

			$row['insurance_status']=$rows['truck_insurance_status'];
			if($rows['truck_insurance_status']=='Active'){
				$row['insurance_company_name']=$rows['insurance_company_name'];
				$row['insurance_start_date']=dateFromDbToFormat($rows['truck_insurance_start_date']); 
				$row['insurance_expiry_date']=dateFromDbToFormat($rows['truck_insurance_expiry_date']);
			}else{
				$row['insurance_company_name']="";
				$row['insurance_start_date']=""; 
				$row['insurance_expiry_date']="";				
			}


			$row['liability_status']=$rows['truck_liability_status'];
			$row['cargo_status']=$rows['truck_cargo_status'];
			$row['pd_value']=$rows['truck_pd_value'];
			$row['new_pd_value']=$rows['truck_new_pd_value'];
			$row['loss_pay_info']=$rows['truck_loss_pay_info'];
			$row['fhvut_status']=$rows['truck_fhvut_status'];
			$row['fhvut_paid_date']=dateFromDbToFormat($rows['truck_fhvut_paid_date']);
			$row['oregon_permit_status']=$rows['truck_oregon_permit_status'];
			$row['family_engine_number']=$rows['truck_family_engine_number'];
			$row['ifta_status']=$rows['truck_ifta_status'];
			$row['pifta_status']=$rows['truck_pifta_status'];
			$row['nm_status']=$rows['truck_nm_status'];
			$row['pre_pass']=$rows['truck_pre_pass'];
			$row['pre_pass_remark']=$rows['truck_pre_pass_remark'];
			$row['hut']=$rows['truck_hut'];
			$row['hut_remark']=$rows['truck_hut_remark'];




			$row['device_company_name']=$rows['device_company_name']!=null?$rows['device_company_name']:"";
			$row['device_serial_no']=$rows['truck_device_serial_no'];
			$row['device_dash_cam_no']=$rows['truck_device_dash_cam_no'];
			$row['halo']=$rows['truck_halo'];
			$row['odometer_update_type']=$rows['truck_odometer_update_type'];
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
		$batch=5000;
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

		$q="SELECT `truck_id`, `truck_code` FROM `trucks` WHERE `truck_status`='ACT'  ORDER BY `truck_code`";

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


	function trucks_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0020', USER_PRIV)){


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

				///check if duplicate truck id is being created
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `truck_id` FROM `trucks` WHERE `truck_status`='ACT' AND `truck_code`='$code' AND NOT `truck_id`='$update_id'"))>0){
					$InvalidDataMessage="Truck ID already exists";
					$dataValidation=false;
				}

				$status_id=0;
				if(isset($param['status_id']) && $param['status_id']!=""){
					$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);

					include_once APPROOT.'/models/masters/VehiclesStatus.php';
					$VehiclesStatus=new VehiclesStatus;

					if(!$VehiclesStatus->isValidId($status_id)){
						$InvalidDataMessage="Invalid status value";
						$dataValidation=false;
					}

				}
				


				$authority_id=0;
				if(isset($param['$authority_id']) && $param['$authority_id']!=""){
					$authority_id=mysqli_real_escape_string($GLOBALS['con'],$param['$authority_id']);

					include_once APPROOT.'/models/masters/Companies.php';
					$Companies=new Companies;

					if(!$Companies->isValidId($authority_id)){
						$InvalidDataMessage="Invalid authority value";
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

				$color_id=0;
				if(isset($param['color_id']) && $param['color_id']!=""){
					$color_id=mysqli_real_escape_string($GLOBALS['con'],$param['color_id']);

					include_once APPROOT.'/models/masters/VehiclesColors.php';
					$VehiclesColors=new VehiclesColors;

					if(!$VehiclesColors->isValidId($color_id)){
						$InvalidDataMessage="Invalid color value";
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
				
				$group=(isset($param['group']))?mysqli_real_escape_string($GLOBALS['con'],$param['group']):'';

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


				$cargo_status=(isset($param['cargo_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['cargo_status']):'';
				$cargo_status=($cargo_status=='Yes' || $cargo_status=='No')?$cargo_status:"";	

				$pd_value=(isset($param['pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['pd_value']):'';

				$new_pd_value=(isset($param['new_pd_value']))?mysqli_real_escape_string($GLOBALS['con'],$param['new_pd_value']):'';

				$loss_pay_info=(isset($param['loss_pay_info']))?mysqli_real_escape_string($GLOBALS['con'],$param['loss_pay_info']):'';

				$fhvut_status=(isset($param['fhvut_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['fhvut_status']):'';
				$fhvut_status=($fhvut_status=='Paid' || $fhvut_status=='Unpaid')?$fhvut_status:"";

				$fhvut_paid_date_raw=(isset($param['fhvut_paid_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['fhvut_paid_date']):'00/00/0000';

				$fhvut_paid_date=isValidDateFormat($fhvut_paid_date_raw)?date('Y-m-d', strtotime($fhvut_paid_date_raw)):'0000-00-00';

				$family_engine_number=(isset($param['family_engine_number']))?mysqli_real_escape_string($GLOBALS['con'],$param['family_engine_number']):'';


				$oregon_permit_status=(isset($param['oregon_permit_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['oregon_permit_status']):'';
				$oregon_permit_status=($oregon_permit_status=='Yes' || $oregon_permit_status=='No')?$oregon_permit_status:"";

				$ifta_status=(isset($param['ifta_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['ifta_status']):'';
				$ifta_status=($ifta_status=='Yes' || $ifta_status=='No')?$ifta_status:"";

				$pifta_status=(isset($param['pifta_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['pifta_status']):'';
				$pifta_status=($pifta_status=='Yes' || $pifta_status=='No')?$pifta_status:"";

				$nm_status=(isset($param['nm_status']))?mysqli_real_escape_string($GLOBALS['con'],$param['nm_status']):'';
				$nm_status=($nm_status=='Yes' || $nm_status=='No')?$nm_status:"";



				$pre_pass=(isset($param['pre_pass']))?mysqli_real_escape_string($GLOBALS['con'],$param['pre_pass']):'';

				$pre_pass_remark=(isset($param['pre_pass_remark']))?mysqli_real_escape_string($GLOBALS['con'],$param['pre_pass_remark']):'';

				$hut=(isset($param['hut']))?mysqli_real_escape_string($GLOBALS['con'],$param['hut']):'';
				$hut_remark=(isset($param['hut_remark']))?mysqli_real_escape_string($GLOBALS['con'],$param['hut_remark']):'';

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
				$device_dash_cam_no=(isset($param['device_dash_cam_no']))?mysqli_real_escape_string($GLOBALS['con'],$param['device_dash_cam_no']):'';
				$halo=(isset($param['halo']))?mysqli_real_escape_string($GLOBALS['con'],$param['halo']):'';
				$odometer_update_type=(isset($param['odometer_update_type']))?mysqli_real_escape_string($GLOBALS['con'],$param['odometer_update_type']):'';
				$odometer_update_type=($odometer_update_type=='Auto' || $odometer_update_type=='Manual')?$odometer_update_type:"Manual";



				//-----data validation ends



				if($dataValidation){
			//--check if the code exists
					

					$update=mysqli_query($GLOBALS['con'],"UPDATE `trucks` SET `truck_code`='$code',`truck_group`='$group', `truck_status_id_fk`='$status_id',`truck_authority_id_fk`='$authority_id', `truck_company_id_fk`='$company_id', `truck_make_id_fk`='$maker_id', `truck_model_id_fk`='$model_id',`truck_color_id_fk`='$color_id', `truck_make_year`='$make_year', `truck_vin_number`='$vin_number', `truck_licence_tag_no`='$licence_tag_no', `truck_licence_state_id`='$licence_state_id', `truck_licence_tag_expiry_date`='$licence_tag_expiery', `truck_ownership_type_id_fk`='$ownership_type_id', `truck_lease_company_id_fk`='$lease_company_id', `truck_lease_ref_no`='$lease_ref_no', `truck_lease_expiry_date`='$lease_expiry_date',`truck_insurance_status`='$insurance_status', `truck_insurance_company_id_fk`='$insurance_company_id', `truck_insurance_start_date`='$insurance_start_date', `truck_insurance_expiry_date`='$insurance_expiry_date', `truck_liability_status`='$liability_status', `truck_cargo_status`='$cargo_status', `truck_pd_value`='$pd_value', `truck_new_pd_value`='$new_pd_value', `truck_loss_pay_info`='$loss_pay_info', `truck_fhvut_status`='$fhvut_status', `truck_fhvut_paid_date`='$fhvut_paid_date', `truck_oregon_permit_status`='$oregon_permit_status', `truck_family_engine_number`='$family_engine_number', `truck_ifta_status`='$ifta_status', `truck_pifta_status`='$pifta_status', `truck_nm_status`='$nm_status', `truck_pre_pass`='$pre_pass', `truck_pre_pass_remark`='$pre_pass_remark', `truck_hut`='$hut', `truck_hut_remark`='$hut_remark', `truck_device_company_id_fk`='$device_company_id', `truck_device_serial_no`='$device_serial_no', `truck_device_dash_cam_no`='$device_dash_cam_no',`truck_halo`='$halo',`truck_odometer_update_type`='$odometer_update_type', `truck_updated_on`='$time', `truck_added_by`='$USERID' WHERE `truck_id`='$update_id'");
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

	}


}
?>