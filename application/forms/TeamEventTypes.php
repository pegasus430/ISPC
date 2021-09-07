<?php

	require_once("Pms/Form.php");

	class Application_Form_TeamEventTypes extends Pms_Form {

		
		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
		
			$error = 0;
			$val = new Pms_Validation();
		
			if(!$val->isstring($post['name']))
			{
				$this->error_message = $Tr->translate('team_event_type_name_error');
				$error = 1;
			}
		
			if($error == 0)
			{
				return true;
			}
		
			return false;
		}
		
		public function insert($post)
		{
			$inserted_id = false;

			$pcenter = new TeamEventTypes();
			$pcenter->client = $post['client'];
			$pcenter->name = $post['name'];
			$pcenter->voluntary = $post['voluntary'];
			$pcenter->isdelete = "0";
			$pcenter->save();

			$inserted_id = $pcenter->id;

			if($inserted_id)
			{
				return $inserted_id;
			}
			else
			{
				return false;
			}
		}
		

		public function update($post)
		{
			$fdoc = Doctrine::getTable('TeamEventTypes')->find($post['tid']);

			$fdoc->name = trim(rtrim($post['name']));
			$fdoc->voluntary = $post['voluntary'];
			$fdoc->isdelete = "0";
			$fdoc->save();
			
			return $fdoc->id;
		}

		public function delete($post)
		{
			$fdoc = Doctrine::getTable('TeamEventTypes')->find($post['tid']);
			if($fdoc)
			{
				$fdoc->isdelete = "1";
				$fdoc->save();
				
				return $fdoc->id;
			}
		}
	}

?>