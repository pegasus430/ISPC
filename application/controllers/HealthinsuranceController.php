<?php

	class HealthinsuranceController extends Zend_Controller_Action {

		public function init()
		{
			/* Initialize action controller here */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
		}

		public function addhealthinsuranceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->layout->setLayout('layout');

			// get PriceList
			$p_lists = new SocialCodePriceList();
			$price_lists = $p_lists->get_all_price_lists($logininfo->clientid); // get all including private lists

			$this->view->price_lists = $price_lists;

			$price_list_groups = new SocialCodePriceListGroups();
			$pricelist_groups = $price_list_groups->get_all_groups($clientid); //get all groups including private groups
			$this->view->pricelist_groups = $pricelist_groups;

			$modules = new Modules();
			if($modules->checkModulePrivileges("90", $clientid))
			{
				$this->view->show_debtor_number = 1;
			}
			else
			{
				$this->view->show_debtor_number = 0;
			}
			
			// Maria:: Migration ISPC to CISPC 08.08.2020
			// ISPC-2461 Ancuta 03.10.2019
			if($modules->checkModulePrivileges("199", $clientid))
			{
				$this->view->show_demstepcare_billing = 1;
			}
			else
			{
				$this->view->show_demstepcare_billing = 0;
			}
			//-- 

			/* ---------------------------Get HE pricelist Permissions ----------------- */
			$hecprevileges = new Modules();
			$hec = $hecprevileges->checkModulePrivileges("74", $logininfo->clientid);

			if($hec)
			{
				$shortcuts = Pms_CommonData::get_prices_shortcuts();
				foreach($shortcuts['hessen'] as $type => $sh_list)
				{
					if($type != 'sapvbe') //exclude sapvbe from he pricelist select
					{
						$he_list_types[$type] = $type;
					}
				}

				$this->view->he_list_types_array = $he_list_types;

				$he_price_list_permission = '1';
			}
			else
			{
				$he_price_list_permission = '0';
			}
			$this->view->he_price_list_permission = $he_price_list_permission;
			/* -------------------------------------------------------------------------- */

			if($this->getRequest()->isPost())
			{
				$helathinsurance_form = new Application_Form_HealthInsurance();

				if($helathinsurance_form->validate($_POST))
				{
					$helathinsurance_form->InsertData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{

					$helathinsurance_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if(strlen($_GET['id']) > 0)
			{
				$health = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$healtharray = $health->toArray();
				$clientid = $healtharray['clientid'];

				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id = ?', $clientid);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];

				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly">';
			}
			else
			{
				$client = Doctrine_Query::create()
					->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
					,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id = ?', $clientid);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];

				$this->view->inputbox .= '<select name="client_name" >';
				foreach($clientarray as $key => $val)
				{

					$this->view->inputbox .='<option value="' . $val['id'] . '">' . $val['client_name'] . '</option>';
				}
				$this->view->inputbox .= '</select>';
			}
		}

		public function edithealthinsuranceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('addhealthinsurance');

			// get PriceList
			$p_lists = new SocialCodePriceList();
			$price_lists = $p_lists->get_all_price_lists($logininfo->clientid); // get all including private lists

			$this->view->price_lists = $price_lists;

			$price_list_groups = new SocialCodePriceListGroups();
			$pricelist_groups = $price_list_groups->get_all_groups($clientid); //get all groups including private groups
			$this->view->pricelist_groups = $pricelist_groups;

			$modules = new Modules();
			if($modules->checkModulePrivileges("90", $clientid))
			{
				$this->view->show_debtor_number = 1;
			}
			else
			{
				$this->view->show_debtor_number = 0;
			}

			// ISPC-2461 Ancuta 03.10.2019
			if($modules->checkModulePrivileges("199", $clientid))
			{
			    $this->view->show_demstepcare_billing = 1;
			}
			else
			{
			    $this->view->show_demstepcare_billing = 0;
			}
			// 
			/* ---------------------------Get HE pricelist Permissions ----------------- */
			$hecprevileges = new Modules();
			$hec = $hecprevileges->checkModulePrivileges("74", $logininfo->clientid);

			if($hec)
			{
				$shortcuts = Pms_CommonData::get_prices_shortcuts();

				foreach($shortcuts['hessen'] as $type => $sh_list)
				{
					if($type != 'sapvbe') //exclude sapvbe from he pricelist select
					{
						$he_list_types[$type] = $type;
					}
				}

				$this->view->he_list_types_array = $he_list_types;

				$he_price_list_permission = '1';
			}
			else
			{
				$he_price_list_permission = '0';
			}
			$this->view->he_price_list_permission = $he_price_list_permission;
			/* -------------------------------------------------------------------------- */

			if($this->getRequest()->isPost())
			{
				$hinsu_form = new Application_Form_HealthInsurance();

				if($hinsu_form->validate($_POST))
				{
					//print_r($_POST);
					$hinsu_form->UpdateData($_POST);
					$this->_redirect(APP_BASE . 'healthinsurance/healthinsurancelist?flg=suc');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}
				else
				{
					$hinsu_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
			if(strlen($_GET['id']) > 0)
			{
				$helathins = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$healtharray = $helathins->toArray();
				$healtharray['valid_from'] = date('d-m-Y', strtotime($healtharray['valid_from']));
				$healtharray['valid_till'] = date('d-m-Y', strtotime($healtharray['valid_till']));
				$this->retainValues($healtharray);

				$clientid = $healtharray['clientid'];
				if($clientid > 0 || $logininfo->clientid > 0)
				{
					if($clientid > 0)
					{
						$client = $clientid;
					}
					else if($logininfo->clientid > 0)
					{
						$client = $logininfo->clientid;
					}


					$client = Doctrine_Query::create()
						->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname
						,AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
						->from('Client')
						->where('id=' . $client);
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}
			}
			else
			{

			}
		}

		public function healthinsurancelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "nm" => "name", "nm2" => "name2");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
			$health = Doctrine_Query::create()
				->select('count(*)')
				->from('HealthInsurance');
				$health->where("isdelete= ?", 0);
				$health->andWhere("extra = ?", 0);
				$health->andWhere("onlyclients = ?",0);
				if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
				{
					$health->andWhere("(name like ? or  kvnumber like ?)", array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
				}
			$health ->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$healthexec = $health->execute();
			$healtharray = $healthexec->toArray();

			$limit = 50;
			$health->select('*');
			$health->where("isdelete= ?", 0);
			$health->andWhere("extra = ?", 0);
			$health->andWhere("onlyclients = ?",0);
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$health->andWhere("(name like ? or  kvnumber like ?)", array("%" . trim($_REQUEST['val']) . "%","%" . trim($_REQUEST['val']) . "%"));
			}
			$health->limit($limit);
			$health->offset($_REQUEST['pgno'] * $limit);

			$healthlimitexec = $health->execute();
			$healthlimit = $healthlimitexec->toArray();
			$grid = new Pms_Grid($healthlimit, 1, $healtharray[0]['count'], "listhealthinsurance.html");
			$this->view->healthinsurancegrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("healthnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['healthinsurancelist'] = utf8_encode($this->view->render('healthinsurance/fetchlist.html'));
			echo json_encode($response);
			exit;
		}

		public function getjsondataAction()
		{
			if($_GET['cid'] > 0)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('clientid= ? ', $_GET['cid'])
					->andWhere('isdelete= ?', 0);

				$track = $user->execute();
				echo json_encode($track->toArray());
				exit;
			}
			else
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isdelete = ?', 0)
					->andWhere('usertype != ?', 'SA');
				$track = $user->execute();
				echo json_encode($track->toArray());
				exit;
			}
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		public function deletehealthinsuranceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->_helper->viewRenderer('healthinsurancelist');

			$ids = split(",", $_GET['id']);

			for($i = 0; $i < sizeof($ids); $i++)
			{
				$thrash = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			}
		}

		public function deleteclienthealthinsuranceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			//$this->_helper->viewRenderer('clienthealthinsurancelist');

			//get health insurance subdivizions
			$symperm = new HealthInsurancePermissions();
			$divisions = $symperm->getClientHealthInsurancePermissions($logininfo->clientid);
			$this->view->divisions = $divisions;

			$ids = split(",", $_GET['id']);

			for($i = 0; $i < sizeof($ids); $i++)
			{
				if(!empty($divisions))
				{
					$ph = Doctrine_Query::create()
						->update('HealthInsurance2Subdivisions')
						->set("isdelete", "1")
						->where("company_id= ?", $_GET['id'] )
						->andWhere("patientonly = 0 ")
						->andWhere("onlyclients = 1 ");
					$ph->execute();
				}

				$thrash = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE . 'healthinsurance/clienthealthinsurancelist?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}

		public function addclienthealthinsurannceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->_helper->layout->setLayout('layout');

			// get PriceList
			$p_lists = new SocialCodePriceList();
			$price_lists = $p_lists->get_all_price_lists($logininfo->clientid); // get all including private lists
			$this->view->price_lists = $price_lists;

			$price_list_groups = new SocialCodePriceListGroups();
			$pricelist_groups = $price_list_groups->get_all_groups($clientid); //get all groups including private groups
			$this->view->pricelist_groups = $pricelist_groups;


			$modules = new Modules();
			if($modules->checkModulePrivileges("90", $clientid))
			{
				$this->view->show_debtor_number = 1;
			}
			else
			{
				$this->view->show_debtor_number = 0;
			}
			
			//ISPC-2452 Ancuta 21.11.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
			if($modules->checkModulePrivileges("204", $clientid))
			{
				$this->view->generate_debtor_number = 1;
			}
			else
			{
				$this->view->generate_debtor_number  = 0;
			}
			//--
			

			// ISPC-2461 Ancuta 03.10.2019
			if($modules->checkModulePrivileges("199", $clientid))
			{
			    $this->view->show_demstepcare_billing = 1;
			}
			else
			{
			    $this->view->show_demstepcare_billing = 0;
			}
			// --
			/* ---------------------------Get HE pricelist Permissions ----------------- */
			$hecprevileges = new Modules();
			$hec = $hecprevileges->checkModulePrivileges("74", $logininfo->clientid);

			if($hec)
			{
				$shortcuts = Pms_CommonData::get_prices_shortcuts();

				foreach($shortcuts['hessen'] as $type => $sh_list)
				{
					if($type != 'sapvbe') //exclude sapvbe from he pricelist select
					{
						$he_list_types[$type] = $type;
					}
				}

				$this->view->he_list_types_array = $he_list_types;


				$he_price_list_permission = '1';
			}
			else
			{
				$he_price_list_permission = '0';
			}
			$this->view->he_price_list_permission = $he_price_list_permission;
			/* -------------------------------------------------------------------------- */

			//get health insurance subdivizions
			$symperm = new HealthInsurancePermissions();
			$divisions = $symperm->getClientHealthInsurancePermissions($logininfo->clientid);
			$this->view->divisions = $divisions;

			if($this->getRequest()->isPost())
			{
				$helathinsurance_form = new Application_Form_HealthInsurance();

				if($helathinsurance_form->validate($_POST, true))
				{

					$_POST['subdivizions_permissions'] = $divisions;
					$helathinsurance_form->InsertClientData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$helathinsurance_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if(strlen($_GET['id']) > 0)
			{
				$health = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$healtharray = $health->toArray();
				$clientid = $healtharray['clientid'];

				$client = Doctrine_Query::create()
					->select("*,
					AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id =' . $clientid);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];

				$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly">';
			}
			else
			{
				$client = Doctrine_Query::create()
					->select("*,
					AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
					->from('Client')
					->where('id =' . $clientid);
				$clientexec = $client->execute();
				$clientarray = $clientexec->toArray();
				$this->view->client_name = $clientarray[0]['client_name'];

				$this->view->inputbox .= '<select name="client_name" >';
				foreach($clientarray as $key => $val)
				{

					$this->view->inputbox .='<option value="' . $val['id'] . '">' . $val['client_name'] . '</option>';
				}
				$this->view->inputbox .= '</select>';
			}
		}

		public function clienthealthinsurancelistoldAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_REQUEST['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}
			else if($_REQUEST['flg'] == 'sucdel')
			{
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			}
		}

		public function fetchclienthilistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('healthinsurance', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "nm" => "name", "nm2" => "name2");
			$this->view->{"style" . $_GET['pgno']} = "active";
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];
			$health = Doctrine_Query::create()
				->select('count(*)')
				->from('HealthInsurance')
				->where('isdelete = ?', 0)
				->andWhere('extra = 0')
				->andWhere("clientid = '" . $clientid . "'")
				->andWhere("onlyclients = '1'");
				if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
				{
					$health->andWhere("(name like '%" . trim($_REQUEST['val']) . "%' or  kvnumber like '%" . trim($_REQUEST['val']) . "%')");
				}				
				$health->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			$healtharray = $health->fetchArray();

			$limit = 50;
			$health->select('*')
			->where("isdelete=0")
			->andWhere('extra = 0')
			->andWhere("clientid='" . $clientid . "'")
			->andWhere("onlyclients = '1'");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$health->andWhere("(name like '%" . trim($_REQUEST['val']) . "%' or  kvnumber like '%" . trim($_REQUEST['val']) . "%')");
			}
			$health->limit($limit);
			$health->offset($_REQUEST['pgno'] * $limit);

			$healthlimit = $health->fetchArray();

			$grid = new Pms_Grid($healthlimit, 1, $healtharray[0]['count'], "clientlisthealthinsurance.html");
			$this->view->healthinsurancegrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("clienthealthnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['healthinsurancelist'] = utf8_encode($this->view->render('healthinsurance/fetchclienthilist.html'));


			echo json_encode($response);
			exit;
		}

		public function editchealthinsuranceAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$this->_helper->viewRenderer('addclienthealthinsurannce');
			// get PriceList
			$p_lists = new SocialCodePriceList();
			$price_lists = $p_lists->get_all_price_lists($clientid); // get all including private lists

			$this->view->price_lists = $price_lists;


			$price_list_groups = new SocialCodePriceListGroups();
			$pricelist_groups = $price_list_groups->get_all_groups($clientid); //get all groups including private groups
			$this->view->pricelist_groups = $pricelist_groups;

			$modules = new Modules();
			if($modules->checkModulePrivileges("90", $clientid))
			{
				$this->view->show_debtor_number = 1;
			}
			else
			{
				$this->view->show_debtor_number = 0;
			}

			//ISPC-2452 Ancuta 21.11.2019// Maria:: Migration ISPC to CISPC 08.08.2020
			if($modules->checkModulePrivileges("204", $clientid))
			{
			    $this->view->generate_debtor_number = 1;
			}
			else
			{
			    $this->view->generate_debtor_number  = 0;
			}
			//--			
			
			// ISPC-2461 Ancuta 03.10.2019
			if($modules->checkModulePrivileges("199", $clientid))
			{
			    $this->view->show_demstepcare_billing = 1;
			}
			else
			{
			    $this->view->show_demstepcare_billing = 0;
			}
			// --
			/* ---------------------------Get HE pricelist Permissions ----------------- */
			$hecprevileges = new Modules();
			$hec = $hecprevileges->checkModulePrivileges("74", $logininfo->clientid);

			if($hec)
			{

				$shortcuts = Pms_CommonData::get_prices_shortcuts();
				foreach($shortcuts['hessen'] as $type => $sh_list)
				{
					if($type != 'sapvbe') //exclude sapvbe from he pricelist select
					{
						$he_list_types[$type] = $type;
					}
				}

				$this->view->he_list_types_array = $he_list_types;


				$he_price_list_permission = '1';
			}
			else
			{
				$he_price_list_permission = '0';
			}

			$this->view->he_price_list_permission = $he_price_list_permission;
			/* -------------------------------------------------------------------------- */

			// get subdivizions details
			$symperm = new HealthInsurancePermissions();
			$divisions = $symperm->getClientHealthInsurancePermissions($logininfo->clientid);
			$this->view->divisions = $divisions;

			if(!empty($divisions))
			{
				$hi2s = Doctrine_Query::create()
					->select("*")
					->from("HealthInsurance2Subdivisions")
					->where("company_id = ?", $_GET['id'] );
				$hi2s_arr = $hi2s->fetchArray();

				foreach($hi2s_arr as $skey => $subdiv_details)
				{
					$subdivizion_details[$subdiv_details['subdiv_id']] = $subdiv_details;
				}

				$this->view->subdivizion_details = $subdivizion_details;
			}

			if($this->getRequest()->isPost())
			{
				$hinsu_form = new Application_Form_HealthInsurance();

				if($hinsu_form->validate($_POST, true))
				{

					$_POST['subdivizions_permissions'] = $divisions;
					$hinsu_form->UpdateData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'healthinsurance/clienthealthinsurancelist?flg=suc&mes='.urlencode($this->view->error_message));
				}
				else
				{
					$hinsu_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if(strlen($_GET['id']) > 0)
			{
				$helathins = Doctrine::getTable('HealthInsurance')->find($_GET['id']);
				$healtharray = $helathins->toArray();
				$healtharray['valid_from'] = date('d-m-Y', strtotime($healtharray['valid_from']));
				$healtharray['valid_till'] = date('d-m-Y', strtotime($healtharray['valid_till']));

				$this->retainValues($healtharray);

				$clientid = $healtharray['clientid'];
				if($clientid > 0 || $logininfo->clientid > 0)
				{
					if($clientid > 0)
					{
						$client = $clientid;
					}
					else if($logininfo->clientid > 0)
					{
						$client = $logininfo->clientid;
					}


					$client = Doctrine_Query::create()
						->select("*,
						AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
						AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
						AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
						AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
						AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
						AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
						AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
						AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
						AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone")
						->from('Client')
						->where('id=' . $client);
					$clientexec = $client->execute();
					$clientarray = $clientexec->toArray();
					$this->view->client_name = $clientarray[0]['client_name'];
					$this->view->inputbox = '<input type="text" name="client_name" id="client_name" value="' . $clientarray[0]['client_name'] . '" readonly="readonly"><input name="clientid" type="hidden" value="' . $clientarray[0]['id'] . '" />';
				}
			}
			else
			{

			}
		}

		public function addressbookinsuranceAction()
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($decid);

			$ph = new PatientHealthInsurance();
			$phi = $ph->getPatientHealthInsurance($ipid);

			if($phi)
			{
				if(empty($phi[0]['institutskennzeichen']) || $phi[0]['institutskennzeichen'] == 0)
				{
					if(!empty($phi[0]['companyid']) && $phi[0]['companyid'] != 0)
					{
						$helathins = Doctrine::getTable('HealthInsurance')->find($phi[0]['companyid']);
						$healtharray = $helathins->toArray();
						$institutskennzeichen = $healtharray['iknumber'];
						$phi[0]['institutskennzeichen'] = $institutskennzeichen;
					}
				}
				if(!empty($phi[0]['companyid']) && $phi[0]['companyid'] != 0)
				{
					$helathins = Doctrine::getTable('HealthInsurance')->find($phi[0]['companyid']);
					$healtharray = $helathins->toArray();

					if(empty($phi[0]['ins_street']))
					{
						$phi[0]['ins_street'] = $healtharray['street1'];
					}
					if(empty($phi[0]['ins_city']))
					{
						$phi[0]['ins_city'] = $healtharray['city'];
					}
					if(empty($phi[0]['ins_zip']))
					{
						$phi[0]['ins_zip'] = $healtharray['zip'];
					}
				}

				$grid = new Pms_Grid($phi, 1, count($phi), "addressbookinsurance.html");

				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "callBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['patientlist'] = utf8_encode($grid->renderGrid());

				echo json_encode($response);
				exit;
			}
		}

		public function healthinsurancepermissionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();

			if($this->getRequest()->isPost())
			{

				$q = Doctrine_Query::create()
					->delete("HealthInsurancePermissions")
					->where("clientid='" . $logininfo->clientid . "'");
				$q->execute();

				foreach($_POST['access'] as $key => $val)
				{
					if($val == 1)
					{
						$fc = new HealthInsurancePermissions();
						$fc->subdiv_id = $key;
						$fc->subdiv_order = $_POST['order'][$key];
						$fc->clientid = $logininfo->clientid;
						$fc->save();
					}
				}
			}

			$sets = Doctrine_Query::create()
				->select("*")
				->from("HealthInsuranceSubdivisions")
				->where("isdelete=0");
			$setarr = $sets->fetchArray();

			$symperm = new HealthInsurancePermissions();
			$perms = $symperm->getClientHealthInsurancePermissions($logininfo->clientid);

			if(!$perms)
			{
				$this->view->set_default = 1;
			}

			foreach($setarr as $key => $sympset)
			{
				$setperm[$key] = $sympset;
				$set = 0;
				foreach($perms as $perm)
				{
					if($perm['subdiv_id'] == $sympset['id'])
					{
						$setperm[$key]['access'] = 1;
						$setperm[$key]['order'] = $perm['subdiv_order'];
						$set = 1;
					}
				}
				if($set == 0)
				{
					$setperm[$key]['access'] = false;
					$setperm[$key]['order'] = false;
				}
			}

			$this->view->listsetspermissions = $setperm;
		}

		public function healthinsurancesubdivisionsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();

			if($this->getRequest()->isPost())
			{
				$fc = new HealthInsuranceSubdivisions();
				$fc->name = $_POST['name'];
				$fc->save();


				$this->_redirect(APP_BASE . 'healthinsurance/healthinsurancesubdivisions');
			}


			$sets = Doctrine_Query::create()
				->select("*")
				->from("HealthInsuranceSubdivisions")
				->where("isdelete=0");
			$setarr = $sets->fetchArray();

			$symperm = new HealthInsurancePermissions();
			$perms = $symperm->getClientHealthInsurancepermissions($logininfo->clientid);
			foreach($setarr as $key => $sympset)
			{
				$subdiv[$key] = $sympset;
				$set = 0;
				foreach($perms as $perm)
				{
					if($perm['subdiv_id'] == $sympset['id'])
					{
						$subdiv[$key]['access'] = 1;
						$subdiv[$key]['order'] = $perm['subdiv_order'];
						$set = 1;
					}
				}
				if($set == 0)
				{
					$subdiv[$key]['access'] = false;
					$subdiv[$key]['order'] = false;
				}
			}
			$this->view->subdiv = $subdiv;
		}

		public function copyAction(){
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $this->view->clientarray = Pms_CommonData::getAllClientsDD ();
		    
		    
		    if($this->getRequest()->isPost())
		    {
		        $source  = $_POST['source'];
		        $target  = $_POST['target'];
		        
		        if($_POST['delete_target'] == "1"){
		            
		            $ph = Doctrine_Query::create()
		            ->update('HealthInsurance')
		            ->set("isdelete", "1")
		            ->set('change_date','?',date("Y-m-d H:i:s"))
		            ->set('change_user','?',$userid)
		            ->where("clientid= ?", $target)
		            ->andWhere("extra = 0 ")
		            ->andWhere("onlyclients = 1 ");
		            $ph->execute();
		            
		            $phs = Doctrine_Query::create()
		            ->update('HealthInsurance2Subdivisions')
		            ->set("isdelete", "1")		            
		            ->set('change_date','?', date("Y-m-d H:i:s"))
		            ->set('change_user','?', $userid)
		            ->where("clientid= ?", $target)
		            ->andWhere("patientonly = 0 ")
		            ->andWhere("onlyclients = 1 ");
		            $phs->execute();
		        }
		        
		        
		        if($source != '0' && $target != '0' ){
		            
        		    $hinsu = Doctrine_Query::create()
        		    ->select('*')
        		    ->from('HealthInsurance')
        		    ->where("clientid= ?", $source)
        		    ->andWhere('extra = 0')
        		    ->andWhere("onlyclients = '1'");
        		    $hinsuarray = $hinsu->fetchArray();
        		    
        		    $company_ids[] = "99999999";
        	        foreach($hinsuarray as $key => $val)
        	        {
                        $company_ids[]  = $val['id'];	        
                        $master_hi[$val['id']] = $val;      
        	        }
        		    
        		    $hi2s = Doctrine_Query::create()
        		    ->select("*")
        		    ->from("HealthInsurance2Subdivisions")
        		    ->whereIn("company_id",  $company_ids);
        		    $hi2s_arr = $hi2s->fetchArray();
        		    
        		    
        		    foreach($hi2s_arr as $skey => $subdiv_details)
        		    {
        		        $subdivizion[$subdiv_details['company_id']][] = $subdiv_details;
        		    }
        		    
        		    if(count($hinsuarray) > 0)
        		    {
        		        foreach($master_hi as $hi_id => $val)
        		        {
        		            $hinsu = new HealthInsurance();
        		            $hinsu->clientid = $target;
        		            $hinsu->name = $val['name'];
        		            $hinsu->name2 = $val['name2'];
        		            $hinsu->insurance_provider = $val['insurance_provider'];
        		            $hinsu->street1 = $val['street1'];
        		            $hinsu->street2 = $val['street2'];
        		            $hinsu->zip = $val['zip'];
        		            $hinsu->city = $val['city'];
        		            $hinsu->phone = $val['phone'];
        		            $hinsu->phone2 = $val['phone2'];
        		            $hinsu->phonefax = $val['phonefax'];
        		            $hinsu->post_office_box = $val['post_office_box'];
        		            $hinsu->post_office_box_location = $val['post_office_box_location'];
        		            $hinsu->email = $val['email'];
        		            $hinsu->zip_mailbox = $val['zip_mailbox'];
        		            $hinsu->kvnumber = $val['kvnumber'];
        		            $hinsu->iknumber = $val['iknumber'];
        // 		            $hinsu->debtor_number = $val['debtor_number'];
        		            $hinsu->comments = $val['comments'];
        		            $hinsu->extra = $val['extra'];
        		            $hinsu->onlyclients = $val['onlyclients'];
        		            $hinsu->valid_from = date("Y-m-d", time());
        		            $hinsu->save();
        		            
        		            $new_hi_id = $hinsu->id;
        		            
        		            $insert_data['health_insurance'][]  = $new_hi_id ;
        		            
        		            if(!empty($subdivizion[$hi_id])){
        		                foreach($subdivizion[$hi_id] as $k=>$sv){
        		                    
        		                    $hinsus = new HealthInsurance2Subdivisions();
        		                    $hinsus->clientid = $target;
        		                    $hinsus->company_id = $new_hi_id;
        		                    $hinsus->subdiv_id = $sv['subdiv_id'];
        		                    $hinsus->name = $sv['name'];
        		                    $hinsus->insurance_provider = $sv['insurance_provider'];
        		                    $hinsus->name2 = $sv['name2'];
        		                    $hinsus->contact_person = $sv['contact_person'];
        		                    $hinsus->street1 = $sv['street1'];
        		                    $hinsus->street2 = $sv['street2'];
        		                    $hinsus->zip = $sv['zip'];
        		                    $hinsus->city = $sv['city'];
        		                    $hinsus->phone = $sv['phone'];
        		                    $hinsus->phone2 = $sv['phone2'];
        		                    $hinsus->post_office_box = $sv['post_office_box'];
        		                    $hinsus->post_office_box_location = $sv['post_office_box_location'];
        		                    $hinsus->zip_mailbox = $sv['zip_mailbox'];
        		                    $hinsus->fax = $sv['fax'];
        		                    $hinsus->email = $sv['email'];
        		                    $hinsus->iknumber = $sv['iknumber'];
        		                    $hinsus->kvnumber = $sv['kvnumber'];
        		                    $hinsus->ikbilling = $sv['ikbilling'];
        // 		                    $hinsus->debtor_number = $sv['debtor_number'];
        		                    $hinsus->comments = $sv['comments'];
        		                    $hinsus->patientonly = $sv['patientonly'];
        		                    $hinsus->onlyclients = $sv['onlyclients'];
        		                    $hinsus->valid_from = date("Y-m-d", time());
        		                    $hinsus->save();
        		                    
        		                    $new_s_id = $hinsus->id;
        		                    
        		                    $insert_data['subdivistions'][]  = $new_s_id ;
        		                }
        		            }
        		        }
        		    }
        		    
        		    echo "health insurance inserted:". count($insert_data['health_insurance']);
        		    echo "<br/>";
        		    echo "subdivistion inserted:". count($insert_data['subdivistions']);
                }
		    }
		}
		
		//get view list client healthinsurance
		public function clienthealthinsurancelistAction(){
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {					
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				if(!$_REQUEST['length']){
					$_REQUEST['length'] = "25";
				}
				$limit = (int)$_REQUEST['length'];
				$offset = (int)$_REQUEST['start'];
				$search_value = addslashes($_REQUEST['search']['value']);
		
				$columns_array = array(
						"0" => "name",
						"1" => "kvnumber"
				);
				$columns_search_array = $columns_array;
				
				if(isset($_REQUEST['order'][0]['column']))
				{
					$order_column = $_REQUEST['order'][0]['column'];
					$order_dir = $_REQUEST['order'][0]['dir'];
				}
				else
				{
					array_push($columns_array, "id");
					$nrcol = array_search ('id', $columns_array);
					$order_column = $nrcol;
					$order_dir = "ASC";
				}
				
				$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
				// ########################################
				// #####  Query for count ###############
				$fdoc1 = Doctrine_Query::create();
				$fdoc1->select('count(*)');
				$fdoc1->from('HealthInsurance');
				$fdoc1->where("clientid = ?", $clientid);
				$fdoc1->andWhere("isdelete = 0 ");
				$fdoc1->andWhere('extra = 0');
				$fdoc1->andWhere("onlyclients = '1'");
				
				$fdocarray = $fdoc1->fetchArray();
				$full_count  = $fdocarray[0]['count'];
					
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen(trim($search_value)) > 0)
				{
					$comma = '';
					$filter_string_all = '';
					
					foreach($columns_search_array as $ks=>$vs)
					{
						$filter_string_all .= $comma.$vs;
						$comma = ',';
					}			
				
					$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp);
					
					$searchstring = mb_strtolower(trim($search_value), 'UTF-8');
					$searchstring_input = trim($search_value);
					if(strpos($searchstring, 'ae') !== false || strpos($searchstring, 'oe') !== false || strpos($searchstring, 'ue') !== false)
					{
						if(strpos($searchstring, 'ss') !== false)
						{
							$ss_flag = 1;
						}
						else
						{
							$ss_flag = 0;
						}
						$regexp = Pms_CommonData::complete_patternation($searchstring_input, $regexp, $ss_flag);
					}
					
					$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
					$regexp_arr[] = $regexp;
					
					//var_dump($regexp_arr);
					$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
					//$search_value = strtolower($search_value);
					//$fdoc1->andWhere("(lower(name) like ? or lower(kvnumber) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
				}
					
				$fdocarray = $fdoc1->fetchArray();
				$filter_count  = $fdocarray[0]['count'];
					
				// ########################################
				// #####  Query for details ###############
				$fdoc1->select('*');
				
				$fdoc1->orderBy($order_by_str);
				$fdoc1->limit($limit);
				$fdoc1->offset($offset);
					
				$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
					
				$report_ids = array();
				$fdoclimit_arr = array();
				foreach ($fdoclimit as $key => $report)
				{
					$fdoclimit_arr[$report['id']] = $report;
					$report_ids[] = $report['id'];
				}
					
				$row_id = 0;
				$link = "";
					
				$resulted_data = array();
				foreach($fdoclimit_arr as $report_id =>$mdata)
				{
					$link = '%s';
					$resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
					$resulted_data[$row_id]['kvnumber'] = sprintf($link,$mdata['kvnumber']);
		
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'healthinsurance/editchealthinsurance?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
					$row_id++;
				}
				
				$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $filter_count; // ??
				$response['data'] = $resulted_data;
		
				$this->_helper->json->sendJson($response);
			}
				
		}
		

		public function updatedebitorAction(){ // Maria:: Migration ISPC to CISPC 08.08.2020
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    if($logininfo->usertype != 'SA')
		    {
		        die(" normal Ben ? ");
		    }
 
		    
		    $clientid = $logininfo->clientid;
		    $client_data = Client::getClientDataByid($clientid);
		    
		    $this->view->client_data = $client_data[0];
		    $hi_obj = new HealthInsurance();
		    
		    if($this->getRequest()->isPost()){
		         
		        // first get al health insurance of client
		        $healtharray_del_arr= array();
		        $health_del = Doctrine_Query::create()
		        ->select('*')
		        ->from('HealthInsurance');
		        $health_del->where("isdelete= ?", 0);
		        $health_del->andWhere("clientid = ?", $clientid);
		        $health_del->andWhere("extra = ?", 0);
		        $health_del->andWhere("onlyclients = ?",1);
		        $health_del->orderBy("name ASC");
		        $healtharray_del_arr = $health_del->fetchArray();
		      
		        
		        // ###################################################################
		        // DELETE ALL all debitor numbers of master health insurance OF CLIENT
		        // ##################################################################
		        
		        foreach($healtharray_del_arr as $k_Del=>$master_hidata_delete){
		            $q_Del = Doctrine_Query::create()
		            ->update('HealthInsurance')
		            ->set('debtor_number', "''" )
		            ->set('change_date', "?" , "'".$master_hidata_delete['change_date']."'" )
		            ->set('change_user',"?", "'".$master_hidata_delete['change_user']."'" )
		            ->where("id = ?", $master_hidata_delete['id'])
		            ->andWhere("clientid = ?", $clientid)
		            ->andWhere("extra = ?", 0)
		            ->andWhere("onlyclients = ?",1);
		            $q_Del->execute();
		            
		        }
		        
		       
		        // GET ALL DATA AGAIN
		        
		        $healtharray= array();
		        $health = Doctrine_Query::create()
		        ->select('*')
		        ->from('HealthInsurance');
		        $health->where("isdelete= ?", 0);
		        $health->andWhere("clientid = ?", $clientid);
		        $health->andWhere("extra = ?", 0);
		        $health->andWhere("onlyclients = ?",1);
		        $health->orderBy("name ASC");
		        $healtharray = $health->fetchArray();
		        
		        // ##################################################################
		        // Update All with NEW debitor numbers
		        // ##################################################################		        
		        
		        $company_id = "";
		        $debitor_number = 0;
		        $all_hi = array();
		        $hi_info_arr = array();
		        foreach($healtharray as $k=>$master_hidata){
		            $company_id = $master_hidata['id'];
		            
		            
		            $hi_info_arr[$company_id][] = $master_hidata['name'];
		            // get client debitor number 
                    $client_hi_debitornr_data = $hi_obj ->generate_hi_debitor_number($clientid);
                    if(!empty($client_hi_debitornr_data)){
                        $debitor_number = $client_hi_debitornr_data['hi_debitor_number'];
                    }
                    
        
		            if(!empty($debitor_number) && !empty($company_id))
		            {
		                
                        // update each health insurance with NEW 
    		            $q = Doctrine_Query::create()
    		            ->update('HealthInsurance')
    		            ->set('debtor_number', "?" , $debitor_number)
                        ->set('change_date', "?" , "'".$master_hidata['change_date']."'" )
                        ->set('change_user',"?", "'".$master_hidata['change_user']."'" )
    		            ->where("id = ?", $master_hidata['id'])
    		            ->andWhere("clientid = ?", $clientid)
    		            ->andWhere("extra = ?", 0)
    		            ->andWhere("onlyclients = ?",1);
    		            $q->execute();

    		            $hi_info_arr[$company_id][] =  'new_debitor: '.$debitor_number;
    		            
    		            // Check if  patients need to be updated aswell
    		            if(isset($_POST['update_company_in_patients']) && $_POST['update_company_in_patients'] == '1')
    		            {
    		                
    		                // get all patients that have current company saved
    		                $ph = new PatientHealthInsurance();
    		                $patients2company = $ph->get_client_hicompany_patients($clientid, $company_id);

    		                
    		                // UPDATE companies in patients
    		                $epidStr = "";
   		                    $change_pat = array();
    		                if(!empty($patients2company))
    		                {
    		                    foreach($patients2company as $k=>$ph_info)
    		                    {
    		                        if(trim($master_hidata['name']) == trim($ph_info['company_name']))
    		                        {
    		                            $q = Doctrine_Query::create()
    		                            ->update('PatientHealthInsurance')
    		                            ->set('ins_debtor_number', "?" , Pms_CommonData::aesEncrypt($debitor_number))
    		                            ->set('change_date', "?","'".$ph_info['change_date']."'" )
    		                            ->set('change_user', "?","'".$ph_info['change_user']."'" )
    		                            ->where("ipid = ?", $ph_info['ipid'])
    		                            ->andWhere("companyid = ?", $company_id)
    		                            ;
    		                            $q->execute();
    		                            $change_pat[]= $ph_info['epid'];
    		                            
    		                        }
    		                    }
    		                    $epidStr = implode(', ',$change_pat);
                                $hi_info_arr[$company_id][] =  'Company debitor number updated in: '.count($change_pat).' pateints <br/>'.$epidStr;
    		                }
    		                
    		            }
    		            
    		            
                        $all_hi[] =  implode(' ',$hi_info_arr[$company_id]);
                    }
		        }
		        
		        echo "DONE<br/>";
		        echo '<div style="display: block">';
		        echo implode("<hr/><br/>",$all_hi);
		        echo "</div>";
		        
		        
		        
		        
		    }
		    
		}
		
	}

?>