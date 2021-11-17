<?php
/**
 *
 */
class Tickets
{

	function tickets_add_action($param){
		$status=false;
		$message=null;
		$response=null;
			//-----data validation starts
 			///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		if(isset($param['text']) && $param['text']!=""){
				//------check if all payments of trip are unpaid
			$text=senetize_input($param['text']);
		}else{
			$InvalidDataMessage="Please provide text";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['stage_id']) && $param['stage_id']!=""){
				//------check if all payments of trip are unpaid
			
			include_once APPROOT.'/models/task-management/TicketStages.php';
			$TicketStages=new TicketStages;
			if($TicketStages->isValidId($param['stage_id'])){
				$stage_id=senetize_input($param['stage_id']);
			}else{
				$InvalidDataMessage="Invalid stage id";
				$dataValidation=false;
			}

		}else{
			$InvalidDataMessage="Please stage id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['ticket_eid']) && $param['ticket_eid']!=""){
				//------check if all payments of trip are unpaid
			$ticket_id=$Enc->safeurlde($param['ticket_eid']);


//---fetch ticket details
			$ticket_details_q=mysqli_query($GLOBALS['con'],"SELECT `ticket_added_by`,`ticket_stage_id_fk`,`details`.`ticket_detail_id` AS  `detail_id`, `details`.`ticket_detail_priority_id_fk` AS  `priority_id`, `details`.`ticket_detail_text` AS  `text` FROM `tms_tickets` LEFT JOIN `tms_tickets_details` AS `details` ON `tms_tickets`.`ticket_id`=`details`.`ticket_detail_ticket_id_fk` WHERE `ticket_id`='$ticket_id' ORDER BY `details`.`auto` DESC LIMIT 1");
			if(mysqli_num_rows($ticket_details_q)==1){
				$ticket_dtl=mysqli_fetch_assoc($ticket_details_q);
				$ticket_added_by=$ticket_dtl['ticket_added_by'];

				///---------check if old stage has been changed
				$is_stage_changed=($stage_id!=$ticket_dtl['ticket_stage_id_fk'])?true:false;
				///---------/check if old stage has been changed

//------check if stage id send is equal to CLOSED
//------allow updation to stage id CLOSED only to the user who generated the ticket
				if($stage_id=='CLOSED' AND $ticket_added_by!=USER_ID){
					$InvalidDataMessage="You can't set the status as CLOSED";
					$dataValidation=false;
					goto ValidationChecker;
				}



				
			}else{
				$InvalidDataMessage="Please provide ticket eid".$ticket_id;
				$dataValidation=false;
				goto ValidationChecker;
			}

		}else{
			$InvalidDataMessage="Please provide ticket eid";
			$dataValidation=false;
			goto ValidationChecker;
		}



		ValidationChecker:
		if($dataValidation){

			$USERID=USER_ID;
			$time=time();
			$executionMessage='';
			$execution=true;			


			///----------insert ticket id entry
			$last_id=mysqli_query($GLOBALS['con'],"SELECT `action_id` FROM `tms_tickets_actions` ORDER BY `auto` DESC LIMIT 1");
			$next_id=(mysqli_num_rows($last_id)==1)?(mysqli_fetch_assoc($last_id)['action_id']):'1';

			$next_id++;
			$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_actions`(`action_id`, `action_text`, `action_ticket_id_fk`, `action_stage_id_fk`, `action_added_on`, `action_added_by`) VALUES ('$next_id','$text','$ticket_id','$stage_id','$time','$USERID')");
			if(!$insert){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;	
			}

			//--update curret stage id in tickets master table
			$update_stage_q="UPDATE `tms_tickets` SET `ticket_stage_id_fk`='$stage_id'";

			//---if stage is updated RESOLVED than update resolved details in tms_tickets also
			if($stage_id=='RESOLVED'){
				$update_stage_q.=" ,`ticket_resolved_on`='$time',`ticket_resolved_by`='$USERID' "; 
				$resolved_on=$time;
				$resolved_by=$USERID;
			}

			$update_stage_q.="  WHERE `ticket_id`='$ticket_id'";



			//---check if this is the first action and the action is not taken by ticket creater itself
			//---if it's the first response update an entry to tms tikcet table
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `action_id` FROM `tms_tickets_actions` WHERE `action_ticket_id_fk`='$ticket_id'"))==1 && $ticket_added_by!=$USERID){
				$update_stage_q="UPDATE `tms_tickets` SET `ticket_first_action_on`='$time', `ticket_first_action_by`='$USERID' WHERE `ticket_id`='$ticket_id'";
			}

			$update_stage=mysqli_query($GLOBALS['con'],$update_stage_q);
			if(!$update_stage){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;	
			}

			executionChecker:
			if($execution){
				$status=true;
				$message="Action created successfuly";

//----if stage has been changed generate a notification
				if($is_stage_changed){

//----------create notification section
					$ticket_url=LIVEURL.'user/task-management/tickets/details?eid='.$Enc->safeurlen($ticket_id);
					$t_desc="
					<div style='padding:12px; border-radius: 10px;background:#f1f1f1'>
					<h3 style='padding: 5px'>Ticket Description</h3>
					<div style='white-space: pre-line;'>".$ticket_dtl['text']."</div>
					</div>
					<a href='$ticket_url' class='link'><b>Click here to respond</b></a>
					<br>
					<br>";				
					$t_body=MAIL_HEADER.$t_desc.MAIL_FOOTER;



					$this->generate_email_notification_for_ticket([ 
						'ticket_id'=>$ticket_id, 
						'ticket_detail_id'=>$ticket_dtl['detail_id'],
						'subject'=>$stage_id." | Ticket Update | Ticket # $ticket_id | Priority : ".$ticket_dtl['priority_id'],
						'body'=>$t_body,
						'link'=>$ticket_url,
						'text'=>'',
						'notify_to_creator'=>true,
					]);
//----------/create notification section 
				}




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
	function tickets_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
			//-----data validation starts
 			///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
		$dataValidation=true;
		$InvalidDataMessage="";

		if(isset($param['ticket_text']) && $param['ticket_text']!=""){
				//------check if all payments of trip are unpaid
			$ticket_text=senetize_input($param['ticket_text']);
		}else{
			$InvalidDataMessage="Please provide ticket text";
			$dataValidation=false;
			goto ValidationChecker;
		}

		if(isset($param['priority_id'])){
			$priority_id=senetize_input($param['priority_id']);

			include_once APPROOT.'/models/task-management/TicketPriorities.php';
			$TicketPriorities=new TicketPriorities;

			if(!$TicketPriorities->isValidId($priority_id)){
				$InvalidDataMessage="Invalid priority id";
				$dataValidation=false;
			}
		}else{
			$InvalidDataMessage="Please provide priority id";
			$dataValidation=false;
			goto ValidationChecker;
		}

		$ticket_due_date='0000-00-00';
		if(isset($param['ticket_due_date'])){

			if(isValidDateFormat($param['ticket_due_date'])){
				$ticket_due_date=date('Y-m-d', strtotime($param['ticket_due_date']));
							///---------restrict the future date selection
				if($ticket_due_date<date('Y-m-d')){
					$InvalidDataMessage="Past date not allowed";
					$dataValidation=false;
					goto ValidationChecker;									
				}
							///---------/restrict the future date selection

			}else{
				$InvalidDataMessage="Please provide valid due";
				$dataValidation=false;
				goto ValidationChecker;							
			}
		}



		$assigned_to_array=[];
		if(isset($param['levels_array'])){

			include_once APPROOT.'/models/masters/Hierarchy.php';
			$Hierarchy=new Hierarchy;
			foreach ($param['levels_array'] as $level_raw) {


				if($Hierarchy->isValidId($level_raw)){
					array_push($assigned_to_array, array('level_id'=>$level_raw,'user_id'=>''));
				}else{
					$InvalidDataMessage="Invalid hierarchy level id";
					$dataValidation=false;
				}
			}

		}

		$users_array=[];
		if(isset($param['users_array'])){

			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;
			foreach ($param['users_array'] as $user_raw) {


				if($Users->isValidId($user_raw)){
					array_push($assigned_to_array, array('level_id'=>'','user_id'=>$user_raw));
				}else{
					$InvalidDataMessage="Invalid user id";
					$dataValidation=false;
				}
			}

		}


//-----restrict ticket creation without user/level assigning
		if(count($assigned_to_array)<1){
			$InvalidDataMessage="Please assign to atleast one user or level";
			$dataValidation=false;			
		}




		ValidationChecker:
		if($dataValidation){

			$USERID=USER_ID;
			$time=time();
			$executionMessage='';
			$execution=true;			


			///----------insert ticket id entry
			$last_ticket_id=mysqli_query($GLOBALS['con'],"SELECT `ticket_id` FROM `tms_tickets` ORDER BY `auto` DESC LIMIT 1");
			$last_ticket_id=(mysqli_num_rows($last_ticket_id)==1)?(mysqli_fetch_assoc($last_ticket_id)['ticket_id']):'0000000';

			$ticker_id_prefix=date("y");
		//---if last ticket id is from old year than change the prefix with current year and start counting from 1
			if($ticker_id_prefix==substr($last_ticket_id
				,0,2)){
				$new_ticket_id=$ticker_id_prefix.sprintf('%04d',(intval(substr($last_ticket_id,2))));
			}else{
				$new_ticket_id=$ticker_id_prefix.'0000';
			}
			$new_ticket_id++;


			$add_ticket_id=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets`(`ticket_id`, `ticket_added_on`, `ticket_added_by`, `ticket_status`,`ticket_stage_id_fk`) VALUES ('$new_ticket_id','$time','$USERID','ACT','OPEN')");
			if(!$add_ticket_id){
				$executionMessage=SOMETHING_WENT_WROG.' step 01';
				$execution=false;
				goto executionChecker;	
			}


			///----------insert ticket details entry
			$last_ticket_details_id=mysqli_query($GLOBALS['con'],"SELECT `ticket_detail_id` FROM `tms_tickets_details` ORDER BY `auto` DESC LIMIT 1");
			$next_ticket_details_id=(mysqli_num_rows($last_ticket_details_id)==1)?(mysqli_fetch_assoc($last_ticket_details_id)['ticket_detail_id']):'10000000';

			$next_ticket_details_id++;



			$add_ticket_details=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_details`(`ticket_detail_id`, `ticket_detail_ticket_id_fk`, `ticket_detail_priority_id_fk`, `ticket_detail_text`,`ticket_detail_due_date`, `ticket_detail_added_on`, `ticket_detail_added_by`, `ticket_detail_status`) VALUES ('$next_ticket_details_id','$new_ticket_id','$priority_id','$ticket_text','$ticket_due_date','$time','$USERID','ACT')");
			if(!$add_ticket_details){
				$executionMessage=SOMETHING_WENT_WROG.' step 02';
				$execution=false;
				goto executionChecker;	
			}

			///----------insert ticket assigned to entries
			$last_ticket_assigned_to=mysqli_query($GLOBALS['con'],"SELECT `assigned_to_id` FROM `tms_tickets_assigned_to` ORDER BY `auto` DESC LIMIT 1");
			$next_ticket_assigned_to=(mysqli_num_rows($last_ticket_assigned_to)==1)?(mysqli_fetch_assoc($last_ticket_assigned_to)['assigned_to_id']):1;

			
			foreach ($assigned_to_array as $ata) {
				$level_id_insert=($ata['level_id']=='')?null:$ata['level_id'];
				$user_id_insert=($ata['user_id']=='')?null:$ata['user_id'];
				$next_ticket_assigned_to++;
				$add_ticket_details=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_assigned_to`(`assigned_to_id`, `assigned_to_ticket_details_id_fk`, `assigned_to_level_id_fk`, `assigned_to_user_id_fk`, `assigned_to_status`) VALUES ('$next_ticket_assigned_to','$next_ticket_details_id','$level_id_insert','$user_id_insert','ACT')");
				if(!$add_ticket_details){
					$executionMessage=SOMETHING_WENT_WROG.' step 02';
					$execution=false;
					goto executionChecker;	
				}


			}

			executionChecker:
			if($execution){
				$status=true;$message="Ticket created successfuly";
				$ticket_url=LIVEURL.'user/task-management/tickets/details?eid='.$Enc->safeurlen($new_ticket_id);

//----------create notification section
				$t_desc="<p>Hi<br>A new ticket #$new_ticket_id has been assigned to you</p><br>
				<div style='padding:12px; border-radius: 10px;background:#f1f1f1'>
				<h3 style='padding: 5px'>Ticket Description</h3>
				<div style='white-space: pre-line;'>".$ticket_text."</div>
				</div>
				<br>
				Ticket generated time ".date('d-M-Y H:i',$time)."
				<br>
				<a href='$ticket_url' class='link'><b>Click here to respond</b></a>
				<br>
				<br>";				
				$t_body=MAIL_HEADER.$t_desc.MAIL_FOOTER;



				$this->generate_email_notification_for_ticket([ 
					'ticket_id'=>$new_ticket_id, 
					'ticket_detail_id'=>$next_ticket_details_id,
					'subject'=>"New ticket Assigned | Ticket # $new_ticket_id | Priority : $priority_id",
					'body'=>$t_body,
					'link'=>$ticket_url,
					'text'=>$ticket_text
				]);
//----------/create notification section 

				
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
	private function generate_email_notification_for_ticket($param){
		$status=false;
		$message=null;
		$response=null;
		$USER_ID=USER_ID;

		$ticket_id=$param['ticket_id'];
		$ticket_detail_id=$param['ticket_detail_id'];

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		//---get assigned to users/groups
		$q="SELECT `user_id`,`user_code`,`user_name`,`user_email` FROM `tms_tickets_assigned_to` LEFT JOIN `utab` ON `tms_tickets_assigned_to`.`assigned_to_user_id_fk`=`utab`.`user_id` WHERE `assigned_to_ticket_details_id_fk`='$ticket_detail_id' AND NOT `assigned_to_user_id_fk`='' AND NOT `user_id`='$USER_ID'
		UNION
		SELECT  `user_id`,`user_code`,`user_name`,`user_email` FROM `tms_tickets_assigned_to` LEFT JOIN `hierarchy_user_junction` ON `tms_tickets_assigned_to`.`assigned_to_level_id_fk`=`hierarchy_user_junction`.`huj_level_id_fk`  LEFT JOIN `utab` ON `hierarchy_user_junction`.`huj_user_id_fk`=`utab`.`user_id` WHERE `assigned_to_ticket_details_id_fk`='$ticket_detail_id' AND NOT `assigned_to_level_id_fk`='' AND NOT `user_id`='$USER_ID'
		UNION
		SELECT  `user_id`,`user_code`,`user_name`,`user_email` FROM `tms_tickets_details` LEFT JOIN `utab` ON `tms_tickets_details`.`ticket_detail_added_by`=`utab`.`user_id` WHERE `ticket_detail_id`='$ticket_detail_id' AND `ticket_detail_status`='ACT' AND NOT `user_id`='$USER_ID'
		";

/*
if(isset($param['notify_to_creator']) && $param['notify_to_creator']==true){
$q.=" ";
}
*/


$qEx=mysqli_query($GLOBALS['con'],$q);



$emails_array=[];

$notifications_id=mysqli_query($GLOBALS['con'],"SELECT `noti_id` FROM `tms_tickets_notifications_unread` ORDER BY `noti_auto` DESC LIMIT 1");
$notifications_id=(mysqli_num_rows($notifications_id)==1)?(mysqli_fetch_assoc($notifications_id)['noti_id']):1;

$notifications_id_next=$notifications_id;

while ($res=mysqli_fetch_assoc($qEx)) {


///-------------insert notifications in ticket unread notification table
	$notifications_id_next=$notifications_id_next+1;

	mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_notifications_unread`(`noti_id`, `noti_for_user_id_fk`,`noti_ticket_id_fk`, `noti_created_on`, `noti_status`,  `noti_id_status`, `noti_heading`, `noti_text`, `noti_link`) VALUES ('$notifications_id_next','".$res['user_id']."','$ticket_id','".time()."','UNREAD','ACT','".$param['subject']."','".$param['text']."','".$param['link']."')");

////----create array of emails to send notifications on email
	$email=$Enc->dec_mail($res['user_email']);
	if($email!=false){
		array_push($emails_array, $email);
	}
}

include_once APPROOT.'/models/common/SendEmail.php';
$SendEmail=new SendEmail;
$pa=[
	'recipient'=>$emails_array,
	'subject'=>$param['subject'],
	'body'=>$param['body'],
];

$EmailRes=$SendEmail->send_to_multi($pa);
if($EmailRes['status']){
	$status=true;
	$message="Notification sent successfuly";
}else{
	$message=$EmailRes['message'];
}




return ['status'=>$status,'message'=>$message,'response'=>$response];		
}
function tickets_list($param){
	$status=false;
	$message=null;
	$response=null;

	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;

	$InvalidDataMessage="";
	$dataValidation=true;

	ValidationChecker:
	if($dataValidation){

		$q="SELECT `ticket_id`, `ticket_added_on`, `ticket_added_by`, `ticket_status`,`ticket_detail_id`, `ticket_detail_ticket_id_fk`, `ticket_detail_text`, `ticket_detail_added_on`, `ticket_detail_status`, `ticket_detail_added_by`, `ticket_detail_due_date`,`ticket_stage_id_fk`,`ticket_detail_priority_id_fk`,`ticket_resolved_on`, `ticket_resolved_by` FROM `tms_tickets` LEFT JOIN `tms_tickets_details` ON `tms_tickets_details`.`ticket_detail_ticket_id_fk`=`tms_tickets`.`ticket_id`  WHERE `ticket_status`='ACT' AND `ticket_detail_status`='ACT'";

///----------apply filters
		if(isset($param['by_user_id']) && $param['by_user_id']!=''){
			$by_user_id=senetize_input($param['by_user_id']);
			$q.=" AND `ticket_added_by`='$by_user_id'";
		}
		if(isset($param['id']) && $param['id']!=''){
			$id=senetize_input($param['id']);
			$q.=" AND `ticket_id` LIKE '%$id%'";
		}
		if(isset($param['stage_id']) && $param['stage_id']!=''){
			$stage_id=senetize_input($param['stage_id']);
			$q.="AND `ticket_stage_id_fk`='$stage_id'";
		}

///----------/apply filters

		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;

			$added_user=$Users->user_basic_details($rows['ticket_added_by']);
			$added_by_user_code=$added_user['user_code'];
			$added_on_datetime=dateTimeFromDbTimestamp($rows['ticket_added_on']);

			$resolved_by_user_code="";
			$resolved_on_datetime="";

			if($rows['ticket_resolved_by']!=""){
				$resolved_user=$Users->user_basic_details($rows['ticket_resolved_by']);
				$resolved_by_user_code=$resolved_user['user_code'];
				$resolved_on_datetime=dateTimeFromDbTimestamp($rows['ticket_resolved_on']);
			}

					//---fetch record levels/users ticket is assigned to
			$get_assined_q="SELECT `assigned_to_id`, `assigned_to_level_id_fk`, `assigned_to_user_id_fk`, `assigned_to_status`,`level_name`,`user_code` FROM `tms_tickets_assigned_to` LEFT JOIN `hierarchy` ON `hierarchy`.`level_id`=`tms_tickets_assigned_to`.`assigned_to_level_id_fk` LEFT JOIN `utab` ON `utab`.`user_id`=`tms_tickets_assigned_to`.`assigned_to_user_id_fk` WHERE `assigned_to_ticket_details_id_fk`='".$rows['ticket_detail_id']."'";


			$get_assined=mysqli_query($GLOBALS['con'],$get_assined_q);
			$assigned_to_users=[];
			$assigned_to_levels=[];
			while ($ast=mysqli_fetch_assoc($get_assined)) {
				if($ast['assigned_to_user_id_fk']!=''){
					array_push($assigned_to_users,array(
						'user_code'=>$ast['user_code']
					));
				}

				if($ast['assigned_to_level_id_fk']!=''){
					array_push($assigned_to_levels,array(
						'level_name'=>$ast['level_name']
					));
				}

			}



			$part_a=array(
				'id' => $rows['ticket_id'], 
				'eid' => $Enc->safeurlen($rows['ticket_id']), 
				'text' => $rows['ticket_detail_text'], 
				'due_date' => dateFromDbToFormat($rows['ticket_detail_due_date']), 
				'stage' => $rows['ticket_stage_id_fk'], 
				'priority' => $rows['ticket_detail_priority_id_fk'], 
				'added_by_user_code' => $added_by_user_code, 
				'added_on_datetime' => $added_on_datetime,
				'assigned_to_levels'=>$assigned_to_levels,
				'assigned_to_users'=>$assigned_to_users, 
				'resolved_by_user_code'=>$resolved_by_user_code, 
				'resolved_on_datetime'=>$resolved_on_datetime 
			);

			if(count(array_merge($assigned_to_users,$assigned_to_levels))>0){
				array_push($list, $part_a);
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


function tickets_by_user($param){
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;
	return $this->tickets_list(array_merge(array('by_user_id'=>USER_ID),$param));
}


function tickets_for_user($param){
	$status=false;
	$message=null;
	$response=null;

	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;

	$InvalidDataMessage="";
	$dataValidation=true;
	if(isset($param['user_id']) && $param['user_id']!=''){
		$user_id=senetize_input($param['user_id']);
	}else{
		$InvalidDataMessage="please provide user id";
		$dataValidation=false;
		goto ValidationChecker;
	}


		//----get user levels 
	$get_user_levels=mysqli_query($GLOBALS['con'],"SELECT `huj_level_id_fk`  FROM `hierarchy_user_junction` WHERE `huj_status`='ACT' AND `huj_user_id_fk`='$user_id'");
	$user_levels_array=[];
	while($gul=mysqli_fetch_assoc($get_user_levels)){
		array_push($user_levels_array,$gul['huj_level_id_fk']);
	}
	ValidationChecker:
	if($dataValidation){


		$q="SELECT `ticket_id`, `ticket_added_on`, `ticket_added_by`, `ticket_status`,`ticket_detail_id`, `ticket_detail_ticket_id_fk`, `ticket_detail_text`, `ticket_detail_added_on`, `ticket_detail_status`, `ticket_detail_added_by`, `ticket_detail_due_date`,`ticket_stage_id_fk`,`priority_name`,`ticket_resolved_on`,`ticket_resolved_by` FROM `tms_tickets` LEFT JOIN `tms_tickets_details` ON `tms_tickets_details`.`ticket_detail_ticket_id_fk`=`tms_tickets`.`ticket_id` LEFT JOIN `priorities` ON `priorities`.`priority_id`=`tms_tickets_details`.`ticket_detail_priority_id_fk` WHERE `ticket_status`='ACT' AND `ticket_detail_status`='ACT'";

///----------apply filters
		if(isset($param['id']) && $param['id']!=''){
			$id=senetize_input($param['id']);
			$q.=" AND `ticket_id` LIKE '%$id%'";
		}
		if(isset($param['stage_id']) && $param['stage_id']!=''){
			$stage_id=senetize_input($param['stage_id']);
			$q.="AND `ticket_stage_id_fk`='$stage_id'";
		}

		$q.=" ORDER BY `ticket_id`";
///----------/apply filters

		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;
			$added_user=$Users->user_basic_details($rows['ticket_added_by']);
			$added_by_user_code=$added_user['user_code'];
			$added_on_datetime=dateTimeFromDbTimestamp($rows['ticket_added_on']);

			$resolved_by_user_code="";
			$resolved_on_datetime="";

			if($rows['ticket_resolved_by']!=""){
				$resolved_user=$Users->user_basic_details($rows['ticket_resolved_by']);
				$resolved_by_user_code=$resolved_user['user_code'];
				$resolved_on_datetime=dateTimeFromDbTimestamp($rows['ticket_resolved_on']);
			}

					//---fetch record levels/users ticket is assigned to
			$get_assined_q="SELECT `assigned_to_id`, `assigned_to_level_id_fk`, `assigned_to_user_id_fk`,`level_name`,`user_code` FROM `tms_tickets_assigned_to` LEFT JOIN `hierarchy` ON `hierarchy`.`level_id`=`tms_tickets_assigned_to`.`assigned_to_level_id_fk` LEFT JOIN `utab` ON `utab`.`user_id`=`tms_tickets_assigned_to`.`assigned_to_user_id_fk` WHERE `assigned_to_ticket_details_id_fk`='".$rows['ticket_detail_id']."' ";


			$get_assined=mysqli_query($GLOBALS['con'],$get_assined_q);
			$assigned_to_users=[];
			$assigned_to_levels=[];
			while ($ast=mysqli_fetch_assoc($get_assined)) {
				if($ast['assigned_to_user_id_fk']!=''){
					array_push($assigned_to_users,array(
						'user_id'=>$ast['assigned_to_user_id_fk'],
						'user_code'=>$ast['user_code']
					));
				}

				if($ast['assigned_to_level_id_fk']!=''){
					array_push($assigned_to_levels,array(
						'level_id'=>$ast['assigned_to_level_id_fk'],
						'level_name'=>$ast['level_name']
					));
				}

			}



			$part_a=array(
				'id' => $rows['ticket_id'], 
				'eid' => $Enc->safeurlen($rows['ticket_id']), 
				'text' => $rows['ticket_detail_text'], 
				'due_date' => dateFromDbToFormat($rows['ticket_detail_due_date']), 
				'stage' => $rows['ticket_stage_id_fk'], 
				'priority' => $rows['priority_name'], 
				'added_by_user_code' => $added_by_user_code, 
				'added_on_datetime' => $added_on_datetime,
				'assigned_to_levels'=>$assigned_to_levels,
				'assigned_to_users'=>$assigned_to_users, 
				'resolved_by_user_code'=>$resolved_by_user_code, 
				'resolved_on_datetime'=>$resolved_on_datetime 
			);


				//----apply fileter

			$push_entry=false;

				//----apply fileter

			$push_entry=false;

				//---push tickets, assigned to user if tickets_for_user==true
			if(isset($param['tickets_for_user']) && $param['tickets_for_user']==true){

				foreach ($assigned_to_users as $assigned_to_users_r) {
					if($assigned_to_users_r['user_id']==$user_id){
						$push_entry=true;
						goto push_entry_now;
					}
				}
			}


			//---push tickets, assigned to user if tickets_for_user_levels==true
			if(isset($param['tickets_for_user_levels']) && $param['tickets_for_user_levels']==true){
				foreach ($assigned_to_levels as $assigned_to_levels_r) {
					if($assigned_to_levels_r['level_id']!='' && in_array($assigned_to_levels_r['level_id'],$user_levels_array)){
						$push_entry=true;
						goto push_entry_now;						
					}
				}
			}


			//---push tickets, assigned to user if tickets_for_user_team==true
			if(isset($param['tickets_for_user_team']) && $param['tickets_for_user_team']==true){
				include_once APPROOT.'/models/masters/Hierarchy.php';
				$Hierarchy=new Hierarchy;
				$user_levels=$Hierarchy->user_all_levels(array('user_id'=>$param['user_id']));
				$user_children_levels=$Hierarchy->user_children_levels($user_levels);
				foreach ($assigned_to_levels as $assigned_to_levels_r) {
					if($assigned_to_levels_r['level_id']!='' && in_array($assigned_to_levels_r['level_id'],$user_children_levels)){
						$push_entry=true;
						goto push_entry_now;						
					}
				}
			}


			push_entry_now:	
			if($push_entry){
				array_push($list, $part_a);
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




function tickets_details($param){
	$status=false;
	$message=null;
	$response=null;

	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;

	$InvalidDataMessage="";
	$dataValidation=true;

	if(isset($param['ticket_eid']) && $param['ticket_eid']!=''){
		$ticket_id=$Enc->safeurlde($param['ticket_eid']);
	}else{
		$InvalidDataMessage="Please send ticket_eid";
		$dataValidation=false;
		goto ValidationChecker;		
	}

	ValidationChecker:
	if($dataValidation){
		$time=time();
		$USERID=USER_ID;

		$qEx=mysqli_query($GLOBALS['con'],"SELECT `ticket_id`, `ticket_added_on`, `ticket_added_by`, `ticket_status`,`ticket_detail_id`, `ticket_detail_ticket_id_fk`, `ticket_detail_text`, `ticket_detail_added_on`, `ticket_detail_status`, `ticket_detail_added_by`, `ticket_detail_due_date`,`ticket_stage_id_fk`,`ticket_detail_priority_id_fk` FROM `tms_tickets` LEFT JOIN `tms_tickets_details` ON `tms_tickets_details`.`ticket_detail_ticket_id_fk`=`tms_tickets`.`ticket_id` WHERE `ticket_status`='ACT' AND `ticket_detail_status`='ACT' AND `ticket_id`='$ticket_id'");

		if(mysqli_num_rows($qEx)==1) {
			$status=true;
			$rows=mysqli_fetch_assoc($qEx);
			include_once APPROOT.'/models/masters/Users.php';
			$Users=new Users;
			$added_user=$Users->user_basic_details($rows['ticket_added_by']);

					//---fetch record levels/users ticket is assigned to
			$get_assined=mysqli_query($GLOBALS['con'],"SELECT `assigned_to_id`, `assigned_to_level_id_fk`, `assigned_to_user_id_fk`, `assigned_to_status`,`level_name`,`user_code` FROM `tms_tickets_assigned_to` LEFT JOIN `hierarchy` ON `hierarchy`.`level_id`=`tms_tickets_assigned_to`.`assigned_to_level_id_fk` LEFT JOIN `utab` ON `utab`.`user_id`=`tms_tickets_assigned_to`.`assigned_to_user_id_fk` WHERE `assigned_to_ticket_details_id_fk`='".$rows['ticket_detail_id']."'");
			$assigned_to_users=[];
			$assigned_to_levels=[];
			while ($ast=mysqli_fetch_assoc($get_assined)) {
				if($ast['assigned_to_user_id_fk']!=''){
					array_push($assigned_to_users,array(
						'user_id'=>$ast['assigned_to_user_id_fk'],
						'user_code'=>$ast['user_code'],
					));
				}

				if($ast['assigned_to_level_id_fk']!=''){
					array_push($assigned_to_levels,array(
						'level_id'=>$ast['assigned_to_level_id_fk'],
						'level_name'=>$ast['level_name']
					));
				}

			}



			$part_a=array(
				'id' => $rows['ticket_id'], 
				'eid' => $Enc->safeurlen($rows['ticket_id']), 
				'text' => $rows['ticket_detail_text'], 
				'due_date' => dateFromDbToFormat($rows['ticket_detail_due_date']), 
				'priority_id' => $rows['ticket_detail_priority_id_fk'], 
				'status' => $rows['ticket_stage_id_fk'], 
				'added_by_user_code' => $added_user['user_code'], 
				'added_on_datetime' => dateTimeFromDbTimestamp($rows['ticket_added_on']),
				'assigned_to_levels'=>$assigned_to_levels,
				'assigned_to_users'=>$assigned_to_users 
			);



				//---get chats releted to  ticket
			$get_chats_q=mysqli_query($GLOBALS['con'],"SELECT `action_id`, `action_text`, `action_ticket_id_fk`, `action_stage_id_fk`, `action_added_on`, `action_added_by` FROM `tms_tickets_actions` WHERE `action_ticket_id_fk`='".$rows['ticket_id']."'");
			$chats_array=[];
			while ($chat=mysqli_fetch_assoc($get_chats_q)) {
				$action_added_user=$Users->user_basic_details($chat['action_added_by']);
				array_push($chats_array, array(
					'id'=>$chat['action_id'],
					'text'=>$chat['action_text'],
					'status'=>$chat['action_stage_id_fk'],
					'added_by_user_code'=>$action_added_user['user_code'],
					'added_on_datetime'=>dateTimeFromDbTimestamp($chat['action_added_on']),
				));
			}
			$part_a['actions']=$chats_array;
			$response['details']=$part_a;


//----make the notification as READ related to this ticket
			mysqli_query($GLOBALS['con'],"UPDATE `tms_tickets_notifications_unread` SET `noti_status`='READ',`noti_read_on`='$time' WHERE `noti_for_user_id_fk`='$USERID' AND  `noti_ticket_id_fk`='$ticket_id'");
//----make the notification as READ related to this ticket


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


function tickets_update($param){
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
		$InvalidDataMessage="Please provide update eid";
		$dataValidation=false;
		goto ValidationChecker;
	}



//---fetch ticket details 
	$ticket_details_q=mysqli_query($GLOBALS['con'],"SELECT `ticket_added_by` FROM `tms_tickets` WHERE `ticket_id`='$update_id'");
	if(mysqli_num_rows($ticket_details_q)==1){

//------allow updation to the user who create the 
		if(mysqli_fetch_assoc($ticket_details_q)['ticket_added_by']!=USER_ID){
			$InvalidDataMessage="Updation of ticket is only allowed for the user who create it";
			$dataValidation=false;
			goto ValidationChecker;
		}
	}else{
		$InvalidDataMessage="Invalid ticket eid";
		$dataValidation=false;
		goto ValidationChecker;				
	}







	if(isset($param['ticket_text']) && $param['ticket_text']!=""){
				//------check if all payments of trip are unpaid
		$ticket_text=senetize_input($param['ticket_text']);
	}else{
		$InvalidDataMessage="Please provide ticket text";
		$dataValidation=false;
		goto ValidationChecker;
	}


	if(isset($param['priority_id'])){
		$priority_id=senetize_input($param['priority_id']);

		include_once APPROOT.'/models/task-management/TicketPriorities.php';
		$TicketPriorities=new TicketPriorities;

		if(!$TicketPriorities->isValidId($priority_id)){
			$InvalidDataMessage="Invalid priority id";
			$dataValidation=false;
		}
	}else{
		$InvalidDataMessage="Please provide priority id";
		$dataValidation=false;
		goto ValidationChecker;
	}

	$ticket_due_date='0000-00-00';
	if(isset($param['ticket_due_date'])){

		if(isValidDateFormat($param['ticket_due_date'])){
			$ticket_due_date=date('Y-m-d', strtotime($param['ticket_due_date']));
							///---------restrict the future date selection
			if($ticket_due_date<date('Y-m-d')){
				$InvalidDataMessage="Past date not allowed";
				$dataValidation=false;
				goto ValidationChecker;									
			}
							///---------/restrict the future date selection

		}else{
			$InvalidDataMessage="Please provide valid due";
			$dataValidation=false;
			goto ValidationChecker;							
		}
	}



	$assigned_to_array=[];
	if(isset($param['levels_array'])){

		include_once APPROOT.'/models/masters/Hierarchy.php';
		$Hierarchy=new Hierarchy;
		foreach ($param['levels_array'] as $level_raw) {


			if($Hierarchy->isValidId($level_raw)){
				array_push($assigned_to_array, array('level_id'=>$level_raw,'user_id'=>''));
			}else{
				$InvalidDataMessage="Invalid hierarchy level id";
				$dataValidation=false;
			}
		}

	}

	$users_array=[];
	if(isset($param['users_array'])){

		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;
		foreach ($param['users_array'] as $user_raw) {


			if($Users->isValidId($user_raw)){
				array_push($assigned_to_array, array('level_id'=>'','user_id'=>$user_raw));
			}else{
				$InvalidDataMessage="Invalid user id";
				$dataValidation=false;
			}
		}

	}


//-----restrict ticket creation without user/level assigning
	if(count($assigned_to_array)<1){
		$InvalidDataMessage="Please assign to atleast one user or level";
		$dataValidation=false;			
	}




	ValidationChecker:
	if($dataValidation){

		$USERID=USER_ID;
		$time=time();
		$executionMessage='';
		$execution=true;


			//----before inserting new ticket details DELETE old details

		$delete_old_q=mysqli_query($GLOBALS['con'],"UPDATE `tms_tickets_details` SET `ticket_detail_status`='DEL'  WHERE `ticket_detail_ticket_id_fk`='$update_id'");

		if(!$delete_old_q){
			$executionMessage=SOMETHING_WENT_WROG.' step 01';
			$execution=false;
			goto executionChecker;				
		}			


			///----------insert ticket details entry
		$last_ticket_details_id=mysqli_query($GLOBALS['con'],"SELECT `ticket_detail_id` FROM `tms_tickets_details` ORDER BY `auto` DESC LIMIT 1");
		$next_ticket_details_id=(mysqli_num_rows($last_ticket_details_id)==1)?(mysqli_fetch_assoc($last_ticket_details_id)['ticket_detail_id']):'10000000';

		$next_ticket_details_id++;



		$add_ticket_details=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_details`(`ticket_detail_id`, `ticket_detail_ticket_id_fk`, `ticket_detail_priority_id_fk`, `ticket_detail_text`,`ticket_detail_due_date`, `ticket_detail_added_on`, `ticket_detail_added_by`, `ticket_detail_status`) VALUES ('$next_ticket_details_id','$update_id','$priority_id','$ticket_text','$ticket_due_date','$time','$USERID','ACT')");
		if(!$add_ticket_details){
			$executionMessage=SOMETHING_WENT_WROG.' step 02';
			$execution=false;
			goto executionChecker;	
		}

			///----------insert ticket assigned to entries
		$last_ticket_assigned_to=mysqli_query($GLOBALS['con'],"SELECT `assigned_to_id` FROM `tms_tickets_assigned_to` ORDER BY `auto` DESC LIMIT 1");
		$next_ticket_assigned_to=(mysqli_num_rows($last_ticket_assigned_to)==1)?(mysqli_fetch_assoc($last_ticket_assigned_to)['assigned_to_id']):1;


		foreach ($assigned_to_array as $ata) {
			$level_id_insert=($ata['level_id']=='')?null:$ata['level_id'];
			$user_id_insert=($ata['user_id']=='')?null:$ata['user_id'];
			$next_ticket_assigned_to++;
			$add_ticket_details=mysqli_query($GLOBALS['con'],"INSERT INTO `tms_tickets_assigned_to`(`assigned_to_id`, `assigned_to_ticket_details_id_fk`, `assigned_to_level_id_fk`, `assigned_to_user_id_fk`, `assigned_to_status`) VALUES ('$next_ticket_assigned_to','$next_ticket_details_id','$level_id_insert','$user_id_insert','ACT')");
			if(!$add_ticket_details){
				$executionMessage=SOMETHING_WENT_WROG.' step 03';
				$execution=false;
				goto executionChecker;	
			}


		}



		executionChecker:
		if($execution){
			$status=true;
			$message="Ticket updated successfuly";
			$ticket_url=LIVEURL.'user/task-management/tickets/details?eid='.$Enc->safeurlen($update_id);
			$t_desc="<p>Hi<br>Ticket #$update_id has been updated</p><br>
			<div style='padding:12px; border-radius: 10px;background:#f1f1f1'>
			<h3 style='padding: 5px'>Ticket Description</h3>
			<div style='white-space: pre-line;'>".$ticket_text."</div>
			</div>
			<br>
			Ticket last updated time ".date('d-M-Y H:i',$time)."
			<br>
			<a href='$ticket_url' class='link'><b>Click here to respond</b></a>
			<br>
			<br>";				
			$t_body=MAIL_HEADER.$t_desc.MAIL_FOOTER;

			$this->generate_email_notification_for_ticket([ 
				'ticket_id'=>$update_id, 
				'ticket_detail_id'=>$next_ticket_details_id,
				'subject'=>"Ticket Update | #$update_id | Priority : $priority_id",
				'body'=>$t_body
			]);

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

function tickets_delete($param){
	$status=false;
	$message=null;
	$response=null;
			//-----data validation starts
 			///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
	$dataValidation=true;
	$InvalidDataMessage="";
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;
	if(isset($param['delete_eid']) && $param['delete_eid']!=""){
				//------check if all payments of trip are unpaid
		$delete_id=$Enc->safeurlde($param['delete_eid']);
	}else{
		$InvalidDataMessage="Please provide delete eid";
		$dataValidation=false;
		goto ValidationChecker;
	}



//---fetch ticket details 
	$ticket_details_q=mysqli_query($GLOBALS['con'],"SELECT `ticket_added_by` FROM `tms_tickets` WHERE `ticket_id`='$delete_id'");
	if(mysqli_num_rows($ticket_details_q)==1){

//------allow deletion of ticket only to the user who create the 
		if(mysqli_fetch_assoc($ticket_details_q)['ticket_added_by']!=USER_ID){
			$InvalidDataMessage="Updation of ticket is only allowed for the user who create it";
			$dataValidation=false;
			goto ValidationChecker;
		}
	}else{
		$InvalidDataMessage="Invalid ticket eid";
		$dataValidation=false;
		goto ValidationChecker;				
	}

	ValidationChecker:
	if($dataValidation){

		$USERID=USER_ID;
		$time=time();
		$executionMessage='';
		$execution=true;


			//----before inserting new ticket details DELETE old details

		$delete_old_q=mysqli_query($GLOBALS['con'],"UPDATE `tms_tickets` SET `ticket_status`='DEL'  WHERE `ticket_id`='$delete_id'");

		if(!$delete_old_q){
			$executionMessage=SOMETHING_WENT_WROG.' step 01';
			$execution=false;
			goto executionChecker;				
		}			

		executionChecker:
		if($execution){
			$status=true;
			$message="Deleted successfuly";
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
}
?>