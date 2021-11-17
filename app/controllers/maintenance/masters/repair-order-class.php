<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/masters/RepairOrderClass.php';
$RepairOrderClass=new RepairOrderClass;
switch (getUri()) {
	//List Mode
	case 'user/maintenance/masters/repair-order-class/list':
		$r=$RepairOrderClass->repair_order_class_list(PARAM);
	break;
/*
	//Add Mode
	case 'user/maintenance/masters/repair-order-class/add-new':
	if(isset(PARAM)){
		$r=$RepairOrderClass->repair_order_class_addnew(PARAM);
	}
	break;

	case 'user/maintenance/masters/repair-order-class/details':
	if(in_array('P0089', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$RepairOrderClass->repair_order_class_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/repair-order-class/update':
	if(isset(PARAM)){
		$r=$RepairOrderClass->repair_order_class_update(PARAM);
	}
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-class/delete':
	if(isset(PARAM)){
		$r=$RepairOrderClass->repair_order_class_delete(PARAM);
	}
	break;
*/
	default:
	$r['message']='NOT_VALID_REQUEST_TYPE';
	break;
}
echo json_encode($r);
?>