<?php
/*
 * ISPC-2773 Lore 14.12.2020
 */

Doctrine_Manager::getInstance()->bindComponent('PatientFamilyInfo', 'IDAT');


abstract class BasePatientFamilyInfo extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_family_info');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parents_marital_status_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('divorced_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('widowed_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('parental_consanguinity', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('child_residing', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('child_residing_text', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>