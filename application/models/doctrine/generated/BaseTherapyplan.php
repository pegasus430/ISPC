<?php

	abstract class BaseTherapyplan extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('therapyplan');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('special_field', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>