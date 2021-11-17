<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/MobileCountryCodes.php';
		$MobileCountryCodes=new MobileCountryCodes;
switch (getUri()) {

	case 'user/masters/mobile-country-codes/add-new':
		$r=$MobileCountryCodes->mobile_country_codes_add_new(PARAM);
	break;

	case 'user/masters/mobile-country-codes/details':
	if(in_array('P0099', USER_PRIV)){
		$r=$MobileCountryCodes->mobile_country_codes_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/mobile-country-codes/list':
	if(in_array('P0099', USER_PRIV)){
		$r=$MobileCountryCodes->mobile_country_codes_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/mobile-country-codes/update':
		$r=$MobileCountryCodes->mobile_country_codes_update(PARAM);
	break;

	case 'user/masters/mobile-country-codes/delete':
		$r=$MobileCountryCodes->mobile_country_codes_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>