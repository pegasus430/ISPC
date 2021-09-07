<?php

	Doctrine_Manager::getInstance()->bindComponent('Munster1a', 'MDAT');

	abstract class BaseMunster1a extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('munster1a');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('formular_id', 'integer', 11, array('type' => 'integer', 'length' => 11));

			$this->hasColumn('input_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('input_value', 'text', NULL, array('type' => 'text', 'length' => NULL));

			
			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>