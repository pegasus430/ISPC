<?php

Doctrine_Manager::getInstance()->bindComponent('FormBlocksSettingsCategories', 'SYSDAT');

class FormBlocksSettingsCategories extends BaseFormBlocksSettingsCategories {

	public function get_settings_categories($clientid)
	{
		$sel_sett = Doctrine_Query::create()
		->select('*')
		->from('FormBlocksSettingsCategories')
		->where('clientid ="' . $clientid . '"')
		->andWhere('isdelete = 0');
		$sel_sett_res = $sel_sett->fetchArray();

		if($sel_sett_res)
		{
			return $sel_sett_res;
		}
		else
		{
			return false;
		}
	}
	
	
	
	public function get_block_categories($clientid, $block)
	{
		$sel_sett = Doctrine_Query::create()
		->select('*')
		->from('FormBlocksSettingsCategories INDEXBY id')
		->where('clientid ="' . $clientid . '"')
		->andWhere('block = "' . $block . '"')
		->andWhere('isdelete = 0');
		$sel_sett_res = $sel_sett->fetchArray();
	
		if($sel_sett_res)
		{
			return $sel_sett_res;
		}
		else
		{
			return false;
		}
	}
	
	
	
	
}
?>