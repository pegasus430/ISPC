<?php

	abstract class BasePatientLives extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_lives');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('alone', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('house_of_relatives', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('apartment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('home', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hospiz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstiges', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('with_partner', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('with_child', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			//ISPC-2292 
			$this->hasColumn('extra', 'enum', 25, array(
			    'type' => 'enum',
			    'length' => 25,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'Living alone',
			        1 => 'with spouse / partner',
			        2 => 'lives with relatives',
			        3 => 'Relatives nearby',
			        4 => 'Relatives in the distance',
			        5 => 'No relatives',
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