<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockTreatmentPlan', 'MDAT');

	class FormBlockTreatmentPlan extends BaseFormBlockTreatmentPlan {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblocktreatmentplan_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblocktreatmentplan';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockTreatmentPlan';
		const PATIENT_FILE_TITLE    = 'FormBlockTreatmentPlan PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblocktreatmentplan';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FormBlockTreatmentPlan was created';
		
		
		

		public function getPatientFormBlockTreatmentPlan($ipid, $contact_form_id)
		{

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockTreatmentPlan')
				->where('ipid =?', $ipid)
				->andWhere('contact_form_id =?', $contact_form_id);
			
			$groupsarray = $groups_sql->fetchArray();


			if($groupsarray)
			{
				return $groupsarray;
			}
		}

		/*
		 * ISPC-2819 Lore 11.02.2021
		 */
		public function getPatientFormBlockTreatmentPlanByIpid($ipid)
		{
		    
		    $groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockTreatmentPlan')
		    ->where('ipid =?', $ipid)
		    ->orderBy('id DESC')
		    ->andWhere('isdelete = 0')
		    ->limit('1');
		    
		    $groupsarray = $groups_sql->fetchArray();
		    
		    $wkm_forms_behand = array();
		    
		    if($groupsarray)
		    {
		        $wkm_forms_behand['medic_goal']     = is_array($groupsarray[0]['medicine']) ? $groupsarray[0]['medicine'][0] : '';
		        $wkm_forms_behand['medic_plan']     = is_array($groupsarray[0]['medicine']) ? $groupsarray[0]['medicine'][1] : '';
		        $wkm_forms_behand['care_goal']      = is_array($groupsarray[0]['maintenance']) ? $groupsarray[0]['maintenance'][0] : '';
		        $wkm_forms_behand['care_plan']      = is_array($groupsarray[0]['maintenance']) ? $groupsarray[0]['maintenance'][1] : '';
		        $wkm_forms_behand['psy_goal']       = is_array($groupsarray[0]['psychological']) ? $groupsarray[0]['psychological'][0] : '';
		        $wkm_forms_behand['psy_plan']       = is_array($groupsarray[0]['psychological']) ? $groupsarray[0]['psychological'][1] : '';
		        $wkm_forms_behand['social_goal']    = is_array($groupsarray[0]['social_work']) ? $groupsarray[0]['social_work'][0] : '';
		        $wkm_forms_behand['social_plan']    = is_array($groupsarray[0]['social_work']) ? $groupsarray[0]['social_work'][1] : '';
		        $wkm_forms_behand['spiritual_goal'] = is_array($groupsarray[0]['spiritual']) ? $groupsarray[0]['spiritual'][0] : '';
		        $wkm_forms_behand['spiritual_plan'] = is_array($groupsarray[0]['spiritual']) ? $groupsarray[0]['spiritual'][1] : '';
		        $wkm_forms_behand['physio_goal']    = is_array($groupsarray[0]['physical_therapy']) ? $groupsarray[0]['physical_therapy'][0] : '';
		        $wkm_forms_behand['physio_plan']    = is_array($groupsarray[0]['physical_therapy']) ? $groupsarray[0]['physical_therapy'][1] : '';
		        $wkm_forms_behand['breath_goal']    = is_array($groupsarray[0]['relaxation_techniques']) ? $groupsarray[0]['relaxation_techniques'][0] : '';
		        $wkm_forms_behand['breath_plan']    = is_array($groupsarray[0]['relaxation_techniques']) ? $groupsarray[0]['relaxation_techniques'][1] : '';
		        
		    }

		    return $wkm_forms_behand;
		    
		}
	}

?>