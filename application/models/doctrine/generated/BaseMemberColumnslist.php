<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberColumnslist', 'SYSDAT');

	abstract class BaseMemberColumnslist extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('member_columnslist');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('columnName', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->hasOne('MemberColumns2tabs', array(
				'local' => 'id',
				'foreign' => 'column'
			));
		}

	}

?>