<?php

	abstract class BaseSapvEvaluation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_evaluation');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('admissionid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2655 Ancuta 16.03.2020
			$this->hasMany('SapvEvaluationMsp1', array(
			    'local' => 'id',
			    'foreign' => 'form_id'
			));
			$this->hasMany('SapvEvaluationMsp2', array(
			    'local' => 'id',
			    'foreign' => 'form_id'
			));
			$this->hasMany('SapvEvaluationIpos1', array(
			    'local' => 'id',
			    'foreign' => 'form_id'
			));
			$this->hasMany('SapvEvaluationIpos2', array(
			    'local' => 'id',
			    'foreign' => 'form_id'
			));
			//--
		}

	}

?>