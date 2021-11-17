<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/driverApp/DaDriverPayments.php';
		$DaDriverPayments=new DaDriverPayments;
switch (getUri()) {
	case 'driver/payments/settlements-list':
	$r=$DaDriverPayments->list_of_settlements(PARAM);
	break;
	case 'driver/payments/transactions-list':
	$r=$DaDriverPayments->list_of_transactions(PARAM);
	break;
	case 'driver/payments/payments-paid-list':
	$r=$DaDriverPayments->list_of_payments_paid(PARAM);
	break;		
	case 'driver/payments/incentives-list':
	$r=$DaDriverPayments->list_of_incentives(PARAM);
	break;

	/*case 'driver/payments/details':
	$r=$DaDriverPayments->details_of_trip(PARAM);
	break;*/


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>