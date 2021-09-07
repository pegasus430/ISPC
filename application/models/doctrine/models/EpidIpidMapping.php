<?php

	Doctrine_Manager::getInstance()->bindComponent('EpidIpidMapping', 'IDAT');

	class EpidIpidMapping extends BaseEpidIpidMapping {

		public function getIpidsEpids($ipids = array())
		{
			if(is_array($ipids) && !empty($ipids))
			{
				$ipids = array_values($ipids);
				$epq = Doctrine_Query::create()
					->select('id, epid, ipid')
					->from('EpidIpidMapping e')
					->whereIn('e.ipid', $ipids);
					//->orderBy('e.epid_num ASC');
				$epids = $epq->fetchArray();

				$epids_final = array();
				foreach($epids as $k_epid => $v_epid)
				{
					$epids_final[$v_epid['ipid']] = strtoupper($v_epid['epid']);
				}

				return $epids_final;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * from an array of epids get a associative array[strtoupper(epid)] = ipid
		 * @param array $epids
		 * @return multitype:array|boolean false on fail args
		 */
		public function getEpidsIpids($epids = array())
		{
			if(is_array($epids) && !empty($epids))
			{
				$epids = array_values($epids);
				$epq = Doctrine_Query::create()
				->select('id, epid, ipid')
				->from('EpidIpidMapping e')
				->whereIn('e.epid', $epids);
				//->orderBy('e.epid_num ASC');
				$epids = $epq->fetchArray();
		
				$epids_final = array();
				foreach($epids as $k_epid => $v_epid)
				{
					$epids_final[strtoupper($v_epid['epid'])] = ($v_epid['ipid']);
				}
		
				return $epids_final;
			}
			else
			{
				return false;
			}
		}
		
		//ispc-1533
		/**
		 * same as getEpidsIpids without the upper(epids)
		 * @param array $epids
		 * @return array|boolean false on fail args
		 */
		public function get_ipids_of_epids( $epids = array() )
		{
			//print_r($epids); die();
			if(is_array($epids) && !empty($epids))
			{
				$epq = Doctrine_Query::create()
				->select('ipid, epid')
				->from('EpidIpidMapping')
				->whereIn('epid', $epids);
				
				$epids = $epq->fetchArray();
		
				foreach($epids as $k_epid => $v_epid)
				{
					$epids_final[$v_epid['epid']] = ($v_epid['ipid']);
				}
		
				return $epids_final;
			}
			else
			{
				return false;
			}
		}	
		
		public function check_epid($clientid,$epid)
		{
			$epq = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping e')
				->where('e.epid = "'.$epid.'" ')
				->andWhere('e.clientid= "'.$clientid.'" ');
			$epids_array = $epq->fetchArray();
			
			if($epids_array && !empty($epids_array))
			{
			    return false;
			} 
			else
			{
			    return true;
			}
		}
		
		public function check_sorted_epid($clientid,$epid_num)
		{
			$epq = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping e')
				->where('e.epid_num = "'.$epid_num.'" ')
				->andWhere('e.clientid= "'.$clientid.'" ');
			$epids_array = $epq->fetchArray();
			
			if($epids_array && !empty($epids_array))
			{
			    return false;
			} 
			else
			{
			    return true;
			}
		}

		public function epid_cloned_patient($data, $target)
		{
			$sortepid = Pms_Uuid::GenerateSortEpid($target);

			$res = new EpidIpidMapping();
			$res->clientid = $target;
			$res->ipid = $data['ipid'];
			$res->epid = $data['epid'];
			$res->epid_chars = $sortepid['epid_chars'];
			$res->epid_num = $sortepid['epid_num'];
			$res->save();

			if($res)
			{
				return $res->id;
			}
			else
			{
				return false;
			}
		}

		
		

	/**
	 * 
	 * @param string $epid
	 * @param number $clientid
	 * @return void|string
	 */
	public static function getIpidFromEpidAndClientid($epid = '', $clientid = 0)
    {
        if (empty($epid) || empty($clientid))
            return;
        
        $arr = Doctrine_Core::getTable('EpidIpidMapping')->findOneByEpidAndClientid($epid, $clientid, Doctrine_Core::HYDRATE_ARRAY);
        
        if ( ! empty($arr) && isset($arr['ipid'])) {
            
            return $arr['ipid'];
            
        } else {
            
            return;
        }        
    }
    
    /**
     * ! ATTENTION ! it return TRUE for an empty ipid string
     * 
     * @param string $ipid
     * @return boolean
     */
    public static function assertIpidExists($ipid = '')
    {
        if (empty($ipid)) {
            return true;
        }
    
        $arr = Doctrine_Core::getTable('EpidIpidMapping')->findOneBy('ipid', $ipid, Doctrine_Core::HYDRATE_ARRAY);
        
        return empty($arr) ? false : true;
        
    }
}

?>