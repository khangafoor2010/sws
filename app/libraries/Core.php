<?php
class Core {

  public function __construct(){
    $uri = $this->getUri();

    $routes= array(
      '/^debug/i' => 'debug',
      '/^api-o/i' => 'api-o',
      '/^api-i/i' => 'api-i',
      '/^driver-salary-slip/i' => 'general/driver-salary-slips',
      '/^driver\/login$/i' => 'driver/login/logs',
      '/^driver\/trips/i' => 'driver/trips/trips',
      '/^driver\/payments/i' => 'driver/payments/payments',
      '/^driver\/settings/i' => 'driver/settings/settings',        
      '/^user\/login/i' => 'user/login/logs',
      '/^user\/miscellaneous\/notes/i' => 'miscellaneous/notes',
      '/^user\/masters\/companies/i' => 'masters/companies',
      '/^user\/masters\/mobile-country-codes/i' => 'masters/mobile-country-codes',
      '/^user\/masters\/salary-parameter-types/i' => 'masters/salary-parameter-types',
      '/^user\/masters\/salary-parameters/i' => 'masters/salary-parameters',
      '/^user\/masters\/employees\/status/i' => 'masters/employees-status',
      '/^user\/masters\/employees\/prefix/i' => 'masters/employees-prefix',
      '/^user\/masters\/employees\/residency/i' => 'masters/employees-residency',
      '/^user\/masters\/lease-companies/i' => 'masters/lease-companies',
      '/^user\/masters\/device-companies/i' => 'masters/device-companies',
      '/^user\/masters\/insurance-companies/i' => 'masters/insurance-companies',
      '/^user\/masters\/reefer-companies/i' => 'masters/reefer-companies',
      '/^user\/masters\/locations/i' => 'masters/locations',
      '/^user\/masters\/vehicles\/status/i' => 'masters/vehicles-status',
      '/^user\/masters\/vehicles\/makers/i' => 'masters/vehicles-makers',
      '/^user\/masters\/vehicles\/models/i' => 'masters/vehicles-models',
      '/^user\/masters\/vehicles\/ownership-types/i' => 'masters/vehicles-ownership-types',
      '/^user\/masters\/vehicles\/colors/i' => 'masters/vehicles-colors',
      '/^user\/masters\/vehicles/i' => 'masters/vehicles',
      '/^user\/masters\/drivers\/checklist\/categories/i' => 'masters/checklist-categories',
      '/^user\/masters\/checklists\/drivers/i' => 'masters/checklists',
      '/^user\/masters\/trucks-document-types/i' => 'masters/trucks-document-types',
      '/^user\/masters\/trucks-documents/i' => 'masters/trucks-documents',
      '/^user\/masters\/trucks/i' => 'masters/trucks',
      '/^user\/masters\/trailers-document-types/i' => 'masters/trailers-document-types',
      '/^user\/masters\/trailers-documents/i' => 'masters/trailers-documents',
      '/^user\/masters\/trailers/i' => 'masters/trailers',
      '/^user\/masters\/route-types/i' => 'masters/route-types',
      '/^user\/masters\/driver-groups/i' => 'masters/driver-groups',
      '/^user\/masters\/driver-ppm-plans/i' => 'masters/driver-ppm-plans',
      '/^user\/masters\/driver-document-types/i' => 'masters/driver-document-types',
      '/^user\/masters\/drivers-documents/i' => 'masters/drivers-documents',
      '/^user\/masters\/drivers/i' => 'masters/drivers',
      '/^user\/masters\/trip-stop-type/i' => 'masters/trip-stop-types',
      '/^user\/masters\/users\/roles-groups/i' => 'masters/roles-groups',
      '/^user\/masters\/users/i' => 'masters/users',
      '/^user\/masters\/hierarchy/i' => 'masters/hierarchy',
      '/^user\/masters\/priorities/i' => 'masters/priorities',
      '/^user\/masters\/documents/i' => 'masters/documents',
      '/^user\/masters\/payment-modes/i' => 'masters/payment-modes',
      '/^user\/accounts\/trips/i' => 'accounts/trips',
      '/^user\/accounts\/drivers-payments/i' => 'accounts/drivers-payments',
      '/^user\/task-management\/tickets-stages/i' => 'task-management/tickets-stages',
      '/^user\/task-management\/ticket-priorities/i' => 'task-management/ticket-priorities',
      '/^user\/task-management\/ticket-notifications/i' => 'task-management/ticket-notifications',
      '/^user\/task-management\/tickets/i' => 'task-management/tickets',
      '/^user\/maintenance\/masters\/repair-order-class/i' => 'maintenance/masters/repair-order-class',
      '/^user\/maintenance\/masters\/repair-order-type/i' => 'maintenance/masters/repair-order-type',
      '/^user\/maintenance\/masters\/repair-order-stage/i' => 'maintenance/masters/repair-order-stage',
      '/^user\/maintenance\/masters\/repair-order-status/i' => 'maintenance/masters/repair-order-status',
      '/^user\/maintenance\/masters\/repair-order-category/i' => 'maintenance/masters/repair-order-category',
      '/^user\/maintenance\/masters\/repair-order-criticality-level/i' => 'maintenance/masters/repair-order-criticality-level',
      '/^user\/maintenance\/masters\/vendor/i' => 'maintenance/masters/vendor',
      '/^user\/maintenance\/masters\/job-work-type/i' => 'maintenance/masters/job-work-type',
      '/^user\/maintenance\/masters\/job-work/i' => 'maintenance/masters/job-work',
      '/^user\/maintenance\/masters\/preventive-maintenance/i' => 'maintenance/masters/preventive-maintenance',

      '/^user\/maintenance\/repair-orders/i' => 'maintenance/repair-orders',




      '/^user\/maintenance\/maintenance-dashboard-truck/i' => 'maintenance/maintenance-dashboard-truck',
      '/^user\/maintenance\/maintenance-dashboard-trailer/i' => 'maintenance/maintenance-dashboard-trailer',
      
      '/^user\/maintenance\/repair-order-followup/i' => 'maintenance/repair-order-followup',         
      '/^user\/maintenance\/work-orders/i' => 'maintenance/work-orders',
      '/^user\/maintenance\/preventive-maintenance-list-trailer/i' => 'maintenance/preventive-maintenance-list-trailer',
      '/^user\/maintenance\/preventive-maintenance-list-truck/i' => 'maintenance/preventive-maintenance-list-truck', 

      '/^user\/maintenance\/incident-entry/i' => 'maintenance/incident-entry',
      '/^user\/maintenance\/incident-followup/i' => 'maintenance/incident-followup',
      '/^user\/maintenance\/claim-entry/i' => 'maintenance/claim-entry',
      '/^user\/maintenance\/inspection-sheet-entry/i' => 'maintenance/inspection-sheet-entry',
      '/^user\/dispatch\/customers/i' => 'dispatch/customers',

      '/^user\/dispatch\/express-loads/i' => 'dispatch/express-loads',

      '/^user\/dispatch\/loads/i' => 'dispatch/loads',
      
      '/^user\/dispatch\/load-status/i' => 'dispatch/load-status',

      '/^user\/dispatch\/commodity-types/i' => 'dispatch/commodity-types',
      '/^user\/settings\/profile/i' => 'user/settings/profile',
      '/^error/'=> 'error-page'
    );
$r=[];
$r['status']=false;
$r['message']=null;
$r['response']=null;
$has_matching_controller=false;
foreach ($routes as $key => $value) {
  if(preg_match($key, $uri)){
    $has_matching_controller=true;
            //---check if  app_id is send
    if(isset($_POST['param'])){


//---check if any file is send
      if(isset($_FILES['file'])){
        define('PARAM_FILE', $_FILES['file']);
      }else{
        define('PARAM_FILE', 'dsfd');
      }
//---check if any file is send




      $param=json_decode($_POST['param'],true);
      define('PARAM', json_decode($_POST['param'],true));
      if(isset(PARAM['app_id'])){
        include_once APPROOT.'/models/validation/Validation.php';
        $Validation=new Validation;

                  //---check if the request is from valid app_id
        if($Validation->is_valid_app(PARAM['app_id'])['status']){

                    //---check if the controller file exists or not
          if(file_exists('../app/controllers/'.$value.'.php')){

                     //--check if the requested contller belons to user area data
            if(preg_match('/^user\/.*$/i', $uri)){

              if(preg_match('/^user\/login.*$/i', $uri)==false){

                if(isset(PARAM['user_key'])){

                  $is_valid_user=$Validation->is_valid_user(PARAM['user_key']);
                  if($is_valid_user['status']){
                    define('USER_ID',$is_valid_user['response']['user_id'] );
                    define('USER_PRIV', explode(',', $is_valid_user['response']['user_priv']));
                    require_once '../app/controllers/'.$value. '.php';

                  }else{
                    $r['message']="Invalid user key";
                    echo json_encode($r);                              
                  }

                }else{
                  $r['message']="Please provide user key";
                  echo json_encode($r);                          
                }
              }else{
                require_once '../app/controllers/'.$value. '.php'; 
              }

            }elseif (preg_match('/^driver\/.*$/i', $uri)) {




              if(preg_match('/^driver\/login.*$/i', $uri)==false){

                if(isset(PARAM['driver_key'])){

                  $is_valid_driver=$Validation->is_valid_driver(PARAM['driver_key']);
                  if($is_valid_driver['status']){
                    define('DRIVER_ID',$is_valid_driver['response']['driver_id'] );
                    require_once '../app/controllers/'.$value. '.php';
                  }else{
                    $r['message']="Invalid user key";
                    echo json_encode($r);                              
                  }

                }else{
                  $r['message']="Please provide user key";
                  echo json_encode($r);                          
                }
              }else{
                require_once '../app/controllers/'.$value. '.php'; 
              }
            }elseif (preg_match('/^api-o\/.*$/i', $uri)) {


              if(isset(PARAM['api_token'])){

                $is_valid_api_o_token=$Validation->is_valid_api_token(PARAM['api_token']);
                if($is_valid_api_o_token['status']){
                  define('TOKEN_ID',$is_valid_api_o_token['response']['token_id'] );
                  require_once '../app/controllers/'.$value. '.php';
                }else{
                  $r['message']="Invalid api token";
                  echo json_encode($r);                              
                }

              }else{
                $r['message']="Please provide your api token";
                echo json_encode($r);                          
              }


            } else {
              require_once '../app/controllers/'.$value. '.php';  
            }


          }else{
            $r['message']="Controller not available";
            echo json_encode($r);
          }

        }else{
          $r['message']="Invalid app id";
          echo json_encode($r);
        }
      }else{
        $r['message']="Please provide app id";
        echo json_encode($r);
      }
    }else{
     $r['message']="Please provide param";
     echo json_encode($r);
   }
   break;
   exit();
 }
}

if(!$has_matching_controller){
  $r['message']="Wrog api url";
  echo json_encode($r);
}


}
public function getUrl(){
  if(isset($_GET['url'])){
    $url = rtrim($_GET['url'], '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);
    $url = explode('/', $url);
    return $url;
  }
}
public function getUri(){
  if(isset($_GET['url'])){
    $url = rtrim($_GET['url'], '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);
    return $url;
  }
} 
}


