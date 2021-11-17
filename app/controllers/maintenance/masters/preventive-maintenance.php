<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;

	include_once APPROOT.'/models/maintenance/masters/PreventiveMaintenance.php';
	$PreventiveMaintenance=new PreventiveMaintenance;
	switch (getUri()) {

	//List Mode
	case 'user/maintenance/masters/preventive-maintenance/list':
	if(in_array('P0218', USER_PRIV)){
		$r=$PreventiveMaintenance->preventive_maintenance_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Add Mode
	case 'user/maintenance/masters/preventive-maintenance/add-new':
		$r=$PreventiveMaintenance->preventive_maintenance_add_new(PARAM);
	break;

	case 'user/maintenance/masters/preventive-maintenance/details':
	if(in_array('P0218', USER_PRIV)){
		$r=$PreventiveMaintenance->preventive_maintenance_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/preventive-maintenance/update':
		$r=$PreventiveMaintenance->preventive_maintenance_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/preventive-maintenance/delete':
		$r=$PreventiveMaintenance->preventive_maintenance_delete(PARAM);
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>