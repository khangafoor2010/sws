<?php
/**
 *
 */
class Trips
{
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
			$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `trips` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND `trip_truck_id_fk`='$truck_id'");
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
					$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`  FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND`trip_driver_driver_id_fk`='".$driver_a_details['id']."'");
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
						$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`  FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND`trip_driver_driver_id_fk`='".$driver_b_details['id']."'");
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
				$insertTrip=mysqli_query($GLOBALS['con'],"INSERT INTO `trips`(`trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`,`trip_total_miles`,`trip_ppm_plan_group_id`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`,`trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`, `trip_status`, `trip_added_on`, `trip_added_by`) VALUES ('$new_trip_id','$truck_id','$driver_group_id','$total_miles','$ppm_plan_id','$pay_per_mile','$incentive_rate','$total_incentive','$start_date','$end_date','PENDING','ACT','$time','$USERID')");
					//-----------add new trip	
				echo mysqli_error($GLOBALS['con']);
				if($insertTrip){
					///---------insert stops
					$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

					$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
					$stop_inserted=true;
					foreach ($stops_array_senetized as $stop_row) {
						$next_stop_id++;
						$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$new_trip_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
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
						$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$new_trip_id','".$sa_pr['driver_id']."','".$sa_pr['parameter_id']."','".$sa_pr['parameter_amount']."','ACT')");
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

						$salary_parameters=$this->driver_salary_parameter(array('trip_id' =>$new_trip_id ,'driver_id'=>$dA['id'] ));
						$net_earnings=$net_earnings+(floatval($salary_parameters['net_impact']));




						$insertDrivers=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_drivers`( `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_driver_status`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`,`trip_driver_net_earnings`,`trip_driver_incentives_status`,`trip_driver_remarks`) VALUES ('$next_trip_driver_id','$new_trip_id','".$dA['id']."','ACT','".$dA['miles']."','$pay_per_mile','".$dA['basic_earnings']."','".$dA['incentive']."','$net_earnings','HOLD','".$dA['remarks']."')");
						if(!$insertDrivers){
							$driver_inserted=false;
						}
					}
					///---------//insert trip drivers

					if($driver_inserted && $stop_inserted && $trip_salary_parameter_inserted){
						$message="Trip Created Successfuly";
						$status=true;
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
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

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
			if(isset($param['trip_id'])){
				$q .=" AND `trip_salary_parameter_trip_id_fk`='".$param['trip_id']."'";
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

		$query="SELECT `trip_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`,`trip_added_on`,`added`.`user_name` AS `added_by_user_name`,`added`.`user_code` AS `added_by_user_code` FROM `trips` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trips`.`trip_truck_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trips`.`trip_driver_group_id_fk` LEFT JOIN `utab` AS `added` ON `added`.`user_id`=`trips`.`trip_added_by` WHERE `trip_status`='ACT'";

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


//---fetch trip_stop records
			$trip_stops=$this->get_trip_stops_records(array('trip_id'=>$rows['trip_id']));
			$row['trip_stops']=$trip_stops['response']['list'];
//---/fetch trip_stop records

//---fetch trip_drivers records
			$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `driver_id`,`driver_code`, `driver_name_first`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_incentives`, `trip_driver_remarks` FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk`  WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_id_fk`='$trip_id'");
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

				$salary_parameters=$this->driver_salary_parameter(array('trip_id' =>$trip_id ,'driver_id'=>$td['driver_id'] ));
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

	$q="SELECT `trip_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`,`trip_added_on`,`trip_added_by`,`trip_approved_on`,`trip_approved_by`,(SELECT SUM(`trip_salary_parameter_amount`) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_id_fk`=`trip_id` AND `parameter_type_id_fk`='REIMBURSEMENT') AS `trip_salary_parameters_reimbursement` ,(SELECT SUM(`trip_salary_parameter_amount`) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_id_fk`=`trip_id` AND `parameter_type_id_fk`='EARNING') AS `trip_salary_parameters_earning`,(SELECT SUM(`trip_salary_parameter_amount`) FROM `trip_salary_parameters` LEFT JOIN `salary_parameters` ON `salary_parameters`.`parameter_id`=`trip_salary_parameters`.`trip_salary_parameter_parameter_id` WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_id_fk`=`trip_id` AND `parameter_type_id_fk`='DEDUCTION') AS `trip_salary_parameters_deduction` FROM `trips` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trips`.`trip_truck_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trips`.`trip_driver_group_id_fk`  WHERE `trip_status`='ACT'";



//----Apply Filters starts


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

	if(isset($param['approval_status_id'])){
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
		$row['miles']=$rows['trip_total_miles'];
		$row['ppm']=$rows['trip_ppm'];
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


//-------fetch stops
		$trip_stops=$this->get_trip_stops_records(array('trip_id'=>$rows['trip_id']));
		$row['trip_stops_names']=$trip_stops['response']['stops_names'];
//------/fetch stops



//---fetch trip_drivers records
		$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `driver_code`, `driver_name_first` FROM `trip_drivers`  LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk`  WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_id_fk`='".$rows['trip_id']."'");
		$trip_drivers_array=[];
		while ($td=mysqli_fetch_assoc($fetch_drivers)) {
			$tdr=[];
			$tdr['driver_code']=$td['driver_code'];
			$tdr['driver_name']=$td['driver_name_first'];
			array_push($trip_drivers_array, $tdr);
		}
		$row['trip_drivers']=$trip_drivers_array;
//---/fetch trip_drivers records
		array_push($list,$row);
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
	if(isset($param['trip_id'])){

		$fetch_trip_stops=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id`, `trip_stop_trip_id`,`stop_type_name`, `trip_stop_date_time`, `trip_stop_miles_driven`, `city`.`location_name` AS `city_name`,`state`.`location_mini_code` AS `state_mini_code`, `trip_stop_status` FROM `trip_stops` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_stops`.`trip_stop_trip_id` LEFT JOIN `trip_stop_types` ON `trip_stop_types`.`stop_type_id`=`trip_stops`.`trip_stop_type_id_fk` LEFT JOIN `locations` AS `city` ON `city`.`location_id`=`trip_stops`.`trip_stop_location_id` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` WHERE `trip_stop_status`='ACT' AND `trip_stop_trip_id`='".$param['trip_id']."' ORDER BY `trip_stop_id`");
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

			//--check if the  approval list is valid
			$list_valid=true;
			foreach ($approve_eid_list as $al) {
				$check_id=$Enc->safeurlde($al);
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `trip_id` FROM `trips` WHERE `trip_id`='$check_id' AND `trip_approval_status_id_fk`='PENDING'"))!=1){
					$list_valid=false;
				}

			}

			if($list_valid){

				$approve_check=true;
				foreach ($approve_eid_list as $al) {
					$approve_id=$Enc->safeurlde($al);
					$approve=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='APPROVED',`trip_approved_on`='$time',`trip_approved_by`='$USERID' WHERE `trip_id`='$approve_id'");



					//------------insert driver payment of this trip to their statement
					$get_drivers_earnings=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_driver_id_fk`, `trip_driver_net_earnings` FROM `trip_drivers` WHERE `trip_driver_trip_id_fk`='$approve_id'");


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


					while ($gde=mysqli_fetch_assoc($get_drivers_earnings)) {
						$new_driver_transection_id++;

						$driver_old_balance_check=mysqli_query($GLOBALS['con']," SELECT transection_balance FROM `driver_statement` WHERE `transection_driver_id_fk`='".$gde['trip_driver_driver_id_fk']."' ORDER BY `auto` DESC LIMIT 1 ");
						$driver_old_balance=(mysqli_num_rows($driver_old_balance_check)==1)?mysqli_fetch_assoc($driver_old_balance_check)['transection_balance']:0;

						$driver_new_balance=$driver_old_balance+floatval($gde['trip_driver_net_earnings']);
						mysqli_query($GLOBALS['con'],"INSERT INTO `driver_statement`(`transection_id`,`transection_driver_id_fk`, `transection_type`, `transection_description`, `transection_amount_cr`,`transection_amount_dr`,`transection_balance`, `transection_added_on`,`transection_added_by`, `transection_status`, `transection_trip_id_fk`) VALUES ('$new_driver_transection_id','".$gde['trip_driver_driver_id_fk']."','CR','Earnings of trip $approve_id','".$gde['trip_driver_net_earnings']."','0','$driver_new_balance','$time','$USERID','ACT','$approve_id')");
					}
					//----------//insert driver payment of this trip to their statement

					if(!$approve){
						$approve_check=false;
					}
						# code...
				}

				if($approve_check){
					$status=true;
					$message="Approved Successfuly";
				}

			}else{
				$message="One or more id's are invalid in list";
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

	$q="SELECT COUNT(`trip_driver_id`) AS `total_trips_by_driver`, `trip_driver_driver_id_fk`,SUM(`trip_driver_mile`) AS `total_miles_driven`, `driver_id`,`driver_code`, CONCAT(`driver_name_first`,' ', `driver_name_middle`,' ', `driver_name_last`) AS `driver_name` FROM `trip_drivers` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_approval_status_id_fk`='APPROVED'";


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

		$q="SELECT `trip_driver_id`, `trip_id`,`truck_code`,`trip_start_date`,`trip_end_date`, `driver_name_first`, `driver_name_middle`, `driver_name_last`,`driver_code`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_status` FROM `trip_drivers` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` JOIN `trucks` ON `trips`.`trip_truck_id_fk`=`trucks`.`truck_id` WHERE `trip_driver_status`='ACT' AND `driver_id`='$driver_id' AND `trip_approval_status_id_fk`='APPROVED'";

		
		/*if(isset($param['approval_status'])){
	$approval_status=mysqli_real_escape_string($GLOBALS['con'],$param['approval_status']);
	$q.=" AND `trip_approval_status_id_fk`='APPROVED'";
}*/

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
	$row['date']=dateFromDbToFormat($rows['trip_start_date']);
	$row['start_date']=dateFromDbToFormat($rows['trip_start_date']);
	$row['end_date']=dateFromDbToFormat($rows['trip_end_date']);$row['id']=$rows['trip_id'];
//---fetch trip_stop records
	$trip_stops=$this->get_trip_stops_records(array('trip_id'=>$rows['trip_id']));
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

		$q="SELECT `trip_id`, `truck_code`,`group_name`, `trip_total_miles`,`trip_ppm`,`trip_incentive_per_mile`,`trip_incentive`, `trip_start_date`, `trip_end_date`,`trip_approval_status_id_fk`, `driver_id`,`driver_code`, `driver_name_first`,`driver_name_middle`,`driver_name_last`, `trip_driver_mile`, `trip_driver_pay_per_mile`, `trip_driver_basic_earnings`, `trip_driver_deductions`, `trip_driver_reimbursement`, `trip_driver_net_earnings`, `trip_driver_incentives`, `trip_driver_reimbursements_remarks`, `trip_driver_deductions_remarks` FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` LEFT JOIN `trucks` ON `trucks`.`truck_id`=`trips`.`trip_truck_id_fk` LEFT JOIN `drivers` ON `drivers`.`driver_id`=`trip_drivers`.`trip_driver_driver_id_fk` LEFT JOIN `driver_groups` ON `driver_groups`.`group_id`=`trips`.`trip_driver_group_id_fk` WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_id_fk`='$trip_id' AND `trip_driver_driver_id_fk`='$driver_id'";

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
			$trip_stops=$this->get_trip_stops_records(array('trip_id'=>$rows['trip_id']));
			$row['trip_stops']=$trip_stops['response'];
//---/fetch trip_stop records


//---fetch salary parameter records
			$salary_parameters=$this->driver_salary_parameter(array('trip_id' =>$trip_id ,'driver_id'=>$driver_id ));
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



function driver_trip_parameters_details_update($param){
	$status=false;
	$message=null;
	$response=[];
	if(in_array('P0134', USER_PRIV)){
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

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$driver_id=$Enc->safeurlde($param['driver_eid']);
		$trip_id=$Enc->safeurlde($param['trip_eid']);

$new_array=[];//--create a new_array for further use to delete deleted entries
$new_parameters_array=[];

////-----------salary parameters
if(isset($param['parameters'])){
	
	$parameters_raw=$param['parameters'];
	
	

	foreach ($parameters_raw as $parameters_raw) {
		$parameter_raw=[];




////----------check if the trip salary parmater is send or a new paramter is sent


		
		

		if(isset($parameters_raw['trip_salary_parameter_eid'])){
			$trip_salary_parameter_id=$Enc->safeurlde($parameters_raw['trip_salary_parameter_eid']);

						//----if the trip salary parameter is send as blank consider it as new parater

			if($trip_salary_parameter_id=="" || $trip_salary_parameter_id==""){
				$trip_salary_parameter_id="NEW";
			}else{

							//--check if the trip_salary_parameter_id is valid or not
				$check_if_valid_tspi_q=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` WHERE `trip_salary_parameter_id`='$trip_salary_parameter_id' AND `trip_salary_parameter_trip_id_fk`='$trip_id' AND `trip_salary_parameter_driver_id_fk`='$driver_id' AND `trip_salary_parameter_status`='ACT'");
				if(mysqli_num_rows($check_if_valid_tspi_q)!=1){
					$InvalidDataMessage="Invalid trip salary parameter id";
					$dataValidation=false;
					goto ValidationChecker;	
				}else{
					array_push($new_array, $trip_salary_parameter_id);
				}

			}
			$parameter_raw['trip_salary_parameter_id']=$trip_salary_parameter_id;		

		}else{
			$InvalidDataMessage="Please provide trip salary parameter eid";
			$dataValidation=false;
			goto ValidationChecker;
		}
////----------/check if the trip salary parmater is send or a new paramter is sent

		if(isset($parameters_raw['parameter_id'])){
			$parameter_id=mysqli_real_escape_string($GLOBALS['con'],$parameters_raw['parameter_id']);
						//---get parameter details
			$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id'");
			if(mysqli_num_rows($pm_d_q)==1){
				$pm_d_result=mysqli_fetch_assoc($pm_d_q);
				$parameter_raw['parameter_id']=$pm_d_result['parameter_id'];
			}else{
				$InvalidDataMessage="Invalid parameter id";
				$dataValidation=false;
				goto ValidationChecker;							
			}
						//---/get parameter details
		}else{
			$InvalidDataMessage="Please provide parameter id";
			$dataValidation=false;
			goto ValidationChecker;
		}


		$pm_d_q=mysqli_query($GLOBALS['con'],"SELECT `parameter_id` FROM `salary_parameters` WHERE `parameter_id`='$parameter_id'");
		if(mysqli_num_rows($pm_d_q)==1){
			$pm_d_result=mysqli_fetch_assoc($pm_d_q);
			$parameter_raw['parameter_id']=$pm_d_result['parameter_id'];
		}else{
			$InvalidDataMessage="Invalid parameter id";
			$dataValidation=false;
			goto ValidationChecker;							
		}
						//---/get parameter details




		if(isset($parameters_raw['parameter_amount'])){
			$parameter_amount=mysqli_real_escape_string($GLOBALS['con'],$parameters_raw['parameter_amount']);
						//---get parameter details
			if(is_numeric($parameter_amount)){
				$parameter_raw['parameter_amount']=$parameter_amount;
			}else{
				$InvalidDataMessage="Invalid parameter amount";
				$dataValidation=false;
				goto ValidationChecker;							
			}
						//---/get parameter details
		}else{
			$InvalidDataMessage="Please provide parameter amount";
			$dataValidation=false;
			goto ValidationChecker;
		}
		array_push($new_parameters_array, $parameter_raw);
	}
}
////-----------salary parameters


ValidationChecker:
if($dataValidation){

	$USERID=USER_ID;




		///--------firstly delete those recores which are not send (deletd in front end). take the existing records from $new_array

	$deleteQ="UPDATE `trip_salary_parameters` SET `trip_salary_parameter_status`='DLT',`trip_salary_parameter_deleted_by`='$USERID'   WHERE `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_trip_id_fk`='$trip_id' AND `trip_salary_parameter_driver_id_fk`='$driver_id'";
	if(count($new_array)>0){
		$new_array=implode(', ', $new_array);
		$deleteQ.="AND NOT `trip_salary_parameter_id` IN (".$new_array.")";
	}
	$deleteQEx=mysqli_query($GLOBALS['con'],$deleteQ);


///-------loop through the array of send paramets 

	foreach ($new_parameters_array as $n_p_a) {
		
		if($n_p_a['trip_salary_parameter_id']=='NEW'){

					///---------insert salary parameters
			$last_trip_salary_parameter_id=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` ORDER BY `auto` DESC LIMIT 1");

			$next_trip_salary_parameter_id=(mysqli_num_rows($last_trip_salary_parameter_id)==1)?mysqli_fetch_assoc($last_trip_salary_parameter_id)['trip_salary_parameter_id']:1000000;
					///-----//Generate New Unique Id
			$next_trip_salary_parameter_id++;
			$insert_trip_salary_parameter=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_salary_parameters`(`trip_salary_parameter_id`, `trip_salary_parameter_trip_id_fk`, `trip_salary_parameter_driver_id_fk`, `trip_salary_parameter_parameter_id`,`trip_salary_parameter_amount`, `trip_salary_parameter_status`) VALUES ('$next_trip_salary_parameter_id','$trip_id','$driver_id','".$n_p_a['parameter_id']."','".$n_p_a['parameter_amount']."','ACT')");

			
					///---------//insert salary parameters
		}else{

		///-----check if the send details are same with the olds or anything have changed. If anything has changed the update the row
			$check_old=mysqli_query($GLOBALS['con'],"SELECT `trip_salary_parameter_id` FROM `trip_salary_parameters` WHERE `trip_salary_parameter_id`='".$n_p_a['trip_salary_parameter_id']."' AND `trip_salary_parameter_trip_id_fk`='$trip_id' AND `trip_salary_parameter_driver_id_fk`='$driver_id' AND `trip_salary_parameter_status`='ACT' AND `trip_salary_parameter_id`='".$n_p_a['parameter_id']."' AND  `trip_salary_parameter_amount`=  '".$n_p_a['parameter_amount']."' AND  `trip_salary_parameter_status`='ACT'");



			$update=mysqli_query($GLOBALS['con'],"UPDATE `trip_salary_parameters` SET`trip_salary_parameter_parameter_id`='".$n_p_a['parameter_id']."',`trip_salary_parameter_amount`='".$n_p_a['parameter_amount']."',`trip_salary_parameter_updated_by`='$USERID' WHERE `trip_salary_parameter_id`='".$n_p_a['trip_salary_parameter_id']."'");
			if($update){
				$status=true;
				$message="update Successfuly";
			}else{
				$message=SOMETHING_WENT_WROG;
			}

			

		}
	}

	$this->update_trip_earnings($trip_id);			
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

function update_trip_earnings($trip_id){
	//-------get basic details of trip

	$trip_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_group_id_fk`, `trip_ppm`, `trip_incentive_per_mile` FROM `trips` WHERE `trip_id`='$trip_id'");

	if(mysqli_num_rows($trip_q)==1){
		$trip=mysqli_fetch_assoc($trip_q);
		$pay_per_mile=$trip['trip_ppm'];
		$driver_group_id=$trip['trip_driver_group_id_fk'];
		$incentive_rate=$trip['trip_incentive_per_mile'];
		

///--get the totol miles of trip
		$total_miles_q=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT SUM(`trip_stop_miles_driven`) AS total_miles FROM `trip_stops` WHERE `trip_stop_trip_id`='$trip_id' AND `trip_stop_status`='ACT'"));
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
		$query="SELECT `trip_id`, `trip_truck_id_fk`,`trip_driver_group_id_fk`,`trip_ppm_plan_group_id`,`trip_ppm`,`trip_incentive_per_mile` FROM `trips`  WHERE `trip_status`='ACT' AND `trip_id`='$trip_id'  AND `trip_approval_status_id_fk`='PENDING'";


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





//---fetch trip_drivers records
			$fetch_drivers=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_driver_id_fk` FROM `trip_drivers`   WHERE `trip_driver_status`='ACT' AND `trip_driver_trip_id_fk`='$trip_id'");
			$trip_drivers_array=[];
			while ($td=mysqli_fetch_assoc($fetch_drivers)) {
				array_push($trip_drivers_array,$td['trip_driver_driver_id_fk']);
			}	
			
			$row['driver_a']=$trip_drivers_array[0];
			if(isset($trip_drivers_array[1])){
				$row['driver_b']=$trip_drivers_array[1];
			}
//---/fetch trip_drivers records

			





//---fetch trip_stop records
			$fetch_trip_stops=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id`, `trip_stop_date_time`,`trip_stop_type_id_fk`,`trip_stop_location_id` ,`trip_stop_miles_driven` FROM `trip_stops`  WHERE `trip_stop_status`='ACT' AND `trip_stop_trip_id`='$trip_id' ORDER BY `trip_stop_id`");
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


function driver_trip_details_update($param){
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


//---check if valid trip id and valid group id is send
//---sent driver group id should match the old saved one

		if(isset($param['trip_eid'])){
			$trip_id=$Enc->safeurlde($param['trip_eid']);
		}else{
			$InvalidDataMessage="Please provide trip eid";
			$dataValidation=false;
			goto ValidationChecker;
		}


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


		$verify_trip=mysqli_query($GLOBALS['con'],"SELECT `auto`, `trip_id` FROM `trips` WHERE `trip_id`='$trip_id' AND `trip_driver_group_id_fk`='$driver_group_id' AND `trip_approval_status_id_fk`='PENDING'");

		if(mysqli_num_rows($verify_trip)!=1){
			$InvalidDataMessage="Invalid trip details are send";
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
					$InvalidDataMessage="Please provide stop location id";
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
			$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `trips` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND `trip_truck_id_fk`='$truck_id' AND NOT `trip_id`='$trip_id'");
			if(mysqli_num_rows($check_truck_clashing_q)>0){
				$InvalidDataMessage="Trip period clashing. Truck ID has been already used for this period";
				$dataValidation=false;
				goto ValidationChecker;			
			}
//-----/check datetime clashing of truck with existiong records




///---------get drivers of the trip. check if the period is clashing with any of the driver

			$get_trip_drivers_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_driver_id_fk` FROM `trip_drivers` WHERE `trip_driver_trip_id_fk`='$trip_id'");

			while ($get_trip_drivers=mysqli_fetch_assoc($get_trip_drivers_q)) {
				

//-----check datetime clashing of driver a with existiong records
//-----it will prevent the repeative use driver a		

			//--check if any trip record exists for the selected truck, datetime of which is  clasing this truck priod
					$check_truck_clashing_q=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_id`, `trip_driver_trip_id_fk`, `trip_driver_driver_id_fk`, `trip_id`, `trip_truck_id_fk`, `trip_driver_group_id_fk`, `trip_total_miles`, `trip_ppm_plan_group_id`, `trip_ppm`, `trip_incentive_per_mile`, `trip_incentive`, `trip_start_date`, `trip_end_date`  FROM `trip_drivers` LEFT JOIN `trips` ON `trips`.`trip_id`=`trip_drivers`.`trip_driver_trip_id_fk` WHERE `trip_status`='ACT' AND `trip_approval_status_id_fk` IN ('APPROVED','PENDING') AND ((`trip_start_date`<'$start_date' AND `trip_end_date`>'$start_date') OR (`trip_start_date`<'$end_date' AND `trip_end_date`>'$end_date')) AND`trip_driver_driver_id_fk`='".$get_trip_drivers['trip_driver_driver_id_fk']."' AND NOT `trip_id`='$trip_id'");
					if(mysqli_num_rows($check_truck_clashing_q)>0){
						$InvalidDataMessage="Trip period clashing. Driver has been already used for this period";
						$dataValidation=false;
						goto ValidationChecker;			
					}
//-----/check datetime clashing of driver a with existiong records


			}







			//-----data validation ends
		ValidationChecker:
		if($dataValidation){
					//$message="validation is ok, You may proceed";

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



			$insertTrip=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_truck_id_fk`='$truck_id',`trip_driver_group_id_fk`='$driver_group_id',`trip_total_miles`='$total_miles',`trip_ppm`='$pay_per_mile',`trip_incentive_per_mile`='$incentive_rate',`trip_incentive`='$total_incentive',`trip_start_date`='$start_date',`trip_end_date`='$end_date',`trip_updated_on`='$time',`trip_updated_by`='$USERID' WHERE `trip_id`='$trip_id'");
					//-----------add new trip	
			

			//-------delete old trips
			mysqli_query($GLOBALS['con'],"DELETE FROM `trip_stops` WHERE `trip_stop_trip_id`='$trip_id'");
			if($insertTrip){
					///---------insert stops
				$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `trip_stop_id` FROM `trip_stops` ORDER BY `auto` DESC LIMIT 1");

				$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['trip_stop_id']:1000000;

					///-----//Generate New Unique Id
				$stop_inserted=true;
				foreach ($stops_array_senetized as $stop_row) {
					$next_stop_id++;
					$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `trip_stops`(`trip_stop_id`, `trip_stop_trip_id`, `trip_stop_type_id_fk`, `trip_stop_date_time`, `trip_stop_miles_driven`, `trip_stop_location_id`, `trip_stop_status`) VALUES ('$next_stop_id','$trip_id','".$stop_row['stop_type_id']."','".$stop_row['stop_date']."','".$stop_row['stop_mile']."','".$stop_row['stop_location_id']."','ACT')");
					if(!$insertStop){
						$stop_inserted=false;
					}
				}
					///---------//insert stops

					///---------//insert trip drivers

				if($stop_inserted){
					$message="Trip Updated Successfuly";
					$status=true;
					$this->update_trip_earnings($trip_id);
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
		$message=NOT_AUTHORIZED_MSG;
	}
	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;

}

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
	$q=mysqli_query($GLOBALS['con'],"SELECT DATE_FORMAT(`trip_end_date`, '%M-%y') AS `trip_month` FROM `trips` WHERE `trip_status`='ACT'  GROUP BY `trip_month` ORDER BY `trip_end_date`");
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
	if(in_array('PADMIN', USER_PRIV)){


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
			$check_id=mysqli_query($GLOBALS['con'],"SELECT `trip_id` FROM `trips` WHERE `trip_id`='$cancel_id' AND `trip_approval_status_id_fk`='APPROVED'");
			if(mysqli_num_rows($check_id)==1){
				$trip_id=mysqli_fetch_assoc($check_id)['trip_id'];
				if($trip_id==""){
					$InvalidDataMessage="Invalid trip eid";
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
				$cancel_trip=mysqli_query($GLOBALS['con'],"UPDATE `trips` SET `trip_approval_status_id_fk`='CANCELLED',`trip_cancelled_on`='$time',`trip_cancelled_by`='$USERID',`trip_canelled_remark`='' WHERE `trip_id`='$trip_id'");

				if($cancel_trip){
	//--------Enter a cancel trip entry in driver statement to balance the amount by debiting the same amount that was credited to during approval of trip

	//-----------get entries from statement
					$get_driver_statements_q=mysqli_query($GLOBALS['con'],"SELECT `transection_id`,`transection_driver_id_fk`,`transection_amount_cr` FROM `driver_statement` WHERE `transection_trip_id_fk`='$trip_id'");





////------------------generate new transection id
					$get_drivers_earnings=mysqli_query($GLOBALS['con'],"SELECT `trip_driver_driver_id_fk`, `trip_driver_net_earnings` FROM `trip_drivers` WHERE `trip_driver_trip_id_fk`='$trip_id'");


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
////------------------generate new transection id


					$cancell_driver_statement=true;
					while ($cancel_row=mysqli_fetch_assoc($get_driver_statements_q)) {
						$transection_id=$cancel_row['transection_id'];
						$transection_amount=$cancel_row['transection_amount_cr'];
						$transection_driver_id=$cancel_row['transection_driver_id_fk'];

						$new_driver_transection_id++;

						$driver_old_balance_check=mysqli_query($GLOBALS['con']," SELECT transection_balance FROM `driver_statement` WHERE `transection_driver_id_fk`='$transection_driver_id' ORDER BY `auto` DESC LIMIT 1 ");
						$driver_old_balance=(mysqli_num_rows($driver_old_balance_check)==1)?mysqli_fetch_assoc($driver_old_balance_check)['transection_balance']:0;

						$driver_new_balance=$driver_old_balance-floatval($transection_amount);
						$cancell_driver_statement_q=mysqli_query($GLOBALS['con'],"INSERT INTO `driver_statement`(`transection_id`,`transection_driver_id_fk`, `transection_type`, `transection_description`, `transection_amount_cr`,`transection_amount_dr`,`transection_balance`, `transection_added_on`,`transection_added_by`, `transection_status`, `transection_trip_id_fk`) VALUES ('$new_driver_transection_id','$transection_driver_id','DR','Cancellation of transaction $transection_id for trip $trip_id','0','$transection_amount','$driver_new_balance','$time','$USERID','ACT','$trip_id')");
						if(!$cancell_driver_statement_q){
							$cancell_driver_statement=false;	
						}

					}
					if($cancell_driver_statement){
						$status=true;
						$message="Cancelled Successfuly";
					}
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