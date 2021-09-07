<?php

	class ModulesController extends Zend_Controller_Action
	{
		public $act;
		public function init()
		{

		}

		public function addmoduleAction()
		{
			
			$mod = Doctrine::getTable('Modules')->findAll();
			$modulearray = $mod->toArray();
			$moduleid = array("0"=>"");
			foreach($modulearray as $key=>$val)
			{
			 	$moduleid[$val['id']] =  $val['module'];
			}

		    $this->view->moduleid = $moduleid;

			$this->view->selectparentid = $_POST['parentid'];

			$a_folders = array(""=>"");
			$this->getparentcategory($a_folders,0,'');
			$this->view->parentid = $a_folders;

			// get all clients
			$client = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax")
									->from('Client')
									->where('isdelete = 0')
									->andWhere('isactive = 0');
			$clientarray = $client->fetchArray();
				
			$this->view->clientsarray = $clientarray;
			$clientarray_id = array();
			foreach( $clientarray as $k=>$client){
			    $clientarray_id[] = $client['id'];
			}

			
			
			if($this->getRequest()->isPost())
			{
			    $module_form = new Application_Form_Modules();
			
			    if($module_form->validate($_POST))
			    {
			
			        $_POST['clients'] = $clientarray_id;
			        $module_form->InsertData($_POST);
			        $this->view->error_message = $this->view->translate("recordinsertsucessfully");
			    }
			    else
			    {
			        $module_form->assignErrorMessages();
			        $this->retainValues($_POST);
			    }
			}
			
		}


		public function editmoduleAction()
		{
			 $this->_helper->viewRenderer('addmodule');
			 if(strlen($_GET['id'])>0)
			 {
			     $doc = Doctrine::getTable('Modules')->find($_GET['id']);
			     $docarray = $doc->toArray();
			     $this->retainValues($docarray);
			     $this->view->selectparentid = $docarray['parentid'];
			     $module_id = $doc->id;
			 
			 }
			 $mod = Doctrine::getTable('Modules')->findAll();
			 $modulearray = $mod->toArray();
			 $moduleid = array(""=>"");
			 foreach($modulearray as $key=>$val)
			 {
			     $moduleid[$val['id']] =  $val['module'];
			 }
			 
			 $this->view->moduleid = $moduleid;
			 $a_folders = array(""=>"");
			 $this->getparentcategory($a_folders,0,'');
			 foreach($a_folders as $k=>$a){
			     $b_folders[$k] = str_replace("&nbsp;","-",$a);
			 }
			 $this->view->parentid = $b_folders;
			 // get all clients
			 $client = Doctrine_Query::create()
			 ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax")
			 						->from('Client')
			 						->where('isdelete = 0')
			 						->andWhere('isactive = 0');
			 $clientarray = $client->fetchArray();
			 	
			 $this->view->clientsarray = $clientarray;
			 $clientarray_id = array();
			 foreach( $clientarray as $k=>$client){
			     $clientarray_id[] = $client['id'];
			 }
			 	
			 if($module_id){
			     $module_access = array();
			     $module_access = Modules::checkClientsModulePrivileges($module_id, $clientarray_id);
			     $this->view->can_access_module = $module_access;
			     
			 }
			 
			 
			 
			if($this->getRequest()->isPost())
			{
				$module_form = new Application_Form_Modules();

				 if($module_form->validate($_POST))
				 {
                    $_POST['clients'] = $clientarray_id;   

                    $module_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE.'modules/moduleslist?flg=suc');
				 }
				 else
				 {
					$module_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function moduleslistAction()
		   {
		   		$logininfo= new Zend_Session_Namespace('Login_Info');

				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('module',$logininfo->userid,'canview');

				if(!$return)
				{
					$this->_redirect(APP_BASE."error/previlege");
				}

				if($_GET['flg']=='suc')
				{
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");;
				}
		   }


	   public function fetchlistAction()
	   {
			$logininfo= new Zend_Session_Namespace('Login_Info');

			$columnarray = array("pk"=>"id","mo"=>"module");
			$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];


			$mod = Doctrine_Query::create()
				  ->select('*')
				  ->from('Modules')
				  ->where('isdelete=0')
				  ->andWhere('parentid=0')
				 ->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
				$modexec = $mod->execute();
				$modarray = $modexec->toArray();

				 $limit = 5000;
			     $mod->select('*');
				 $mod->where('isdelete=0');
				 $mod->limit($limit);
			   	$mod->offset($_GET['pgno']*$limit);

			   $modlimitexec = $mod->execute();
			   $modlimit = $modlimitexec->toArray();

			    $a_mod= array();
				$this->getmodulehierarchy($a_mod,0,'');

			
				$module_ids = array();
				if(!empty($modarray)){
                    foreach ($modarray as $k=>$ml){
                        $module_ids[] = $ml['id'];
                    }
				    
                    $clients_array = Doctrine_Query::create()
                    ->select('clientid,moduleid')
                    ->from('ClientModules')
                    ->whereIn("moduleid " , $module_ids )
                    ->andWhere("canaccess = 1")
                    ->fetchArray();
                    
                    if(!empty($clients_array)){
                        foreach($clients_array as $k=>$mcl){
                            $modulse2clients[$mcl['moduleid']][] = $mcl['clientid'];
                        }                      
                    }
				}
				
				
                $this->view->modulse2clients = $modulse2clients;
                $grid = new Pms_Grid($a_mod,1,$modarray[0]['count'],"listmodules.html");
                $this->view->modulesgrid = $grid->renderGrid();
                $this->view->navigation = $grid->dotnavigation("modnavigation.html",5,$_GET['pgno'],$limit);

				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "callBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['modulelist'] =$this->view->render('modules/fetchlist.html');

				echo json_encode($response);
				exit;
	   }

	   private function retainValues($values)
		{
		  foreach($values as $key=>$val)
			{

				$this->view->$key = $val;

			}

		}


		public function deletemodulesAction()
		{
			$this->_helper->viewRenderer('moduleslist');

			$logininfo= new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('modules',$logininfo->userid,'candelete');

				if(!$return)
				{
					$this->_redirect(APP_BASE."error/previlege");
				}
				if($this->getRequest()->isPost())
				{
					 if(count($_POST['mod_id'])<1){$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;}

					 if($error==0)
					 {
						 foreach($_POST['mod_id'] as $key=>$val)
						 {
							 $thrash = Doctrine::getTable('Modules')->find($val);
							 $thrash->isdelete = 1;
							 $thrash->save();
							$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
						 }

					}
				}
		}


	private function getparentcategory(&$a_folders,$parentid,$space)
	{
		$folder = Doctrine_Query::create()
				->select('*')
				->from('Modules')
				->where('parentid='.$parentid);
			$folderexec = $folder->execute();
			$folderarray = $folderexec->toArray();
		foreach($folderarray as $key=>$val)
		{
			$a_folders[$val['id']] = $space.$val['module'];
			$this->getparentcategory($a_folders,$val['id'],$space."&nbsp;&nbsp;&nbsp;");
		}

		 return ;

	}

	private function getmodulehierarchy(&$a_mod,$parentid,$space)
	{
		$folder = Doctrine_Query::create()
				->select('*')
				->from('Modules')
				->where('isdelete=0')
				->andWhere('parentid='.$parentid)
				 ->orderBy("Module",'ASC');

			$folderexec = $folder->execute();
			$folderarray = $folderexec->toArray();
		foreach($folderarray as $key=>$val)
		{
			array_push($a_mod,array('space'=>$space,'module'=>$val['module'],'comment'=>$val['comment'],'id'=>$val['id']));
			$this->getmodulehierarchy($a_mod,$val['id'],$space."&nbsp;&nbsp;&nbsp;");
		}

		 return ;

	}
	
	
	public function listclients2moduleAction()
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');

	    if (empty($_REQUEST['module'])) {
	        return false;
	    }
	    
	    $module_id = $_REQUEST['module'];
	    $module_data = Doctrine_Query::create()
	    ->select("*")
	    ->from('Modules')
	    ->where('isdelete=0')
	    ->andWhere('id =?', $module_id)
	    ->fetchOne();
	    $this->view->module_data = $module_data;
	    
	    $clients_array = Modules::clients2modules(array($module_id));
	    
	    if ( ! empty($clients_array)) {
            $clist = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
            ->from('Client')
            ->where('isdelete=0')
            ->andWhereIn('id', $clients_array)
            ->fetchArray();
	
    	    $list = array();
    	    foreach ($clist as $k => $cli_data) {
    	        $list[$cli_data['id']]['id'] =  $cli_data['id'];
    	        $list[$cli_data['id']]['name'] = $cli_data['client_name'];
    	    }
	    }
	    
	    $this->view->client_list = $list;
	    
	}
	}


?>