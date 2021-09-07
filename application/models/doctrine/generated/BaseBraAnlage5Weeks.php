<?php
	abstract class BaseBraAnlage5Weeks extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bra_anlage5_weeks');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anlage5_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('start_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('end_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('hospital_days', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('products', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('doctor_km', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('nurse_km', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('doctor_weg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nurse_weg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>
