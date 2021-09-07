<?php

	abstract class BaseKinderEntranceAssessmentSorrowfully extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kinder_entrance_assessment_sorrowfully');
			
			$this->hasColumn('id', 'integer', 11, 
					array('type' => 'integer', 
							'length' => 10, 
							'primary' => true, 
							'autoincrement' => true));
			
			$this->hasColumn('ipid', 'string', 255, 
					array('type' => 'string', 
							'length' => 255,
							'foreign' => 'id',
					));
			
			$this->hasColumn('assessment_id', 'integer', 11,
					array('type' => 'integer',
							'length' => 11
					));
			
			$this->hasColumn('distress', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('hemoptysis', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('airway_obstruction', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('respiratory_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('dysuria', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('hematuria', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('voiding_dysfunction', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('urogenital_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('disorder', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('intracranial_pressure', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('restlessness', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('spasticity', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('cerebral_seizures', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('developmental_disorder', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('autoaggression', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('insomnia', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('depressive', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('neuroligical_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('wound_a', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('anorexia', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('mucositis', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('dysphagia', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('throw_up', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('hematemesis', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('icterus', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('ileus', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('ascites', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('diarrhea', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('constipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('gastrointestinal_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('urinary', 'integer', 1, array('type' => 'integer', 'length' => 1));
			

			
		}
			
		function setUp()
		{
			//$this->actAs(new Timestamp());
			$this->hasOne('KinderEntranceAssessment', array(
					'local' => 'assessment_id',
					'foreign' => 'id'));
		}
			
	}
	
?>