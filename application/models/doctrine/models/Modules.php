<?php

	class Modules extends BaseModules {

		private static $client_modules = null;
		 
		public static function checkModulePrivileges($moduleid = 0, $clientid = 0)
		{
		    if (empty($clientid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
		    
			//cla v2 - in most pages this function is called multiple times
			if (is_null(self::$client_modules) || empty(self::$client_modules[$clientid]) ){
				$drop = Doctrine_Query::create()
				->select('id, moduleid')
				->from('ClientModules')
				->andWhere("clientid = ? " , $clientid )
				->andWhere("canaccess = 1");
				$droparray = $drop->fetchArray();
				
				if (is_array($droparray) && !empty($droparray)) {
					$modules = array(); 
					foreach($droparray as $k=>$v) {
						$modules[$v['moduleid']] = $v['id'];
					}
					self::$client_modules[$clientid] = $modules;
					
					if (isset(self::$client_modules[$clientid][$moduleid])){
						return true;
					}
					else {
						return false;
					}
				} else{
					//something went wrog or no module access, we should set $client_modules as empty array()
					self::$client_modules[$clientid] = array();
					return false;
				}
			}
			else {
				if (isset(self::$client_modules[$clientid][$moduleid])){
					return true;
				}
				else {
					return false;
				}
			}
			
			return;
			//old version

			$drop = Doctrine_Query::create()
				->select('*')
				->from('ClientModules')
				->where("moduleid='" . $moduleid . "'")
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("canaccess = 1");
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function checkClientsModulePrivileges($moduleid, $clientids, $client_arr = array())
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('ClientModules')
				->where("moduleid = ?" , $moduleid )
				->andWhere("canaccess = 1");
			if(is_array($clientids))
			{
				$drop->andWhereIn("clientid", $clientids);
			}
			else
			{
				$drop->andWhere("clientid = ?" , $clientids );
			}
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				if(!empty($client_arr))
				{

					foreach($droparray as $client_perms)
					{
						$client_permissions[$client_perms['clientid']] = $client_perms;
					}

//					filter clients_arr data with resulted ids
					foreach($client_arr as $client_data)
					{
						if($client_permissions[$client_data['id']]['canaccess'] == '1')
						{
							$clients_perm[$client_data['id']] = $client_data;
							$clients_perm['ids'][] = $client_data['id'];
						}
					}
				}
				else
				{
//					just return clients with module perms
					foreach($droparray as $client_perm)
					{
						$clients_perm[] = $client_perm['clientid'];
					}
				}

				return $clients_perm;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @cla on 06.06.2018 - changed to static and cleaned up
		 * 
		 * @param unknown $module_array
		 * @param string $client_ids_array
		 * @return void|multitype:Ambigous <> |boolean
		 */
		public static function clients2modules($module_array = array(), $client_ids_array = false)
		{
		    if (empty($module_array)) {
		        return;
		    }
		    
		    $module_array = is_array($module_array) ? $module_array : array($module_array); 
		    
			$drop = Doctrine_Query::create()
				->select('*')
				->from('ClientModules')
				->where("canaccess = 1")
				->andWhereIn("moduleid", $module_array);

			if( ! empty($client_ids_array) && is_array($client_ids_array)) {
				$drop->andWhereIn("clientid", $client_ids_array);
			}

			$droparray = $drop->fetchArray();

			if ($droparray) {

			    $clients_perm = array();
			    
				foreach ($droparray as $client_perm) {
					$clients_perm[] = $client_perm['clientid'];
				}

				return $clients_perm;
				
			} else {
			    
				return false;
			}
		}

		private function _getNiceName($ids = 0) {
		    
		    $result = array();
		    
		    if (empty($ids)) return;
		    
		    $ids =  is_array($ids) ? $ids : array($ids);

		    $q = $this->getTable()->createQuery()
		    ->select('id, module, comment')
		    ->whereIn('id', $ids)->fetchArray();

		    foreach ($q as $row) {
		        $result[$row['id']] = array(  
		            'id' => $row['id'] ,
		            'module' => $row['module'] , 
		            'comment' => $row['comment']
		        );
		    }
		    return $result;		    
		}
		
		
		public function get_client_modules($clientid = 0, $return_ids = false)
		{
		    if (empty($clientid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');   
		        $clientid = $logininfo->clientid;
		    }
		    
			if (is_null(self::$client_modules) || empty(self::$client_modules[$clientid])) {
				$this->checkModulePrivileges(-1, $clientid);
			}
			
			if ( ! empty(self::$client_modules[$clientid]) && ! isset(self::$client_modules[$clientid]['_moduleNiceName'])) {
			    $moduleNiceName = $this->_getNiceName(array_keys(self::$client_modules[$clientid]));
			    self::$client_modules[$clientid]['_moduleNiceName'] = $moduleNiceName;
			}
			
			// return true, instead of id
			if ( ! $return_ids) {
			    array_walk(self::$client_modules[$clientid], function (&$v, $k){
			        if ($k != '_moduleNiceName') { 
			            $v = true;
			        }
			    });
			}		
			return self::$client_modules[$clientid];
		}
		
		
		
	}

?>