<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/accounts/Trips.php';
		$Trips=new Trips;
switch (getUri()) {
	case 'user/accounts/trips/quick-totals':
		$r=$Trips->trips_quick_totals(PARAM);
	break;
	case 'user/accounts/trips/add-new':
		$r=$Trips->trips_add_new(PARAM);
	break;

	case 'user/accounts/trips/details':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->trips_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/trips/details-for-updation':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->trips_details_for_updation(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/trips/update':
		$r=$Trips->trips_update(PARAM);
	break;


	case 'user/accounts/trips/list':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->trips_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/trips/quick-list':
	$r=$Trips->trips_quick_list(PARAM);
	break;	

	case 'user/accounts/trips/driver-trips-list':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->drivers_total_trips_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;	

	case 'user/accounts/trips/driver-all-trips-list':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->driver_all_trips_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;	

	case 'user/accounts/trips/list-pending-approval':
	if(in_array('P0120', USER_PRIV)){
		$r=$Trips->trips_list(array_merge(PARAM,array('approval_status_id' =>'PENDING')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	case 'user/accounts/trips/pending-approval-approve':
		$r=$Trips->trips_approve(PARAM);
	break;	
	case 'user/accounts/trips/pending-approval-reject':
		$r=$Trips->trips_reject(PARAM);
	break;

	case 'user/accounts/trips/driver-trip-parameters-details':
		$r=$Trips->driver_trip_parameters_details(PARAM);
	break;	

	case 'user/accounts/trips/driver-trip-parameters-details-update':
		$r=$Trips->driver_trip_parameters_details_update(PARAM);
	break;		
	case 'user/accounts/trips/months-list':
		$r=$Trips->trips_months_list(PARAM);
	break;

	case 'user/accounts/trips/cancel':
		$r=$Trips->trips_cancel(PARAM);
	break;

	case 'user/accounts/trips/resettle':
		$r=$Trips->trips_resettle(PARAM);
	break;		
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>