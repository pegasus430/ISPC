<?php

	abstract class BaseFbFieldValues extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('fb_fieldvalues');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('fieldid', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('patientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('fieldvalue', 'text', NULL, array('type' => 'text'));
		}

		function setUp()
		{

			$this->actAs(new Timestamp());
		}

	}

?>