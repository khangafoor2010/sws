<?php
/**
 *
 */
class Locations
{

 		function isValidLocationStateId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `state_id` from `location_states` WHERE `state_id`='$id' AND `state_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}
	 function isValidLocationCityId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `city_id` from `location_cities` WHERE `city_id`='$id' AND `city_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}

	function isValidLocationZipId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `zip_id` from `location_zipcodes` WHERE `zip_id`='$id' AND `zip_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	}

	function countries_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P13', USER_PRIV)){


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `country_id` FROM `location_countries` WHERE `country_status`='ACT' AND `country_name`='$name'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `location_countries`( `country_name`, `country_status`, `country_added_on`, `country_added_by`) VALUES ('$name','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Country name already exists";
				}
			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function countries_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			
			$query="SELECT `country_id`, `country_name` FROM `location_countries` WHERE `country_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND country_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND country_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	

				
				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['name']=$rows['country_name'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}	

	function countries_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;


		$q="SELECT `country_id`, `country_name` FROM `location_countries` WHERE `country_status`='ACT'";

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `country_name`";
				break;		
				default:
				$q .=" ORDER BY `country_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `country_id`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['country_id'];
			$row['eid']=$Enc->safeurlen($rows['country_id']);
			$row['name']=$rows['country_name'];
			array_push($list,$row);
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


	function countries_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P15', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `country_id` FROM `location_countries` WHERE `country_status`='ACT' AND `country_name`='$name' AND NOT `country_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"UPDATE `location_countries` SET `country_name`='$name',`country_updated_on`='$time',`country_updated_by`='$USERID' WHERE `country_id`='$update_id'");
					if($insert){
						$status=true;
						$message="Updated Successfuly";	
					}else{
						$message=SOMETHING_WENT_WROG;
					}
				}else{
					$message="Country name already exists";
				}
			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function countries_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P16', USER_PRIV)){
			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `country_id` FROM `location_countries` WHERE `country_id`='$delete_eid' AND NOT `country_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `location_countries` SET `country_status`='DLT',`country_deleted_on`='$time',`country_deleted_by`='$USERID' WHERE `country_id`='$delete_eid'");
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

	}







	function states_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P13', USER_PRIV)){


			if(isset($param['name']) && isset($param['country_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($country_id)){

 					//--check if the coutry exists in table or not
					$ValideateCountry=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `country_id` from location_countries where country_id='$country_id'"));
					if($ValideateCountry>0){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `state_id` FROM `location_states` WHERE `state_status`='ACT' AND `state_name`='$name' AND `state_country_id_fk`='$country_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `location_states`(`state_name`, `state_country_id_fk`, `state_status`, `state_added_on`, `state_added_by`) VALUES ('$name','$country_id','ACT','$time','$USERID')");
							if($insert){
								$status=true;
								$message="Added Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="State name already exists";
						}
					}else{
						$message="Please provide valid coutry id";
					}
				}else{
					$message="Please provide valid coutry id";
				}
			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function states_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			
			$query="SELECT `state_id`, `state_name`,`country_id`,`country_name` FROM `location_states` LEFT JOIN `location_countries` ON `location_states`.`state_country_id_fk`=`location_countries`.`country_id` WHERE `state_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND state_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND state_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	

				
				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['name']=$rows['state_name'];
				$row['country']=$rows['country_name'];
				$row['country_id']=$rows['country_id'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}


	function states_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `state_id`, `state_name`,`country_name` FROM `location_states` LEFT JOIN `location_countries` ON `location_states`.`state_country_id_fk`=`location_countries`.`country_id` WHERE `state_status`='ACT'";
		if(isset($param['country_id'])){
			$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
			$q.=" AND `state_country_id_fk`='$country_id'";
		}

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `state_name`";
				break;
				case 'country':
				$q .=" ORDER BY `country_name`";
				break; 						
				default:
				$q .=" ORDER BY `state_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `state_id`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['state_id'];
			$row['eid']=$Enc->safeurlen($rows['state_id']);
			$row['name']=$rows['state_name'];
			$row['country']=$rows['country_name'];
			array_push($list,$row);
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



	function states_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P15', USER_PRIV)){


			if(isset($param['name']) && isset($param['country_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($country_id)){

 					//--check if the coutry exists in table or not
					$ValideateCountry=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `country_id` from location_countries where country_id='$country_id'"));
					if($ValideateCountry>0){
			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `state_id` FROM `location_states` WHERE `state_status`='ACT' AND `state_name`='$name' AND `state_country_id_fk`='$country_id' AND NOT `state_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `location_states` SET `state_name`='$name',`state_country_id_fk`='$country_id',`state_updated_on`='$time',`state_updated_by`='$USERID'WHERE `state_id`='$update_id'");
							if($insert){
								$status=true;
								$message="Updated Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="State name already exists";
						}
					}else{
						$message="Invalid country id";
					}
				}else{
					$message="Invalid country id";
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function states_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P16', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `state_id` FROM `location_states` WHERE `state_id`='$delete_eid' AND NOT `state_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `location_states` SET `state_status`='DLT',`state_deleted_on`='$time',`state_deleted_by`='$USERID' WHERE `state_id`='$delete_eid'");
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

	}

	function cities_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P13', USER_PRIV)){


			if(isset($param['name']) && isset($param['state_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($state_id)){

 					//--check if the state exists in table or not
					$ValideateState=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `state_id` from location_states where state_id='$state_id'"));
					if($ValideateState>0){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `city_id` FROM `location_cities` WHERE `city_status`='ACT' AND `city_name`='$name' AND `city_state_id_fk`='$state_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `location_cities`(`city_name`, `city_state_id_fk`, `city_status`, `city_added_on`, `city_added_by`) VALUES ('$name','$state_id','ACT','$time','$USERID')");
							if($insert){
								$status=true;
								$message="Added Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="State name already exists";
						}
					}else{
						$message="Please provide valid coutry id";
					}
				}else{
					$message="Please provide valid coutry id";
				}
			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function cities_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			
			$query="SELECT `city_id`, `city_name`,`state_name`,`state_id`,`country_name` FROM `location_cities` LEFT JOIN `location_states` ON `location_cities`.`city_state_id_fk`=`location_states`.`state_id` LEFT JOIN `location_countries` ON `location_states`.`state_country_id_fk`=`location_countries`.`country_id` WHERE `city_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND city_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND city_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	

				
				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['name']=$rows['city_name'];
				$row['state']=$rows['state_name'];
				$row['state_id']=$rows['state_id'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}


	function cities_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `city_id`, `city_name`,`state_name`,`country_name` FROM `location_cities` LEFT JOIN `location_states` ON `location_cities`.`city_state_id_fk`=`location_states`.`state_id` LEFT JOIN `location_countries` ON `location_states`.`state_country_id_fk`=`location_countries`.`country_id` WHERE `city_status`='ACT'";

		if(isset($param['state_id'])){
			$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
			$q.=" AND `city_state_id_fk`='$state_id'";
		}




		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `city_name`";
				break; 				
				case 'state':
				$q .=" ORDER BY `state_name`";
				break;
				case 'country':
				$q .=" ORDER BY `country_name`";
				break; 						
				default:
				$q .=" ORDER BY `state_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `state_id`";	
		}



		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['city_id'];
			$row['eid']=$Enc->safeurlen($rows['city_id']);
			$row['name']=$rows['city_name'];
			$row['state']=$rows['state_name'];
			$row['country']=$rows['country_name'];
			array_push($list,$row);
		}
		if(count($list)>0){
			$status=true;
		}else{
			$message="No records found";
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



	function cities_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P15', USER_PRIV)){


			if(isset($param['name']) && isset($param['state_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($state_id)){

 					//--check if the coutry exists in table or not
					$ValideateState=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `state_id` from location_states where state_id='$state_id'"));
					if($ValideateState>0){
			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `city_id` FROM `location_cities` WHERE `city_status`='ACT' AND `city_name`='$name' AND `city_state_id_fk`='$state_id' AND NOT `city_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `location_cities` SET `city_name`='$name',`city_state_id_fk`='$state_id',`city_updated_on`='$time',`city_updated_by`='$USERID'WHERE `city_id`='$update_id'");
							if($insert){
								$status=true;
								$message="Updated Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="State name already exists";
						}
					}else{
						$message="Invalid country id";
					}
				}else{
					$message="Invalid country id";
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function cities_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P16', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `city_id` FROM `location_cities` WHERE `city_id`='$delete_eid' AND NOT `city_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `location_cities` SET `city_status`='DLT',`city_deleted_on`='$time',`city_deleted_by`='$USERID' WHERE `city_id`='$delete_eid'");
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

	}



	function zipcodes_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P13', USER_PRIV)){


			if(isset($param['name']) && isset($param['city_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($city_id)){

 					//--check if the state exists in table or not
					$ValideateCity=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `city_id` from location_cities where city_id='$city_id'"));
					if($ValideateCity>0){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `zip_id` FROM `location_zipcodes` WHERE `zip_status`='ACT' AND `zip_name`='$name'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `location_zipcodes`(`zip_name`, `zip_city_id_fk`, `zip_status`, `zip_added_on`, `zip_added_by`) VALUES ('$name','$city_id','ACT','$time','$USERID')");
							if($insert){
								$status=true;
								$message="Added Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="Zip Code already exists";
						}
					}else{
						$message="Please provide valid coutry id";
					}
				}else{
					$message="Please provide valid coutry id";
				}
			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function zipcodes_details($param){
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for'])){
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			
			$query="SELECT `zip_id`, `zip_name`,`city_id`,`city_name`,`state_name`,`country_name` FROM `location_zipcodes` LEFT JOIN `location_cities` ON `location_cities`.`city_id`=`location_zipcodes`.`zip_city_id_fk` LEFT JOIN `location_states` ON `location_states`.`state_id`=`location_cities`.`city_state_id_fk` LEFT JOIN `location_countries` ON `location_countries`.`country_id`=`location_states`.`state_country_id_fk` WHERE `zip_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND zip_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND zip_id='$details_for_eid'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_eid";
				}
				break;	

				
				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}else{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery){
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['name']=$rows['zip_name'];
				$row['city_id']=$rows['city_id'];
				$row['city']=$rows['city_name'];
				$row['state']=$rows['state_name'];
				$row['country']=$rows['country_name'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 				
		}


		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	

		
	}


	function zipcodes_list($param){
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page'])){
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));

		}
		if($page<1){
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `zip_id`, `zip_name`,`city_name`,`state_name`,`country_name` FROM `location_zipcodes` LEFT JOIN `location_cities` ON `location_cities`.`city_id`=`location_zipcodes`.`zip_city_id_fk` LEFT JOIN `location_states` ON `location_states`.`state_id`=`location_cities`.`city_state_id_fk` LEFT JOIN `location_countries` ON `location_countries`.`country_id`=`location_states`.`state_country_id_fk` WHERE `zip_status`='ACT'";
		if(isset($param['city_id'])){
			$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
			$q.=" AND `zip_city_id_fk`='$city_id'";
		}

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `zip_name`";
				break; 				
				case 'city':
				$q .=" ORDER BY `city_name`";
				break;
				case 'state':
				$q .=" ORDER BY `state_name`";
				break;
				case 'country':
				$q .=" ORDER BY `country_name`";
				break; 						
				default:
				$q .=" ORDER BY `state_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `state_id`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['zip_id'];
			$row['eid']=$Enc->safeurlen($rows['zip_id']);
			$row['name']=$rows['zip_name'];
			$row['city']=$rows['city_name'];
			$row['state']=$rows['state_name'];
			$row['country']=$rows['country_name'];
			array_push($list,$row);
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



	function zipcodes_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P15', USER_PRIV)){


			if(isset($param['name']) && isset($param['city_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($city_id)){

 					//--check if the coutry exists in table or not
					$ValideateCity=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `city_id` from location_cities where city_id='$city_id'"));
					if($ValideateCity>0){
			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `zip_id` FROM `location_zipcodes` WHERE `zip_status`='ACT' AND `zip_name`='$name' AND `zip_city_id_fk`='$city_id' AND NOT `zip_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `location_zipcodes` SET `zip_name`='$name',`zip_city_id_fk`='$city_id',`zip_updated_on`='$time',`zip_updated_by`='$USERID'WHERE `zip_id`='$update_id'");
							if($insert){
								$status=true;
								$message="Updated Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="State name already exists";
						}
					}else{
						$message="Invalid country id";
					}
				}else{
					$message="Invalid country id";
				}

			}else{
				$message=REQUIRE_NECESSARY_FIELDS;
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

	function zipcodes_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P16', USER_PRIV)){


			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `zip_id` FROM `location_zipcodes` WHERE `zip_id`='$delete_eid' AND NOT `zip_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `location_zipcodes` SET `zip_status`='DLT',`zip_deleted_on`='$time',`zip_deleted_by`='$USERID' WHERE `zip_id`='$delete_eid'");
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

	}

}
?>