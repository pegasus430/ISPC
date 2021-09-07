<?php

	abstract class BasePatientsLinked extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patients_linked');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			//ISPC-2614 Ancuta 15.07.2020
			$this->hasColumn('intense_system', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			//-- 
			$this->hasColumn('source', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('target', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('copy_files', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('copy_meds', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
		}

		function setUp()
		{
		    
// 			$this->actAs(new Createtimestamp());
			$this->actAs(new Timestamp());
		}

	}

?>