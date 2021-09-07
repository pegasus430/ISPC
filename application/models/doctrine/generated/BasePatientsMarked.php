<?php

	abstract class BasePatientsMarked extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patients_marked');
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
			// --
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('target', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('copy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('copy_options', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('copy_files', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('request', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('shortcuts', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('p', 'a', 'c')));
		}

	}

?>