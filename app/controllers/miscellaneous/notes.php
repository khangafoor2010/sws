<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/miscellaneous/Notes.php';
		$Notes=new Notes;
switch (getUri()) {

	case 'user/miscellaneous/notes/list':
		$r=$Notes->notes_list(PARAM);
	break;	
	case 'user/miscellaneous/notes/add-new':
		$r=$Notes->notes_add_new(PARAM);
	break;
	case 'user/miscellaneous/notes/toggle-high-priority-status':
		$r=$Notes->toggle_high_priority_status(PARAM);
	break;
	case 'user/miscellaneous/notes/delete':
		$r=$Notes->notes_delete(PARAM);
	break;			
/*
	case 'user/masters/companies/add-new':
		$r=$Notes->companies_add_new(PARAM);
	break;

	case 'user/masters/companies/details':
	if(in_array('P0034', USER_PRIV)){
		$r=$Notes->companies_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/companies/list':
	if(in_array('P0034', USER_PRIV)){
		$r=$Notes->companies_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/companies/update':
		$r=$Notes->companies_update(PARAM);
	
	break;

	case 'user/masters/companies/delete':
		$r=$Notes->companies_delete(PARAM);
	
	break;
*/
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>