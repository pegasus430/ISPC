<?php

	abstract class BaseDoctorLettersPermissions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('doctor_letters_permissions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('letter', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{

			$this->hasOne('DoctorLetters', array(
				'local' => 'letter',
				'foreign' => 'id'
			));
		}

	}

?>