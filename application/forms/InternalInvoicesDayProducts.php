<?php

class Application_Form_InternalInvoicesDayProducts extends Pms_Form
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
				if($post['grouped'][$v_produs_id] == '1')
				{
					$grouped_product[$v_produs_id] = $post['grouped'][$v_produs_id];
				}
				else
				{
					$grouped_product[$v_produs_id] = '0';
				}
				
				$products_data[] = array(
						'client'		=>$client,
						'list'			=>$list,
						'usergroup'		=>$post['user_group'][$v_produs_id],
						'grouped'		=>$grouped_product,
						'sapv'			=>$post['sapv'][$v_produs_id],
						'normal_price_name'	=>trim(rtrim($post['normal_price_name'][$v_produs_id])),
						'normal_price'		=>Pms_CommonData::str2num($post['normal_price'][$v_produs_id]),
						'hosp_adm_price_name'	=>trim(rtrim($post['hosp_adm_price_name'][$v_produs_id])),
						'hosp_adm_price'	=>Pms_CommonData::str2num($post['hosp_adm_price'][$v_produs_id]),
						'hosp_price_name'	=>trim(rtrim($post['hosp_price_name'][$v_produs_id])),
						'hosp_price'		=>Pms_CommonData::str2num($post['hosp_price'][$v_produs_id]),
						'hosp_dis_price_name'	=>trim(rtrim($post['hosp_dis_price_name'][$v_produs_id])),
						'hosp_dis_price'	=>Pms_CommonData::str2num($post['hosp_dis_price'][$v_produs_id]),
						'hospiz_adm_price_name'	=>trim(rtrim($post['hospiz_adm_price_name'][$v_produs_id])),
						'hospiz_adm_price'	=>Pms_CommonData::str2num($post['hospiz_adm_price'][$v_produs_id]),
						'hospiz_price_name'	=>trim(rtrim($post['hospiz_price_name'][$v_produs_id])),
						'hospiz_price'		=>Pms_CommonData::str2num($post['hospiz_price'][$v_produs_id]),
						'hospiz_dis_price_name'	=>trim(rtrim($post['hospiz_dis_price_name'][$v_produs_id])),
						'hospiz_dis_price'	=>Pms_CommonData::str2num($post['hospiz_dis_price'][$v_produs_id]),
						'standby_price_name'	=>trim(rtrim($post['standby_price_name'][$v_produs_id])),
						'standby_price'		=>Pms_CommonData::str2num($post['standby_price'][$v_produs_id]),
						'hosp_dis_hospiz_adm_price_name'=>trim(rtrim($post['hosp_dis_hospiz_adm_price_name'][$v_produs_id])),
						'hospiz_dis_hosp_adm_price'	=>Pms_CommonData::str2num($post['hospiz_dis_hosp_adm_price'][$v_produs_id]),
						'hospiz_dis_hosp_adm_price_name'=>trim(rtrim($post['hospiz_dis_hosp_adm_price_name'][$v_produs_id])),
						'hospiz_dis_hosp_adm_price'	=>Pms_CommonData::str2num($post['hospiz_dis_hosp_adm_price'][$v_produs_id]),
						'holiday'			=>$post['holiday'][$v_produs_id],
						'isdelete'			=>'0',
				);
			}

			$collection = new Doctrine_Collection('InternalInvoicesDayProducts');
			$collection->fromArray($products_data);
			$collection->save();

			foreach($post['update_pid'] as $k_prod=> $v_prod)
			{
				$update_product = Doctrine::getTable('InternalInvoicesDayProducts')->findOneByIdAndClientAndList($v_prod, $client, $list);

				if($update_product)
				{
					if($post['grouped'][$v_prod] == '1')
					{
						$grouped_product[$v_prod] = $post['grouped'][$v_prod];
					}
					else
					{
						$grouped_product[$v_prod] = '0';
					}
					
					$update_product->usergroup = $post['user_group'][$v_prod];
					$update_product->grouped = $post['grouped'][$v_prod];
					$update_product->sapv = $post['sapv'][$v_prod];
					$update_product->normal_price = Pms_CommonData::str2num($post['normal_price'][$v_prod]);
					$update_product->normal_price_name = $post['normal_price_name'][$v_prod];

					$update_product->hosp_adm_price_name = $post['hosp_adm_price_name'][$v_prod];
					$update_product->hosp_adm_price = Pms_CommonData::str2num($post['hosp_adm_price'][$v_prod]);

					$update_product->hosp_price_name = $post['hosp_price_name'][$v_prod];
					$update_product->hosp_price = Pms_CommonData::str2num($post['hosp_price'][$v_prod]);

					$update_product->hosp_dis_price_name = $post['hosp_dis_price_name'][$v_prod];
					$update_product->hosp_dis_price = Pms_CommonData::str2num($post['hosp_dis_price'][$v_prod]);

					$update_product->hospiz_adm_price_name = $post['hospiz_adm_price_name'][$v_prod];
					$update_product->hospiz_adm_price = Pms_CommonData::str2num($post['hospiz_adm_price'][$v_prod]);

					$update_product->hospiz_price_name = $post['hospiz_price_name'][$v_prod];
					$update_product->hospiz_price = Pms_CommonData::str2num($post['hospiz_price'][$v_prod]);

					$update_product->hospiz_dis_price_name = $post['hospiz_dis_price_name'][$v_prod];
					$update_product->hospiz_dis_price = Pms_CommonData::str2num($post['hospiz_dis_price'][$v_prod]);

					$update_product->standby_price_name = $post['standby_price_name'][$v_prod];
					$update_product->standby_price = Pms_CommonData::str2num($post['standby_price'][$v_prod]);
						
					$update_product->hosp_dis_hospiz_adm_price_name = $post['hosp_dis_hospiz_adm_price_name'][$v_prod];
					$update_product->hosp_dis_hospiz_adm_price = Pms_CommonData::str2num($post['hosp_dis_hospiz_adm_price'][$v_prod]);
						
					$update_product->hospiz_dis_hosp_adm_price_name = $post['hospiz_dis_hosp_adm_price_name'][$v_prod];
					$update_product->hospiz_dis_hosp_adm_price = Pms_CommonData::str2num($post['hospiz_dis_hosp_adm_price'][$v_prod]);
						
					$update_product->holiday = $post['holiday'][$v_prod];

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
			->update('InternalInvoicesDayProducts')
			->set('isdelete', '1')
			->whereNotIn('id', $returned_products)
			->andWhere('client = "' . $client . '"')
			->andWhere('list = "' . $list . '"')
			->andWhere('isdelete=0');
			$update_res = $update->execute();
		}
	}

}

?>