<?php

	require_once("Pms/Form.php");

	class Application_Form_Anlage14 extends Pms_Form {

		public function insert_data($ipid = false, $post, $shortcuts = false, $curent_month_days = false)
		{
			$dummy_date = '0000-00-00 00:00:00';

			if($ipid && $post['curent_month'] != '' && date('Y', strtotime($post['curent_month'])) != '1970')
			{
				$clear = Application_Form_Anlage14::clear_form_data($post['clientid'], $ipid, $post['curent_month']);
			}

			if($ipid && $shortcuts && $curent_month_days)
			{
				foreach($curent_month_days as $k_day => $v_day)
				{
					foreach($shortcuts as $k_short => $v_shortcut)
					{
						if($post[$v_shortcut][$v_day]['checked'] == '1' && $v_shortcut['sh_telefonat'])
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
						else if($v_shortcut['sh_telefonat'])
						{
							$checked = '0';
							$qty = $post[$v_shortcut][$v_day]['qty'];
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
					$collection = new Doctrine_Collection('Anlage14Control');
					$collection->fromArray($shortcuts_data);
					$collection->save();
				}


				//save non grid data
				if($ipid && $post)
				{
					//find if curent patient first day of month is allready saved
					$curent_month_date = date('Y-m-d H:i:s', strtotime($post['curent_month']));

					$existing_data = Doctrine::getTable('Anlage14')->findOneByIpidAndDate($ipid, $curent_month_date);

					if($existing_data)
					{
						if(strlen($post['raapv_sapv_date']) > '0')
						{
							$existing_data->raapv_sapv_date = date('Y-m-d H:i:s', strtotime($post['raapv_sapv_date']));
						}
						else
						{
							$existing_data->raapv_sapv_date = $dummy_date;
						}

						if(strlen($post['khws_sapv_date']) > '0')
						{
							$existing_data->khws_sapv_date = date('Y-m-d H:i:s', strtotime($post['khws_sapv_date']));
						}
						else
						{
							$existing_data->khws_sapv_date = $dummy_date;
						}

						if(strlen($post['stathospiz_sapv_date']) > '0')
						{
							$existing_data->stathospiz_sapv_date = date('Y-m-d H:i:s', strtotime($post['stathospiz_sapv_date']));
						}
						else
						{
							$existing_data->stathospiz_sapv_date = $dummy_date;
						}

						if(strlen($post['pwunsch_sapv_date']) > '0')
						{
							$existing_data->pwunsch_sapv_date = date('Y-m-d H:i:s', strtotime($post['pwunsch_sapv_date']));
						}
						else
						{
							$existing_data->pwunsch_sapv_date = $dummy_date;
						}

						if(strlen($post['dead_sapv_date']) > '0')
						{
							$existing_data->dead_sapv_date = date('Y-m-d H:i:s', strtotime($post['dead_sapv_date']));
						}
						else
						{
							$existing_data->dead_sapv_date = $dummy_date;
						}

						if(strlen($post['aapv_start']) > '0')
						{
							$existing_data->aapv_start = date('Y-m-d H:i:s', strtotime($post['aapv_start']));
						}
						else
						{
							$existing_data->aapv_start = $dummy_date;
						}

						if(strlen($post['aapv_end']) > '0')
						{
							$existing_data->aapv_end = date('Y-m-d H:i:s', strtotime($post['aapv_end']));
						}
						else
						{
							$existing_data->aapv_end = $dummy_date;
						}

						if(strlen($post['hospiz_start']) > '0')
						{
							$existing_data->hospiz_start = date('Y-m-d H:i:s', strtotime($post['hospiz_start']));
						}
						else
						{
							$existing_data->hospiz_start = $dummy_date;
						}

						if(strlen($post['hospiz_end']) > '0')
						{
							$existing_data->hospiz_end = date('Y-m-d H:i:s', strtotime($post['hospiz_end']));
						}
						else
						{
							$existing_data->hospiz_end = $dummy_date;
						}

						if(strlen($post['patient_wish_start']) > '0')
						{
							$existing_data->patient_wish_start = date('Y-m-d H:i:s', strtotime($post['patient_wish_start']));
						}
						else
						{
							$existing_data->patient_wish_start = $dummy_date;
						}

						if(strlen($post['patient_wish_end']) > '0')
						{
							$existing_data->patient_wish_end = date('Y-m-d H:i:s', strtotime($post['patient_wish_end']));
						}
						else
						{
							$existing_data->patient_wish_end = $dummy_date;
						}

						$existing_data->overall_non_hospiz_visits = $post['overall_non_hospiz_visits'];
						$existing_data->overall_phones = $post['overall_phones'];
						$existing_data->overall_beko = $post['overall_beko'];
						$existing_data->overall_folgeko = $post['overall_folgeko'];
						$existing_data->overall_hospiz_visits = $post['overall_hospiz_visits'];
						$existing_data->save();
						$ins_id = $existing_data->id;
					}
					else
					{
						$insert_new = new Anlage14();
						$insert_new->ipid = $ipid;
						$insert_new->date = $curent_month_date;
						if(strlen($post['raapv_sapv_date']) > '0')
						{
							$insert_new->raapv_sapv_date = date('Y-m-d H:i:s', strtotime($post['raapv_sapv_date']));
						}
						else
						{
							$insert_new->raapv_sapv_date = $dummy_date;
						}

						if(strlen($post['khws_sapv_date']) > '0')
						{
							$insert_new->khws_sapv_date = date('Y-m-d H:i:s', strtotime($post['khws_sapv_date']));
						}
						else
						{
							$insert_new->khws_sapv_date = $dummy_date;
						}

						if(strlen($post['stathospiz_sapv_date']) > '0')
						{
							$insert_new->stathospiz_sapv_date = date('Y-m-d H:i:s', strtotime($post['stathospiz_sapv_date']));
						}
						else
						{
							$insert_new->stathospiz_sapv_date = $dummy_date;
						}

						if(strlen($post['pwunsch_sapv_date']) > '0')
						{
							$insert_new->pwunsch_sapv_date = date('Y-m-d H:i:s', strtotime($post['pwunsch_sapv_date']));
						}
						else
						{
							$insert_new->pwunsch_sapv_date = $dummy_date;
						}

						if(strlen($post['dead_sapv_date']) > '0')
						{
							$insert_new->dead_sapv_date = date('Y-m-d H:i:s', strtotime($post['dead_sapv_date']));
						}
						else
						{
							$insert_new->dead_sapv_date = $dummy_date;
						}

						if(strlen($post['aapv_start']) > '0')
						{
							$insert_new->aapv_start = date('Y-m-d H:i:s', strtotime($post['aapv_start']));
						}
						else
						{
							$insert_new->aapv_start = $dummy_date;
						}

						if(strlen($post['aapv_end']) > '0')
						{
							$insert_new->aapv_end = date('Y-m-d H:i:s', strtotime($post['aapv_end']));
						}
						else
						{
							$insert_new->aapv_end = $dummy_date;
						}

						if(strlen($post['hospiz_start']) > '0')
						{
							$insert_new->hospiz_start = date('Y-m-d H:i:s', strtotime($post['hospiz_start']));
						}
						else
						{
							$insert_new->hospiz_start = $dummy_date;
						}

						if(strlen($post['hospiz_end']) > '0')
						{
							$insert_new->hospiz_end = date('Y-m-d H:i:s', strtotime($post['hospiz_end']));
						}
						else
						{
							$insert_new->hospiz_end = $dummy_date;
						}

						if(strlen($post['patient_wish_start']) > '0')
						{
							$insert_new->patient_wish_start = date('Y-m-d H:i:s', strtotime($post['patient_wish_start']));
						}
						else
						{
							$insert_new->patient_wish_start = $dummy_date;
						}

						if(strlen($post['patient_wish_end']) > '0')
						{
							$insert_new->patient_wish_end = date('Y-m-d H:i:s', strtotime($post['patient_wish_end']));
						}
						else
						{
							$insert_new->patient_wish_end = $dummy_date;
						}

						$insert_new->overall_non_hospiz_visits = $post['overall_non_hospiz_visits'];
						$insert_new->overall_phones = $post['overall_phones'];
						$insert_new->overall_beko = $post['overall_beko'];
						$insert_new->overall_folgeko = $post['overall_folgeko'];
						$insert_new->overall_hospiz_visits = $post['overall_hospiz_visits'];
						$insert_new->save();
						$ins_id = $insert_new->id;
					}

					if($ins_id && count($post['hospital_start']) > '0')
					{
						$clear_hospitals_data = $this->clear_hospitals_data($post, $ipid);
						
						foreach($post['hospital_start'] as $k_hosp => $v_hosp)
						{
							if(strlen(trim(rtrim($v_hosp))) >'0' && strlen(trim(rtrim($post['hospital_end'][$k_hosp]))) >'0')
							{
								$hospitals_data[] = array(
									'ipid' => $ipid,
									'formid' => $ins_id,
									'hospital_start' => date('Y-m-d H:i:s', strtotime($v_hosp)),
									'hospital_end' => date('Y-m-d H:i:s', strtotime($post['hospital_end'][$k_hosp])),
									'isdelete' => '0'
								);
							}
						}

						if($hospitals_data)
						{
							$collection = new Doctrine_Collection('Anlage14Hospitals');
							$collection->fromArray($hospitals_data);
							$collection->save();
						}
					}
				}
			}
		}

		public function clear_form_data($client, $ipid, $curent_month)
		{
			$q = Doctrine_Query::create()
				->update('Anlage14Control')
				->set('isdelete', "1")
				->where('client = "' . $client . '"')
				->andWhere('ipid LIKE "' . $ipid . '"')
				->andWhere('MONTH(date) = MONTH("' . $curent_month . '")')
				->andWhere('YEAR(date) = YEAR("' . $curent_month . '")');
			$q->execute();
		}

		public function clear_hospitals_data($post, $ipid = false)
		{
			if($ipid)
			{
				//find if curent patient first day of month is allready saved
				$curent_month_date = date('Y-m-d H:i:s', strtotime($post['curent_month']));
				$existing_data = Doctrine::getTable('Anlage14')->findOneByIpidAndDate($ipid, $curent_month_date);

				if($existing_data)
				{
					$q = Doctrine_Query::create()
						->update('Anlage14Hospitals')
						->set('isdelete', "1")
						->where('formid = "' . $existing_data->id . '"')
						->andWhere('ipid LIKE "' . $ipid . '"');
					$q->execute();
				}
			}
		}

		//add specific groups used in the shortcut "Palliativeinsatz psychosoz. Berufe nach § 11 Abs. 4 [sh_other_visits]" (from client details)
		public function insert_groups($client, $post)
		{
			Application_Form_Anlage14::clear_groups($client);

			foreach($post['client_groups'] as $k_cl_gr => $v_cl_gr)
			{
				$client_groups_arr[] = array(
					'clientid' => $client,
					'groupid' => $v_cl_gr,
					'isdelete' => "0"
				);
			}

			if($client_groups_arr)
			{
				$collection = new Doctrine_Collection('Anlage14ClientGroups');
				$collection->fromArray($client_groups_arr);
				$collection->save();
			}
		}

		public function clear_groups($client)
		{
			$q = Doctrine_Query::create()
				->update('Anlage14ClientGroups')
				->set('isdelete', "1")
				->where('clientid = "' . $client . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

	}

?>