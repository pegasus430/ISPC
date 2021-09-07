<?php

	Doctrine_Manager::getInstance()->bindComponent('FormsItems', 'SYSDAT');

	class FormsItems extends BaseFormsItems {

//		$form is string form identifier!
        /**
         * 
         * @param string $clientid
         * @param string $form
         * @param string $type
         * @return multitype:|Ambigous <multitype:, Doctrine_Collection>|Ambigous <multitype:, unknown>
         */
		public function get_all_form_items($clientid = false, $form = false, $type=false)
		{
		    if( empty($clientid) || empty($form) ){
		        return array();
		    }
		    
			if(is_array($form))
			{
				$form_ids = $form;
			}
			else
			{
				$form_ids = array($form);
			}

            //  check if client has items, if not,  add
			FormsItems::items_add_if_empty($clientid,$form_ids);// 14.11.2018 -  TODO-1932:: force add items to client if has NO items available @Ancuta
			
			$q = Doctrine_Query::create()
				->select('*')
				->from('FormsItems')
				->Where('client = ?',$clientid )
				->andWhereIn('form', $form_ids);
			if($type)
			{
				$q->andWhere('type= ?', $type);
			}
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_item => $v_item)
				{
					$forms_items[$v_item['form']][] = $v_item;
				}
				
				return $forms_items;
			}  
		}
		
        /**
         * 
         * @param unknown $clientid
         * @param unknown $items
         * @return multitype:|Ambigous <multitype:, Doctrine_Collection>
         */
		public function items_details($clientid, $items)
		{
		    if(empty($clientid) || empty($items)){
		        return array();
		    }
		    
			$q = Doctrine_Query::create()
				->select('*')
				->from('FormsItems')
				->whereIn('id', $items)
				->andWhere('client = ?',$clientid );
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$q_res_arr[$v_res['id']] = $v_res;
				}

				return $q_res_arr;
			}
		}

		
    public function items_add_if_empty($clientid = false, $form_ids = array())
    {
        if (empty($clientid) || empty($form_ids)) {
            return false;
        }
        
        // if empty db - add default
        $q = Doctrine_Query::create()->select('*')
            ->from('FormsItems')
            ->Where('client = ?', $clientid)
            ->andWhereIn('form', $form_ids);
        $q_res = $q->fetchArray();
        
        if (empty($q_res)) {
            $q_def = Doctrine_Query::create()->select('*')
                ->from('FormsItems')
                ->Where('client = 0 ')
                ->andWhereIn('form', $form_ids);
            $q_def_res = $q_def->fetchArray();
            
            $items_arr = array();
            foreach ($q_def_res as $k => $data) {
                $items_arr[] = array(
                    'client' => $clientid,
                    'form' => $data['form'],
                    'item' => $data['item'],
                    'type' => $data['type']
                );
            }
            if (! empty($items_arr)) {
                // insert many records with one query!!
                $collection = new Doctrine_Collection('FormsItems');
                $collection->fromArray($items_arr);
                $collection->save();
            }
        }
    }
		
	}

?>