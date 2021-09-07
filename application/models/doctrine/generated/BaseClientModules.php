<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientModules', 'IDAT');

	abstract class BaseClientModules extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_modules');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('moduleid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canaccess', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'clientid',
				'foreign' => 'id'
			));

			$this->hasOne('Modules', array(
				'local' => 'moduleid',
				'foreign' => 'id'
			));

			$this->actAs(new Timestamp());
		}

	}

?>