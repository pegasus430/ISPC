<?php
abstract class BaseVoluntaryworkersStatusesDetails extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('voluntaryworkers_statuses_details');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('status_id', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
	}
}

?>