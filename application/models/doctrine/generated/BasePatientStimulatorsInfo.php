<?php
/*
 * ISPC-2787 Lore 11.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientStimulatorsInfo', 'IDAT');


abstract class BasePatientStimulatorsInfo extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_stimulators_info');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vagus_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('vagus_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pacemaker_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pacemaker_text', 'string', 255, array('type' => 'string', 'length' => 255));

		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>