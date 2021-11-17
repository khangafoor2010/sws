<?php
/**
 *
 */
class SalaryParameterTypes
{

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `parameter_type_id` from `salary_parameter_types` WHERE `parameter_type_id`='$id' AND `parameter_type_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}


 	function salary_parameter_types_list($param){
 		$status=false;
 		$message=null;
 		$response=null;
 		include_once APPROOT.'/models/common/Enc.php';
 		$Enc=new Enc;

 		$get=mysqli_query($GLOBALS['con'],"SELECT `parameter_type_id`, `parameter_type_impact`  FROM `salary_parameter_types` WHERE `parameter_type_status`='ACT'");
 		$list=[];
 		while ($rows=mysqli_fetch_assoc($get)) {
 			$row=[];
 			$row['id']=$rows['parameter_type_id'];
 			$row['name']=$rows['parameter_type_id'];
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