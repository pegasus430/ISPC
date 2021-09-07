<?php

	abstract class BaseLmuImportActions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('lmu_import_lesit');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('patient_id', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('end', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('Akt_Inhalt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('Akt_Kategorie', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('Dauer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('Benutzer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('Fahrtstrecke', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('Rufbereitschaft', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('imported', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
		}
	}

?>