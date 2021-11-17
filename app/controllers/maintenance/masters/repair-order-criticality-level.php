<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/masters/RepairOrderCriticalityLevel.php';
$RepairOrderCriticalityLevel=new RepairOrderCriticalityLevel;
switch (getUri()) {
	//List Mode
	case 'user/maintenance/masters/repair-order-criticality-level/list':
		$r=$RepairOrderCriticalityLevel->repair_order_criticality_Level_list(PARAM);
	break;
/*
	//Add Mode
	case 'user/maintenance/masters/repair-order-criticality-level/add-new':
		$r=$RepairOrderCriticalityLevel->repair_order_criticality_Level_addnew(PARAM);
	
	break;

	case 'user/maintenance/masters/repair-order-criticality-level/details':
	if(in_array('P0089', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$RepairOrderCriticalityLevel->repair_order_criticality_Level_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/repair-order-criticality-level/update':
	if(isset(PARAM)){
		$r=$RepairOrderCriticalityLevel->repair_order_criticality_Level_update(PARAM);
	}
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-criticality-level/delete':
	if(isset(PARAM)){
		$r=$RepairOrderCriticalityLevel->repair_order_criticality_Level_delete(PARAM);
	}
	break;
*/
	default:
	$r['message']='NOT_VALID_REQUEST_TYPE';
	break;
}
echo json_encode($r);
?>