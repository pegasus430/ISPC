<?php

	abstract class BaseDgpHistory extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('dgp_history');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('upload_date', 'date', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('xml_string', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('response_code', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('response_text', 'string', 255, array('type' => 'string', 'length' => 255));
		}
	}

?>