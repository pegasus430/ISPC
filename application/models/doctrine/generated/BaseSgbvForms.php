<?php

	abstract class BaseSgbvForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sgbv_forms');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('form_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('first', 'follow')));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('approved_limit', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('no_evaluation_possible', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('accident', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('regulation_time', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospital_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ambulant_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('size_degree', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('preparations', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wound_findings', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('temporarily', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('free_of_charge', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('SgbvFormsItems', array(
				'local' => 'id',
				'foreign' => 'sgbv_form_id'
			));
		}

	}

?>