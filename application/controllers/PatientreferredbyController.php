<?php

class PatientreferredbyController extends Zend_Controller_Action
{
	public $act;

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

	public function addreferredbyAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientreferredby',$logininfo->userid,'canadd');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}	
			
		if($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_PatientReferredBy();

			if($fdoctor_form->validate($_POST))
			{
				$a_post = $_POST;
				$logininfo= new Zend_Session_Namespace('Login_Info');
				$clientid =$logininfo->clientid;

				if($clientid>0)
				{
					$a_post['clientid'] = $clientid;
				}
					
				$succ = $fdoctor_form->InsertData($a_post);
				if($succ)
				{
					$Tr = new Zend_View_Helper_Translate();
					$this->view->succ_message = $Tr->translate('addsucc_msg');
				}

			}else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editreferredbyAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientreferredby',$logininfo->userid,'canedit');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}	
		$this->view->act="patientreferredby/editreferredby?id=".$_GET['id'];
		$this->_helper->viewRenderer('addreferredby');
		if($this->getRequest()->isPost())
		{
			$fdoctor_form = new Application_Form_PatientReferredBy();


			if($fdoctor_form->validate($_POST))
			{

				$succ = $fdoctor_form->UpdateData($_POST);

				if($succ)
				{
					$Tr = new Zend_View_Helper_Translate();
					//$this->view->succ_message = $Tr->translate('editsucc_msg');
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE.'patientreferredby/referredbylist?flg=suc&mes='.urlencode($this->view->error_message));
				}

			}else
			{
				$fdoctor_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
		$fdoc = Doctrine::getTable('PatientReferredBy')->find($_GET['id']);
			
		if($fdoc)
		{
			$this->retainValues($fdoc->toArray());
		}
	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}

	public function referredbylistoldAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientreferredby',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");;
		}
	}
	 
	public function getjsondataAction()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('PatientReferredBy')
		->where('isdelete = ?', 0);
		$track = $fdoc->execute();

		echo json_encode($track->toArray());
		exit;
	}
	 
	public function fetchlistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientreferredby',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		$columnarray = array("pk"=>"id","rn"=>"referred_name");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";
		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('PatientReferredBy')
		->where('isdelete = ?',0)
		->andWhere('clientid = ?',$logininfo->clientid)
		->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
			
		$fdocexec = $fdoc->execute();
		$fdocarray = $fdocexec->toArray();

			
		$limit = 50;
		$fdoc->select('*');
		$fdoc->limit($limit);
		$fdoc->offset($_GET['pgno']*$limit);
			
		$fdoclimitexec = $fdoc->execute();
		$fdoclimit = $fdoclimitexec->toArray();
			
		$grid = new Pms_Grid($fdoclimit,1,$fdocarray[0]['count'],"listreferredby.html");
		$this->view->referredbygrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("reffnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['referredbylist'] =$this->view->render('patientreferredby/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}
	 
	public function deletereferredbyAction()
	{
			
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('patientreferredby',$logininfo->userid,'candelete');
			
		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		//$this->_helper->viewRenderer('referredbylist');

		/*if($this->getRequest()->isPost())
		{
			if(count($_POST['ref_id'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}

			if($error==0)
			{
				foreach($_POST['ref_id'] as $key=>$val)
				{
					$thrash = Doctrine::getTable('PatientReferredBy')->find($val);
					$thrash->isdelete = 1;
					$thrash->save();
						
				}
					
			}
		}*/
		$delids = $_POST['delids'];
		if($delids == "")
		{
			$this->view->error_message = $this->view->translate('selectatleastone');
			$this->_redirect(APP_BASE . 'patientreferredby/referredbylist?mes='.urlencode($this->view->error_message));
			//$this->_helper->viewRenderer('locationslist');
		}
		else
		{
			$delids = explode('|', $delids);
			if(count($delids) > 1)
			{
				$this->view->error_message = $this->view->translate("recordsdeletedsucessfully");
			}
			else
			{
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			}
			foreach($delids as $delid)
			{
				$thrash = Doctrine::getTable('PatientReferredBy')->find($delid);
				$thrash->isdelete = 1;
				$thrash->save();
			}
		}
		$this->_redirect(APP_BASE . 'patientreferredby/referredbylist?flg=suc&mes='.urlencode($this->view->error_message));
		
	}
	
	//get view list referredby
	public function referredbylistAction(){
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
					"1" => "referred_name"
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
			$fdoc1->from('PatientReferredBy');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			
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
				//$fdoc1->andWhere("(lower(referred_name) like ?)", array("%" . trim($search_value) . "%"));
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
				$resulted_data[$row_id]['checkloc'] = '<input class="checkloc" name="checkloc[]" id="'.$mdata['id'].'" type="checkbox" value=""  />';
				$resulted_data[$row_id]['referred_name'] = sprintf($link,$mdata['referred_name']);
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'patientreferredby/editreferredby?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}	
	}

}

?>