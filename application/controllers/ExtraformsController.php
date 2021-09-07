<?php

class ExtraformsController extends Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function addformAction()
	{
	  
		$logininfo= new Zend_Session_Namespace('Login_Info');
		if($this->getRequest()->isPost())
		{
			$extra_form = new Application_Form_ExtraForms();

			if($extra_form->validate($_POST))
			{
				$extra_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("formadded");
			}else
			{
				$extra_form->assignErrorMessages();
			}
		}

		$cl = new Client();
		$qarray = $cl->getClientData();

		$grid = new Pms_Grid($qarray,1,count($qarray),"formclientlistcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->listclients = $grid->renderGrid();


	}

	public function editformAction()
	{
		$this->view->act="extraforms/editform?id=".$_GET['id'];
		$this->_helper->viewRenderer('addform');
	  
		if($this->getRequest()->isPost())
		{
			$extra_form = new Application_Form_ExtraForms();

			if($extra_form->validate($_POST))
			{
				$extra_form->UpdateData($_POST);
				$this->_redirect(APP_BASE."extraforms/formlist");

			}else
			{
				$extra_form->assignErrorMessages();
			}
		}


		$frm = Doctrine_Core::getTable('ExtraForms')->find($_GET['id']);
		if($frm)
		{
			$frmarr = $frm->toarray();
			$this->retainValues($frmarr);
		}

		$q = Doctrine_Core::getTable('ExtraFormsClient')->findBy('formid',$_GET['id']);

		$clarr = array();

		foreach($q->toArray() as $key=>$val)
		{
			$clarr[] = $val['clientid'];
		}

		$cl = new Client();
		$qarray = $cl->getClientData();

        usort($qarray, array(new Pms_Sorter('client_name'), "_strcmp"));//13.03.2019 bY Ancuta - sort clients alphabetic 
		$grid = new Pms_Grid($qarray,1,count($qarray),"formclientlistcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->listclients = $grid->renderGrid();
	}


	public function formlistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('healthinsurance',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
		}
	}
	 
	public function fetchlistAction()
	{

		$columnarray = array("pk"=>"id","fn"=>"formname");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$user = Doctrine_Query::create()
		->select('count(*)')
		->from('ExtraForms')
		->where('isdelete =  0')
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$userexec = $user->execute();
		$userarray = $userexec->toArray();
			
		$limit = 1000;//Maria:: Migration CISPC to ISPC 20.08.2020 
		$user->select('*');
		$user->limit($limit);
		$user->offset($_GET['pgno']*$limit);
		 
		$userlimitexec = $user->execute();
		$usserlimit = $userlimitexec->toArray();
			
		$grid = new Pms_Grid($usserlimit,1,$userarray[0]['count'],"listextraforms.html");
		$this->view->extraformsgrid = $grid->renderGrid();
		 
		$this->view->navigation = $grid->dotnavigation("extraformsnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['formlist'] =$this->view->render('extraforms/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}
	 
	private function retainValues($values)
	{

		foreach($values as $key=>$val)
		{
			if(!is_array($val))
			{
				$this->view->$key = $val;
			}
		}
	}

	public function deleteformAction()
	{
		$this->_helper->viewRenderer('formlist');
		if($this->getRequest()->isPost())
		{
			if(count($_POST['frm_id'])<1){
				$this->view->error_message=$this->view->translate('selectatleastone'); $error=2;
			}
			if($error==0)
			{
				foreach($_POST['frm_id'] as $key=>$val)
				{
					$update = Doctrine_Query::create()
					->update('ExtraForms')
					->set('isdelete', '1')
					->where('id = ?', $val);
					$update->execute();
					$this->view->error_message = $this->view->translate("formdeletedsucessfully");
				}
			}
		}
	}
	 
}

?>