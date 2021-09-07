<?php

	abstract class BaseZipDensity extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('zip_density');
			
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('zipcode', 'varchar', 10, array('type' => 'varchar', 'length' => 10));
			$this->hasColumn('population_density', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('sparsely', 'average', 'densely')));
			$this->hasColumn('valid_year', 'varchar', 10, array('type' => 'varchar', 'length' => 10));
			$this->hasColumn('inactive', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
		}


		

	}

?>