<?php

	abstract class BaseCountries extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('country');
			$this->hasColumn('country_id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('country_name', 'string', 255, array('type' => 'string', 'length' => 255));
		}

	}

?>