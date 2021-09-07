<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
	abstract class BasePatientCaseStatus extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_case_status');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('case_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('case_type', 'enum', 16, array(
				'type' => 'enum',
				'length' => 20,
				'values' =>
					array(
						0 => 'station',
						1 => 'konsil',
						2 => 'ambulant',
						3 => 'sapv' ,
						4 => 'standby',
					),
			));
			$this->hasColumn('admdate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
            $this->hasColumn('disdate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
            $this->hasColumn('case_finished', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('patient_discharge_id', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('discharge_method', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('discharge_location', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('discharge_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp(){
		    
			$this->actAs(new Timestamp());

		}

	}

?>
