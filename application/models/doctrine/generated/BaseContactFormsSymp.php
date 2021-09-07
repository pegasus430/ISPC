<?php

	abstract class BaseContactFormsSymp extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('contact_forms_symptomatology');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('symp_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('last_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('current_value', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('details', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{

			$this->hasOne('ContactForms', array(
				'local' => 'contact_form_id',
				'foreign' => 'id'
			));

			$this->hasOne('SymptomatologyValues', array(
				'local' => 'symp_id',
				'foreign' => 'id'
			));
		}

	}

?>