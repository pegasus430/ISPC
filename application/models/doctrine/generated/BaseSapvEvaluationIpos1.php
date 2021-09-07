<?php


	abstract class BaseSapvEvaluationIpos1 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_evaluation_ipos1');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('health_insurance_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('health_insurance_kassen_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_iknumber', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('problems_a', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problems_b', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problems_c', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('difficulty_breathing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lack_energy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nausea', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vomit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anorexia', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('constipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dry_mouth', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sleepiness', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('limited_mobility', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('more_symptoms1_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('more_symptoms1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('more_symptoms2_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('more_symptoms2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('more_symptoms3_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('more_symptoms3', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('disease', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('concerned_worried', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('depressed_sad', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('peace_yourself', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('feelings_family', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('information_wanted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('practical_problems', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2655 Ancuta 21.09.2020 // TODO-3428
			$this->hasOne('SapvEvaluation', array(
			    'local' => 'form_id',
			    'foreign' => 'id'
			));
			//--
			
			
			
			//ISPC-2655 Ancuta 16.03.2020
			$this->hasOne('SapvEvaluationMsp1', array(
			    'local' => 'id',
			    'foreign' => 'form_id'
			));
			//--
		}

	}

?>