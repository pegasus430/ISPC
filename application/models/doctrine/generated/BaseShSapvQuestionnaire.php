<?php

	abstract class BaseShSapvQuestionnaire extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('shsapv_questionnaire');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('no_sapv_data', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_data_exists', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('beantragt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativarzt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('diagno_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
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
			$this->hasColumn('med_infusion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_pca_pumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_inhalation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_fest', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('med_bedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
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
			$this->hasColumn('sh_doc_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sh_doctor_user', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stampusers', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>
