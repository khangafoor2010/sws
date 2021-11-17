<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Trailers.php';
		$trailers=new trailers;
switch (getUri()) {

	case 'user/masters/trailers/add-new':
		$r=$trailers->trailers_add_new(PARAM);
	break;

	case 'user/masters/trailers/details':
	if(in_array('P0024', USER_PRIV)){
		$r=$trailers->trailers_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trailers/list':
	if(in_array('P0024', USER_PRIV)){
		$r=$trailers->trailers_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trailers/update':
		$r=$trailers->trailers_update(PARAM);
	break;

	case 'user/masters/trailers/delete':
		$r=$trailers->trailers_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>