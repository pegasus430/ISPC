<?php

	abstract class BasePatientDiagnosis extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_diagnosis');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('diagnosis_type_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('diagnosis_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('icd_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('diagnosis_from', 'text', NULL, array('type' => 'text', 'length' => NULL)); //ISPC - 2364
			$this->hasColumn('comments', 'text', NULL, array('type' => 'text', 'length' => NULL)); //ISPC - 2364
			$this->hasColumn('custom_order', 'integer', 4, array('type' => 'integer', 'length' => 4));//ISPC-2654 Ancuta 12.10.2020
			
		}

		function setUp()
		{
		    $this->hasOne('PatientDiagnosisParticipants', array(
		        'local' => 'id',
		        'foreign' => 'patient_diagnosis_id',
// 		        'owningSide' => false,
		        'cascade'    => array('delete'),
		    ));
		    
		    //ISPC-2654 Ancuta 07.10.2020
		    $this->hasOne('PatientDiagnosisClinical', array(
		        'local' => 'id',
		        'foreign' => 'patient_diagnosis_id',
// 		        'owningSide' => false,
		        'cascade'    => array('delete'),
		    ));
		    //--
			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
			$this->actAs(new PatientUpdate());
			
			//ISPC-2614 Ancuta 08.08.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
			$this->addListener(new IntenseDiagnosisConnectionListener(array(
			)), "IntenseDiagnosisConnectionListener");
			//
			
		}

	}

?>