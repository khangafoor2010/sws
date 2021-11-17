<?php
/*
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesConditions.php';
		$VehiclesConditions=new VehiclesConditions;
switch (getUri()) {

	case 'user/masters/vehicles/conditions/add-new':
	if(isset(PARAM)){
		$r=$VehiclesConditions->vehicles_conditions_add_new(PARAM);
	}
	break;

	case 'user/masters/vehicles/conditions/details':
	if(in_array('P29', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$VehiclesConditions->vehicles_conditions_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/conditions/list':
	if(in_array('P29', USER_PRIV)){
		$r=$VehiclesConditions->vehicles_conditions_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/conditions/update':
	if(isset(PARAM)){
		$r=$VehiclesConditions->vehicles_conditions_update(PARAM);
	}
	break;

	case 'user/masters/vehicles/conditions/delete':
	if(isset(PARAM)){
		$r=$VehiclesConditions->vehicles_conditions_delete(PARAM);
	}
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
*/?>