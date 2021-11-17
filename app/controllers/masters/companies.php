<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Companies.php';
		$Companies=new Companies;
switch (getUri()) {

	case 'user/masters/companies/add-new':
		$r=$Companies->companies_add_new(PARAM);
	
	break;

	case 'user/masters/companies/details':
	if(in_array('P0034', USER_PRIV)){
		$r=$Companies->companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/companies/list':
	if(in_array('P0034', USER_PRIV)){
		$r=$Companies->companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/companies/update':
		$r=$Companies->companies_update(PARAM);
	
	break;

	case 'user/masters/companies/delete':
		$r=$Companies->companies_delete(PARAM);
	
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>