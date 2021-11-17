<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/task-management/TicketNotifications.php';
		$TicketNotifications=new TicketNotifications;
switch (getUri()) {
	case 'user/task-management/ticket-notifications/user-notifications':
		$r=$TicketNotifications->user_notifications(PARAM);
	break;

	case 'user/task-management/ticket-notifications/user-total-unread-notifications':
		$r=$TicketNotifications->user_total_unread_notifications(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>