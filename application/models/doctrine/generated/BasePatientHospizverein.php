<?php

	abstract class BasePatientHospizverein extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_hospizverein');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospizverein', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hospizverein_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			//ISPC-2070
			$this->hasColumn('necessity', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'yes',
			        1 => 'no',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2614 Ancuta 16-17.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			    
			)), "IntenseConnectionListener");
			//
		}

	}

?>