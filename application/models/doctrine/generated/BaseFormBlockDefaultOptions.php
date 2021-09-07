<?php

	abstract class BaseFormBlockDefaultOptions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_default_options');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('block', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('option_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('shortcut', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-2487 Ancuta 28.11.2019
			$this->hasColumn('op_class', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>