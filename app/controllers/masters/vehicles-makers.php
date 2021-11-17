<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesMakers.php';
		$VehiclesMakers=new VehiclesMakers;
switch (getUri()) {

	case 'user/masters/vehicles/makers/add-new':
		$r=$VehiclesMakers->vehicles_makers_add_new(PARAM);
	break;


	case 'user/masters/vehicles/makers/list':
	if(in_array('P0054', USER_PRIV)){
		$r=$VehiclesMakers->vehicles_makers_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/makers/detials':
	if(in_array('P0054', USER_PRIV)){
		$r=$VehiclesMakers->vehicles_makers_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/makers/update':
		$r=$VehiclesMakers->vehicles_makers_update(PARAM);
	break;

	case 'user/masters/vehicles/makers/delete':
		$r=$VehiclesMakers->vehicles_makers_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>