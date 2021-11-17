<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/dispatch/CommodityTypes.php';
		$CommodityTypes=new CommodityTypes;
switch (getUri()) {
	case 'user/dispatch/commodity-types/list':
	$r=$CommodityTypes->list(PARAM);
	break;
	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>