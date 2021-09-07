<?php

	require_once("Pms/Form.php");

	class Application_Form_PatientDayStructureActions extends Pms_Form {

		public function clear_block_data($ipid, $form_id)
		{
			if(!empty($form_id))
			{
				$Q = Doctrine_Query::create()
					->update('PatientDayStructureActions')
					->set('isdelete', '1')
					->where("form_id='" . $form_id . "'")
					->andWhere('ipid LIKE "' . $ipid . '"');
				$Q->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function insert($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			foreach($post['item'] as $key => $action_values)
			{
				if( (strlen($action_values['start']) > 0 && strlen($action_values['end']) > 0)   || strlen($action_values['description']) > 0 )  {
					$records[] = array(
						"ipid" => $post['ipid'],
						"form_id" => $post['form_id'],
						"start" => $action_values['start'],
						"end" => $action_values['end'],
						"description" => $action_values['description'],
						"measures" => $action_values['measures']
					);
				}
			}

			$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['form_id']);

			$collection = new Doctrine_Collection('PatientDayStructureActions');
			$collection->fromArray($records);
			$collection->save();
		}

	}

?>