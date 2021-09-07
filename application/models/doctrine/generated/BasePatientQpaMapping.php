<?php

	abstract class BasePatientQpaMapping extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_qpa_maping');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('epid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('assign_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('till_assign', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('User', array(
				'local' => 'id',
				'foreign' => 'userid'
			));

			$this->hasOne('Client', array(
				'local' => 'id',
				'foreign' => 'clientid'
			));
			$this->hasOne('EpidIpidMapping', array(
				'local' => 'epid',
				'foreign' => 'epid'
			));

			$this->actAs(new Timestamp());
			$this->actAs(new Trigger());
		}

	}

?>