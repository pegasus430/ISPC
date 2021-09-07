<?php

	abstract class BaseBraAnlage5 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bra_anlage5');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('start_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('end_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('hospital_days', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_date', 'date', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('visit_doctor', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('visit_nurse', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('doctor_data', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('nurse_data_i', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('nurse_data_ii', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('nurse_data_iii', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('location_data', 'string', 255, array('type' => 'string', 'length' => 10));
			$this->hasColumn('overall_amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1)); // 0 - saved  1 - invoiced
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>