<?php

	abstract class BaseTabMenus extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('tab_menus');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('menu_title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('menu_link', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parent_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sortorder', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('efa_menu', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasMany('TabMenuClient', array(
			    'local' => 'id',
			    'foreign' => 'menu_id'
			));
		}

	}

?>