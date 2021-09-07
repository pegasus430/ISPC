<?php

	Doctrine_Manager::getInstance()->bindComponent('OrgSteps', 'SYSDAT');

	abstract class BaseOrgSteps extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('org_steps');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('path', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('master', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('todo_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ismanual', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('order', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());

			$this->hasOne('OrgPaths', array(
				'local' => 'path',
				'foreign' => 'id'
			));
		}

	}

?>