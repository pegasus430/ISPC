<?php
/*
 * ISPC-2670 Lore 24.09.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientEvn', 'IDAT');


    abstract class BasePatientEvn extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_evn');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('evn_option', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('evn_text', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>