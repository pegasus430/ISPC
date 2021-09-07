<?php

	abstract class BaseFallprotocolForm extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('fallprotocol_form');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date_fall', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('wasthere', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('falllocation', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('prehistory_known', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('guest_striking', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('guest_fixed', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fall_led', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('shoes', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('glasses', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('auditiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('walking', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('external_circumstances', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_contact', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('consequences_visible', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('reaction_fall', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('guest_transport', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

	}

?>