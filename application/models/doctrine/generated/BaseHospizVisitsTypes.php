<?php

	abstract class BaseHospizVisitsTypes extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('hospiz_visits_types');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('grund', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('old_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('billable', 'integer', 1, array('type' => 'integer', 'length' => 1));

		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>