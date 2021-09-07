<?php

	Doctrine_Manager::getInstance()->bindComponent('DashboardLabels', 'SYSDAT');

	abstract class BaseDashboardLabels extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dashboard_labels');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('font_color', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('action', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			$this->hasMany('DashboardLabels', array(
				'local' => 'action',
				'foreign' => 'action'
			));
		}

	}

?>