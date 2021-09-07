<?php

abstract class BaseMembersInvoicePayments extends Doctrine_Record 
{

	function setTableDefinition()
	{
		
			$this->setTableName('members_invoice_payments');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('invoice', 'int', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('amount', 'decimal', 11, array('scale' => 2));
			
			$this->hasColumn('status', 'enum', null, array(
					'type' => 'enum',
					'notnull' => true,
					'values' => array('created', 'installment', 'payment-requested', 'paid', 'storno' ), 
					'comment'=>'
					created = a payment created for an invoice without installments=sepa settings
					installment = a payment created for an invoice that has settings
					payment-requested = if you create the sepa-xml
					paid = invoice is marked as paid or payment installment is paid
					storno =  if you storno the invoice or the installmet of the invoice
					'
			));
			
			$this->hasColumn('comment', 'text', null);
			
			$this->hasColumn('scheduled_due_date', 'datetime', null, array(
					'type' => 'datetime',
					'notnull' => false,
					'default' => null,
					'length' => null,
					'comment'=>'when the installment should be paid'
			));
				
			
			
			
			$this->hasColumn('paid_date', 'datetime', 255, array('type' => 'datetime', 'length' => NULL));
// 			$this->hasColumn('isdelete', 'int', 1, array('type' => 'integer', 'length' => 1));


			$this->index('id', array(
					'fields' => array('id'),
					'primary' => true
			));
			
			$this->index('invoice+isdelete', array(
					'fields' => array('invoice' , 'isdelete')
			));
			$this->index('status', array(
					'fields' => array('status')
			));
			
		}

	function setUp()
	{
// 			$this->actAs(new Createtimestamp());
		parent::setUp();
			
		$this->hasOne('MembersInvoices', array(
				'local' => 'invoice',
				'foreign' => 'id'
		));
		
		$this->actAs(new Timestamp());
			
		$this->actAs(new Softdelete());
			
	}

}

?>