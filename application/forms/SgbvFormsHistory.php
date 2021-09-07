<?php

require_once("Pms/Form.php");

class Application_Form_SgbvFormsHistory extends Pms_Form
{

	public function InsertHistorySgbvData($post,$sgbv_form_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = new SgbvFormsHistory();
		$stmb->ipid = $ipid;
		$stmb->parent = $sgbv_form_id;
		$stmb->entity_type = 'sgbv';
		$stmb->entity_id  = $sgbv_form_id;
		$stmb->new_status = $post['status'];
		$stmb->old_status = $post['old_status'];
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

	public function InsertHistorySgbvItemsData($post,$parent_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		foreach($post['action'] as $action_id=>$action_values){

			if( $action_values['status'] != $action_values['old_status'] || empty($action_values['old_status']) ) {
				$records[] = array(
						"ipid" => $ipid,
						"parent" => $parent_id,
						"entity_id" => $action_values['id'],
						"entity_type" => 'action',
						"new_status" => $post['sgbv']['status'],
						"old_status" => $post['sgbv']['old_status']
				);
			}

		}

		$collection = new Doctrine_Collection('SgbvFormsHistory');
		$collection->fromArray($records);
		$collection->save();

	}

	public function CancelSgbvFromHistory($ipid,$sgbv_form_id){
		$q = Doctrine_Query::create()
		->delete('SgbvFormsHistory')
		->where("parent='" . $sgbv_form_id. "'")
		->andWhere('ipid LIKE "' . $ipid . '"');
		$q->execute();
	}

}

?>