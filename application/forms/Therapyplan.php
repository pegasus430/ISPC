<?php

	require_once("Pms/Form.php");

	class Application_Form_Therapyplan extends Pms_Form {

		public function insert_data($ipid, $post, $form_tabnames)
		{
//			print_r($post);
//			exit;
			
			if(strlen($post['special_field']) >'0')
			{
				$special= Doctrine::getTable('Therapyplan')->find($post['fid']);
				if($special->toArray())
				{
					$special->special_field = $post['special_field'];
					$special->save();
				}
			}
			
			foreach($post['user'] as $k_elem => $v_values)
			{
				foreach($v_values as $k_element_row => $v_name)
				{
					if(strlen($v_name) > '0' && strlen($post['userid'][$k_elem][$k_element_row]) > '0')
					{
						$final_data_arr[] = array(
							'ipid' => $ipid,
							'therapyplan_id' => $post['fid'],
							'tabname' => $k_elem,
							'user_id' => $post['userid'][$k_elem][$k_element_row],
							'full_name' => $v_name,
							'comment' => $post['comment'][$k_elem][$k_element_row],
						);
					}
				}
			}

			if(count($final_data_arr) > '0')
			{
				$this->clear_items($post['fid']);

				$collection = new Doctrine_Collection('TherapyplanItems');
				$collection->fromArray($final_data_arr);
				$collection->save();

				return true;
			}
		}

		public function create_main_form($ipid)
		{
			$res = new Therapyplan();
			$res->ipid = $ipid;
			$res->isdelete = "0";
			$res->save();

			if($res->id)
			{
				return $res->id;
			}
			else
			{
				return false;
			}
		}

		private function clear_items($formid)
		{
			$Q = Doctrine_Query::create()
				->update('TherapyplanItems')
				->set('isdelete', '1')
				->where('therapyplan_id = "' . $formid . '"')
				->andWhere('isdelete = "0"');
			$Q->execute();

			return true;
		}

	}

?>