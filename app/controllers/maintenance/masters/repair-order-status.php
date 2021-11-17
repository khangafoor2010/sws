<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/maintenance/masters/RepairOrderStatus.php';
		$repairorderstatus=new RepairOrderStatus;
switch (getUri()) {

	//List Mode
	case 'user/maintenance/masters/repair-order-status/list':
	if(in_array('P0014', USER_PRIV)){
		$r=$repairorderstatus->repair_order_status_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
/*
	//Add Mode
	case 'user/maintenance/masters/repair-order-status/add-new':
	if(isset(PARAM)){
		$r=$repairorderstatus->repair_order_status_addnew(PARAM);
	}
	break;	

	case 'user/maintenance/masters/repair-order-status/details':
	if(in_array('P0014', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$repairorderstatus->repair_order_status_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/repair-order-status/update':
	if(isset(PARAM)){
		$r=$repairorderstatus->repair_order_status_update(PARAM);
	}
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-status/delete':
	if(isset(PARAM)){
		$r=$repairorderstatus->repair_order_status_delete(PARAM);
	}
	break;
*/
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>