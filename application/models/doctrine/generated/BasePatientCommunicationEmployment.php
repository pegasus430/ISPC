<?php
/*
 * ISPC-2793 Lore 18.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientCommunicationEmployment', 'IDAT');


abstract class BasePatientCommunicationEmployment extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_communication_employment');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verbal_utterances_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('verbal_utterances_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('speech_understanding_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('speech_understanding_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('communication_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('communication_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tools_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tools_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('restlessness_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('restlessness_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('preferences_interests', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('habits', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dislikes', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sole_occupations', 'string', 255, array('type' => 'string', 'length' => 255));

		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>