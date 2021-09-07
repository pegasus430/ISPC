<?php

	abstract class BaseFormBlockGoa extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_goa');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('consultation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('recipe_transfer', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('expert_advice', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_i_y1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_i_y2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_i_y3', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('discussion_of_impact', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consultant_discussion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('detailed_report', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_ii_y1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_ii_y2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('charge_ii_y3', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>