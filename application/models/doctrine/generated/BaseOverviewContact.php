<?php

	abstract class BaseOverviewContact extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('overview_contact');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('content', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>