<?php
require_once("Pms/Form.php");
class Application_Form_SgbvForms extends Pms_Form
{

	public function InsertData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		if(!empty($post['valid_from'])){
			$valid_from = date("Y-m-d H:i:s",  strtotime($post['valid_from']));
		} else{
			$valid_from = "0000-00-00 00:00:00";
		}

		if(!empty($post['valid_till'])){
			$valid_till = date("Y-m-d H:i:s",  strtotime($post['valid_till']));
		} else{
			$valid_till = "0000-00-00 00:00:00";
		}

		if(!empty($post['approved_limit'])){
			$approved_limit = date("Y-m-d H:i:s",  strtotime($post['approved_limit']));
		} else{
			$approved_limit = "0000-00-00 00:00:00";
		}


		$sgbv_form_master = new SgbvForms();
		$patient_sgbv_array = $sgbv_form_master->getallPatientSgbvForm($ipid);

		if(!empty($patient_sgbv_array) && count($patient_sgbv_array)>1) {
			$post['form_type'] ="follow";
		} else{
			$post['form_type'] ="first";
		}


		$stmb = new SgbvForms();
		$stmb->ipid = $ipid;
		$stmb->valid_from =$valid_from;
		$stmb->valid_till =$valid_till;
		$stmb->form_type = $post['form_type'];
		$stmb->approved_limit = $approved_limit;
		$stmb->no_evaluation_possible = $post['no_evaluation_possible'];
		$stmb->accident = $post['accident'];
		$stmb->regulation_time = $post['regulation_time'];
		$stmb->hospital_treatment = $post['hospital_treatment'];
		$stmb->ambulant_treatment = $post['ambulant_treatment'];
		$stmb->size_degree = $post['size_degree'];
		$stmb->preparations = $post['preparations'];
		$stmb->wound_findings = $post['wound_findings'];
		$stmb->temporarily = 1;
		$stmb->free_of_charge = $post['free_of_charge'];
		$stmb->save();

		$result = $stmb->id;

		if ($stmb->id > 0)
		{
			return $stmb->id;
		}
		else
		{
			return false;
		}
	}

	public function UpdateSimpleData ($post,$sgbv_form_id )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			

		if(!empty($post['valid_from'])){
			$valid_from = date("Y-m-d H:i:s",  strtotime($post['valid_from']));
		} else{
			$valid_from = "0000-00-00 00:00:00";
		}

		if(!empty($post['valid_till'])){
			$valid_till = date("Y-m-d H:i:s",  strtotime($post['valid_till']));
		} else{
			$valid_till = "0000-00-00 00:00:00";
		}

		if(!empty($post['approved_limit'])){
			$approved_limit = date("Y-m-d H:i:s",  strtotime($post['approved_limit']));
		} else{
			$approved_limit = "0000-00-00 00:00:00";
		}

		$stmb = Doctrine::getTable('SgbvForms')->find($sgbv_form_id);
		$stmb->valid_from =$valid_from;
		$stmb->valid_till =$valid_till;
		$stmb->form_type = $post['form_type'];
		$stmb->status = $post['status'];
		$stmb->approved_limit  = $approved_limit;
		$stmb->no_evaluation_possible = $post['no_evaluation_possible'];
		$stmb->accident = $post['accident'];
		$stmb->regulation_time = $post['regulation_time'];
		$stmb->hospital_treatment = $post['hospital_treatment'];
		$stmb->ambulant_treatment = $post['ambulant_treatment'];
		$stmb->size_degree= $post['size_degree'];
		$stmb->preparations= $post['preparations'];
		$stmb->wound_findings= $post['wound_findings'];
		$stmb->temporarily = 0;
		$stmb->save();
	}
	
	public function UpdateSgbvModal($post,$sgbv_form_id )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$sgbv_id = $_REQUEST['sgbv_id'];

		if(!empty($post['valid_from'])){
			$valid_from = date("Y-m-d H:i:s",  strtotime($post['valid_from']));
		} else{
			$valid_from = "0000-00-00 00:00:00";
		}

		if(!empty($post['valid_till'])){
			$valid_till = date("Y-m-d H:i:s",  strtotime($post['valid_till']));
		} else{
			$valid_till = "0000-00-00 00:00:00";
		}

		if(!empty($post['approved_limit'])){
			$approved_limit = date("Y-m-d H:i:s",  strtotime($post['approved_limit']));
		} else{
			$approved_limit = "0000-00-00 00:00:00";
		}
		$stmb = Doctrine::getTable('SgbvForms')->find($sgbv_form_id);
		$stmb->valid_from =$valid_from;
		$stmb->valid_till =$valid_till;
		$stmb->form_type = $post['form_type'];
		$stmb->approved_limit = $approved_limit;
		$stmb->free_of_charge = $post['free_of_charge'];
		$stmb->save();
	}

	public function CancelSgbvFrom($ipid,$sgbv_form_id){
		$q = Doctrine_Query::create()
		->delete('SgbvForms')
		->where("id='" . $sgbv_form_id. "'")
		->andWhere('temporarily = 1')
		->andWhere('ipid LIKE "' . $ipid . '"');
		$q->execute();
	}

	public function insert_minimal_sgbv($data)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = new SgbvForms();
		$stmb->ipid = $ipid;
		$stmb->valid_from =$data['valid_from'];
		$stmb->valid_till =$data['valid_till'];
		$stmb->form_type = $data['form_type'];
		$stmb->status = $data['status'];
		$stmb->save();
		$ins_id = $stmb->id;

		if($ins_id)
		{
			return $ins_id;
		}
		else
		{
			return false;
		}
	}

}
?>