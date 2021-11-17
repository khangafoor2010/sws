<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/Documents.php';
		$Documents=new Documents;
switch (getUri()) {

	case 'user/masters/documents/driver-documents':
	$r=$Documents->driver_documents(PARAM);
	break;
	
	case 'user/masters/documents/all-drivers-documents':
	$r=$Documents->all_drivers_documents_list(PARAM);
	break;

	case 'user/masters/documents/truck-documents':
	$r=$Documents->truck_documents(PARAM);
	break;

	case 'user/masters/documents/all-trucks-documents':
	$r=$Documents->all_trucks_documents_list(PARAM);
	break;

	case 'user/masters/documents/driver-documents-upload':
	$r=$Documents->driver_documents_upload(PARAM,PARAM_FILE);
	break;
	case 'user/masters/documents/truck-documents-upload':
	$r=$Documents->truck_documents_upload(PARAM,PARAM_FILE);
	break;
	break;
	case 'user/masters/documents/verify':
	$r=$Documents->documents_verify(PARAM);
	break;
	case 'user/masters/documents/reject':
	$r=$Documents->documents_reject(PARAM);
	break;			
	default:
		$r['message']=NOT_VALID_REQUEST_TYPE;
		break;
}
echo json_encode($r);
?>