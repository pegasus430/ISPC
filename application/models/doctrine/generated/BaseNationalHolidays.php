<?php

	abstract class BaseNationalHolidays extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('national_holidays');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('holiday', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('NationalHolidays2State', array(
				'local' => 'id',
				'foreign' => 'holiday_id'
			));


			$this->actAs(new Timestamp());
		}

	}

?>