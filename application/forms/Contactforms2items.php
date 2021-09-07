<?php

	require_once("Pms/Form.php");

	class Application_Form_Contactforms2items extends Pms_Form {

		public function insert_data($post)
		{
			$this->clear_data($post['clientid']);

			foreach($post['form2item'] as $k_form => $items)
			{
				foreach($items as $k_item_id => $v_item)
				{
					$forms2items_data[] = array(
						'clientid' => $post['clientid'],
						'form' => $k_form,
						'item' => $k_item_id,
						'isdelete' => '0',
					);
				}
			}

			if($forms2items_data)
			{
				$collection = new Doctrine_Collection('Forms2Items');
				$collection->fromArray($forms2items_data);
				$collection->save();
			}
		}

		private function clear_data($client)
		{
			$update = Doctrine_Query::create()
				->update("Forms2Items")
				->set('isdelete', '1')
				->where('clientid = "' . $client . '"')
				->andWhere('isdelete = "0"');
			$update->execute();

			if($update)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

	}

?>