<?php

require_once("Pms/Form.php");

class Application_Form_BtmGroupPermissions extends Pms_Form {

	public function insert_btm_permisions($client, $group, $post_data)
	{
		if($client && $group)
		{
			$clear_perms = $this->clear_btm_permisions($client, $group);

			foreach($post_data['has_access'] as $btm_key => $btm_value)
			{
				$perms_arr[] = array(
						'clientid' => $client,
						'groupid' => $group,
						'name' => $btm_key,
						'value' => $btm_value,
				);
			}

			if(count($perms_arr) > 0)
			{
				$collection = new Doctrine_Collection('BtmGroupPermissions');
				$collection->fromArray($perms_arr);
				$collection->save();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @author Loredana
	 * ISPC-2302  	    // Maria:: Migration ISPC to CISPC 08.08.2020
	 * @param unknown $client
	 * @param unknown $post_data
	 * @return boolean
	 */
	public function save_btm_permisions($client, $post_data)
	{
	    if($client)
	    {
	        $clear_perms = $this->clear_all_btm_permisions($client);
	        
	        foreach($post_data['has_access'] as $btm_key => $btm_value)
	        {
	            foreach ($btm_value as $bk_id => $btm_value_ins){
	                
	                $perms_arr[] = array(
	                    'clientid' => $client,
	                    'groupid' => $btm_key,
	                    'name' => $bk_id,
	                    'value' => $btm_value_ins,
	                ); 
	            }

	        }
	        
	        if(count($perms_arr) > 0)
	        {
	            $collection = new Doctrine_Collection('BtmGroupPermissions');
	            $collection->fromArray($perms_arr);
	            $collection->save();
	        }
	        
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}

	/**
	 * @author Loredana
	 * ISPC-2302
	 * @param boolean $group
	 */
	public function save_btmHistoryData($group = false)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $lastid_his = Doctrine_Query::create()
	    ->select('max(bulkid) as lastbulkid')
	    ->from('BtmGroupPermissionsHistory');
	    $last_bulkid_arr = $lastid_his->fetchArray();
	    
	    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
	    $new_bulkid = $last_bulkid + 1 ;
	    
	    
	    $dates_for_hist = Doctrine_Query::create()
	    ->select('*')
	    ->from('BtmGroupPermissions')
	    ->where('clientid =?', $clientid) ;
	    
	    if(!empty($group)){
	        $dates_for_hist->andwhere('groupid= ?', $group);
	    }
	    
	    $Bulk = '1';
	    if ($group){
	        $Bulk = '0';
	    }
	    $data_for_hist_array= $dates_for_hist->fetchArray();
	    
	    if(!empty($data_for_hist_array))
	    {
	        foreach($data_for_hist_array as $key => $vals)
	        {
	            $data_for_hist_array[$key]["id"] = NULL;
	            $data_for_hist_array[$key]["bulk"] = $Bulk;
	            $data_for_hist_array[$key]["bulkid"] = $new_bulkid;
	            $data_for_hist_array[$key]["id_bgp"] = $vals["id"];
	        }
	        
	        if(count($data_for_hist_array) > 0)
	        {
	            $collection = new Doctrine_Collection('BtmGroupPermissionsHistory');
	            $collection->fromArray($data_for_hist_array);
	            $collection->save();
	        }
	        
	    }
	}
	public function clear_btm_permisions($client, $group)
	{
		$del_perms = Doctrine_Query::create()
		->delete('*')
		->from('BtmGroupPermissions')
		->where('clientid="' . $client . '"')
		->andWhere('groupid="' . $group . '"');
		$del_perms_exec = $del_perms->execute();
	}


	/**
	 * @author Loredana 
	 * ISPC-2302 	    // Maria:: Migration ISPC to CISPC 08.08.2020
	 * @param unknown $client
	 */
	public function clear_all_btm_permisions($client)
	{
	    $del_perms = Doctrine_Query::create()
	    ->delete('*')
	    ->from('BtmGroupPermissions')
	    ->where('clientid= ?' , $client );
	    
	    $del_perms_exec = $del_perms->execute();
	}
}

?>