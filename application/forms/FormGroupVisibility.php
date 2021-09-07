<?php
require_once("Pms/Form.php");
class Application_Form_FormGroupVisibility extends Pms_Form
{

    /**
     * @author Loredana
     * ISPC-2482 @Lore 21.11.2019
     * @param boolean $group
     */
    public function save_HistoryVisibility($client, $tabela)
    {
        if(empty($client) || empty($tabela)){
            return false;
        }
        
        $tabela_history = $tabela.'History';
        $lastid_his = Doctrine_Query::create()
        ->select('max(bulkid) as lastbulkid')
        ->from('GroupDefaultVisibilityHistory');
        $last_bulkid_arr = $lastid_his->fetchArray();
        
        $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
        $new_bulkid = $last_bulkid + 1 ;
        
        
        $dates_for_hist = Doctrine_Query::create()
        ->select('*')
        ->from($tabela)
        ->where('clientid =?', $client)
        ->andWhere('groupid is not NULL');
        
        $data_for_hist_array= $dates_for_hist->fetchArray();
        
        if(!empty($data_for_hist_array))
        {
            foreach($data_for_hist_array as $key => $vals)
            {
                $data_for_hist_array[$key]["id"] = NULL;
                $data_for_hist_array[$key]["bulkid"] = $new_bulkid;
                $data_for_hist_array[$key]["id_gdv"] = $vals["id"];
            }
            
            if(count($data_for_hist_array) > 0)
            {
                $collection = new Doctrine_Collection($tabela_history);
                $collection->fromArray($data_for_hist_array);
                $collection->save();
            }
            
        }
    }

   /**
	* @author Loredana
	* ISPC-2482 @Lore 21.11.2019 
    * @param unknown $client
    * @param unknown $tabela
    */ 
    public function clear_visibility_permisions ( $client, $tabela)
    {
        if(empty($client) || empty($tabela)){
            return false;
        }
        
        $del_perms = Doctrine_Query::create()
        ->delete('*')
        ->from($tabela)
        ->where('clientid =?', $client)
        ->andWhere('groupid is not NULL');
        $del_perms_exec = $del_perms->execute();
   
    }

   /**
	* @author Loredana
	* ISPC-2482 @Lore 21.11.2019  
    * @param unknown $post_data
    * @param unknown $client
    * @return boolean
    */ 
    public function insert_defa_visibility_permisions ($post_data, $client){
        
        if(empty($client)){
            return false;
        }
        
        $cg = Doctrine_Query::create()
        ->select('*')
        ->from('Usergroup indexBy groupname')
        ->where('clientid =?', $client)
        ->andWhere('isdelete = 0');
        
        $client_groups_array = $cg->fetchArray();
       
        foreach ($post_data['gdf'] as $group_name => $group_id )
        {   
            foreach ($group_id as $val_id => $value)
            {
                $grp = new GroupDefaultVisibility();
                $grp->master_group_id = $client_groups_array[$group_name]['groupmaster'];
                $grp->groupid = $val_id;
                $grp->clientid = $client;
                $grp->save();
            }

        }
        
    }

    /**
	* @author Loredana
	* ISPC-2482 @Lore 21.11.2019 
    * @param unknown $post_data
    * @param unknown $client
    * @return boolean
    */
    public function insert_secrecy_visibility_permisions ($post_data, $client){
        
      if(empty($client)){
          return false;
      }
      //dd($post_data); 
      $cg = Doctrine_Query::create()
      ->select('*')
      ->from('Usergroup indexBy groupname')
      ->where('clientid =?', $client)
      ->andWhere('isdelete = 0');
      
      $client_groups_array = $cg->fetchArray();
      
      foreach ($post_data['sct'] as $group_name => $group_id )
      {
          foreach ($group_id as $val_id => $value)
          {
              $grp = new GroupSecrecyVisibility();
              $grp->master_group_id = $client_groups_array[$group_name]['groupmaster'];
              $grp->groupid = $val_id;
              $grp->clientid = $client;
              $grp->save();
          }
          
      }

    }

	


}
?>