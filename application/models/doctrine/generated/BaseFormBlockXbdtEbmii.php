<?php

	abstract class BaseFormBlockXbdtEbmii extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_xbdt_ebm_ii');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('action_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('action_value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('patient_xbdt_action_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}
	}

?>