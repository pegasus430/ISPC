<?php
require_once("Pms/Form.php");
class Application_Form_FormTypePermissions extends Pms_Form
{

	public function insert_type_permisions ( $client, $group, $post_data )
	{
		if ($client && $group)
		{
			$clear_perms = $this->clear_type_permisions($client, $group);

			foreach ($post_data['has_access'] as $type_key => $type_value)
			{
				$perms_arr[] = array(
						'clientid' => $client,
						'groupid' => $group,
						'type' => $type_key,
						'value' => $type_value,
				);
			}

			if (count($perms_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormTypePermissions');
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
	 * ISPC-2302 pct.3 @Lore 25.10.2019
	 * @param unknown $client
	 * @param unknown $post_data
	 * @return boolean
	 */
	public function save_type_permisions ( $client, $post_data )
	{
	    if ($client)
	    {
	        $clear_perms = $this->clear_all_type_permisions($client);
	        
	        foreach ($post_data['has_access'] as $type_key => $type_value)
	        {
	            foreach ($type_value as $tk_id => $type_value_ins){
	                
	                $perms_arr[] = array(
	                    'clientid' => $client,
	                    'groupid' => $type_key,
	                    'type' => $tk_id,
	                    'value' => $type_value_ins,
	                );
	            }
	        }
	        
	        if (count($perms_arr) > 0)
	        {
	            $collection = new Doctrine_Collection('FormTypePermissions');
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
	 * ISPC-2302 pct.3 @Lore 25.10.2019
	 * @param boolean $group
	 */
	public function save_TypeHistoryData($group = false)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $lastid_his = Doctrine_Query::create()
	    ->select('max(bulkid) as lastbulkid')
	    ->from('FormTypePermissionsHistory');
	    $last_bulkid_arr = $lastid_his->fetchArray();
	    
	    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
	    $new_bulkid = $last_bulkid + 1 ;
	    
	    
	    $dates_for_hist = Doctrine_Query::create()
	    ->select('*')
	    ->from('FormTypePermissions')
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
	            $data_for_hist_array[$key]["id_ftp"] = $vals["id"];
	        }
	        
	        if(count($data_for_hist_array) > 0)
	        {
	            $collection = new Doctrine_Collection('FormTypePermissionsHistory');
	            $collection->fromArray($data_for_hist_array);
	            $collection->save();
	        }
	        
	    }
	}
	
	public function clear_type_permisions ( $client, $group )
	{
		$del_perms = Doctrine_Query::create()
		->delete('*')
		->from('FormTypePermissions')
		->where('clientid="' . $client . '"')
		->andWhere('groupid="' . $group . '"');
		$del_perms_exec = $del_perms->execute();
	}

	/**
	 * @author Loredana
	 * ISPC-2302 pct.3 @Lore 25.10.2019
	 * @param unknown $client
	 * @param unknown $group
	 */
	public function clear_all_type_permisions ( $client )
	{
	    $del_perms = Doctrine_Query::create()
	    ->delete('*')
	    ->from('FormTypePermissions')
	    ->where('clientid = ?', $client);
	    $del_perms_exec = $del_perms->execute();
	}
}
?>