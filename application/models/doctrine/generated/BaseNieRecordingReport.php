<?php

	Doctrine_Manager::getInstance()->bindComponent('NieRecordingReport', 'MDAT');

	abstract class BaseNieRecordingReport extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('nie_recording_report');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('admission_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('background_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('neurological_findings_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('physical_findings_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('diagnosis_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('arrangements_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('medication_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('bedarfsmedication_text', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>