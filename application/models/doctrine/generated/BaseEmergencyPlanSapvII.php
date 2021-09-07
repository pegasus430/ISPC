<?php
//ISPC-2736 Lore 12.11.2020
	abstract class BaseEmergencyPlanSapvII extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('emergency_plan_sapv_ii');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('special_features', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('relatives', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('supervisor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('main_diagnosis', 'string', null, array('type' => 'string', 'length' => null));
			$this->hasColumn('prev_attorney', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('living_will', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('serv_available', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('resuscitation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('hosp_required', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('crises', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('artificial_food', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('antibiotic_therapy', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('transfusion', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('infusion', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('palliative_sedation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('none', '1', '0'), 'default' => 'none'));
			$this->hasColumn('sapv24_date', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sapv24_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
			$this->hasColumn('iscomplete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default'=>0));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>