<?php

	abstract class BaseSisAmbulantThematics extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sis_ambulant_thematics');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('thematic', 'string', 255, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('thematic_text', 'text',NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('dekubitus', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_dekubitus', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_dekubitus', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('sturz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_sturz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_sturz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('inkontinenz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_inkontinenz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_inkontinenz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('schmerz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_schmerz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_schmerz', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('ernahrung', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_ernahrung', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_ernahrung', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('sonstiges', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('future_sonstiges', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1','2')));
			$this->hasColumn('beratung_sonstiges', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
		    $this->hasOne('SisAmbulant', array(
		        'local' => 'form_id',
		        'foreign' => 'id'
		    ));
		    
			$this->actAs(new Timestamp());
		}

	}

?>