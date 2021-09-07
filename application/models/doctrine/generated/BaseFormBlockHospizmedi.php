<?php

	abstract class BaseFormBlockHospizmedi extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_hospiz_medi');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medication', 'integer', 20, array('type' => 'integer', 'length' => 20));
			$this->hasColumn('sprepared', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('given', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>