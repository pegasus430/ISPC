<?php

	abstract class BasePaycenterZip extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('paycenter_zip');
			$this->hasColumn('id', 'integer', 20, array('type' => 'integer', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('paycenter', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('zip', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>