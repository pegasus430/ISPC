<?php

	abstract class BaseMessageCoordinator extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('message_coordinator');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cntperson_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('qpa_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qpa_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('qpa_fax', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('familydoc_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('familydoc_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('familydoc_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('pflege_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflege_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflege_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('observation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}
}

?>