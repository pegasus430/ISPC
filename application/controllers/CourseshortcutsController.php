<?php

class CourseshortcutsController extends Zend_Controller_Action
{
	 
	public $act;
	public function init()
	{
		/* Initialize action controller here */

	}
	private function retainValues($values)

	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function addcorseshortcutsAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->view->act="patient/addcorseshortcuts";
		$this->view->isfilter = 1;

		if($this->getRequest()->isPost())
		{
			$Courseshortcuts_form = new Application_Form_Courseshortcuts();
			if($Courseshortcuts_form->validate($_POST))
			{
				$Courseshortcuts_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$Courseshortcuts_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

		}


			
	}


	public function courseshortcutlistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate('courseshortcutupdatedsuccessfully');
		}

	}
	 
	public function fetchcourseshortcutlistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($logininfo->usertype=="SA")
		{
			$setclientid = "clientid='0'";
		}
		else
		{
			$setclientid = 'clientid="'.$clientid.'"';
		}
		$columnarray = array("pk"=>"shortcut_id","shrt"=>"shortcut","flname"=>"course_fullname","crdate"=>"create_date");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$dtype = Doctrine_Query::create()
		->select('*')
		->from('Courseshortcuts')
		->where('isdelete=0')
		->andWhere('clientid="'.$clientid.'"')
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);


		$dtypeexec = $dtype->execute();
		$dtypearray = $dtypeexec->toArray();
		$dtypelimitexec = $dtype->execute();
		$dtypelimit = $dtypelimitexec->toArray();
			
		$limit = 50;
		$dtype->select('*');
		$dtype->limit($limit);
		$dtype->offset($_GET['pgno']*$limit);

			
		$grid = new Pms_Grid($dtypelimit,1,$dtypearray[0]['count'],"listcouseshortcuts.html");
		$this->view->diagnosistypegrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("shortcutlistnavigation.html",5,$_GET['pgno'],$limit);
			
		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['shorcutlist'] =$this->view->render('courseshortcuts/fetchcourseshortcutlist.html');
			
		echo json_encode($response);
		exit;
	}
	 
	 
	public function editcourseshortcutAction()
	{
		$this->view->act="courseshortcuts/editcourseshortcut?id=".$_GET['id'];
		$this->_helper->viewRenderer('addcorseshortcuts');

		if($this->getRequest()->isPost())	{
			$Courseshortcuts_form = new Application_Form_Courseshortcuts();
			if($Courseshortcuts_form->validate($_POST))
			{
				$Courseshortcuts_form->UpdateData($_POST);
				$this->view->error_message = $this->view->translate('recordupdatedsucessfully');
				$this->_redirect(APP_BASE.'courseshortcuts/courseshortcutlist?flg=suc');
			}
			else
			{
				$Courseshortcuts_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
		$shortcuts = Doctrine::getTable('Courseshortcuts')->find($_GET['id']);
		if($shortcuts)
		{
			$this->retainValues($shortcuts->toArray());
				
			$this->view->invis = "";
			$shrtctarr = array('K','D','M','S');

			if(in_array($shortcuts['shortcut'],$shrtctarr))
			{
				$this->view->invis = "readonly='readonly'";
			}

		}
		 
	}
	 
	public function deletecourseshortcutAction()
	{
		 
		$logininfo= new Zend_Session_Namespace('Login_Info');

		if($this->getRequest()->isPost())
		{
			if(count($_POST['shortcut_id'])<1){
				$this->view->error_message=$this->view->translate('selectatleastone'); $error=1;
			}
			if($error==0)
			{
				if($logininfo->usertype=='SA')
				{
					foreach($_POST['shortcut_id'] as $key=>$val)
					{
						$mod = Doctrine::getTable('Courseshortcuts')->find($val);
						$mod->isdelete = 1;
						$mod->save();
					}

					$this->view->error_message=$this->view->translate('courseshortcutdeletedsuccessfully');
						
				}else{
						
					foreach($_POST['shortcut_id'] as $key=>$val)
					{
						$mod = Doctrine::getTable('Courseshortcuts')->find($val);
						$mod->isdelete = 1;
						$mod->save();
						$this->view->error_message=$this->view->translate('courseshortcutdeletedsuccessfully');
					}
						
				}
			}
		}
			
		$this->_helper->viewRenderer('courseshortcutlist');
	}




}

?>