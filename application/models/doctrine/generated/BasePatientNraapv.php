<?php

	abstract class BasePatientNraapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_nraapv');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));			
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_contactphone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qpa_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qpa_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qpa_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fdoc_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fdoc_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fdoc_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflege_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflege_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflege_fax', 'string', 255, array('type' => 'string', 'length' => 255));
//			$this->hasColumn('other_info', 'text', 65535, array('type' => 'text', 'length' => 65535, 'nullable'=>true));
			$this->hasColumn('other_info', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));			
		}
		
		function setUp()
		{
				$this->actAs(new Timestamp());
		}

	}

?>