<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
include_once APPROOT.'/models/masters/RolesGroups.php';
$RolesGroups=new RolesGroups;
switch (getUri()) {
	case 'user/masters/users/roles-groups/add-new':
	$r=$RolesGroups->roles_groups_add_new(PARAM);
	break;

	case 'user/masters/users/roles-groups/list':
	if(in_array('P0136', USER_PRIV)){
		$r=$RolesGroups->roles_groups_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/users/roles-groups/details':
	$r=$RolesGroups->roles_groups_details(array_merge(PARAM,array('details_for' =>'eid')));
	break;

	case 'user/masters/users/roles-groups/update':
	$r=$RolesGroups->roles_groups_update(PARAM);
	break;
	case 'user/masters/users/roles-groups/delete':
	$r=$RolesGroups->roles_groups_delete(PARAM);
	break;
	case 'user/masters/users/roles-groups-all-roles':
	$r=$RolesGroups->roles_groups_all_roles_list(PARAM);
	break;
	case 'user/masters/users/roles-groups-roles':
	$r=$RolesGroups->roles_groups_group_roles(PARAM);
	break;

	case 'user/masters/users/roles-groups/roles-groups-users-junction':
	$r=$RolesGroups->roles_groups_users_junction(PARAM);
	break;
	
	case 'user/masters/users/roles-groups-roles-update':
	$r=$RolesGroups->roles_groups_group_roles_update(PARAM);
	break;

	case 'user/masters/users/roles-groups/assign-users':
	$r=$RolesGroups->roles_groups_assign_users(PARAM);
	break;


	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>