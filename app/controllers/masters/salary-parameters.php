<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/SalaryParameters.php';
		$SalaryParameters=new SalaryParameters;
switch (getUri()) {

	case 'user/masters/salary-parameters/add-new':
		$r=$SalaryParameters->salary_parameters_add_new(PARAM);
	break;

	case 'user/masters/salary-parameters/details':
		$r=$SalaryParameters->salary_parameters_details(array_merge(PARAM,array('details_for' =>'eid')));
	break;

	case 'user/masters/salary-parameters/list':
		$r=$SalaryParameters->salary_parameters_list(PARAM);
	break;

	case 'user/masters/salary-parameters/update':
		$r=$SalaryParameters->salary_parameters_update(PARAM);
	break;

	case 'user/masters/salary-parameters/delete':
		$r=$SalaryParameters->salary_parameters_delete(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>