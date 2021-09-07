<?php

	abstract class BaseKbvKeytabs extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kbv_keytabs');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('sn', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('kbv_oid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('version', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('v', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('dn', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('valid', 'varchar', 255, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>