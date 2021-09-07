<?php
abstract class BaseMemberMembershipEnd extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('member_membership_end');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('description', 'string',255, array('type' => 'string','length' => 255));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	 	$this->actAs(new Timestamp());
	}
	

}

?>