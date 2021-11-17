<?php

class LoadStatus

{

	function isValidId($id){

		return (mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `load_status_id` from `d_mas_load_status` WHERE `load_status_id`='".senetize_input($id)."' AND `load_status_id_status`='ACT'"))==1);
}


	function list($param){

		$status=false;

		$message=null;

		$response=null;

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;



		$get=mysqli_query($GLOBALS['con'],"SELECT `load_status_id`, `load_status_name` FROM `d_mas_load_status` WHERE `load_status_id_status`='ACT'");

		$list=[];

		while ($rows=mysqli_fetch_assoc($get)) {

		array_push($list,[
				'id'=>$rows['load_status_id'],
				'eid'=>$Enc->safeurlen($rows['load_status_id']),
				'name'=>$rows['load_status_name']
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