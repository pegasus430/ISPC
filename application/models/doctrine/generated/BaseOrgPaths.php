<?php

	Doctrine_Manager::getInstance()->bindComponent('OrgPaths', 'SYSDAT');

	abstract class BaseOrgPaths extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('org_paths');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('function', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			$this->hasOne('OrgSteps', array(
				'local' => 'id',
				'foreign' => 'path'
			));
		}

	}

?>