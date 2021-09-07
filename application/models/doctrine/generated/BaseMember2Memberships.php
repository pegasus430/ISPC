<?php

abstract class BaseMember2Memberships extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('member2memberships');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('member', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('membership', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('membership_price', 'decimal', 10, array('scale' => 2));
		$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		//ispc 1739
		$this->hasColumn('end_reasonid', 'integer', 11, array('type' => 'integer','length' => 11));		
	}
	
	function setUp()
	{
	    $this->hasOne('Member', array(
	        'local' => 'member',
	        'foreign' => 'id'
	    ));
	    
	 	$this->actAs(new Timestamp());
	}
	

}

?>