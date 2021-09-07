<?php

	abstract class BaseFeststellung extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('feststellung');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('grund', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('grund_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('palliativzentrum', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('institution', 'string', 500, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verordnung_durch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('f_krankenhaus', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('f_address', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('einmalige', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>