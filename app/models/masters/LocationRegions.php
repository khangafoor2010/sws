<?php

class LocationRegions

{

	function isValidId($id){

		return (mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `region_id` from `location_regions` WHERE `region_id`='".senetize_input($id)."' AND `region_status`='ACT'"))==1);
}


	function list($param){

		$status=false;

		$message=null;

		$response=null;

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;



		$get=mysqli_query($GLOBALS['con'],"SELECT `region_id`, `region_name` FROM `location_regions` WHERE `region_status`='ACT'");

		$list=[];

		while ($rows=mysqli_fetch_assoc($get)) {

		array_push($list,[
				'id'=>$rows['region_id'],
				'eid'=>$Enc->safeurlen($rows['region_id']),
				'name'=>$rows['region_name']
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