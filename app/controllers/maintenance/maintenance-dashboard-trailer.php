<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/MaintenanceDashboardTrailer.php';
$MaintenanceDashboardTrailer=new MaintenanceDashboardTrailer;
switch (getUri()) 
{

	case 'user/maintenance/maintenancedash-board-truck/list':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$MaintenanceDashboardTrailer->maintenancedashboardtrailer_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>