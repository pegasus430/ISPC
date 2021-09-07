<?php

	abstract class BaseStateCityZip extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('state_city_zip');
			
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			
			$this->hasColumn('state', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('city', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('zipcode', 'varchar', 10, array('type' => 'varchar', 'length' => 10));		
			
		}


		

	}

?>