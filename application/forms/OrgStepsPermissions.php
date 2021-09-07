<?php
require_once("Pms/Form.php");
class Application_Form_OrgStepsPermissions extends Pms_Form
{

	public function insert_step_permisions ( $client, $group, $post_data )
	{
		if ($client && $group)
		{
			$clear_perms = $this->clear_step_permisions($client, $group);

			foreach ($post_data['has_access'] as $step_key => $step_value)
			{
				$perms_arr[] = array(
						'clientid' => $client,
						'groupid' => $group,
						'step' => $step_key,
						'value' => $step_value,
				);
			}

			if (count($perms_arr) > 0)
			{
				$collection = new Doctrine_Collection('OrgStepsPermissions');
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

	public function clear_step_permisions ( $client, $group )
	{
		$del_perms = Doctrine_Query::create()
		->delete('*')
		->from('OrgStepsPermissions')
		->where('clientid="' . $client . '"')
		->andWhere('groupid="' . $group . '"');
		$del_perms_exec = $del_perms->execute();
	}
	

	/**
	 * @author Loredana
	 * ISPC-2302 pct.3 @Lore 25.10.2019
	 * @param unknown $client
	 * @param unknown $post_data
	 * @return boolean
	 */
	public function save_step_permisions ( $client, $post_data )
	{
	    if ($client)
	    {
	        $clear_perms = $this->clear_all_step_permisions($client);
	        
	        foreach ($post_data['has_access'] as $step_key => $step_value)
	        {
	            foreach ($step_value as $sk_id => $step_value_ins){
	                
	                $perms_arr[] = array(
	                    'clientid' => $client,
	                    'groupid' => $step_key,
	                    'step' => $sk_id,
	                    'value' => $step_value_ins,
	                ); 
	            }
	        }
	        
	        if (count($perms_arr) > 0)
	        {
	            $collection = new Doctrine_Collection('OrgStepsPermissions');
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
	 */
	public function clear_all_step_permisions ( $client )
	{
	    $del_perms = Doctrine_Query::create()
	    ->delete('*')
	    ->from('OrgStepsPermissions')
	    ->where('clientid= ?' , $client );
	    
	    $del_perms_exec = $del_perms->execute();
	}
	
	
	/**
	 * @author Loredana
	 * ISPC-2302 pct.3 @Lore 25.10.2019
	 * @param boolean $group
	 */
	public function save_StepHistoryData($group = false)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $lastid_his = Doctrine_Query::create()
	    ->select('max(bulkid) as lastbulkid')
	    ->from('OrgStepsPermissionsHistory');
	    $last_bulkid_arr = $lastid_his->fetchArray();
	    
	    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
	    $new_bulkid = $last_bulkid + 1 ;
	    
	    
	    $dates_for_hist = Doctrine_Query::create()
	    ->select('*')
	    ->from('OrgStepsPermissions')
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
	            $data_for_hist_array[$key]["id_osp"] = $vals["id"];
	        }
	        
	        if(count($data_for_hist_array) > 0)
	        {
	            $collection = new Doctrine_Collection('OrgStepsPermissionsHistory');
	            $collection->fromArray($data_for_hist_array);
	            $collection->save();
	        }
	        
	    }
	}
	

}
?>