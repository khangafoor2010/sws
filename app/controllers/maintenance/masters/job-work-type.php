<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;

include_once APPROOT.'/models/maintenance/masters/JobWorkType.php';
$JobWorkType=new JobWorkType;
switch (getUri()) {
	//List Mode
	case 'user/maintenance/masters/job-work-type/list':
	if(in_array('P0208', USER_PRIV)){
		$r=$JobWorkType->job_work_type_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Add Mode
	case 'user/maintenance/masters/job-work-type/add-new':
		$r=$JobWorkType->job_work_type_add_new(PARAM);
	break;

	case 'user/maintenance/masters/job-work-type/details':
	if(in_array('P0208', USER_PRIV)){
		$r=$JobWorkType->job_work_type_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	//Modify Mode
	case 'user/maintenance/masters/job-work-type/update':
		$r=$JobWorkType->job_work_type_update(PARAM);
	break;

	//Delete Mode
	case 'user/maintenance/masters/job-work-type/delete':
		$r=$JobWorkType->job_work_type_delete(PARAM);
	break;

	default:
	$r['message']='NOT_VALID_REQUEST_TYPE';
	break;
}
echo json_encode($r);
?>