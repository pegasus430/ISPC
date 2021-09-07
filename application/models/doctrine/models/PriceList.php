<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceList', 'SYSDAT');

	class PriceList extends BasePriceList {

		public function get_lists($clientid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceList')
				->where("clientid='" . $clientid . "'")
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');
			$res = $query->fetchArray();

			foreach($res as $k_res => $v_res)
			{
				$return[$v_res['id']] = $v_res;
			}

			return $return;
		}

		public function get_last_list($clientid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceList')
				->where("clientid='" . $clientid . "'")
				->orderBy('create_date DESC')
				->andwhere('isdelete ="0"')
				->limit('1');
			$res = $query->fetchArray();


			return $res;
		}

		public function check_client_list($clientid, $listid)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceList')
				->where("clientid='" . $clientid . "'")
				->andWhere('id="' . $listid . '"')
				->andwhere('isdelete ="0"')
				->orderBy('id ASC');
			$res = $query->fetchArray();

			if($res)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		//ISPC-2609 + ISPC-2000  Ancuta 26.09.2020- added client param
		public function get_period_price_list($start, $end,$clientid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($clientid === false){
    			$clientid = $logininfo->clientid;
			}
			
			$p_admission = new PriceAdmission();
			$p_daily = new PriceDaily();
			$p_visits = new PriceVisits();
			$p_performance = new PricePerformance();
			$p_bra_sapv = new PriceBraSapv();
			$p_bra_sapv_weg = new PriceBraSapvWeg();
			$p_bre_sapv = new PriceBreSapv();
			$p_bre_hospiz = new PriceBreHospiz();
			$p_bre_dta = new PriceBreDta();
			$p_bayern_sapv = new PriceBayernSapv();
			$p_bayern = new PriceBayern();
			$p_rp = new PriceRpInvoice();
			$p_sh = new PriceShInvoice();
			$pm = new PatientMaster();
			$p_he_dta = new PriceHessenDta();
			$p_sh_in = new PriceShInternal();
			
			$p_sh_shifts_in = new PriceShInternalUserShifts();
			
			$p_hospiz = new PriceHospiz();
			$p_care_level = new PriceCareLevel();

			$sh_shifts_user_groups = $p_sh_shifts_in->internal_price_user_groups();
			
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			$price_list_admission = $p_admission->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['admission']);
			$price_list_daily = $p_daily->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['daily']);
			$price_list_visits = $p_visits->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['visits']);
//			$price_list_performance = $p_performance->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['performance']);
 			$price_list_performance = $p_performance->get_multiple_list_pricebylocation($returned['list_ids'], $clientid, $shortcuts['performance']);
			$price_list_bra_sapv = $p_bra_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bra_sapv']);
			$price_list_bra_sapv_weg = $p_bra_sapv_weg->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bra_sapv_weg']);
			$price_list_bre_sapv = $p_bre_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_sapv']);
			$price_list_bre_hospiz = $p_bre_hospiz->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_hospiz']);

			//be carefully with bre_dta (it returns 3 level array list-shortcut-dta_location)
			$price_list_bre_dta = $p_bre_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_dta']);
			$price_list_he_dta = $p_he_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['hessen_dta']);

			$price_list_bayern_sapv = $p_bayern_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern_sapv']);
			$price_list_bayern = $p_bayern->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern']);
			$price_list_rp = $p_rp->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['rp']);
			$price_list_sh = $p_sh->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['shanlage14']);
			$price_list_sh_in = $p_sh_in->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['sh_internal']);
			
			$price_list_hospiz = $p_hospiz->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['hospiz']);
			$price_list_care_level = $p_care_level->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['care_level']);

			//ISPC-2257
			$price_list_sh_shifts_in = $p_sh_shifts_in->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['sh_shifts_internal']);
			
			

			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] = array_merge(
				    $price_list_admission[$v_list_id], 
				    $price_list_daily[$v_list_id], 
				    $price_list_visits[$v_list_id], 
				    $price_list_performance[$v_list_id], 
				    $price_list_bra_sapv[$v_list_id], 
				    $price_list_bra_sapv_weg[$v_list_id], 
				    $price_list_bre_sapv[$v_list_id], 
				    $price_list_bre_hospiz[$v_list_id], 
				    $price_list_bre_dta[$v_list_id], 
				    $price_list_bayern_sapv[$v_list_id], 
				    $price_list_bayern[$v_list_id], 
				    $price_list_rp[$v_list_id], 
				    $price_list_sh[$v_list_id], 
				    $price_list_he_dta[$v_list_id], 
				    $price_list_sh_in[$v_list_id], 
				    $price_list_hospiz[$v_list_id], 
				    $price_list_care_level[$v_list_id],
				    $price_list_sh_shifts_in[$v_list_id]
				    );
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day][0] = $master_shortcuts[$k_list];
					}
				}

				if(empty($master_result[$v_day]))
				{
					//empty as in no list for this day!
					foreach($shortcuts['admission'] as $k_short_a => $v_short_a)
					{
						$res_prices[$v_short_a]['shortcut'] = $v_short_a;
						$res_prices[$v_short_a]['list'] = '0';
						$res_prices[$v_short_a]['price'] = '0.00';
						$res_prices[$v_short_a]['client'] = '0.00';
						$res_prices[$v_short_a]['doctor'] = '0.00';
						$res_prices[$v_short_a]['nurse'] = '0.00';
					}

					foreach($shortcuts['daily'] as $k_short => $v_short)
					{
						$res_prices[$v_short]['shortcut'] = $v_short;
						$res_prices[$v_short]['list'] = '0';
						$res_prices[$v_short]['price'] = '0.00';
						$res_prices[$v_short]['client'] = '0.00';
						$res_prices[$v_short]['nurse'] = '0.00';
						$res_prices[$v_short]['doctor'] = '0.00';
						$res_prices[$v_short]['duty_nurse'] = '0.00';
						$res_prices[$v_short]['duty_doctor'] = '0.00';
					}

					foreach($shortcuts['visits'] as $k_short_v => $v_short_v)
					{
						$res_prices[$v_short_v]['shortcut'] = $v_short_v;
						$res_prices[$v_short_v]['list'] = '0';
						$res_prices[$v_short_v]['price'] = '0.00';
						$res_prices[$v_short_v]['t_start'] = '0';
						$res_prices[$v_short_v]['t_end'] = '0';
					}

					foreach($shortcuts['bra_sapv'] as $k_short_v => $v_short_bra)
					{
						$res_prices[$v_short_bra]['shortcut'] = $v_short_bra;
						$res_prices[$v_short_bra]['list'] = '0';
						$res_prices[$v_short_bra]['price'] = $default_price_list['bra_sapv'][$v_short_bra];
					}

					foreach($shortcuts['bra_sapv_weg'] as $k_short_v => $v_short_bra_weg)
					{
						$res_prices[$v_short_bra_weg]['shortcut'] = $v_short_bra_weg;
						$res_prices[$v_short_bra_weg]['list'] = '0';
						$res_prices[$v_short_bra_weg]['price'] = $default_price_list['bra_sapv_weg'][$v_short_bra_weg];
					}

					foreach($shortcuts['bre_sapv'] as $k_short_v => $v_short_bre)
					{
						$res_prices[$v_short_bre]['shortcut'] = $v_short_bre;
						$res_prices[$v_short_bre]['list'] = '0';
						$res_prices[$v_short_bre]['price'] = $default_price_list['bre_sapv'][$v_short_bre];
					}
					
					foreach($shortcuts['bre_hospiz'] as $k_short_v => $v_short_hosp)
					{
						$res_prices[$v_short_hosp]['shortcut'] = $v_short_hosp;
						$res_prices[$v_short_hosp]['list'] = '0';
						$res_prices[$v_short_hosp]['price'] = $default_price_list['bre_hospiz'][$v_short_hosp];
					}

					foreach($shortcuts['bayern_sapv'] as $k_short_v => $v_short_bay)
					{
						$res_prices[$v_short_bay]['shortcut'] = $v_short_bay;
						$res_prices[$v_short_bay]['list'] = '0';
						$res_prices[$v_short_bay]['price'] = $default_price_list['bayern_sapv'][$v_short_bay];
					}

					foreach($shortcuts['rp'] as $k_short_v => $v_short_bay)
					{
						$res_prices[$v_short_bay] = $default_price_list['rp'][$v_short_bay];

						$res_prices[$v_short_bay]['shortcut'] = $v_short_bay;
						$res_prices[$v_short_bay]['list'] = '0';
					}
					
					foreach($shortcuts['shanlage14'] as $k_short_v => $v_short_sh)
					{
						$res_prices[$v_short_sh] = $default_price_list['sh'][$v_short_sh];

						$res_prices[$v_short_sh]['shortcut'] = $v_short_sh;
						$res_prices[$v_short_sh]['list'] = '0';
					}
					
					foreach($shortcuts['sh_internal'] as $k_short_v => $v_short_sh)
					{
						$res_prices[$v_short_sh] = $default_price_list['sh_internal'][$v_short_sh];

						$res_prices[$v_short_sh]['shortcut'] = $v_short_sh;
						$res_prices[$v_short_sh]['list'] = '0';
					}
					
					foreach($shortcuts['hospiz'] as $k_short_hospiz => $v_short_hospiz)
					{
						$res_prices[$v_short_hospiz] = $default_price_list['hospiz'][$v_short_hospiz];

						$res_prices[$v_short_hospiz]['shortcut'] = $v_short_hospiz;
						$res_prices[$v_short_hospiz]['list'] = '0';
					}
					
					foreach($shortcuts['care_level'] as $k_short_care_level => $v_short_care_level)
					{
						$res_prices[$v_short_care_level] = $default_price_list['care_level'][$v_short_care_level];

						$res_prices[$v_short_care_level]['shortcut'] = $v_short_care_level;
						$res_prices[$v_short_care_level]['list'] = '0';
					}
					
					foreach($shortcuts['sh_shifts_internal'] as $k_short_v => $v_short_sh)
					{
					    foreach($sh_shifts_user_groups as $k=>$pgr){
					        
                            $res_prices[$v_short_sh][$pgr] = $default_price_list['sh_shifts_internal'][$v_short_sh][$pgr];
	       					$res_prices[$v_short_sh][$pgr]['shortcut'] = $v_short_sh;
    						$res_prices[$v_short_sh][$pgr]['list'] = '0';
					    }
					}
					
					
					$master_result[$v_day][0] = $res_prices;
				}
			}

//			print_r($period_days_array);
//			print_r($price_list_admission);
//			print_r($returned);
//			print_r($master_shortcuts);
//			exit;
// 	 		print_r($master_result);
// 	 		exit;
			return $master_result;
		}
		
		
		
        /**
         * @auth Ancuta
         * Created on 18.03.2019
         * for  TODO-2194 ::  the issue was because all the shortcuts - were grouped in a single array,  and shortcut KO from bayern_sapv  was overwritten by sh_internal_shifts
         * ispc-2609 + ispc-2000 Ancuta 26.09.2020 Added cleint param
         * @param unknown $start
         * @param unknown $end
         * @return string
         */
		public function get_period_price_list_day2type($start, $end,$clientid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(!$clientid){
    			$clientid = $logininfo->clientid;
			}
			$p_admission = new PriceAdmission();
			$p_daily = new PriceDaily();
			$p_visits = new PriceVisits();
			$p_performance = new PricePerformance();
			$p_bra_sapv = new PriceBraSapv();
			$p_bra_sapv_weg = new PriceBraSapvWeg();
			$p_bre_sapv = new PriceBreSapv();
			$p_bre_hospiz = new PriceBreHospiz();
			$p_bre_dta = new PriceBreDta();
			$p_bayern_sapv = new PriceBayernSapv();
			$p_bayern = new PriceBayern();
			$p_rp = new PriceRpInvoice();
			$p_sh = new PriceShInvoice();
			$pm = new PatientMaster();
			$p_he_dta = new PriceHessenDta();
			$p_sh_in = new PriceShInternal();
			
			$p_sh_shifts_in = new PriceShInternalUserShifts();
			
			$p_hospiz = new PriceHospiz();
			$p_care_level = new PriceCareLevel();

			$sh_shifts_user_groups = $p_sh_shifts_in->internal_price_user_groups();
			
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			$price_list = array();
			$price_list['admission']     = $p_admission->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['admission']);
			$price_list['daily']         = $p_daily->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['daily']);
			$price_list['visits']        = $p_visits->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['visits']);
 			$price_list['performance']   = $p_performance->get_multiple_list_pricebylocation($returned['list_ids'], $clientid, $shortcuts['performance']);
			$price_list['bra_sapv']      = $p_bra_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bra_sapv']);
			$price_list['bra_sapv_weg']  = $p_bra_sapv_weg->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bra_sapv_weg']);
			$price_list['bre_sapv']      = $p_bre_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_sapv']);
			$price_list['bre_hospiz']    = $p_bre_hospiz->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_hospiz']);
			$price_list['bre_dta']       = $p_bre_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_dta']);//be carefully with bre_dta (it returns 3 level array list-shortcut-dta_location)
			$price_list['hessen_dta']    = $p_he_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['hessen_dta']);
			$price_list['bayern_sapv']   = $p_bayern_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern_sapv']);
			$price_list['bayern']        = $p_bayern->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern']);
			$price_list['rp']            = $p_rp->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['rp']);
			$price_list['shanlage14']    = $p_sh->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['shanlage14']);
			$price_list['sh_internal']   = $p_sh_in->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['sh_internal']);
			$price_list['hospiz']        = $p_hospiz->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['hospiz']);
			$price_list['care_level']    = $p_care_level->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['care_level']);
			$price_list['sh_shifts_internal']  = $p_sh_shifts_in->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['sh_shifts_internal']); //ISPC-2257
			
            foreach($price_list as $sh_ident => $list_data)
            {
                foreach($list_data as $list_id=>$sh_values)
                {
                    $master_shortcuts[$list_id][$sh_ident] = $sh_values; 
                }
            }
 
			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day][0] = $master_shortcuts[$k_list];
					}
				}
				
				
				if(empty($master_result[$v_day]))
				{   
				    //empty as in no list for this day!
				    foreach($shortcuts as $shortcut_group => $shortcuts_array ){
				        
				        foreach($shortcuts_array as $k_short => $v_short){
				            
				            $res_prices[$shortcut_group][$v_short]['shortcut'] = $v_short;
				            $res_prices[$shortcut_group][$v_short]['list'] = '0';
				            $res_prices[$shortcut_group][$v_short]['price'] = '0.00';
				            
				            
				            if($shortcut_group == 'admission'){
                                $res_prices[$shortcut_group][$v_short]['client'] = '0.00';
                                $res_prices[$shortcut_group][$v_short]['doctor'] = '0.00';
    				            $res_prices[$shortcut_group][$v_short]['nurse'] = '0.00';
				            }
				            
				            else if($shortcut_group == 'daily'){
        						$res_prices[$shortcut_group][$v_short]['client'] = '0.00';
        						$res_prices[$shortcut_group][$v_short]['nurse'] = '0.00';
        						$res_prices[$shortcut_group][$v_short]['doctor'] = '0.00';
        						$res_prices[$shortcut_group][$v_short]['duty_nurse'] = '0.00';
        						$res_prices[$shortcut_group][$v_short]['duty_doctor'] = '0.00';
				            }
				            elseif($shortcut_group == 'visits'){
				                $res_prices[$shortcut_group][$v_short]['t_start'] = '0';
				                $res_prices[$shortcut_group][$v_short]['t_end'] = '0';
				            }
				            
				            elseif(in_array($shortcut_group, array('bra_sapv','bra_sapv_weg','bre_sapv','bre_hospiz','bayern_sapv','rp','shanlage14','sh_internal','hospiz','care_level')) ) {
				                $res_prices[$shortcut_group][$v_short]['price'] = $default_price_list[$shortcut_group][$v_short];
				            }
				            
                            elseif($shortcut_group == 'sh_shifts_internal'){
                                
                                foreach($sh_shifts_user_groups as $k=>$pgr)
                                {
                                    $res_prices[$shortcut_group][$v_short][$pgr] = $default_price_list['sh_shifts_internal'][$v_short][$pgr];
                                    $res_prices[$shortcut_group][$v_short][$pgr]['shortcut'] = $v_short;
                                    $res_prices[$shortcut_group][$v_short][$pgr]['list'] = '0';
                                }
                            }
				        }
				    }
					$master_result[$v_day][0] = $res_prices;
				}
			}
			
//			print_r($period_days_array);
//			print_r($price_list_admission);
//			print_r($returned);
//			print_r($master_shortcuts);
//			exit;
// 	 		print_r($master_result);
// 	 		exit;
			return $master_result;
		}
		//ISPC-2609 + ISPC-2000 Ancuta 24.09.2020 - add client param +  $specific was missing- not used !?!?
		public function get_period_price_list_specific($start, $end , $specific = false, $clientid = false) 
		{
		    if( ! $clientid){
    			$logininfo = new Zend_Session_Namespace('Login_Info');
    			$clientid = $logininfo->clientid;
		    }
		    
		    
			$pm = new PatientMaster();

			$p_performance = new PricePerformance();

			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

 			$price_list_performance = $p_performance->get_multiple_list_pricebylocation($returned['list_ids'], $clientid, $shortcuts['performance']);


			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] =  $price_list_performance[$v_list_id];
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day][0] = $master_shortcuts[$k_list];
					}
				}
			}

			return $master_result;
		}
		
		public function period_price_list_specific_rlp($start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$pm = new PatientMaster();

			$p_rlp = new PriceRlp();
			$p_rp = new RlpInvoices();
			$default_price_list = $p_rp->rlp_products_default_prices();
				
			
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid= ?', $clientid)
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			
			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

 			$price_list = $p_rlp->get_multiple_list_price($returned['list_ids'], $clientid,$default_price_list);
 			
			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] =  $price_list[$v_list_id];
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day] = $master_shortcuts[$k_list];
					}
				}
			}

			return $master_result;
		}
		
		
		
		public function period_price_list_specific_brekinder($start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$pm = new PatientMaster();

			$p_brekinder = new PriceBreKinder();
			$p_is = new InvoiceSystem();
			$default_price_list = $p_is->invoice_products_default_prices("bre_kinder_invoice");
				
			
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid= ?', $clientid)
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			
			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

 			$price_list = $p_brekinder->get_multiple_list_price($returned['list_ids'], $clientid,$default_price_list);
 			
			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] =  $price_list[$v_list_id];
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day] = $master_shortcuts[$k_list];
					}
				}
			}

			return $master_result;
		}
		
		/**
		 * Ancuta 07.12.2018
		 * ISPC-2286
		 * @param unknown $start
		 * @param unknown $end
		 * @return unknown
		 */
		public function period_price_list_specific_nordrhein($start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$pm = new PatientMaster();

			$p_nordrhein = new PriceNordrhein();
			$p_is = new InvoiceSystem();
			$default_price_list = $p_is->invoice_products_default_prices("nr_invoice");
				
			
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid= ?', $clientid)
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			
			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

 			$price_list = $p_nordrhein->get_multiple_list_price($returned['list_ids'], $clientid,$default_price_list);
 			
			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] =  $price_list[$v_list_id];
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day] = $master_shortcuts[$k_list];
					}
				}
			}

			return $master_result;
		}
		
		
		/* SPECIAL PRICE LIST FOR DTA*/
		
		
		public function get_period_dta_price_list($start, $end)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$p_bre_dta = new PriceBreDta();
			$p_rp_dta = new PriceRpDta();
			$pm = new PatientMaster();
			$p_he_dta = new PriceHessenDta();
			

			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			$period_days_array = $pm->getDaysInBetween($start_date, $end_date);

			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
 
			//be carefully with bre_dta (it returns 3 level array list-shortcut-dta_location)
			$price_list_bre_dta = $p_bre_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_dta']);
			$price_list_he_dta = $p_he_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['hessen_dta']);
 
			$price_list_rp_dta = $p_rp_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['rp_dta']);
 

			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] = array_merge( $price_list_bre_dta[$v_list_id], $price_list_rp_dta[$v_list_id],   $price_list_he_dta[$v_list_id] );
			}

			foreach($period_days_array as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day][0] = $master_shortcuts[$k_list];
					}
				}

				if(empty($master_result[$v_day]))
				{
					//empty as in no list for this day!
					foreach($shortcuts['rp_dta'] as $k_short_v => $v_short_bay)
					{
						$res_prices[$v_short_bay] = $default_price_list['rp_dta'][$v_short_bay];

						$res_prices[$v_short_bay]['shortcut'] = $v_short_bay;
						$res_prices[$v_short_bay]['list'] = '0';
					}
					$master_result[$v_day][0] = $res_prices;
				}
			}

//			print_r($period_days_array);
//			print_r($price_list_admission);
//			print_r($returned);
//			print_r($master_shortcuts);
//			exit;
// 	 		print_r($master_result);
// 	 		exit;
			return $master_result;
		}

		//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020 - added cleint id param
		public function get_client_list_period($start, $end,$clientid = false)
		{
		    if(!$clientid){
	       		$logininfo = new Zend_Session_Namespace('Login_Info');
    			$clientid = $logininfo->clientid;
		    }

			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));

			$query = Doctrine_Query::create()
				->select('*')
				->from('PriceList')
				->where('isdelete = "0"')
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('DATE("' . $start_date . '") <= `end` ')
				->andWhere('DATE("' . $end_date . '") >= `start` ')
				->orderBy('start ASC');
			$res = $query->fetchArray();

			if($res)
			{
				return $res;
			}
			else
			{
				return false;
			}
		}
		public function get_client_list_patients_periods($patients_periods = false)
		{
			if($patients_periods)
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				$sql = array();
				foreach($patients_periods as $k_ipid => $v_patient_data)
				{
					$sql[] = " (DATE('".date('Y-m-d', strtotime($v_patient_data['start']))."') <=  DATE(`end`) AND DATE('".date('Y-m-d', strtotime($v_patient_data['start']))."') >= DATE(`start`)) ";
				}
				
				if($sql)
				{
					$sql_where =implode("OR", $sql);
				}
				
				$query = Doctrine_Query::create()
					->select('*')
					->from('PriceList')
					->where('isdelete = "0"')
					->andWhere('clientid="' . $clientid . '"')
					->andWhere($sql_where)
					->orderBy('start ASC');
				$res = $query->fetchArray();
				
				if($res)
				{
					return $res;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_report_period_price_list($report_period, $finalPeriodDays)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$p_admission = new PriceAdmission();
			$p_daily = new PriceDaily();
			$p_visits = new PriceVisits();
			$p_performance = new PricePerformance();
			$p_bra_sapv = new PriceBraSapv();
			$p_bre_sapv = new PriceBreSapv();
			$p_bre_dta = new PriceBreDta();
			$p_bayern_sapv = new PriceBayernSapv();
			$p_bayern = new PriceBayern();
			$pm = new PatientMaster();
			$p_sh_report = new PriceShReport();

			$x = 0;
			$dates_sql = "";
			foreach($report_period as $keys => $intvals)
			{
				$dates_sql .= '((start <= "' . date('Y-m-d H:i:s', strtotime($intvals['start'])) . '" ) AND (end   >= "' . date('Y-m-d H:i:s', strtotime($intvals['start'])) . '" )	) OR ((start >= "' . date('Y-m-d H:i:s', strtotime($intvals['start'])) . '") AND (start <  "' . date('Y-m-d H:i:s', strtotime($intvals['end'])) . '"))  OR ';
			}

			$query = Doctrine_Query::create();
			$query->select('*');
			$query->from('PriceList');
			$query->where('isdelete = "0"');
			$query->andWhere('clientid="' . $clientid . '"');
			$query->andWhere('' . substr($dates_sql, 0, -4) . '');
			$query->orderBy('start ASC');
			$res = $query->fetchArray();


			foreach($res as $k_res_period => $v_res_period)
			{
				$returned['list_ids'][] = $v_res_period['id'];
				$returned['list_details'][$v_res_period['id']] = $v_res_period;
			}

			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

			$price_list_admission = $p_admission->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['admission']);
			$price_list_daily = $p_daily->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['daily']);
			$price_list_visits = $p_visits->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['visits']);
			$price_list_performance = $p_performance->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['performance']);
			$price_list_bra_sapv = $p_bra_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bra_sapv']);
			$price_list_bre_sapv = $p_bre_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_sapv']);
			$price_list_bre_dta = $p_bre_dta->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bre_dta']);
			$price_list_bayern_sapv = $p_bayern_sapv->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern_sapv']);
			$price_list_bayern = $p_bayern->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['bayern']);
			$price_list_sh_report = $p_sh_report->get_multiple_list_price($returned['list_ids'], $clientid, $shortcuts['shanlage14_report']);

			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
			{
				$master_shortcuts[$v_list_id] = array_merge($price_list_admission[$v_list_id], $price_list_daily[$v_list_id], $price_list_visits[$v_list_id], $price_list_performance[$v_list_id], $price_list_bra_sapv[$v_list_id], $price_list_bre_sapv[$v_list_id], $price_list_bre_dta[$v_list_id], $price_list_bayern_sapv[$v_list_id], $price_list_bayern[$v_list_id], $price_list_sh_report[$v_list_id]);
			}

			foreach($finalPeriodDays as $k_day => $v_day)
			{
				foreach($returned['list_details'] as $k_list => $v_list)
				{
					$r1start = strtotime($v_day);
					$r1end = strtotime($v_day);
					$r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
					$r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));

					if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
					{
						$master_result[$v_day][0] = $master_shortcuts[$k_list];
					}
				}

				if(empty($master_result[$v_day]))
				{
					//empty as in no list for this day!
					foreach($shortcuts['admission'] as $k_short_a => $v_short_a)
					{
						$res_prices[$v_short_a]['shortcut'] = $v_short_a;
						$res_prices[$v_short_a]['list'] = '0';
						$res_prices[$v_short_a]['price'] = '0.00';
						$res_prices[$v_short_a]['client'] = '0.00';
						$res_prices[$v_short_a]['doctor'] = '0.00';
						$res_prices[$v_short_a]['nurse'] = '0.00';
					}

					foreach($shortcuts['daily'] as $k_short => $v_short)
					{
						$res_prices[$v_short]['shortcut'] = $v_short;
						$res_prices[$v_short]['list'] = '0';
						$res_prices[$v_short]['price'] = '0.00';
						$res_prices[$v_short]['client'] = '0.00';
						$res_prices[$v_short]['nurse'] = '0.00';
						$res_prices[$v_short]['doctor'] = '0.00';
						$res_prices[$v_short]['duty_nurse'] = '0.00';
						$res_prices[$v_short]['duty_doctor'] = '0.00';
					}

					foreach($shortcuts['visits'] as $k_short_v => $v_short_v)
					{
						$res_prices[$v_short_v]['shortcut'] = $v_short_v;
						$res_prices[$v_short_v]['list'] = '0';
						$res_prices[$v_short_v]['price'] = '0.00';
						$res_prices[$v_short_v]['t_start'] = '0';
						$res_prices[$v_short_v]['t_end'] = '0';
					}


					foreach($shortcuts['bra_sapv'] as $k_short_v => $v_short_bra)
					{
						$res_prices[$v_short_bra]['shortcut'] = $v_short_bra;
						$res_prices[$v_short_bra]['list'] = '0';
						$res_prices[$v_short_bra]['price'] = $default_price_list['bra_sapv'][$v_short_bra];
					}

					foreach($shortcuts['bre_sapv'] as $k_short_v => $v_short_bre)
					{
						$res_prices[$v_short_bre]['shortcut'] = $v_short_bre;
						$res_prices[$v_short_bre]['list'] = '0';
						$res_prices[$v_short_bre]['price'] = $default_price_list['bre_sapv'][$v_short_bre];
					}

					foreach($shortcuts['bayern_sapv'] as $k_short_v => $v_short_bay)
					{
						$res_prices[$v_short_bay]['shortcut'] = $v_short_bay;
						$res_prices[$v_short_bay]['list'] = '0';
						$res_prices[$v_short_bay]['price'] = $default_price_list['bayern_sapv'][$v_short_bay];
					}
					foreach($shortcuts['sh_report'] as $k_short_v => $v_short_bay)
					{
						$res_prices[$v_short_bay]['shortcut'] = $v_short_bay;
						$res_prices[$v_short_bay]['list'] = '0';
						$res_prices[$v_short_bay]['price'] = $default_price_list['sh_report'][$v_short_bay];
					}

					$master_result[$v_day][0] = $res_prices;
				}
			}

			return $master_result;
		}

		
		
		/**
		 * Auth Ancuta
		 * ISCP-2461
		 * @param unknown $start
		 * @param unknown $end
		 * @return unknown
		 */
		public function period_price_list_specific_demstepcare($start, $end)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $pm = new PatientMaster();
		
		    $price_obj = new PriceDemstepcare();
		    $p_is = new InvoiceSystem();
		    $default_price_list = $p_is->invoice_products_default_prices("demstepcare_invoice");
		
		    	
		    $start_date = date('Y-m-d', strtotime($start));
		    $end_date = date('Y-m-d', strtotime($end));
		
		    $query = Doctrine_Query::create()
		    ->select('*')
		    ->from('PriceList')
		    ->where('isdelete = "0"')
		    ->andWhere('clientid= ?', $clientid)
		    ->andWhere('DATE("' . $start_date . '") <= `end` ')
		    ->andWhere('DATE("' . $end_date . '") >= `start` ')
		    ->orderBy('start ASC');
		    $res = $query->fetchArray();
		
		    	
		    $period_days_array = $pm->getDaysInBetween($start_date, $end_date);
		
		    foreach($res as $k_res_period => $v_res_period)
		    {
		        $returned['list_ids'][] = $v_res_period['id'];
		        $returned['list_details'][$v_res_period['id']] = $v_res_period;
		    }
		
		 			$price_list = $price_obj->get_multiple_list_price($returned['list_ids'], $clientid,$default_price_list);
		
		 			foreach($returned['list_ids'] as $k_list_id => $v_list_id)
		 			{
		 			    $master_shortcuts[$v_list_id] =  $price_list[$v_list_id];
		 			}
		
		 			foreach($period_days_array as $k_day => $v_day)
		 			{
		 			    foreach($returned['list_details'] as $k_list => $v_list)
		 			    {
		 			        $r1start = strtotime($v_day);
		 			        $r1end = strtotime($v_day);
		 			        $r2start = strtotime(date('Y-m-d', strtotime($v_list['start'])));
		 			        $r2end = strtotime(date('Y-m-d', strtotime($v_list['end'])));
		
		 			        if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
		 			        {
		 			            $master_result[$v_day] = $master_shortcuts[$k_list];
		 			        }
		 			    }
		 			}
		
		 			return $master_result;
		}		
		
		
	}

?>