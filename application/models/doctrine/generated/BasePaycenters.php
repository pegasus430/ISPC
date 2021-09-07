<?php

	abstract class BasePaycenters extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('paycenters');
			$this->hasColumn('id', 'integer', 20, array('type' => 'integer', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('paycenter', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>