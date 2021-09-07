<?php

	Doctrine_Manager::getInstance()->bindComponent('columns2users', 'SYSDAT');

	abstract class BaseColumns2users extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('columns2users');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('c2t_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('user_id', 'int', 11, array('type' => 'string', 'length' => 11));
			//ISPC-2479 Ancuta 01.11.2020
			$this->hasColumn('is_primary', 'int', 1, array('type' => 'string', 'length' => 1));
			//-- 
		}

		function setUp()
		{
			$this->hasOne('Columns2tabs', array(
				'local' => 'c2t_id',
				'foreign' => 'id'
			));
		}

	}

?>