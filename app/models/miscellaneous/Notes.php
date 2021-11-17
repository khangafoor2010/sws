<?php
/**
 *
 */
class Notes
{


	function notes_add_new($param){
		$status=false;
		$message=null;
		$response=null;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['reference_type_id'])){
				//------check if all payments of trip are unpaid
			$reference_type_id=senetize_input($param['reference_type_id']);
			$is_valid_reference_type_id=mysqli_query($GLOBALS['con'],"SELECT `note_reference_type_id` FROM `note_reference_types` WHERE `note_reference_type_id`='".$reference_type_id."' AND `note_references_type_status`='ACT'");
			if(mysqli_num_rows($is_valid_reference_type_id)!=1){
				$InvalidDataMessage="Invalid reference type";
				$dataValidation=false;
				goto ValidationChecker;				
			}


		}else{
			$InvalidDataMessage="Please reference type id";
			$dataValidation=false;
			goto ValidationChecker;
		}



		$document_type_id=null;

		if($reference_type_id=='DRIVER-DOCUMENT' || $reference_type_id=='TRUCK-DOCUMENT'){

			if(isset($param['document_type_eid']) && $param['document_type_eid']!=""){
				$document_type_id=$Enc->safeurlde($param['document_type_eid']);
				include_once APPROOT.'/models/masters/DocumentTypes.php';
				$DocumentTypes=new DocumentTypes;
				if(!$DocumentTypes->isValidId($document_type_id)){
					$InvalidDataMessage="Please provide  valid document type eid";
					$dataValidation=false;
					goto ValidationChecker;					
				}			
			}else{
				$InvalidDataMessage="Please provide document type eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}

		if(isset($param['reference_eid'])){

			$reference_id=$Enc->safeurlde($param['reference_eid']);
			


			//----------validate reference id

			switch ($reference_type_id) {
				case 'TRIP':
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `trip_id` FROM `trips` WHERE `trip_id`='$reference_id'"))!=1){
					$InvalidDataMessage="Invalid reference id";
					$dataValidation=false;
					goto ValidationChecker;						
				}
				break;
				
				default:
					# code...
				break;
			}




		}else{
			$InvalidDataMessage="Please reference id";
			$dataValidation=false;
			goto ValidationChecker;
		}



		if(isset($param['text']) && $param['text']!=""){
				//------check if all payments of trip are unpaid
			$text=senetize_input($param['text']);
		}else{
			$InvalidDataMessage="Please provide text";
			$dataValidation=false;
			goto ValidationChecker;
		}

		ValidationChecker:
		if($dataValidation){
			$USERID=USER_ID;
			$time=time();

 					///-----Generate New Unique Id
			$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `note_id` FROM `notes` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['note_id'])+1:1001;
					///-----//Generate New Unique Id


			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `notes`(`note_id`, `note_reference_type_id_fk`, `note_reference_id_fk`,`note_document_type_id_fk`, `note_text`, `note_status`, `note_added_by`, `note_added_on`) VALUES ('$next_id','$reference_type_id','$reference_id','$document_type_id','$text','ACT','$USERID','$time')");
			if($insert){
				$status=true;
				$message="Added successfuly";
			}else{
				$message=SOMETHING_WENT_WROG;
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



	function notes_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['reference_type_id'])){
				//------check if all payments of trip are unpaid
			$reference_type_id=senetize_input($param['reference_type_id']);
		}else{
			$InvalidDataMessage="Please reference type id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['reference_eid'])){
				//------check if all payments of trip are unpaid
			$reference_id=$Enc->safeurlde($param['reference_eid']);
		}else{
			$InvalidDataMessage="Please reference id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$document_type_id="";
		if($reference_type_id=='DRIVER-DOCUMENT' || $reference_type_id=='TRUCK-DOCUMENT'){

			if(isset($param['document_type_eid']) && $param['document_type_eid']!=""){
				$document_type_id=$Enc->safeurlde($param['document_type_eid']);
				include_once APPROOT.'/models/masters/DocumentTypes.php';
				$DocumentTypes=new DocumentTypes;
				if(!$DocumentTypes->isValidId($document_type_id)){
					$InvalidDataMessage="Please provide  valid document type eid";
					$dataValidation=false;
					goto ValidationChecker;					
				}			
			}else{
				$InvalidDataMessage="Please provide document type eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

		}



		ValidationChecker:
		if($dataValidation){
			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;

			$q="SELECT   `note_id`,`note_text`, `note_added_by`, `note_added_on`,`note_high_priority_status` FROM `notes` WHERE `note_reference_type_id_fk`='$reference_type_id' AND `note_reference_id_fk`='$reference_id' AND `note_status`='ACT'";

			if(isset($param['last_note_eid']) && $param['last_note_eid']!=""){
				$last_note_id_send=$Enc->safeurlde($param['last_note_eid']);
				$q.=" AND `note_id`>'$last_note_id_send'";
			}

			if($document_type_id!=""){
				$q.=" AND `note_document_type_id_fk`='$document_type_id'";
			}

			$get=mysqli_query($GLOBALS['con'],$q);
			$list=[];
			$last_note_eid='';
			while ($rows=mysqli_fetch_assoc($get)) {
				$row=[];
				$last_note_eid=$Enc->safeurlen($rows['note_id']);
				$row['eid']=$Enc->safeurlen($rows['note_id']);
				$row['text']=$rows['note_text'];
				$row['high_priority_status']=$rows['note_high_priority_status'];

				$added_user=$Users->user_basic_details($rows['note_added_by']);
				$row['added_by_user_code']=$added_user['user_code'];
				$row['added_on_datetime']=dateTimeFromDbTimestamp($rows['note_added_on']);
				$row['user_type']=($rows['note_added_by']==USER_ID)?'SELF':'OTHER';
				array_push($list,$row);
			}
			$response=[];
			$response['list']=$list;
			$response['last_note_eid']=$last_note_eid;
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
		$r['message']=$document_type_id.' - '.$reference_id;
		$r['response']=$response;
		return $r;	
	}


	function toggle_high_priority_status($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['note_eid'])){
				//------check if all payments of trip are unpaid
			$note_id=$Enc->safeurlde($param['note_eid']);
		}else{
			$InvalidDataMessage="Please provide note eid";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['high_priority_status'])){
				//------check if all payments of trip are unpaid
			$high_priority_status=senetize_input($param['high_priority_status']);
			if($high_priority_status=='OFF' || $high_priority_status=="ON"){

			}else{
				$InvalidDataMessage="Please provide highlight status as OFF/ON";
				$dataValidation=false;
				goto ValidationChecker;		
			}
		}else{
			$InvalidDataMessage="Please provide highlight status";
			$dataValidation=false;
			goto ValidationChecker;
		}



		ValidationChecker:
		if($dataValidation){
			$USERID=USER_ID;
			$time=time();
			$update=mysqli_query($GLOBALS['con'],"UPDATE  `notes` SET `note_high_priority_status`='$high_priority_status',`note_updated_on`='$time',`note_updated_by`='$USERID'  WHERE `note_id`='$note_id' AND `note_status`='ACT' AND `note_added_by`='$USERID'");

			if($update){
				$status=true;
			}else{
				$message=SOMETHING_WENT_WROG;
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

	function notes_delete($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['note_eid'])){
				//------check if all payments of trip are unpaid
			$note_id=$Enc->safeurlde($param['note_eid']);
		}else{
			$InvalidDataMessage="Please provide note eid";
			$dataValidation=false;
			goto ValidationChecker;
		}


		ValidationChecker:
		if($dataValidation){
			$USERID=USER_ID;
			$time=time();
			$update=mysqli_query($GLOBALS['con'],"UPDATE  `notes` SET `note_status`='DEL',`note_deleted_on`='$time',`note_deleted_by`='$USERID'  WHERE `note_id`='$note_id' AND `note_status`='ACT' AND `note_added_by`='$USERID'");

			if($update){
				$status=true;
			}else{
				$message=SOMETHING_WENT_WROG;
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