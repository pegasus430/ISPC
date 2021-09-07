<?php

	abstract class BasePriceFormBlocks extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_form_blocks');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('option_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('block', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 5, array('type' => 'string', 'length' => 5));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
		}

	}

?>