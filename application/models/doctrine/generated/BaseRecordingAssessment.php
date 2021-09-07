<?php

	abstract class BaseRecordingAssessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('recording_assessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('orientierung_voll', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('orientierung_teilweise', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('orientierung_schwer', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('orientierung_desorientiert', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('depresiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anspannung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('desorientier', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('who', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verstopfung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('swache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('appetitmangel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstiges', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstigesmore', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('diagnosen', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>