<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlocks2Type', 'SYSDAT');

	class FormBlocks2Type extends BaseFormBlocks2Type {

		public function get_form_types_blocks($client, $form_type, $is_checking = false)
		{
			$select = Doctrine_Query::create()
				->select('*')
				->from('FormBlocks2Type')
				->where('form_type="' . $form_type . '"')
				->andWhere('clientid = "' . $client . '"');
			$select_res = $select->fetchArray();

			if($select_res)
			{
				if($is_checking)
				{
					foreach($select_res as $k_res => $v_res)
					{
						$returned[$v_res['form_type']][] = $v_res['form_block'];
					}

					return $returned;
				}
				else
				{
					return $select_res;
				}
			}
			else
			{
				return false;
			}
		}


		/**
		 * 
		 * @param number $client
		 * @param number $form_type
		 * @param string $is_checking
		 * @param unknown $form_blocks
		 * @return unknown|Ambigous <multitype:, Doctrine_Collection>|boolean
		 * Ancuta
		 * 01.10.2018
		 */
		public function get_form_types_blocks_special($client = 0, $form_type = 0 ,  $form_blocks = array())
		{
		    if(empty($client)){
		        $logininfo = new Zend_Session_Namespace('Login_Info');
                $client = $logininfo->clientid;
		    }
		    
			$select = Doctrine_Query::create()
				->select('*')
				->from('FormBlocks2Type')
				->where('clientid = ?', $client);
    			if(!empty($form_type)){
    				$select->andWhere('form_type = ?', $form_type );
    			}
    			
    			if(!empty($form_blocks) && is_array($form_blocks)){
    				$select->andWhereIn('form_block', $form_blocks );
    			}
    			
			$select_res = $select->fetchArray();
//     			dd($select_res);

			if($select_res)
			{
				return $select_res;
				 
			}
			else
			{
				return false;
			}
		}

	}

?>