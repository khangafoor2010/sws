<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/IncidentEntry.php';
$IncidentEntry=new IncidentEntry;
switch (getUri()) 
{

	case 'user/maintenance/incident-entry/list':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$IncidentEntry->incident_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/incident-entry/add-new':
	if(isset(PARAM))
	{
		$r=$IncidentEntry->incident_add_new(PARAM);
	}
	break;

	case 'user/maintenance/incident-entry/details':
	if(in_array('P0009', USER_PRIV))
	{
		PARAM['details_for']='eid';
		$r=$IncidentEntry->incident_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/incident-entry/update':
	if(isset(PARAM))
	{
		$r=$IncidentEntry->incident_update(PARAM);
	}
	break;

	case 'user/maintenance/incident-entry/delete':
	if(isset(PARAM))
	{
		$r=$IncidentEntry->incident_delete(PARAM);
	}
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>