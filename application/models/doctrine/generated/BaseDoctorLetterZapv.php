<?php

	abstract class BaseDoctorLetterZapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('doctor_letter_zapv');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('subject', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('address', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('letter_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('letter_docfax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('letter_username', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('content1', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('patientmaster_chk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verord_status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_periods', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('main_diagnosis', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('complex_symptom', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('symptomatics_str', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('medication', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('medi_action', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('measures_str', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('signature', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lettertype', 'integer', 11, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>