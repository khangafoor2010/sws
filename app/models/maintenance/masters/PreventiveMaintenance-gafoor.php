<?php

class PreventiveMaintenance
{
	function isValidId($id)
	{
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `pm_id` from `sm_preventive_maintenance` WHERE `pm_id`='$id' AND `pm_id_status`='ACT'"))==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function preventive_maintenance_list($param)
	{
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$get=mysqli_query($GLOBALS['con'],"SELECT `pm_id`, `pm_name`, `pm_mode`, `pm_value`,`pm_advance_notice`,`vehicle_name` as `pm_unittype` FROM `sm_preventive_maintenance` left outer join `vehicles` on `pm_unit_type_id_fk`=`vehicle_id` WHERE `pm_id_status`='ACT'");
		$list=[];
		while ($rows=mysqli_fetch_assoc($get))
		{
			$row=[];
			$row['id']=$rows['pm_id'];
			$row['eid']=$Enc->safeurlen($rows['pm_id']);
			$row['name']=$rows['pm_name'];
			$row['mode']=$rows['pm_mode'];
			$row['value']=$rows['pm_value'];
			$row['advancenotice']=$rows['pm_advance_notice'];
			$row['unittype']=$rows['pm_unittype'];
			array_push($list,$row);
		}
		$response=[];
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

	function preventive_maintenance_addnew($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0013', USER_PRIV))
		{
			if(isset($param['name']) && isset($param['unittype_id']))
			{
				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$mode=mysqli_real_escape_string($GLOBALS['con'],$param['mode']);
				$value=mysqli_real_escape_string($GLOBALS['con'],$param['value']);
				$advancenotice=mysqli_real_escape_string($GLOBALS['con'],$param['advancenotice']);
				$unittype_id=mysqli_real_escape_string($GLOBALS['con'],$param['unittype_id']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($unittype_id))
				{
 					//--check if the coutry exists in table or not
					$Validateunittype=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` from vehicles where `vehicle_status`='ACT' AND `vehicle_id`='$unittype_id'"));
					if($Validateunittype>0)
					{

					//--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` WHERE `pm_id_status`='ACT' AND `pm_name`='$name'");
						if(mysqli_num_rows($codeRows)<1)
						{

					///-----Generate New Unique Id
							$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` ORDER BY `pm_auto` DESC LIMIT 1");
							$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['pm_id'])+1:0;
					///-----//Generate New Unique Id

							$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `sm_preventive_maintenance`(`pm_id`, `pm_name`, `pm_value`, `pm_advance_notice`, `pm_mode`, `pm_unit_type_id_fk` , `pm_id_status`,`pm_added_on`,`pm_added_by`) VALUES ('$next_id','$name','$value','$advancenotice','$mode','$unittype_id','ACT','$time','$USERID')");
							if($insert)
							{
								$status=true;
								$message="Added Successfuly";	
							}else
							{
								$message=SOMETHING_WENT_WROG;
							}
						}
						else
						{
							$message="Type name already exists";
						}
					}
					else
					{
						$message="Please provide valid class id";
					}
				}
				else
				{
					$message="Please provide valid class id";
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

	function preventive_maintenance_details($param)
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
			
			$query="SELECT `pm_id`, `pm_name`, `pm_mode`, `pm_value`, `pm_advance_notice`, `pm_unit_type_id_fk` as `pm_unittype` FROM `sm_preventive_maintenance` WHERE `pm_id_status`='ACT'";

 			//--check, against what is the detail asked
			switch ($details_for) 
			{
				case 'id':
				if(isset($param['details_for_id']))
				{
					$details_for_id=mysqli_real_escape_string($GLOBALS['con'],$param['details_for_id']);
					$query .=" AND `pm_id`='$details_for_id'";
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
					$query .=" AND `pm_id`='$details_for_eid'";
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
			$get=mysqli_query($GLOBALS['con'],$query);
			if(mysqli_num_rows($get)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($get);
				$row=[];
				$row['name']=$rows['pm_name'];
				$row['mode']=$rows['pm_mode'];
				$row['value']=$rows['pm_value'];
				$row['advancenotice']=$rows['pm_advance_notice'];
				$row['unittype']=$rows['pm_unittype'];
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

	function preventive_maintenance_update($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0015', USER_PRIV))
		{
			if(isset($param['name']) && isset($param['unittype_id']) && isset($param['update_eid']))
			{
				$name=mysqli_real_escape_string($GLOBALS['con'],$param['name']);
				$mode=mysqli_real_escape_string($GLOBALS['con'],$param['mode']);
				$value=mysqli_real_escape_string($GLOBALS['con'],$param['value']);
				$advancenotice=mysqli_real_escape_string($GLOBALS['con'],$param['advancenotice']);
				$unittype_id=mysqli_real_escape_string($GLOBALS['con'],$param['unittype_id']);
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				
				$update_id=$Enc->safeurlde($param['update_eid']);
				$USERID=USER_ID;
				$time=time();

				if(is_numeric($unittype_id))
				{
 					//--check if the class exists in table or not
					$Validateunittype=mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `vehicle_id` from vehicles where `vehicle_status`='ACT' AND `vehicle_id`='$unittype_id'"));
					if($Validateunittype>0)
					{

			        //--check if the code exists
						$codeRows=mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` WHERE `pm_id_status`='ACT' AND `pm_name`='$name' AND NOT `pm_id`='$update_id'");
						if(mysqli_num_rows($codeRows)<1){
							$update=mysqli_query($GLOBALS['con'],"UPDATE `sm_preventive_maintenance` SET `pm_name`='$name',`pm_mode`='$mode',`pm_value`='$value',`pm_advance_notice`='$advancenotice',`pm_unit_type_id_fk`='$unittype_id',`pm_updated_on`='$time',`pm_updated_by`='$USERID' WHERE `pm_id`='$update_id'");
							if($update)
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
							$message="Name already exists";
						}
					}
					else
					{
						$message="Invalid class id";
					}
				}
				else
				{
					$message="Invalid class id";
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

	function preventive_maintenance_delete($param)
	{
		$status=false;
		$message=null;
		$response=null;
		if(in_array('P0091', USER_PRIV))
		{
			if(isset($param['delete_eid']))
			{
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;

				$delete_eid=$Enc->safeurlde($param['delete_eid']);				
				$USERID=USER_ID;
				$time=time();

			   //--check if the code exists
				$codeRows=mysqli_query($GLOBALS['con'],"SELECT `pm_id` FROM `sm_preventive_maintenance` WHERE `pm_id`='$delete_eid' AND NOT `pm_id_status`='DLT'");
				if(mysqli_num_rows($codeRows)==1)
				{
					$delete=mysqli_query($GLOBALS['con'],"UPDATE `sm_preventive_maintenance` SET `pm_id_status`='DLT',`pm_deleted_on`='$time',`pm_deleted_by`='$USERID' WHERE `pm_id`='$delete_eid'");
					
					if($delete)
					{
						$status=true;
						$message="Deleted Successfuly";	
					}else
					{
						$message=SOMETHING_WENT_WROG;
					}
				}else
				{
					$message="Invalid eid";
				}
			}else
			{
				$message="Please Provide delete_eid";
			}
		}else
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