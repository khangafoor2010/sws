<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/InsuranceCompanies.php';
		$InsuranceCompanies=new InsuranceCompanies;
switch (getUri()) {

	case 'user/masters/insurance-companies/add-new':
		$r=$InsuranceCompanies->insurance_companies_add_new(PARAM);
	break;

	case 'user/masters/insurance-companies/details':
	if(in_array('P0074', USER_PRIV)){
		$r=$InsuranceCompanies->insurance_companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/insurance-companies/list':
	if(in_array('P0074', USER_PRIV)){
		$r=$InsuranceCompanies->insurance_companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/insurance-companies/update':
		$r=$InsuranceCompanies->insurance_companies_update(PARAM);
	break;

	case 'user/masters/insurance-companies/delete':
		$r=$InsuranceCompanies->insurance_companies_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>