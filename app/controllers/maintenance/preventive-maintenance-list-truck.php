<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/PreventiveMaintenanceListTruck.php';
$PreventiveMaintenanceListTruck=new PreventiveMaintenanceListTruck;
switch (getUri()) 
{

	case 'user/maintenance/preventive-maintenance-list-truck/list':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$PreventiveMaintenanceListTruck->PreventiveMaintenanceListTruck_list(PARAM);
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