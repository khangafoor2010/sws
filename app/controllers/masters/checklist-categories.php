<?php
$r=[];
$r['ChecklistCategories']=false;
$r['message']=null;
$r['response']=null;
		include_once APPROOT.'/models/masters/ChecklistCategories.php';
		$ChecklistCategories=new ChecklistCategories;
switch (getUri()) {



	case 'user/masters/drivers/checklist/categories/list':
		PARAM['related_to']='Driver';
		$r=$ChecklistCategories->checklist_category_list(PARAM);

	break;

	default:
		$r['message']='NOT_VALID_REQUEST_TYPE';
		break;
}
echo json_encode($r);
?>