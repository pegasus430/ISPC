<?php

	Doctrine_Manager::getInstance()->bindComponent('SapvExportHistory', 'SYSDAT');

	class SapvExportHistory extends BaseSapvExportHistory {

		public function get_sapv_export_history($clientid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$get_history = Doctrine_Query::create()
				->select('id, client, parent, ipid, create_date')
				->from('SapvExportHistory')
				->where('client = "' . $clientid . '"');
			$get_history_res = $get_history->fetchArray();

			if($get_history_res)
			{
				return $get_history_res;
			}
			else
			{
				return false;
			}
		}

	}

?>