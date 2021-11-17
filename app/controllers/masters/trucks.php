<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Trucks.php';
		$Trucks=new Trucks;
switch (getUri()) {

	case 'user/masters/trucks/add-new':
		$r=$Trucks->trucks_add_new(PARAM);
	break;

	case 'user/masters/trucks/details':
	if(in_array('P0019', USER_PRIV)){
		$r=$Trucks->trucks_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trucks/list':
	if(in_array('P0019', USER_PRIV)){
		$r=$Trucks->trucks_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trucks/list-basic':
	if(in_array('P0019', USER_PRIV)){
		$r=$Trucks->trucks_list_basic(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trucks/update':
		$r=$Trucks->trucks_update(PARAM);
	break;

	case 'user/masters/trucks/delete':
		$r=$Trucks->trucks_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>