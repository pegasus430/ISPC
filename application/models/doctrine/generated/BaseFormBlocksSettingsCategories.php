<?php

	abstract class BaseFormBlocksSettingsCategories extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_blocks_settings_categories');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('block', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('category', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			
			$this->hasOne('FormBlocksSettings', array(
					'local' => 'id',
					'foreign' => 'option_category'
			));
			
			
			$this->actAs(new Timestamp());
		}

	}

?>