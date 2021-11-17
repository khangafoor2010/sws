<?php
/**
 *
 */
 class Vehicles
 {

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` from `vehicles` WHERE `vehicle_id`='$id' AND `vehicle_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}

 	function vehicles_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `vehicle_id`, `vehicle_name` FROM `vehicles` WHERE `vehicle_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['vehicle_id'];
 			$row['eid']=$Enc->safeurlen($rows['vehicle_id']);
 			$row['name']=$rows['vehicle_name'];
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
 	function vehicles_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			
			$query="SELECT `vehicle_id`, `vehicle_name` FROM `vehicles` WHERE `vehicle_status`='ACT'";

			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND vehicle_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND vehicle_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	

				
				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
 				$row['id']=$rows['vehicle_id'];
 				$row['eid']=$Enc->safeurlen($rows['vehicle_id']);
				$row['name']=$rows['vehicle_name'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}

 }
 ?>