<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/masters/JobWork.php';
$JobWork=new JobWork;
switch (getUri()) {
	//List Mode
	case 'user/maintenance/masters/job-work/list':
	if(in_array('P0213', USER_PRIV)){
		$r=$JobWork->job_work_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	//Add Mode
	case 'user/maintenance/masters/job-work/add-new':
		$r=$JobWork->job_work_add_new(PARAM);
	break;
	case 'user/maintenance/masters/job-work/details':
	if(in_array('P0213', USER_PRIV)){
		$r=$JobWork->job_work_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	//Modify Mode
	case 'user/maintenance/masters/job-work/update':
		$r=$JobWork->job_work_update(PARAM);
	break;
	//Delete Mode
	case 'user/maintenance/masters/job-work/delete':
		$r=$JobWork->job_work_delete(PARAM);
	break;

	default:
	$r['message']='NOT_VALID_REQUEST_TYPE';
	break;
}
echo json_encode($r);
?>