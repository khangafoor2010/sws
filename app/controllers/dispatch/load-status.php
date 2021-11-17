<?php

$r=[];

$r['status']=false;

$r['message']=null;

$r['response']=null;

		include_once APPROOT.'/models/dispatch/LoadStatus.php';

		$LoadStatus=new LoadStatus;

switch (getUri()) {

	case 'user/dispatch/load-status/list':

	$r=$LoadStatus->list(PARAM);

	break;

	default:

		$r['message']='NOT_VALID_REQUEST_TYPE';

		break;

}

echo json_encode($r);

?>