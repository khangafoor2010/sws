<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/userLogs/Profile.php';
		$Profile=new Profile;
switch (getUri()) {

	case 'user/settings/profile/reset-password':
	$r=$Profile->reset_password(PARAM);
	break;


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>