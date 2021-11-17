<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/userLogs/UserLogs.php';
		$UserLogs=new UserLogs;
switch (getUri()) {

	case 'user/login':
	$r=$UserLogs->login(PARAM);
	break;
	case 'user/login-forget-password':
	$r=$UserLogs->forget_password(PARAM);
	break;
	case 'user/login-set-new-password':
	$r=$UserLogs->set_new_password(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>