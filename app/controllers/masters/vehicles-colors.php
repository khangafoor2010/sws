<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesColors.php';
		$VehiclesColors=new VehiclesColors;
switch (getUri()) {

	case 'user/masters/vehicles/colors/add-new':
		$r=$VehiclesColors->vehicles_colors_add_new(PARAM);
	break;
	case 'user/masters/vehicles/colors/details':
	if(in_array('P0069', USER_PRIV)){
		$r=$VehiclesColors->vehicles_colors_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/colors/list':
	if(in_array('P0069', USER_PRIV)){
		$r=$VehiclesColors->vehicles_colors_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/colors/update':
		$r=$VehiclesColors->vehicles_colors_update(PARAM);
	break;

	case 'user/masters/vehicles/colors/delete':
		$r=$VehiclesColors->vehicles_colors_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>