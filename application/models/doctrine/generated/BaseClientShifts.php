<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientShifts', 'SYSDAT');

	abstract class BaseClientShifts extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_shifts');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			//ISPC-2612 Ancuta 30.06.2020
			$this->hasColumn('connection_id', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from connections_master',
			));
			$this->hasColumn('master_id', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'id from of master entry from parent client',
			));
			//--
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('color', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('shortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('show_time', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isholiday', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('istours', 'integer', 11, array('type' => 'integer', 'length' => 1, 'default'=>0)); // this is the pseudogroup_id

			$this->hasColumn('active_till', 'date', null, array(
					'type' => 'date',
					'notnull' => false,
					'default' => null,
			));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
			
			
			//ISPC-2612 Ancuta 30.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

	}

?>