<?php

	abstract class BaseEmergencyPlanSapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('emergency_plan_sapv');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('main_diagnosis', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_intubation', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_resuscitation', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_hospitalization', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_living_will', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_vorsorgevollmacht', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('agreements_symptom_control', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('palliative_sister', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('palliative_sister_txt', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('standby_roster', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('standby_roster_txt', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('home_visits', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('home_visits_txt', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('fd_informed', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('fd_informed_txt', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('drug_plan', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('drug_plan_txt', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('phone_documented', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', 'yes', 'no'), 'default' => 'none'));
			$this->hasColumn('phone_documented_txt', 'string', null, array('type' => 'string', 'length' => null));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>