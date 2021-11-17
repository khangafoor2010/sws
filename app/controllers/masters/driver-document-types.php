<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/DocumentTypes.php';
		$DocumentTypes=new DocumentTypes;
switch (getUri()) {

	case 'user/masters/driver-document-types/add-new':
		$r=$DocumentTypes->document_types_add_new(array_merge(PARAM,['relates_to'=>'DRIVER']));
	break;

	case 'user/masters/driver-document-types/details':
		$r=$DocumentTypes->document_types_details(array_merge(PARAM,array('details_for' =>'eid')));
	break;

	case 'user/masters/driver-document-types/list':
		$r=$DocumentTypes->document_types_list(array_merge(PARAM,['relates_to'=>'DRIVER']));
	break;

	case 'user/masters/driver-document-types/update':
		$r=$DocumentTypes->document_types_update(array_merge(PARAM,['relates_to'=>'DRIVER']));
	break;

	case 'user/masters/driver-document-types/delete':
		$r=$DocumentTypes->document_types_delete(PARAM);
	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>