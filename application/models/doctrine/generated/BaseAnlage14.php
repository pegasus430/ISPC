<?php

	abstract class BaseAnlage14 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage14');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('raapv_sapv_date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('khws_sapv_date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('stathospiz_sapv_date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('pwunsch_sapv_date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('dead_sapv_date', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('aapv_start', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('aapv_end', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('hospiz_start', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('hospiz_end', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('patient_wish_start', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('patient_wish_end', 'datetime', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('overall_non_hospiz_visits', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('overall_phones', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('overall_beko', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('overall_folgeko', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('overall_hospiz_visits', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>