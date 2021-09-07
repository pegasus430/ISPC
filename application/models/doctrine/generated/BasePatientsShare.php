<?php

	abstract class BasePatientsShare extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patients_share');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('link', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('source', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('target', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 11, array('type' => 'string', 'length' => 3));
			$this->hasColumn('shared', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

	}

?>