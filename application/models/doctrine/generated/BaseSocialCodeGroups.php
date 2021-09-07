<?php

	abstract class BaseSocialCodeGroups extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_groups');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('groupname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('groupshortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('group_order', 'integer', 10, array('type' => 'integer', 'length' => 10));
		}

		function setUp()
		{
			$this->hasOne('SocialCodeActions', array(
				'local' => 'id',
				'foreign' => 'groupid'
			));

			$this->hasOne('SocialCodePriceGroups', array(
				'local' => 'id',
				'foreign' => 'groupid'
			));
		}

	}

?>