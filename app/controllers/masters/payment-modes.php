<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/PaymentModes.php';
		$PaymentModes=new PaymentModes;
switch (getUri()) {
	case 'user/masters/payment-modes/list':
	$r=$PaymentModes->payment_modes_list(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>