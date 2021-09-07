<?php

	abstract class BaseLinks extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('perm_links');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('menu', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('master_link', 'integer', 10, array('type' => 'integer', 'length' => 1, 'default' => 0));
			$this->hasColumn('link', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ismaster', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('isffa', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('issadmin', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('iscadmin', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('ispatientonly', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
			$this->hasColumn('isediting', 'integer', 11, array('type' => 'integer', 'length' => 11, 'default' => 0));
		}

	}

?>