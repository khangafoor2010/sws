<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/task-management/Tickets.php';
		$Tickets=new Tickets;
switch (getUri()) {

	
	case 'user/task-management/tickets/add-new':
		$r=$Tickets->tickets_add_new(PARAM);
	break;
	case 'user/task-management/tickets/tickets-by-user':
		$r=$Tickets->tickets_by_user(PARAM);
	break;	
	case 'user/task-management/tickets/tickets-for-user':
		$r=$Tickets->tickets_for_user(array_merge(PARAM,['user_id'=>USER_ID,'tickets_for_user'=>true,'tickets_for_user_levels'=>true]));
	break;
	case 'user/task-management/tickets/tickets-for-team':
		$r=$Tickets->tickets_for_user(array_merge(PARAM,['user_id'=>USER_ID,'tickets_for_user_team'=>true]));
	break;
	case 'user/task-management/tickets/details':
		$r=$Tickets->tickets_details(PARAM);
	break;
	case 'user/task-management/tickets/tickets-add-action':
		$r=$Tickets->tickets_add_action(PARAM);
	break;		
	case 'user/task-management/tickets/update':
		$r=$Tickets->tickets_update(PARAM);
	break;
	case 'user/task-management/tickets/delete':
		$r=$Tickets->tickets_delete(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>