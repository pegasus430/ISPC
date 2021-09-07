<?php

	abstract class BaseOverview extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('overview');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('overviewboxid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('boxconditions', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>