<?php

	abstract class BasePlansMediPrint extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('plans_medi_print');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('plansmedi_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}
		

	}

?>