<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/driverApp/DaDriverSettings.php';
		$DaDriverSettings=new DaDriverSettings;
switch (getUri()) {

	case 'driver/settings/password-reset':
	$r=$DaDriverSettings->password_reset(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>