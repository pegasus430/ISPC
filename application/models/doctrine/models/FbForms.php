<?php

	Doctrine_Manager::getInstance()->bindComponent('FbForms', 'SYSDAT');

	class FbForms extends BaseFbForms {

		public static function getForms()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$q = Doctrine_Query::create()
				->select("f.*,b.*")
				->from("FbFormClients f")
				->innerjoin("f.FbForms b")
				->where("f.clientid = '" . $logininfo->clientid . "'");
			$qe = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		
			$clarr = array();
			foreach($qe as $key => $val)
			{
				$clarr[] = array('id' => $val['formid'], 'formname' => $val['FbForms']['formname']);
			}

			return $clarr;
		}

	}

?>