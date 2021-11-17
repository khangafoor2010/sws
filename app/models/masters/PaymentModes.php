<?php
/**
 *
 */
 class PaymentModes
 {


	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `mode_id` from `payment_modes` WHERE `mode_id`='$id' AND `mode_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}

 	function payment_modes_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `mode_id`, `mode_name`, `mode_status` FROM `payment_modes` WHERE `mode_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			array_push($list,[
 				'id'=>$rows['mode_id'],
 				'eid'=>$Enc->safeurlen($rows['mode_id']),
 				'name'=>$rows['mode_name'],
 			]);
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