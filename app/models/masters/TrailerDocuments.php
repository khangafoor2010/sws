<?php
class TrailerDocuments
{
	function trailer_documents_quick_totals($param){
		$status=false;
		$message=null;
		$response=null;
		$res=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT 
			(SELECT COUNT(`trailer_id`) FROM `trailers` join `documents_types` ON `documents_types`.`document_type_relates_to`='TRAILER' AND `document_type_status`='ACT' AND `trailer_status`='ACT') as `total`,
			(SELECT COUNT(`document_type_id`) FROM `trailers` join `documents_types` ON `document_type_status`='ACT' AND `document_type_required_option`='T' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0 AND `document_id` IS NULL) AS `pending_uploads`,
			(SELECT COUNT(`document_type_id`) FROM `trailers` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0 AND `document_verification_status`='PENDING') AS `pending_verification`,			
			(SELECT COUNT(`document_type_id`) FROM `trailers` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0 AND `document_verification_status`='REJECTED') AS `rejected`,
			(SELECT COUNT(`document_type_id`) FROM `trailers` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0 AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) BETWEEN 0 AND `document_type_expiry_alert`) AS `expiry_alert`,
			(SELECT COUNT(`document_type_id`) FROM `trailers` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0 AND `document_type_expiry_option`='T' AND DATEDIFF(`document_expriry_date`,CURDATE()) <=0) AS `expired`"));
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
	function trailer_documents_reject($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['document_eid'])){
			$document_id=$Enc->safeurlde($param['document_eid']);
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `trailer_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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

			$verify=mysqli_query($GLOBALS['con'],"UPDATE `trailer_documents` SET `document_verification_status`='REJECTED',`document_rejected_on`='$time',`document_rejected_by`='".USER_ID."' WHERE `document_id`='$document_id'");

			if(!$verify){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;				
			}
			executionChecker:
			if($execution)
			{
				$status=true;
				$message="Rejected Successfuly";
			}else
			{
				$message=$executionMessage;
			}
		}else
		{
			$message=$InvalidDataMessage;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function trailer_documents_verify($param)
	{
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['document_eid']))
		{
			$document_id=$Enc->safeurlde($param['document_eid']);
			$validate_document_id=mysqli_query($GLOBALS['con'],"SELECT `document_id`,`document_type_relates_to` FROM `trailer_documents` LEFT JOIN `documents_types` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` WHERE `document_id`='$document_id' AND `document_verification_status`='PENDING'");
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
			$verify=mysqli_query($GLOBALS['con'],"UPDATE `trailer_documents` SET `document_verification_status`='VERIFIED',`document_verified_on`='$time',`document_verified_by`='".USER_ID."' WHERE `document_id`='$document_id'");

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
		}else
		{
			$message=$InvalidDataMessage;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function trailer_documents_upload($param,$param_file){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(!in_array('P0172', USER_PRIV)){
			$InvalidDataMessage=NOT_AUTHORIZED_MSG;
			$dataValidation=false;
			goto ValidationChecker;				
		}
		if(isset($param['trailer_eid'])){
			$trailer_id=$Enc->safeurlde($param['trailer_eid']);
			$get_trailer=mysqli_query($GLOBALS['con'],"SELECT `trailer_id`, `trailer_code` FROM `trailers` WHERE `trailer_id`='$trailer_id'");
			if(mysqli_num_rows($get_trailer)==1){
				$trailer_code=mysqli_fetch_assoc($get_trailer)['trailer_code'];
			}else{
				$InvalidDataMessage="Invalid trailer eid";
				$dataValidation=false;
				goto ValidationChecker;				
			}

		}else{
			$InvalidDataMessage="Please provide trailer eid";
			$dataValidation=false;
			goto ValidationChecker;	 			
		}

		if(isset($param['document_type_eid'])){
			$document_type_id=$Enc->safeurlde($param['document_type_eid']);

			//----------check if docucment type id belongs to DRIVER
			$get_document_type_details=mysqli_query($GLOBALS['con'],"SELECT  `document_type_id`,`document_type_name`, `document_type_expiry_option` FROM `documents_types` WHERE `document_type_id`='$document_type_id' AND `document_type_relates_to`='TRAILER'");
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
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `document_id` FROM `trailer_documents` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['document_id'])+1:1;
			///-----//Generate New Unique Id

			//--upload image 	
			$send_param=array(
				'path'=>'documents/trailers'
			);
			$new_file_name=$trailer_code.'-'.to_alphanumeric($document_type_name,'-').'-'.date('ymdHi').'-'.$next_id.'.'.$fileType;

			$upload_document=upload_document($send_param,array('tmp_name'=>$param_file["tmp_name"],'type'=>$param_file["type"],'name'=>$new_file_name));

			if(!$upload_document){
				$executionMessage=SOMETHING_WENT_WROG.' step 01'.$upload_document['message'];
				$execution=false;
				goto executionChecker;
			}

			$time=time();

			$create_history_of_old_docs=mysqli_query($GLOBALS['con'],"INSERT INTO `history_trailer_documents` (`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_trailer_id_fk`, `document_status`, `document_verification_status`, `document_verified_on`, `document_verified_by`, `document_rejected_on`, `document_rejected_by`, `document_added_on`, `document_added_by`, `document_deleted_on`, `docuemnt_deleted_by`) SELECT `document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_trailer_id_fk`, `document_status`, `document_verification_status`, `document_verified_on`, `document_verified_by`, `document_rejected_on`, `document_rejected_by`, `document_added_on`, `document_added_by`, `document_deleted_on`, `docuemnt_deleted_by` FROM `trailer_documents` WHERE `document_trailer_id_fk`='$trailer_id' AND `document_type_id_fk`='$document_type_id'");

			if(!$create_history_of_old_docs){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;				
			}

			//--insert document in table
			$document_insert=mysqli_query($GLOBALS['con'],"INSERT INTO `trailer_documents`(`document_id`, `document_type_id_fk`, `document_file_name`, `document_expriry_date`, `document_trailer_id_fk`, `document_status`,`document_verification_status`, `document_added_on`, `document_added_by`) VALUES ('$next_id','$document_type_id','$new_file_name','$expiry_date','$trailer_id','ACT','PENDING','$time','".USER_ID."')");
			if(!$document_insert){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;
			}

			$delete_old_uploads=mysqli_query($GLOBALS['con'],"DELETE FROM `trailer_documents` WHERE `document_type_id_fk`='$document_type_id' AND `document_trailer_id_fk`='$trailer_id' AND NOT `document_id`='$next_id'");
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

	function trailer_documents($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['trailer_eid']) && $param['trailer_eid']!="")
		{
			$trailer_id=$Enc->safeurlde($param['trailer_eid']);
		}else{
			$InvalidDataMessage="Please provide trailer eid";
			$dataValidation=false;
			goto ValidationChecker;
		}

		ValidationChecker:
		if($dataValidation)
		{
			$qEx=mysqli_query($GLOBALS['con'],"SELECT `document_type_id`,`document_type_name`,`document_type_expiry_option`,`document_type_expiry_alert`, `document_type_required_option` ,`document_id`, `document_file_name`, `document_expriry_date`,DATEDIFF(`document_expriry_date`,CURDATE()) AS `expiry_days_left`, `document_added_on`, `document_added_by`, `document_verification_status`, `document_verified_on`, `document_verified_by` FROM  `documents_types` LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk`  AND  `document_trailer_id_fk`='$trailer_id' WHERE `document_type_relates_to`='TRAILER' ORDER BY `document_type_name`");

			$list=[];
			$path=DOCUMENTS_ROOT.'documents/trailers/';
			while ($rows=mysqli_fetch_assoc($qEx)) 
			{
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
						'verified_on_datetime'=>$verified_on_datetime
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

			function all_trailer_documents_list($param){
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
					$q="SELECT `trailer_id`,`trailer_code`,`document_type_id`,`document_type_name`,`document_type_expiry_option`,`document_type_expiry_alert`, `document_type_required_option` ,`document_id`, `document_file_name`, `document_expriry_date`,DATEDIFF(`document_expriry_date`,CURDATE()) AS `expiry_days_left`, `document_added_on`, `document_added_by`, `document_verification_status`, `document_verified_on`, `document_verified_by` FROM `trailers` join `documents_types` ON `document_type_status`='ACT' LEFT JOIN `trailer_documents` ON `documents_types`.`document_type_id`=`trailer_documents`.`document_type_id_fk` AND `document_trailer_id_fk`=`trailer_id` WHERE `trailer_status`='ACT' AND `document_type_relates_to`='TRAILER' AND NOT `trailer_id`=0";

//-----------apply filter
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
					$q .="  ORDER BY `trailer_code`,`document_type_name`  limit $from, $batch";
					$qEx=mysqli_query($GLOBALS['con'],$q);

					$list=[];
					$counter=$from;
					$list=[];
					$list=[];$path=DOCUMENTS_ROOT.'documents/trailers/';
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
								'verified_on_datetime'=>$verified_on_datetime
							];
						}

						array_push($list,[
							'trailer_id'=>$rows['trailer_id'],
							'trailer_eid'=>$Enc->safeurlen($rows['trailer_id']),
							'trailer_code'=>$rows['trailer_code'],
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
				$r['message']=$message;
				$r['response']=$response;
				return $r;	
			}
		}
		?>