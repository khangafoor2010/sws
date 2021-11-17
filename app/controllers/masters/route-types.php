<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/RouteTypes.php';
		$RouteTypes=new RouteTypes;
switch (getUri()) {

	case 'user/masters/route-types/add-new':
		$r=$RouteTypes->route_types_add_new(PARAM);
	break;

	case 'user/masters/route-types/details':
	if(in_array('P0109', USER_PRIV)){
		$r=$RouteTypes->route_types_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/route-types/list':
		$r=$RouteTypes->route_types_list(PARAM);

	break;

	case 'user/masters/route-types/update':
		$r=$RouteTypes->route_types_update(PARAM);
	break;

	case 'user/masters/route-types/delete':
		$r=$RouteTypes->route_types_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>