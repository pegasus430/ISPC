<?php

	abstract class BaseExtraFormsClient extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('extraforms_client');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>