<?php

class SapvverordnungController extends Zend_Controller_Action
{
	public $act;
	public function init()
	{
		/* Initialize action controller here */
	}

	public function  sapvverordnungpermissionsAction() {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();

		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("SapvverordnungPermissions")
			->where("clientid='".$logininfo->clientid."'");
			$q->execute();

			if(is_array($_POST['access']))
			{

				foreach($_POST['access'] as $key=>$val)
				{
					if($val == 1){
						$fc = new SapvverordnungPermissions();
						$fc->subdiv_id= $key;
						$fc->subdiv_order= $_POST['order'][$key];
						$fc->clientid = $logininfo->clientid;
						$fc->save();
					}
				}

			} else{

				$this->view->error_message = $this->view->translate("please select al least one subdivision");
			}
		}

		$sets = Doctrine_Query::create()
		->select("*")
		->from("SapvverordnungSubdivisions")
		->where("isdelete=0");
		$setarr = $sets->fetchArray();

		// 		SapvverordnungSubdivisions
		$symperm = new SapvverordnungPermissions();
		$perms = $symperm->getClientSapvverordnungpermissions($logininfo->clientid);

		if(!$perms){
			$this->view->set_default = 1;
		}

		foreach ($setarr as $key => $sympset){
			$setperm[$key] = $sympset;
			$set = 0;
			foreach($perms as $perm){
				if($perm['subdiv_id'] == $sympset['id']){
					$setperm[$key]['access'] = 1;
					$setperm[$key]['order'] = $perm['subdiv_order'];
					$set = 1;
				}
			}
			if($set == 0){
				$setperm[$key]['access'] = false;
				$setperm[$key]['order'] = false;
			}
		}

		$grid = new Pms_Grid($setperm,1,count($setperm),"sapvverordnungsubdivisions.html");
		$this->view->listsets = $grid->renderGrid();
	}

	public function  sapvverordnungsubdivisionsAction() {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();

		if($this->getRequest()->isPost())
		{
			$fc = new SapvverordnungSubdivisions();
			$fc->name= $_POST['name'];
			$fc->save();

			$this->_redirect(APP_BASE.'sapvverordnung/sapvverordnungsubdivisions');
		}


		$sets = Doctrine_Query::create()
		->select("*")
		->from("SapvverordnungSubdivisions")
		->where("isdelete=0");
		$setarr = $sets->fetchArray();

		// 		SapvverordnungSubdivisions
		$symperm = new SapvverordnungPermissions();
		$perms = $symperm->getClientSapvverordnungpermissions($logininfo->clientid);
		foreach ($setarr as $key => $sympset){
			$subdiv[$key] = $sympset;
			$set = 0;
			foreach($perms as $perm){
				if($perm['subdiv_id'] == $sympset['id']){
					$subdiv[$key]['access'] = 1;
					$subdiv[$key]['order'] = $perm['subdiv_order'];
					$set = 1;
				}
			}
			if($set == 0){
				$subdiv[$key]['access'] = false;
				$subdiv[$key]['order'] = false;
			}
		}

		$this->view->subdiv = $subdiv;

	}
}

?>