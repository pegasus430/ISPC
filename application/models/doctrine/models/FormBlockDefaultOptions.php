<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockDefaultOptions', 'SYSDAT');

	class FormBlockDefaultOptions extends BaseFormBlockDefaultOptions {

		public function get_block_defaut_options($block)
		{
			$sel_sett = Doctrine_Query::create()
				->select('*')
				->from('FormBlockDefaultOptions')
				->where('block="' . $block . '"')
				->andWhere('isdelete = 0');
				if($block == "coordinator_actions"){
    				$sel_sett ->orderBy('id ASC'); //ISPC-2487
				}else{
    				$sel_sett ->orderBy('option_name ASC');
				}
				
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