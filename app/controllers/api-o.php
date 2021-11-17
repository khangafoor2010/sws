<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/api/ApiO.php';
		$ApiO=new ApiO;
switch (getUri()) {
	case 'api-o/drivers-contacts':
	$r=$ApiO->drivers_contacts(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>