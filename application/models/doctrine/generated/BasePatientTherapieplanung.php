<?php

	abstract class BasePatientTherapieplanung extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_therapieplanung');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ernahrungstherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('infusionstherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antibiose_bei_pneumonie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antibiose_bei_HWI', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumorreduktionstherapie_chemo', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krankenhausverlegung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('lagerung_durch_pflege', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('orale_medikation_mehr', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('blut_volumenersatztherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliative', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('freetext', 'string', 255, array('type' => 'string', 'length' => 255));
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