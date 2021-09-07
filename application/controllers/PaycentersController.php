<?php

	class PaycentersController extends Zend_Controller_Action {

		public $act;

		public function paycentersAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;


			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			}

			//add/edit save START
			$paycenter_form = new Application_Form_Paycenters();
			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$post['client'] = $clientid;

				//pcid = paycenter id
				if($_REQUEST['pcid'] > '0')
				{
					//edit
					$post['pcid'] = $_REQUEST['pcid'];
					$update = $paycenter_form->update($post);
				}
				else
				{
					//add
					$insert = $paycenter_form->insert($post);
				}
			}
			//add/edit save END
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('paycenters', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "pc" => "paycenter");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_REQUEST['ord']];
			$this->view->{$_REQUEST['clm'] . "order"} = $orderarray[$_REQUEST['ord']];

			if($logininfo->clientid > 0)
			{
				$where = ' and client=' . $logininfo->clientid;
			}
			else
			{
				$where = ' and client=0';
			}

			$limit = 50;
			$fdoc = Doctrine_Query::create()
				->select('count(*)')
				->from('Paycenters')
				->where("isdelete = 0 " . $where)
				->andWhere('paycenter != ""')
				->orderBy($columnarray[$_REQUEST['clm']] . " " . $_REQUEST['ord']);
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("`paycenter` like ?","'%" . trim($_REQUEST['val']) . "%'");
			}
			$fdoc->offset($_REQUEST['pgno'] * $limit);
			$fdocarray = $fdoc->fetchArray();

			$fdoc->select('*');
			$fdoc->where("`isdelete` ='0' ".$where." ");
			if(isset($_REQUEST['val']) && strlen($_REQUEST['val']) > 0)
			{
				$fdoc->andWhere("`paycenter` like ?","'%" . trim($_REQUEST['val']) . "%'");
			}
			$fdoc->limit($limit);
			$fdoc->offset($_REQUEST['pgno'] * $limit);

			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc->fetchArray());
			$paycenters[] = '9999999999999';
			foreach($fdoclimit as $k_limit => $v_limit)
			{
				$paycenters[] = $v_limit['id'];
			}

			$q = Doctrine_Query::create()
				->select('*')
				->from('PaycenterZip')
				->where('isdelete = "0"')
				->andWhereIn('paycenter', $paycenters);
			$q_res = $q->fetchArray();

			foreach($q_res as $k_qres => $v_qres)
			{
				$paycenters_zips[$v_qres['paycenter']][] = $v_qres['zip'];
			}

			foreach($fdoclimit as $k_flimit => $v_limit)
			{
				$fdoclimit[$k_flimit]['zips'] = implode(', ', $paycenters_zips[$v_limit['id']]);
			}

			$this->view->{"style" . $_GET['pgno']} = "active";
			$grid = new Pms_Grid($fdoclimit, 1, $fdocarray[0]['count'], "paycenters.html");

			if($fdoclimit)
			{
				$this->view->paycentersgrid = $grid->renderGrid();
			}
			else
			{
				$this->view->paycentersgrid = '<tr><td colspan="4"><center>' . $this->view->translate('no_paycenters') . '</center></td></tr>';
			}

			$this->view->navigation = $grid->dotnavigation("paycentersnavigation.html", 5, $_REQUEST['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['paycenters'] = $this->view->render('paycenters/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function addpaycenterAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$paycenters_form = new Application_Form_Paycenters();

				$post = $_POST;
				$post['client'] = $logininfo->clientid;

				if($paycenters_form->validate($post))
				{
					$paycenters_form->insert($post);


					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$paycenters_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editpaycenterAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->view->act = "paycenters/editpaycenter?id=" . $_REQUEST['id'];

			$this->_helper->viewRenderer('addpaycenter');

			if($this->getRequest()->isPost())
			{
				$paycenters_form = new Application_Form_Paycenters();

				if($paycenters_form->validate($_POST))
				{
					$a_post = $_POST;
					$pcid = $_REQUEST['pcid'];
					$a_post['pcid'] = $pcid;
					$a_post['clientid'] = $this->clientid;

					$paycenters_form->update($a_post);
					$this->_redirect(APP_BASE . 'paycenters/paycenters?flg=suc');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				}
				else
				{
					$fdoctor_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			if($_REQUEST['pcid'] > 0)
			{
				$fdoc = Doctrine::getTable('Paycenters')->find($_REQUEST['pcid']);
				$fdoc_zips = Doctrine::getTable('PaycenterZip')->findByPaycenterAndIsdelete($_REQUEST['pcid'], "0");

				if($fdoc)
				{
					if($fdoc_zips)
					{
						foreach($fdoc_zips->toArray() as $k_zip => $v_zip)
						{
							$zips[] = $v_zip['zip'];
						}
					}

					$fdocarray = $fdoc->toArray();
					$fdocarray['zips'] = implode(',', $zips);

					$this->retainValues($fdocarray);
				}

				$clientid = $fdocarray['clientid'];
			}
		}

		public function deletepaycenterAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
			if($_REQUEST['pcid'] > '0')
			{
				$fdoc = Doctrine::getTable('Paycenters')->findOneByIdAndClient($_REQUEST['pcid'], $logininfo->clientid);
				
				if($fdoc)
				{
					$fdoc->isdelete = 1;
					$fdoc->save();
				}
			}
			$this->redirect(APP_BASE.'paycenters/paycenters');
			exit;
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

	}

?>