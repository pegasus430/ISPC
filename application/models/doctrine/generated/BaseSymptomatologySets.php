<?php

	abstract class BaseSymptomatologySets extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology_sets');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

	}

?>