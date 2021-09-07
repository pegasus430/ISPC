<?php

	abstract class BaseVoluntaryworkersStatuses extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('voluntaryworkers_statuses');
			$this->hasColumn('id', 'bigint', 11, array('type' => 'bigint', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('vw_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('status', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('status_old', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('n', 'e', 'k', 'p', 's', 'sh', 'pal', 'nac', 'off', 'tel', 'tra')));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			
			$this->hasOne('Voluntaryworkers', array(
			    'local' => 'vw_id',
			    'foreign' => 'id'
			));
		}

	}

?>