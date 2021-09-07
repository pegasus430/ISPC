<?php

abstract class BaseFormBlockIpos extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('form_block_ipos');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('ipos0', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('ipos1a', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ipos1b', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ipos1c', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('ipos2a', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2b', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2c', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2d', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2e', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2f', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2g', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2h', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2i', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2j', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2k', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2l', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos2m', 'integer', 1, array('type' => 'integer','length' => 1));
		
		$this->hasColumn('ipos2ktext', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ipos2ltext', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('ipos2mtext', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('ipos3', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos4', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos5', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos6', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos7', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos8', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ipos9', 'integer', 1, array('type' => 'integer','length' => 1));
		
		$this->hasColumn('score', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('special', 'string', 255, array('type' => 'string','length' => 255));


        $this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('user', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('status', 'integer', 11, array('type' => 'integer','length' => 11));

		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
		$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
		$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint','length' => 20));
	}

	function setUp()
	{
		$this->actAs(new TimeStamp());
	}

}

?>
