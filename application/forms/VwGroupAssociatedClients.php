<?php

	require_once("Pms/Form.php");

	class Application_Form_VwGroupAssociatedClients extends Pms_Form {


	    function clients_group_add($clients, $group)
	    {
	        
	        //add delete here //DONE
	        if(!empty($clients['connected']) && !empty($group))
	        {
	            $this->client_groups_delete($group);
	    
	            foreach($clients['connected'] as $k_client => $v_client)
	            {
	                if($v_client == $clients['parent']){
	                    $parent = "1";
	                }else{
	                    $parent = "0";
	                }
	                
	                $a_clients = new VwGroupAssociatedClients();
	                $a_clients->group_id = $group;
	                $a_clients->client = $v_client;
	                $a_clients->parent = $parent;
	                $a_clients->status = "0";
	                $a_clients->save();
	            }
	    
	            return $a_clients->id;
	        }
	    }
	    

	    function client_groups_delete($group)
	    {
	        $q = Doctrine_Query::create()
	        ->delete('VwGroupAssociatedClients')
	        ->where('group_id ="' . $group . '"');
	        $q->execute();
	    }
	    
	    function clients_group_set_delete($group)
	    {
	        $cl_group = Doctrine_Query::create()
	        ->update("VwGroupAssociatedClients")
	        ->set('status', "1")
	        ->where("group_id='" . $group . "'");
	        $cl_group->execute();
	    
	        return $cl_group;
	    }
	     
	    
	}

?>