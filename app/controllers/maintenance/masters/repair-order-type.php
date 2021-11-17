<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;

	include_once APPROOT.'/models/maintenance/masters/RepairOrderType.php';
	$RepairOrderType=new RepairOrderType;
	switch (getUri()) {

	//List Mode
	case 'user/maintenance/masters/repair-order-type/list':
	if(in_array('P0192', USER_PRIV)){
		$r=$RepairOrderType->repair_order_type_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	//Add Mode
	case 'user/maintenance/masters/repair-order-type/add-new':
		$r=$RepairOrderType->repair_order_type_add_new(PARAM);
	break;

	case 'user/maintenance/masters/repair-order-type/details':
	if(in_array('P0192', USER_PRIV)){
		$r=$RepairOrderType->repair_order_type_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	//Modify Mode
	case 'user/maintenance/masters/repair-order-type/update':
		$r=$RepairOrderType->repair_order_type_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-type/delete':
		$r=$RepairOrderType->repair_order_type_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>