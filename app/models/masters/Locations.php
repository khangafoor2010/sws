<?php
/**
 *
 */
class Locations
{
	function isValidLocationCountryId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from `locations` WHERE `location_id`='$id' AND `location_status`='ACT' AND `location_type`='COUNTRY'"))==1){
			return true;
		}else{
			return false;
		}
	}
	function isValidLocationStateId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from `locations` WHERE `location_id`='$id' AND `location_status`='ACT' AND `location_type`='STATE'"))==1){
			return true;
		}else{
			return false;
		}
	}
	function isValidLocationCityId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from `locations` WHERE `location_id`='$id' AND `location_status`='ACT' AND `location_type`='CITY'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function isValidLocationZipId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from `locations` WHERE `location_id`='$id' AND `location_status`='ACT' AND `location_type`='ZIPCODE'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from `locations` WHERE `location_id`='$id' AND `location_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function countries_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0013', USER_PRIV)){


			if(isset($param['name'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$USERID=USER_ID;
				$time=time();


			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND location_type='COUNTRY'");
				if(mysqli_num_rows($codeRows)<1){
					

					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['location_id'])+1:1;
					///-----//Generate New Unique Id
					

					$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `locations`( `location_id`,`location_name`, `location_type`, `location_city_id_fk`, `location_state_id_fk`, `location_country_id_fk`, `location_status`, `location_added_on`, `location_added_by`) VALUES ('$next_id','$name','COUNTRY','0','0','0','ACT','$time','$USERID')");
					if($insert){
						$status=true;
						$message="Added Successfuly";	
					}else{
						$message=$next_id;
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
			
			$query="SELECT `location_id`, `location_name` FROM `locations` WHERE `location_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND location_id='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND location_id='$details_for_eid'";
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
				$row['name']=$rows['location_name'];
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


		$q="SELECT `location_id`, `location_name` FROM `locations` WHERE `location_status`='ACT' AND `location_type`='COUNTRY'";

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `location_name`";
				break;		
				default:
				$q .=" ORDER BY `location_name`";
				break;
			}
		}else{
			$q .=" ORDER BY `location_name`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['location_id'];
			$row['eid']=$Enc->safeurlen($rows['location_id']);
			$row['name']=$rows['location_name'];
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
		if(in_array('P0015', USER_PRIV)){


			if(isset($param['name']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_type`='COUNTRY' AND `location_name`='$name' AND NOT `location_id`='$update_id'");
				if(mysqli_num_rows($codeRows)<1){
					$insert=mysqli_query($GLOBALS['con'],"UPDATE `locations` SET `location_name`='$name',`location_updated_on`='$time',`location_updated_by`='$USERID' WHERE `location_id`='$update_id'");
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

	function delete_location($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0016', USER_PRIV)){
			if(isset($param['delete_eid'])){
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			//--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_id`='$delete_eid' AND NOT `location_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1){
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `locations` SET `location_status`='DLT',`location_deleted_on`='$time',`location_deleted_by`='$USERID' WHERE `location_id`='$delete_eid'");
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
		if(in_array('P0013', USER_PRIV)){


			if(isset($param['name']) && isset($param['country_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
				$USERID=USER_ID;
				$time=time();



				$mini_code="";
				if(isset($param['mini_code'])){
					$mini_code=mysqli_real_escape_string($GLOBALS['con'],$param['mini_code']);
				}

				if(is_numeric($country_id)){




 					//--check if the coutry exists in table or not
					$ValideateCountry=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$country_id' AND location_type='COUNTRY'"));
					if($ValideateCountry>0){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_country_id_fk`='$country_id' AND `location_type`='STATE'");
						if(mysqli_num_rows($codeRows)<1){


					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['location_id'])+1:0;
					///-----//Generate New Unique Id



							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `locations`(`location_id`,`location_name`,`location_mini_code`,`location_type`,`location_city_id_fk`,`location_state_id_fk`, `location_country_id_fk`, `location_status`, `location_added_on`, `location_added_by`) VALUES ('$next_id','$name',`$mini_code`,'STATE','0','0','$country_id','ACT','$time','$USERID')");
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
			
			$query="SELECT `state`.`location_id`,`state`.`location_name`,`state`.`location_mini_code`,`country`.`location_name` AS `country_name`,`country`.`location_id` AS `country_id` FROM `locations` AS `state` LEFT JOIN `locations` AS country ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `state`.`location_status`='ACT' AND `state`.`location_type`='State'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND `state`.`location_id`='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND `state`.`location_id`='$details_for_eid'";
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
				$row['name']=$rows['location_name'];
				$row['mini_code']=$rows['location_mini_code'];
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
		$batch=500;
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

		$q="SELECT `state`.`location_id`,`state`.`location_name`,`country`.`location_name` AS `country_name`,`state`.`location_mini_code` AS `state_mini_code` FROM `locations` AS `state` LEFT JOIN `locations` AS country ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `state`.`location_status`='ACT' AND `state`.`location_type`='State'";
		if(isset($param['country_id'])){
			$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
			$q.=" AND `state`.`location_country_id_fk`='$country_id'";
		}

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `location_name`";
				break;
				case 'country':
				$q .=" ORDER BY `location_name`";
				break; 						
				default:
				$q .=" ORDER BY `location_name`";
				break;
			}
		}else{
			$q .=" ORDER BY `location_id`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['location_id'];
			$row['eid']=$Enc->safeurlen($rows['location_id']);
			$row['name']=$rows['location_name'];
			$row['mini_code']=$rows['state_mini_code'];
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
		if(in_array('P0015', USER_PRIV)){


			if(isset($param['name']) && isset($param['country_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$country_id=mysqli_real_escape_string($GLOBALS['con'],$param['country_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
	
				$mini_code="";
				if(isset($param['mini_code'])){
					$mini_code=mysqli_real_escape_string($GLOBALS['con'],$param['mini_code']);
				}



				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($country_id)){

 					//--check if the coutry exists in table or not
					$ValideateCountry=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$country_id' AND location_type='COUNTRY'"));
					if($ValideateCountry>0){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_country_id_fk`='$country_id' AND `location_type`='COUNTRY' AND NOT `location_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `locations` SET `location_name`='$name',`location_mini_code`='$mini_code',`location_country_id_fk`='$country_id',`location_updated_on`='$time',`location_updated_by`='$USERID'WHERE `location_id`='$update_id'");
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


	function cities_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0013', USER_PRIV)){


			if(isset($param['name']) && isset($param['state_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($state_id)){

 					//--check if the state exists in table or not
					$ValideateState=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$state_id' AND location_type='STATE' AND location_status='ACT'"));
					if($ValideateState==1){

			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_state_id_fk`='$state_id' AND location_type='CITY'");
						if(mysqli_num_rows($codeRows)<1){


					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['location_id'])+1:0;
					///-----//Generate New Unique Id


							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `locations`(`location_id`,`location_name`, `location_type`,`location_city_id_fk`, `location_state_id_fk`,`location_country_id_fk`, `location_status`, `location_added_on`, `location_added_by`) VALUES ('$next_id','$name','CITY','0','$state_id','0','ACT','$time','$USERID')");
							if($insert){
								$status=true;
								$message="Added Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="City name already exists";
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
			
			$query="SELECT `city`.`location_id` AS `city_id`, `city`.`location_name` AS `city_name`,`state`.`location_id` AS `state_id`,`state`.`location_name` AS `state_name` ,`country`.`location_name` AS `country_name` FROM `locations` as `city` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` LEFT JOIN `locations` AS `country` ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `city`.`location_type`='CITY' AND `city`.`location_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND `city`.`location_id`='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND `city`.`location_id`='$details_for_eid'";
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
		$batch=5000;
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

		$q="SELECT `city`.`location_id` AS `city_id`, `city`.`location_name` AS `city_name`,`state`.`location_name` AS `state_name`,`state`.`location_mini_code` AS `state_mini_code` ,`country`.`location_name` AS `country_name` FROM `locations` as `city` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` LEFT JOIN `locations` AS `country` ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `city`.`location_type`='CITY' AND `city`.`location_status`='ACT'";

		if(isset($param['state_id'])){
			$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
			$q.=" AND `city`.`location_state_id_fk`='$state_id'";
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
				$q .=" ORDER BY `city_name`";
				break;
			}
		}else{
			$q .=" ORDER BY `city_name`";	
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
			$row['state_mini_code']=$rows['state_mini_code'];
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
		if(in_array('P0015', USER_PRIV)){


			if(isset($param['name']) && isset($param['state_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$state_id=mysqli_real_escape_string($GLOBALS['con'],$param['state_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($state_id)){

 					//--check if the state exists in table or not
					$ValideateState=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$state_id' AND location_type='STATE' AND location_status='ACT'"));
					if($ValideateState==1){

			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_state_id_fk`='$state_id' AND location_type='STATE' AND NOT `location_id`='$update_id'");

						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `locations` SET `location_name`='$name',`location_state_id_fk`='$state_id',`location_updated_on`='$time',`location_updated_by`='$USERID' WHERE `location_id`='$update_id'");
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



	function zipcodes_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0013', USER_PRIV)){


			if(isset($param['name']) && isset($param['city_id'])){
				$name=ucwords(mysqli_real_escape_string($GLOBALS['con'],$param['name']));
				$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($city_id)){

 					//--check if the city exists in table or not
					$ValideateCity=mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$city_id' AND `location_status`='ACT' AND `location_type`='CITY'");
					
					if(mysqli_num_rows($ValideateCity)==1){


			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_type`='ZIPCODE'");
						if(mysqli_num_rows($codeRows)<1){

					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` ORDER BY `auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['location_id'])+1:0;
					///-----//Generate New Unique Id



							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `locations`(`location_id`,`location_name`,`location_type`, `location_city_id_fk`,`location_state_id_fk`,`location_country_id_fk`, `location_status`, `location_added_on`, `location_added_by`) VALUES ('$next_id','$name','ZIPCODE','$city_id','0','0','ACT','$time','$USERID')");
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
			
			$query="SELECT `zipcode`.`location_id` AS `id`,`zipcode`.`location_name` AS `name`, `city`.`location_id` AS `city_id`, `city`.`location_name` AS `city_name`,`state`.`location_name` AS `state_name` ,`country`.`location_name` AS `country_name` FROM `locations` as `zipcode` LEFT JOIN `locations` AS `city` ON `city`.`location_id`=`zipcode`.`location_city_id_fk` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` LEFT JOIN `locations` AS `country` ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `zipcode`.`location_type`='ZIPCODE' AND `zipcode`.`location_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) {
				case 'id':
				if(isset($param['details_for_id'])){
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND `zipcode`.`location_id`='$details_for_id'";
					$runQuery=true;
				}else{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid'])){
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$query .=" AND  `zipcode`.`location_id`='$details_for_eid'";
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
				$row['name']=$rows['name'];
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
		$batch=2000;
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

		$q="SELECT `zipcode`.`location_id` AS `id`,`zipcode`.`location_name` AS `name`, `city`.`location_id` AS `city_id`, `city`.`location_name` AS `city_name`,`state`.`location_name` AS `state_name` ,`country`.`location_name` AS `country_name` FROM `locations` as `zipcode` LEFT JOIN `locations` AS `city` ON `city`.`location_id`=`zipcode`.`location_city_id_fk` LEFT JOIN `locations` AS `state` ON `state`.`location_id`=`city`.`location_state_id_fk` LEFT JOIN `locations` AS `country` ON `country`.`location_id`=`state`.`location_country_id_fk` WHERE `zipcode`.`location_type`='ZIPCODE' AND `zipcode`.`location_status`='ACT'";
		if(isset($param['city_id'])){
			$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
			$q.=" AND `city`.`location_id`='$city_id'";
		}

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `name`";
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
				$q .=" ORDER BY `zipcode`.`location_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `zipcode`.`location_id`";	
		}		 

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);
		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['id'];
			$row['eid']=$Enc->safeurlen($rows['id']);
			$row['name']=$rows['name'];
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
		if(in_array('P0015', USER_PRIV)){


			if(isset($param['name']) && isset($param['city_id']) && isset($param['update_eid'])){

				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$city_id=mysqli_real_escape_string($GLOBALS['con'],$param['city_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($city_id)){

 					//--check if the city exists in table or not
					$ValideateCity=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `location_id` from locations where location_id='$city_id' AND `location_status`='ACT' AND `location_type`='CITY'"));
					if($ValideateCity>0){
			//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `location_id` FROM `locations` WHERE `location_status`='ACT' AND `location_name`='$name' AND `location_type`='ZIPCODE' AND NOT `location_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$insert=mysqli_query($GLOBALS['con'],"UPDATE `locations` SET `location_name`='$name',`location_city_id_fk`='$city_id',`location_updated_on`='$time',`location_updated_by`='$USERID'WHERE `location_id`='$update_id'");
							if($insert){
								$status=true;
								$message="Updated Successfuly";	
							}else{
								$message=SOMETHING_WENT_WROG;
							}
						}else{
							$message="Zipcode name already exists";
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
}
?>