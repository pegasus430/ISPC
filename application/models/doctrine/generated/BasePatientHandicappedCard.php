<?php
/*
 * ISPC-2669 Lore 23.09.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientHandicappedCard', 'IDAT');


    abstract class BasePatientHandicappedCard extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_handicapped_card');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('since_date', 'datetime');
			$this->hasColumn('approved_option', 'string', 32, array('type' => 'string', 'length' => 32));
			$this->hasColumn('approved_date', 'datetime');
			$this->hasColumn('marks_option', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>