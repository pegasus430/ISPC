<?php

	abstract class BaseHealthInsuranceSubdivisions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('health_insurance_subdivisions');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
		    /*
		     * @cla on 22.06.2018
		     * who the f is this ???
		     */
		    /*
			$this->hasOne('HealthInsurance2Subdivision', array(
				'local' => 'id',
				'foreign' => 'subdiv_id'
			));
			*/
			
			
			$this->hasOne('HealthInsurancePermissions', array(
				'local' => 'id',
				'foreign' => 'subdiv_id'
			));
		}

	}

?>