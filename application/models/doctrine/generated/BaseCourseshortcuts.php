<?php

	abstract class BaseCourseshortcuts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('course_shortcuts');
			$this->hasColumn('shortcut_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('course_fullname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isfilter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isbold', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isitalic', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isunderline', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('font_color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>