<?php

	abstract class BaseStammblattlmu extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('stammblatt_lmu');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pattel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cntpers1name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cntpers1tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cntpers1handy', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenverfugung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bevollmachtigter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('angehorige', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('therapy', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('wirdversorgt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('notruf', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('morephones', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('fachdienst_entry', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));

			// ISPC-2327 (23.01.2019)
			$this->hasColumn('client_working_schedule', 'text', null, array('type' => 'text', 'length' => NULL));
			

		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>