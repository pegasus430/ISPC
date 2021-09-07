<?php

	abstract class BaseGroupsVisitForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('groups_visit_forms');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('groupid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('tabmenu', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('form_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('image', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>