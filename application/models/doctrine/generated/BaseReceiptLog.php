<?php

	abstract class BaseReceiptLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('receipt_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('user', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('receipt', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('operation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('assign','unasign','created','edited','deleted','duplicated','printed','faxed','sc')));
			$this->hasColumn('assign_type', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('involved_users', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('old_status', 'string', 3, array('type' => 'string', 'length' => 3));
			$this->hasColumn('new_status', 'string', 3, array('type' => 'string', 'length' => 3));
			$this->hasColumn('source', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>