<?php

	abstract class BaseReceipts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('receipts');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('type', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('foc', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bvg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aid', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vaccine', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('price', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('insurance_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zipcode', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('birthdate', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('ins_kassenno', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_insuranceno', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ins_status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('lanr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('datum', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('medication_1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('custom_line_1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medication1line1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medication_2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('custom_line_2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medication2line2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medication_3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('custom_line_3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medication3line3', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stampuser', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('stampid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isduplicated', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('source', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('receipt_status', 'string', 3, array('type' => 'string', 'length' => 3));
			//ISPC-2711 Ancuta 12.03.2021 
			$this->hasColumn('btm_a_symbol', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//-
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			
			//ISPC-1941
			$this->hasMany('ReceiptItems', array(
					'local' => 'id',
					'foreign' => 'receipt_id'
			));
		}

	}

?>