<?php
/**
 * 
 */
class ApiIn
{

	function update_truck_live_readings($param){
		$status=false;
		$message=null;
		$response=[];
		$samsara = samsara_api('https://api.samsara.com/fleet/vehicles/stats?types=obdOdometerMeters%2CobdEngineSeconds');
		$OBJ=json_decode($samsara,true);
		$time=date('Y-m-d H:i:s');
		foreach ($OBJ['data'] as $data) {
			
			if(isset($data['name']) && $data['name']!=''){
				$code=senetize_input($data['name']);
				$engine_hours=isset($data['obdEngineSeconds']['value'])?floor($data['obdEngineSeconds']['value']/60/60):'0';
				$odo=isset($data['obdOdometerMeters']['value'])?floor($data['obdOdometerMeters']['value']*0.000621371):'0';
				$update=mysqli_query($GLOBALS['con'],"UPDATE `trucks` SET `truck_current_odometer_reading`='$odo',`truck_current_engine_hours`='$engine_hours',`truck_current_readings_updated_on`='$time',`truck_current_reading_updated_by_mode`='AUTO' WHERE `truck_code`='$code' AND `truck_odometer_update_type`='AUTO' AND `truck_status`='ACT'");
				if(!$update){
					$message="Something went wrong";
					goto executionChecker;
				}else{
					$status=true;
					$message="Updated successfuly";
				}

			}
		}		
		executionChecker:
	return ['status'=>$status,'message'=>$message,'response'=>$samsara];	
	}

}

?>


