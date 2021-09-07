<?php

require_once("Pms/Form.php");

class Application_Form_DiagnosisText extends Pms_Form
{
	public function validate($post)
	{
	}


	public function InsertData($post)
	{

		$resarr = array();

		$resarr = array();
		foreach($post['newdiagnosis'] as $key=>$val)
		{
			$dg =explode("|",$val);

			$res = new DiagnosisText();
			$res->clientid = $post['clientid'];
			$res->icd_primary = $post['newdiagnosisicd'][$key];
			$res->free_name =  htmlentities($dg[0], ENT_COMPAT, 'UTF-8');
			$res->free_desc =  htmlentities($dg[1], ENT_COMPAT, 'UTF-8');
			$res->save();

			$resarr[$key]=$res;
		}

		return $resarr;
	}

	public function InsertEditData($post)
	{
		$resarr = array();
		foreach($post['newdiagnosis'] as $key=>$val)
		{
			$dg =explode("|",$val);

			$res = new DiagnosisText();
			$res->clientid = $post['clientid'];
			$res->icd_primary = $post['newdiagnosisicd'][$key];
			$res->free_name = htmlentities($dg[0], ENT_COMPAT, 'UTF-8');
			$res->free_desc = htmlentities($dg[1], ENT_COMPAT, 'UTF-8');
			$res->save();

			$resarr[$key]=$res;
		}
		return $resarr;
	}

	public function UpdateData($post)
	{

		$res = Doctrine::getTable('Diagnosis')->find($_GET['id']);
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

	public function Insert_EdData($post)
	{

		$resarr = array();
		foreach($post['newdiagnosis_ed'] as $key=>$val)
		{
			$dg =explode("|",$val);

			$res = new DiagnosisText();
			$res->clientid = $post['clientid'];
			$res->free_name = htmlentities($dg[0], ENT_COMPAT, 'UTF-8');;
			$res->free_desc = htmlentities($dg[1], ENT_COMPAT, 'UTF-8');;
			$res->save();

			$resarr[$key]=$res;
		}
		return $resarr;
	}


}

?>