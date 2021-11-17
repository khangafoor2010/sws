<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;

include_once APPROOT.'/models/masters/TrailerDocuments.php';
$TrailerDocuments=new TrailerDocuments;
switch (getUri()) {
	case 'user/masters/trailers-documents/quick-totals':
	$r=$TrailerDocuments->trailer_documents_quick_totals(PARAM);
	break;

	case 'user/masters/trailers-documents/all-trailers-documents':
	$r=$TrailerDocuments->all_trailer_documents_list(PARAM);
	break;

	case 'user/masters/trailers-documents/trailer-documents':
	$r=$TrailerDocuments->trailer_documents(PARAM);
	break;

	case 'user/masters/trailers-documents/upload':
	$r=$TrailerDocuments->trailer_documents_upload(PARAM,PARAM_FILE);
	break;

	case 'user/masters/trailers-documents/verify':
	$r=$TrailerDocuments->trailer_documents_verify(PARAM);
	break;
	
	case 'user/masters/trailers-documents/reject':
	$r=$TrailerDocuments->trailer_documents_reject(PARAM);
	break;

	default:
	$r['message']=NOT_VALID_REQUEST_TYPE;
	break;
}
echo json_encode($r);
?>