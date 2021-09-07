<?php

	abstract class BaseSisAmbulant extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sis_ambulant');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('dependent_person', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
		    $this->hasMany('SisAmbulantThematics', array(
		        'local' => 'id',
		        'foreign' => 'form_id'
		    ));
		    
			$this->actAs(new Timestamp());
		}

	}

?>