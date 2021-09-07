<?php
class ManualController extends Zend_Controller_Action
{
	public function init()
	{
	}

	public function addmanualAction()
	{
		$val = new Pms_Validation();
		$logininfo= new Zend_Session_Namespace('Login_Info');


		if($this->getRequest()->isPost())
		{
			if(strlen($_FILES['filename']['name'])<1){
				$this->view->error_filename = $this->view->translate("uploadfile");$error=1;
			}
			if($error==0)
			{
				ini_set("upload_max_filesize", "10M");
				$filename= "uploadfile/".$_FILES['filename']['name'];
					
				move_uploaded_file($_FILES['filename']['tmp_name'],"uploadfile/".$_FILES['filename']['name']);
					
				$mnual = new Manual();
				$mnual->filename = $_FILES['filename']['name'];
				$mnual->description = $_POST['description'];
				$mnual->save();

				if($mnual->id>0)
				{
					$this->view->error_message = $this->view->translate("manualuploadedsuccessfully");
				}
			}

		}
	}

	public function editmanualAction()
	{
		$val = new Pms_Validation();
		$this->_helper->viewRenderer('addmanual');
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{
			if(strlen($_FILES['filename']['name'])<1){
				$this->view->error_filename = $this->view->translate("uploadfile");$error=1;
			}
			if($error==0)
			{
				ini_set("upload_max_filesize", "10M");
				$filename= "uploadfile/".$_FILES['filename']['name'];
					
				move_uploaded_file($_FILES['filename']['tmp_name'],"uploadfile/".$_FILES['filename']['name']);
					
				$mnual = Doctrine::getTable('Manual')->find($_GET['id']);
				$mnual->filename = $_FILES['filename']['name'];
				$mnual->description = $_POST['description'];
				$mnual->save();

				if($mnual->id>0)
				{
					$this->_redirect(APP_BASE.'manual/listmanual?flg=suc');
					$this->view->error_message = $this->view->translate("manualuploadedsuccessfully");
				}
			}

		}

		$msn = Doctrine::getTable('Manual')->find($_GET['id']);
		if($msn)
		{
			$this->retainValues($msn->toArray());
		}
	}

	public function listmanualAction()
	{
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
		}
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk"=>"id");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];

		$manual = Doctrine_Query::create()
		->select('count(*)')
		->from('Manual')
		->where('isdelete = 0')
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$manualexec = $manual->execute();
		$manualarray = $manualexec->toArray();
			
		$limit = 50;
		$manual->select('*');
		$manual->limit($limit);
		$manual->offset($_GET['pgno']*$limit);
		 
		$manuallimitexec = $manual->execute();
		$manuallimit = $manuallimitexec->toArray();
			
		$grid = new Pms_Grid($manuallimit,1,$manualarray[0]['count'],"listmanual.html");
		$this->view->manualgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("manualnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['manuallist'] =$this->view->render('manual/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}

	public function helpAction()
	{
		$manual = Doctrine_Query::create()
		->select('*')
		->from('Manual')
		->where('isdelete = 0 and isactive=0');
		$manualexec = $manual->execute();
		$manualarray = $manualexec->toArray();
		 
		$grid = new Pms_Grid($manualarray,1,count($manualarray),"listhelp.html");
		$this->view->helpgrid = $grid->renderGrid();
	}


	public function manualdeleteAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('manual',$logininfo->userid,'candelete');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($this->getRequest()->isPost())
		{
			if(count($_POST['manualid'])<1){
				$this->view->error_message=$this->view->translate("selectatlestone"); $error=1;
			}
				
			if($error==0)
			{
				if($logininfo->usertype=='SA')
				{
						
					foreach($_POST['manualid'] as $key=>$val)
					{
						$mod = Doctrine::getTable('Manual')->find($val);
						$mod->isdelete = 1;
						$mod->save();
					}

					$this->view->error_message=$this->view->translate('recorddeletedsuccessfully');
				}
			}
		}

		$this->_helper->viewRenderer('listmanual');

	}
	 
	private function retainValues($values)
	{
		foreach($values as $key=>$val)

		{
			$this->view->$key = $val;
		}

	}
}

?>