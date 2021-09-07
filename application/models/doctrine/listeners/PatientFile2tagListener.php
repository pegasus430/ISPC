<?php

	class PatientFile2tagListener extends Doctrine_Record_Listener {

		public function postInsert(Doctrine_Event $event)
		{
			$sys_gen = $event->getInvoker()->system_generated;
			$file_id = $event->getInvoker()->id;
			
			if($file_id && $sys_gen == "1")
			{
				$ins_tag = Application_Form_PatientFile2tags::insert_file_tags($file_id, array("2"));
			}
		}

	}

?>