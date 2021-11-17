<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/DriverDocuments.php';
		$DriverDocuments=new DriverDocuments;
switch (getUri()) {
	case 'user/masters/drivers-documents/quick-totals':
	$r=$DriverDocuments->driver_documents_quick_totals(PARAM);
	break;
	case 'user/masters/drivers-documents/document-history':
	$r=$DriverDocuments->document_history(PARAM);
	break;
	case 'user/masters/drivers-documents/all-drivers-documents':
	$r=$DriverDocuments->all_drivers_documents_list(PARAM);
	break;

	case 'user/masters/drivers-documents/driver-documents':
	$r=$DriverDocuments->driver_documents(PARAM);
	break;

	case 'user/masters/drivers-documents/upload':
	$r=$DriverDocuments->driver_documents_upload(PARAM,PARAM_FILE);
	break;
	case 'user/masters/drivers-documents/verify':
	$r=$DriverDocuments->driver_documents_verify(PARAM);
	break;
	case 'user/masters/drivers-documents/reject':
	$r=$DriverDocuments->driver_documents_reject(PARAM);
	break;	

/*
	case 'user/masters/drivers-documents/truck-documents':
	$r=$DriverDocuments->truck_documents(PARAM);
	break;

	case 'user/masters/drivers-documents/all-drivers-documents':
	$r=$DriverDocuments->all_trucks_documents_list(PARAM);
	break;


	case 'user/masters/drivers-documents/truck-documents-upload':
	$r=$DriverDocuments->truck_documents_upload(PARAM,PARAM_FILE);
	break;
	break;
		*/
	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>