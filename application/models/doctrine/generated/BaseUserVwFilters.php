<?php

	abstract class BaseUserVwFilters extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_vw_filters');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('client', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('status_color_g', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_y', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_r', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_b', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_inactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_blue', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_purple', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status_color_grey', 'integer', 1, array('type' => 'integer', 'length' => 1));
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