<?php

	abstract class BaseDgpPatientsHistory extends Pms_Doctrine_Record {
	    /* ISPC-1775,ISPC-1678 */
		function setTableDefinition()
		{
			$this->setTableName('dgp_patients_history');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('history_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('upload_date', 'date', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}
	}

?>