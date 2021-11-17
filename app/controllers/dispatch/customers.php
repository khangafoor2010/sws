<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/dispatch/Customers.php';
		$Customers=new Customers;
switch (getUri()) {

	case 'user/dispatch/customers/add-new':
		$r=$Customers->customers_add_new(PARAM);
	break;

	case 'user/dispatch/customers/details':
	if(in_array('P0163', USER_PRIV)){
		$r=$Customers->customers_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/dispatch/customers/quick-list':
		$r=$Customers->customers_quick_list(PARAM);
	break;

	case 'user/dispatch/customers/list':
	if(in_array('P0163', USER_PRIV)){
		$r=$Customers->customers_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
/*
	case 'user/masters/trucks/list-basic':
	if(in_array('P0019', USER_PRIV)){
		$r=$Customers->customers_list_basic(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
*/
	case 'user/dispatch/customers/update':
		$r=$Customers->customers_update(PARAM);
	break;
/*
	case 'user/masters/customers/delete':
		$r=$Customers->customers_delete(PARAM);
	break;
*/
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>