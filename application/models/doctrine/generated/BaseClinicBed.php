<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */

	abstract class BaseClinicBed extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('clinic_bed');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('bed_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bed_kuerzel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icon_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
		}

		function setUp(){

			$this->hasMany('PatientClinicBed', array(
				'local' => 'id',
				'foreign' => 'bed_id'
			));
		    
			$this->actAs(new Timestamp());

		}

	}

?>
