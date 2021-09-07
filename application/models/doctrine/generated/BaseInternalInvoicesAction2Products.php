<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesAction2Products', 'SYSDAT');

	abstract class BaseInternalInvoicesAction2Products extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoices_action2products');
			$this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('product_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('action_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>