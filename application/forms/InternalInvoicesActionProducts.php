<?php

class Application_Form_InternalInvoicesActionProducts extends Pms_Form
{

	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$validate = new Pms_Validation();

		$global_error = false;
		//update validation
		if(count($post['update_pid']) > 0)
		{
			foreach($post['update_pid'] as $k_produs=> $v_produs_id)
			{
				$error[$v_produs_id] = false;
				if(strlen($post['user_group'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['form_type'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['range_start'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['range_end'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['range_type'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['time_start'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['time_end'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}


				if($error[$v_produs_id])
				{
					$this->error_message[$v_produs_id] = $Tr->translate('product_has_empty_fields');
					$global_error = true;
				}

			}
		}

		//new items validation
		if(count($post['new_product']) > 0)
		{
			foreach($post['new_product'] as $k_row_produs=> $v_produs_row_id)
			{
				$error[$v_produs_row_id] = false;
				if(strlen($post['user_group'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['form_type'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['range_start'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['range_end'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['range_type'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['time_start'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['time_end'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

			 
				if($error[$v_produs_row_id])
				{
					$this->error_message[$v_produs_row_id] = $Tr->translate('product_has_empty_fields');
					$global_error = true;
				}
			}
		}

		return !$global_error;
	}

	public function insert_product($post = false, $client = false, $list = false)
	{
	    
		if($post && $client && $list)
		{
			$returned_products = array_values($post['update_pid']);
			$returned_products[] = '9999999999999';
				
			if(!empty($returned_products))
			{
				$this->delete_products($returned_products, $client, $list);
			}
			
			$product_id = false;
			foreach($post['new_product'] as $k_produs=> $v_produs_id)
			{
			    $km_rage_start = '';
			    $km_rage_end = '';
			    $min_rage_start = '';
			    $min_rage_end = '';
			     
			    if($post['range_type'][$v_produs_id] == 'min')
			    {
			        $min_rage_start = $post['range_start'][$v_produs_id];
			        $min_rage_end = $post['range_end'][$v_produs_id];
			    
			        $km_rage_start = '';
			        $km_rage_end = '';
			    }
			    elseif($post['range_type'][$v_produs_id] == 'km')
			    {
			        $min_rage_start = '';
			        $min_rage_end = '';
			    
			        $km_rage_start = $post['range_start'][$v_produs_id];
			        $km_rage_end = $post['range_end'][$v_produs_id];
			    }
 
			    $new_product = new InternalInvoicesActionProducts();
				$new_product->client =  $client;
				$new_product->list = $list;
				$new_product->usergroup = $post["user_group"][$v_produs_id];
				$new_product->contactform_type = $post["form_type"][$v_produs_id];
				$new_product->range_start = $min_rage_start;
				$new_product->range_end = $min_rage_end;
				$new_product->km_range_start = $km_rage_start;
				$new_product->km_range_end = $km_rage_end;
				$new_product->range_type = $post["range_type"][$v_produs_id];
				$new_product->time_start = $post["time_start"][$v_produs_id];
				$new_product->time_end = $post["time_end"][$v_produs_id];
				$new_product->calculation_trigger = $post["trigger"][$v_produs_id];
				$new_product->holiday =  $post["holiday"][$v_produs_id];
				$new_product->save();
				$product_id = $new_product->id;

				if($product_id ){
				    
				    $actions_array = array();
				    $search_key="";
				    foreach($post['actions'][$v_produs_id] as $k => $action_id){

				        $search_key = $list.$product_id.$action_id;
				        if(!in_array($search_key,$actions_array)){
				            
				            $action2products[] = array(
				                'client' => $client,
				                'list' => $list,
				                'product_id' => $product_id,
				                'action_id' => $action_id
				            );
				            $actions_array[] = $search_key;
				        }
				    }
				    
				}
			}
			
		    if(!empty($action2products)){
			    $collection = new Doctrine_Collection('InternalInvoicesAction2Products');
			    $collection->fromArray($action2products);
			    $collection->save();
		    }
			
		    
		    $action2products_up = array();
		    $actions_array_up = array();
		    $search_key_up = "";
			foreach($post['update_pid'] as $k_prod => $v_prod)
			{
				$update_product = Doctrine::getTable('InternalInvoicesActionProducts')->findOneByIdAndClientAndList($v_prod, $client, $list);

				if($update_product)
				{
					$update_product->usergroup = $post['user_group'][$v_prod];
					$update_product->contactform_type = $post['form_type'][$v_prod];
					if($post['range_type'][$v_prod] == 'min')
					{
						$update_product->range_start = $post['range_start'][$v_prod];
						$update_product->range_end = $post['range_end'][$v_prod];
						$update_product->km_range_start = '';
						$update_product->km_range_end = '';
					}
					else
					{
						$update_product->range_start = '';
						$update_product->range_end = '';
						$update_product->km_range_start = Pms_CommonData::str2num($post['range_start'][$v_prod]);
						$update_product->km_range_end = Pms_CommonData::str2num($post['range_end'][$v_prod]);
					}
					$update_product->range_type = $post['range_type'][$v_prod];
					$update_product->time_start = $post['time_start'][$v_prod];
					$update_product->time_end = $post['time_end'][$v_prod];
					$update_product->calculation_trigger = $post['trigger'][$v_prod];
					$update_product->holiday = $post['holiday'][$v_prod];
					$update_product->save();
				}
				
				
				
				// update actions to product
                $clear_actions = $this->delete_action2products($v_prod, $client, $list);

				foreach($post['actions'][$v_prod] as $ks => $action_id_up){
				     
			        $search_key_up = $list.$v_prod.$action_id_up;

				    if(!in_array($search_key_up,$actions_array_up)){
				        $actions_array_up[] = $search_key_up;
				        
				        $action2products_up[] = array(
				            'client' => $client,
				            'list' => $list,
				            'product_id' => $v_prod,
				            'action_id' => $action_id_up
				        );
				        
				    }
				}
			}
 
			if(!empty($action2products_up)){
    			$collection_i = new Doctrine_Collection('InternalInvoicesAction2Products');
    			$collection_i->fromArray($action2products_up);
    			$collection_i->save();
			}
		}
		else
		{
			return false;
		}
	}

	private function delete_products($returned_products = false, $client = false, $list = false)
	{
		if($returned_products && $client && $list)
		{
			$update = Doctrine_Query::create()
			->update('InternalInvoicesActionProducts')
			->set('isdelete', '1')
			->whereNotIn('id', $returned_products)
			->andWhere('client = "'.$client.'"')
			->andWhere('list = "' . $list . '"')
			->andWhere('isdelete=0');
			$update_res = $update->execute();
		}
	}

	private function delete_action2products($product_id = false, $client = false, $list = false)
	{
		if($product_id && $client && $list)
		{
			$update = Doctrine_Query::create()
			->update('InternalInvoicesAction2Products')
			->set('isdelete', '1')
			->where('product_id = "'.$product_id.'"')
			->andWhere('client = "'.$client.'"')
			->andWhere('list = "' . $list . '"')
			->andWhere('isdelete=0');
			$update_res = $update->execute();
		}
	}

}

?>
