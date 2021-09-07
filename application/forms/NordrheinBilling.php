<?php

	require_once("Pms/Form.php");

	class Application_Form_NordrheinBilling extends Pms_Form {

		public function insert_data($ipid = false, $post, $shortcuts = false, $curent_month_days = false)
		{
			$dummy_date = '0000-00-00 00:00:00';

			if($ipid && $post['curent_month'] != '' && date('Y', strtotime($post['curent_month'])) != '1970')
			{
				$clear = Application_Form_NordrheinBilling::clear_form_data($post['clientid'], $ipid, $post['curent_month']);
			}

			if($ipid && $shortcuts && $curent_month_days)
			{
				foreach($curent_month_days as $k_day => $v_day)
				{
					foreach($shortcuts as $k_short => $v_shortcut)
					{
						if($post[$v_shortcut][$v_day]['checked'] == '1')
						{
							$checked = '1';

							if($post[$v_shortcut][$v_day]['qty'] == '0')
							{
								$qty = $checked;
							}
							else
							{
								$qty = $post[$v_shortcut][$v_day]['qty'];
							}
						}
						else
						{
							$checked = '0';
							$qty = 0;
						}

						$shortcuts_data[] = array(
							'client' => $post['clientid'],
							'ipid' => $ipid,
							'shortcut' => $v_shortcut,
							'qty' => $qty, //real qty
							'value' => $checked, //this will be checked value
							'date' => date('Y-m-d H:i:s', strtotime($v_day)),
							'isdelete' => '0'
						);
					}
				}

				if($shortcuts_data)
				{
					$collection = new Doctrine_Collection('NordrheinBilling');
					$collection->fromArray($shortcuts_data);
					$collection->save();
				}
 
			}
		}

		public function clear_form_data($client, $ipid, $curent_month)
		{
			$q = Doctrine_Query::create()
				->update('NordrheinBilling')
				->set('isdelete', "1")
				->where('client = ?', $client)
				->andWhere('ipid= ?',$ipid)
				->andWhere('MONTH(date) = MONTH("' . $curent_month . '")')
				->andWhere('YEAR(date) = YEAR("' . $curent_month . '")');
			$q->execute();
		}
	}

?>