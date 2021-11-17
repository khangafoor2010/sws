<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/RepairOrderFollowup.php';
$RepairOrderFollowup=new RepairOrderFollowup;
switch (getUri()) 
{

	case 'user/maintenance/repair-order-followup/list':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$RepairOrderFollowup->repairorderfollowup_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/repair-order-followup/add-new':
	if(isset(PARAM))
	{
		$r=$RepairOrderFollowup->repairorderfollowup_add_new(PARAM);
	}
	break;

	case 'user/maintenance/repair-order-followup/details':
	if(in_array('P0009', USER_PRIV))
	{
		PARAM['details_for']='eid';
		$r=$RepairOrderFollowup->repairorderfollowup_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/repair-order-followup/update':
	if(isset(PARAM))
	{
		$r=$RepairOrderFollowup->repairorderfollowup_update(PARAM);
	}
	break;

	case 'user/maintenance/repair-order-followup/delete':
	if(isset(PARAM))
	{
		$r=$RepairOrderFollowup->repairorderfollowup_delete(PARAM);
	}
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>