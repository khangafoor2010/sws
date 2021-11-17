<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/accounts/DriverPayments.php';
		$DriverPayments=new DriverPayments;
switch (getUri()) {


	case 'user/accounts/drivers-payments/group-transactions-list':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->drivers_group_transactions_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/drivers-payments/transactions-list':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->drivers_transactions_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;	

	case 'user/accounts/drivers-payments/payements-list':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->drivers_payments_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;	

	case 'user/accounts/drivers-payments/payements-paid-list':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->drivers_payments_paid_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	case 'user/accounts/drivers-payments/group-transactions-details':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->group_transaction_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/drivers-payments/transactions-details':
	if(in_array('P0140', USER_PRIV)){
		$r=$DriverPayments->transaction_details(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;	
	

	case 'user/accounts/drivers-payments/all-drivers-payble-list':
	if(in_array('P0124', USER_PRIV)){
		$r=$DriverPayments->all_drivers_payble_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/drivers-payments/all-drivers-payment-status':
	if(in_array('P0124', USER_PRIV)){
		$r=$DriverPayments->all_drivers_payment_status(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/accounts/drivers-payments/make-drivers-group-transaction':
	if(in_array('P0125', USER_PRIV)){
		$r=$DriverPayments->make_drivers_group_transaction(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/accounts/drivers-payments/driver-pending-paybles':
	if(in_array('P0124', USER_PRIV)){
		$r=$DriverPayments->driver_payments_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	
	case 'user/accounts/drivers-payments/monthy-hold-incentives-all-drivers':
	if(in_array('P0124', USER_PRIV)){
		$r=$DriverPayments->monthy_incentives_all_drivers(PARAM,array('status' =>'HOLD'));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
/*	
	case 'user/accounts/drivers-payments/monthy-hold-incentives-all-drivers-move':
	if(in_array('P0128', USER_PRIV)){
		$r=$DriverPayments->monthy_incentives_all_drivers_move(PARAM);
	}
	break;
*/
	case 'user/accounts/drivers-payments/move-trips-incentive':
	if(in_array('P0128', USER_PRIV)){
		$r=$DriverPayments->move_trips_incentive(PARAM);
	}
	break;	

	case 'user/accounts/drivers-payments/add-earnings-and-deductions':
	if(in_array('P0141', USER_PRIV)){
		$r=$DriverPayments->add_earnings_and_deductions(PARAM);
	}
	break;

	case 'user/accounts/drivers-payments/earnings-and-deductions-details':
		$r=$DriverPayments->earnings_and_deductions_details(array_merge(PARAM,array('details_for' =>'eid')));
	break;	
	case 'user/accounts/drivers-payments/earnings-and-deductions-update':
		$r=$DriverPayments->earnings_and_deductions_update(PARAM);
	break;
	case 'user/accounts/drivers-payments/earnings-and-deductions-delete':
		$r=$DriverPayments->earnings_and_deductions_delete(PARAM);
	break;			
	/*case 'user/accounts/trips/pending-approval-approve':
	if(isset(PARAM)){
		$r=$DriverSalaries->trips_approve(PARAM);
	}
	break;	*/
	case 'user/accounts/drivers-payments/transactions':
	if(in_array('PADMIN', USER_PRIV)){
		$r=$DriverPayments->drivers_transactions_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>