<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Status.php';
		$Status=new Status;
switch (getUri()) {

	case 'user/masters/vehicles/conditions/add-new':
		$r=$Status->vehicles_conditions_add_new(PARAM);
	break;

	case 'user/masters/vehicles/conditions/details':
	if(in_array('P29', USER_PRIV)){
		$r=$Status->vehicles_conditions_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/conditions/list':
	if(in_array('P29', USER_PRIV)){
		$r=$Status->vehicles_conditions_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/conditions/update':
		$r=$Status->vehicles_conditions_update(PARAM);
	break;

	case 'user/masters/vehicles/conditions/delete':
		$r=$Status->vehicles_conditions_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>