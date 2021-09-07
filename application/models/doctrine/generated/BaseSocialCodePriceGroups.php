<?php

abstract class BaseSocialCodePriceGroups extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('social_code_price_groups');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('list', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
	}

	function setUp()
	{
		
		
		$this->hasOne('SocialCodeGroups', array(
				'local' => 'groupid',
				'foreign' => 'id'
		));
		
		
		$this->hasOne('SocialCodePriceList', array(
				'local' => 'list',
				'foreign' => 'id'
		));

		
	
	
	}
}

?>