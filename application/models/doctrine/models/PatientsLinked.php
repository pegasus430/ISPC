<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientsLinked', 'SYSDAT');

	class PatientsLinked extends BasePatientsLinked {

		public function get_link_data($lid)
		{
			$lp = Doctrine_Query::create()
				->select('*')
				->from('PatientsLinked')
				->where('id = "' . $lid . '"');
			$link_data = $lp->fetchArray();

			if($link_data)
			{
				return $link_data;
			}
		}

		public function linked_patients($source)
		{
			$lp = Doctrine_Query::create()
				->select('*')
				->from('PatientsLinked')
				->where('source = "' . $source . '"');
			$linked_patients = $lp->fetchArray();

			if($linked_patients)
			{
				return $linked_patients;
			}
			else
			{
				return false;
			}
		}

		/**
		 * TODO-3378 Ancuta 27.08.2020
		 * @param unknown $ipid
		 * @return array|Doctrine_Collection|boolean
		 * @deprecated
		 */
		public function get_related_patients_200827($ipid)
		{
			$rp = Doctrine_Query::create()
				->select('*')
				->from('PatientsShare')
				->where('source = "' . $ipid . '"')
				->orWhere('target = "' . $ipid . '"')
				->groupBy('link');
			$related_patients = $rp->fetchArray();

			if($related_patients)
			{
				return $related_patients;
			}
			else
			{
				return false;
			}
		}
    
		/**
		 * @author Ancuta 27.08.2020 add a check to see if the link between patients still exists!
		 * @param unknown $ipid
		 * TODO-3378 Ancuta 27.08.2020
		 * @return boolean|array|Doctrine_Collection
		 */
        public function get_related_patients($ipid)
        {
            $rp = Doctrine_Query::create()->select('*')
                ->from('PatientsShare')
                ->where('source = "' . $ipid . '"')
                ->orWhere('target = "' . $ipid . '"')
                ->groupBy('link');
            $related_patients = $rp->fetchArray();
            
            //TODO-3378
            $link_ids = array();
            foreach ($related_patients as $k => $shared) {
                $link_ids[] = $shared['link'];
            }
            if (empty($link_ids)) {
                return false;
            }
            $link_info_arr = Doctrine_Query::create()->select('*')
                ->from('PatientsLinked')
                ->whereIn('id',  $link_ids)
                ->fetchArray();
    
            $link_info = array();
            foreach ($link_info_arr as $kl => $li) {
                if($li['source'] == $ipid || $li['target'] == $ipid ){
                    $link_info[$li['id']] = $li;
                }
            }
                
            foreach ($related_patients as $k => $rp) {
                if (empty($link_info[$rp['link']])) {
                    unset($related_patients[$k]);
                }
            }
            //-- 
            
            if (! empty($related_patients)) {
                return $related_patients;
            } else {
                return false;
            }
        }
		

		/**
		 * @param unknown $client_ipids
		 * @param boolean $allow_only_intense
		 * @return array|Doctrine_Collection|boolean
		 * //ISPC-2614 Ancuta 15.07.2020 - added a new param $allow_only_intense
		 */
		public function client_sent_linked_patients($client_ipids,$allow_only_intense = false)
		{
		    if(empty($client_ipids)){
		        return;
		    }
			$lp = Doctrine_Query::create()
				->select('*')
				->from('PatientsLinked')
				->whereIn('source', $client_ipids);
				//ISPC-2614 Ancuta 15.07.2020
				if($allow_only_intense){ 
				    $lp->andWhere('intense_system is NOT NULL');
			    }
			    //-- 
			    $lp->orderBy('create_date DESC');
			$linked_patients = $lp->fetchArray();

			if($linked_patients)
			{
				return $linked_patients;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param unknown $client_ipids
		 * @param boolean $allow_only_intense
		 * @return array|Doctrine_Collection|boolean
		 * //ISPC-2614 Ancuta 15.07.2020 - added a new param $allow_only_intense
		 */
		public function client_received_linked_patients($client_ipids,$allow_only_intense = false)
		{
			$lp = Doctrine_Query::create()
				->select('*')
				->from('PatientsLinked')
				->whereIn('target', $client_ipids);
				//ISPC-2614 Ancuta 15.07.2020
				if($allow_only_intense){
				    $lp->andWhere('intense_system is NOT NULL');
			     }
			     //--
			$lp->orderBy('create_date DESC');
			$linked_patients = $lp->fetchArray();

			if($linked_patients)
			{
				return $linked_patients;
			}
			else
			{
				return false;
			}
		}

		public function get_link_shortcuts($link = "", $client_shortcuts = "")
		{
			$shrts = Doctrine_Query::create()
				->select('*')
				->from('PatientsShare')
				->where('link = "' . $link . '"');
			$shortcuts = $shrts->fetchArray();

			if($shortcuts)
			{
				foreach($shortcuts as $shortcut)
				{
					$linked_shortcuts[$shortcut['source']][$shortcut['shortcut']] = $shortcut;
				}
				return $linked_shortcuts;
			}
		}

	}

?>