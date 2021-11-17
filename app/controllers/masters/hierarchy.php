<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/masters/Hierarchy.php';
$Hierarchy=new Hierarchy;
switch (getUri()) {
	case 'user/masters/hierarchy/levels/add-new':
	$r=$Hierarchy->levels_add_new(PARAM);
	break;

	case 'user/masters/hierarchy/levels/list':
		$r=$Hierarchy->levels_list(PARAM);
	break;

	case 'user/masters/hierarchy/levels/details':
	$r=$Hierarchy->levels_details(PARAM);
	break;

	case 'user/masters/hierarchy/levels/update':
	$r=$Hierarchy->levels_update(PARAM);
	break;
	case 'user/masters/hierarchy/levels/delete':
	$r=$Hierarchy->levels_delete(PARAM);
	break;
	case 'user/masters/hierarchy/levels/levels-users-junction':
	$r=$Hierarchy->levels_users_junction(PARAM);
	break;

	case 'user/masters/hierarchy/levels/assign-users':
	$r=$Hierarchy->level_assign_users(PARAM);
	break;	
	
	case 'user/masters/hierarchy/levels/children':
	$r=$Hierarchy->level_children([103]);
	break;

	case 'user/masters/hierarchy/levels/user-children':
	$r=$Hierarchy->user_level_children();
	break;
	case 'user/masters/hierarchy/levels/parents':
	$r=$Hierarchy->levels_parents([108]);
	break;


	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>