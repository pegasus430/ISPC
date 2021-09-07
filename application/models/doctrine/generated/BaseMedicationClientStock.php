<?php

	abstract class BaseMedicationClientStock extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('medication_client_stock');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medicationid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//ISPC-2768 Lore 05.01.2021
			$this->hasColumn('btm_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('methodid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sonstige_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('done_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>