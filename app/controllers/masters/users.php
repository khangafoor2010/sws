<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Users.php';
		$Users=new Users;
switch (getUri()) {
	case 'user/masters/users/add-new':
		$r=$Users->users_add_new(PARAM);
	break;

	case 'user/masters/users/quick-list':
		$r=$Users->user_quick_list(PARAM);
	break;
	
	case 'user/masters/users/list':
	if(in_array('P0003', USER_PRIV)){
		$r=$Users->users_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/users/details':
	if(in_array('P0003', USER_PRIV)){
		$r=$Users->users_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/users/update':
		$r=$Users->users_update(PARAM);
	break;

	case 'user/masters/users/password-update':
		$r=$Users->users_password_update(PARAM);
	break;
	case 'user/masters/users/assign-roles-group':
	$r=$Users->assign_users_roles_groups(PARAM);
	break;


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>