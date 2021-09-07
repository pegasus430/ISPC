<?php

	Doctrine_Manager::getInstance()->bindComponent('columnslist', 'SYSDAT');

	abstract class BaseColumnslist extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('columnslist');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('columnName', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->hasOne('Columns2tabs', array(
				'local' => 'id',
				'foreign' => 'column'
			));
		}

	}

?>