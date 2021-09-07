<?php

	abstract class BasePatientQpaLeading extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_qpa_leading');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'integer', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
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
				'local' => 'ipid',
				'foreign' => 'ipid'
			));

			$this->actAs(new Timestamp());
		}

	}

?>