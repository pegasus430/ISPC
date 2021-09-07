<?php

	abstract class BaseFormBlocksOptions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_blocks_options');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('form_type', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('open', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('block', 'string', 255, array('type' => 'string', 'length' => 255));
			//TODO-3843
			$this->hasColumn('write2recorddata', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('write2recorddata_color', 'string', 255, array('type' => 'string', 'length' => 255));
            //TODO-4035 Nico 12.04.2021
            $this->hasColumn('write2shortcut', 'string', 255, array('type' => 'string', 'length' => 255));

        }

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>