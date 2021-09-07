<?php

	abstract class BaseMdkSapvQuestionnaire extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('mdk_sapv_questionnaire');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('no_sapv_data', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_data_exists', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('patient_health_insurance', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('beantragt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('familydoctor', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativarzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflegedienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('contactperson', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hospizdienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('diagno_main', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('diagno_meta', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('diagno_side', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('operativ', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('operativ_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('chemo', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemo_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('radiatio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('radiatio_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospital_period', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospital_location', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('symptom_control', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('med_oral', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_iv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_im', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_sc', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_peg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_infusion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_pca_pumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_inhalation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_fest', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_fest_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('med_bedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_bedarf_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
            //ISPC-2765,Elena,26.01.2021
            $this->hasColumn('med_vernebelung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lymphdrainage', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemotherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('radiatio_needed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('atemtherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sauerstoffgabe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urostoma', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anuspraeter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tracheostoma', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lagerung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ablaufsonde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wound_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wound_treatment_description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('family_social_environment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('mdk_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('mdk_sapv_team', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mdk_sapv_pallarz', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stampuser', 'string', 255, array('type' => 'string', 'length' => 255));
			//ISPC-2765,Elena,26.01.2021
            $this->hasColumn('psycho', 'text', NULL, array('type' => 'text', 'length' => NULL));
            $this->hasColumn('vital', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('haut', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('chk_schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_dyspnoe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_uebelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_obstipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_durchfall', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_depression', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chk_haut', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('social_support_needed', 'text', NULL, array('type' => 'text', 'length' => NULL));


            $this->hasColumn('bzrr', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('metastasen', 'integer', 1, array('type' => 'integer', 'length' => 1));
            $this->hasColumn('metastasen_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
            $this->hasColumn('az', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('ez', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('mdk_ort', 'string', 255, array('type' => 'string', 'length' => 255));

            $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>