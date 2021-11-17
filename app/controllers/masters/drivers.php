<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Drivers.php';
		$Drivers=new Drivers;
switch (getUri()) {

	case 'user/masters/drivers/add-new':
		$r=$Drivers->drivers_add_new(PARAM);
	break;

	case 'user/masters/drivers/details':
	if(in_array('P0009', USER_PRIV)){
		$r=$Drivers->drivers_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/masters/drivers/quick-list':
		$r=$Drivers->drivers_quick_list(PARAM);
	break;

	case 'user/masters/drivers/list':
	if(in_array('P0009', USER_PRIV)){
		$r=$Drivers->drivers_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	case 'user/masters/drivers/update':
		$r=$Drivers->drivers_update(PARAM);
	break;

	case 'user/masters/drivers/password-reset':
		$r=$Drivers->driver_password_reset(PARAM);
	break;
	
	case 'user/masters/drivers/delete':
		$r=$Drivers->drivers_delete(PARAM);
	break;

	case 'user/masters/drivers/toggle-settlement-status':
		$r=$Drivers->toggle_settlement_status(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>