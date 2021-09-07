<?php

	abstract class BaseRpTermination extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('rp_termination');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_health_insurance', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_pat_last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_birthd', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_pat_address', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_zip_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_epid', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('rp_insurance_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_sex', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('rp_start_date_erst', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_date_erst', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_sapv_erst', 'string', 50, array('type' => 'string', 'length' => 50));

			$this->hasColumn('rp_start_date_folge', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_date_folge', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_sapv_folge', 'string', 50, array('type' => 'string', 'length' => 50));

			$this->hasColumn('rp_vat_representative', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_info_dependant', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('rp_sapv_team', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_hausarzt_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_doc_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('rp_doctor_user', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_home_care', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_last_day_sapv', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('rp_sapv_not_needed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_power_requirement_a', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('rp_power_requirement_b', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_sapv_ended', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_sapv_ended_day', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			
			
			$this->hasColumn('rp_hospitalization', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_hospitalization_day', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			$this->hasColumn('rp_patient_death', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_patient_death_day', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			$this->hasColumn('rp_in_hospiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_in_hospiz_day', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			$this->hasColumn('rp_sapv_accordance', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_sapv_accordance_day', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
   
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>