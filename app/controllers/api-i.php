<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/api/ApiIn.php';
		$ApiIn=new ApiIn;
switch (getUri()) {
	case 'api-i/update-truck-live-readings':
	$r=$ApiIn->update_truck_live_readings(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>