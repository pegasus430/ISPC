<?php

	abstract class BaseReassessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('reassessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('depresiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anspannung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('desorientier', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dekubitus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfebedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('versorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('umfelds', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vigilanz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verstopfung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('swache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('appetitmangel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('indic_sapv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('indic_sapv_txt', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('fill_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>