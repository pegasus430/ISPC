<?php

	abstract class BaseBreSapvControl extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bre_sapv_control');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('qty', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
		}

	}

?>