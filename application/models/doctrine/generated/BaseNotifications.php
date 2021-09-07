<?php

	abstract class BaseNotifications extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('notification_settings');
			$this->hasColumn('user_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true));
			$this->hasColumn('admission', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('discharge', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('sixweeks', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('fourwnote', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('krise', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('wlvollversorgung', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('wlvollversorgung_25days', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('dashboard_display_patbirthday', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('sapv_enabled', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('sapv_popup', 'string', 4, array('type' => 'string', 'length' => 4));
			//ISPC - 2125 - alerts if a verordnung is after XX days still in mode "Keine Angabe"
			$this->hasColumn('sapv_noinf_enabled', 'int', 1, array('type' => 'int', 'length' => 1));
			$this->hasColumn('sapv_noinf_popup', 'string', 4, array('type' => 'string', 'length' => 4));
			// medication acknowlege ISPC -1483
			$this->hasColumn('medication_acknowledge', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			$this->hasColumn('todo', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
            // ISPC-1712 
			$this->hasColumn('medication_interval', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'assigned', 'all')));
			//ISPC-1884
			$this->hasColumn('medication_doctor_receipt', 'enum', null, array(
					'type'		=> 'enum',
					'notnull'	=> false,
					'values'	=> array('none', 'assigned', 'all'),
					'default'	=> 'none',
					'comment'	=> 'patient->medication->receipt->request from a doctor',
			));
				
			//ISPC-1952
			$this->hasColumn('dashboard_grouped', 'enum', null, array(
					'type'		=> 'enum',
					'notnull'	=> false,
					'values'	=> array('0', '1'),
					'default'	=> '0',
					'comment'	=> '1 = grouped, all TODO in the dashboard of one patient is shown grouped.',
			));
			// ISPC-1547
			$this->hasColumn('patient_hospital_admission', 'enum', null, array(
			    'type' => 'enum', 
			    'notnull' => false, 
			    'values' => array('none', 'assigned', 'all')
			));
			// ISPC-1547
			$this->hasColumn('patient_hospital_discharge', 'enum', null, array(
			    'type' => 'enum', 
			    'notnull' => false, 
			    'values' => array('none', 'assigned', 'all')
			));
			#ISPC-2432 Ancuta 22.01.2020 
			$this->hasColumn('mePatient_device_uploads', 'enum', null, array(
			    'type' => 'enum', 
			    'notnull' => false, 
			    'values' => array('none', 'assigned', 'all')
			));

				
		}

		function setUp()
		{
			
			$this->hasOne('User', array(
				'local' => 'user_id',
				'foreign' => 'id'
			));
			
		}

	}

?>
