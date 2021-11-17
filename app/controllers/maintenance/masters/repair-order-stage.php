<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
	include_once APPROOT.'/models/maintenance/masters/RepairOrderStage.php';
	$RepairOrderStage=new RepairOrderStage;

switch (getUri()) {
	//List Mode
	case 'user/maintenance/masters/repair-order-stage/list':
	if(in_array('P0198', USER_PRIV)){
		$r=$RepairOrderStage->repair_order_stage_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Add Mode
	case 'user/maintenance/masters/repair-order-stage/add-new':
		$r=$RepairOrderStage->repair_order_stage_add_new(PARAM);
	break;

	case 'user/maintenance/masters/repair-order-stage/details':
	if(in_array('P0198', USER_PRIV)){
		$r=$RepairOrderStage->repair_order_stage_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/repair-order-stage/update':
		$r=$RepairOrderStage->repair_order_stage_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-stage/delete':
		$r=$RepairOrderStage->repair_order_stage_delete(PARAM);
	break;

	default:
	$r['message']='NOT_VALID_REQUEST_TYPE';
	break;
}
echo json_encode($r);
?>