<?php

	abstract class BaseMenus extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('menus');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('menu_title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parent_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('left_position', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('top_position', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sortorder', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sortorder_top', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('foradmin', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('forsuperadmin', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('openin', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('menu_link', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-2782 CRISTI.C 21.01.2021
			$this->hasColumn('menu_info', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('group_menu_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('menu_icon', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>