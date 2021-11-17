<?php

class CommodityTypes

{

	function isValidId($id){

		$id=senetize_input($id);

		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `commodity_type_id` from `d_commodity_types` WHERE `commodity_type_id`='$id' AND `commodity_type_status`='ACT'"))==1){

			return true;

		}else{

			return false;

		}

	}


	function list($param){

		$status=false;

		$message=null;

		$response=null;

		include_once APPROOT.'/models/common/Enc.php';

		$Enc=new Enc;



		$get=mysqli_query($GLOBALS['con'],"SELECT `commodity_type_id`, `commodity_type_name` FROM `d_commodity_types` WHERE `commodity_type_status`='ACT'");

		$list=[];

		while ($rows=mysqli_fetch_assoc($get)) {

			$row=[];

			$row['id']=$rows['commodity_type_id'];

			$row['eid']=$Enc->safeurlen($rows['commodity_type_id']);

			$row['name']=$rows['commodity_type_name'];

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