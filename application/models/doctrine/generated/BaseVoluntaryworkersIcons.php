<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersIcons', 'SYSDAT');

	abstract class BaseVoluntaryworkersIcons extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_icons');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('icon_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>