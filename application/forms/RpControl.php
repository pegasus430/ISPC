<?php

	require_once("Pms/Form.php");

	class Application_Form_RpControl extends Pms_Form {

		public function insert_rp_values($ipid, $post, $current_period_days, $rp_shortcuts)
		{
			$ipid_arr = array($ipid);
			//clear old data
			self::reset_rp_control($ipid_arr, $current_period_days[0]);
			
			foreach($post as $k_shortcut => $shortcut_data)
			{
				if(in_array($k_shortcut, $rp_shortcuts))
				{
					foreach($current_period_days as $k_cp_day => $v_cp_day)
					{
						$formatted_date = date('Y-m-d H:i:s', strtotime($v_cp_day));

						$rp_final_data[] = array(
							'ipid' => $ipid,
							'shortcut' => $k_shortcut,
							'qty_home' => $post[$k_shortcut][$v_cp_day]['p_home'],
							'qty_nurse' => $post[$k_shortcut][$v_cp_day]['p_nurse'],
							'qty_hospiz' => $post[$k_shortcut][$v_cp_day]['p_hospiz'],
							'date' => $formatted_date,
						);
					}
				}
			}
			
			//insert current month data
			$rp_collection = new Doctrine_Collection('RpControl');
			$rp_collection->fromArray($rp_final_data);
			$rp_collection->save();
		}

		public function reset_rp_control($ipid, $date)
		{
			$start_date = date('Y-m-d H:i:s', strtotime($date));

			$q_del = Doctrine_Query::create()
				->update('RpControl')
				->set('isdelete', '1')
				->where('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('YEAR(date) = YEAR("' . $date . '")')
				->andWhereIn('ipid', $ipid);
			$q_del_res = $q_del->execute();
		}

	}

?>
