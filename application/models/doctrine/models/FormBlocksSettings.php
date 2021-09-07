<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlocksSettings', 'SYSDAT');

	class FormBlocksSettings extends BaseFormBlocksSettings {

		public function get_blocks_settings($clientid,$date = false,$block = false)
		{
			$sel_sett = Doctrine_Query::create()
				->select('*')
				->from('FormBlocksSettings')
				->where('clientid ="' . $clientid . '"')
				->andWhere('isdelete = 0');
			
			if($date){
				$sel_sett->andWhere(' (DATE("'.$date.'") BETWEEN DATE(valid_from) AND IF(valid_till <> "0000-00-00 00:00:00", valid_till, NOW())  ) ');
			} else{
				$sel_sett->andWhere('valid_till = "0000-00-00 00:00:00"');
			}
			if($block){
				$sel_sett->andWhere(' block = "'.$block.'" ');
				//echo $sel_sett->getSqlQuery(); 
			} 
			$sel_sett_res = $sel_sett->fetchArray();

			if($sel_sett_res)
			{
				return $sel_sett_res;
			}
			else
			{
				return false;
			}
		}
		
		public function get_blocks_settings_pr($clientid,$period)
		{
			$sel_sett = Doctrine_Query::create()
				->select('*')
				->from('FormBlocksSettings')
				->where('clientid ="' . $clientid . '"')
				->andWhere('isdelete = 0');
			$sel_sett_res = $sel_sett->fetchArray();

            
            foreach($sel_sett_res as $k=>$it){
                $it_start = date("Y-m-d",strtotime($it['valid_from']));;
                
                if($it['valid_till'] == "0000-00-00 00:00:00"){
                    $it_end = date("Y-m-d",time());
                } else{
                    $it_end = date("Y-m-d",strtotime($it['valid_till']));
                }
                
                if(Pms_CommonData::isintersected($it_start,$it_end , $period['start'],$period['end']))
                {
                    $items[] = $it;
                }
            }

			
			if($items)
			{
				return $items;
			}
			else
			{
				return false;
			}
		}

		public function get_block($clientid = 0 , $block = '', $ascendend = false)
		{
		    //ISPC-2612 Ancuta 27.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower2category('FormBlocksSettings',$clientid,$block);
		    
		    
			$sel_sett = Doctrine_Query::create()
				->select('*')
				->from('FormBlocksSettings')
				->where('clientid = ?', $clientid)
				->andWhere('block = ? ', $block)
				->andWhere('isdelete = 0');
				if($client_is_follower){
				    $sel_sett->andWhere('connection_id is NOT null');
				    $sel_sett->andWhere('master_id is NOT null');
				}
			if($ascendend)
			{
				if($block == "ebm_ber"){
					$sel_sett->orderBy('id  ASC');
				} else{
					$sel_sett->orderBy('option_name ASC');
				}
			}
			$sel_sett_res = $sel_sett->fetchArray();

			if($sel_sett_res)
			{
				return $sel_sett_res;
			}
			else
			{
				return false;
			}
		}

	}

?>