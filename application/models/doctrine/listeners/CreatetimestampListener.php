<?php

	class CreatetimestampListener extends Doctrine_Record_Listener {

		public function preInsert(Doctrine_Event $event)
		{
			$event->getInvoker()->create_date = date("Y-m-d H:i:s", time());

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$event->getInvoker()->create_user = $logininfo->userid;
		}

	}

?>