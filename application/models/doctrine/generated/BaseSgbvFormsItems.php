<?php

	abstract class BaseSgbvFormsItems extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sgbv_forms_items');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('sgbv_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('action_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('approved_limit', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('per_day', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('per_week', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('free_of_charge', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());


			$this->hasOne('SgbvForms', array(
				'local' => 'sgbv_form_id',
				'foreign' => 'id'
			));
		}

	}

?>