<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/TripStopTypes.php';
		$TripStopTypes=new TripStopTypes;
switch (getUri()) {

	case 'user/masters/trip-stop-types/add-new':
		$r=$TripStopTypes->stop_types_add_new(PARAM);
	break;

	case 'user/masters/trip-stop-types/details':
		$r=$TripStopTypes->stop_types_details(array_merge(PARAM,array('details_for' =>'eid')));

	break;

	case 'user/masters/trip-stop-types/list':
		$r=$TripStopTypes->stop_types_list(PARAM);
	break;

	case 'user/masters/trip-stop-types/update':
		$r=$TripStopTypes->stop_types_update(PARAM);
	break;

	case 'user/masters/trip-stop-types/delete':
		$r=$TripStopTypes->stop_types_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>