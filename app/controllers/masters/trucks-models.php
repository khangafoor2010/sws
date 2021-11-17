<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/TrucksModels.php';
		$TrucksModels=new TrucksModels;
switch (getUri()) {

	case 'user/masters/trucks/models/add-new':
		$r=$TrucksModels->trucks_models_add_new(PARAM);
	break;


	case 'user/masters/trucks/models/list':
	if(in_array('P59', USER_PRIV)){
		$r=$TrucksModels->trucks_models_list(PARAM);
	}else{
		$r['message']=NOT_AUTHORIZED_MSG;
	}
	break;

	case 'user/masters/trucks/models/update':
		$r=$TrucksModels->trucks_models_update(PARAM)
		;
	break;

	case 'user/masters/trucks/models/delete':
		$r=$TrucksModels->trucks_models_delete(PARAM);
	break;

	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>