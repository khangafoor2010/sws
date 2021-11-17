<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;

	include_once APPROOT.'/models/maintenance/masters/RepairOrderCategory.php';
	$repairordercategory=new RepairOrderCategory;
	switch (getUri()) {

	//List Mode
	case 'user/maintenance/masters/repair-order-category/list':
	if(in_array('P0203', USER_PRIV)){
		$r=$repairordercategory->repair_order_category_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Add Mode
	case 'user/maintenance/masters/repair-order-category/add-new':
		$r=$repairordercategory->repair_order_category_add_new(PARAM);
	
	break;

	case 'user/maintenance/masters/repair-order-category/details':
	if(in_array('P0203', USER_PRIV)){
		$r=$repairordercategory->repair_order_category_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/repair-order-category/update':
		$r=$repairordercategory->repair_order_category_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/repair-order-category/delete':
		$r=$repairordercategory->repair_order_category_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>