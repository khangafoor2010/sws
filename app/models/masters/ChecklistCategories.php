<?php
class ChecklistCategories
{
	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `category_id` from `checklist_categories` WHERE `category_id`='$id' AND `category_status`='ACT' "))==1){
			return true;
		}else{
			return false;
		}
	} 	
function checklist_category_list($param){
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



		if(isset($param['related_to'])){


		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `category_id`, `category_related_to`, `category_name`, `category_status`, `category_added_on`, `category_added_by`, `category_updated_on`, `category_updated_by`, `category_deleted_on`, `category_deleted_by` FROM `checklist_categories` WHERE `category_status`='ACT'";




		$q .=" ORDER BY `category_name`";

		$totalRows=mysqli_num_rows(mysqli_query($GLOBALS['con'],$q));
		$q .=" limit $from, $range";
		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['category_id'];
			$row['eid']=$Enc->safeurlen($rows['category_id']);
			$row['name']=$rows['category_name'];

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


		}else{
			$message="Please provide releted to value";
		}





		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
}
}
?>