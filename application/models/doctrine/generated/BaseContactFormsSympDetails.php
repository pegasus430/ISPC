<?php

	abstract class BaseContactFormsSympDetails extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('contact_forms_symptomatology_details');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('entry_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('detail_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{

			$this->hasOne('ContactForms', array(
				'local' => 'contact_form_id',
				'foreign' => 'id'
			));

			$this->hasOne('ContactFormsSymp', array(
				'local' => 'entry_id',
				'foreign' => 'id'
			));
		}

	}

?>