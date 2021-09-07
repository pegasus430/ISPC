<?php

require_once("Pms/Form.php");

class Application_Form_Modules extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['module'])){
			$this->error_message['module']=$Tr->translate('entermodulename'); $error=1;
		}

		if($_GET['id']<1)
		{
			$user = Doctrine::getTable('Modules')->findBy('module',$post['module']);
			if(count($user->toArray())>0){
				$this->error_message['module'] = $Tr->translate("modulealreadyexists");$error=7;
			}
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$mod = new Modules();
		$mod->module = $post['module'];
		$mod->comment = $post['comment'];
		$mod->parentid = $post['parentid'];
		$mod->save();
		
		$moduleid = $mod->id;
		
		
		if($moduleid && !empty($post['grant_access'])){
		    
		    
		    foreach($post['clients'] as $k=>$client_id){
		    
		        // if client  and moodule are in table  - update
		        if(!empty($post['grant_access'])){
		    
		            if(in_array($client_id,$post['grant_access'])){
		                $canaccess[$client_id][$moduleid] = "1";
		            } else {
		                $canaccess[$client_id][$moduleid]  = "0";
		            }
		        }
		        else
		        {
		            $canaccess[$client_id][$moduleid]  = "0";
		        }
		    
		        $records[] = array(
		            "clientid"=>$client_id,
		            "moduleid"=>$moduleid,
		            "canaccess"=> $canaccess[$client_id][$moduleid]
		        );
		    }
		    
		    if(!empty($records)){
		        $collection = new Doctrine_Collection('ClientModules');
		        $collection->fromArray($records);
		        $collection->save();
		        
		    }
		}
	}

	public function UpdateData($post)
	{

	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	    
		$mod = Doctrine::getTable('Modules')->find($_GET['id']);
		$mod->module = $post['module'];
		$mod->comment = $post['comment'];
		$mod->parentid = $post['parentid'];
		$mod->save();
		
		if(!empty($_GET['id'])){
	        $module_id = $_GET['id'];
	        
		    foreach($post['clients'] as $k=>$client_id){
		        
		        // if client  and moodule are in table  - update
		        if(!empty($post['grant_access'])){
		            
    		        if(in_array($client_id,$post['grant_access'])){
    		            $canaccess[$client_id][$module_id] = "1";
    		        } else {
    		            $canaccess[$client_id][$module_id]  = "0";
    		        }
		        } 
		        else 
		        {
		            $canaccess[$client_id][$module_id]  = "0";
		        }
		        
		        $drop = Doctrine_Query::create()
		        ->select('*')
		        ->from('ClientModules')
		        ->where("moduleid='" . $module_id . "'")
		        ->andWhere("clientid = '" . $client_id . "'");
		        $droparray = $drop->fetchArray();
		        
		        if(!empty($droparray))
		        {
                    foreach($droparray as $k=>$line)
                    {
                        $update = Doctrine_Query::create()
                        ->update('ClientModules')
                        ->set('canaccess', $canaccess[$client_id][$module_id] )
                        ->set('change_user', $userid )
                        ->set('change_date', "'".date('Y-m-d H:i:s', time()) ."'")
                        ->where('id = "' . $line['id'] . '"')
                        ->andWhere('clientid = "' . $client_id . '"')
                        ->andWhere('moduleid = "' . $module_id . '"');
                        $rows = $update->execute();
                    }
                    
		        } else {
		            
		            $clientmodules = new ClientModules();
		            $clientmodules->clientid = $client_id;
		            $clientmodules->moduleid = $module_id;
		            $clientmodules->canaccess = $canaccess[$client_id][$module_id];
		            $clientmodules->save();
		        }
		    }
		}
	}
}
?>