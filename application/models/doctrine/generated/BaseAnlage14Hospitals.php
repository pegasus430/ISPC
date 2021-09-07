<?php

	abstract class BaseAnlage14Hospitals extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage14_hospitals');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('formid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('hospital_start', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('hospital_end', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>