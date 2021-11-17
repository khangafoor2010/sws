<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/maintenance/PreventiveMaintenanceListTrailer.php';
$PreventiveMaintenanceListTrailer=new PreventiveMaintenanceListTrailer;
switch (getUri()) 
{

	case 'user/maintenance/preventive-maintenance-list-trailer/list':
	if(in_array('P0009', USER_PRIV))
	{
		$r=$PreventiveMaintenanceListTrailer->PreventiveMaintenanceListTrailer_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/preventive-maintenance-list-trailer/add-new':
	if(isset(PARAM))
	{
		$r=$PreventiveMaintenanceListTrailer->PreventiveMaintenanceListTrailer_add_new(PARAM);
	}
	break;

	case 'user/maintenance/preventive-maintenance-list-trailer/details':
	if(in_array('P0009', USER_PRIV))
	{
		PARAM['details_for']='eid';
		$r=$PreventiveMaintenanceListTrailer->PreventiveMaintenanceListTrailer_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/maintenance/preventive-maintenance-list-trailer/update':
	if(isset(PARAM))
	{
		$r=$PreventiveMaintenanceListTrailer->PreventiveMaintenanceListTrailer_update(PARAM);
	}
	break;

	case 'user/maintenance/preventive-maintenance-list-trailer/delete':
	if(isset(PARAM))
	{
		$r=$PreventiveMaintenanceListTrailer->PreventiveMaintenanceListTrailer_delete(PARAM);
	}
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>