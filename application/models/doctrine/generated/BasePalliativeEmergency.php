<?php

abstract class BasePalliativeEmergency extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('palliative_emergency');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('diagnosen', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('palliative_problem_a', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('palliative_problem_b', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('palliative_problem_c', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('palliative_problem_d', 'text', NULL, array('type' => 'text', 'length' => NULL));
		
		$this->hasColumn('feature_a', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('feature_b', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('feature_c', 'string', 255, array('type' => 'string', 'length' => 255));
		
		$this->hasColumn('living_will', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('living_will_more', 'string', 255, array('type' => 'string', 'length' => 255));
		
		$this->hasColumn('cnt_legal', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('care_document', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('patient_diagno_edu', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('member_diagno_edu', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('cnt_first_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('cnt_legal_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('pflege_ppd_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('assigned_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('family_doc_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('pflege_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('amb_hospiz_details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('seelsorge', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('hospize_app', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('kv_emergency_call', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('kv_rescue', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('technical_emergency', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('last_hospital_stay', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('last_hospital_name', 'string', 255, array('type' => 'string', 'length' => 255));

		$this->hasColumn('life_prolonging_all_mesures', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('life_prolonging_no_resuscitation', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('life_prolonging_no_ventilation', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('life_prolonging_no_icu', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('life_prolonging_no_hospital', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('life_prolonging_palliative', 'integer', 1, array('type' => 'integer','length' => 1));
		
		$this->hasColumn('bedarf_medication', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('schmerzp_medication', 'text', NULL, array('type' => 'text', 'length' => NULL));
		$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('new_instance', 'integer', 1, array('type' => 'integer','length' => 1));
	}

	function setUp()
	{
		$this->actAs(new Timestamp());
	} 

}

?>