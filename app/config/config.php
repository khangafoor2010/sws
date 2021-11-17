<?php
session_start();

//date_default_timezone_set('Asia/Kolkata');
date_default_timezone_set('America/Los_Angeles');

               //connection checking
    //$con=mysqli_connect("localhost","root","","sigea_update_real");
     $con=mysqli_connect("localhost","sigealogistics_sws","Amrik@sigea786#","sigealogistics_sws");
    if(!$con){
        echo "data base not connected";
    }
    //APPROOT
    define('APPROOT', dirname(dirname(__FILE__)));

function getBaseUrl(){
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    $link = "https"; 
else
    $link = "http";  
$link .= "://";  
$link .= $_SERVER['HTTP_HOST']; 
$link .= '/public/';
return $link;
}



    //URLROOT (Dynamic links)
    define('URLROOT', getBaseUrl());
    define('LIVEURL', 'https://agile.sigealogistics.com/');
    define('DOCUMENTS_ROOT','http://documents.freongroup.com/files/' );
    //define('URLROOT', 'http://localhost:8080/freon/');
    
    function upload_document($param,$document){
 $curl = curl_init();
       // OPTIONS:
 $url='https://documents.freongroup.com/upload.php';
 $data=$param;
 $data['app_id']="myappkey";


 if(isset($document['tmp_name']) && isset($document['type']) && isset($document['name'])){

$file= new CURLFile($document['tmp_name'], $document['type'], $document['name']); 

 curl_setopt($curl, CURLOPT_URL, $url);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($curl, CURLOPT_POST, true);
 curl_setopt($curl, CURLOPT_POSTFIELDS,array('param' =>json_encode($data),'file'=>$file));
 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
       // EXECUTE:
 $result = curl_exec($curl);
 if(!$result){die("Connection Failure");}
 curl_close($curl);
 return $result;

}else{
    return 'Error in document upload file';
}

}

//---define max file size that can be uploaded in ( size in bytes);
define('MAX_FILE_UPLOAD_SIZE', 10000000);


define('MAIL_HEADER', "<html><head><style type='text/css'>*{box-sizing: border-box;padding: 0;margin: 0;}p{padding:4px;}body{background:#f1f1f1;}html{font-family: calibri}.bg-one{background: #042854}.bg-two{background: #2ecce8}a{text-decoration: none;color: black;}.link{color: blue;}main{width: 90%;max-width: 800px;margin:auto;border-radius: 8px;overflow: hidden;border:3px solid #042854;}.head{background:#042854;color: white;text-align: center;padding:3px 10px;display: block;}.footer{padding: 7px;text-align: center;background:#042854;color: white;}.content{padding: 20px 15px;background:white;}</style></head><body><main><section class='head bg-one'><h1>Freon Group</h1></section><div style='height: .4em;' class='bg-two'></div><section class='content'>");

define('MAIL_FOOTER', "</section><div style='height: .2em;' class='bg-two'></div><section class='footer bg-one'><a style='color:white;font-weight: bold;'href='https://freongroup.com'>www.freongroup.com</section></main></body></html>");

function samsara_api($url){
   $curl = curl_init();
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Authorization: Bearer 11hKxNdhehhGtTBm6AnbMClPu2AUjJ',
      'Content-Type: application/json',
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}
?>
