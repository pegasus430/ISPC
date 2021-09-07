<?php
require_once("Pms/Form.php");
class Application_Form_FormBlockPermissions extends Pms_Form
{

	public function insert_block_permisions ( $client, $group, $post_data )
	{
		if ($client && $group)
		{
			$clear_perms = $this->clear_block_permisions($client, $group);

			foreach ($post_data['has_access'] as $block_key => $block_value)
			{
				$perms_arr[] = array(
						'clientid' => $client,
						'groupid' => $group,
						'block' => $block_key,
						'value' => $block_value,
				);
			}

			if (count($perms_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormBlockPermissions');
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
	 * ISPC-2302 pct.3 @Lore 24.10.2019
	 * @param unknown $client
	 * @param unknown $post_data
	 * @return boolean
	 */
	public function save_block_permisions ( $client, $post_data )
	{
	    
	    if ($client)
	    {
	        $clear_perms = $this->clear_form_block_permisions($client);
	       
	        foreach ($post_data['has_access'] as $block_key => $block_value)
	        {
	           foreach ($block_value as $bk_id => $block_value_ins){
	            
	                $perms_arr[] = array(
	                    'clientid' => $client,
	                    'groupid' => $block_key,
	                    'block' => $bk_id,
	                    'value' => $block_value_ins,
	                );
	            }
	        }
	        
	        if (count($perms_arr) > 0)
	        {
	            $collection = new Doctrine_Collection('FormBlockPermissions');
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
	 * ISPC-2302 pct.3 @Lore 24.10.2019
	 * @param boolean $group
	 */
	public function save_FormHistoryData($group = false)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $lastid_his = Doctrine_Query::create()
	    ->select('max(bulkid) as lastbulkid')
	    ->from('FormBlockPermissionsHistory');
	    $last_bulkid_arr = $lastid_his->fetchArray();
	    
	    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
	    $new_bulkid = $last_bulkid + 1 ;
	    
	    
	    $dates_for_hist = Doctrine_Query::create()
	    ->select('*')
	    ->from('FormBlockPermissions')
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
	            $data_for_hist_array[$key]["id_fbp"] = $vals["id"];
	        }
	        
	        if(count($data_for_hist_array) > 0)
	        {
	            $collection = new Doctrine_Collection('FormBlockPermissionsHistory');
	            $collection->fromArray($data_for_hist_array);
	            $collection->save();
	        }
	        
	    }
	}
	
	
	public function clear_block_permisions ( $client, $group )
	{
	    $del_perms = Doctrine_Query::create()
	    ->delete('*')
	    ->from('FormBlockPermissions')
	    ->where('clientid="' . $client . '"')
	    ->andWhere('groupid="' . $group . '"');
	    $del_perms_exec = $del_perms->execute();
	}
	
	/**
	 * @author Loredana
	 * ISPC-2302 pct.3 @Lore 24.10.2019
	 * @param unknown $client
	 */
	public function clear_form_block_permisions ( $client )
	{
		$del_perms = Doctrine_Query::create()
		->delete('*')
		->from('FormBlockPermissions')
		->where('clientid= ?' , $client );
		
		$del_perms_exec = $del_perms->execute();
	}

}
?>