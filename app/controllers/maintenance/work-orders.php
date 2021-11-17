<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/maintenance/WorkOrders.php';
		$WorkOrders=new WorkOrders;
		
switch (getUri()) {

	case 'user/maintenance/work-orders/list':
	if(in_array('P0233', USER_PRIV)){
		$r=$WorkOrders->work_orders_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/work-orders/add-new':
		$r=$WorkOrders->work_order_add_new(PARAM);
	break;

	case 'user/maintenance/work-orders/details':
	if(in_array('P0233', USER_PRIV)){
		$r=$WorkOrders->work_order_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/work-orders/update':
		$r=$WorkOrders->WorkOrders_update(PARAM);
	break;

	case 'user/maintenance/work-orders/delete':
		$r=$WorkOrders->work_order_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>