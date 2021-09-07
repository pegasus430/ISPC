<?php

	abstract class BasePriceMemberships extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_memberships');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('membership', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
		}

		function setUp()
		{
			
		}
	}

?>