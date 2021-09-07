<?php

abstract class BaseMemberDonationsHistory extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('member_donations_history');
		
		$this->hasColumn('action', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('revision', 'integer', 6, array('type' => 'integer', 'length' => 6));
		$this->hasColumn('dt_datetime', 'datetime', NULL);
		
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('member', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('donation_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('amount', 'decimal', 11, array('scale' => 2));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('merged_parent', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('merged_slave', 'integer', 11, array('type' => 'integer','length' => 11));
	}
	
	function setUp()
	{
		$this->hasMany('MemberDonationsHistory', array(
				'local' => 'id',
				'foreign' => 'id'
		));
		$this->hasOne('MemberDonations', array(
				'local' => 'id',
				'foreign' => 'id'
		));
		
	}
	

}

?>