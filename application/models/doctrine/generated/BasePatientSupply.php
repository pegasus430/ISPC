<?php

	abstract class BasePatientSupply extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_supply');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('even', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('spouse', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('member', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('private_support', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nursing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliativpflegedienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('heimpersonal', 'integer', 1, array('type' => 'integer', 'length' => 1));
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