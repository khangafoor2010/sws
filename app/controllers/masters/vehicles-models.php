<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesModels.php';
		$VehiclesModels=new VehiclesModels;
switch (getUri()) {

	case 'user/masters/vehicles/models/add-new':
		$r=$VehiclesModels->vehicles_models_add_new(PARAM);
	break;


	case 'user/masters/vehicles/models/list':
	if(in_array('P0059', USER_PRIV)){
		$r=$VehiclesModels->vehicles_models_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/models/details':
	if(in_array('P0059', USER_PRIV)){
		$r=$VehiclesModels->vehicles_models_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/masters/vehicles/models/update':
		$r=$VehiclesModels->vehicles_models_update(PARAM)
		;
	break;

	case 'user/masters/vehicles/models/delete':
		$r=$VehiclesModels->vehicles_models_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>