<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/DeviceCompanies.php';
		$DeviceCompanies=new DeviceCompanies;
switch (getUri()) {

	case 'user/masters/device-companies/add-new':
		$r=$DeviceCompanies->device_companies_add_new(PARAM);
	break;

	case 'user/masters/device-companies/details':
	if(in_array('P0064', USER_PRIV)){
		$r=$DeviceCompanies->device_companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/device-companies/list':
	if(in_array('P0064', USER_PRIV)){
		$r=$DeviceCompanies->device_companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/device-companies/update':
		$r=$DeviceCompanies->device_companies_update(PARAM);
	
	break;

	case 'user/masters/device-companies/delete':
		$r=$DeviceCompanies->device_companies_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>