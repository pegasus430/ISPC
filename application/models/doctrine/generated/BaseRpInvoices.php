<?php

	abstract class BaseRpInvoices extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('rp_invoice');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'int', 11, array('type' => 'bigint', 'length' => 11));

			$this->hasColumn('krankenkasse', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('geb', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kassen_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('versicherten_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_status', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betriebsstatten_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('arzt_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('topdatum', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_ik', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('invoice_start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('invoice_end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('main_diagnosis', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('sapv_id', 'int', 11, array('type' => 'int', 'length' => 11));
			$this->hasColumn('sapv_start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('sapv_end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('prefix', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('invoice_number', 'int', 11, array('type' => 'int', 'length' => 11));
			$this->hasColumn('invoice_total', 'int', 11, array('type' => 'ingeger', 'length' => 11));
			$this->hasColumn('paid_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('status', 'int', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stample', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv_erst', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('sapv_folge', 'string', 20, array('type' => 'string', 'length' => 20));
			
			$this->hasColumn('date_delivery', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('sig_date', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('bottom_signature', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('isdelete', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('record_id', 'int', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('storno', 'int', 1, array('type' => 'string', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			//ISPC-2747 Lore 26.11.2020
			$this->hasColumn('show_boxes', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('custom_invoice', 'string', 255, array('type' => 'string', 'length' => 255));
			
			//ISPC-2312 Ancuta 06.12.2020
			$this->hasColumn('isarchived', 'int', 1, array('type' => 'string', 'length' => 1));
			//--
			
			//ISPC-2747 Lore 07.12.2020
			$this->hasColumn('start_active', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_active', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('sapv_approve_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('sapv_approve_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('address', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('footer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_care', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('debtor_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ppun', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('paycenter', 'string', 255, array('type' => 'string', 'length' => 255));
			//.
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>