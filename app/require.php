<?php
    //Require libraries from folder libraries
	require_once 'config/config.php';
    require_once 'helpers/common_function.php';
    require_once 'libraries/Core.php';

    //--default messages
    define('NOT_AUTHORIZED_MSG', 'You are not authoirzed for this activity');  
    define('NOT_VALID_REQUEST_TYPE', 'Invalid requst type');  
    define('REQUIRE_NECESSARY_FIELDS', 'Please enter necessary fields');  
    define('SOMETHING_WENT_WROG', 'Something went wrong');  

    //Instantiate core class
    $init = new Core();
