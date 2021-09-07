<?php

	require_once("Pms/Form.php");

	class Application_Form_ClientContactFormSettings extends Pms_Form {

		public function insert_data($clientid, $post)
		{
			if($clientid > '0' && !empty($post))
			{
				$this->clear_client_data($clientid);

				$settings = new ClientContactFormSettings();
				$settings->client = $clientid;

				if(strlen(trim($post['date'])) > '0')
				{
					$settings->date = $post['date'];
				}
				$settings->isdelete = '0';
				$settings->save();

				if($settings->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		private function clear_client_data($clientid)
		{
			$upd = Doctrine::getTable('ClientContactFormSettings')->findOneByClientAndIsdelete($clientid, '0');
			if($upd)
			{
				$upd->isdelete = '1';
				$upd->save();
			}
		}

	}

?>