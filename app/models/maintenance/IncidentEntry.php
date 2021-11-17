<?php

class IncidentEntry
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `order_id` from `repairorder_header` WHERE `order_id`='$id'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function incident_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$batch=50;
		$page=1;
		if(isset($param['page']))
		{
			$page=intval(mysqli_real_escape_string($GLOBALS['con'],$param['page']));
		}
		if($page<1)
		{
			$page=1;
		}
		$from=$batch*($page-1);
		$range=$batch*$page;

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT  `order_id` as `order_id`, `order_date` as `date_created`, `status_name` as `status_name`, 
		`vehicle_name` as `asset_type`,
        case `A`.`doctype_id` when 1 then `truck_code` when 2 then `trailer_code` end as `asset_name`, 
		`driver_code` as `driver_id`, `driver_name_first` as `driver_name`, `type_name` as `type_name`,`stage_name` as `stage_name`, 
		`start_date` as `start_date`,`end_date` as `end_date`, `A`.`class_id` as `class_id`, `class_name` as `class_name`,
		`doctype_id` as `unit_type_id`, `A`.`status_id` as `status_id`, `asset_id` as `unit_id`, `A`.`type_id` as `type_id`, 
		`A`.`driver_id` as `driver_id`,`A`.`stage_id` as `stage_id`
		FROM `repairorder_header` as `A` 
		left join `repairorder_status` as `B` on `A`.`status_id`=`B`.`status_id`
		left join `repairorder_type` as `C` on `A`.`type_id`=`C`.`type_id` 
		left join `repairorder_class` as `D` on `A`.`class_id`=`D`.`class_id`
		left join `repairorder_stage` as `E` on `A`.`stage_id`=`E`.`stage_id`
		left join `vehicles` as `F` on `A`.`doctype_id`=`F`.`vehicle_id`
        left join `drivers` as `G` on `A`.`driver_id`=`G`.`driver_id`
        left join `trucks` as `H` on `A`.`asset_id`=`H`.`truck_id`
        left join `trailers` as `I` on `A`.`asset_id`=`I`.`trailer_id`
        WHERE `A`.`status`='ACT'";

		////---------------Apply filters

		if(isset($param['class_id']) && $param['class_id']!=""){
			$class_id=mysqli_real_escape_string($GLOBALS['con'],$param['class_id']);
			$q .=" AND `A`.`class_id` ='$class_id'";
		}
		if(isset($param['order_id']) && $param['order_id']!=""){
			$order_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_id']);
			$q .=" AND `A`.`order_id` LIKE '%$order_id%'";
		}
		if(isset($param['unit_type_id']) && $param['unit_type_id']!=""){
			$unit_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['unit_type_id']);
			$q .=" AND `A`.`class_id` ='$unit_type_id'";
		}
		if(isset($param['unit_id']) && $param['unit_id']!=""){
			$unit_id=mysqli_real_escape_string($GLOBALS['con'],$param['unit_id']);
			$q .=" AND `A`.`asset_id` ='$unit_id'";
		}
		if(isset($param['status_id']) && $param['status_id']!=""){
			$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q .=" AND `A`.`status_id` ='$status_id'";
		}
		if(isset($param['type_id']) && $param['type_id']!=""){
			$type_id=mysqli_real_escape_string($GLOBALS['con'],$param['type_id']);
			$q .=" AND `A`.`type_id` ='$type_id'";
		}
		if(isset($param['driver_id']) && $param['driver_id']!=""){
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q .=" AND `A`.`driver_id` ='$driver_id'";
		}
		if(isset($param['stage_id']) && $param['stage_id']!=""){
			$stage_id=mysqli_real_escape_string($GLOBALS['con'],$param['stage_id']);
			$q .=" AND `A`.`stage_id` ='$stage_id'";
		}
	
		/*
        if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `location_name`";
				break;		
				default:
				$q .=" ORDER BY `location_id`";
				break;
			}
		}else{
			$q .=" ORDER BY `location_id`";	
		}
		*/	
		////---------------/Apply filters

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) 
		{
			$row=[];
			$row['id']=$rows['order_id'];
			$row['eid']=$Enc->safeurlen($rows['order_id']);
			$row['date_created']=dateFromDbToFormat($rows['date_created']);
			$row['status_name']=$rows['status_name'];
			$row['class_name']=$rows['class_name'];
			$row['driver_id']=$rows['driver_id'];
			$row['driver_name']=$rows['driver_name'];
			$row['stage_name']=$rows['stage_name'];
			$row['asset_type']=$rows['asset_type'];
			$row['asset_name']=$rows['asset_name'];
			$row['type_name']=$rows['type_name'];
			$row['start_date']=dateFromDbToFormat($rows['start_date']);
			$row['end_date']=dateFromDbToFormat($rows['end_date']);
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
		if(count($list)>0)
		{
			$status=true;
		}
		else
		{
			$message="No records found";
		} 		
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function incident_add_new($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0008', USER_PRIV))
		{
			if(isset($param['order_class_id']) && $param['order_class_id']!="")
			{
				$USERID=USER_ID;
				$time=time();
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
			    //-----data validation starts
				$dataValidation=true;
				$InvalidDataMessage="";

				$order_date_raw=(isset($param['order_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_date']):'00/00/0000';
				$order_date=isValidDateFormat($order_date_raw)?date('Y-m-d', strtotime($order_date_raw)):'0000-00-00';

				$order_class_id=0;
				if(isset($param['order_class_id']) && $param['order_class_id']!="")
				{
					$order_class_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_class_id']);
				}

				$order_status_id=0;
				if(isset($param['order_status_id']) && $param['order_status_id']!="")
				{
					$order_status_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_status_id']);
				}

				$order_driver_id=0;
				if(isset($param['order_driver_id']) && $param['order_driver_id']!="")
				{
					$order_driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_driver_id']);
				}

				$order_type_id=0;
				if(isset($param['order_type_id']) && $param['order_type_id']!="")
				{
					$order_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_type_id']);
				}

				$order_stage_id=0;
				if(isset($param['order_stage_id']) && $param['order_stage_id']!="")
				{
					$order_stage_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_stage_id']);
				}

				$order_start_date_raw=(isset($param['order_start_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_start_date']):'00/00/0000';
				$order_start_date=isValidDateFormat($order_start_date_raw)?date('Y-m-d', strtotime($order_start_date_raw)):'0000-00-00';		

				$order_start_time="00:00";
				if(isset($param['order_start_time']) && $param['order_start_time']!="")
				{
					$order_start_time=mysqli_real_escape_string($GLOBALS['con'],$param['order_start_time']);
				}

				$order_end_date_raw=(isset($param['order_end_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_end_date']):'00/00/0000';
				$order_end_date=isValidDateFormat($order_end_date_raw)?date('Y-m-d', strtotime($order_end_date_raw)):'0000-00-00';

				$order_end_time="00:00";
				if(isset($param['order_end_time']) && $param['order_end_time']!="")
				{
					$order_end_time=mysqli_real_escape_string($GLOBALS['con'],$param['order_end_time']);
				}

				$order_unitype_id=0;
				if(isset($param['order_unitype_id']) && $param['order_unitype_id']!="")
				{
					$order_unitype_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_unitype_id']);
				}

				$order_unit_no=0;
				if(isset($param['order_unit_no']) && $param['order_unit_no']!="")
				{
					$order_unit_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_unit_no']);
				}

				$order_refdoctype_id=0;
				if(isset($param['order_refdoctype_id']) && $param['order_refdoctype_id']!="")
				{
					$order_refdoctype_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_refdoctype_id']);
				}

				$order_refdoc_no=0;
				if(isset($param['order_refdoc_no']) && $param['order_refdoc_no']!="")
				{
					$order_refdoc_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_refdoc_no']);
				}

				$order_contact_person=0;
				if(isset($param['order_contact_person']) && $param['order_contact_person']!="")
				{
					$order_contact_person=mysqli_real_escape_string($GLOBALS['con'],$param['order_contact_person']);
				}

				$order_contact_no=0;
				if(isset($param['order_contact_no']) && $param['order_contact_no']!="")
				{
					$order_contact_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_contact_no']);
				}
				//-----data validation ends

				////----------validate issue
				if(isset($param['stops']))
				{
					$stops=json_decode($param['stops'],true);
					$stops_array_senetized=[];
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['categoryid']))
						{
							$categoryid=mysqli_real_escape_string($GLOBALS['con'],$stop['categoryid']);
							$stop_item_senetized['categoryid']=$categoryid;
						}
						else
						{
							$InvalidDataMessage="Please provide category";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['criticalitylevelid']))
						{
							$criticalitylevelid=mysqli_real_escape_string($GLOBALS['con'],$stop['criticalitylevelid']);
							$stop_item_senetized['criticalitylevelid']=$criticalitylevelid;
						}
						else
						{
							$InvalidDataMessage="Please provide criticality level";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['jobworkid']))
						{
							$jobworkid=mysqli_real_escape_string($GLOBALS['con'],$stop['jobworkid']);
							$stop_item_senetized['jobworkid']=$jobworkid;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['issuereported']))
						{
							$issuereported=mysqli_real_escape_string($GLOBALS['con'],$stop['issuereported']);
							$stop_item_senetized['issuereported']=$issuereported;
						}
						else
						{
							$InvalidDataMessage="Please provide issue reported";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['issuedescription']))
						{
							$issuedescription=mysqli_real_escape_string($GLOBALS['con'],$stop['issuedescription']);
							$stop_item_senetized['issuedescription']=$issuedescription;
						}
						else
						{
							$InvalidDataMessage="Please provide issue description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation)
				{
 					///-----Generate New Unique Id
					$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `order_id` FROM `repairorder_header` ORDER BY `auto` DESC LIMIT 1");
					$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['order_id'])+1:0;
					///-----//Generate New Unique Id

					$insertheader=mysqli_query($GLOBALS['con'],"INSERT INTO `repairorder_header`(`order_id`, `order_date`, `doctype_id`, `status_id`, `asset_id`, `driver_id`, `type_id`, `stage_id`, `start_date`, `start_time`, `end_date`, `end_time`, `class_id`, `contactperson`, `contactnumber`, `refdocname`, `refdocno`,`status`) VALUES ('$next_id','$order_date','$order_unitype_id','$order_status_id','$order_unit_no','$order_driver_id','$order_type_id','$order_stage_id','$order_start_date','$order_start_time','$order_end_date','$order_end_time','$order_class_id','$order_contact_person','$order_contact_no','$order_refdoctype_id','$order_refdoc_no','ACT')");

					if($insertheader)
					{
						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `repairorder_header` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['order_id']:100000;
						*/
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `repairorder_detail`(`order_id`, `category_id_fk`, `criticalitylevel_id_fk`, `jobwork_id_fk`, `issue_reported`, `issue_description`) VALUES ('$next_id','".$stop_row['categoryid']."','".$stop_row['criticalitylevelid']."','".$stop_row['jobworkid']."','".$stop_row['issuereported']."','".$stop_row['issuedescription']."')");
							if(!$insertStop)
							{
								$stop_inserted=false;
							}
						}
						///---------//insert issue

						if($stop_inserted)
						{
							$status=true;
							$message=count($stops_array_senetized);
						}
						else
						{
							$message=SOMETHING_WENT_WROG.' step a';
						}
					}
					else
					{
						$message=SOMETHING_WENT_WROG.' step 2';
					}
				}
				else
				{
					$message=$InvalidDataMessage;
				}
			}
			else
			{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}
		else
		{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;
	}

	function incident_details($param)
	{
		$status=false;
		$message=null;
		$response=null;
		$details_for="";
		$runQuery=false;
		if(isset($param['details_for']))
		{
			$details_for=$param['details_for'];
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			$q="SELECT `order_id`, `order_date`, `doctype_id`, `status_id`, `asset_id`, `driver_id`, `type_id`, `stage_id`, `start_date`, `start_time`, `end_date`, `end_time`, `class_id`, `contactperson`, `contactnumber`, `refdocname`, `refdocno`,
				case `doctype_id` when 1 then `truck_vin_number` when 2 then `trailer_vin_number` end as `vin_no`
				FROM `repairorder_header` 
				left join `trucks` as `H` on `asset_id`=`H`.`truck_id`
				left join `trailers` as `I` on `asset_id`=`I`.`trailer_id`
				where `status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) 
			{
				case 'id':
				if(isset($param['details_for_id']))
				{
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$q .=" AND `order_id`='$details_for_id'";
					$runQuery=true;
				}
				else
				{
					$message="Please enter details_for_id";
				}
				break;	

				case 'eid':
				if(isset($param['details_for_eid']))
				{
					$details_for_eid=$Enc->safeurlde($param['details_for_eid']);
					$q .=" AND `order_id`='$details_for_eid'";
					$runQuery=true;
				}
				else
				{
					$message="Please enter details_for_eid";
				}
				break;	

				default:
				$message="Please provide valid details_for parameter";
				break;
			}
		}
		else
		{
			$message="Please provide details_for parameter";
		}
		$response=[];

		if($runQuery)
		{
			$get=mysqli_query($GLOBALS['con'],$q);
			if(mysqli_num_rows($get)==1)
			{
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['order_class_id']=$rows['class_id'];
				$row['order_no']=$rows['order_id'];
				$row['order_date']=dateFromDbToFormat($rows['order_date']);
				$row['order_status_id']=$rows['status_id'];
				$row['order_driver_id']=$rows['driver_id'];
				$row['order_type_id']=$rows['type_id'];
				$row['order_stage_id']=$rows['stage_id'];				
				$row['order_start_date']=dateFromDbToFormat($rows['start_date']);	
				$row['order_start_time']=$rows['start_time'];	
				$row['order_end_date']=dateFromDbToFormat($rows['end_date']);
				$row['order_end_time']=$rows['end_time'];	
				$row['order_contact_person']=$rows['contactperson'];
				$row['order_contact_number']=$rows['contactnumber'];
				$row['order_unitype_id']=$rows['doctype_id'];								
				$row['order_unit_no']=$rows['asset_id'];	
				$row['order_vin_no']=$rows['vin_no'];
				$row['order_refdoctype_id']=$rows['refdocname'];
				$row['order_refdoc_no']=$rows['refdocno'];

				//////////////////////////////////////////////////////////////
				$get_issue_q=mysqli_query($GLOBALS['con'],"SELECT `category_id_fk` as `category_id`, `criticalitylevel_id_fk` as `criticalitylevel_id`, `jobwork_id_fk` as `jobwork_id`, `issue_reported`, `issue_description` FROM `repairorder_detail` WHERE `order_id`='".$rows['order_id']."'");

				$issuelist=[];
				while ($rowslist=mysqli_fetch_assoc($get_issue_q)) 
				{
					$rowlist=[];
					$rowlist['category_id']=$rowslist['category_id'];
					$rowlist['criticalitylevel_id']=$rowslist['criticalitylevel_id'];
					$rowlist['jobwork_id']=$rowslist['jobwork_id'];
					$rowlist['issue_reported']=$rowslist['issue_reported'];
					$rowlist['issue_description']=$rowslist['issue_description'];
					array_push($issuelist,$rowlist);
				}
				////////////////////////////////////////////////////////////
				$get_note_q=mysqli_query($GLOBALS['con'],"SELECT `note_date`, `note_time`, `notes_remarks`, `next_note_date`, `note_by` FROM `repairorder_followup_detail` WHERE `repairorder_id_fk`='".$rows['order_id']."'");

				$followuplist=[];
				while ($rowslist_n=mysqli_fetch_assoc($get_note_q)) 
				{
					$rowlist_n=[];
					$rowlist_n['note_date']=dateFromDbToFormat($rowslist_n['note_date']);
					$rowlist_n['note_time']=$rowslist_n['note_time'];
					$rowlist_n['notes_remarks']=$rowslist_n['notes_remarks'];
					$rowlist_n['next_note_date']=dateFromDbToFormat($rowslist_n['next_note_date']);
					$rowlist_n['note_by']=$rowslist_n['note_by'];
					array_push($followuplist,$rowlist_n);
				}
				////////////////////////////////////////////////////////////
				$get_workorder_q=mysqli_query($GLOBALS['con'],"SELECT `A`.`workorder_id` as `workorder_id`, `A`.`repairorder_id_fk` as `repairorder_id`, `A`.`workorder_date` as `workorder_date`, `G`.`vehicle_name` as `unittype_name`, case `A`.`unittype_id` when 1 then `truck_code` when 2 then `trailer_code` end as `asset_name`, `A`.`engine_reading` as `engine_reading`, `D`.`vendor_name` as `vendor_name`, `E`.`location_name` as `state_name`, `F`.`location_name` as `city_name`,`A`.`vendor_phone` as `vendor_phone`, `A`.`workorder_amount` as `workorder_amount`, `A`.`technician_comments` as `technician_comments` 
		        FROM `workorder_header` as `A` left join `repairorder_header` as `B` on `A`.`repairorder_id_fk`=`B`.`order_id` left join `vendor_master` as `D` on `A`.`vendor_id`=`D`.`vendor_id` left join `locations` as `E` on `A`.`state_id`=`E`.`location_id` left join `locations` as `F` on `A`.`city_id`=`F`.`location_id` left join `vehicles` as `G` on `A`.`unittype_id`=`G`.`vehicle_id` left join `trucks` as `H` on `A`.`unit_id`=`H`.`truck_id` left join `trailers` as `I` on `A`.`unit_id`=`I`.`trailer_id` 
                WHERE `repairorder_id_fk`='".$rows['order_id']."'");

				$workorderlist=[];
				while ($rowslist_w=mysqli_fetch_assoc($get_workorder_q)) 
				{
					$rowlist_w=[];
					$rowlist_w['workorder_id']=$rowslist_w['workorder_id'];
					$rowlist_w['workorder_date']=dateFromDbToFormat($rowslist_w['workorder_date']);
					$rowlist_w['repairorder_id']=$rowslist_w['repairorder_id'];
					$rowlist_w['unittype_name']=$rowslist_w['unittype_name'];
					$rowlist_w['unit_name']=$rowslist_w['asset_name'];
					$rowlist_w['vendor_name']=$rowslist_w['vendor_name'];
					$rowlist_w['state_name']=$rowslist_w['state_name'];
					$rowlist_w['city_name']=$rowslist_w['city_name'];
					$rowlist_w['workorder_amount']=$rowslist_w['workorder_amount'];
					array_push($workorderlist,$rowlist_w);
				}
				////////////////////////////////////////////////////////////

				$row['issue_list']=$issuelist;
				$row['followup_list']=$followuplist;
				$row['workorder_list']=$workorderlist;
				$response['details']=$row;
			}
			else
			{
				$message="No records found";
			} 				
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}	

	function repairorderentry_update($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0010', USER_PRIV))
		{
			if(isset($param['update_eid']) && isset($param['update_eid']))
			{
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$update_id=$Enc->safeurlde($param['update_eid']);				
				$USERID=USER_ID;
				$time=time();

			    //-----data validation starts
 			    ///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;

				$dataValidation=true;
				$InvalidDataMessage="";

				$order_date_raw=(isset($param['order_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_date']):'00/00/0000';
				$order_date=isValidDateFormat($order_date_raw)?date('Y-m-d', strtotime($order_date_raw)):'0000-00-00';

				$order_class_id=0;
				if(isset($param['order_class_id']) && $param['order_class_id']!="")
				{
					$order_class_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_class_id']);
				}

				$order_status_id=0;
				if(isset($param['order_status_id']) && $param['order_status_id']!="")
				{
					$order_status_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_status_id']);
				}

				$order_driver_id=0;
				if(isset($param['order_driver_id']) && $param['order_driver_id']!="")
				{
					$order_driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_driver_id']);
				}

				$order_type_id=0;
				if(isset($param['order_type_id']) && $param['order_type_id']!="")
				{
					$order_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_type_id']);
				}

				$order_stage_id=0;
				if(isset($param['order_stage_id']) && $param['order_stage_id']!="")
				{
					$order_stage_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_stage_id']);
				}

				$order_start_date_raw=(isset($param['order_start_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_start_date']):'00/00/0000';
				$order_start_date=isValidDateFormat($order_start_date_raw)?date('Y-m-d', strtotime($order_start_date_raw)):'0000-00-00';		

				$order_start_time="00:00";
				if(isset($param['order_start_time']) && $param['order_start_time']!="")
				{
					$order_start_time=mysqli_real_escape_string($GLOBALS['con'],$param['order_start_time']);
				}

				$order_end_date_raw=(isset($param['order_end_date']))?mysqli_real_escape_string($GLOBALS['con'],$param['order_end_date']):'00/00/0000';
				$order_end_date=isValidDateFormat($order_end_date_raw)?date('Y-m-d', strtotime($order_end_date_raw)):'0000-00-00';

				$order_end_time="00:00";
				if(isset($param['order_end_time']) && $param['order_end_time']!="")
				{
					$order_end_time=mysqli_real_escape_string($GLOBALS['con'],$param['order_end_time']);
				}

				$order_unitype_id=0;
				if(isset($param['order_unitype_id']) && $param['order_unitype_id']!="")
				{
					$order_unitype_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_unitype_id']);
				}

				$order_unit_no=0;
				if(isset($param['order_unit_no']) && $param['order_unit_no']!="")
				{
					$order_unit_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_unit_no']);
				}

				$order_refdoctype_id=0;
				if(isset($param['order_refdoctype_id']) && $param['order_refdoctype_id']!="")
				{
					$order_refdoctype_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_refdoctype_id']);
				}

				$order_refdoc_no=0;
				if(isset($param['order_refdoc_no']) && $param['order_refdoc_no']!="")
				{
					$order_refdoc_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_refdoc_no']);
				}

				$order_contact_person=0;
				if(isset($param['order_contact_person']) && $param['order_contact_person']!="")
				{
					$order_contact_person=mysqli_real_escape_string($GLOBALS['con'],$param['order_contact_person']);
				}

				$order_contact_no=0;
				if(isset($param['order_contact_no']) && $param['order_contact_no']!="")
				{
					$order_contact_no=mysqli_real_escape_string($GLOBALS['con'],$param['order_contact_no']);
				}

				//-----data validation ends

				////----------validate issue
				$stops_array_senetized=[];
				if(isset($param['stops']))
				{
					$stops=json_decode($param['stops'],true);
					
					foreach ($stops as $stop) 
					{
						$stop_item_senetized=[];

					//----validate category
						if(isset($stop['categoryid']))
						{
							$categoryid=mysqli_real_escape_string($GLOBALS['con'],$stop['categoryid']);
							$stop_item_senetized['categoryid']=$categoryid;
						}
						else
						{
							$InvalidDataMessage="Please provide category";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['criticalitylevelid']))
						{
							$criticalitylevelid=mysqli_real_escape_string($GLOBALS['con'],$stop['criticalitylevelid']);
							$stop_item_senetized['criticalitylevelid']=$criticalitylevelid;
						}
						else
						{
							$InvalidDataMessage="Please provide criticality level";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['jobworkid']))
						{
							$jobworkid=mysqli_real_escape_string($GLOBALS['con'],$stop['jobworkid']);
							$stop_item_senetized['jobworkid']=$jobworkid;
						}
						else
						{
							$InvalidDataMessage="Please provide job work";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['issuereported']))
						{
							$issuereported=mysqli_real_escape_string($GLOBALS['con'],$stop['issuereported']);
							$stop_item_senetized['issuereported']=$issuereported;
						}
						else
						{
							$InvalidDataMessage="Please provide issue reported";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['issuedescription']))
						{
							$issuedescription=mysqli_real_escape_string($GLOBALS['con'],$stop['issuedescription']);
							$stop_item_senetized['issuedescription']=$issuedescription;
						}
						else
						{
							$InvalidDataMessage="Please provide issue description";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate issue reported
						array_push($stops_array_senetized,$stop_item_senetized);
					}
				}
				ValidationChecker:

				if($dataValidation)
				{
			    //--check if the code exists
					$update=mysqli_query($GLOBALS['con'],"UPDATE `repairorder_header` SET `order_date`='$order_date',`doctype_id`='$order_unitype_id',`status_id`='$order_status_id',`asset_id`='$order_unit_no',`driver_id`='$order_driver_id',`type_id`='$order_type_id',`stage_id`='$order_stage_id',`start_date`='$order_start_date',`start_time`='$order_start_time',`end_date`='$order_end_date',`end_time`='$order_end_time',`class_id`='$order_class_id',`contactperson`='$order_contact_person',`contactnumber`='$order_contact_no',`refdocname`='$order_refdoctype_id',`refdocno`='$order_refdoc_no' WHERE `order_id`='$update_id'");

					if($update)
					{
						$stop_deleted=true;
						$delete=mysqli_query($GLOBALS['con'],"DELETE FROM `repairorder_detail` WHERE `order_id`='$update_id'");
							if(!$delete)
							{
								$stop_deleted=false;
							}

						///---------insert issue
						/*
						$last_stop_id=mysqli_query($GLOBALS['con'],"SELECT `srno` FROM `tab_repairorder_detail` ORDER BY `auto` DESC LIMIT 1");
						$next_stop_id=(mysqli_num_rows($last_stop_id)==1)?mysqli_fetch_assoc($last_stop_id)['order_id']:100000;
						*/
						///-----//Generate New Unique Id
						$stop_inserted=true;
						foreach ($stops_array_senetized as $stop_row1) 
						{
							//$next_stop_id++;
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `repairorder_detail`(`order_id`, `category_id_fk`, `criticalitylevel_id_fk`, `jobwork_id_fk`, `issue_reported`, `issue_description`) VALUES ('$update_id','".$stop_row1['categoryid']."','".$stop_row1['criticalitylevelid']."','".$stop_row1['jobworkid']."','".$stop_row1['issuereported']."','".$stop_row1['issuedescription']."')");
							if(!$insertStop)
							{
								$stop_inserted=false;
							}
						}
						///---------//insert issue

						if($stop_inserted)
						{
							$status=true;
							$message="Updated Successfuly";
						}
						else
						{
							$message=SOMETHING_WENT_WROG;
						}
					}
					else
					{
						$message=SOMETHING_WENT_WROG;
					}
				}
				else
				{
					$message=$InvalidDataMessage;
				}
			}
			else
			{
				$message=REQUIRE_NECESSARY_FIELDS;
			}
		}
		else
		{
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