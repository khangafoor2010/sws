<?php

class PreventiveMaintenanceListTrailer
{
	
	function PreventiveMaintenanceListTrailer_list($param)
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

		$q="SELECT distinct `trailer_code`,`pm_id`, `pm_name`, `pm_mode`, `pm_value`, `trailer_enginehours` as `pm_currentreading` ,`pm_advancenotice`,`trailer_enginehours`-`pm_value` as `pm_difference`,(`trailer_enginehours`-`pm_value`)-`pm_value` as `pm_overdue`,`sm_work_order_header`.`engine_reading` as `pm_lastreading`, 
		case 
		when (`trailer_enginehours`-`pm_value`)>=`pm_value` then 'High' 
		when (`trailer_enginehours`-`pm_value`)>=`pm_advancenotice` and (`trailer_enginehours`-`pm_value`)<`pm_value` then 'Medium' 
		else 'Low' end as `pm_status` 
		FROM `trailers` 
		join `sm_preventive_maintenance` on `sm_preventive_maintenance`.`pm_unittypeid_fk` in('2','3') and `pm_mode`='Hours'
		LEFT JOIN `sm_work_order_detail` ON `sm_preventive_maintenance`.`pm_id`=`sm_work_order_detail`.`jobwork_id` 
		left join `sm_work_order_header` ON `sm_work_order_detail`.`jobwork_id`=`sm_work_order_header`.`workorder_id` and `trailers`.`trailer_id`=`sm_work_order_header`.`unit_id` and `sm_work_order_header`.unittype_id='2'
		WHERE `trailers`.`trailer_status`='ACT' AND NOT `trailers`.`trailer_id`='0'
		union ALL
		SELECT distinct `trailer_code`,`pm_id`, `pm_name`, `pm_mode`, `pm_value`, CURRENT_DATE () as `pm_currentreading` ,`pm_advancenotice`,`trailer_enginehours`-`pm_value` as `pm_difference`,(`trailer_enginehours`-`pm_value`)-`pm_value` as `pm_overdue`,`sm_work_order_header`.`engine_reading` as `pm_lastreading`, 
		case 
		when (`trailer_enginehours`-`pm_value`)>=`pm_value` then 'High' 
		when (`trailer_enginehours`-`pm_value`)>=`pm_advancenotice` and (`trailer_enginehours`-`pm_value`)<`pm_value` then 'Medium' 
		else 'Low' end as `pm_status` 
		FROM `trailers` 
		join `sm_preventive_maintenance` on `sm_preventive_maintenance`.`pm_unittypeid_fk` in('2','3') and `pm_mode`='Days'
		LEFT JOIN `sm_work_order_detail` ON `sm_preventive_maintenance`.`pm_id`=`sm_work_order_detail`.`jobwork_id` 
		left join `sm_work_order_header` ON `sm_work_order_detail`.`jobwork_id`=`sm_work_order_header`.`workorder_id` and `trailers`.`trailer_id`=`sm_work_order_header`.`unit_id` and `sm_work_order_header`.unittype_id='2'
		WHERE `trailers`.`trailer_status`='ACT' AND NOT `trailers`.`trailer_id`='0'
		ORDER BY `trailer_code`,`pm_name`";

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
			$row['id']=$rows['trailer_code'];
			$row['name']=$rows['pm_name'];
			$row['mode']=$rows['pm_mode'];
			$row['advancenotice']=$rows['pm_advancenotice'];
			$row['value']=$rows['pm_value'];
			$row['currentreading']=$rows['pm_currentreading'];
			$row['lastreading']=$rows['pm_lastreading'];
			$row['difference']=$rows['pm_difference'];
			$row['overdue']=$rows['pm_overdue'];
			$row['pmstatus']=$rows['pm_status'];
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