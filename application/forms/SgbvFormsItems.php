<?php

require_once("Pms/Form.php");

class Application_Form_SgbvFormsItems extends Pms_Form
{
	public function clear_items_data($ipid, $sgbv_form_id )
	{
		if (!empty($sgbv_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('SgbvFormsItems')
			->set('isdelete','1')
			->where("sgbv_form_id='" . $sgbv_form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post,$sgbv_form_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		foreach($post['action'] as $action_id=>$action_values){

			if($action_values['parent']){
				$Q = Doctrine_Query::create()
				->update('SocialCodeActions')
				->set('parent','"'.$action_values['parent'].'"')
				->where("id='" . $action_values['id']. "'")
				->andWhere("clientid='" . $clientid. "'")
				->andWhere(" custom = 1");
				$Q->execute();
			}


			if(!empty($post['sgbv']['approved_limit'])){
				$post['sgbv']['approved_limit'] = date('Y-m-d 00:00:00', strtotime($post['sgbv']['approved_limit']));
			} else{
				$post['sgbv']['approved_limit'] = '0000-00-00 00:00:00';
			}

			$records[] = array(
					"ipid" => $ipid,
					"sgbv_form_id" => $sgbv_form_id,
					"action_id" => $action_values['id'],
					"valid_from" =>  date('Y-m-d 00:00:00', strtotime($action_values['valid_from'])),
					"valid_till" =>  date('Y-m-d 00:00:00', strtotime($action_values['valid_till'])),
					"status" => $post['sgbv']['status'],
					"approved_limit" => $post['sgbv']['approved_limit'],
					"per_day" => $action_values['per_day'],
					"per_week" => $action_values['per_week'],
					"free_of_charge" => $action_values['free_of_charge']
			);
		}

		$clear_block_entryes = $this->clear_items_data($ipid, $sgbv_form_id);

		$collection = new Doctrine_Collection('SgbvFormsItems');
		$collection->fromArray($records);
		$collection->save();
	}

	public function insert_items_minimal ( $data, $sgbv_form_id )
	{
		foreach ($data['items'] as $k_item => $v_item)
		{
			$records[] = array(
					"ipid" => $data['ipid'],
					"sgbv_form_id" => $sgbv_form_id,
					"action_id" => $v_item,
					"valid_from" => date('Y-m-d H:i:s', strtotime($data['valid_from'])),
					"valid_till" => date('Y-m-d H:i:s', strtotime($data['valid_till'])),
					"status" => '0',
					"approved_limit" => '0000-00-00 00:00:00',
					"per_day" => '1',
					"per_week" => '7',// default value for this should be 7
					"free_of_charge" => '0'
			);
		}
		$collection = new Doctrine_Collection('SgbvFormsItems');
		$collection->fromArray($records);
		$collection->save();
	}

	public function update_old_items($last_verordnung, $valid_till, $submited_ids = false, $sgbv_foc_actions = false)
	{

		$q = Doctrine_Query::create()
		->update('SgbvFormsItems')
		->set('valid_till', "'".$valid_till."'")
		->where("sgbv_form_id='" . $last_verordnung. "'");
		$q->execute();

		if($submited_ids && $sgbv_foc_actions)
		{

			//exclude sgbv foc actions from updating the qtys
			$incremented_actions = array_diff($submited_ids, $sgbv_foc_actions);
			$incremented_actions = array_values(array_unique($incremented_actions));

			$q_act = Doctrine_Query::create()
			->update('SgbvFormsItems')
			->set('per_day', 'per_day + "1"')
			->set('per_week', 'per_week + "7"')// default value is 7 - so we should add 7 to the week number
			->where("sgbv_form_id='" . $last_verordnung. "'")
			->andWhereIn('action_id', $incremented_actions);
			$q_act->execute();
		}
	}

	public function CancelSgbvFromItems($ipid,$sgbv_form_id){
		$q = Doctrine_Query::create()
		->delete('SgbvFormsItems')
		->where("sgbv_form_id='" . $sgbv_form_id. "'")
		->andWhere('ipid LIKE "' . $ipid . '"');
		$q->execute();
	}
}

?>