<?php
abstract class BaseZapvAssessmentIISymp extends Doctrine_Record
{

	function setTableDefinition ()
	{
		$this->setTableName('zapv_assessment_ii_symp');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('symp_group', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('symp_description', 'text', NULL, array('type' => 'string','length' => NULL));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}

	function setUp ()
	{
		$this->actAs(new Timestamp());
	}

}
?>