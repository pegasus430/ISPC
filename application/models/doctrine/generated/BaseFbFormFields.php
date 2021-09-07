<?php

	abstract class BaseFbFormFields extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('fb_fields');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('fieldid', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('patientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('columnno', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('type', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('label', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linkedtable', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linkedfield', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('isrequired', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('validator', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('options', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('columns', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('description', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('content', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>