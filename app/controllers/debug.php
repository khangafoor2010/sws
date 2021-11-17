<?php
$q=mysqli_query($GLOBALS['con'],"SELECT `auto`,`driver_id`,`driver_ssn_number` FROM `drivers` WHERE LENGTH(driver_password) = 4 ORDER BY auto");
while ($rows=mysqli_fetch_assoc($q)) {
	$id=$rows['driver_id'];
	$driver_password=password_hash(substr($rows['driver_ssn_number'], -4,4), PASSWORD_DEFAULT) ;
	$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_password`='$driver_password' WHERE `driver_id`='$id'");
echo mysqli_error($GLOBALS['con']);
if($update){
    echo "updated $id   $driver_password";
}else{
    echo 'not updated $id';
}
}

/*
include_once APPROOT.'/models/common/Enc.php';
$Enc=new Enc;
$q=mysqli_query($GLOBALS['con'],"SELECT `auto`,`driver_id`,`driver_ssn_number` FROM `drivers`");
echo 'YEW';
while ($rows=mysqli_fetch_assoc($q)) {
$id=$rows['auto'];
echo $rows['driver_id'];
echo '<br>'
echo $rows['driver_ssn_number'];
//$update=mysqli_query($GLOBALS['con'],"UPDATE `drivers` SET `driver_mobile_no`='$mobile' WHERE `auto`='$id'");
echo mysqli_error($GLOBALS['con']);

/*if($update){
    echo "updated $id";
}else{
    echo 'not updated $id';
}
}*/
?>