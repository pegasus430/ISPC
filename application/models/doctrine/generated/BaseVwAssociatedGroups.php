<?php

	abstract class BaseVwAssociatedGroups extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('vw_associated_groups');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>