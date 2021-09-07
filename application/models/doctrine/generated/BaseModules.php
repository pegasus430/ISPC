<?php

	Doctrine_Manager::getInstance()->bindComponent('Modules', 'SYSDAT');

	abstract class BaseModules extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('modules');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('module', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('parentid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>