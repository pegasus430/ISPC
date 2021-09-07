<?php

/**
 * 
 * @property string $teammeeting_settings
 * changes done for ISPC-2452
 */
Doctrine_Manager::getInstance()->bindComponent('Client', 'MDAT');

abstract class BaseClient extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('hospital_clients');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('client_name', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('epid_chars', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('epid_start_no', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('postcode', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('district', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('country', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('firstname', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('lastname', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('emailid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('team_name', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('userlimit', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('maxcontact', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('discharge_day_period', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('greetings', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('institutskennzeichen', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('betriebsstattennummer', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('isactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('preregion', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('fdoc_caresalone', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('inactivetime', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('maintainance', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('fileupoadpass', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('dgp_user', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('dgp_pass', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('symptomatology_scale', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'a')));
		$this->hasColumn('health_insurance_client', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('recipient', 'text', null, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('lbg_sapv_provider', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('lbg_postcode', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('lbg_city', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('lbg_street', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('lbg_institutskennzeichen', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('invoice_number_type', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('invoice_number_prefix', 'string', 255, array('type' => 'integer', 'length' => 255));
		$this->hasColumn('invoice_number_start', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('invoice_due_days', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('automatically_assign_users', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '1'));
		$this->hasColumn('max_nurse_visits', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('emergencynr_a', 'string', 255, array('type' => 'integer', 'length' => 255));
		$this->hasColumn('emergencynr_b', 'string', 255, array('type' => 'integer', 'length' => 255));
		$this->hasColumn('billing_method', 'string', 255, array('type' => 'integer', 'length' => 255));
		$this->hasColumn('membership_billing_method', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('calendar', 'membership')));
		$this->hasColumn('tagesplanung_standby_patients', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '0'));
		$this->hasColumn('new_medication_fields', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '0'));
		$this->hasColumn('ppun_start', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		//ISPC-2452 Ancuta 21.11.2019
		$this->hasColumn('hi_debitor_start', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		//--
		$this->hasColumn('receipt_print_style', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('kv_receipt', 'mmi_receipt'), 'default' => 'mmi_receipt'));
		$this->hasColumn('mandate_reference', 'integer', 11, array('type' => 'integer', 'length' => 11));
		//ispc-1533			
		$this->hasColumn('tagesplanung_default_visit_time', 'tiny', 4, array('type' => 'tiny', 'length' => 4, 'default' => '20'));
		$this->hasColumn('tagesplanung_only_user_with_shifts', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '0'));
		//ISPC-1914 client setting to define the standard length of a visit (contact form)
		$this->hasColumn('contactform_default_visit_length', 'smallint', 6, array('type' => 'smallint', 'default' => '15' ,'comment'=>'contactfom visit default duration'));
		//ispc-1842
		$this->hasColumn('sepa_iban', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('sepa_bic', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('sepa_ci', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
						
		$this->hasColumn('route_calculation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '0'));
		//ispc-1886
		$this->hasColumn('dgp_transfer_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		
		//ISPC-2161 this are all saved with no/yes
		$this->hasColumn('teammeeting_settings', 'object', null, array(
		    'type' => 'object',
		    'fixed' => false,
		    'unsigned' => false,
		    'primary' => false,
		    'notnull' => false,
		    'autoincrement' => false,
		    'values' =>
		    array(
		        0 => 'events', // translate :  teammeeting_settings_events
		        1 => 'prefill', // translate :  teammeeting_settings_prefill
		        2 => 'contact', // translate :  teammeeting_settings_contact
		        3 => 'epid', 	//translate: teammeeting_settings_epid
		        4 => 'todo', //translate: teammeeting_settings_todo
		        5 => 'action', //translate: teammeeting_settings_action
		        6 => 'users', //translate: teammeeting_settings_users
	    		7 => 'addextrapat', //translate: teammeeting_settings_addextrapat
	    		8 => 'onlyactivepat', //translate: teammeeting_settings_onlyactivepat
		    	9 => 'statusdrop', //translate: teammeeting_settings_statusdrop
		    	10 => 'xt', //translate: teammeeting_settings_xt
		    	11 => 'showtodos', //translate: teammeeting_settings_showtodos
		    	12 => 'tbyusers', //translate: teammeeting_settings_tbyusers
		    	13 => 'lastfcomment', //translate: teammeeting_settings_lastfcomment
		    	14 => 'iconprefill', //translate: teammeeting_settings_iconprefill ISPC - 2261
		    	15 => 'crisestatus', //translate: teammeeting_settings_crisestatus ISPC - 2310
				165 => 'targets', //translate: teammeeting_settings_targets //ISPC-2556 Andrei 27.05.2020 added targets and changed array index for next values
		        17 => 'treatment_process',   //translate: teammeeting_settings_treatment_process         //ISPC-2896 Lore19.04.2021
		        18 => 'show_problems',   //translate: teammeeting_settings_show_problems         //ISPC-2896 Lore 23.04.2021
		        //3 => 'add your own, it will be prepended to the form, with nein/ja radio',
		    ),
		));
		//ISPC - 2271 - notfall messages settings for user groups client
		$this->hasColumn('notfall_messages_settings', 'object', null, array(
				'type' => 'object',
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'default' => NULL,
		));
		
		//ISPC-2272 (07.11.2018)
		$this->hasColumn('company_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('cost_center', 'varchar', 255, array('type' => 'varchar', 'length' => 255));

		/*
		 * ISPC-2095
		 * 6) can we add a CLIENT setting when the tourenplan HOURS will start and end?
		 * so the customer could say
		 * START: 6.00
		 * END: 18.00
		 * so they dont see hours in which they never would add a visit.
		 * 11) can we add a client setting from when TILL when a tourenplanung is done?
		 * we are planning from Monday till Sunday.
		 * Most clients are planning from Saturday till Friday. Can we change this?
		 * i THINK this just affects the printed plans
		 */
		$this->hasColumn('tourenplanung_settings', 'array', null, array(
		    'type' => 'array',
		    'fixed' => false,
		    'unsigned' => false,
		    'primary' => false,
		    'notnull' => false,
		    'default' => NULL,
		    'comments' => ''
		));
		
		
		
		
		// ISPC-2327 (23.01.2019)
		$this->hasColumn('working_schedule', 'text', null, array('type' => 'text', 'length' => NULL));
		
		//ISPC - 2311 - patient course color entries
		$this->hasColumn('patient_course_settings', 'object', null, array(
				'type' => 'object',
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'default' => NULL,
		));
		
		// ISPC-2331 05.03.2019 Ancuta
		$this->hasColumn('rlp_past_revenue', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('rlp_books_end_day', 'int', 3, array('type' => 'integer', 'length' => 3));
		$this->hasColumn('rlp_books_end_month', 'int', 3, array('type' => 'integer', 'length' => 3));
		//--
		
		//ISPC - 2163 - activate shortcut V in fb3 and fb8
		$this->hasColumn('activate_shortcut_v_settings', 'array', null, array(
		 'type' => 'array',
		 'fixed' => false,
		 'unsigned' => false,
		 'primary' => false,
		 'notnull' => false,
		 'default' => NULL,
		 ));
		
		//ISPC-2417 Lore 29.08.2019 // Demstepcare_upload - 10.09.2019 Ancuta
		$this->hasColumn('days_after_todo', 'integer', 2, array('type' => 'integer', 'length' => 2));
		
		
		
		//ISPC-2452 Ancuta 24.09.2019
		$this->hasColumn('rlp_hi_account_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('rlp_pv_account_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('rlp_terms_of_payment', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		
		$this->setColumnOption('activate_shortcut_v_settings', 'values', [
				"shortcut_values",
				"shortcut_type"
		]
		);
		//ISPC-2171 Lore 15.11.2019
		$this->hasColumn('hospiz_hi_cont', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('hospiz_pv_cont', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		$this->hasColumn('hospiz_const_center', 'varchar', 255, array('type' => 'varchar', 'length' => 255));

		// ISPC-2171 Ancuta 08.01.2020
		$this->hasColumn('rlp_document_header_txt', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
		//--
		
		//ISPC-2636 Lore 29.07.2020
		$this->hasColumn('client_medi_sort', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('importance', 'medication', 'drug', 'indication', 'change_date') ));
		$this->hasColumn('user_overwrite_medi_sort_option', 'integer', 1, array('type' => 'integer', 'length' => 1));
		//.
		
		//ISPC-2769 Lore 06.01.2021
		$this->hasColumn('show_medi_times_when_given', 'integer', 1, array('type' => 'integer', 'length' => 1));
				
		
		//ISPC-2459 Ancuta 04.08.2020 
		$this->hasColumn('movement_start_number', 'integer', 11, array('type' => 'integer', 'length' => 11));
		//.
		
		//TODO-3365 Carmen 21.08.2020
		$this->hasColumn('pharmaindex_settings', 'object', null, array(
				'type' => 'object',
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false,
				'values' =>
				array(
						0 => 'atc', // translate :  pharmaindex_settings_atc
						1 => 'drug', // translate :  pharmaindex_settings_drug
						2 => 'dosage_form', 	//translate: pharmaindex_settings_dosage_form
						3 => 'unit', //translate: pharmaindex_settings_unit
						4 => 'type', //translate: pharmaindex_settings_type
						5 => 'takinghint', //translate: pharmaindex_settings_takinghint
						//3 => 'add your own, it will be prepended to the form, with nein/ja radio',
				),
		));
		//--
		
		
		//ISPC-2827 Ancuta 26.03.2021
		$this->hasColumn('efa_client', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '0'));
		// --
		
		//ISPC-2864 Ancuta 20.04.2021
		$this->hasColumn('efa_problem_extension', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('sapv', 'bpss')));
		//--
		
		
		
	}

	function setUp()
	{
		//ISPC-2806 Dragos 28.01.2021
		$this->hasOne('ClientComplaintSettings', array(
			'local' => 'id',
			'foreign' => 'clientid',
// 		        'owningSide' => false,
			'cascade'    => array('delete'),
		));
		// -- //
		$this->actAs(new Timestamp());
	}

}

?>
