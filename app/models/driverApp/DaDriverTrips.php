<?php
/**
 * 
 */
class DaDriverTrips
{
	function list_of_trips($param){
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;
	$driver_eid=$Enc->safeurlen(DRIVER_ID);
	include_once APPROOT.'/models/accounts/Trips.php';
	$Trips=new Trips;
	return $Trips->driver_all_trips_list(array('driver_eid'=>$driver_eid,'approval_status'=>'APPROVED'));
	}

	function details_of_trip($param){

	$status=false;
	$message=null;
	$response=[];	
	include_once APPROOT.'/models/common/Enc.php';
	$Enc=new Enc;
	$driver_eid=$Enc->safeurlen(DRIVER_ID);
	include_once APPROOT.'/models/accounts/Trips.php';
	if(isset($param['trip_id'])){
	$Trips=new Trips;
	$trip_eid=$Enc->safeurlen($param['trip_id']);
	$result=$Trips->driver_trip_details(array('driver_eid'=>$driver_eid,'trip_eid'=>$trip_eid));
	if($result['status']){
		$response=$result['response'];
		$status=true;
	}		
}else{
	$message="Please provide trip id";
}


	$r=[];
	$r['status']=$status;
	$r['message']=$message;
	$r['response']=$response;
	return $r;	

	}
}

?>