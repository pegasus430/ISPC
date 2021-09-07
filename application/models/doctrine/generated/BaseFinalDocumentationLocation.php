<?php

	abstract class BaseFinalDocumentationLocation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('final_documentation_location');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('von', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('bis', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('krankenhaus', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('decomp_pat', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('decomp_um', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pat_wun', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('new_instance', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>