<?php
/**
 * 
 */
class ApiO
{

	function drivers_contacts($param){
		$status=false;
		$message=null;
		$response=[];
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `driver_id`, `driver_code`, `driver_name_first`, `driver_name_middle`, `driver_name_last`, `driver_mobile_no`, `driver_mobile_country_code_id_fk`,`status_name` FROM `drivers` LEFT JOIN `employee_status` ON `drivers`.`driver_status_id_fk`=`employee_status`.`status_id`  WHERE `driver_status`='ACT' AND `status_name`='Active' ORDER BY `driver_id`";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$name_first=($rows['driver_name_first']!='')?$rows['driver_name_first']:'';
			$name_middle=($rows['driver_name_middle']!='')?' '.$rows['driver_name_middle']:'';
			$name_last=($rows['driver_name_last']!='')?' '.$rows['driver_name_last']:'';
			
			array_push($list,[
				'code'=>$rows['driver_code'],
				'name'=>$name_first.$name_middle.$name_last,
				'mobile_number'=>$row['mobile_number']=$Enc->dec_mob($rows['driver_mobile_no']),
				'mobile_country_code'=>$row['mobile_country_code']=$rows['driver_mobile_country_code_id_fk']
			]);
		}
		$response['list']=$list;
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		} 		

		$r=[];
		$r['status']=$status;
		$r['message']=$Enc->safeurlen('1'.time());
		$r['response']=$response;
		return $r;	
	}

}

?>


