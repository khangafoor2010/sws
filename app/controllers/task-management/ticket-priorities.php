<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/task-management/TicketPriorities.php';
		$TicketPriorities=new TicketPriorities;
switch (getUri()) {
	case 'user/task-management/ticket-priorities':
		$r=$TicketPriorities->priorities_list(PARAM);
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>