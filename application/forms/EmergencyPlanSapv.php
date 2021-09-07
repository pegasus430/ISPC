<?php

require_once("Pms/Form.php");

class Application_Form_EmergencyPlanSapv extends Pms_Form
{
    
	public function insert($ipid,$post)
	{
		$stmb = new EmergencyPlanSapv();
		$stmb->ipid = $ipid;
        
        foreach($post['data'] as $field =>$value ){
		  $stmb->$field = $value;
        }
		
		$stmb->save();

		if($stmb->id>0)
		{
			return true;
		}else{
			return false;
		}
	}

	public function update($ipid,$post)
	{
	    $stmb = Doctrine::getTable('EmergencyPlanSapv')->find($post['formid']);
		$stmb->ipid = $ipid;
        foreach($post['data'] as $field =>$value ){
		  $stmb->$field = $value;
        }
		$stmb->save();

		return true;
		
	}
}

?>