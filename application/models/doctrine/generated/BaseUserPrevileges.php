<?php

	Doctrine_Manager::getInstance()->bindComponent('UserPrevileges', 'IDAT');

	abstract class BaseUserPrevileges extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('userprevileges');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('moduleid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canadd', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canedit', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canview', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('candelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'userid',
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