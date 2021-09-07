<?php

	abstract class BaseShortcutPrevileges extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('shortcutprevileges');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('groupid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('shortcutid', 'integer', 8, array('type' => 'integer', 'length' => 8));
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

			$this->actAs(new Timestamp());
		}

	}

?>