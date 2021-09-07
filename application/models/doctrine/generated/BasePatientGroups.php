<?php

// ISPC-2482 
// 13.12.2019

	abstract class BasePatientGroups extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_groups');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('master_groupid', 'integer', 11, array('type' => 'integer', 'length' => 11)); //MASTER GROUP
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));//Client group
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('create_date', 'datetime');
			$this->hasColumn('change_date', 'datetime');
			$this->hasColumn('create_user', 'bigint');
			$this->hasColumn('change_user', 'bigint');
		}

		
		public function setUp()
		{
		    parent::setUp();
		    
		    $this->actAs(new Timestamp());
		    
		    $this->actAs(new Softdelete());
		}
		
	}

?>