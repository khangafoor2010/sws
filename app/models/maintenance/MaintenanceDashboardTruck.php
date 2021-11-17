<?php

class MaintenanceDashboardTruck
{
	
	function maintenancedashboardtruck_list($param)
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

		if(isset($param['class_id']) && $param['class_id']!="")
		{
			$class_id=mysqli_real_escape_string($GLOBALS['con'],$param['class_id']);
			$q .=" AND `A`.`class_id` ='$class_id'";
		}
		if(isset($param['order_id']) && $param['order_id']!="")
		{
			$order_id=mysqli_real_escape_string($GLOBALS['con'],$param['order_id']);
			$q .=" AND `A`.`order_id` LIKE '%$order_id%'";
		}
		if(isset($param['unit_type_id']) && $param['unit_type_id']!="")
		{
			$unit_type_id=mysqli_real_escape_string($GLOBALS['con'],$param['unit_type_id']);
			$q .=" AND `A`.`class_id` ='$unit_type_id'";
		}
		if(isset($param['unit_id']) && $param['unit_id']!="")
		{
			$unit_id=mysqli_real_escape_string($GLOBALS['con'],$param['unit_id']);
			$q .=" AND `A`.`asset_id` ='$unit_id'";
		}
		if(isset($param['status_id']) && $param['status_id']!="")
		{
			$status_id=mysqli_real_escape_string($GLOBALS['con'],$param['status_id']);
			$q .=" AND `A`.`status_id` ='$status_id'";
		}
		if(isset($param['type_id']) && $param['type_id']!="")
		{
			$type_id=mysqli_real_escape_string($GLOBALS['con'],$param['type_id']);
			$q .=" AND `A`.`type_id` ='$type_id'";
		}
		if(isset($param['driver_id']) && $param['driver_id']!="")
		{
			$driver_id=mysqli_real_escape_string($GLOBALS['con'],$param['driver_id']);
			$q .=" AND `A`.`driver_id` ='$driver_id'";
		}
		if(isset($param['stage_id']) && $param['stage_id']!="")
		{
			$stage_id=mysqli_real_escape_string($GLOBALS['con'],$param['stage_id']);
			$q .=" AND `A`.`stage_id` ='$stage_id'";
		}
	
		/*
        if(isset($param['sort_by'])){
			switch ($param['sort_by']) 
			{
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
}
?>