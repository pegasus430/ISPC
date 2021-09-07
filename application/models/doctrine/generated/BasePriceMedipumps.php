<?php

	abstract class BasePriceMedipumps extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_medipumps');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('medipump', 'integer', 11, array('type' => 'integer', 'length' => 11));

			$this->hasColumn('price_first', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('first_start', 'integer', 10, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('first_end', 'integer', 10, array('type' => 'integer', 'length' => 11));

			$this->hasColumn('price_follow', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('follow_start', 'integer', 10, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('follow_end', 'integer', 10, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			
		}
	}

?>