<?php
/**
 * ISPC-2508 Carmen 23.01.2020
 *  Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
 */

	Doctrine_Manager::getInstance()->bindComponent('FormBlockArtificialEntriesExits', 'MDAT');

	class FormBlockArtificialEntriesExits extends BaseFormBlockArtificialEntriesExits {
		
		/**
		 * translations are grouped into an array
		 * @var unknown
		 */
		const LANGUAGE_ARRAY    = 'formblockartificialentriesexits_lang';
		
		/**
		 * define the FORMID and FORMNAME, if you want to piggyback some triggers
		 * @var unknown
		 */
		const TRIGGER_FORMID    = null;
		const TRIGGER_FORMNAME  = 'frm_formblockartificialentriesexits';
		
		/**
		 * insert into patient_files will use this
		 */
		const PATIENT_FILE_TABNAME  = 'FormBlockArtificialEntriesExits';
		const PATIENT_FILE_TITLE    = 'FormBlockArtificialEntriesExits PDF'; //this will be translated
		
		/**
		 * insert into patient_course will use this
		 */
		const PATIENT_COURSE_TYPE       = 'K'; 
		
		const PATIENT_COURSE_TABNAME    = 'formblockartificialentriesexits';
		
		//this is just for demo, another one is used on contact_form save
		const PATIENT_COURSE_TITLE      = 'FormBlockArtificialEntriesExits was created';

	}

?>