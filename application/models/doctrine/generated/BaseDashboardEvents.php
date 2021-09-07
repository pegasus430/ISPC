<?php

	abstract class BaseDashboardEvents extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dashboard_events');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('group_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tabname', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('triggered_by', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('title', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('complete_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('complete_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('until_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

	}

?>