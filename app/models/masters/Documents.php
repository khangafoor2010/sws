<?php
/**
 *
 */
class Documents
{

	function documents_verify($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['document_eid'])){
			$document_id=$Enc->safeurlde($param['document_eid']);
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
			if(mysqli_num_rows($validate_document_id)==1){
				$document_type_relates_to=mysqli_fetch_assoc($validate_document_id)['document_type_relates_to'];

			}else{
				$InvalidDataMessage="Invalid document eid".$document_id;
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide document eid";
			$dataValidation=false;
			goto ValidationChecker;	 			
		}

		//-----check the type document relates to
		//-----check if user have right to verify this type of documents
		if($document_type_relates_to=='DRIVER'){
			if(!in_array('P0146', USER_PRIV)){
				$InvalidDataMessage=NOT_AUTHORIZED_MSG;
				$dataValidation=false;
				goto ValidationChecker;				
			}
		}elseif ($document_type_relates_to=='TRUCK') {
			if(!in_array('P0148', USER_PRIV)){
				$InvalidDataMessage=NOT_AUTHORIZED_MSG;
				$dataValidation=false;
				goto ValidationChecker;	
			}
		}




			ValidationChecker:
			if($dataValidation){
				$executionMessage='';
				$execution=true;	
				$time=time();

				$verify=mysqli_query($GLOBALS['con'],"UPDATE `documents` SET `document_verification_status`='VERIFIED',`document_verified_on`='$time',`document_verified_by`='".USER_ID."' WHERE `document_id`='$document_id'");

				if(!$verify){
					$executionMessage=SOMETHING_WENT_WROG.' step 01';
					$execution=false;
					goto executionChecker;				
				}


				executionChecker:
				if($execution){
					$status=true;
					$message="Verified Successfuly";
				}else{
					$message=$executionMessage;
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


		function documents_reject($param){
			$status=false;
			$message=null;
			$response=null;

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$InvalidDataMessage="";
			$dataValidation=true;

			if(isset($param['document_eid'])){
				$document_id=$Enc->safeurlde($param['document_eid']);
				$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
				if(mysqli_num_rows($validate_document_id)==1){
					$document_type_relates_to=mysqli_fetch_assoc($validate_document_id)['document_type_relates_to'];

				}else{
					$InvalidDataMessage="Invalid document eid".$document_id;
					$dataValidation=false;
					goto ValidationChecker;
				}

			}else{
				$InvalidDataMessage="Please provide document eid";
				$dataValidation=false;
				goto ValidationChecker;	 			
			}

		//-----check the type document relates to
		//-----check if user have right to verify this type of documents
			if($document_type_relates_to=='DRIVER'){
				if(!in_array('P0146', USER_PRIV)){
					$InvalidDataMessage=NOT_AUTHORIZED_MSG;
					$dataValidation=false;
					goto ValidationChecker;				
				}
			}elseif ($document_type_relates_to=='TRUCK') {
				if(!in_array('P0148', USER_PRIV)){
					$InvalidDataMessage=NOT_AUTHORIZED_MSG;
					$dataValidation=false;
					goto ValidationChecker;	
				}
			}



				ValidationChecker:
				if($dataValidation){
					$executionMessage='';
					$execution=true;	
					$time=time();

					$verify=mysqli_query($GLOBALS['con'],"UPDATE `documents` SET `document_verification_status`='REJECTED',`document_rejected_on`='$time',`document_rejected_by`='".USER_ID."' WHERE `document_id`='$document_id'");

					if(!$verify){
						$executionMessage=SOMETHING_WENT_WROG.' step 01';
						$execution=false;
						goto executionChecker;				
					}


					executionChecker:
					if($execution){
						$status=true;
						$message="Rejected Successfuly";
					}else{
						$message=$executionMessage;
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

			function driver_documents_upload($param,$param_file){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$InvalidDataMessage="";
				$dataValidation=true;

				if(!in_array('P0145', USER_PRIV)){
					$InvalidDataMessage=NOT_AUTHORIZED_MSG;
					$dataValidation=false;
					goto ValidationChecker;				
				}



				if(isset($param['driver_eid'])){
					$driver_id=$Enc->safeurlde($param['driver_eid']);
					$get_driver=mysqli_query($GLOBALS['con'],"SELECT `driver_id`, `driver_code` FROM `drivers` WHERE `driver_id`='$driver_id'");
					if(mysqli_num_rows($get_driver)==1){
						$driver_code=mysqli_fetch_assoc($get_driver)['driver_code'];
					}else{
						$InvalidDataMessage="Invalid Driver eid";
						$dataValidation=false;
						goto ValidationChecker;				
					}

				}else{
					$InvalidDataMessage="Please provide drivder eid";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}

				if(isset($param['document_type_eid'])){
					$document_type_id=$Enc->safeurlde($param['document_type_eid']);

			//----------check if docucment type id belongs to DRIVER
					$get_document_type_details=mysqli_query($GLOBALS['con'],"SELECT  `document_type_id`,`document_type_name`, `document_type_expiry_option` FROM `documents_types` WHERE `document_type_id`='$document_type_id' AND `document_type_relates_to`='DRIVER'");
					if(mysqli_num_rows($get_document_type_details)==1){
						$dtd=mysqli_fetch_assoc($get_document_type_details);
						$document_type_name=$dtd['document_type_name'];

				///-----check if expiry date is required or not for the docuemnt
						if($dtd['document_type_expiry_option']=='T'){

					//--check if expiry date is send in param or not
							if(isset($param['expiry_date']) && $param['expiry_date']!=''){
								if(isValidDateFormat($param['expiry_date'])){
									$expiry_date=date('Y-m-d', strtotime($param['expiry_date']));
								}else{
									$InvalidDataMessage="Invalid expiry format";
									$dataValidation=false;
									goto ValidationChecker;								
								}
							}else{
								$InvalidDataMessage="Please provide expiry date";
								$dataValidation=false;
								goto ValidationChecker;								
							}

						}else{
							$expiry_date='0000-00-00';
						}


					}else{
						$InvalidDataMessage="Invalid document eid";
						$dataValidation=false;
						goto ValidationChecker;	 				
					}

				}else{
					$InvalidDataMessage="Please provide document eid";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}


//----verify document details to be uploaded
				if(isset($param_file['tmp_name'])){
					$fileType= strtolower(pathinfo($param_file["name"],PATHINFO_EXTENSION));

				}else{
					$InvalidDataMessage="Please provide document tmp_name";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}



				if(filesize($param_file['tmp_name'])>floatval(MAX_FILE_UPLOAD_SIZE)){
					$InvalidDataMessage='File size should be less than '.MAX_FILE_UPLOAD_SIZE.' Bytes';
					$dataValidation=false;
					goto ValidationChecker;	 			
				}




				if(!isset($param_file['name'])){
					$InvalidDataMessage="Please provide document name";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}



				ValidationChecker:
				if($dataValidation){


					$executionMessage='';
					$execution=true;


			///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `documents` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['document_id'])+1:1;
			///-----//Generate New Unique Id




			//--upload image 	
					$send_param=array(
						'path'=>'documents/drivers'
					);
					$new_file_name=$driver_code.'-'.to_alphanumeric($document_type_name,'-').'-'.date('ymdHi').'-'.$next_id.'.'.$fileType;

					$upload_document=upload_document($send_param,array('tmp_name'=>$param_file["tmp_name"],'type'=>$param_file["type"],'name'=>$new_file_name));




					if(!$upload_document){
						$executionMessage=SOMETHING_WENT_WROG.' step 01'.$upload_document['message'];
						$execution=false;
						goto executionChecker;
					}

					$time=time();

					$delete_old_docs=mysqli_query($GLOBALS['con'],"UPDATE `documents` SET `document_status`='DEL',`document_deleted_on`='$time',`docuemnt_deleted_by`='".USER_ID."' WHERE `document_status`='ACT' AND `document_driver_id_fk`='$driver_id' AND `document_type_id_fk`='$document_type_id'");

					if(!$delete_old_docs){
						$executionMessage=SOMETHING_WENT_WROG.' step 02';
						$execution=false;
						goto executionChecker;				
					}


			//--insert document in table
					$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_driver_id_fk`, `document_status`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$driver_id','ACT','PENDING','$time','".USER_ID."')");
					if(!$document_insert){
						$executionMessage=SOMETHING_WENT_WROG.' step 03';
						$execution=false;
						goto executionChecker;
					}

					executionChecker:
					if($execution){
						$status=true;
						$message="Uploaded Successfuly";
					}else{
						$message=$executionMessage;
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


			function truck_documents_upload($param,$param_file){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$InvalidDataMessage="";
				$dataValidation=true;


				if(!in_array('P0147', USER_PRIV)){
					$InvalidDataMessage=NOT_AUTHORIZED_MSG;
					$dataValidation=false;
					goto ValidationChecker;				
				}


				if(isset($param['truck_eid'])){
					$truck_id=$Enc->safeurlde($param['truck_eid']);
					$get_driver=mysqli_query($GLOBALS['con'],"SELECT `truck_id`, `truck_code` FROM `trucks` WHERE `truck_id`='$truck_id'");
					if(mysqli_num_rows($get_driver)==1){
						$truck_code=mysqli_fetch_assoc($get_driver)['truck_code'];
					}else{
						$InvalidDataMessage="Invalid truck eid";
						$dataValidation=false;
						goto ValidationChecker;				
					}

				}else{
					$InvalidDataMessage="Please provide truck eid";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}

				if(isset($param['document_type_eid'])){
					$document_type_id=$Enc->safeurlde($param['document_type_eid']);

			//----------check if docucment type id belongs to DRIVER
					$get_document_type_details=mysqli_query($GLOBALS['con'],"SELECT  `document_type_id`,`document_type_name`, `document_type_expiry_option` FROM `documents_types` WHERE `document_type_id`='$document_type_id' AND `document_type_relates_to`='TRUCK'");
					if(mysqli_num_rows($get_document_type_details)==1){
						$dtd=mysqli_fetch_assoc($get_document_type_details);
						$document_type_name=$dtd['document_type_name'];

				///-----check if expiry date is required or not for the docuemnt
						if($dtd['document_type_expiry_option']=='T'){

					//--check if expiry date is send in param or not
							if(isset($param['expiry_date']) && $param['expiry_date']!=''){
								if(isValidDateFormat($param['expiry_date'])){
									$expiry_date=date('Y-m-d', strtotime($param['expiry_date']));
								}else{
									$InvalidDataMessage="Invalid expiry format";
									$dataValidation=false;
									goto ValidationChecker;								
								}
							}else{
								$InvalidDataMessage="Please provide expiry date";
								$dataValidation=false;
								goto ValidationChecker;								
							}

						}else{
							$expiry_date='0000-00-00';
						}


					}else{
						$InvalidDataMessage="Invalid document eid";
						$dataValidation=false;
						goto ValidationChecker;	 				
					}

				}else{
					$InvalidDataMessage="Please provide document eid";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}


//----verify document details to be uploaded
				if(isset($param_file['tmp_name'])){
					$fileType= strtolower(pathinfo($param_file["name"],PATHINFO_EXTENSION));

				}else{
					$InvalidDataMessage="Please provide document tmp_name";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}


				if(filesize($param_file['tmp_name'])>floatval(MAX_FILE_UPLOAD_SIZE)){
					$InvalidDataMessage='File size should be less than '.MAX_FILE_UPLOAD_SIZE.' Bytes';
					$dataValidation=false;
					goto ValidationChecker;	 			
				}

				if(!isset($param_file['name'])){
					$InvalidDataMessage="Please provide document name";
					$dataValidation=false;
					goto ValidationChecker;	 			
				}



				ValidationChecker:
				if($dataValidation){


					$executionMessage='';
					$execution=true;


			///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `documents` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['document_id'])+1:1;
			///-----//Generate New Unique Id



			//--upload image 	
					$send_param=array(
						'path'=>'documents/trucks'
					);
					$new_file_name=$truck_code.'-'.to_alphanumeric($document_type_name,'-').'-'.date('ymdHi').'-'.$next_id.'.'.$fileType;

					$upload_document=upload_document($send_param,array('tmp_name'=>$param_file["tmp_name"],'type'=>$param_file["type"],'name'=>$new_file_name));

					if(!$upload_document){
						$executionMessage=SOMETHING_WENT_WROG.' step 01'.$upload_document['message'];
						$execution=false;
						goto executionChecker;
					}

					$time=time();


					$delete_old_docs=mysqli_query($GLOBALS['con'],"UPDATE `documents` SET `document_status`='DEL',`document_deleted_on`='$time',`docuemnt_deleted_by`='".USER_ID."' WHERE `document_status`='ACT' AND `document_truck_id_fk`='$truck_id' AND `document_type_id_fk`='$document_type_id'");

					if(!$delete_old_docs){
						$executionMessage=SOMETHING_WENT_WROG.' step 02';
						$execution=false;
						goto executionChecker;				
					}


			//--insert document in table

					$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_truck_id_fk`, `document_status`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$truck_id','ACT','PENDING','$time','".USER_ID."')");
					if(!$document_insert){
						$executionMessage=SOMETHING_WENT_WROG.' step 03';
						$execution=false;
						goto executionChecker;
					}

					executionChecker:
					if($execution){
						$status=true;
						$message="Uploaded Successfuly";
					}else{
						$message=$executionMessage;
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
			function driver_documents($param){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				if(isset($param['driver_eid'])){
					$driver_id=$Enc->safeurlde($param['driver_eid']);

					return $this->documents_list(array('driver_id'=>$driver_id,'document_type_relates_to'=>'DRIVER'));

				}else{
					$message="Please provide drivder eid";
				}

				$r=[];
				$r['status']=$status;
				$r['message']=$message;
				$r['response']=$response;
				return $r;	
			}
			function truck_documents($param){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				if(isset($param['truck_eid'])){
					$truck_id=$Enc->safeurlde($param['truck_eid']);

					return $this->documents_list(array('truck_id'=>$truck_id,'document_type_relates_to'=>'TRUCK'));

				}else{
					$message="Please provide truck eid";
				}

				$r=[];
				$r['status']=$status;
				$r['message']=$message;
				$r['response']=$response;
				return $r;	
			}	
			function documents_list($param){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$InvalidDataMessage="";
				$dataValidation=true;

				ValidationChecker:
				if($dataValidation){
					$q="SELECT `document_type_id`, `document_type_name`, `document_type_expiry_option`, `document_type_required_option`, `document_type_expiry_alert`, `document_type_status`,`document_type_relates_to` FROM `documents_types` WHERE `document_type_status`='ACT' ";


					if(isset($param['document_type_relates_to']) && $param['document_type_relates_to']!==''){
						$document_type_relates_to=senetize_input($param['document_type_relates_to']);
						$q.=" AND `document_type_relates_to`='$document_type_relates_to'";
					}			


					$qEx=mysqli_query($GLOBALS['con'],$q);
					$list=[];
					while ($rows=mysqli_fetch_assoc($qEx)) {
						$part_a=array(
							'type_id' => $rows['document_type_id'], 
							'type_eid' => $Enc->safeurlen($rows['document_type_id']), 
							'type_name' =>$rows['document_type_name'], 
							'type_expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
							'type_required_option' =>($rows['document_type_required_option']=='T')?true:false, 
						);

						$path=DOCUMENTS_ROOT.'documents/';
						switch ($rows['document_type_relates_to']) {
							case 'DRIVER':
							$path.='drivers/';
							break;
							case 'TRUCK':
							$path.='trucks/';
							break;						
							default:
							# code...
							break;
						}

					//-------check if the file has been uploaded or not
						$get_document_q="SELECT `document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_driver_id_fk`, `document_status`, `document_added_on`, `document_added_by`,DATEDIFF(`document_expriry_date`,CURDATE()) AS `expiry_days_left`, `document_verified_on`, `document_verified_by`, `document_added_on`, `document_added_by`,`document_verified_by`,`document_verified_on`,`document_verification_status` FROM `documents` WHERE `document_status`='ACT'  AND`document_type_id_fk`='".$rows['document_type_id']."'";


						if(isset($param['driver_id']) && $param['driver_id']!==''){
							$driver_id=senetize_input($param['driver_id']);
							$get_document_q.=" AND `document_driver_id_fk`='$driver_id'";
						}
						if(isset($param['truck_id']) && $param['truck_id']!==''){
							$truck_id=senetize_input($param['truck_id']);
							$get_document_q.=" AND `document_truck_id_fk`='$truck_id'";
						}
						$get_document_q.="  ORDER BY `auto` DESC LIMIT 1";


						$get_document=mysqli_query($GLOBALS['con'],$get_document_q);
						$part_b=array('is_uploaded' => false);
						$expiry_alert=false;
						$is_expired=false;
						$verification_status='';
						if(mysqli_num_rows($get_document)==1){
							$document=mysqli_fetch_assoc($get_document);


							include_once APPROOT.'/models/masters/Users.php';
							$Users=new Users;
							$added_user=$Users->user_basic_details($document['document_added_by']);
							$added_by_user_code=$added_user['user_code'];
							$added_on_datetime=dateTimeFromDbTimestamp($document['document_added_on']);


							$verified_by_user_code='';
							$verified_on_datetime='';

							if($document['document_verification_status']=='VERIFIED'){
								$verified_user=$Users->user_basic_details($document['document_verified_by']);
								$verified_by_user_code=$verified_user['user_code'];
								$verified_on_datetime=dateTimeFromDbTimestamp($document['document_verified_on']);
							}

							if($rows['document_type_expiry_option']=='T'){
								if($document['expiry_days_left']<=$rows['document_type_expiry_alert']){
									$expiry_alert=true;
								}
								if($document['expiry_days_left']<=0){
									$is_expired=true;
								}
							}
							$verification_status=$document['document_verification_status'];
							$part_b=array(
								'is_uploaded' => true,
								'document_details' =>array(

									'name'=>$document['document_file_name'],
									'eid'=>$Enc->safeurlen($document['document_id']),
									'expiry_date'=>dateFromDbToFormat($document['document_expriry_date']),
									'expiry_days_left'=>($document['expiry_days_left']!=null)?$document['expiry_days_left']:'',
									'expiry_alert'=>$expiry_alert,
									'is_expired'=>$is_expired,
									'file_path'=>$path.$document['document_file_name'],
									'verification_status'=>$verification_status,
									'added_by_user_code'=>$added_by_user_code,
									'added_on_datetime'=>$added_on_datetime,
									'verified_by_user_code'=>$verified_by_user_code,
									'verified_on_datetime'=>$verified_on_datetime
								) 
							);
						}

						$push_entry=true;

			//-----------apply upload status filter
						if(isset($param['is_uploaded']) && $param['is_uploaded']!=''){
							$param['is_uploaded']=senetize_input($param['is_uploaded']);
							if($part_b['is_uploaded']!=to_boolean($param['is_uploaded'])){
								$push_entry=false;
							}	
						}

//-----------apply expiry status filter
						if(isset($param['is_expired']) && $param['is_expired']!=''){

							$param['is_expired']=senetize_input($param['is_expired']);
							if($is_expired!=to_boolean($param['is_expired'])){
								$push_entry=false;

							}				
						}

//-----------apply expiry status filter
						if(isset($param['expiry_alert']) && $param['expiry_alert']!=''){

							$param['expiry_alert']=senetize_input($param['expiry_alert']);
							if($expiry_alert!=to_boolean($param['expiry_alert'])){
								$push_entry=false;

							}				
						}

//-----------apply verification filter
						if(isset($param['verification_status']) && $param['verification_status']!=''){

							if($verification_status!=senetize_input($param['verification_status'])){
								$push_entry=false;

							}				
						}

						if($push_entry){
							array_push($list, array_merge($part_a,$part_b));
						}


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

			function all_drivers_documents_list($param){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$q="SELECT  `driver_id`, `driver_code`, `driver_name_first`, `driver_name_middle`, `driver_name_last` FROM `drivers` LEFT JOIN `employee_status` ON `employee_status`.`status_id`=`drivers`.`driver_status_id_fk` WHERE `driver_status`='ACT' AND `status_type_id_fk`='ACTIVE'";


				$qEx=mysqli_query($GLOBALS['con'],$q);
				$list=[];
				while ($rows=mysqli_fetch_assoc($qEx)) {
					$row=[];
					$row['driver_eid']=$Enc->safeurlen($rows['driver_id']);
					$row['driver_code']=$rows['driver_code'];
					$row['driver_name']=$rows['driver_name_first'];
					$driver_documents=$this->documents_list(array_merge(array('driver_id'=>$rows['driver_id'],'document_type_relates_to'=>'DRIVER'),$param));
					$row['documents']=$driver_documents['response']['list'];

					if(count($row['documents'])>0){
						array_push($list, $row);	
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


			function all_trucks_documents_list($param){
				$status=false;
				$message=null;
				$response=null;

				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$q="SELECT `truck_id`, `truck_code` FROM `trucks` WHERE `truck_status`='ACT' ";


				$qEx=mysqli_query($GLOBALS['con'],$q);
				$list=[];
				while ($rows=mysqli_fetch_assoc($qEx)) {
					$row=[];
					$row['truck_eid']=$Enc->safeurlen($rows['truck_id']);
					$row['truck_code']=$rows['truck_code'];
					$truck_documents=$this->documents_list(array_merge(array('truck_id'=>$rows['truck_id'],'document_type_relates_to'=>'TRUCK'),$param));
					$row['documents']=$truck_documents['response']['list'];

					if(count($row['documents'])>0){
						array_push($list, $row);	
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
		}
		?>