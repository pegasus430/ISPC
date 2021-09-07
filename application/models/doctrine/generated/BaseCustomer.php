<?php

	abstract class BaseCustomer extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('customer');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('username', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('password', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('address', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gender', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('dob', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('newsletter', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('sessionid', 'string', 255, array('type' => 'string', 'length' => 255));
		}

	}

?>