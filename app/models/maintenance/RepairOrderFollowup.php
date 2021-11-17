<?php

class RepairOrderFollowup
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `order_id` from `sm_repair_order_header` WHERE `order_id`='$id'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function repairorderfollowup_list($param)
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

		$q="SELECT  `order_id` as `order_id`, `order_date` as `date_created`, `status_name` as `status_name`,`vehicle_name` as `asset_type`,
        case `A`.`doctype_id` when 1 then `truck_code` when 2 then `trailer_code` end as `asset_name`, 
		`driver_code` as `driver_id`, `driver_name_first` as `driver_name`, `type_name` as `type_name`,`stage_name` as `stage_name`, 
		`start_date` as `start_date`,`end_date` as `end_date`, `A`.`class_id` as `class_id`, `class_name` as `class_name`,
		`doctype_id` as `unit_type_id`, `A`.`status_id` as `status_id`, `asset_id` as `unit_id`, `A`.`type_id` as `type_id`, 
		`A`.`driver_id` as `driver_id`,`A`.`stage_id` as `stage_id`
		FROM `sm_repair_order_header` as `A` 
		left join `sm_repair_order_status` as `B` on `A`.`status_id`=`B`.`status_id`
		left join `sm_repair_order_type` as `C` on `A`.`type_id`=`C`.`type_id` 
		left join `sm_repair_order_class` as `D` on `A`.`class_id`=`D`.`class_id`
		left join `sm_repair_order_stage` as `E` on `A`.`stage_id`=`E`.`stage_id`
		left join `vehicles` as `F` on `A`.`doctype_id`=`F`.`vehicle_id`
        left join `drivers` as `G` on `A`.`driver_id`=`G`.`driver_id`
        left join `trucks` as `H` on `A`.`asset_id`=`H`.`truck_id`
        left join `trailers` as `I` on `A`.`asset_id`=`I`.`trailer_id`
        WHERE `A`.`status`='ACT' and `A`.`status_id`='1'";

		////---------------Apply filters
		if(isset($param['class_id']) && $param['class_id']!=""){
			$class_id=mysqli_real_escape_string($GLOBALS['con'],$param['class_id']);
			$q .=" AND `A`.`class_id` ='$class_id'";
		}
		if(isset($param['unit_type_id']) && $param['unit_type_id']!=""){
			$unit_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['unit_type_id']);
			$q .=" AND `A`.`doctype_id` ='$unit_type_id'";
		}
		if(isset($param['type_id']) && $param['type_id']!=""){
			$type_id=mysqli_real_escape_string($GLOBALS['con'],$param['type_id']);
			$q .=" AND `A`.`type_id` ='$type_id'";
		}

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

	function repairorderfollowup_details($param)
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
				FROM `sm_repair_order_header` 
				left join `trucks` as `H` on `asset_id`=`H`.`truck_id`
				left join `trailers` as `I` on `asset_id`=`I`.`trailer_id`
				where 1=1";

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

				$get_issue_q=mysqli_query($GLOBALS['con'],"SELECT `note_date`, `note_time`, `notes_remarks`, `next_note_date`, `note_by` FROM `sm_repair_order_followup_detail` WHERE `repairorder_id_fk`='".$rows['order_id']."'");

				$issuelist=[];
				while ($rowslist=mysqli_fetch_assoc($get_issue_q)) 
				{
					$rowlist=[];
					$rowlist['note_date']=dateFromDbToFormat($rowslist['note_date']);
					$rowlist['note_time']=$rowslist['note_time'];
					$rowlist['notes_remarks']=$rowslist['notes_remarks'];
					$rowlist['next_note_date']=dateFromDbToFormat($rowslist['next_note_date']);
					$rowlist['note_by']=$rowslist['note_by'];
					array_push($issuelist,$rowlist);
				}
				////////////////////////////////////////////////////////////
				$row['issue_list']=$issuelist;
				$response['details']=$row;
			}
			else
			{
				$message="No records found";
			} 				
		}
		$r=[];
		$r['status']=$status;
		$r['message']="SELECT `note_date`, `note_time`, `notes_remarks`, `next_note_date`, `note_by` FROM `sm_repair_order_followup_detail` WHERE `repairorder_id_fk`='".$rows['order_id']."'";
		$r['response']=$response;
		return $r;	
	}	

	function repairorderfollowup_update($param)
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
						if(isset($stop['note_date']))
						{
							$note_date_raw=(isset($stop['note_date']))?mysqli_real_escape_string($GLOBALS['con'],$stop['note_date']):'00/00/0000';
				            $note_date=isValidDateFormat($note_date_raw)?date('Y-m-d', strtotime($note_date_raw)):'0000-00-00';
							
							//$note_date=mysqli_real_escape_string($GLOBALS['con'],$stop['note_date']);
							$stop_item_senetized['note_date']=$note_date;
						}
						else
						{
							$InvalidDataMessage="Please note date";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate category
					//----validate criticality level
						if(isset($stop['note_time']))
						{
							$note_time=mysqli_real_escape_string($GLOBALS['con'],$stop['note_time']);
							$stop_item_senetized['note_time']=$note_time;
						}
						else
						{
							$InvalidDataMessage="Please provide note time";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate criticality level
					//----validdate job work
						if(isset($stop['notes_remarks']))
						{
							$notes_remarks=mysqli_real_escape_string($GLOBALS['con'],$stop['notes_remarks']);
							$stop_item_senetized['notes_remarks']=$notes_remarks;
						}
						else
						{
							$InvalidDataMessage="Please provide notes remarks";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----/validate job work
					//----validdate issue reported
						if(isset($stop['next_note_date']))
						{
							$next_note_date_raw=(isset($stop['next_note_date']))?mysqli_real_escape_string($GLOBALS['con'],$stop['next_note_date']):'00/00/0000';
				            $next_note_date=isValidDateFormat($next_note_date_raw)?date('Y-m-d', strtotime($next_note_date_raw)):'0000-00-00';

							//$next_note_date=mysqli_real_escape_string($GLOBALS['con'],$stop['next_note_date']);
							$stop_item_senetized['next_note_date']=$next_note_date;
						}
						else
						{
							$InvalidDataMessage="Please provide next note date";
							$dataValidation=false;
							goto ValidationChecker;
						}
					//----validdate issue description
						if(isset($stop['note_by']))
						{
							$note_by=mysqli_real_escape_string($GLOBALS['con'],$stop['note_by']);
							$stop_item_senetized['note_by']=$note_by;
						}
						else
						{
							$InvalidDataMessage="Please provide note by";
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
						$stop_deleted=true;
						$delete=mysqli_query($GLOBALS['con'],"DELETE FROM `sm_repair_order_followup_detail` WHERE `repairorder_id_fk`='$update_id'");
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
							$insertStop=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_repair_order_followup_detail`(`repairorder_id_fk`, `note_date`, `note_time`, `notes_remarks`, `next_note_date`, `note_by`) VALUES ('$update_id','".$stop_row1['note_date']."','".$stop_row1['note_time']."','".$stop_row1['notes_remarks']."','".$stop_row1['next_note_date']."','".$stop_row1['note_by']."')");
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