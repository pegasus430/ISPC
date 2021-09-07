<?php

	abstract class BaseLocations2dta extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('locations2dta');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('location', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('dta', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		}
	}

?>
