<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Locations.php';
		$Locations=new Locations;
		include_once APPROOT.'/models/masters/LocationAddresses.php';
		$LocationAddresses=new LocationAddresses;
switch (getUri()) {
	case 'user/masters/locations/addresses/add-new':
		$r=$LocationAddresses->addresses_add_new(PARAM);
	break;	


	case 'user/masters/locations/addresses/details':
		$r=$LocationAddresses->addresses_details(array_merge(PARAM));
	break;

	case 'user/masters/locations/addresses/list':
		$r=$LocationAddresses->addresses_list(PARAM);
	break;
	case 'user/masters/locations/addresses/quick-list':
		$r=$LocationAddresses->addresses_quick_list(PARAM);
	break;
	case 'user/masters/locations/addresses/update':
		$r=$LocationAddresses->addresses_update(PARAM);
	break;

	case 'user/masters/locations/addresses/delete':
		$r=$LocationAddresses(PARAM);
	break;
	case 'user/masters/locations/countries/add-new':
		$r=$Locations->countries_add_new(PARAM);
	break;

	case 'user/masters/locations/countries/details':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->countries_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/locations/countries/list':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->countries_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/locations/countries/update':
		$r=$Locations->countries_update(PARAM);
	break;

	case 'user/masters/locations/countries/delete':
		$r=$Locations->delete_location(PARAM);
	break;



	case 'user/masters/locations/states/add-new':
		$r=$Locations->states_add_new(PARAM);
	break;	


	case 'user/masters/locations/states/details':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->states_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/masters/locations/states/list':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->states_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/locations/states/update':
		$r=$Locations->states_update(PARAM);
	break;

	case 'user/masters/locations/states/delete':
		$r=$Locations->delete_location(PARAM);
	break;



	case 'user/masters/locations/cities/add-new':
		$r=$Locations->cities_add_new(PARAM);
	break;	


	case 'user/masters/locations/cities/details':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->cities_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/masters/locations/cities/list':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->cities_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/locations/cities/update':
		$r=$Locations->cities_update(PARAM);
	break;

	case 'user/masters/locations/cities/delete':
		$r=$Locations->delete_location(PARAM);
	break;


	case 'user/masters/locations/zipcodes/add-new':
		$r=$Locations->zipcodes_add_new(PARAM);
	break;	


	case 'user/masters/locations/zipcodes/details':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->zipcodes_details(array_merge(PARAM,array('details_for' =>'eid')));
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;


	case 'user/masters/locations/zipcodes/list':
	if(in_array('P0014', USER_PRIV)){
		$r=$Locations->zipcodes_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/locations/zipcodes/update':
		$r=$Locations->zipcodes_update(PARAM);
	break;

	case 'user/masters/locations/zipcodes/delete':
		$r=$Locations->delete_location(PARAM);
	break;


	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>