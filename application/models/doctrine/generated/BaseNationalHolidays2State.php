<?php

	abstract class BaseNationalHolidays2State extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('national_holidays2state');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('holiday_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('state', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('NationalHolidays', array(
				'local' => 'holiday_id',
				'foreign' => 'id'
			));

			$this->hasOne('Client', array(
				'local' => 'state',
				'foreign' => 'country'
			));

			$this->actAs(new Timestamp());
		}

	}

?>