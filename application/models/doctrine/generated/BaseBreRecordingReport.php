<?php

	Doctrine_Manager::getInstance()->bindComponent('BreRecordingReport', 'MDAT');

	abstract class BaseBreRecordingReport extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bre_recording_report');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('curent_medical_history', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('social_history', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('priority_items_1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority_items_2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority_items_3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority_items_4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority_items_5', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('priority_items_6', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('living_will_option', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('attorney_power_option', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('contact_person_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('contact_person_name', 'string', 100, array('type' => 'string', 'length' => 100));
			$this->hasColumn('contact_person_lastname', 'string', 100, array('type' => 'string', 'length' => 100));
			$this->hasColumn('resuscitation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('causal_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('other_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('other_therapy_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('body_condition_inconspicuous', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_red_az', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_kachekt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_fear', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_constipation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_morning_sickness', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('body_condition_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('consciousness_inconspicuous', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_tarnished', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_restless', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_stupor', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_coma', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_slowed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_disorientation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_more', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('consciousness_more_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('skin_inconspicuous', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_pale', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_cyanotic', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_ikter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_dry', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_hemoragy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('skin_more_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('edema_uleg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_oleg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_hands', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_eyelids', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_face', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_moving', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('edema_more_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_skin_mucous_membran', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_skin_mucous_membran_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_heart', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_heart_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_lungs', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_lungs_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_abdomen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_abdomen_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_musculo_skeletal', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_musculo_skeletal_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physical_exam_neurological', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('physical_exam_neurological_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('human', 'text', null, array('type' => 'integer', 'length' => null));
			$this->hasColumn('other_findings', 'text', null, array('type' => 'integer', 'length' => null));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>