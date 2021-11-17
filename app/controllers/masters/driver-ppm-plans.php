<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/DriverPpmPlans.php';
		$DriverPpmPlans=new DriverPpmPlans;
switch (getUri()) {

	case 'user/masters/driver-ppm-plans/add-new':
		$r=$DriverPpmPlans->driver_ppm_plans_add_new(PARAM);
	break;

	case 'user/masters/driver-ppm-plans/details':
	if(in_array('PADMIN', USER_PRIV)){
		$r=$DriverPpmPlans->driver_ppm_plans_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/driver-ppm-plans/list':
		$r=$DriverPpmPlans->driver_ppm_plans_list(PARAM);
	break;

	case 'user/masters/driver-ppm-plans/update':
		$r=$DriverPpmPlans->driver_ppm_plans_update(PARAM);
	break;

	case 'user/masters/driver-ppm-plans/delete':
		$r=$DriverPpmPlans->driver_ppm_plans_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>