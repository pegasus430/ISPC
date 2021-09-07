<?php

abstract class BaseGenehmigungs  extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('genehmigungs');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));

		$this->hasColumn('mndiagnosis', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('diagnosemit', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('metastasis', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('sidediagnosis', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('bishtherapie', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('actuellmed', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('art_der_verabreichung', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('andere', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('andere_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('besondere', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('besondere_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('k_index', 'string', 3, array('type' => 'string','length' => 3));
		$this->hasColumn('pflegestufe', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('st_pflegestufe', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('schsymptomatik', 'string', 2, array('type' => 'string','length' => 2));
		$this->hasColumn('lokalisation', 'string', 255, array('type' => 'string','length' => 255));
		
		$this->hasColumn('dyspnoe', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('hamoptoe', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('nyha', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('respiratorische_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('respiratorische_txt_chk', 'integer', 1, array('type' => 'integer','length' => 1));

		$this->hasColumn('quantitative', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('hirndrucksymptome', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('spastik', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('myoklonus', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('muskelkampfe', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('depression', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('psychotische_syndrome', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('neurologische_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('neurologische_txt_chk', 'integer', 1, array('type' => 'integer','length' => 1));

		$this->hasColumn('lokalisation_a_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('lokalisation_a_chk', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('lokalisation_b_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('lokalisation_b_chk', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('lokalisation_c_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('lokalisation_c_chk', 'integer', 1, array('type' => 'integer','length' => 1));
		
		$this->hasColumn('anorexie_kachexie', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('mukositis', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('dysphagie', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('hamatemesis', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ikterus', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('ileus', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('aszites', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('diarrhoe', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('fisteln', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('gastrointestinale_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('gastrointestinale_txt_chk', 'integer', 1, array('type' => 'integer','length' => 1));

		$this->hasColumn('harnwegsinfekt', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('dysurie', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('blasentenesmen', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('hamaturie', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('vaginale_blutung', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('urogenitale_txt', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('urogenitale_txt_chk', 'integer', 1, array('type' => 'integer','length' => 1));
		
		$this->hasColumn('besondere_erfordernisse', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('aktueller_versorgungsbedarf', 'text',NULL, array('type' => 'text','length' => NULL));
	
	}
	function setUp()
	{
		$this->actAs(new Timestamp());
	} 
}

?>