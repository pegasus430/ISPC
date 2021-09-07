<?php

class DoctorlettersController extends Zend_Controller_Action
{


	public function init()
	{
		/* Initialize action controller here */
	}


	public function doctorletterspermissionsAction() {
		$logininfo= new Zend_Session_Namespace('Login_Info');
			

		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("DoctorLettersPermissions")
			->where("clientid= ?", $logininfo->clientid);
			$q->execute();

			if(is_array($_POST['access']))
			{

				foreach($_POST['access'] as $key=>$val)
				{
					if($val == 1){
						$fc = new DoctorLettersPermissions();
						$fc->letter= $key;
						$fc->clientid = $logininfo->clientid;
						$fc->save();
					}
				}
					
			}

			$this->_redirect(APP_BASE.'doctorletters/doctorletterspermissions');
		}

		$letters = Doctrine_Query::create()
		->select("*")
		->from("DoctorLetters")
		->where("isdelete=0");
		$lettersarray = $letters->fetchArray();
			
		$clientletters = new DoctorLettersPermissions();
		$clientletterarray = $clientletters->getClientLetters($logininfo->clientid);
		foreach($lettersarray as $key => $letter){
			$letterperm[$key] = $letter;
			$set = 0;
			foreach($clientletterarray as $perm){
				if($perm['letter'] == $letter['id']){
					$letterperm[$key]['access'] = 1;
					$set = 1;
				}
			}
			if($set == 0){
				$letterperm[$key]['access'] = false;
			}
		}
		$grid = new Pms_Grid($letterperm,1,count($letterperm),"listletters.html");
		$this->view->listletters = $grid->renderGrid();
	}

	public function addsymptomAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
			

		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}

			$split = explode(".",$_POST['entry_date']);
			$post_entry_date = $split[2]."-".$split[1]."-".$split[0];

			$symptomaster_form = new Application_Form_SymptomatologyMaster();
			if($symptomaster_form->validate($_POST))
			{
				$symptomaster_form->InsertData($_POST);

				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}else{
				$symptomaster_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if($logininfo->clientid>0)
		{
			$client=$logininfo->clientid;

			$client = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
					AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
					,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
					->from('Client')
					->where('id = ?', $client);
			$clientexec = $client->execute();
			$clientarray = $clientexec->toArray();
			$this->view->client_name = $clientarray[0]['client_name'];
			$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly">
			<input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
		}else{
			$this->view->error_message = "<div class='err'>".$this->view->translate('selectclient')."</div>";
		}
	}

	public function editsymptomAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('addsymptom');


		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
			


		if($this->getRequest()->isPost())
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE."error/previlege");
			}


			$symptomaster_form = new Application_Form_SymptomatologyMaster();
			if($symptomaster_form->validate($_POST))
			{
				$symptomaster_form->UpdateData($_POST);

				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE.'symptomatology/symptomlist?flg=suc');
			}else{
				$symptomaster_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if(strlen($_GET['id'])>0)
		{


			$alert = Doctrine::getTable('SymptomatologyMaster')->find($_GET['id']);
			$alertarray = $alert->toArray();
			$alertarray['entry_date']=date('d-m-Y',strtotime($alertarray['entry_date']));
			$this->retainValues($alertarray);


			if($clientid>0 || $logininfo->clientid>0)
			{
				if($clientid>0){
					$client=$clientid;
				}else if($logininfo->clientid>0){
					$client=$logininfo->clientid;
				}

					
				$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
						AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
						,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
						->from('Client')
						->where('id = ? ', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
			}
		}else{

			if($logininfo->clientid>0)
			{
				$client=$logininfo->clientid;

					
				$client = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
						AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
						,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
						->from('Client')
						->where('id = ?', $client);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];
				$this->view->inputbox = '<label for="client_name">'.$this->view->translate('client_name').'</label><input type="text" name="client_name" id="client_name" value="'.$clientarray[0]['client_name'].'" readonly="readonly"><input name="clientid" type="hidden" value="'.$clientarray[0]['id'].'" />';
			}
		}
	}

	public function symptomlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
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
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'canview');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($logininfo->clientid>0)
		{
			$where = ' and clientid='.$logininfo->clientid;
		}else{
			$where = ' and clientid=0';
		}
			
		$columnarray = array("pk"=>"id","ds"=>"sym_description");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];


		$symptom = Doctrine_Query::create()
		->select('count(*)')
		->from('SymptomatologyMaster')
		->andWhere('isdelete = 0 '.$where)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);

		$symptomexec = $symptom->execute();
		$symptomarray = $symptomexec->toArray();
			
		$limit = 50;
		$symptom->select('*');
		$symptom->where('isdelete = 0'.$where);
		$symptom->limit($limit);
		$symptom->offset($_GET['pgno']*$limit);
			
		$symptomlimitexec = $symptom->execute();
		$symptomlimit = $symptomlimitexec->toArray();
			
		$grid = new Pms_Grid($symptomlimit,1,$symptomarray[0]['count'],"listsymptom.html");
		$this->view->symptomgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("symptomnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['symptomlist'] = $this->view->render('symptomatology/fetchlist.html');
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


	public function deletesymptomAction()
	{
		$this->_helper->viewRenderer('symptomlist');

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientsymptomatology',$logininfo->userid,'candelete');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		if($this->getRequest()->isPost())
		{
			if(count($_POST['symptom_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}

			if($error==0)
			{
				foreach($_POST['symptom_id'] as $key=>$val)
				{
					$thrash = Doctrine::getTable('SymptomatologyMaster')->find($val);
					$thrash->isdelete = 1;
					$thrash->save();



				}
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");

			}
		}
	}



	public function setclientsAction()
	{
		if($this->getRequest()->isPost())
		{
			$q = Doctrine_Query::create()
			->delete("SymptomatologyPermissions")
			->where("setid= ?", $_GET['setid']);
			$q->execute();

			if(is_array($_POST['clientid']))
			{
				foreach($_POST['clientid'] as $key=>$val)
				{
					$fc = new SymptomatologyPermissions();
					$fc->setid= $_GET['setid'];
					$fc->clientid = $val;
					$fc->save();
				}
					
			}
			$this->_redirect(APP_BASE.'symptomatology/symptomatologypermissions');
		}

		$set = Doctrine_Query::create()
		->select('*')
		->from("SymptomatologySets")
		->where("id= ?", $_GET['setid']);
		$setarr = $set->fetchArray();

		$q = Doctrine_Core::getTable('SymptomatologyPermissions')->findBy('setid',$_GET['setid']);

		$clarr = array();

		foreach($q->toArray() as $key=>$val)
		{
			$clarr[] = $val['clientid'];
		}

		$q = Doctrine_Query::create()
		->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
				AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
				,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
				->from('Client')
				->where('isdelete=0');
		$qexec = $q->execute();
		$qarray = $qexec->toArray();
		$grid = new Pms_Grid($qarray,1,count($qarray),"clientlistcheckbox.html");
		$grid->clarr = $clarr;
		$this->view->setarr = $setarr;
		$this->view->listclients = $grid->renderGrid();
	}

}

?>
