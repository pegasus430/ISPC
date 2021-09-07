<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumns2users', 'SYSDAT');

	abstract class BaseMemberColumns2users extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('member_columns2users');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('c2t_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('user_id', 'int', 11, array('type' => 'string', 'length' => 11));
		}

		function setUp()
		{
			$this->hasOne('MemberColumns2tabs', array(
				'local' => 'c2t_id',
				'foreign' => 'id'
			));
		}

	}

?>