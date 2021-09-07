<?php

	abstract class BasePriceRpInvoice extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_rp_invoice');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('p_home', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('p_nurse', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('p_hospiz', 'decimal', 10, array('scale' => 2));
		}

	}

?>