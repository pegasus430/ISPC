<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumns2tabs', 'SYSDAT');

	abstract class BaseMemberColumns2tabs extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('member_columns2tabs');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('column', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('tab', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('user_selectable', 'int', 1, array('type' => 'string', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('MemberColumnslist', array(
				'local' => 'column',
				'foreign' => 'id'
			));
		}

	}

?>