<?php
/**
 *
 */
class TruckDocuments
{
    
    
    	function pending_uploads_list($param){
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
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;

		$InvalidDataMessage="";
		$dataValidation=true;

		ValidationChecker:
		if($dataValidation){
			$q="SELECT `truck_id`,`truck_code`,`document_type_id`,`document_type_name`,`document_type_expiry_option`,`document_type_expiry_alert`, `document_type_required_option` ,`document_id` FROM `trucks` join `documents_types` ON `document_type_status`='ACT' AND `document_type_required_option`='T' AND ( NOT `document_static_name`='RENTAL-AGREEMENT' OR (`document_static_name`='RENTAL-AGREEMENT' AND `truck_ownership_type_id_fk` IN ('LEASE','RENTAL'))) LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_id` IS NULL";

//-----------apply filter
			if(isset($param['truck_id']) && $param['truck_id']!=''){
				$truck_id=senetize_input($param['truck_id']);
				$q.=" AND `truck_id`='$truck_id'";

			}

			if(isset($param['document_type_id']) && $param['document_type_id']!=''){
				$document_type_id=senetize_input($param['document_type_id']);
				$q.=" AND `document_type_id`='$document_type_id'";

			}
//----------/apply filter



			$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
			$q .="  ORDER BY `truck_code`,`document_type_name`  limit $from, $batch";
			$qEx=mysqli_query($GLOBALS['con'],$q);

			$list=[];
			$counter=$from;
			$list=[];
			$list=[];$path=DOCUMENTS_ROOT.'documents/trucks/';
			while ($rows=mysqli_fetch_assoc($qEx)) {
				array_push($list,[
					'sr_no'=>++$counter,
					'truck_id'=>$rows['truck_id'],
					'truck_eid'=>$Enc->safeurlen($rows['truck_id']),
					'truck_code'=>$rows['truck_code'],
					'type_id' => $rows['document_type_id'], 
					'type_eid' => $Enc->safeurlen($rows['document_type_id']), 
					'type_name' =>$rows['document_type_name'], 
					'type_expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
					'type_required_option' =>($rows['document_type_required_option']=='T')?true:false,
					'is_uploaded'=>($rows['document_id']!=NULL)?true:false
				]);

			}

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

		}else{
			$message=$InvalidDataMessage;
		}

		$r=[];
		$r['status']=$status;
		$r['message']=$param;
		$r['response']=$response;
		return $r;	
	}
    	function document_history($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;

		$InvalidDataMessage="";
		$dataValidation=true;


		if(isset($param['truck_eid']) && $param['truck_eid']!=""){
			$truck_id=$Enc->safeurlde($param['truck_eid']);
		}else{
			$InvalidDataMessage="Please provide truck eid";
			$dataValidation=false;
			goto ValidationChecker;	 			
		}
		if(isset($param['document_type_eid'])  && $param['document_type_eid']!=""){
			$document_type_id=$Enc->safeurlde($param['document_type_eid']);
		}else{
			$InvalidDataMessage="Please provide ddocument eid";
			$dataValidation=false;
			goto ValidationChecker;	 			
		}

		ValidationChecker:
		if($dataValidation){
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `document_id`, `document_file_name`, `document_expriry_date`, `document_status`, `document_remarks`, `document_verification_status`, `document_verified_on`, `document_verified_by`, `document_rejected_on`, `document_rejected_by`, `document_added_on`, `document_added_by` FROM `history_truck_documents` WHERE `document_truck_id_fk`='$truck_id' AND `document_type_id_fk`='$document_type_id' ORDER BY `document_added_by` DESC");
			$list=[];
			$path=DOCUMENTS_ROOT.'documents/drivers/';
			while ($rows=mysqli_fetch_assoc($qEx)) {
				$added_user=$Users->user_basic_details($rows['document_added_by']);
				$added_by_user_code=$added_user['user_code'];
				$added_on_datetime=dateTimeFromDbTimestamp($rows['document_added_on']);	
				$verified_by_user_code='';
				$verified_on_datetime='';
				$rejected_by_user_code='';
				$rejected_on_datetime='';

				if($rows['document_verification_status']=='VERIFIED'){
					$verified_user=$Users->user_basic_details($rows['document_verified_by']);
					$verified_by_user_code=$verified_user['user_code'];
					$verified_on_datetime=dateTimeFromDbTimestamp($rows['document_verified_on']);
				}

				if($rows['document_verification_status']=='REJECTED'){
					$rejected_user=$Users->user_basic_details($rows['document_rejected_by']);
					$rejected_by_user_code=$rejected_user['user_code'];
					$rejected_on_datetime=dateTimeFromDbTimestamp($rows['document_rejected_on']);
				}

				array_push($list,[

					'name'=>$rows['document_file_name'],
					'id'=>$rows['document_id'],
					'eid'=>$Enc->safeurlen($rows['document_id']),
					'expiry_date'=>dateFromDbToFormat($rows['document_expriry_date']),
					'file_path'=>$path.$rows['document_file_name'],
					'verification_status'=>$rows['document_verification_status'],
					'added_by_user_code'=>$added_by_user_code,
					'added_on_datetime'=>$added_on_datetime,
					'verified_by_user_code'=>$verified_by_user_code,
					'verified_on_datetime'=>$verified_on_datetime,
					'rejected_by_user_code'=>$rejected_by_user_code,
					'rejected_on_datetime'=>$rejected_on_datetime,					
					'remarks'=>$rows['document_remarks'],
				]);


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

	return ['status'=>$status,'message'=>$message,'response'=>$response];	
	}
	function truck_documents_quick_totals($param){
		$status=false;
		$message=null;
		$response=null;
		$res=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT 
			(SELECT COUNT(`truck_id`) FROM `trucks` join `documents_types` ON `documents_types`.`document_type_relates_to`='TRUCK' AND `document_type_status`='ACT' AND `truck_status`='ACT') as `total`,
						(SELECT COUNT(`document_type_id`) FROM `trucks` join `documents_types` ON `document_type_status`='ACT' AND `document_type_required_option`='T' AND ( NOT `document_static_name`='RENTAL-AGREEMENT' OR (`document_static_name`='RENTAL-AGREEMENT' AND `truck_ownership_type_id_fk` IN ('LEASE','RENTAL'))) LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_id` IS NULL) AS `pending_uploads`,
						(SELECT COUNT(`document_type_id`) FROM `trucks` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_verification_status`='PENDING') AS `pending_verification`,			
			(SELECT COUNT(`document_type_id`) FROM `trucks` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_verification_status`='REJECTED') AS `rejected`,
			(SELECT COUNT(`document_type_id`) FROM `trucks` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) BETWEEN 0 AND `document_type_expiry_alert`) AS `expiry_alert`,
			(SELECT COUNT(`document_type_id`) FROM `trucks` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0 AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) <=0) AS `expired`"));
		$response['total']=$res['total'];
		$response['pending_uploads']=$res['pending_uploads'];
		$response['pending_verification']=$res['pending_verification'];
		$response['rejected']=$res['rejected'];
		$response['expiry_alert']=$res['expiry_alert'];
		$response['expired']=$res['expired'];
		$status=true;
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

	}
	function truck_documents_reject($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['document_eid'])){
			$document_id=$Enc->safeurlde($param['document_eid']);
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `truck_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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


		ValidationChecker:
		if($dataValidation){
			$executionMessage='';
			$execution=true;	
			$time=time();

			$verify=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_verification_status`='REJECTED',`document_rejected_on`='$time',`document_rejected_by`='".USER_ID."' WHERE `document_id`='$document_id'");

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


	function truck_documents_verify($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['document_eid'])){
			$document_id=$Enc->safeurlde($param['document_eid']);
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `truck_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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



		ValidationChecker:
		if($dataValidation){
			$executionMessage='';
			$execution=true;	
			$time=time();

			$verify=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_verification_status`='VERIFIED',`document_verified_on`='$time',`document_verified_by`='".USER_ID."' WHERE `document_id`='$document_id'");

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
			$get_truck=mysqli_query($GLOBALS['con'],"SELECT `truck_id`, `truck_code` FROM `trucks` WHERE `truck_id`='$truck_id'");
			if(mysqli_num_rows($get_truck)==1){
				$truck_code=mysqli_fetch_assoc($get_truck)['truck_code'];
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

$remarks=(isset($param['remarks']))?senetize_input($param['remarks']):'';

		ValidationChecker:
		if($dataValidation){


			$executionMessage='';
			$execution=true;


			///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `truck_documents` ORDER BY `auto` DESC LIMIT 1");
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

			$create_history_of_old_docs=mysqli_query($GLOBALS['con'],"INSERT INTO `history_truck_documents` (`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_truck_id_fk`, `document_status`,`document_remarks`, `document_verification_status`, `document_verified_on`, `document_verified_by`, `document_rejected_on`, `document_rejected_by`, `document_added_on`, `document_added_by`, `document_deleted_on`, `docuemnt_deleted_by`) SELECT `document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_truck_id_fk`, `document_status`,`document_remarks`, `document_verification_status`, `document_verified_on`, `document_verified_by`, `document_rejected_on`, `document_rejected_by`, `document_added_on`, `document_added_by`, `document_deleted_on`, `docuemnt_deleted_by` FROM `truck_documents` WHERE `document_truck_id_fk`='$truck_id' AND `document_type_id_fk`='$document_type_id'");

			if(!$create_history_of_old_docs){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;				
			}


			//--insert document in table
			$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `truck_documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_truck_id_fk`, `document_status`,`document_remarks`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$truck_id','ACT','$remarks','PENDING','$time','".USER_ID."')");
			if(!$document_insert){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;
			}

			$delete_old_uploads=mysqli_query($GLOBALS['con'],"DELETE FROM `truck_documents` WHERE `document_type_id_fk`='$document_type_id' AND `document_truck_id_fk`='$truck_id' AND NOT `document_id`='$next_id'");
			if(!$delete_old_uploads){
				$executionMessage=SOMETHING_WENT_WROG.' step 04';
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

	function truck_documents($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['truck_eid']) && $param['truck_eid']!=""){
			$truck_id=$Enc->safeurlde($param['truck_eid']);
		}else{
			$InvalidDataMessage="Please provide truck eid";
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `document_type_id`,`document_type_name`,`document_type_expiry_option`,`document_type_expiry_alert`, `document_type_required_option` ,`document_id`, `document_file_name`, `document_expriry_date`,DATEDIFF(`document_expriry_date`,CURDATE()) AS `expiry_days_left`, `document_added_on`, `document_added_by`, `document_verification_status`, `document_verified_on`, `document_verified_by`,`document_remarks` FROM  `documents_types` LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk`  AND  `document_truck_id_fk`='$truck_id' WHERE `document_type_relates_to`='TRUCK' ORDER BY `document_type_name`");

			$list=[];
			$path=DOCUMENTS_ROOT.'documents/trucks/';
			while ($rows=mysqli_fetch_assoc($qEx)) {




				$doc_details=[];	
				if($rows['document_id']!=NULL){

					$added_user=$Users->user_basic_details($rows['document_added_by']);
					$added_by_user_code=$added_user['user_code'];
					$added_on_datetime=dateTimeFromDbTimestamp($rows['document_added_on']);	
					$verified_by_user_code='';
					$verified_on_datetime='';
					$expiry_alert=false;
					$is_expired=false;


					if($rows['document_verification_status']=='VERIFIED'){
						$verified_user=$Users->user_basic_details($rows['document_verified_by']);
						$verified_by_user_code=$verified_user['user_code'];
						$verified_on_datetime=dateTimeFromDbTimestamp($rows['document_verified_on']);
					}

					if($rows['document_type_expiry_option']=='T'){
						if($rows['expiry_days_left']<=$rows['document_type_expiry_alert']){
							$expiry_alert=true;
						}
						if($rows['expiry_days_left']<=0){
							$is_expired=true;
						}
					}				
					$doc_details=[

						'name'=>$rows['document_file_name'],
						'eid'=>$Enc->safeurlen($rows['document_id']),
						'expiry_date'=>dateFromDbToFormat($rows['document_expriry_date']),
						'expiry_days_left'=>($rows['expiry_days_left']!=null)?$rows['expiry_days_left']:'',
						'expiry_alert'=>$expiry_alert,
						'is_expired'=>$is_expired,
						'file_path'=>$path.$rows['document_file_name'],
						'verification_status'=>$rows['document_verification_status'],
						'added_by_user_code'=>$added_by_user_code,
						'added_on_datetime'=>$added_on_datetime,
						'verified_by_user_code'=>$verified_by_user_code,
						'verified_on_datetime'=>$verified_on_datetime,
						'remarks'=>$rows['document_remarks']
					];

				}



				array_push($list,[
					'type_id' => $rows['document_type_id'], 
					'type_eid' => $Enc->safeurlen($rows['document_type_id']), 
					'type_name' =>$rows['document_type_name'], 
					'type_expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
					'type_required_option' =>($rows['document_type_required_option']=='T')?true:false,
					'is_uploaded'=>($rows['document_id']!=NULL)?true:false,
					'document_details'=>$doc_details 
				]);

/*
	
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
						}*/


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
				$r['message']=$message.json_encode($param);
				$r['response']=$response;
				return $r;	
			}

			function all_truck_documents_list($param){
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
				include_once APPROOT.'/models/masters/Users.php';
				$Users=new Users;

				$InvalidDataMessage="";
				$dataValidation=true;

				ValidationChecker:
				if($dataValidation){
					$q="SELECT `truck_id`,`truck_code`,`document_type_id`,`document_type_name`,`document_type_expiry_option`,`document_type_expiry_alert`, `document_type_required_option` ,`document_id`, `document_file_name`, `document_expriry_date`,DATEDIFF(`document_expriry_date`,CURDATE()) AS `expiry_days_left`, `document_added_on`, `document_added_by`, `document_verification_status`, `document_verified_on`, `document_verified_by`,`document_remarks` FROM `trucks` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `truck_documents` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` AND `document_truck_id_fk`=`truck_id` WHERE `truck_status`='ACT' AND `document_type_relates_to`='TRUCK' AND NOT `truck_id`=0";

//-----------apply filter
					if(isset($param['truck_id']) && $param['truck_id']!=''){
							$truck_id=senetize_input($param['truck_id']);
								$q.=" AND `truck_id`='$truck_id'";
								
						}

					if(isset($param['document_type_id']) && $param['document_type_id']!=''){
							$document_type_id=senetize_input($param['document_type_id']);
								$q.=" AND `document_type_id`='$document_type_id'";
								
						}
					if(isset($param['is_uploaded']) && $param['is_uploaded']!=''){
						$is_uploaded=to_boolean($param['is_uploaded']);
						if($is_uploaded==false){
							$q.=" AND `document_id` IS NULL";
						}	
					}

					if(isset($param['is_required']) && $param['is_required']!=''){
						$is_required=to_boolean($param['is_required']);
						if($is_required==false){
							$q.=" AND `document_type_required_option`='F'";
						}else{
							$q.=" AND `document_type_required_option`='T'";
						}	
					}

					if(isset($param['verification_status']) && $param['verification_status']!=''){

						$verification_status=senetize_input($param['verification_status']);
						$q.=" AND `document_verification_status`='$verification_status'";				
					}

					if(isset($param['expiry_alert']) && $param['expiry_alert']!=''){

						if(to_boolean($param['expiry_alert'])){
							$q.=" AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) BETWEEN 0 AND `document_type_expiry_alert`";
						}				
					}

					if(isset($param['is_expired']) && $param['is_expired']!=''){
						if(to_boolean($param['is_expired'])){
							$push_entry=false;
							$q.=" AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) <=0";
						}				
					}
//----------/apply filter



					$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
					$q .="  ORDER BY `truck_code`,`document_type_name`  limit $from, $batch";
					$qEx=mysqli_query($GLOBALS['con'],$q);

					$list=[];
					$counter=$from;
					$list=[];
					$list=[];$path=DOCUMENTS_ROOT.'documents/trucks/';
					while ($rows=mysqli_fetch_assoc($qEx)) {




						$doc_details=[];	
						if($rows['document_id']!=NULL){

							$added_user=$Users->user_basic_details($rows['document_added_by']);
							$added_by_user_code=$added_user['user_code'];
							$added_on_datetime=dateTimeFromDbTimestamp($rows['document_added_on']);	
							$verified_by_user_code='';
							$verified_on_datetime='';
							$expiry_alert=false;
							$is_expired=false;


							if($rows['document_verification_status']=='VERIFIED'){
								$verified_user=$Users->user_basic_details($rows['document_verified_by']);
								$verified_by_user_code=$verified_user['user_code'];
								$verified_on_datetime=dateTimeFromDbTimestamp($rows['document_verified_on']);
							}

							if($rows['document_type_expiry_option']=='T'){
								if($rows['expiry_days_left']<=$rows['document_type_expiry_alert']){
									$expiry_alert=true;
								}
								if($rows['expiry_days_left']<=0){
									$is_expired=true;
								}
							}				
							$doc_details=[

								'name'=>$rows['document_file_name'],
								'eid'=>$Enc->safeurlen($rows['document_id']),
								'expiry_date'=>dateFromDbToFormat($rows['document_expriry_date']),
								'expiry_days_left'=>($rows['expiry_days_left']!=null)?$rows['expiry_days_left']:'',
								'expiry_alert'=>$expiry_alert,
								'is_expired'=>$is_expired,
								'file_path'=>$path.$rows['document_file_name'],
								'verification_status'=>$rows['document_verification_status'],
								'added_by_user_code'=>$added_by_user_code,
								'added_on_datetime'=>$added_on_datetime,
								'verified_by_user_code'=>$verified_by_user_code,
								'verified_on_datetime'=>$verified_on_datetime,
								'remarks'=>$rows['document_remarks']
							];

						}



						array_push($list,[
						    'sr_no'=>++$counter,
							'truck_id'=>$rows['truck_id'],
							'truck_eid'=>$Enc->safeurlen($rows['truck_id']),
							'truck_code'=>$rows['truck_code'],
							'type_id' => $rows['document_type_id'], 
							'type_eid' => $Enc->safeurlen($rows['document_type_id']), 
							'type_name' =>$rows['document_type_name'], 
							'type_expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
							'type_required_option' =>($rows['document_type_required_option']=='T')?true:false,
							'is_uploaded'=>($rows['document_id']!=NULL)?true:false,
							'document_details'=>$doc_details 
						]);


					}

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

				}else{
					$message=$InvalidDataMessage;
				}

				$r=[];
				$r['status']=$status;
				$r['message']=$param;
				$r['response']=$response;
				return $r;	
			}



	/*

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
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `truck_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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

				$verify=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_verification_status`='VERIFIED',`document_verified_on`='$time',`document_verified_by`='".USER_ID."' WHERE `document_id`='$document_id'");

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
				$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `truck_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`truck_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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

					$verify=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_verification_status`='REJECTED',`document_rejected_on`='$time',`document_rejected_by`='".USER_ID."' WHERE `document_id`='$document_id'");

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

			function truck_documents_upload($param,$param_file){
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
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `truck_documents` ORDER BY `auto` DESC LIMIT 1");
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

					$delete_old_docs=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_status`='DEL',`document_deleted_on`='$time',`docuemnt_deleted_by`='".USER_ID."' WHERE `document_status`='ACT' AND `document_driver_id_fk`='$driver_id' AND `document_type_id_fk`='$document_type_id'");

					if(!$delete_old_docs){
						$executionMessage=SOMETHING_WENT_WROG.' step 02';
						$execution=false;
						goto executionChecker;				
					}


			//--insert document in table
					$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `truck_documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_driver_id_fk`, `document_status`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$driver_id','ACT','PENDING','$time','".USER_ID."')");
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
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `truck_documents` ORDER BY `auto` DESC LIMIT 1");
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


					$delete_old_docs=mysqli_query($GLOBALS['con'],"UPDATE `truck_documents` SET `document_status`='DEL',`document_deleted_on`='$time',`docuemnt_deleted_by`='".USER_ID."' WHERE `document_status`='ACT' AND `document_truck_id_fk`='$truck_id' AND `document_type_id_fk`='$document_type_id'");

					if(!$delete_old_docs){
						$executionMessage=SOMETHING_WENT_WROG.' step 02';
						$execution=false;
						goto executionChecker;				
					}


			//--insert document in table

					$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `truck_documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_truck_id_fk`, `document_status`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$truck_id','ACT','PENDING','$time','".USER_ID."')");
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
			function truck_documents($param){
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
					$truck_documents=$this->documents_list(array_merge(array('driver_id'=>$rows['driver_id'],'document_type_relates_to'=>'DRIVER'),$param));
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
			}*/
		}
		?>