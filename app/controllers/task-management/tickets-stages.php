<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/task-management/TicketStages.php';
		$TicketStages=new TicketStages;
switch (getUri()) {


	case 'user/task-management/tickets-stages/tickets-stages':
		$r=$TicketStages->ticket_stages_list(PARAM);
	break;	


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>