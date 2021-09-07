<?php

	abstract class BaseUserCourseFilters extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_course_filters');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'bigint', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'user',
				'foreign' => 'id'
			));
			
			
			$this->actAs(new Timestamp());
		}

	}

?>