<?php
/*
 * ISPC-2788 Lore 08.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientNutritionInfo', 'IDAT');


abstract class BasePatientNutritionInfo extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_nutrition_info');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nutritional_status', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('allergies_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('allergies_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('oral_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('oral_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('oral_offer_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('oral_offer_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tube_feeding_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('tube_feeding_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rinsing_required_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('rinsing_required_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('food_consistency_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('food_consistency_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('independence', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('independence_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('enrichment_required_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('enrichment_required_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('food_preferences_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('food_dislikes_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('food_particular_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('food_meals_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('manufacturer_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('thicken_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('thicken_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('administration_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('administration_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('amount_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('liquid_preferences_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('liquid_dislikes_text', 'string', 255, array('type' => 'string', 'length' => 255));

		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>