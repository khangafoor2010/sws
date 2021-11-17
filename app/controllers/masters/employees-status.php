<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/EmployeesStatus.php';
		$EmployeesStatus=new EmployeesStatus;
switch (getUri()) {

	case 'user/masters/employees/status/add-new':
		$r=$EmployeesStatus->employees_status_add_new(PARAM);
	break;

	case 'user/masters/employees/status/details':
	if(in_array('P0084', USER_PRIV)){
		$r=$EmployeesStatus->employees_status_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/employees/status/list':
		$r=$EmployeesStatus->employees_status_list(PARAM);
	break;

	case 'user/masters/employees/status/update':
		$r=$EmployeesStatus->employees_status_update(PARAM);
	break;

	case 'user/masters/employees/status/delete':
		$r=$EmployeesStatus->employees_status_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>