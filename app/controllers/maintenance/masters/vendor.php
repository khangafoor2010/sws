<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/maintenance/masters/Vendor.php';
		$vendor=new Vendor;
switch (getUri()) {

	//List Mode
	case 'user/maintenance/masters/vendor/list':
	if(in_array('P0223', USER_PRIV)){
		$r=$vendor->vendor_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Add Mode
	case 'user/maintenance/masters/vendor/add-new':
		$r=$vendor->vendor_add_new(PARAM);
	break;	

	case 'user/maintenance/masters/vendor/details':
	if(in_array('P0223', USER_PRIV)){
		$r=$vendor->vendor_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/vendor/update':
		$r=$vendor->vendor_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/vendor/delete':
		$r=$vendor->vendor_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>