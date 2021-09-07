<?php
	//ISPC-661 pct.14 Carmen 16.09.2020
    //#ISPC-2512PatientCharts
	Doctrine_Manager::getInstance()->bindComponent('OrganicEntriesExitsSets', 'MDAT');

	class OrganicEntriesExitsSets extends BaseOrganicEntriesExitsSets {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'organicentriesexitssets_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_organicentriesexitssets';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'OrganicEntriesExitsSets';
		const PATIENT_FILE_TITLE    = 'OrganicEntriesExitsSets PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'organicentriesexitssets';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'OrganicEntriesExitsSets was created';

	}

?>