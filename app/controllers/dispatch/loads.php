<?php

$r=[];

$r['status']=false;

$r['message']=null;

$r['response']=null;

        include_once APPROOT.'/models/dispatch/Loads.php';

        $Loads=new Loads;

switch (getUri()) {



    case 'user/dispatch/loads/add-new':

        $r=$Loads->loads_add_new(PARAM);

    break;



    case 'user/dispatch/loads/details':

    if(in_array('P0174', USER_PRIV)){

        $r=$Loads->loads_details(PARAM);

    }else{

        $r['message']=NOT_AUTHORIZED_MSG;

    }

    break;

    case 'user/dispatch/loads/stop-details':

    if(in_array('P0174', USER_PRIV)){

        $r=$Loads->stop_details(PARAM);

    }else{

        $r['message']=NOT_AUTHORIZED_MSG;

    }

    break;

    case 'user/dispatch/loads/quick-list':

        $r=$Loads->loads_quick_list(PARAM);

    break;



    case 'user/dispatch/loads/list':

    if(in_array('P0174', USER_PRIV)){

        $r=$Loads->loads_list(PARAM);

    }else{

        $r['message']=NOT_AUTHORIZED_MSG;

    }

    break;

/*

    case 'user/masters/trucks/list-basic':

    if(in_array('P0019', USER_PRIV)){

        $r=$Loads->loads_list_basic(PARAM);

    }else{

        $r['message']=NOT_AUTHORIZED_MSG;

    }

    break;

*/

    case 'user/dispatch/loads/load-information-update':

        $r=$Loads->load_information_update(PARAM);

    break;
        case 'user/dispatch/loads/load-stop-information-update':

        $r=$Loads->stop_information_update(PARAM);

    break;


/*

    case 'user/masters/customers/delete':

        $r=$Loads->loads_delete(PARAM);

    break;

*/

    default:

        $r['message']=NOT_VALID_REQUEST_TYPE;

        break;

}

echo json_encode($r);

?>