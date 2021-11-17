<?php
/**
 *
 */
class TicketStages
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `ticket_stage_id` from `tms_tickets_stages` WHERE `ticket_stage_id`='$id' AND `ticket_stage_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}
	
		function ticket_stages_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `ticket_stage_id`, `ticket_stage_status` FROM `tms_tickets_stages` WHERE `ticket_stage_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['ticket_stage_id'];
 			$row['name']=$rows['ticket_stage_id'];
 			array_push($list,$row);
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

}
?>