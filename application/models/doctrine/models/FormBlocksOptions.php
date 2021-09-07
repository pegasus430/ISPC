<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlocksOptions', 'MDAT');

	class FormBlocksOptions extends BaseFormBlocksOptions {

		public function get_blocks_options($clientid=0, $form_type=false)
		{
			if(empty($clientid)){
				return array();
			}
			
			$sel_ord = Doctrine_Query::create()
				->select('*')
				->from('FormBlocksOptions')
				->where('clientid =?', $clientid);
			if($form_type)
			{
				$sel_ord->andWhere('form_type=?', $form_type);
			}
			$sel_ord_res = $sel_ord->fetchArray();

			if($sel_ord_res)
			{
				return $sel_ord_res;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * TODO-3843 Ancuta 11.02.2021
		 * @param number $clientid
		 * @param number $form_type
		 * @param boolean $block
		 * @return array
		 */
		public static function  write2course_recordata($clientid = 0,$form_type =false,$block =false ){
		    
		    if(empty($clientid)){
		        return array();
		    }
		    
		    $sel_ord = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlocksOptions')
		    ->where('clientid =?', $clientid)
		    ->andWhere('write2recorddata=?',1);
		    if($form_type)
		    {
		        $sel_ord->andWhere('form_type=?', $form_type);
		    }
		    if($block)
		    {
		        $sel_ord->andWhere('block=?', $block);
		    }
		    $sel_ord_res = $sel_ord->fetchArray();
		    
		    if(empty($sel_ord_res)){
		        return array();
		    }
		        
		    $write2course_blocks = array();
		    foreach($sel_ord_res as $k=>$bl_data){
		        $write2course_blocks[$bl_data['block']]['allow'] = '1';
		        $write2course_blocks[$bl_data['block']]['color'] = '#'.$bl_data['write2recorddata_color'];
                $write2course_blocks[$bl_data['block']]['shortcut'] = $bl_data['write2shortcut'];//TODO-4035
		    }

		    
		    return $write2course_blocks;
		}
	}

?>