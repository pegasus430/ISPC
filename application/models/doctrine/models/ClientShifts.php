<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientShifts', 'SYSDAT');

	class ClientShifts extends BaseClientShifts {

		public function get_client_shifts($client)
		{
		    //ISPC-2612 Ancuta 30.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('ClientShifts',$client);
		    
		    
			$sel = Doctrine_Query::create()
				->select('*')
				->from('ClientShifts')
				->Where('client = "' . $client . '"')
			    ->andwhere("isdelete = 0");
			    if($client_is_follower){//ISPC-2612 Ancuta 30.06.2020
			        $sel->andWhere('connection_id is NOT null');
			        $sel->andWhere('master_id is NOT null');
			    }
			$sel_res = $sel->fetchArray();

			if($sel_res)
			{
				$sel_res_arr[0]['id'] = '0';
				$sel_res_arr[0]['name'] = "Dienstplan";
				$sel_res_arr[0]['color'] = '98FB98';
				foreach($sel_res as $k_sel => $v_sel)
				{
					$sel_res_arr[$v_sel['id']] = $v_sel;
				}

				return $sel_res_arr;
			}
			else
			{
				//return hardcoded old event
				$sel_res_arr[0]['id'] = '0';
				$sel_res_arr[0]['name'] = "Dienstplan";
				$sel_res_arr[0]['color'] = '98FB98';

				return $sel_res_arr;
			}
		}

		public function get_shift_details($sid, $client)
		{
		    //ISPC-2612 Ancuta 30.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('ClientShifts',$client);
		    
		    
			$sel = Doctrine_Query::create()
				->select('*')
				->from('ClientShifts')
				->where('client = "' . $client . '"')
				->andWhere('id = "' . $sid . '"')
			    ->andwhere("isdelete = 0");
			    if($client_is_follower){//ISPC-2612 Ancuta 30.06.2020
			        $sel->andWhere('connection_id is NOT null');
			        $sel->andWhere('master_id is NOT null');
			    }
			$sel_res = $sel->fetchArray();

			if($sel_res)
			{
				foreach($sel_res as $k_sel => $v_sel)
				{
					$sel_res_arr[$v_sel['id']] = $v_sel;
				}

				return $sel_res_arr;
			}
			else
			{
				return false;
			}
		}
	
		
		/**
		 * ISPC-2612 Ancuta 30.06.2020
		 * @return array|Doctrine_Collection|boolean
		 */
		public function get_all_shifts_details()
		{
		    
			$sel = Doctrine_Query::create()
				->select('*')
				->from('ClientShifts');
			$sel_res = $sel->fetchArray();

			if($sel_res)
			{
			    //TODO-3355 Ancuta 18.08.2020
			    $sel_res_arr = array();
			    $sel_res_arr[0]['id'] = '0';
			    $sel_res_arr[0]['name'] = "Dienstplan";
			    $sel_res_arr[0]['color'] = '98FB98';
			    //---
				foreach($sel_res as $k_sel => $v_sel)
				{
					$sel_res_arr[$v_sel['id']] = $v_sel;
				}

				return $sel_res_arr;
			}
			else
			{
				return false;
			}
		}
	}
?>