<?php
abstract class BaseVoluntaryworkersPrimaryStatuses extends Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('voluntaryworkers_primary_statuses');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('status_id', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
	}
}

?>