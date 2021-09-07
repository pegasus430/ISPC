<?php

	abstract class BaseMembersInvoices extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('members_invoice');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('member', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('invoice_start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('invoice_end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('membership_start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('membership_end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('membership_data', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('invoiced_month', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('prefix', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_number', 'int', 11, array('type' => 'int', 'length' => 11));
			$this->hasColumn('invoice_total', 'int', 11, array('type' => 'ingeger', 'length' => 11));
			$this->hasColumn('paid_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('status', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('client_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('recipient', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('comment', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('isarchived', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('record_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('storno', 'int', 1, array('type' => 'string', 'length' => 1));
			
			$this->hasColumn('storno_comment',	'text',	null);
			
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
		    //TODO-2939 Lore 21.02.2020
		    $this->hasOne('Member', array(
		        'local' => 'member',
		        'foreign' => 'id'
		    ));
		    
			$this->actAs(new Timestamp());
		}

	}

?>