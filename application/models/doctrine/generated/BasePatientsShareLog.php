<?php

	abstract class BasePatientsShareLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patients_share_log');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('course_id', 'integer', 20, array('type' => 'integer', 'length' => 20));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('source_ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			/**
			 * @since 07.08.2018
			 */
			$this->hasColumn('source_course_id', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
		}

	}

?>