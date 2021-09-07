<?php

	abstract class BaseFormBlockClientSymptoms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_client_symptoms');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			//ISPC-2516 Carmen 09.07.2020
			$this->hasColumn('source', 'enum', 3, array(
					'type' => 'enum',
					'length' => 3,
					'fixed' => false,
					'unsigned' => false,
					'values' =>
					array(
							0 => 'cf',
							1 => 'charts',
					),
					'primary' => false,
					'default' => 'cf',
					'notnull' => true,
					'autoincrement' => false,
			));
			$this->hasColumn('symptom_date', 'timestamp', null, array(
					'type' => 'timestamp',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'autoincrement' => false,
			));
			//--
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('symptom_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('severity', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('sorrowfully', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comment', 'string',NULL, array('type' => 'string','length' => NULL));
			$this->hasColumn('care_specifications','string',NULL, array('type' => 'string','length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2516 Carmen 09.07.2020
			$this->hasOne('ContactForms', array(
					'local' => 'contact_form_id',
					'foreign' => 'id'
			));
		}
	}

?>