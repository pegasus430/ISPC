<?php

	abstract class BaseUser extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('username', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('password', 'string', 255, array('type' => 'string', 'length' => 255));
//		$this->hasColumn('pwder', 'string', 32, array('type' => 'string','length' => 32));
			$this->hasColumn('isadmin', 'integer', 8, array('type' => 'integer', 'length' => 8));
			
			//ISPC-2827 Ancuta 26.03.2021
			$this->hasColumn('efa_user', 'integer', 8, array('type' => 'integer', 'length' => 8));
			//-- 
			$this->hasColumn('issuperclientadmin', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('user_title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('emailid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('mobile', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('private_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betriebsstattennummer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('LANR', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ispc-1802
			$this->hasColumn('isactive_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			//ispc-1533
			$this->hasColumn('makes_visits', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('usertype', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parentid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('groupid', 'bigint', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('sessionid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('logintime', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('notification', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('no10contactsbox', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('onlyAssignedPatients', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sixwnote', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('fourwnote', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('shortname', 'string', 2, array('type' => 'string', 'length' => 2));
			$this->hasColumn('usercolor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('user_status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'g', 'y', 'r')));
			$this->hasColumn('verlauf_newest', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('t', 'b')));
			$this->hasColumn('verlauf_fload', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('y', 'n')));
			$this->hasColumn('verlauf_action', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('a', 'd')));
			$this->hasColumn('verlauf_entries', 'integer', 11, array('type' => 'integer', 'length' => 11));

			//Bank details
			$this->hasColumn('bank_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bank_account_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bank_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('iban', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bic', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//ISPC-2272
			$this->hasColumn('debitor_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			
			$this->hasColumn('ikusernumber', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dashboard_limit', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('show_custom_events', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('allow_own_list_discharged', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('km_calculation_settings', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('user', 'client')));
			$this->hasColumn('assigned_standby', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('control_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//     ISPC-790 evolution - connected admins
			$this->hasColumn('duplicated_user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			//ISPC-1082 - Teambesprechung
			$this->hasColumn('meeting_attendee', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//MMI Receipt stuff
			$this->hasColumn('mmi_n', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mmi_k', 'string', 255, array('type' => 'string', 'length' => 255));
			//Rooster shortcuts
			$this->hasColumn('roster_shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('default_stampusers', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('default_stampid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//ispc-1817
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			// IMPORT ONLY
			$this->hasColumn('import_id', 'string', 255, array('type' => 'string', 'length' => 255));
			// ISPC-2018
			$this->hasColumn('patient_file_tag_rights', 'text', null, array('type' => 'text', 'fixed' => false, 'unsigned' => false, 'primary' => false, 'default' => 'create,use', 'notnull' => true, 'autoincrement' => false));
			
			// ISPC-2065
// 			$this->hasColumn('elvi_username', 'string', 255, array('type' => 'string', 'length' => 255));
// 			$this->hasColumn('elvi_password', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('receipt_print_settings', 'object', null, array(
					'type' => 'object',
					'fixed' => false,
					'unsigned' => false,
					'primary' => false,
					'notnull' => false,
					'default' => NULL,
			));
			
			
			//ISPC-2272 Ancuta 30-31.03.2020
			$this->hasColumn('user_specific_account', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//ISPC-2513 Lore 13.04.2020
			//#ISPC-2512PatientCharts
			$this->hasColumn('header_type', 'enum', 255, array(
			    'type' => 'enum',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'default_ispc_header',
			        1 => 'type_2_header',
			    ),
			    'primary' => false,
			    'notnull' => true,
			    'autoincrement' => false,
			));

            // IM-10 clinic has station/konsil/ambulant patients. Many users only handle one type of patients and want
            //those patients in first view e.g. in reportsclinic/casestatus
            $this->hasColumn('preferred_clinic_list', 'string', 255, array('type' => 'string', 'length' => 255));

            //id for login with ldap if username cannot be used for login
            $this->hasColumn('ldapid', 'string', 255, array('type' => 'string', 'length' => 255));

            //ISPC-2474 Ancuta 23.10.2020
                $this->hasColumn('patient_deletion_allowed', 'integer', 1, array('type' => 'integer', 'length' => 1));
                $this->hasColumn('patient_deletion_password', 'string', 255, array('type' => 'string', 'length' => 255));
            //--
            
        }

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
			
			$this->hasOne('Notifications', array(
					'local' => 'id',
					'foreign' => 'user_id'
			));
			
			$this->hasOne('UserSettings', array(
			    'local' => 'id',
			    'foreign' => 'userid'
			));

			$this->hasMany('UserVacations', array(
			    'local' => 'id',
			    'foreign' => 'userid'
			));
				
			$this->hasMany('VacationsReplacements', array(
			    'local' => 'id',
			    'foreign' => 'userid'
			));
				
			$this->hasOne('ElviUsers', array(
			    'local' => 'id',
			    'foreign' => 'user_id'
			));
			
				
			
			$this->actAs(new Timestamp());
		}

	}

?>