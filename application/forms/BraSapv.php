<?php
require_once("Pms/Form.php");
class Application_Form_BraSapv extends Pms_Form
{
	public function insert_bra_sapv_values ( $ipid, $post, $current_period_days, $bra_shortcuts, $weekprice_shortcuts )
	{
		foreach ($post as $k_shortcut => $shortcut_data)
		{
			if (in_array($k_shortcut, $bra_shortcuts))
			{
				foreach ($current_period_days as $k_cp_day => $v_cp_day)
				{
					$day_number = date('d', strtotime($v_cp_day));
					$formatted_date = date('Y-m-d H:i:s', strtotime($v_cp_day));

					if (array_key_exists($day_number, $shortcut_data))
					{
						$value = '1';

						if (in_array($k_shortcut, $weekprice_shortcuts))
						{
							$starter = '1';
							$end_group_date[$k_shortcut] = date('Y-m-d', strtotime('+6 days', strtotime($formatted_date)));
						}
						else
						{
							$starter = '0';
						}

						if($post[$k_shortcut.'_qty'][$day_number] > '1')
						{
							$qty = $post[$k_shortcut.'_qty'][$day_number];
						}
						else
						{
							$qty = '1';
						}
					}
					else
					{
						$value = '0';

						if (in_array($k_shortcut, $weekprice_shortcuts) && strlen($end_group_date[$k_shortcut]) > 0 && strtotime($v_cp_day) <= strtotime($end_group_date[$k_shortcut]))
						{
							$value = '1';
						}
						//check remaining days from any starter within previous month
						if(strlen($post[$k_shortcut.'_start_days'][$day_number])>0 && $post[$k_shortcut.'_start_days'][$day_number] == '1')
						{
							$value = '1';
						}

						$starter = '0';
					}

					$brasapv_final_data[] = array(
							'ipid' => $ipid,
							'shortcut' => $k_shortcut,
							'qty' => $qty,
							'value' => $value,
							'starter' => $starter,
							'date' => $formatted_date,
					);
				}
			}
		}

		//insert current month data
		$brasapv_collection = new Doctrine_Collection('BraSapvControl');
		$brasapv_collection->fromArray($brasapv_final_data);
		$brasapv_collection->save();
	}

	public function reset_bra_sapv ( $ipid, $start_date )
	{
		$start_date = date('Y-m-d H:i:s', strtotime($start_date));

		$q_del = Doctrine_Query::create()
		->delete('BraSapvControl')
		->where('MONTH(date) = MONTH("' . $start_date . '")')
		->andWhere('YEAR(date) = YEAR("' . $start_date . '")')
		->andWhere('ipid LIKE "' . $ipid . '"');
		$q_del_res = $q_del->execute();
	}

}
?>
