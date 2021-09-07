<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesActionProducts', 'SYSDAT');

	abstract class BaseInternalInvoicesActionProducts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoices_action_products');
			$this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('usergroup', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('contactform_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('range_start', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('range_end', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('km_range_start', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('km_range_end', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('range_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('min', 'km')));
			$this->hasColumn('time_start', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('time_end', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('calculation_trigger', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('time_start', 'time_end')));
			$this->hasColumn('holiday', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>