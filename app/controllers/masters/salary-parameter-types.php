<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/SalaryParameterTypes.php';
		$SalaryParameterTypes=new SalaryParameterTypes;
switch (getUri()) {


	case 'user/masters/salary-parameter-types/list':
		$r=$SalaryParameterTypes->salary_parameter_types_list(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>