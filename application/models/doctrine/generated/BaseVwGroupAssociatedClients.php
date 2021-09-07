<?php

	abstract class BaseVwGroupAssociatedClients extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('vw_group_associated_clients');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('group_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('parent', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>