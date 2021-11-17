<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/driverApp/DaDriverLogs.php';
		$DaDriverLogs=new DaDriverLogs;
switch (getUri()) {

	case 'driver/login':
	$r=$DaDriverLogs->login(PARAM);
	break;


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>