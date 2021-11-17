<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/TruckDocuments.php';
		$TruckDocuments=new TruckDocuments;
switch (getUri()) {
	case 'user/masters/trucks-documents/quick-totals':
	$r=$TruckDocuments->truck_documents_quick_totals(PARAM);
	break;
	
	case 'user/masters/trucks-documents/document-history':
	$r=$TruckDocuments->document_history(PARAM);
	break;	

	case 'user/masters/trucks-documents/all-trucks-documents':
	$r=$TruckDocuments->all_truck_documents_list(PARAM);
	break;
	
	case 'user/masters/trucks-documents/pending-uploads':
	$r=$TruckDocuments->pending_uploads_list(PARAM);
	break;	

	case 'user/masters/trucks-documents/truck-documents':
	$r=$TruckDocuments->truck_documents(PARAM);
	break;

	case 'user/masters/trucks-documents/upload':
	$r=$TruckDocuments->truck_documents_upload(PARAM,PARAM_FILE);
	break;
	case 'user/masters/trucks-documents/verify':
	$r=$TruckDocuments->truck_documents_verify(PARAM);
	break;
	case 'user/masters/trucks-documents/reject':
	$r=$TruckDocuments->truck_documents_reject(PARAM);
	break;
/*
	case 'user/masters/trucks-documents/truck-documents':
	$r=$TruckDocuments->truck_documents(PARAM);
	break;

	case 'user/masters/trucks-documents/all-trucks-documents':
	$r=$TruckDocuments->all_trucks_documents_list(PARAM);
	break;

	case 'user/masters/trucks-documents/driver-documents-upload':
	$r=$TruckDocuments->truck_documents_upload(PARAM,PARAM_FILE);
	break;
	case 'user/masters/trucks-documents/truck-documents-upload':
	$r=$TruckDocuments->truck_documents_upload(PARAM,PARAM_FILE);
	break;
	break;
	case 'user/masters/trucks-documents/verify':
	$r=$TruckDocuments->documents_verify(PARAM);
	break;
	case 'user/masters/trucks-documents/reject':
	$r=$TruckDocuments->documents_reject(PARAM);
	break;			*/
	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>