<?php

	abstract class BaseFormBlockTimeDivision extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_block_time_division');

			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('contact_form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
					
			$this->hasColumn('on_patient', 'smallint', 6, array('type' => 'smallint', 'length' => 6));		
			$this->hasColumn('relatives', 'smallint', 6, array('type' => 'smallint', 'length' => 6));
			$this->hasColumn('systemic', 'smallint', 6, array('type' => 'smallint', 'length' => 6));
			$this->hasColumn('professional', 'smallint', 6, array('type' => 'smallint', 'length' => 6));
			$this->hasColumn('remain', 'smallint', 6, array('type' => 'smallint', 'length' => 6));
			$this->hasColumn('on_call', 'integer', 1, array('type' => 'integer', 'length' => 1));	
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			//create_date create_user change_date change_user
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>