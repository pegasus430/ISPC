<?php

	abstract class BaseVoluntaryworkersCourse extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_course');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('course_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('course_type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('course_title', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('tabname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('recordid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('recorddata', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('wrong', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isstandby', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wrongcomment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('ishidden', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isserialized', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('source_vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('done_date', 'timestamp', 255, array('type' => 'timestamp', 'length' => 255));
			$this->hasColumn('done_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('done_id', 'integer', 11, array('type' => 'inteeger', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			/* // ISPC-2603 Andrei 02.06.2020
			$this->HasOne('Voluntaryworkers', array(
			    'local' => 'vw_id',
			    'foreign' => 'id'
			)); */
		}

	}

?>