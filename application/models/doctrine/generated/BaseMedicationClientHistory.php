<?php

	abstract class BaseMedicationClientHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('medication_client_history');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medicationid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('methodid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('stid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn(
					'self_id', 
					'integer', 11, 
					array(
							'type' => 'integer',
							'length' => 11,
							'comment' => 'this row is connected with this self_id',
							'default' => 0	
					));
			$this->hasColumn(
					'patient_stock_id', 
					'integer', 11, 
					array(
							'type' => 'integer',
							'length' => 11,
							'comment' => 'id in MedicationPatientHistory',
							'default' => 0	
					));
			
			
			//ISPC-2768 Lore 05.01.2021
			$this->hasColumn('btm_number', 'string', 255, array('type' => 'string', 'length' => 255));
			
			
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sonstige_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('done_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>