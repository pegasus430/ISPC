<?php

	require_once("Pms/Form.php");

	class Application_Form_HospizPerformance extends Pms_Form {

		public function insert_hospiz_performance($ipid, $post, $current_period_days, $bre_hospiz_shortcuts)
		{
			if(count($post['assessment']) > '0')
			{
				foreach($post as $k_shortcut => $shortcut_data)
				{
					if(in_array($k_shortcut, $bre_hospiz_shortcuts))
					{
						foreach($current_period_days as $k_cp_day => $v_cp_day)
						{
							$day_number = date('Y-m-d', strtotime($v_cp_day));
							$formatted_date = date('Y-m-d H:i:s', strtotime($v_cp_day));

							if(array_key_exists($day_number, $shortcut_data))
							{
								$value = '1';
								$qty = '1';
							}
							else
							{
								$value = '0';
								$qty = '0';
							}

							$brehospiz_final_data[] = array(
								'ipid' => $ipid,
								'client' => $clientid,
								'shortcut' => $k_shortcut,
								'qty' => $qty,
								'value' => $value,
								'date' => $formatted_date,
							);
						}
					}
				}
			}
			else
			{
				//no post data but submit was made(aka cear all checkboxes)
				foreach($current_period_days as $k_cp_day => $v_cp_day)
				{
					$day_number = date('Y-m-d', strtotime($v_cp_day));
					$formatted_date = date('Y-m-d H:i:s', strtotime($v_cp_day));

					$value = '0';
					$qty = '0';

					$brehospiz_final_data[] = array(
						'ipid' => $ipid,
						'client' => $clientid,
						'shortcut' => $k_shortcut,
						'qty' => $qty,
						'value' => $value,
						'date' => $formatted_date,
					);
				}
			}
			//insert current month data
			$bre_hospiz_collection = new Doctrine_Collection('HospizControl');
			$bre_hospiz_collection->fromArray($brehospiz_final_data);
			$bre_hospiz_collection->save();
		}

		public function reset_hospiz_performance($ipid, $start_date)
		{
			$start_date = date('Y-m-d H:i:s', strtotime($start_date));

			$q_del = Doctrine_Query::create()
				->delete('HospizControl')
				->where('MONTH(date) = MONTH("' . $start_date . '")')
				->andWhere('YEAR(date) = YEAR("' . $start_date . '")')
				->andWhere('ipid LIKE "' . $ipid . '"');
			$q_del_res = $q_del->execute();
		}

	}

?>
