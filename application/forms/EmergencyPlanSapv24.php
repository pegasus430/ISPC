<?php

require_once("Pms/Form.php");

class Application_Form_EmergencyPlanSapv24 extends Pms_Form
{
    
	public function insert($ipid,$post)
	{
		
		$stmb = new EmergencyPlanSapv24();
		$stmb->ipid = $ipid;
        
        foreach($post['data'] as $field =>$value ) {
        	if($field != 'medication') {
        		if($field == 'sapv24_date')
        		{
        			$stmb->$field = date('Y-m-d', strtotime($value));
        		}
        		else 
        		{
					$stmb->$field = $value;
       			}
        	}
        }
      
		$stmb->save();
		
		if($stmb->id>0)
		{
			foreach($post['data']['medication'] as $field =>$value ) {
				$stmb_med = new EmergencyPlanSapv24Medication();
				foreach($value as $k=>$v) {
					$stmb_med->$k = $v;
				}
				$stmb_med->planid = $stmb->id;
				$stmb_med->save();
			}
			return true;
		}else{
			return false;
		}
	}

	public function update($ipid,$post)
	{
		
	    $stmb = Doctrine::getTable('EmergencyPlanSapv24')->find($post['formid']);
		$stmb->ipid = $ipid;
		
        foreach($post['data'] as $field =>$value ){
        	if($field != 'medication') {
        		if($field == 'sapv24_date')
        		{
        			$stmb->$field = date('Y-m-d', strtotime($value));
        		}
        		else 
        		{
					$stmb->$field = $value;
       			}
        	}
       		
        }
        
		$stmb->save();
		
		$med = new EmergencyPlanSapv24Medication();
		$meddata = $med->get_emergency_plan_sapv24_medication($post['formid']);
		
		foreach($meddata as $km=>$vm) {
			$stmb_med = Doctrine::getTable('EmergencyPlanSapv24Medication')->find($vm['id']);
			$stmb_med->isdelete = '1';
			$stmb_med->save();
		}
		//var_dump($post['data']['medication']);exit;
		
		foreach($post['data']['medication'] as $field =>$value ) {
			$stmb_med = new EmergencyPlanSapv24Medication();
			foreach($value as $k=>$v) {
				
				$stmb_med->$k = $v;
			}
			$stmb_med->planid = $stmb->id;
			$stmb_med->save();
		}
		
		return true;
		
	}
	
	public function reloaddata($ipid)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
	
		$rst = Doctrine::getTable('EmergencyPlanSapv24')->findOneById($_POST['formid']);
		$rst->isdelete = 1;
		$rst->save();
		
		$med = new EmergencyPlanSapv24Medication();
		$meddata = $med->get_emergency_plan_sapv24_medication($_POST['formid']);
		
		foreach($meddata as $km=>$vm) {
			$stmb_med = Doctrine::getTable('EmergencyPlanSapv24Medication')->find($vm['id']);
			$stmb_med->isdelete = '1';
			$stmb_med->save();
		}
	
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Notfallplan 24 - Formular wurde neu geladen";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt('emergencyplansapv24');
		$custcourse->recordid = $_POST['formid'];
		$custcourse->done_name = Pms_CommonData::aesEncrypt('emergencyplansapv24');
		$custcourse->done_id = $_POST['formid'];
		$custcourse->save();
	}
}

?>