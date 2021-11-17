<?php
/**
 * 
 */
class Hierarchy
{
	function isValidId($id){
		$id=mysqli_real_escape_string($GLOBALS['con'],$id);
		if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `level_id` from `hierarchy` WHERE `level_id`='$id' AND `level_status`='ACT'"))==1){
			return true;
		}else{
			return false;
		}
	}

	function user_children_levels($level_id_array)
	{
		$tree=[];
		$parent_array=$level_id_array;
		while(count($parent_array)>0){
			foreach ($parent_array as $pa) {
				$child_array=[];
				$get_childs_q=mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` WHERE `level_status`='ACT' AND `level_parent_level_id_fk` ='$pa'");
				while($ca=mysqli_fetch_assoc($get_childs_q)){
					array_push($tree,$ca['level_id']);	
					array_push($parent_array,$ca['level_id']);	
				}
				if (($key = array_search($pa, $parent_array)) !== false) {
					unset($parent_array[$key]);
				}
			}	
		}
		return $tree;		
	}

	function user_level_children(){

		//---select the levels user belongs to;
		$levels=[];
		$q=mysqli_query($GLOBALS['con'],"SELECT `huj_level_id_fk`  FROM `hierarchy_user_junction` WHERE `huj_user_id_fk`='".USER_ID."' AND `huj_status`='ACT'");
		while($res=mysqli_fetch_assoc($q)){
			array_push($levels,$res['huj_level_id_fk']);
		}

		return $this->level_children($levels);

	}

	function levels_parents($level_id_array)
	{
		$tree=[];
		$children_array=$level_id_array;

$dumy="";
		//while(count($children_array)>0){
			foreach ($children_array as $ca) {
				$get_childs_q=mysqli_query($GLOBALS['con'],"SELECT  `level_parent_level_id_fk` FROM `hierarchy` WHERE `level_status`='ACT' AND `level_id` ='$ca'");
				while($ca=mysqli_fetch_assoc($get_childs_q)){
					array_push($tree,$ca['level_parent_level_id_fk']);	
					array_push($children_array,$ca['level_parent_level_id_fk']);	
				}
				if (($key = array_search($ca, $children_array)) !== false) {
					unset($children_array[$key]);
					$dumy.=" YES";
				}else{
					$dumy.=" NO".array_search($ca, $children_array);
				}
			}	
		


		return ['tree'=>$tree,'children'=>$children_array,'dumy'=>$dumy];		
	}

	function levels_add_new($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){

			$USERID=USER_ID;
			$time=time();


			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
			$dataValidation=true;
			$InvalidDataMessage="";


			if(isset($param['parent_id'])){
				$parent_id=senetize_input($param['parent_id']);

				if(!$this->isValidId($parent_id)){
					$InvalidDataMessage="Invalid parent id";
					$dataValidation=false;
					goto ValidationChecker;
				}

			}else{
				$InvalidDataMessage="Please provide parent id";
				$dataValidation=false;
				goto ValidationChecker;
			}


			if(isset($param['name'])){
				$name=senetize_input($param['name']);
			}else{
				$InvalidDataMessage="Please provide level name";
				$dataValidation=false;
				goto ValidationChecker;
			}

//------check duplicacy of name with same parent id

			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` WHERE `level_status`='ACT' AND `level_parent_level_id_fk`='$parent_id' AND `level_name`='$name'"))>0){
				$InvalidDataMessage="$name already exists";
				$dataValidation=false;
				goto ValidationChecker;
			}


			ValidationChecker:
			if($dataValidation){

					///-----Generate New Unique Id
				$get_last_id=mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` ORDER BY `auto` DESC LIMIT 1");
				$next_id=(mysqli_num_rows($get_last_id)==1)?(mysqli_fetch_assoc($get_last_id)['level_id'])+1:0;
					///-----//Generate New Unique Id


				$insert=mysqli_query($GLOBALS['con'],"INSERT INTO `hierarchy`(`level_id`, `level_name`, `level_parent_level_id_fk`, `level_status`, `level_added_on`, `level_added_by`) VALUES ('$next_id','$name','$parent_id','ACT','$time','$USERID')");
				if($insert){
					$status=true;
					$message="Added Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
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


	function levels_list($param){
		$status=false;
		$message=null;
		$response=null;
		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;

		$q="SELECT `level`.`level_id` AS `level_id`,`level`.`level_name` AS `level_name`, `parent`.`level_id` AS `parent_level_id`, `parent`.`level_name` AS `parent_level_name` FROM `hierarchy` AS `level` LEFT JOIN `hierarchy` AS `parent` ON `level`.`level_parent_level_id_fk`=`parent`.`level_id` WHERE `level`.`level_status`='ACT' AND NOT `level`.`level_type`='ROOT'";

		if(isset($param['sort_by'])){
			switch ($param['sort_by']) {
				case 'name':
				$q .=" ORDER BY `level`.`level_name`";
				break;
				case 'parent':
				$q .=" ORDER BY `parent`.`level_name`";
				break;						
				default:
				$q .=" ORDER BY `level`.`level_id`";	
				break;
			}
		}else{
			$q .=" ORDER BY `level`.`level_id`";	
		}

		$qEx=mysqli_query($GLOBALS['con'],$q);

		$list=[];
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['id']=$rows['level_id'];
			$row['eid']=$Enc->safeurlen($rows['level_id']);
			$row['name']=$rows['level_name'];
			$row['parent']=($rows['parent_level_name']!=NULL)?$rows['parent_level_name']:'';
			array_push($list,$row);
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
	function levels_details($param){
		$status=false;
		$message=null;
		$response=[];
		if(isset($param['eid'])){
			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;
			$level_id=$Enc->safeurlde($param['eid']);
			
			$q=mysqli_query($GLOBALS['con'],"SELECT `level`.`level_id` AS `level_id`,`level`.`level_name` AS `level_name`, `parent`.`level_id` AS `parent_level_id`, `parent`.`level_name` AS `parent_level_name` FROM `hierarchy` AS `level` LEFT JOIN `hierarchy` AS `parent` ON `level`.`level_parent_level_id_fk`=`parent`.`level_id` WHERE `level`.`level_status`='ACT' AND `level`.`level_id`='$level_id'");
			if(mysqli_num_rows($q)==1){
				$status=true;
				$rows=mysqli_fetch_assoc($q);
				$row=[];
				$row['id']=$rows['level_id'];
				$row['eid']=$Enc->safeurlen($rows['level_id']);
				$row['name']=$rows['level_name'];
				$row['parent_id']=$rows['parent_level_id'];
				$response['details']=$row;
			}else{
				$message="No records found";
			} 


		}else{
			$message="Please provide eid";
		}



		$r=[];
		$r['status']=$status;
		$r['message']=$message;
		$r['response']=$response;
		return $r;	
	}

	function levels_update($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){


			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['update_eid'])){
				$update_id=$Enc->safeurlde($param['update_eid']);
			}else{
				$InvalidDataMessage="Please provide parent id";
				$dataValidation=false;
				goto ValidationChecker;
			}

			if(isset($param['parent_id'])){
				$parent_id=senetize_input($param['parent_id']);

				if(!$this->isValidId($parent_id)){
					$InvalidDataMessage="Invalid parent id";
					$dataValidation=false;
					goto ValidationChecker;
				}

			}else{
				$InvalidDataMessage="Please provide parent id";
				$dataValidation=false;
				goto ValidationChecker;
			}


			if(isset($param['name'])){
				$name=senetize_input($param['name']);
			}else{
				$InvalidDataMessage="Please provide level name";
				$dataValidation=false;
				goto ValidationChecker;
			}

//------check duplicacy of name with same parent id

			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` WHERE `level_status`='ACT' AND `level_parent_level_id_fk`='$parent_id' AND `level_name`='$name' AND NOT `level_id`='$update_id'"))>0){
				$InvalidDataMessage="$name already exists";
				$dataValidation=false;
				goto ValidationChecker;
			}


			ValidationChecker:
			if($dataValidation){
				$USERID=USER_ID;
				$time=time();

				$insert=mysqli_query($GLOBALS['con'],"UPDATE `hierarchy` SET `level_name`='$name', `level_parent_level_id_fk`='$parent_id' WHERE `level_id`='$update_id' AND NOT `level_type`='ROOT'");
				if($insert){
					$status=true;
					$message="Added Successfuly";	
				}else{
					$message=SOMETHING_WENT_WROG;
				}
			}else{
				$message=$InvalidDataMessage;
			}


		}else{
			$message=NOT_AUTHORIZED_MSG;
		}
		$r=[];
		$r['status']=$status;
		$r['message']=$param;
		$r['response']=$response;
		return $r;

	}
	function levels_delete($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){


			include_once APPROOT.'/models/common/Enc.php';
			$Enc=new Enc;

			//-----data validation starts
 					///----start a dataValidation variable with true value, if any of any validation fails change it to false. and restrict the final insert command on it;
			$dataValidation=true;
			$InvalidDataMessage="";

			if(isset($param['delete_eid'])){
				$delete_id=$Enc->safeurlde($param['delete_eid']);
			}else{
				$InvalidDataMessage="Please provide delete eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

			//------check if valid eid is send 
			//------chekck if id for deletion is send for level only not for ROOT

			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` WHERE `level_id`='$delete_id' AND `level_status`='ACT' AND `level_type`='LEVEL'"))!=1){
				$InvalidDataMessage="Invalid delete eid";
				$dataValidation=false;
				goto ValidationChecker;
			}

//--------restrict deletion of level that have child levels
			if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT `level_id` FROM `hierarchy` WHERE `level_parent_level_id_fk`='$delete_id' AND `level_status`='ACT' AND `level_type`='LEVEL'"))!=1){
				$InvalidDataMessage="Can't delete a level that have one or more active child levels";
				$dataValidation=false;
				goto ValidationChecker;
			}



			ValidationChecker:
			if($dataValidation){
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `hierarchy` SET `level_status`='DLT' WHERE `level_id`='$delete_id'");
				if($delete){
					$status=true;
					$message="Deleted Successfuly";	
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

	function levels_users_junction($param){
		

		$status=false;
		$message=null;
		$response=[];

		include_once APPROOT.'/models/common/Enc.php';
		$Enc=new Enc;
		$q="SELECT `huj_user_id_fk`, `huj_level_id_fk`,`user_code`,`level_name` FROM `hierarchy_user_junction` LEFT JOIN `utab` ON `utab`.`user_id`=`hierarchy_user_junction`.`huj_user_id_fk` LEFT JOIN `hierarchy` ON `hierarchy`.`level_id`=`hierarchy_user_junction`.`huj_level_id_fk` WHERE `huj_status`='ACT'";

		if(isset($param['level_eid'])){
			$level_id=$Enc->safeurlde($param['level_eid']);
			$q.=" AND `huj_level_id_fk`='$level_id'";
		}
		if(isset($param['user_eid'])){
			$user_id=$Enc->safeurlde($param['user_eid']);
			$q.=" AND `huj_user_id_fk`='$user_id'";
		}				

		$list=[];
		$qEx=mysqli_query($GLOBALS['con'],$q);
		while ($rows=mysqli_fetch_assoc($qEx)) {
			$row=[];
			$row['user_eid']=$Enc->safeurlen($rows['huj_user_id_fk']);
			$row['level_eid']=$Enc->safeurlen($rows['huj_level_id_fk']);
			$row['user_code']=$rows['user_code'];
			$row['level_name']=$rows['level_name'];
			array_push($list,$row);
			$status=true;
		}
		$response['list']=$list;
		$r=[];
		$r['status']=$status;
		$r['message']=$param;
		$r['response']=$response;
		return $r;

	}

	function level_assign_users($param){
		$status=false;
		$message=null;
		$response=null;
		if(in_array('PADMIN', USER_PRIV)){


			if(isset($param['level_eid'])){
				
				include_once APPROOT.'/models/common/Enc.php';
				$Enc=new Enc;
				$USERID=USER_ID;
				$time=time();

				$level_id=$Enc->safeurlde($param['level_eid']);
				$users_id_array=[];
				if(isset($param['users_eid_list'])){
					$users_eid_list=$param['users_eid_list'];
					foreach ($users_eid_list as $eid) {
						array_push($users_id_array, $Enc->safeurlde($eid));
					}
				}





				$users_id_array_string=implode(', ', $users_id_array);
				//------------firstly delete those existing junctions which are not send in updated list
				$delete=mysqli_query($GLOBALS['con'],"UPDATE `hierarchy_user_junction` SET `huj_deleted_on`='$time',`huj_deleted_by`='$USERID',`huj_status`='DEL' WHERE `huj_level_id_fk`='$level_id' AND NOT `huj_user_id_fk` IN (".$users_id_array_string.")");

				$insert=true;///statrt $insert variable with true value. during insert if any error occur change it to false
				foreach ($users_id_array as $user_id) {

						//-----check if the send user id is allready assigned
					if(mysqli_num_rows(mysqli_query($GLOBALS['con'],"SELECT auto FROM `hierarchy_user_junction` WHERE `huj_level_id_fk`='$level_id' AND `huj_user_id_fk`='$user_id' AND `huj_status`='ACT'"))<1){
						$insert_new=mysqli_query($GLOBALS['con'],"INSERT INTO `hierarchy_user_junction`(`huj_user_id_fk`, `huj_level_id_fk`, `huj_status`, `huj_added_on`) VALUES ('$user_id','$level_id','ACT','$USERID')");
						if(!$insert_new){
							$message=SOMETHING_WENT_WROG;
						}
					}

				}

				if($insert){
					$status=true;
					$message="Assigned Successfuly";
				}


			}else{
				$message="Please provide group eid ";
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

	function user_all_levels($param){
		$r=[];
		if(isset($param['user_id'])){
			$user_id=senetize_input($param['user_id']);
			$q=mysqli_query($GLOBALS['con'],"SELECT  `huj_level_id_fk` FROM `hierarchy_user_junction` WHERE `huj_status`='ACT' AND `huj_user_id_fk`='$user_id'");
			while($res=mysqli_fetch_assoc($q)){
				array_push($r,$res['huj_level_id_fk']);
			}
		}
		return $r;
	}


}
?>