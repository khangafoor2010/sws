<?php
/**
 *
 */
class LocationAddresses
{


	function isValidId($id){
		$id=senetize_input($id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `loc_id` from `location_addresses` WHERE `loc_id`='$id' AND `loc_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function addresses_quick_list($param){
			$status=false;
			$message=null;
			$response=null;

			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$q="SELECT `loc_id`, `loc_name`, `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`, `loc_phone_number`, `loc_fax_number`, `loc_email`, `loc_sales_representative`, `loc_customer_service_representative`, `loc_hours_of_operation`, `loc_remarks`, `loc_status`, `loc_added_on`, `loc_added_by`, `loc_updated_on`, `loc_updated_by`, `loc_deleted_on`, `loc_deleted_by` FROM `location_addresses` WHERE `loc_status`='ACT'";


			$order_by_type='ASC';
			if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){
				$order_by_type='DESC';
			}
			if(isset($param['sort_by'])){
				switch ($param['sort_by']) {
					case 'loc_id':
					$q .=" ORDER BY `loc_id`";
					break;	
					default:
					$q .=" ORDER BY `loc_id`";
					break;
				}
			}else{
				$q .=" ORDER BY `loc_id`";	
			}

		$qEx=mysqli_query($GLOBALS['con'],$q);

			$list=[];
			while ($rows=mysqli_fetch_assoc($qEx)) {
			array_push($list,[
				'id'=>$rows['loc_id'],
				'eid'=>$Enc->safeurlen($rows['loc_id']),
				'name'=>$rows['loc_name'],
				'address_line'=>$rows['loc_address_line'],
				'city'=>$rows['loc_city'],
				'state'=>$rows['loc_state'],
				'zipcode'=>$rows['loc_zipcode']
			]);
			}
			$response=[];

			$response['list']=$list;
			if(count($list)>0){
				$status=true;
			}else{
				$message="No records found";
			} 		

			$r=[];
			$r['status']=$status;
			$r['message']=$message;
			$r['response']=$response;
			return $r;	
		}
	function addresses_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0178', USER_PRIV)){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['name']) && $param['name']!=""){
				$name=senetize_input($param['name']);

				//---check code duplicacy
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `loc_name` FROM `location_addresses` WHERE `loc_status`='ACT' AND `loc_name`='$name'"))>0){
					$InvalidDataMessage="location name already exists";
					$dataValidation=false;
					goto ValidationChecker;					
				}

			}else{
				$InvalidDataMessage="Please provide location name";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['address_line']) && $param['address_line']!=""){
				$address_line=senetize_input($param['address_line']);
			}else{
				$InvalidDataMessage="Please address line";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['city']) && $param['city']!=""){
				$city=senetize_input($param['city']);
			}else{
				$InvalidDataMessage="Please city";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['state']) && $param['state']!=""){
				$state=senetize_input($param['state']);
			}else{
				$InvalidDataMessage="Please state";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['zipcode']) && $param['zipcode']!=""){
				$zipcode=senetize_input($param['zipcode']);
			}else{
				$InvalidDataMessage="Please zipcode";
				$dataValidation=false;
				goto ValidationChecker;
			}
			$phone_number="";
			if(isset($param['phone_number']) && $param['phone_number']!=""){
				$phone_number=$Enc->enc_mob($param['phone_number']);
			}
			$fax_number="";
			if(isset($param['fax_number']) && $param['fax_number']!=""){
				$fax_number=$Enc->enc_mob($param['fax_number']);
			}
			$email="";
			if(isset($param['email']) && $param['email']!=""){
				$email=$Enc->enc_mail($param['email']);
			}

			$sales_respresentative="";
			if(isset($param['sales_respresentative']) && $param['sales_respresentative']!=""){
				$sales_respresentative=senetize_input($param['sales_respresentative']);
			}
			$customer_service_respresentative="";
			if(isset($param['customer_service_respresentative']) && $param['customer_service_respresentative']!=""){
				$customer_service_respresentative=senetize_input($param['customer_service_respresentative']);
			}
			$hours_of_operation="";
			if(isset($param['hours_of_operation']) && $param['hours_of_operation']!=""){
				$hours_of_operation=senetize_input($param['hours_of_operation']);
			}
			$remarks="";
			if(isset($param['remarks']) && $param['remarks']!=""){
				$remarks=senetize_input($param['remarks']);
			}

			ValidationChecker:

			if($dataValidation){
				$time=time();
				$USERID=USER_ID;
 					///-----Generate New Unique Id
				$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `loc_id` FROM `location_addresses` ORDER BY `auto` DESC LIMIT 1");
				$get_last_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['loc_id']):'LC0000';
				$next_id='LC'.sprintf('%04d',(intval(substr($get_last_id,2))+1));

					///-----//Generate New Unique Id
				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `location_addresses`( `loc_id`, `loc_name`,  `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`, `loc_phone_number`, `loc_fax_number`, `loc_email`, `loc_sales_representative`, `loc_customer_service_representative`, `loc_hours_of_operation`, `loc_remarks`, `loc_status`, `loc_added_on`, `loc_added_by`) VALUES ('$next_id','$name','$address_line','$city','$state','$zipcode','$phone_number','$fax_number','$email','$sales_respresentative','$customer_service_respresentative','$hours_of_operation','$remarks','ACT','$time','$USERID')");
				if($insert){
					$status=true;
					$message="Added Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG.mysqli_error($GLOBALS['con']);
				}

			}else{
				$message=$InvalidDataMessage;
			}

		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}

	function addresses_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=500;
		$page=1;
		if(isset($param['page'])){
			$page=intval(senetize_input($param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `loc_id`, `loc_name`, `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`, `loc_phone_number`, `loc_fax_number`, `loc_email`, `loc_sales_representative`, `loc_customer_service_representative`, `loc_hours_of_operation`, `loc_remarks`, `loc_status`, `loc_added_on`, `loc_added_by`, `loc_updated_on`, `loc_updated_by`, `loc_deleted_on`, `loc_deleted_by` FROM `location_addresses` WHERE `loc_status`='ACT'";



//----Apply Filters starts


		if(isset($param['name']) && $param['name']!=""){
			$q .=" AND `loc_name`='".senetize_input($param['name'])."'";
		}

//-----Apply fitlers ends




		$order_by_type='ASC';
		if(isset($param['order_by_method']) && $param['order_by_method']=='descending'){
			$order_by_type='DESC';
		}
		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'loc_id':
				$q .=" ORDER BY `loc_id`";
				break;	
				default:
				$q .=" ORDER BY `loc_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `loc_id`";	
		}
		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			array_push($list,[
				'id'=>$rows['loc_id'],
				'eid'=>$Enc->safeurlen($rows['loc_id']),
				'name'=>$rows['loc_name'],
				'address_line'=>$rows['loc_address_line'],
				'city'=>$rows['loc_city'],
				'state'=>$rows['loc_state'],
				'zipcode'=>$rows['loc_zipcode'],
				'phone_number'=>$Enc->dec_mob($rows['loc_phone_number']),
				'fax_number'=>$Enc->dec_mob($rows['loc_fax_number']),
				'email'=>$Enc->dec_mail($rows['loc_email']),
				'sales_respresentative'=>$rows['loc_sales_representative'],
				'customer_service_respresentative'=>$rows['loc_customer_service_representative'],
				'hours_of_operation'=>$rows['loc_hours_of_operation'],
				'remarks'=>$rows['loc_remarks'],
			]);
		}
		$response=[];
		$response['total']=$totalRows;
		$response['totalRows']=$totalRows;
		$response['totalPages']=ceil($totalRows/$batch);
		$response['currentPage']=$page;
		$response['resultFrom']=$from+1;
		$response['resultUpto']=$range;
		$response['list']=$list;
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
		} 		

		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function addresses_details($param){
		$status=false;
		$message=null;
		$response=[];
		if(in_array('P0179', USER_PRIV)){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['eid']) &&  $param['eid']!=""){
				$location_id=$Enc->safeurlde($param['eid']);
			}else{
				$dataValidation=false;
				$InvalidDataMessage="Please provide cusetomer eid";
				goto ValidationChecker;
			}
			ValidationChecker:
			if($dataValidation){
				$q=mysqli_query($GLOBALS['con'],"SELECT `loc_id`, `loc_name`, `loc_address_line`, `loc_city`, `loc_state`, `loc_zipcode`, `loc_phone_number`, `loc_fax_number`, `loc_email`, `loc_sales_representative`, `loc_customer_service_representative`, `loc_hours_of_operation`, `loc_remarks`, `loc_status`, `loc_added_on`, `loc_added_by`, `loc_updated_on`, `loc_updated_by`, `loc_deleted_on`, `loc_deleted_by` FROM `location_addresses` WHERE `loc_status`='ACT' AND `loc_id`='$location_id'");
				if(mysqli_num_rows($q)==1){
					$status=true;
					$rows=mysqli_fetch_assoc($q);
					$response['details']=[
						'id'=>$rows['loc_id'],
						'eid'=>$Enc->safeurlen($rows['loc_id']),
						'name'=>$rows['loc_name'],
						'address_line'=>$rows['loc_address_line'],
						'city'=>$rows['loc_city'],
						'state'=>$rows['loc_state'],
						'zipcode'=>$rows['loc_zipcode'],
						'phone_number'=>$Enc->dec_mob($rows['loc_phone_number']),
						'fax_number'=>$Enc->dec_mob($rows['loc_fax_number']),
						'email'=>$Enc->dec_mail($rows['loc_email']),
						'sales_respresentative'=>$rows['loc_sales_representative'],
						'customer_service_respresentative'=>$rows['loc_customer_service_representative'],
						'hours_of_operation'=>$rows['loc_hours_of_operation'],
						'remarks'=>$rows['loc_remarks'],
					];

				}else{
					$message="Invalid location eid";
				}
			}else{
				$message=$InvalidDataMessage;
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	


	}	

	function addresses_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0164', USER_PRIV)){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$dataValidation=true;
			$InvalidDataMessage="";


			if(isset($param['update_eid'])){
				$update_id=$Enc->safeurlde($param['update_eid']);
			}else{
				$InvalidDataMessage="Please provide update eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['name']) && $param['name']!=""){
				$name=senetize_input($param['name']);

				//---check code duplicacy
				if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `loc_name` FROM `location_addresses` WHERE `loc_status`='ACT' AND `loc_name`='$name' AND NOT `loc_id`='$update_id'"))>0){
					$InvalidDataMessage="location name already exists";
					$dataValidation=false;
					goto ValidationChecker;					
				}

			}else{
				$InvalidDataMessage="Please provide location name";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['address_line']) && $param['address_line']!=""){
				$address_line=senetize_input($param['address_line']);
			}else{
				$InvalidDataMessage="Please address line";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['city']) && $param['city']!=""){
				$city=senetize_input($param['city']);
			}else{
				$InvalidDataMessage="Please city";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['state']) && $param['state']!=""){
				$state=senetize_input($param['state']);
			}else{
				$InvalidDataMessage="Please state";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['zipcode']) && $param['zipcode']!=""){
				$zipcode=senetize_input($param['zipcode']);
			}else{
				$InvalidDataMessage="Please zipcode";
				$dataValidation=false;
				goto ValidationChecker;
			}
			$phone_number="";
			if(isset($param['phone_number']) && $param['phone_number']!=""){
				$phone_number=$Enc->enc_mob($param['phone_number']);
			}
			$fax_number="";
			if(isset($param['fax_number']) && $param['fax_number']!=""){
				$fax_number=$Enc->enc_mob($param['fax_number']);
			}
			$email="";
			if(isset($param['email']) && $param['email']!=""){
				$email=$Enc->enc_mail($param['email']);
			}

			$sales_respresentative="";
			if(isset($param['sales_respresentative']) && $param['sales_respresentative']!=""){
				$sales_respresentative=senetize_input($param['sales_respresentative']);
			}
			$customer_service_respresentative="";
			if(isset($param['customer_service_respresentative']) && $param['customer_service_respresentative']!=""){
				$customer_service_respresentative=senetize_input($param['customer_service_respresentative']);
			}
			$hours_of_operation="";
			if(isset($param['hours_of_operation']) && $param['hours_of_operation']!=""){
				$hours_of_operation=senetize_input($param['hours_of_operation']);
			}
			$remarks="";
			if(isset($param['remarks']) && $param['remarks']!=""){
				$remarks=senetize_input($param['remarks']);
			}
			ValidationChecker:

			if($dataValidation){
				$time=time();
				$USERID=USER_ID;
				
					///-----//Generate New Unique Id
				$insert=mysqli_query($GLOBALS['con'],"UPDATE  `location_addresses` SET `loc_name`='$name',  `loc_address_line`='$address_line', `loc_city`='$city', `loc_state`='$state', `loc_zipcode`='$zipcode', `loc_phone_number`='$phone_number', `loc_fax_number`='$fax_number', `loc_email`='$email', `loc_sales_representative`='$sales_respresentative', `loc_customer_service_representative`='$customer_service_respresentative', `loc_hours_of_operation`='$hours_of_operation', `loc_remarks`='$remarks', `loc_updated_on`='$time', `loc_updated_by`='$USERID' WHERE `loc_id`='$update_id'");
				if($insert){
					$status=true;
					$message="Updated Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG.mysqli_error($GLOBALS['con']);
				}

			}else{
				$message=$InvalidDataMessage;
			}

		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}


/*

	function trucks_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0021', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `truck_id` FROM `trucks` WHERE `truck_id`='$delete_eid' AND NOT `truck_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `trucks` SET `truck_status`='DLT',`truck_deleted_on`='$time',`truck_deleted_by`='$USERID' WHERE `truck_id`='$delete_eid'");
					if($delete){
						$status=true;
						$message="Deleted Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Invalid eid";
				}
			}else{
				$message="Please Provide delete_eid";
			}
		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;

	}*/


}
?>