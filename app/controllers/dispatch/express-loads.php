<?php
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
        include_once APPROOT.'/models/dispatch/ExpressLoads.php';
        $ExpressLoads=new ExpressLoads;
switch (getUri()) {

    case 'user/dispatch/express-loads/add-new':
        $r=$ExpressLoads->express_loads_add_new(PARAM);
    break;

    case 'user/dispatch/express-loads/details':
    if(in_array('P0169', USER_PRIV)){
        $r=$ExpressLoads->express_loads_details(PARAM);
    }else{
        $r['message']=NOT_AUTHORIZED_MSG;
    }
    break;

    case 'user/dispatch/express-loads/quick-list':
        $r=$ExpressLoads->express_loads_quick_list(PARAM);
    break;

    case 'user/dispatch/express-loads/load-status-wise-total':
        $r=$ExpressLoads->load_status_wise_total(PARAM);
    break;

    case 'user/dispatch/express-loads/list':
    if(in_array('P0169', USER_PRIV)){
        $r=$ExpressLoads->express_loads_list(PARAM);
    }else{
        $r['message']=NOT_AUTHORIZED_MSG;
    }
    break;
/*
    case 'user/masters/trucks/list-basic':
    if(in_array('P0019', USER_PRIV)){
        $r=$ExpressLoads->express_loads_list_basic(PARAM);
    }else{
        $r['message']=NOT_AUTHORIZED_MSG;
    }
    break;
*/
    case 'user/dispatch/express-loads/update':
        $r=$ExpressLoads->express_loads_update(PARAM);
    break;
    case 'user/dispatch/express-loads/update-opration-info':
        $r=$ExpressLoads->express_loads_update_operation_info(PARAM);
    break;
    case 'user/dispatch/express-loads/update-booking-info':
        $r=$ExpressLoads->express_loads_update_booking_info(PARAM);
    break;
    case 'user/dispatch/express-loads/document-update-roc-file':
        $r=$ExpressLoads->express_loads_document_update_roc_file(PARAM,PARAM_FILE);
    break;            
/*
    case 'user/masters/customers/delete':
        $r=$ExpressLoads->express_loads_delete(PARAM);
    break;
*/
    default:
        $r['message']=NOT_VALID_REQUEST_TYPE;
        break;
}
echo json_encode($r);
?>