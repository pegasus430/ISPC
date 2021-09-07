<?php

	abstract class BasePatientStandbyDelete extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_standbydelete');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 64, array('type' => 'varchar', 'length' => 64));
			$this->hasColumn('start', 'date', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('end', 'date', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->hasOne('EpidIpidMapping', array(
				'local' => 'ipid',
				'foreign' => 'ipid'
			));
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionAdmissionsListener(array(
			)), "IntenseConnectionAdmissionsListener");
			//
// 			//ISPC-2614 Ancuta 16.07.2020
// 			$this->addListener(new IntenseConnectionListener(array(
			    
// 			)), "IntenseConnectionListener");
// 			//
		}

	}

?>