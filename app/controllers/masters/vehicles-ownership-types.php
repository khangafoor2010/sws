<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/VehiclesOwnershipTypes.php';
		$VehiclesOwnershipTypes=new VehiclesOwnershipTypes;
switch (getUri()) {

	case 'user/masters/vehicles/ownership-types/add-new':
		$r=$VehiclesOwnershipTypes->vehicles_ownership_types_add_new(PARAM);
	break;
	case 'user/masters/vehicles/ownership-types/details':
	if(in_array('P0039', USER_PRIV)){
		$r=$VehiclesOwnershipTypes->vehicles_ownership_types_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/ownership-types/list':
	if(in_array('P0039', USER_PRIV)){
		$r=$VehiclesOwnershipTypes->vehicles_ownership_types_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/vehicles/ownership-types/update':
		$r=$VehiclesOwnershipTypes->vehicles_ownership_types_update(PARAM);
	break;

	case 'user/masters/vehicles/ownership-types/delete':
		$r=$VehiclesOwnershipTypes->vehicles_ownership_types_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>