<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/masters/Priorties.php';
$Priorties=new priorties;
switch (getUri()) {

	case 'user/masters/priorities/add-new':
	$r=$Priorties->priorities_add_new(PARAM);
	
	break;

	case 'user/masters/priorities/details':
	$r=$Priorties->priorities_details(array_merge(PARAM,array('details_for' =>'eid')));
	break;

	case 'user/masters/priorities/list':
	$r=$Priorties->priorities_list(PARAM);
	break;

	case 'user/masters/priorities/update':
	$r=$Priorties->priorities_update(PARAM);
	
	break;

	case 'user/masters/priorities/delete':
	$r=$Priorties->priorities_delete(PARAM);
	
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>