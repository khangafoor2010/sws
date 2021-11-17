<?php
/**
 * 
 */
class  TicketNotifications
{

			function user_total_unread_notifications($param){
 		$status=false;
 		$message=null;
 		$response=[];
 		$user_id=USER_ID;
 		$get=mysqli_fetch_assoc(mysqli_query($GLOBALS['con'],"SELECT COUNT(`noti_id`) AS  	`total_unread` FROM `tms_tickets_notifications_unread` WHERE `noti_id_status`='ACT' AND `noti_status`='UNREAD' AND `noti_for_user_id_fk`='$user_id'"));
 		$response['total_unread_notifications']=$get['total_unread'];
 		if($get['total_unread']>0){
 			$status=true;
 		}else{
 			$message="No notification found";
 		} 		
 		return ['status'=>$status,'message'=>$message,'response'=>$response];	
 	}
	
		function user_notifications($param){
 		$status=false;
 		$message=null;
 		$response=null;
		$batch=5000;
		$page=1;
		 		$user_id=USER_ID;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
 		$q="SELECT `noti_id`,`noti_heading`, `noti_text`, `noti_link`, `noti_status`  FROM `tms_tickets_notifications_unread` WHERE `noti_id_status`='ACT' AND `noti_for_user_id_fk`='$user_id'";

 	//---------apply filter
 			if(isset($param['status']) && $param['status']!=""){
 				$status_type=senetize_input($param['status']);
 				$q.=" AND `noti_status`='$status_type'";
 			}
 			if(isset($param['limit']) && $param['limit']!=""){
 				$batch=intval(senetize_input($param['limit']));
 				//$q.=" AND `noti_status`='$status'";
 			}

 	//---------/apply filter
		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" ORDER BY `noti_created_on` DESC limit $from, $batch";
		$qEx=mysqli_query($GLOBALS['con'],$q);

 		$list=[];
 		while ($res=mysqli_fetch_assoc($qEx)) {
 			array_push($list,[
 				'id'=>$res['noti_id'],
 				'heading'=>$res['noti_heading'],
 				'text'=>$res['noti_text'],
 				'link'=>$res['noti_link'],
 				'status'=>$res['noti_status']
 			]);
 		}

 		$response=[];
 		$response['total']=$totalRows;
		$response['totalRows']=$totalRows;
		$response['currentPage']=$page;
		$response['resultFrom']=$from+1;
		$response['resultUpto']=$from+$batch;
 		$response['list']=$list;
 		if(count($list)>0){
 			$status=true;
 		}else{
 			$message="No notification found";
 		} 		

 		$r=[];
 		$r['status']=$status;
 		$r['message']=$q;
 		$r['response']=$response;
 		return $r;	
 	}
}
?>