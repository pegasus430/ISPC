<?php

	abstract class BaseMmiReceiptTxtBlocks extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('mmi_receipt_txt_blocks');
			
			$this->hasColumn('id', 'integer', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('text', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('isdeleted', 'integer', 1, array('type' => 'bigint', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>
