<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/driverApp/DaDriverTrips.php';
		$DaDriverTrips=new DaDriverTrips;
switch (getUri()) {

	case 'driver/trips/list':
	$r=$DaDriverTrips->list_of_trips(PARAM);
	break;

	case 'driver/trips/details':
	$r=$DaDriverTrips->details_of_trip(PARAM);
	break;
	
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>