<?php

	abstract class BaseMedicationPatientHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('medication_patient_history');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('to_userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medicationid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			//ISPC-2768 Lore 04.01.2021
			$this->hasColumn('btm_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('methodid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('verlauf_hide', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('source', 'string', 100, array('type' => 'string', 'length' => 100));
			$this->hasColumn('done_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('sonstige_more', 'string', 100, array('type' => 'string', 'length' => 100));
			
			$this->hasColumn('self_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
				
			
			
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			
			$this->index('id', array(
					'fields' => array('id'),
					'primary' => true
			));
			
			
			$this->index('ipid+medicationid+isdelete', array(
					'fields' => array(
							'ipid',
							'medicationid',
							'isdelete'
					)
			));
			
			$this->index('clientid', array(
					'fields' => array('clientid')
			));
				
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>