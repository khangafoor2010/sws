<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesStatus.php';
		$VehiclesStatus=new VehiclesStatus;
switch (getUri()) {

	case 'user/masters/vehicles/status/add-new':
		$r=$VehiclesStatus->vehicles_status_add_new(PARAM);
	break;

	case 'user/masters/vehicles/status/details':
	if(in_array('P0029', USER_PRIV)){
		$r=$VehiclesStatus->vehicles_status_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/status/list':
	if(in_array('P0029', USER_PRIV)){
		$r=$VehiclesStatus->vehicles_status_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/status/update':
		$r=$VehiclesStatus->vehicles_status_update(PARAM);
	break;

	case 'user/masters/vehicles/status/delete':
		$r=$VehiclesStatus->vehicles_status_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>