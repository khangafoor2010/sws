<?php
/**
 *
 */
 class TicketPriorities
 {


	function isValidId($id ,$priority_type=""){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		$q="SELECT `priority_id` from `tms_ticket_priorities` WHERE `priority_id`='$id' AND `priority_status`='ACT' ";
		if($priority_type!=''){
			$priority_type=senetize_input($priority_type);
			$q.=" AND `priority_for_id_fk`='$priority_type'";
		}
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],$q))==1){
			return true;
		}else{
			return false;
		}
	}


 	function priorities_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `priority_id`, `priority_name` FROM `tms_ticket_priorities` WHERE `priority_status`='ACT' ORDER BY `priority_name`");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['priority_id'];
 			$row['eid']=$Enc->safeurlen($rows['priority_id']);
 			$row['name']=$rows['priority_name'];
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