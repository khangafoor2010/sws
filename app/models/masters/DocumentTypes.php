<?php
/**
 *
 */
class DocumentTypes
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `document_type_id` from `documents_types` WHERE `document_type_id`='$id' AND `document_type_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}


	function document_types_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q="SELECT `document_type_id`, `document_type_relates_to`, `document_type_name`, `document_type_expiry_option`, `document_type_required_option`, `document_type_expiry_alert`, `document_type_status` FROM `documents_types` WHERE `document_type_status`='ACT'";
		if(isset($param['relates_to'])){
			$q.=" AND `document_type_relates_to`='".senetize_input($param['relates_to'])."'";
		}
		$get=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($get)) {
			array_push($list,
				array(
					'id' => $rows['document_type_id'], 
					'eid' => $Enc->safeurlen($rows['document_type_id']), 
					'name' => $rows['document_type_name'], 
					'relates_to' =>$rows['document_type_relates_to'], 
					'expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
					'is_required' =>($rows['document_type_required_option']=='T')?true:false,
					'expiry_alert_days' =>$rows['document_type_expiry_alert'], 
				)
			);

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

	function document_types_add_new($param){
		$status=false;
		$message=null;
		$response=null;
			//-----data validation starts
 			///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;




		if(isset($param['relates_to']) && $param['relates_to']!=""){
				//------check if all payments of trip are unpaid
			$relates_to=senetize_input($param['relates_to']);
		}else{
			$InvalidDataMessage="Please provide relates_to";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
				//------check if all payments of trip are unpaid
			$name=senetize_input($param['name']);

		//----check duplicay of name
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `documents_types` WHERE  `document_type_status`='ACT' AND `document_type_name`='$name' AND  `document_type_relates_to`='$relates_to'"))>0){
				$InvalidDataMessage="Name already exists";
				$dataValidation=false;
				goto ValidationChecker;		
			}	


		}else{
			$InvalidDataMessage="Please provide name";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$is_required='T';//by defauly set document is_required as true
		if(isset($param['is_required']) && to_boolean($param['is_required'])==false){
			$is_required='F';
		}
		$expiry_option='T';//by defauly set document expriy_option as true
		if(isset($param['expiry_option']) && to_boolean($param['expiry_option'])==false){
			$expiry_option='F';
		}

		if($expiry_option=='T'){
			if(isset($param['expiry_time']) && $param['expiry_time']!=""){
				if(is_numeric($param['expiry_time'])){
					$expiry_time=senetize_input($param['expiry_time']);
				}
			}else{
				$InvalidDataMessage="Please provide expiry time";
				$dataValidation=false;
				goto ValidationChecker;		
			}
		}else{
			$expiry_time='';
		}
		

		ValidationChecker:
		if($dataValidation){

			$USERID=USER_ID;
			$time=time();
			$executionMessage='';
			$execution=true;			


			///----------insert ticket id entry
			$last_id=mysqli_query($GLOBALS['con'],"SELECT `document_type_id` FROM `documents_types` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($last_id)==1)?(mysqli_fetch_assoc($last_id)['document_type_id']):'1';

			$next_id++;
			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `documents_types`(`document_type_id`,  `document_type_relates_to`, `document_type_name`, `document_type_expiry_option`, `document_type_required_option`, `document_type_expiry_alert`, `document_type_status`, `document_type_added_on`, `document_type_added_by`) VALUES ('$next_id','$relates_to','$name','$expiry_option','$is_required','$expiry_time','ACT','$time','$USERID')");
			if(!$insert){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;	
			}
			executionChecker:
			if($execution){
				$status=true;
				$message="Added successfuly";
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

	function document_types_details($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$InvalidDataMessage="";
		$dataValidation=true;

		if(isset($param['eid']) && $param['eid']!=''){
			$id=$Enc->safeurlde($param['eid']);
		}else{
			$InvalidDataMessage="Please send eid";
			$dataValidation=false;
			goto ValidationChecker;		
		}

		ValidationChecker:
		if($dataValidation){

			$qEx=mysqli_query($GLOBALS['con'],"SELECT `document_type_id`, `document_type_relates_to`, `document_type_name`, `document_type_expiry_option`, `document_type_required_option`, `document_type_expiry_alert`, `document_type_status` FROM `documents_types` WHERE `document_type_status`='ACT' AND `document_type_id`='$id'");

			if(mysqli_num_rows($qEx)==1) {
				$status=true;
				$rows=mysqli_fetch_assoc($qEx);
				
				$response['details']=array(
					'eid' => $Enc->safeurlen($rows['document_type_id']), 
					'name' => $rows['document_type_name'], 
					'relates_to' =>$rows['document_type_relates_to'], 
					'expiry_option' =>($rows['document_type_expiry_option']=='T')?true:false, 
					'is_required' =>($rows['document_type_required_option']=='T')?true:false,
					'expiry_alert_days' =>$rows['document_type_expiry_alert'], 
				);

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
	function document_types_update($param){
		$status=false;
		$message=null;
		$response=null;
			//-----data validation starts
 			///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		if(isset($param['update_eid']) && $param['update_eid']!=""){
				//------check if all payments of trip are unpaid
			$update_id=$Enc->safeurlde($param['update_eid']);
		}else{
			$InvalidDataMessage="Please provide relates_to";
			$dataValidation=false;
			goto ValidationChecker;
		}



//----------check if document type is editable or not


			$get_dynamic_type_q=mysqli_query($GLOBALS['con'],"SELECT  `document_static_name` FROM `documents_types` WHERE `document_type_id`='$update_id'");
			$get_dynamic_type=mysqli_fetch_assoc($get_dynamic_type_q)['document_static_name'];

		if($get_dynamic_type!=''){
			$InvalidDataMessage="You are not allowed to update these this entry";
			$dataValidation=false;
			goto ValidationChecker;
		}



		if(isset($param['relates_to']) && $param['relates_to']!=""){
				//------check if all payments of trip are unpaid
			$relates_to=senetize_input($param['relates_to']);
		}else{
			$InvalidDataMessage="Please provide relates_to";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['name']) && $param['name']!=""){
				//------check if all payments of trip are unpaid
			$name=senetize_input($param['name']);

		//----check duplicay of name
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `auto` FROM `documents_types` WHERE  `document_type_status`='ACT' AND `document_type_name`='$name' AND  `document_type_relates_to`='$relates_to' AND NOT `document_type_id`='$update_id'"))>0){
				$InvalidDataMessage="Name already exists";
				$dataValidation=false;
				goto ValidationChecker;		
			}	


		}else{
			$InvalidDataMessage="Please provide name";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$is_required='T';//by defauly set document is_required as true
		if(isset($param['is_required']) && to_boolean($param['is_required'])==false){
			$is_required='F';
		}
		$expiry_option='T';//by defauly set document expriy_option as true
		if(isset($param['expiry_option']) && to_boolean($param['expiry_option'])==false){
			$expiry_option='F';
		}

		if($expiry_option=='T'){
			if(isset($param['expiry_time']) && $param['expiry_time']!=""){
				if(is_numeric($param['expiry_time'])){
					$expiry_time=senetize_input($param['expiry_time']);
				}
			}else{
				$InvalidDataMessage="Please provide expiry time";
				$dataValidation=false;
				goto ValidationChecker;		
			}
		}else{
			$expiry_time='';
		}
		

		ValidationChecker:
		if($dataValidation){

			$USERID=USER_ID;
			$time=time();
			$executionMessage='';
			$execution=true;			


			///----------insert ticket id entry
			$last_id=mysqli_query($GLOBALS['con'],"SELECT `document_type_id` FROM `documents_types` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($last_id)==1)?(mysqli_fetch_assoc($last_id)['document_type_id']):'1';

			$next_id++;
			$insert=mysqli_query($GLOBALS['con'],"UPDATE `documents_types` SET `document_type_name`='$name', `document_type_expiry_option`='$expiry_option', `document_type_required_option`='$is_required', `document_type_expiry_alert`='$expiry_time' ,`document_type_updated_on`='$time',`document_type_updated_by`='$USERID' WHERE `document_type_id`='$update_id' AND `document_static_name`=''");
			if(!$insert){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;	
			}
			executionChecker:
			if($execution){
				$status=true;
				$message="Updated successfuly";
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

	function document_types_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0154', USER_PRIV)){


			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['delete_eid'])){
				$delete_id=$Enc->safeurlde($param['delete_eid']);
			}else{
				$InvalidDataMessage="Please provide delete eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

			$get_dynamic_type_q=mysqli_query($GLOBALS['con'],"SELECT  `document_static_name` FROM `documents_types` WHERE `document_type_id`='$delete_id'");
			$get_dynamic_type=mysqli_fetch_assoc($get_dynamic_type_q)['document_static_name'];

		if($get_dynamic_type!=''){
			$InvalidDataMessage="You are not allowed to deleted these this entry";
			$dataValidation=false;
			goto ValidationChecker;
		}

			ValidationChecker:
			if($dataValidation){
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `documents_types` SET `document_type_status`='DLT',`document_type_deleted_on`='".time()."',`document_type_deleted_by`='".USER_ID."' WHERE `document_type_id`='$delete_id' AND `document_static_name`=''");
				if($delete){
					$status=true;
					$message="Deleted Successfuly";	
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
}
?>