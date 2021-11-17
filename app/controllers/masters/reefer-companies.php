<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/ReeferCompanies.php';
		$ReeferCompanies=new ReeferCompanies;
switch (getUri()) {

	case 'user/masters/reefer-companies/add-new':
		$r=$ReeferCompanies->reefer_companies_add_new(PARAM);
	break;

	case 'user/masters/reefer-companies/details':
	if(in_array('P0074', USER_PRIV)){
		$r=$ReeferCompanies->reefer_companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/reefer-companies/list':
	if(in_array('P0074', USER_PRIV)){
		$r=$ReeferCompanies->reefer_companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/reefer-companies/update':
		$r=$ReeferCompanies->reefer_companies_update(PARAM);
	break;

	case 'user/masters/reefer-companies/delete':
		$r=$ReeferCompanies->reefer_companies_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>