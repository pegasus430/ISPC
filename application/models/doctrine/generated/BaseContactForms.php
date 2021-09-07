<?php

	abstract class BaseContactForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('contact_forms');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('parent', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('begin_date_h', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('begin_date_m', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('end_date_h', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('end_date_m', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			// ISPC - 2019
			$this->hasColumn('billable_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			// ISPC - 1591
			$this->hasColumn('over_midnight', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('fahrtzeit', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('fahrtstreke_km', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('comment_apotheke', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('case_history', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('quality', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ecog', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('karnofsky', 'integer', 3, array('type' => 'integer', 'length' => 3));
			$this->hasColumn('care_instructions', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('internal_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('sgbxi_quality', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('befund_txt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('free_visit', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('therapy_txt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('create_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('change_user', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			
			//ISPC-1951 + Mail   
			$this->hasColumn('invoice_condition', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('show_on_invoice', 'hide_on_invoice'),'default'=>'show_on_invoice','comment'=>'This is for invoices - to calculate or not on invoices' ));
			
			//ispc-2291
			$this->hasColumn('expert_accompanied', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Begleitung durch eine Ligetis Fachkraft?',
			));
			
		}

		function setUp()
		{
		    
		    $this->hasMany('ContactFormsSymp', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    
		    //ispc-2291
		    $this->hasOne('FormBlockPuncture', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    $this->hasOne('FormBlockInfusion', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    $this->hasOne('FormBlockInfusiontimes', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    $this->hasOne('FormBlockAdverseevents', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    
		    //ISPC-2454
		    $this->hasMany('FormBlockCustom', array(
	    		'local' => 'id',
	    		'foreign' => 'contact_form_id'
		    ));
		    
		    //ISPC-2487 Ancuta 27.11.2019
		    $this->hasMany('FormBlockCoordinatorActions', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    
		    //ISPC-2895 Ancuta 20.05.2021
		    $this->hasMany('FormBlockDelegation', array(
		        'local' => 'id',
		        'foreign' => 'contact_form_id'
		    ));
		    //--
		    
		    //ISPC-2516 Carmen 09.07.2020
		    $this->hasMany('FormBlockClientSymptoms', array(
		    		'local' => 'id',
		    		'foreign' => 'contact_form_id'
		    ));
		    
			$this->actAs(new Timestamp());
			
			/*
			 * ISPC-2071
			 */
			$this->addListener(new ContactForms2PatientCourseListener(), 'ContactForms2PatientCourseListener');
			
		}

	}

?>