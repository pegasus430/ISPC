<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesSpecificProducts', 'SYSDAT');

	abstract class BaseInternalInvoicesSpecificProducts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('internal_invoices_specific_products');
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
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('code', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('calculation_trigger', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('time_start', 'time_end')));
			$this->hasColumn('asigned_users', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('holiday', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('showtime', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>