<?php

	abstract class BaseClientHospitalSettings extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('client_hospital_settings');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));

			$this->hasColumn('hospiz_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'tr'));
			$this->hasColumn('hospiz_dis', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'tr'));
			$this->hasColumn('hospiz_day', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'hz'));

			$this->hasColumn('hosp_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'tr'));
			$this->hasColumn('hosp_dis', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'tr'));
			$this->hasColumn('hosp_day', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'hp'));

			$this->hasColumn('hosp_dis_hospiz_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp', 'hz'), 'default' => 'tr'));
			$this->hasColumn('hospiz_dis_hosp_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp', 'hz'), 'default' => 'tr'));

			$this->hasColumn('hosp_pat_dis', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'tr'));
			$this->hasColumn('hosp_pat_dis_final', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '1'));
			
			$this->hasColumn('hospiz_pat_dis', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'tr'));
			$this->hasColumn('hospiz_pat_dis_final', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '1'));

			$this->hasColumn('hosp_pat_dead', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'tr'));
			$this->hasColumn('hosp_pat_dead_final', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '1'));
			$this->hasColumn('hospiz_pat_dead', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'tr'));
			$this->hasColumn('hospiz_pat_dead_final', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1'), 'default' => '1'));

			$this->hasColumn('hosp_dis_hosp_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'hp'));
			$this->hasColumn('hospiz_dis_hospiz_adm', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'hz'));
			
			$this->hasColumn('hospiz_first_day', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hz'), 'default' => 'hz'));
			$this->hasColumn('hosp_first_day', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('tr', 'hp'), 'default' => 'hp'));

			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>