<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
	abstract class BasePatientClinicBed extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_clinic_bed');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
            $this->hasColumn('bed_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('valid_from', 'datetime', null, array('type' => 'datetime', ));
            $this->hasColumn('valid_till', 'datetime', null, array('type' => 'datetime',));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

		}

	}

?>