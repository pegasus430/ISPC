<?php

	require_once("Pms/Form.php");

	class Application_Form_PlansMediPrint extends Pms_Form {

		
		public function insert_groups($client, $post)
		{
			//print_r($post); exit;
			Application_Form_PlansMediPrint::clear_groups($client);

			foreach($post['client_plans'] as $k_cl_gr => $v_cl_gr)
			{
				$client_groups_arr[] = array(
					'clientid' => $client,
					'plansmedi_id' => $v_cl_gr,
					'isdelete' => "0"
				);
			}

			if($client_groups_arr)
			{
				$collection = new Doctrine_Collection('PlansMediPrint');
				$collection->fromArray($client_groups_arr);
				$collection->save();
			}
		}

		public function clear_groups($client)
		{
			$q = Doctrine_Query::create()
				->update('PlansMediPrint')
				->set('isdelete', "1")
				->where('clientid = "' . $client . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

	}

?>