<?php
abstract class BaseAnlage2 extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('anlage2');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('admission_date', 'date', 255, array('type' => 'date', 'length' => 255));
		$this->hasColumn('admission', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('location', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('patient_care', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('members_included', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('icd_diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('diagnosis_date', 'date', 255, array('type' => 'date', 'length' => 255));
		$this->hasColumn('participants', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pain_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('name_therapy', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('pain_level', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('medication_form', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('wound_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));

	}

	function setUp()
	{
		$this->actAs(new Createtimestamp());
	}

}

?>