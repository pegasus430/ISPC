<?php

	abstract class BaseVisitKoordination extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('visit_koordination');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('visit_begin_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_begin_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_end_date_h', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_end_date_m', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('visit_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('quality', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('fahrtzeit', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('fahrtstreke_km', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('visit_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('visit_care_instructions', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>