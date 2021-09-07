<?php

	abstract class BaseSapvverordnungPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapvverordnung_permissions');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('subdiv_id', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('subdiv_order', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('SapvverordnungSubdivisions', array(
				'local' => 'subdiv_id',
				'foreign' => 'id'
			));
		}

	}

?>