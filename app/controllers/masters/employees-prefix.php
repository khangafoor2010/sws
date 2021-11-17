<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/EmployeesPrefix.php';
		$EmployeesPrefix=new EmployeesPrefix;
switch (getUri()) {

	case 'user/masters/employees/prefix/add-new':
		$r=$EmployeesPrefix->employees_prefix_add_new(PARAM);
	break;

	case 'user/masters/employees/prefix/details':
	if(in_array('P0089', USER_PRIV)){
		$r=$EmployeesPrefix->employees_prefix_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/employees/prefix/list':
	if(in_array('P0089', USER_PRIV)){
		$r=$EmployeesPrefix->employees_prefix_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/employees/prefix/update':
		$r=$EmployeesPrefix->employees_prefix_update(PARAM);
	break;

	case 'user/masters/employees/prefix/delete':
		$r=$EmployeesPrefix->employees_prefix_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>