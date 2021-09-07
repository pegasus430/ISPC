<?php

	abstract class BasePatientStammblattsapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_stammblattsapv');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenverfugung', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('betreuung', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('betreuer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuer_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuer_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vorsorgevollmacht', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('bevollmachtigter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bevollmachtigter_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bevollmachtigter_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('allergien', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('behandlungswunsch', 'text', NULL, array('type' => 'text', 'length' => NUll));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>