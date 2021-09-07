<?php

	abstract class BaseDoctorLetterFaxvor extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('doctor_letter_faxvor');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('letter_title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('content1', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('content2', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('content3', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('sapv_arzt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_ppflegekraft', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('telefonat', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('telefonat_von', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('telefonat_bis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('km_doppelt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fahrdauer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('letter_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			//missing doctor_id
//			$this->hasColumn('doctor_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('subject', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('address', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('letter_docfax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('selectedchecks', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lettertype', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new PatientInsert());
			$this->actAs(new Trigger());
		}

	}

?>