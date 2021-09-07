<?php

	abstract class BaseForms2Items extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('forms2items');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('form', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('item', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>