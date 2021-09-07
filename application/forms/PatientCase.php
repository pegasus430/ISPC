<?php

require_once("Pms/Form.php");

class Application_Form_PatientCase extends Pms_Form
{
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['admission_date'])){
			$this->error_message['admission_date']=$Tr->translate('admissiondate_error'); $error=1;
		}

		if(strtotime($post['admission_date']) > strtotime(date("d.m.Y",time()))){
			$this->error_message['admission_date']=$Tr->translate('err_dischargedatefuture'); $error=5;
		}

		if(!$val->isstring($post['clientid'])){
			$this->error_message['clientid']=$Tr->translate('client_error'); $error=33;
		}


		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		 
		$cust = new PatientCase();
		if(strlen($post['admission_date'])>0)
		{
			$bd_date = explode(".",$post['admission_date']);
			$adate = $bd_date[2]."-".$bd_date[1]."-".$bd_date[0]." ".$post['adm_timeh'].":".$post['adm_timem'];
		}
		else
		{
			$adate = date("Y-m-d H:i:s",time());
		}
		$cust->admission_date = $adate ;
		$cust->comments = Pms_CommonData::aesEncrypt($post['comments']);
		$cust->clientid = $post['clientid'];
		$cust->save();

		$epid = Pms_Uuid::GenerateEpid($post['clientid'],$cust->id);

		// check if epid exists
		$check_epid = EpidIpidMapping::check_epid($post['clientid'],$epid);
		
		
		$r = 1;
		while ($check_epid === false){
		  $r++;
		  $epid = Pms_Uuid::GenerateEpid($post['clientid'],$cust->id);
		  if ($r > 10)
		  {
		      return false;
		      break;
		  }
		}
		
		
		
		$cust = Doctrine::getTable('PatientCase')->find($cust->id);
		$cust->epid = $epid;
		$cust->save();

		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		Pms_Triggers::InsertFamilyDoctor($post['ipid'],$logininfo->clientid,$epid);
		return $cust;
	}

	
	public function regenerate($post)
	{
		if(!empty($post['case_id'])) {
			$epid = Pms_Uuid::GenerateEpid($post['clientid'],$post['case_id']);
				
			// check if epid exists
			$check_epid = EpidIpidMapping::check_epid($post['clientid'],$epid);
				
				
			$r = 1;
			while ($check_epid === false){
				$r++;
				$epid = Pms_Uuid::GenerateEpid($post['clientid'],$post['case_id']);
				if ($r > 10)
				{
					return false;
					break;
				}
			}
				
				
			$cust = Doctrine::getTable('PatientCase')->find($post['case_id']);
			$cust->epid = $epid;
			$cust->save();
			return $cust;
		}
	}
	
	public function UpdateData($post)
	{
		$admission_date = explode(".",$post['admission_date']);
		$ad = $admission_date[2]."-".$admission_date[1]."-".$admission_date[0]." ".$post['adm_timeh'].":".$post['adm_timem'];

		$epid = Pms_CommonData::getEpid($post['ipid']);

		$q = Doctrine_Query::create()
		->update('PatientCase')
		->set('admission_date', "'".$ad."'")
		->where("epid = '".$epid."'");
		$q->execute();
	}
	 
	public function test($func){

		$func();

	}


}

?>