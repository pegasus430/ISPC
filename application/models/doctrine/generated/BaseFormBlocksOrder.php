<?php

	abstract class BaseFormBlocksOrder extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_blocks_order');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('form_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('box_order', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>