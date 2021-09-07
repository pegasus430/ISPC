<?php

	abstract class BaseUserPatientShortcuts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_patient_shortcuts');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('shortcutid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canadd', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canedit', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('canview', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('candelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>