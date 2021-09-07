<?php

	abstract class BasePatientMobility extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_mobility');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bed', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('walker', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wheelchair', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('goable', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nachtstuhl', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wechseldruckmatraze', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bedmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('walkermore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wheelchairmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('goablemore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nachtstuhlmore', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wechseldruckmatrazemore', 'string', 255, array('type' => 'string', 'length' => 255));
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