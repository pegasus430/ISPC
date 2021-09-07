<?php

	abstract class BaseGroupCourseDefaultPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('group_course_default_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('master_group_id', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('shortcutid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canadd', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canedit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('canview', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('candelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
//			$this->hasOne('GroupDefault', array(
//	  	            'foreign' => 'id'
//	            ));
		}

	}

?>