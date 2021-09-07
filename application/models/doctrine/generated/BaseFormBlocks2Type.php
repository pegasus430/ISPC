<?php

	abstract class BaseFormBlocks2Type extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_blocks2type');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('form_type', 'string', 11, array('type' => 'string', 'length' => 11));
			$this->hasColumn('form_block', 'string', 11, array('type' => 'string', 'length' => 11));
		}

	}

?>