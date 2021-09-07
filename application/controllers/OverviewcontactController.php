<?php

class OverviewcontactController extends Zend_Controller_Action
{
	 
	public function init()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->usertype = $logininfo->usertype;
	}

	public function editcontactaddressAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('editcontactaddress');

		if($this->getRequest()->isPost())
		{
			if(strlen($_POST['content'])<1){
				$this->view->error_content=$this->view->translate("enteroverviewcontactaddress"); $error=1;
			}

			if($error==0)
			{
				$import = Doctrine::getTable('OverviewContact')->find('1');
				$import->content= Pms_CommonData::aesEncrypt($_POST['content']);
				$import->save();
				 
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}else{
					
				$this->retainValues($_POST);
			}
		}

		$editnews = Doctrine_Query::create()
		->select("*,AES_DECRYPT(content,'".Zend_Registry::get('salt')."') as content")
		->from('OverviewContact')
		->where('id=1');
		$editexec = $editnews->execute();
		if($editexec)
		{
			$contarrray = $editexec->toArray();
			$this->retainValues($contarrray[0]);
		}

	}

	private function retainValues($values)
	{
		 
		foreach($values as $key=>$val)
		{
			$this->view->$key = stripslashes($val);
		}
	}
	 
}

?>
