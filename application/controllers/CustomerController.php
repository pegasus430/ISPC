<?php

class CustomerController extends Zend_Controller_Action
{

	public function init()
	{

		/* Initialize action controller here */
	}

	public function customerAction()
	{
		// action body
		if ($this->getRequest()->isPost())
		{
			if(strlen(trim($this->_request->getPost('name')))<1){
				$this->view->error_name = "Please provide name";$error=1;
			}
			if(strlen(trim($this->_request->getPost('address')))<1){
				$this->view->error_address = "Please provide address";$error=1;
			}
			if(strlen(trim($this->_request->getPost('gender')))<1){
				$this->view->error_gender = "Please provide gender";$error=1;
			}
			if(strlen(trim($this->_request->getPost('dob')))<1){
				$this->view->error_dob = "Please provide date of birth";$error=1;
			}

			if($error==0)
			{
					
				$conn = Doctrine_Manager::connection();
				$conn->beginTransaction();
				$cust = new Customer();
				$cust->name = $this->_request->getPost('name');
				$cust->address = $this->_request->getPost('address');
				$cust->gender = $this->_request->getPost('gender');
				$cust->dob = $this->_request->getPost('dob');
				$cust->newsletter =$this->_request->getPost('newsletter');
				$cust->save();

				$custup = Doctrine::getTable('Customer')->find($cust->id);
				$custup->username = "nadyshaikh";
				$custup->password = "password";
				$custup->save();

				if($conn->commit())
				{
					$this->view->error_message = 'Success';
				}else{
					$conn->rollback();
					$this->view->error_message = 'Customer could not be added';
				}
			}

		}
			
	}

	public function editAction()
	{
		if ($this->getRequest()->isPost())
		{
			if(strlen(trim($this->_request->getPost('name')))<1){
				$this->view->error_name = "Please provide name";$error=1;
			}
			if(strlen(trim($this->_request->getPost('address')))<1){
				$this->view->error_address = "Please provide address";$error=1;
			}
			if(strlen(trim($this->_request->getPost('gender')))<1){
				$this->view->error_gender = "Please provide gender";$error=1;
			}
			if(strlen(trim($this->_request->getPost('dob')))<1){
				$this->view->error_dob = "Please provide date of birth";$error=1;
			}

			if($error==0)
			{
				$cust = Doctrine::getTable('Customer')->find($_GET['id']);
				$cust->name = $this->_request->getPost('name');
				$cust->address = $this->_request->getPost('address');
				$cust->gender = $this->_request->getPost('gender');
				$cust->dob = $this->_request->getPost('dob');
				$cust->newsletter =$this->_request->getPost('newsletter');
				$cust->save();
					
				$this->_redirect("customer/list");
			}
		}

		$cust = Doctrine::getTable('Customer')->findById($_GET['id']);
		$this->view->custarray = $cust->toArray();

		if($this->view->custarray[0]['gender']==1)
		{

			$this->view->male = 'checked="checked"';

		}elseif($this->view->custarray[0]['gender']==2)
		{
			$this->view->female = 'checked="checked"';
		}
			
		if($this->view->custarray[0]['newsletter']==1)
		{
			$this->view->newsletter = "checked";
		}
			
	}


	public function listAction()
	{
		if($_GET['del']=='yes')
		{
			$cust = Doctrine::getTable('Customer')->find($_GET['id']);
			$cust->delete();
		}

		$cust = Doctrine::getTable('Customer')->findAll();
		$this->view->custarray = $cust->toArray();

	}

}

?>