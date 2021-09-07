<?php
	abstract class BaseFormBlockVisitType extends Doctrine_Record {
		
		function setTableDefinition()
		{
			$this->setTableName('form_block_visit_type');
			
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ethically', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('somatic', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('psychosocial', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('coordination', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}
		
		function setUp()
		{
			$this->actAs(new Timestamp());
		}
	}