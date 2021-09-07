<?php

	abstract class BaseWoundDocumentation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('wound_documentation');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('w_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_type', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_size', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_depth', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_width', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_type_degree', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_type_more', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('w_wounddescription', 'text', NULL, array('type' => 'text', 'length' => NULL)); //ISPC-2465 Carmen 14.10.2019
			$this->hasColumn('w_localisation', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('w_treatment_goals', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_change_day', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_change_week', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_treatment_other', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('w_wet', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_clean', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_clean_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_disinfection', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_disinfection_gel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_disinfection_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('w_dressings', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_dressings_product', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('w_dressings_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('w_dressings', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_dressings_product', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('w_dressings_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('w_dressings_second', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_dressings_second_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_dressings_second_product', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('w_dressings_second_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('w_surrounding_skin_protect', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_surrounding_skin_protect_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_surrounding_skin_protect_product', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('w_surrounding_skin_protect_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('w_odor', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_odor_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('w_exudation_therapy', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('w_exudation_therapy_more', 'string', 255, array('type' => 'string', 'length' => 255));
           
			$this->hasColumn('w_isclosed', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>