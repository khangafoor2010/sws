<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/LeaseCompanies.php';
		$LeaseCompanies=new LeaseCompanies;
switch (getUri()) {

	case 'user/masters/lease-companies/add-new':
		$r=$LeaseCompanies->lease_companies_add_new(PARAM);
	break;

	case 'user/masters/lease-companies/details':
	if(in_array('P0044', USER_PRIV)){
		$r=$LeaseCompanies->lease_companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/lease-companies/list':
	if(in_array('P0044', USER_PRIV)){
		$r=$LeaseCompanies->lease_companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/lease-companies/update':
		$r=$LeaseCompanies->lease_companies_update(PARAM);
	break;

	case 'user/masters/lease-companies/delete':
		$r=$LeaseCompanies->lease_companies_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>