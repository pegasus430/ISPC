<?php

	class IconDefaultPermissionsListener extends Doctrine_Record_Listener {

		public function postInsert(Doctrine_Event $event)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$ins_icon_id = $event->getInvoker()->id;

			if($ins_icon_id && $clientid != '0')
			{
				$group_default = new Application_Form_GroupDefault();
				$ins_perms = $group_default->insert_default_perms($clientid, $ins_icon_id);
			}
		}

	}

?>