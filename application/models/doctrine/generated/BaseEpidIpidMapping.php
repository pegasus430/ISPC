<?php

	abstract class BaseEpidIpidMapping extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('epid_ipid');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('epid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('epid_chars', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('epid_num', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('visible_since', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('discharge_since', 'date', NULL, array('type' => 'date', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('PatientMaster', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			$this->hasMany('PatientQpaMapping', array(
				'local' => 'epid',
				'foreign' => 'epid'
			));
			$this->hasMany('PatientActive', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientStandby', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			$this->hasMany('PatientStandbyDelete', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientLocation', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasMany('PatientDischarge', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			$this->hasMany('SapvVerordnung', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			$this->hasMany('PatientHealthInsurance', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			
			//ISPC-2432 Ancuta 
			$this->hasMany('MePatientDevices', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));
			
			
			//ISPC-2459 Ancuta 04.08.2020
			$this->hasMany('PatientVisitnumber', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));

	
			//ISPC-2474 Ancuta 23.10.2020
			$this->hasOne('Patient4Deletion', array(
			    'local' => 'ipid',
			    'foreign' => 'ipid'
			));
			
			
			$this->actAs(new Timestamp());
		}

	}

?>