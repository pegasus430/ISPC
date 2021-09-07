<?php

	abstract class BaseMembersSepaSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('members_sepa_settings');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('memberid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('member2membershipsid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('howoften', 'enum', null, array(
					'type' => 'enum', 
					'notnull' => false,
					'values' => array('monthly', 'quarterly', 'biannual', 'annually'),
					'default' => 'monthly'));
		
			$this->hasColumn('when_day', 'int', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('when_month', 'int', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('amount', 'float', null);
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
				
			
					
		}

		function setUp()
		{
			$this->hasOne('Member', array(
				'local' => 'memberid',
				'foreign' => 'id'
			));
			
			$this->actAs(new Timestamp());
		}

	}

?>