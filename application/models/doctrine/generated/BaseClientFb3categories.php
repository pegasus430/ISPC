<?php

	abstract class BaseClientFb3categories extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_fb3categories');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('categoryid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('category_title', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>