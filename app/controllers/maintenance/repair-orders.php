<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/RepairOrders.php';
$RepairOrders=new RepairOrders;
switch (getUri()) 
{

	case 'user/maintenance/repair-orders/list':
	if(in_array('P0228', USER_PRIV))
	{
		$r=$RepairOrders->repair_order_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/repair-orders/add-new':
		$r=$RepairOrders->repair_order_add_new(PARAM);
	break;

	case 'user/maintenance/repair-orders/details':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$RepairOrders->repair_order_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/repair-orders/update':
		$r=$RepairOrders->repair_order_update(PARAM);	
	break;

	case 'user/maintenance/repair-orders/delete':
		$r=$RepairOrders->repair_order_delete(PARAM);
	break;

	case 'user/maintenance/repair-orders/add-follow-up':
		$r=$RepairOrders->add_follow_ups(PARAM);
	break;
	case 'user/maintenance/repair-orders/follow-ups-list':
	$r=$RepairOrders->repair_order_follow_up_list(PARAM);
	break;

	case 'user/maintenance/repair-orders/update-status':
		$r=$RepairOrders->repair_order_status_update(PARAM);
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>