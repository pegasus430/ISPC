<?php

	Doctrine_Manager::getInstance()->bindComponent('Forms2Items', 'SYSDAT');

	//contact forms 2 form items
	class Forms2Items extends BaseForms2Items {

		public function get_form_items($clientid, $form = false)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('Forms2Items')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"');
			if($form)
			{
				$q->andWhere('form = "' . $form . '"');
			}
			$q_res = $q->fetchArray();

			if($q_res)
			{
				$items_arr[] = '9999999999';
				foreach($q_res as $k_res => $v_res)
				{
					$items_arr[] = $v_res['item'];
				}
				$forms_items = FormsItems::items_details($clientid, $items_arr);

				foreach($q_res as $k_res_data => $v_res_data)
				{
					$v_res_data['item_data'] = $forms_items[$v_res_data['item']];
					$form_items[$v_res_data['form']][$v_res_data['item']] = $v_res_data;
				}

				return $form_items;
			}
			else
			{
				return false;
			}
		}

		public function get_items_forms($clientid, $items = false)
		{
			if($items)
			{
				$forms_items_data = FormsItems::items_details($clientid, $items);

				$q = Doctrine_Query::create()
					->select('*')
					->from('Forms2Items')
					->where('isdelete = "0"')
					->andWhere('clientid="' . $clientid . '"')
					->andWhereIn('item', $items);
				$q_res = $q->fetchArray();

				foreach($q_res as $k_res => $v_res)
				{
					$item2contactforms[$forms_items_data[$v_res['item']]['item']][] = $v_res['form'];
				}

				if($item2contactforms)
				{
					return $item2contactforms;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

	}

?>