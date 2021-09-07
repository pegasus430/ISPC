<?php

	abstract class BasePatientReligions extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_religionszugehorigkeit');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('religion', 'integer', 11, array('type' => 'integer', 'length' => 11));
			// TODO-1890
			$this->hasColumn('religionfreetext', 'string', 255, array('type' => 'string', 'length' => 255));
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