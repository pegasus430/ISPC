<?php

require_once("Pms/Form.php");

class Application_Form_Diagnosis extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['catalogue'])){
			$this->error_message['catalogue']=$Tr->translate('catalogue_error'); $error=1;
		}
		if(!$val->isstring($post['main_group'])){
			$this->error_message['main_group']=$Tr->translate('maingroup_error'); $error=2;
		}
		if(!$val->isstring($post['detail_code'])){
			$this->error_message['detail_code']=$Tr->translate('datailcode_error'); $error=3;
		}
		if(!$val->isstring($post['rating'])){
			$this->error_message['rating']=$Tr->translate('datailcode_error'); $error=5;
		}
		if(!$val->isstring($post['terminal'])){
			$this->error_message['terminal']=$Tr->translate('terminal_error'); $error=6;
		}
		if(!$val->isstring($post['gender'])){
			$this->error_message['gender']=$Tr->translate('gender_error'); $error=7;
		}
		if(!$val->isstring($post['lowest_age'])){
			$this->error_message['lowest_age']=$Tr->translate('lowestage_error'); $error=8;
		}
		if(!$val->isstring($post['highest_age'])){
			$this->error_message['highest_age']=$Tr->translate('highestage_error'); $error=9;
		}
		if(!$val->isstring($post['version'])){
			$this->error_message['version']=$Tr->translate('version_error'); $error=10;
		}
		if(!$val->isstring($post['error1'])){
			$this->error_message['error1']=$Tr->translate('error1_error'); $error=11;
		}
		if(!$val->isstring($post['error2'])){
			$this->error_message['error2']=$Tr->translate('error2_error'); $error=12;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}


	public function validateicd($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		if(!$val->isstring($post['detail_code'])){
			$this->error_message['detail_code']=$Tr->translate('datailcode_error'); $error=3;
		}
		if(!$val->isstring($post['description'])){
			$this->error_message['description']=$Tr->translate('datailcode_error'); $error=4;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function validateDiagnosis_Type($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['client_id'])){
			$this->error_message['client_id']=$Tr->translate('enterclientid'); $error=1;
		}
		if(!$val->isstring($post['abbrevation'])){
			$this->error_message['abbrevation']=$Tr->translate('enterabbrevation'); $error=2;
		}
		if(!$val->isstring($post['description'])){
			$this->error_message['description']=$Tr->translate('enterdescription'); $error=4;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		 
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$res = new Diagnosis();
		$res->clientid = $logininfo->clientid;
		$res->catalogue = $post['catalogue'];
		$res->main_group = $post['main_group'];
		$res->detail_code = $post['detail_code'];
		$res->description = $post['description'];
		$res->rating = $post['rating'];
		$res->terminal = $post['terminal'];
		$res->gender = $post['gender'];
		$res->lowest_age = $post['lowest_age'];
		$res->highest_age =$post['highest_age'];
		$res->version =$post['version'];
		$res->error1 =$post['error1'];
		$res->error2 =$post['error2'];
		$res->save();
	}

	public function UpdateData($post)
	{
			
		$res = Doctrine::getTable('Diagnosis')->find($_GET['id']);
		$res->catalogue = $post['catalogue'];
		$res->main_group = $post['main_group'];
		$res->detail_code = $post['detail_code'];
		$res->icd_year = $post['icd_year'];
		$res->icd_primary = $post['icd_primary'];
		$res->icd_star = $post['icd_star'];
		$res->icd_cross = $post['icd_cross'];
		$res->description = $post['description'];
		$res->rating = $post['rating'];
		$res->terminal = $post['terminal'];
		$res->gender = $post['gender'];
		$res->lowest_age = $post['lowest_age'];
		$res->highest_age =$post['highest_age'];
		$res->version =$post['version'];
		$res->error1 =$post['error1'];
		$res->error2 =$post['error2'];
		$res->save();
	}
	 
	public function InsertDiagnosistypeData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		 
		$res = new DiagnosisType();
		$res->clientid = $post['client_id'];
		$res->abbrevation = $post['abbrevation'];
		$res->description = $post['description'];
		$res->save();
	}



	public function UpdateDiagnosistypeData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		 
		$res = Doctrine::getTable('DiagnosisType')->find($_GET['id']);
		$res->clientid = $post['client_id'];
		$res->abbrevation = $post['abbrevation'];
		$res->description = $post['description'];

		$res->save();
	}
	public function getHDdiagnosis($ipid){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$dg = new DiagnosisType();
		$abb2 = "'HD'";
		$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid,$abb2);

		foreach($ddarr2 as $key=>$valdia)
		{
			$typeid .= '"'.$valdia['id'].'",';
		}
		$dispat = Doctrine_Query::create()
		->select('*, AES_DECRYPT(tabname, "'.Zend_Registry::get('salt').'") as a_tabname')
		->from("PatientDiagnosis")
		->where('ipid = "'.$ipid.'" and diagnosis_type_id in ('.substr($typeid,0,-1).') ')
		->groupBy('diagnosis_id')
		->orderBy('diagnosis_id DESC');
		$disipidarray = $dispat->fetchArray();

		$i = 0;
		foreach($disipidarray as $key=>$val)
		{
			if($val['a_tabname']=='diagnosis')
			{
				if($val['diagnosis_id']==""){
					$val['diagnosis_id']='0';
				}

				$diagno = Doctrine_Query::create()
				->select("*")
				->from("Diagnosis")
				->where("id=".$val['diagnosis_id']);
				$diagnoexec = $diagno->execute();
				$dispat->getSqlQuery();
				$diagnoarray = $diagnoexec->toArray();

				$statdia_array = array();

				$statdia_array['description'] = $diagnoarray[0]["description"];


				$count[$key]  = $disipidarray[$i]['sum_diagnos'];
				$sortarray1[] = $statdia_array;
			} elseif ($val['a_tabname'] == "diagnosis_freetext"){

				if($val['diagnosis_id']==""){
					$val['diagnosis_id']='0';
				}

				$dg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisText')
				->where('id in ('.$val['diagnosis_id'].')');

				$res1 = $dg->execute();
				$try1 = $res1->toArray();

				$statdia_array['description'] = $try1[0]['free_name'];

				$sortarray1[] = $statdia_array;

			} elseif($val['a_tabname'] == "diagnosis_icd") {

				if($val['diagnosis_id']==""){
					$val['diagnosis_id']='0';
				}

				$dgg = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->where("id = '".$val['diagnosis_id']."' ")
				->orderBy('id ASC');

				$res2 = $dgg->execute();
				$try2 = $res2->toArray();

				$icd_primary = !empty($try2[0]['icd_primary'])? $try2[0]['icd_primary'] : "-";

					
				$statdia_array['description'] = $try2[0]['description'];

				$sortarray1[] = $statdia_array;

			}
			++$i;
				
				
		}
		return $sortarray1;
	}
}

?>