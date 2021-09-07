<?php

	abstract class BaseUsergroup extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('usergroups');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('groupname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('groupmaster', 'string', 128, array('type' => 'string', 'length' => 128));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isactive', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('indashboard', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('startpage_duty', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('social_groups', 'object', null, array(
			    'type' => 'object',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'values'=>array(
			        'social_worker',//Sozialarbeiter
			        'psychologist',//Psychologe
			        'spiritual_welfare'//Seelsorge
			)
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
		}

	}

?>