<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Checklists.php';
		$Checklists=new Checklists;
switch (getUri()) {

	case 'user/masters/checklists/add-new':
	if(isset(PARAM)){
		$r=$Checklists->checklists_add_new(PARAM);
	}
	break;

	case 'user/masters/checklists/details':
	if(in_array('P104', USER_PRIV)){
		PARAM['details_for']='eid';
		$r=$Checklists->checklists_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/checklists/drivers/list':
	if(in_array('P104', USER_PRIV)){
		$r=$Checklists->checklists_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/checklists/update':
	if(isset(PARAM)){
		$r=$Checklists->checklists_update(PARAM);
	}
	break;

	case 'user/masters/checklists/delete':
	if(isset(PARAM)){
		$r=$Checklists->checklists_delete(PARAM);
	}
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>