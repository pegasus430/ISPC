<?php

class Application_Form_InternalInvoicesSpecificProducts extends Pms_Form
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

				if(strlen($post['price'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['name'][$v_produs_id]) == '0')
				{
					$error[$v_produs_id] = true;
				}

				if(strlen($post['code'][$v_produs_id]) == '0')
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

				if(strlen($post['price'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['name'][$v_produs_row_id]) == '0')
				{
					$error[$v_produs_row_id] = true;
				}

				if(strlen($post['code'][$v_produs_row_id]) == '0')
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
				
			foreach($post['new_product'] as $k_produs=> $v_produs_id)
			{
				if($post['related_users'][$v_produs_id] != 0)
				{
					$related_users[$v_produs_id] = $post['related_users'][$v_produs_id];
				}
				else
				{
					$related_users[$v_produs_id] = '0';
				}

				if($post['range_type'][$v_produs_id] == 'min')
				{
					$products_data[] = array(
							'client'=> $client,
							'list'=>$list,
							'usergroup'=>$post['user_group'][$v_produs_id],
							'contactform_type'=>$post['form_type'][$v_produs_id],
							'range_start' =>$post['range_start'][$v_produs_id],
							'range_end' =>$post['range_end'][$v_produs_id],
							'km_range_start' =>'',
							'km_range_end' =>'',
							'range_type'=>$post['range_type'][$v_produs_id],
							'time_start'=>$post['time_start'][$v_produs_id],
							'time_end'=>$post['time_end'][$v_produs_id],
							'price'=>Pms_CommonData::str2num($post['price'][$v_produs_id]),
							'name'=>$post['name'][$v_produs_id],
							'code'=>$post['code'][$v_produs_id],
							'calculation_trigger'=>$post['trigger'][$v_produs_id],
							'asigned_users'=> $related_users[$v_produs_id],
							'holiday'=> $post['holiday'][$v_produs_id],
							'showtime'=> $post['showtime'][$v_produs_id],
					);
				}
				else if($post['range_type'][$v_produs_id])
				{
					$products_data[] = array(
							'client'=> $client,
							'list'=>$list,
							'usergroup'=>$post['user_group'][$v_produs_id],
							'contactform_type'=>$post['form_type'][$v_produs_id],
							'range_start' =>'',
							'range_end' =>'',
							'km_range_start' =>$post['range_start'][$v_produs_id],
							'km_range_end' =>$post['range_end'][$v_produs_id],
							'range_type'=>$post['range_type'][$v_produs_id],
							'time_start'=>$post['time_start'][$v_produs_id],
							'time_end'=>$post['time_end'][$v_produs_id],
							'price'=>Pms_CommonData::str2num($post['price'][$v_produs_id]),
							'name'=>$post['name'][$v_produs_id],
							'code'=>$post['code'][$v_produs_id],
							'calculation_trigger'=>$post['trigger'][$v_produs_id],
							'asigned_users'=> $related_users[$v_produs_id],
							'holiday'=> $post['holiday'][$v_produs_id],
							'showtime'=> $post['showtime'][$v_produs_id],
					);
				}


			}
			//			print_r($products_data);exit;
			$collection = new Doctrine_Collection('InternalInvoicesSpecificProducts');
			$collection->fromArray($products_data);
			$collection->save();
				
			foreach($post['update_pid'] as $k_prod => $v_prod)
			{
				$update_product = Doctrine::getTable('InternalInvoicesSpecificProducts')->findOneByIdAndClientAndList($v_prod, $client, $list);

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
					$update_product->price = Pms_CommonData::str2num($post['price'][$v_prod]);
					$update_product->name = $post['name'][$v_prod];
					$update_product->code = $post['code'][$v_prod];
					$update_product->calculation_trigger = $post['trigger'][$v_prod];
					$update_product->asigned_users = $post['related_users'][$v_prod];
					$update_product->holiday = $post['holiday'][$v_prod];
					$update_product->showtime = $post['showtime'][$v_prod];
					$update_product->save();
				}
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
			->update('InternalInvoicesSpecificProducts')
			->set('isdelete', '1')
			->whereNotIn('id', $returned_products)
			->andWhere('client = "'.$client.'"')
			->andWhere('list = "' . $list . '"')
			->andWhere('isdelete=0');
			$update_res = $update->execute();
		}
	}

}

?>
