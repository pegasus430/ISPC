<?php

	abstract class BasePatientHospizvizits extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_hospiz_vizits');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'b')));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('hospizvizit_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('besuchsdauer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fahrtkilometer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fahrtzeit', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('grund', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nightshift', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('amount', 'integer', 10, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2614 Ancuta 20.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}

	}

?>