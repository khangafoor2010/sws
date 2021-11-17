<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/DriverGroups.php';
		$DriverGroups=new DriverGroups;
switch (getUri()) {
/*
	case 'user/masters/driver-groups/add-new':
	if(isset(PARAM)){
		$r=$DriverGroups->driver_groups_add_new(PARAM);
	}
	break;

	case 'user/masters/driver-groups/details':
	if(in_array('P114', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$DriverGroups->driver_groups_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
*/
	case 'user/masters/driver-groups/list':
		$r=$DriverGroups->driver_groups_list(PARAM);
	break;
/*
	case 'user/masters/driver-groups/update':
	if(isset(PARAM)){
		$r=$DriverGroups->driver_groups_update(PARAM);
	}
	break;

	case 'user/masters/driver-groups/delete':
	if(isset(PARAM)){
		$r=$DriverGroups->driver_groups_delete(PARAM);
	}
	break;
*/
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>