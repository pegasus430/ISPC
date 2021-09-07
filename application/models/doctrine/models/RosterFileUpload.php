<?php

	Doctrine_Manager::getInstance()->bindComponent('RosterFileUpload', 'SYSDAT');

	class RosterFileUpload extends BaseRosterFileUpload {

		public function getClientFiles($clientid)
		{
			$patient = Doctrine_Query::create()
				->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
				->from('RosterFileUpload')
				->where('clientid="' . $clientid . '"');
			$fl = $patient->execute();
			$filearray = $fl->toArray();
			return $filearray;
		}

	}

?>