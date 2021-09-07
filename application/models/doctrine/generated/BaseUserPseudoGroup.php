<?php

	abstract class BaseUserPseudoGroup extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_pseudo');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('servicesname', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mobile', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));

			//ispc-1533
			$this->hasColumn('makes_visits', 'enum', NULL, array('type' => 'enum', 'values'=>array('0','1', 'tours')));
				
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
			$this->hasMany('PseudoGroupUsers', array(
				'local' => 'id',
				'foreign' => 'pseudo_id'
			));


			$this->actAs(new Timestamp());
		}

	}

?>