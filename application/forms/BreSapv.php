<?php

	require_once("Pms/Form.php");

	class Application_Form_BreSapv extends Pms_Form {

		public function insert_bre_sapv_values($ipid, $post, $current_period_days, $bre_shortcuts, $days2verordnet = false)
		{
			foreach($post as $k_shortcut => $shortcut_data)
			{
				if(in_array($k_shortcut, $bre_shortcuts))
				{
					foreach($current_period_days as $k_cp_day => $v_cp_day)
					{
						$day_number = date('d', strtotime($v_cp_day));
						$formatted_date = date('Y-m-d H:i:s', strtotime($v_cp_day));

						if(array_key_exists($day_number, $shortcut_data) && (($k_shortcut == "aut" && end($days2verordnet[$v_cp_day]) == "3") || ($k_shortcut == "vv" && end($days2verordnet[$v_cp_day]) == "4") || $k_shortcut == "abk" || $k_shortcut == "bk" ))
						{
							$value = '1';
							$qty = '1';
						}
						else
						{
							$value = '0';
							$qty = '0';
						}

						$bresapv_final_data[] = array(
							'ipid' => $ipid,
							'shortcut' => $k_shortcut,
							'qty' => $qty,
							'value' => $value,
							'date' => $formatted_date,
						);
					}
				}
			}
			//insert current month data
			$brasapv_collection = new Doctrine_Collection('BreSapvControl');
			$brasapv_collection->fromArray($bresapv_final_data);
			$brasapv_collection->save();
		}

		public function reset_bre_sapv($ipid, $start_date)
		{
			$start_date = date('Y-m-d H:i:s', strtotime($start_date));

			$q_del = Doctrine_Query::create()
				->delete('BreSapvControl')
				->where('MONTH(date) = MONTH("' . $start_date . '")')
				->andWhere('YEAR(date) = YEAR("' . $start_date . '")')
				->andWhere('ipid LIKE "' . $ipid . '"');
			$q_del_res = $q_del->execute();
		}

	}

?>
