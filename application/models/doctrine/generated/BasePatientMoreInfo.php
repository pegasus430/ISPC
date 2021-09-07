<?php

	abstract class BasePatientMoreInfo extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_moreinfo');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('peg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pumps', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('zvk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('magensonde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pegmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('portmore', 'string', 255, array('type' => 'string', 'length' => 255));
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