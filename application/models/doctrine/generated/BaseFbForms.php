<?php

	abstract class BaseFbForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('fb_forms');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formname', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('patientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}
	}

?>