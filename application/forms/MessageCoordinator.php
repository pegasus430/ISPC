<?php

require_once("Pms/Form.php");

class Application_Form_MessageCoordinator extends Pms_Form{
	
	public function insert_data($ipid,$post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
					
				$ins = new MessageCoordinator();
				$ins->ipid = $ipid;
				$ins->patient_phone = $post['patient_phone'];
				$ins->cntperson_phone = $post['cntperson_phone'];
				$ins->qpa_name = $post['qpa_name'];
				$ins->qpa_phone = $post['qpa_phone'];
				$ins->qpa_fax = $post['qpa_fax'];
				$ins->familydoc_name = $post['familydoc_name'];
				$ins->familydoc_phone = $post['familydoc_phone'];
				$ins->familydoc_fax = $post['familydoc_fax'];
				$ins->pflege_name = $post['pflege_name'];
				$ins->pflege_phone = $post['pflege_phone'];
				$ins->pflege_fax = $post['pflege_fax'];
				$ins->observation = $post['observation'];
				$ins->isdelete = '0';
				$ins->save();
			
		
	}
	
	public function update_data($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$upd = Doctrine::getTable('MessageCoordinator')->find($post['saved_id']);
		//print_r($upd->toArray());exit;
		$upd->patient_phone = $post['patient_phone'];
		$upd->cntperson_phone = $post['cntperson_phone'];
		$upd->qpa_name = $post['qpa_name'];
		$upd->qpa_phone = $post['qpa_phone'];
		$upd->qpa_fax = $post['qpa_fax'];
		$upd->familydoc_name = $post['familydoc_name'];
		$upd->familydoc_phone = $post['familydoc_phone'];
		$upd->familydoc_fax = $post['familydoc_fax'];
		$upd->pflege_name = $post['pflege_name'];
		$upd->pflege_phone = $post['pflege_phone'];
		$upd->pflege_fax = $post['pflege_fax'];
		$upd->observation = $post['observation'];
		
		$upd->save();
	}
	
}