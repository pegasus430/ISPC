<?php

	abstract class BaseGroupPrevileges extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('groupprevileges');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('groupid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('moduleid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canadd', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canedit', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canview', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('candelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->hasOne('Usergroup', array(
				'local' => 'groupid',
				'foreign' => 'id'
			));

			$this->hasOne('Modules', array(
				'local' => 'moduleid',
				'foreign' => 'id'
			));

			$this->actAs(new Timestamp());
		}

	}

?>