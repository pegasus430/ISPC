<?php

	abstract class BaseClientContactFormSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_contact_form_settings');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('date', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('start_date','end_date', 'greater_duration'), 'default' => 'start'));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>