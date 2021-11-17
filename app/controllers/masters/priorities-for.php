<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/masters/PriortiesFor.php';
$PriortiesFor=new PriortiesFor;
switch (getUri()) {

	case 'user/masters/priorities-for/list':
	$r=$PriortiesFor->priorities_for_list(PARAM);
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>