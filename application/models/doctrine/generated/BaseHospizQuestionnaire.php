<?php

	abstract class BaseHospizQuestionnaire extends Doctrine_Record {

		function setTableDefinition()
		{
				$this->setTableName('hospiz_questionnaire');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('application_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('admission_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('initial_application', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('renew_application', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('diagnostic_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('metastasen_options', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('metastasen_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('findings_az', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('findings_ez', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('findings_height_weight', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('findings_skin_condition', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mental_disorders', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('operativ', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('operativ_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('chemo', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemo_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('radiatio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('radiatio_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('symptom_schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_dyspnoe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_obstipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_durchfalle', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_depression', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_angste', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_haut', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('symptom_control', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('med_oral', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_iv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_im', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_sc', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_infusion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_nebuliser', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_inhalation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('kg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lymphdrainage', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemotherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('radiatio_needed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('atemtherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sauerstoffgabe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bzrr_control', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urostoma', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anuspraeter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tracheostoma', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wound_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('family_social_environment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('required_psychosocial_support', 'text', NULL, array('type' => 'text', 'length' => NULL));
			//ISPC-2647 Lore 05.08.2020
			$this->hasColumn('hospiz_nord', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('active', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('user', 'integer', 20, array('type' => 'integer', 'length' => 20));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>