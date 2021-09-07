<?php

	require_once("Pms/Form.php");

	class Application_Form_ClientPriceList extends Pms_Form {

		public function validate_period($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$validate = new Pms_Validation();

			if(!$validate->isdate($post['start']))
			{
				$this->error_message['date'] = $Tr->translate('invalid_start_date');
			}
			else if(!$validate->isdate($post['end']))
			{
				$this->error_message['date'] = $Tr->translate('invalid_end_date');
			}

			if($validate->isdate($post['start']) && $validate->isdate($post['end']))
			{
				$start = strtotime($post['start']);
				$end = strtotime($post['end']);

				if($start <= $end)
				{

					return true;
				}
				else
				{
					$this->error_message['date'] = $Tr->translate('invalid_date_period');
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function save_price_list($post_data)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$p_list = new PriceList();
			$p_list->clientid = $clientid;
			$p_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
			$p_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
			$p_list->save();

			return $p_list->id;
		}

		public function edit_list($post_data, $list)
		{
			$edit_list = Doctrine_Core::getTable('PriceList')->findOneById($list);
			$edit_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
			$edit_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
			$edit_list->save();

			return $edit_list->id;
		}

		public function delete_price_list($list)
		{
			$del_list = Doctrine_Core::getTable('PriceList')->findOneById($list);
			$del_list->isdelete = '1';
			$del_list->save();
		}

		public function save_prices_admission($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceAdmission');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceAdmission();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->client = Pms_CommonData::str2num($shortcut_data['client']);
				$prices->doctor = Pms_CommonData::str2num($shortcut_data['doctor']);
				$prices->nurse = Pms_CommonData::str2num($shortcut_data['nurse']);
				$prices->dta_id= $shortcut_data['dta_id'];
				$prices->dta_price= Pms_CommonData::str2num($shortcut_data['dta_price']);
				$prices->save();
			}
		}

		public function save_prices_daily($post_data, $list)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceDaily');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceDaily();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->client = Pms_CommonData::str2num($shortcut_data['client']);
				$prices->doctor = Pms_CommonData::str2num($shortcut_data['doctor']);
				$prices->duty_nurse = Pms_CommonData::str2num($shortcut_data['duty_nurse']);
				$prices->duty_doctor = Pms_CommonData::str2num($shortcut_data['duty_doctor']);
				$prices->dta_id= $shortcut_data['dta_id'];
				$prices->dta_price= Pms_CommonData::str2num($shortcut_data['dta_price']);
				$prices->save();
			}
		}

		public function save_prices_visits($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceVisits');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceVisits();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->t_start = $shortcut_data['t_start'];
				$prices->t_end = $shortcut_data['t_end'];
				$prices->dta_id= $shortcut_data['dta_id'];
				$prices->dta_price= Pms_CommonData::str2num($shortcut_data['dta_price']);
				$prices->save();
			}
		}

		public function save_prices_performance($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PricePerformance');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PricePerformance();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->dta_id = $shortcut_data['dta_id'];
				$prices->dta_price = Pms_CommonData::str2num($shortcut_data['dta_price']);
				$prices->save();
			}
		}

		public function save_prices_performancebylocation($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			$this->clear_entries($clientid, $list, 'PricePerformance');
		
			foreach($post_data as $k_location_type => $location_type_data)
			{
				foreach($location_type_data as $k_shortcut => $shortcut_data)
				{
					$prices = new PricePerformance();
					$prices->list = $list;
					$prices->clientid = $clientid;
					$prices->location_type = $k_location_type;
					$prices->shortcut = $k_shortcut;
					$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
					$prices->dta_id = $shortcut_data['dta_id'];
					$prices->dta_price = Pms_CommonData::str2num($shortcut_data['dta_price']);
					$prices->booking_account = $shortcut_data['booking_account'];	    // Maria:: Migration ISPC to CISPC 08.08.2020
					$prices->save();
				}
			}
		}
		
		public function save_prices_medipumps($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceMedipumps');

			foreach($post_data as $k_medipumpid => $medipump_data)
			{
				$prices = new PriceMedipumps();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->medipump = $k_medipumpid;

				$prices->price_first = Pms_CommonData::str2num($medipump_data['price_first']);
				$prices->first_start = $medipump_data['first_start'];
				$prices->first_end = $medipump_data['first_end'];

				$prices->price_follow = Pms_CommonData::str2num($medipump_data['price_follow']);
				$prices->follow_start = $medipump_data['follow_start'];
				$prices->follow_end = $medipump_data['follow_end'];

				$prices->save();
			}
		}
		public function save_prices_memberships($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceMemberships');

			foreach($post_data as $k_membership_id => $membership_data)
			{
				$prices = new PriceMemberships();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->membership = $k_membership_id;
				$prices->price = Pms_CommonData::str2num($membership_data['price']);
				$prices->save();
			}
		}

		public function save_prices_form_blocks($post_data, $list)
		{


			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceFormBlocks');

			foreach($post_data as $block => $options_data)
			{
				foreach($options_data as $option_id => $option_value)
				{
					$records[] = array(
						"list" => $list,
						"clientid" => $clientid,
						"option_id" => $option_id,
						"shortcut" => strtoupper($option_value['shortcut']),
						"price" => Pms_CommonData::str2num($option_value['price']),
						"block" => $block
					);
				}
			}
			$collection = new Doctrine_Collection('PriceFormBlocks');
			$collection->fromArray($records);
			$collection->save();
		}

		public function save_prices_bra_sapv($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBraSapv');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBraSapv();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}
		
		public function save_prices_bra_sapv_weg($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			$this->clear_entries($clientid, $list, 'PriceBraSapvWeg');
		
			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBraSapvWeg();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->doctor = Pms_CommonData::str2num($shortcut_data['doctor']);
				$prices->nurse = Pms_CommonData::str2num($shortcut_data['nurse']);
				$prices->save();
			}
		}

		
		public function save_prices_bre_sapv($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBreSapv');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBreSapv();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		public function save_prices_hessen($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceHessen');

			foreach($post_data as $k_list_type => $shortcut_data)
			{
				foreach($shortcut_data as $k_short_data => $v_short_data)
				{
					$price_data[] = array(
						'list' => $list,
						'clientid' => $clientid,
						'list_type' => $k_list_type,
						'shortcut' => $k_short_data,
						'price' => Pms_CommonData::str2num($v_short_data['price'])
					);
				}
			}

			$collection = new Doctrine_Collection('PriceHessen');
			$collection->fromArray($price_data);
			$collection->save();
		}

		public function save_prices_bayern_sapv($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBayernSapv');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBayernSapv();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->type = $shortcut_data['type'];
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		public function save_prices_sgbxi($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceSgbxi');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceSgbxi();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		public function save_prices_bayern($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBayern');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBayern();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		public function save_prices_rp($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceRpInvoice');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceRpInvoice();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->p_home = Pms_CommonData::str2num($shortcut_data['p_home']);
				$prices->p_nurse = Pms_CommonData::str2num($shortcut_data['p_nurse']);
				$prices->p_hospiz = Pms_CommonData::str2num($shortcut_data['p_hospiz']);
				$prices->save();
			}
		}

		public function save_prices_bre_dta($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBreDta');

			foreach($post_data as $k_shortcut => $shortcut_locations)
			{
				foreach($shortcut_locations as $k_location_id => $shortcut_data)
				{
					$prices = new PriceBreDta();
					$prices->list = $list;
					$prices->clientid = $clientid;
					$prices->dta_id = $shortcut_data['id'];
					$prices->dta_name = $shortcut_data['name'];
					$prices->dta_location = $k_location_id;
					$prices->shortcut = $k_shortcut;
					$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
					$prices->save();
				}
			}
		}

		public function save_prices_bre_hospiz($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBreHospiz');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceBreHospiz();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->dta_id = $shortcut_data['dta_id'];
				$prices->save();
			}
		}

		public function save_prices_sh($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceShInvoice');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceShInvoice();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		public function save_prices_hessen_dta($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceHessenDta');

			foreach($post_data as $k_list_type => $shortcut_data)
			{
				foreach($shortcut_data as $k_short_data => $v_short_data)
				{
					$price_data[] = array(
						'list' => $list,
						'clientid' => $clientid,
						'list_type' => $k_list_type,
						'shortcut' => $k_short_data,
						'price' => Pms_CommonData::str2num($v_short_data['price']),
						'dta_id' => $v_short_data['dta_id']
					);
				}
			}

			$collection = new Doctrine_Collection('PriceHessenDta');
			$collection->fromArray($price_data);
			$collection->save();
		}

		public function save_prices_sh_report($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceShReport');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceShReport();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->doctor = Pms_CommonData::str2num($shortcut_data['doctor']);
				$prices->nurse = Pms_CommonData::str2num($shortcut_data['nurse']);
				$prices->save();
			}
		}

		public function save_prices_sh_internal($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceShInternal');

			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceShInternal();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}

		

		public function save_prices_hospiz($post_data, $list)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceHospiz');
		
		    foreach($post_data as $k_shortcut => $shortcut_data)
		    {
		        $prices = new PriceHospiz();
		        $prices->list = $list;
		        $prices->clientid = $clientid;
		        $prices->shortcut = $k_shortcut;
		        $prices->price = Pms_CommonData::str2num($shortcut_data['price']);
		        $prices->save();
		    }
		}

		public function save_prices_care_level($post_data, $list)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceCareLevel');
		
		    foreach($post_data as $k_shortcut => $shortcut_data)
		    {
		        $prices = new PriceCareLevel();
		        $prices->list = $list;
		        $prices->clientid = $clientid;
		        $prices->shortcut = $k_shortcut;
		        $prices->description = $shortcut_data['description'];
		        $prices->price = Pms_CommonData::str2num($shortcut_data['price']);
		        $prices->save();
		    }
		}
		

		public function save_prices_xbdt_actions($post_data, $list)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceXbdtActions');
		
		    foreach($post_data as $action_id => $shortcut_data)
		    {
		        $prices = new PriceXbdtActions();
		        $prices->list = $list;
		        $prices->clientid = $clientid;
		        $prices->action_id = $action_id;
		        $prices->shortcut = $shortcut_data['shortcut'];
		        $prices->price = Pms_CommonData::str2num($shortcut_data['price']);
		        $prices->save();
		    }
		}
		
		
		public function save_prices_rp_dta($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			$this->clear_entries($clientid, $list, 'PriceRpDta');
			foreach($post_data as $shortcut => $location_data)
			{
				foreach($location_data as $location_id => $sapv_data)
				{
					foreach($sapv_data as $sapv_id => $posting_data)
					{
						$price_data[] = array(
								'list' => $list,
								'clientid' => $clientid,
								'shortcut' => $shortcut,
								'location_type' => $location_id,
								'sapv_type' => $sapv_id,
								'dta_id' => $posting_data['dta_id'],
								'dta_name' => $posting_data['dta_name'],
								'dta_price' => Pms_CommonData::str2num($posting_data['dta_price'])
						);
					}
				}
			}
		
			$collection = new Doctrine_Collection('PriceRpDta');
			$collection->fromArray($price_data);
			$collection->save();
		}
		
		
		public function save_prices_rlp($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceRlp');
			foreach($post_data as $shortcut => $location_data)
			{
				foreach($location_data as $location_id => $posting_data)
				{
					 
					$price_data[] = array(
							'list' => $list,
							'clientid' => $clientid,
							'shortcut' => $shortcut,
							'location_type' => $location_id,
							'dta_digits_3_4' => $posting_data['dta_digits_3_4'],
							'dta_digits_7_10' => $posting_data['dta_digits_7_10'],
							'price' => Pms_CommonData::str2num($posting_data['price']),
							'dta_price' => Pms_CommonData::str2num($posting_data['dta_price'])
					);
				}
			}
		
			$collection = new Doctrine_Collection('PriceRlp');
			$collection->fromArray($price_data);
			$collection->save();
		}
		
		
		/**
		 * @Ancuta 22.06.2018
		 * @param unknown $post_data
		 * @param unknown $list
		 */
		
		public function save_prices_bre_kinder($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$this->clear_entries($clientid, $list, 'PriceBreKinder');
			foreach($post_data as $shortcut => $location_data)
			{
				foreach($location_data as $location_id => $posting_data)
				{
					 
					$price_data[] = array(
							'list' => $list,
							'clientid' => $clientid,
							'shortcut' => $shortcut,
							'location_type' => $location_id,
							'dta_id' => $posting_data['dta_id'],
							'price' => Pms_CommonData::str2num($posting_data['price']),
							'dta_price' => Pms_CommonData::str2num($posting_data['dta_price'])
					);
				}
			}
		
			$collection = new Doctrine_Collection('PriceBreKinder');
			$collection->fromArray($price_data);
			$collection->save();
		}
		
		
		
		/**
		 * @Ancuta 11.10.2018
		 * @param unknown $post_data
		 * @param unknown $list
		 */

		public function save_prices_sh_shifts_internal($post_data, $list)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceShInternalUserShifts');
		
		    foreach($post_data as $k_shortcut => $shortcut_data_arr)
		    {
		        foreach($shortcut_data_arr as $price_group=>$shortcut_data)
		        {
		            $price_data[] = array(
		                'list' => $list,
		                'clientid' => $clientid,
		                'shortcut' => $k_shortcut,
		                'user_group' => $price_group,
		                'price' => Pms_CommonData::str2num($shortcut_data['price'])
		            );
		        }
		    }

		    if(!empty($price_data)){
    		    $collection = new Doctrine_Collection('PriceShInternalUserShifts');
    		    $collection->fromArray($price_data);
    		    $collection->save();
		    }
		}
		
		

		/**
		 * @Ancuta 06.12.2018
		 * @param unknown $post_data
		 * @param unknown $list
		 */
		
		public function save_prices_nordrhein($post_data, $list)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceNordrhein');
		    foreach($post_data as $shortcut => $location_data)
		    {
		        foreach($location_data as $location_id => $posting_data)
		        {
		
		            $price_data[] = array(
		                'list' => $list,
		                'clientid' => $clientid,
		                'shortcut' => $shortcut,
		                'location_type' => $location_id,
		                'dta_id' => $posting_data['dta_id'],
		                'price' => Pms_CommonData::str2num($posting_data['price']),
		                'dta_price' => Pms_CommonData::str2num($posting_data['dta_price'])
		            );
		        }
		    }
		
		    $collection = new Doctrine_Collection('PriceNordrhein');
		    $collection->fromArray($price_data);
		    $collection->save();
		}
		
		
		
		
		
		
		private function clear_entries($clientid = false, $list = false, $table = false)
		{
			if($clientid && $table && $list)
			{
				$Q = Doctrine_Query::create()
					->delete($table)
					->where("clientid = ?", $clientid)
					->andWhere("list = ?", $list);
				$Q->execute();
			}
		}
		
		
		/**
		 * @author Ancuta 01.10.2019  	    // Maria:: Migration ISPC to CISPC 08.08.2020
		 * copy of save_prices_nordrhein
		 * @param unknown $post_data
		 * @param unknown $list
		 */
		public function save_prices_demstepcare($post_data, $list)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $this->clear_entries($clientid, $list, 'PriceDemstepcare');
		    foreach($post_data as $shortcut => $location_data)
		    {
		        foreach($location_data as $location_id => $posting_data)
		        {
		
		            $price_data[] = array(
		                'list' => $list,
		                'clientid' => $clientid,
		                'shortcut' => $shortcut,
		                'dta_id' => $posting_data['dta_id'],
		                'price' => Pms_CommonData::str2num($posting_data['price'])
		                
		            );
		        }
		    }
		
		    $collection = new Doctrine_Collection('PriceDemstepcare');
		    $collection->fromArray($price_data);
		    $collection->save();
		}
		
		public function save_prices_nr_anlage10($post_data, $list)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			$this->clear_entries($clientid, $list, 'PriceNordrheinAnlage10');
		
			foreach($post_data as $k_shortcut => $shortcut_data)
			{
				$prices = new PriceNordrheinAnlage10();
				$prices->list = $list;
				$prices->clientid = $clientid;
				$prices->shortcut = $k_shortcut;
				$prices->price = Pms_CommonData::str2num($shortcut_data['price']);
				$prices->save();
			}
		}
		
	}

?>