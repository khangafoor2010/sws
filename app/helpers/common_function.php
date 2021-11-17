<?php
  function getUrl(){
      if(isset($_GET['url'])){
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', $url);
        return $url;
      }
  }
  function getUri(){
      if(isset($_GET['url'])){
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $url;
      }
  } 
function to_alphanumeric($str,$seperator=""){
    return preg_replace( '/[^a-z0-9]/i', $seperator, $str);
}
function validate_int($value){
    return is_numeric($value);
}
function validate_float($value){
    return is_numeric($value);
}
function to_boolean($val){
  return filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}

function isValidDateFormat($date, $format = 'm/d/Y'){
$b = DateTime::createFromFormat($format, $date);
return $b && $b->format($format) === $date;
}
function isValidTimeFormat($date, $format = 'H:i'){
$b = DateTime::createFromFormat($format, $date);
return $b && $b->format($format) === $date;
}
function dateFromDbToFormat($dateFromDb){
        if($dateFromDb!="0000-00-00"){
          return date('m/d/Y',strtotime($dateFromDb));
        }else{
          return "";
        }
}

function dateTimeFromDbTimestamp($timestamp){
        if($timestamp!=""){
          return date('m/d/Y H:i',$timestamp);
        }else{
          return "";
        }
}

function dateFromDbDatetime($datetime){
        if(substr($datetime,0,10)!="0000-00-00"){
          return date('m/d/Y',strtotime($datetime));
        }else{
          return "";
        }
}

function timeFromDbTime($datetime){
        return date('H:i',strtotime($datetime));
}

function isValidMobileNumber($mobile){
          if (preg_match("/^[0-9]{10,10}$/",$mobile)){
            return true;
          }else{
            return false;
          }
}
function senetize_input($input){
  return mysqli_real_escape_string($GLOBALS['con'],$input);
}
/*
function GT_default_page(){
  header('location:'.URLROOT.'home/login');
}
function GT_login(){
  header('location:'.URLROOT.'home/login');
}
function GT_logout(){
  header('location:'.URLROOT.'home/logout');
}


function verifyuser($key){
 include_once APPROOT.'/models/home/Logs.php';
 $Logs=new Logs;
 return $Logs->IsLoggedIn($key);
}*/
?>


