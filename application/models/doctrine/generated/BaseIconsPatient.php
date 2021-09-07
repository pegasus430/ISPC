<?php

	Doctrine_Manager::getInstance()->bindComponent('IconsPatient', 'SYSDAT');

	abstract class BaseIconsPatient extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('icons_patient');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icon_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			 
			$this->actAs(new Softdelete()); //ISPC-2396 Carmen 08.10.2019 
		}

	}

?>