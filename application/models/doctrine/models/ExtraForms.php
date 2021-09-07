<?php

	Doctrine_Manager::getInstance()->bindComponent('ExtraForms', 'SYSDAT');

	class ExtraForms extends BaseExtraForms {
		
		private static $client_forms = null;
		
		public static function getClientPersmission($clientid = 0, $blockid = 0)
		{
		   
		    if (empty($clientid)) {
		        $logininfo =  new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
		    
			if (is_null(self::$client_forms)){

				$fdoc = Doctrine_Query::create()
				->select('id, formid')
				->from('ExtraFormsClient')
				->where("clientid = ? " , $clientid );
				//->andWhere("formid ='" . $blockid . "'");
				$mncd = $fdoc->fetchArray();
				
				if (is_array($mncd) && !empty($mncd)) {
					$forms = array();
					foreach($mncd as $k=>$v) {
						$forms[$v['formid']] = $v['id'];
					}
					self::$client_forms[$clientid] = $forms;
						
					if (isset(self::$client_forms[$clientid][$blockid])){
						return 1;
					}
					else {
						return 0;
					}
				}
				else {
					//something went wrog or no form access
					self::$client_forms[$clientid] = array();
					return 0;
				}

			}
			else {
				if (isset(self::$client_forms[$clientid][$blockid])){
					return 1;
				}
				else {
					return 0;
				}
			}
			
			return;
			//old version below
			
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraFormsClient')
				->where("clientid ='" . $clientid . "'")
				->andWhere("formid ='" . $blockid . "'");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();

				if(count($fcarr) > 0)
				{
					return 1;
				}
				return 0;
			}
		}
		
		
		/**
		 * 
		 * this function is to be used on no load intensive pages, cause it used 2 db
		 * @param number $clientid
		 * @param string $return_ids
		 */
		public function get_client_forms($clientid = 0, $return_ids = false)
		{
		    if (is_null(self::$client_forms) || empty(self::$client_forms[$clientid])) {
		        $this->getClientPersmission( $clientid , -1);
		    }
		   
		    if ( ! empty(self::$client_forms[$clientid]) && ! isset(self::$client_forms[$clientid]['_formNiceName'])) {
		        $formNiceName = $this->_getNiceName(array_keys(self::$client_forms[$clientid]));
		        self::$client_forms[$clientid]['_formNiceName'] = $formNiceName;
		    }
		    		    
		    // return true, instead of id
		    if ( ! $return_ids) {
		        array_walk(self::$client_forms[$clientid], function (&$v, $k){
		            if ($k != '_formNiceName') {
		                $v = true;
		            }
		        });
		    }
		    return self::$client_forms[$clientid];
		}
		
		private function _getNiceName($ids = 0) {
		
		    $result = array();
		
		    if (empty($ids)) return;
		
		    $ids =  is_array($ids) ? $ids : array($ids);
		
		    $q = $this->getTable()->createQuery()
		    ->select('id, formname, inadmission, indetails, instammdatenerweitert')
		    ->whereIn('id', $ids)->fetchArray();
		
		    foreach ($q as $row) {
		        $result[$row['id']] = array(
		            'id' => $row['id'] ,
		            'formname' => $row['formname'] ,
		            'inadmission' => $row['inadmission'],
		            'indetails' => $row['indetails'],
		            'instammdatenerweitert' => $row['instammdatenerweitert'],
		        );
		    }
		    return $result;
		}
		
		
		
		public function getClientsPersmission($clientids, $blockid)
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraFormsClient')
				->whereIn("clientid", $clientids)
				->andWhere("formid ='" . $blockid . "'");
			$mncd = $fdoc->fetchArray();

			if($mncd)
			{
				foreach($mncd as $k_res => $v_perms)
				{
					$client_ids[] = $v_perms['clientid'];
				}

				return $client_ids;
			}
			else
			{
				return false;
			}
		}

		public function getExtraFormdata($frmid)
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraForms')
				->where("id ='" . $frmid . "'");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

		public function getExtraFormsAdmission()
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraForms')
				->where("inadmission=1");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

		public function getExtraFormsDetails()
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraForms')
				->where("indetails=1");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

		public function getExtraFormsStammdatenerweitert()
		{
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('ExtraForms')
				->where("instammdatenerweitert=1");
			$mncd = $fdoc->execute();

			if($mncd)
			{
				$fcarr = $mncd->toArray();
				return $fcarr;
			}
		}

	}

?>