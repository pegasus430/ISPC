<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientInvoicePermissions', 'SYSDAT');

	class ClientInvoicePermissions extends BaseClientInvoicePermissions {

		public function get_client_invoice_perms($client)
		{
			
			$cip = Doctrine_Query::create()
				->select('*')
				->from('ClientInvoicePermissions')
				->where('clientid="' . $client . '"')
				->andWhere('isdelete = "0"');
			$cip_res = $cip->fetchArray();

			if($cip_res)
			{
				foreach($cip_res as $k_perms => $v_perms)
				{
					$permissions[$v_perms['invoice']]['canedit'] = $v_perms['canedit'];
					$permissions[$v_perms['invoice']]['canadd'] = $v_perms['canadd'];
					$permissions[$v_perms['invoice']]['canview'] = $v_perms['canview'];
					$permissions[$v_perms['invoice']]['candelete'] = $v_perms['candelete'];
				}
			}

			return $permissions;
		}
		
		public function get_client_allowed_invoice($client)
		{
			if(empty($client)){
			    return array();
			}
		    
			$cip_res = Doctrine_Query::create()
				->select('*')
				->from('ClientInvoicePermissions')
				->where('clientid=?', $client)
				->andWhere('isdelete = "0"')
			   ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			$permissions = array();
			if(!empty($cip_res))
			{
			    if ($cip_res['canview'] == "1"){
			        $permissions[] = $cip_res['invoice'];
			    }
			}

    		return $permissions;
		}
		
		
		/**
		 * ISPC-2312 
		 * Ancuta 06.12.2020
		 * @param unknown $client
		 * @return array
		 */
		public function get_client_allowed_invoices($client)
		{
			if(empty($client)){
			    return array();
			}
		    
			$cip_res_arr = Doctrine_Query::create()
				->select('*')
				->from('ClientInvoicePermissions')
				->where('clientid=?', $client)
				->andWhere('isdelete = "0"')
				->orderBy('id ASC')
			   ->fetchArray();

			   
			$permissions = array();
			if(!empty($cip_res_arr))
			{
			    foreach($cip_res_arr as $k=>$cip_res){
			        
    			    if ($cip_res['canview'] == "1"){
    			        $permissions[$cip_res['invoice']] = $cip_res['invoice'];
    			    }
			    }
			}

    		return $permissions;
		}

	}

?>