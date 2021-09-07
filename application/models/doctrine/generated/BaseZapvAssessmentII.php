<?php
abstract class BaseZapvAssessmentII extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('zapv_assessment_ii');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('admission', 'consulting','end')));
		$this->hasColumn('status', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('active', 'inactiv')));
		$this->hasColumn('first_sapv_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('first_sapv_type', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('latest_sapv_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('latest_sapv_type', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('diagnosis', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('curative_treatment', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1', '2')));
		$this->hasColumn('after_sapvrl', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1', '2')));
		$this->hasColumn('used_contact_forms', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('advice_checked', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('1', '2')));
		$this->hasColumn('advice_description', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('advice_involved_persons', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('providers', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('providers_other', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('treatment_plan', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('support_needs', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('sapv', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('sapv_requierments', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('sapv_requierments_until', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('sapv_end_date', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('reason_of_termination', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('other_messages', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('done_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('done_by', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('comments', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}

	function setUp ()
	{
		$this->actAs(new Timestamp());
	}

}
?>