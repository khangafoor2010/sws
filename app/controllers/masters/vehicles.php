<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Vehicles.php';
		$Vehicles=new Vehicles;
switch (getUri()) {
	case 'user/masters/vehicles/list':
	if(in_array('P0049', USER_PRIV)){
		$r=$Vehicles->vehicles_list(PARAM);
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