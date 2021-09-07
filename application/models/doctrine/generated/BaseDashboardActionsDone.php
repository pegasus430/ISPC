<?php

	Doctrine_Manager::getInstance()->bindComponent('BaseDashboardActionsDone', 'SYSDAT');

	abstract class BaseDashboardActionsDone extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dashboard_actions_done');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('event', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('extra', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('s', 'u')));
			$this->hasColumn('done', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('done_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>