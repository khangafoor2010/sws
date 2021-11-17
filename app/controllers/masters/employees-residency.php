<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/EmployeesResidency.php';
		$EmployeesResidency=new EmployeesResidency;
switch (getUri()) {

	case 'user/masters/employees/residency/add-new':
		$r=$EmployeesResidency->employees_residency_add_new(PARAM);
	break;

	case 'user/masters/employees/residency/details':
	if(in_array('P0094', USER_PRIV)){
		$r=$EmployeesResidency->employees_residency_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/employees/residency/list':
	if(in_array('P0094', USER_PRIV)){
		$r=$EmployeesResidency->employees_residency_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/employees/residency/update':
		$r=$EmployeesResidency->employees_residency_update(PARAM);
	break;

	case 'user/masters/employees/residency/delete':
		$r=$EmployeesResidency->employees_residency_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>