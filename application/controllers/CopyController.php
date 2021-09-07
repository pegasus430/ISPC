<?php

	class CopyController extends Zend_Controller_Action {

	    public $act;
	    
	    public function init()
	    {
	        /* Initialize action controller here */
	    }
	    
	    private function retainValues($values)
	    {
	        foreach($values as $key => $val)
	        {
	            $this->view->$key = $val;
	        }
	    }
	    
	    
	    
	    
	    public function copyAction()
	    {
            $client = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
	        ->from("Client")
	        ->where('isdelete=0')
            ->orderBy("AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') ASC");
	        $client_array_q = $client->fetchArray();
	        
	        $client_array = array("0" => $this->view->translate('selectclient'));
	        
	        foreach($client_array_q  as $key => $val)
	        {
	            $client_array[$val['id']] = $val['client_name'];
	        }
	        $this->view->clientarray = $client_array;
	        
	        
	        if($this->getRequest()->isPost())
	        {
	            $copy_form = new Application_Form_Copy();
	            if($copy_form->validate($_POST))
	            {
	                $copy_form->copy_data($_POST);
	                $this->view->error_message = $this->view->translate("recordinsertsucessfully");
	                $this->_redirect(APP_BASE . 'copy/copy?flg=succ'); 
	            }
	            else
	            {
	                $copy_form->assignErrorMessages();
	                $this->retainValues($_POST);
	            }
	        }    
	    }
	    
	    
	    public function updateexistinglistsAction()
	    {
            $client = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
	        ->from("Client")
	        ->where('isdelete=0')
            ->orderBy("AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') ASC");
	        $client_array_q = $client->fetchArray();
	        
	        $client_array = array("0" => $this->view->translate('selectclient'));
	        
	        foreach($client_array_q  as $key => $val)
	        {
	            $client_array[$val['id']] = $val['client_name'];
	        }
	        $this->view->clientarray = $client_array;
	        
	        
	        if($this->getRequest()->isPost())
	        {
	        
	            $copy_form = new Application_Form_Copy();
	            if($copy_form->validate($_POST))
	            {
	                $copy_form->update_data($_POST);
	                $this->view->error_message = $this->view->translate("recordinsertsucessfully");
	                $this->_redirect(APP_BASE . 'copy/updateexistinglists');
	            }
	            else
	            {
	                $copy_form->assignErrorMessages();
	                $this->retainValues($_POST);
	            }
	        }    
	    }
	}
?>