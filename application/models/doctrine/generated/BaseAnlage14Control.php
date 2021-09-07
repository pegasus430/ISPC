<?php

	abstract class BaseAnlage14Control extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage14_control');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qty', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>