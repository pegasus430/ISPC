<?php

abstract class BaseMember2MembershipsHistory extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('member2memberships_history');
		
		$this->hasColumn('action', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('revision', 'integer', 6, array('type' => 'integer', 'length' => 6));
		$this->hasColumn('dt_datetime', 'datetime', NULL);
		
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('member', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('membership', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('membership_price', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('end_reasonid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
		$this->hasMany('Member2MembershipsHistory', array(
				'local' => 'id',
				'foreign' => 'id'
		));
		$this->hasOne('Member2Memberships', array(
				'local' => 'id',
				'foreign' => 'id'
		));
			
	}
	

}

?>