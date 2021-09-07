<?php

	abstract class BaseClientInvoices extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_invoices');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('clientid', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('epid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('prefix', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rnummer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('invoiceTotal', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('invoiceVat', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('isDelete', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('completedDate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('dueDate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('paidDate', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('record_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('storno', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('create_date', 'date', NULL, array('type' => 'date', 'length' => NULL));
			//ISPC-2312 Ancuta 07.12.2020
			$this->hasColumn('isarchived', 'int', 1, array('type' => 'string', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>