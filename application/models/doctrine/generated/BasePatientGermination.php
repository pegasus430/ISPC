<?php
//ISPC-1897 Germination = Bacteria , used in patient stammdaten
	abstract class BasePatientGermination extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_germination');
			
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('germination_cbox', 'enum', null, array('type' => 'enum', 'notnull' => false,'values' => array("on","off")));
			$this->hasColumn('germination_text', 'string', 255, array('type' => 'string', 'length' => 255));
            // TODO-1890
			$this->hasColumn('iso_cbox', 'enum', null, array('type' => 'enum', 'notnull' => false,'values' => array("on","off")));
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			//$this->actAs(new Softdelete());
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			    
			)), "IntenseConnectionListener");
			//
			
		}

	}

?>