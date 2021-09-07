<?php

	Doctrine_Manager::getInstance()->bindComponent('columns2tabs', 'SYSDAT');

	abstract class BaseColumns2tabs extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('columns2tabs');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('column', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('tab', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('user_selectable', 'int', 1, array('type' => 'string', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('Columnslist', array(
				'local' => 'column',
				'foreign' => 'id'
			));
		}

	}

?>