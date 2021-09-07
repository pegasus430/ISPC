<?php

	abstract class BaseSymptomatologyPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('symptomatology_permissions');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('setid', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('setorder', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('SymptomatologySets', array(
				'local' => 'setid',
				'foreign' => 'id'
			));
		}

	}

?>