<?php

class NewsController extends Zend_Controller_Action
{
	public function init()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->usertype = $logininfo->usertype;
		
// 		if(!$logininfo->clientid)
// 		{
// 			//redir to select client error
// 			$this->_redirect(APP_BASE . "error/noclient");
// 			exit;
// 		}
	}

	public function addnewsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		//ISPC-2421,Elena,22.04.2021
		$this->view->is_add = 1;

		if($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$news_form = new Application_Form_News();
			if($news_form->validate($_POST))
			{
				$news_form->InsertData($_POST);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				//$this->_redirect(APP_BASE.'news/newslist?flg=suc');
			}else{
				$news_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}

		if($logininfo->usertype=='SA')
		{

			$clt = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name")
			->from('Client')
			->where('isdelete=0');
			$clt->orderBy("AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."')");
			$cltexec = $clt->execute();
			$cltarray = $cltexec->toArray();
			//ISPC-2421,Elena,22.04.2021
			//$client_array = array(""=>"Select Client");

			foreach($cltarray as $keyarray=>$clientval)
			{

				$client_array[$clientval['id']] = $clientval['client_name'];
			}

			$this->view->client_array = $client_array;

			$this->view->commentbox = '<label id="lbl_issystem" for="issystem">'. $this->view->translate('issystem').'</label>
			<input type="checkbox" name="issystem" id="issystem" value="1" >
			<br />';

		}else{

			$usr = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('clientid='.$logininfo->clientid.' and isactive=0 and isdelete=0')
			->orderBy('last_name ASC');
			$usrexec = $usr->execute();
			$usrarray = $usrexec->toArray();
			$userarray = array(""=>"Select User");
			foreach($usrarray as $key=>$val)
			{
				$userarray[$val['id']] = $val['username'];
			}
			$this->view->userarray = $userarray;
		}
	}



	public function editnewsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('addnews');

		if($this->getRequest()->isPost())
		{
			
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$news_form = new Application_Form_News();


			if($news_form->validate($_POST))
			{
				$news_form->UpdateData($_POST);
				
				$q = Doctrine_Query::create()
				->delete('PopupVisibility')
				->where('newsid = ?', $_GET['id']);
				$q->execute();
				
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE.'news/newslist?flg=suc&mes='.urlencode($this->view->error_message));
			}else{
				$news_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

		}

		$editnews = Doctrine_Query::create()
		->select("*,AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') as news_title,AES_DECRYPT(news_content,'".Zend_Registry::get('salt')."') as news_content")
		->from('News')
		->where('id = ?', $_GET['id']);
		$editnewsexec = $editnews->execute();

		if($editnewsexec)
		{
			$newsarray = $editnewsexec->toArray();
			$newsarray['news_date']=date('d.m.Y',strtotime($newsarray[0]['news_date']));

			$newsmap = Doctrine::getTable('NewsMaping')->findBy('newsid',$_GET['id']);
			$maparray = $newsmap->toArray();

			foreach($maparray as $key=>$val)
			{
				$useridarray[] = $val['userid'];
			}
			$this->view->useridarray = $useridarray;
			if($maparray[0]['clientid']>0)
			{
				$this->view->clientarray = $maparray[0]['clientid'];
				$clientid = $maparray[0]['clientid'];
			}else{
				$this->view->clientarray =$newsarray[0]['clientid'];
				$clientid = $newsarray[0]['clientid'];
			}
			$usr = Doctrine::getTable('User')->findBy('clientid',$clientid);
			$usrarray = $usr->toArray();

			$userarray = array(""=>"Select User");
			foreach($usrarray as $key=>$val)
			{
				$userarray[$val['id']] = $val['username'];
			}
			$this->view->userarray = $userarray;
			if($newsarray[0]['issystem']==1){
				$this->view->checked = 'checked="checked"';
			}
			$newsarray[0]['news_date'] = date("d.m.Y",strtotime($newsarray[0]['news_date']));
			$this->retainValues($newsarray[0]);

		}

		if($logininfo->usertype=='SA')
		{

			$clt = Doctrine_Query::create()
			->select("*,AES_DECRYPT(client_name,'".Zend_Registry::get('salt')."') as client_name,AES_DECRYPT(street1,'".Zend_Registry::get('salt')."') as street1,AES_DECRYPT(street2,'".Zend_Registry::get('salt')."') as street2,
					AES_DECRYPT(postcode,'".Zend_Registry::get('salt')."') as postcode,AES_DECRYPT(city,'".Zend_Registry::get('salt')."') as city,AES_DECRYPT(firstname,'".Zend_Registry::get('salt')."') as firstname,AES_DECRYPT(lastname,'".Zend_Registry::get('salt')."') as lastname
					,AES_DECRYPT(emailid,'".Zend_Registry::get('salt')."') as emailid,AES_DECRYPT(phone,'".Zend_Registry::get('salt')."') as phone")
					->from('Client')
					->where('isdelete=0');
			$cltexec = $clt->execute();
			$cltarray = $cltexec->toArray();
			$client_array = array(""=>"Select Client");

			foreach($cltarray as $keyarray=>$clientval)
			{

				$client_array[$clientval['id']] = $clientval['client_name'];
			}

			$this->view->client_array = $client_array;


			$this->view->commentbox = '<label id="lbl_issystem" for="issystem">'. $this->view->translate('issystem').'</label>
			<input type="checkbox" name="issystem" id="issystem" value="1" '.$this->view->checked.'  >
			<br />';

		}else{

			$usr = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('clientid='.$logininfo->clientid.' and isactive=0 and isdelete=0')
			->orderBy('last_name ASC');
			$usrexec = $usr->execute();
			$usrarray = $usrexec->toArray();
			$userarray = array(""=>"Select User");
			foreach($usrarray as $key=>$val)
			{
				$userarray[$val['id']] = $val['username'];
			}
			$this->view->userarray = $userarray;
		}

	}


	public function fetchuserAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		if(strlen($_GET['id'])>0)
		{
			$idsAsInt = [];
			//echo $_GET['id'];
			try{
				$ids = explode(',', $_GET['id']);
			}catch(Exception $exp){
				$response['msg'] = "Error";
				$response['error'] = "Falsche Parameter";

				echo json_encode($response);
				exit();
			}

			foreach($ids as $one_id){
				try{
					$idAsInt = intval($one_id);
					$idsAsInt[] = $idAsInt;
				}catch(Exception $exp){

					$response['msg'] = "Error";
					$response['error'] = "Falsche Parameter";

					echo json_encode($response);
					exit();
				}
			}

			$usr = Doctrine_Query::create()
			->select('*')
			->from('User')
			->whereIn('clientid', $idsAsInt)
			->andWhere('isactive=0 and isdelete=0')
			->andWhere('isdelete=0')
			->orderBy('last_name ASC');
			//print_r($usr->getSqlQuery());
			$usrexec = $usr->execute();
			$usrarray = $usrexec->toArray();
			$userarray = array(""=>"Select User");
			foreach($usrarray as $key=>$val)
			{
				$userarray[$val['id']] = $val['username'];
			}
		}

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['userlist'] = $this->view->formSelect('userid[]', $_POST['userid'], array("multiple"=>"multiple"), $userarray);

		echo json_encode($response);
		exit;
	}

	public function newslistoldAction()
	{
		if($_GET['flg']=='suc')
		{
			$this->view->error_message = $this->view->translate("newsupdatedsuccessfully");
		}

	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk"=>"id","nt"=>"news_title","nd"=>"news_date");

		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");
		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];
		$this->view->{"style".$_GET['pgno']} = "active";
		$news = Doctrine_Query::create()
		->select('count(*)')
		->from('News');
		if($logininfo->usertype!='SA')
		{
			$news->where('clientid = '.$logininfo->clientid);
			$news->andWhere('userid = '.$logininfo->userid);
		}else{
			if($logininfo->clientid>0)
			{
				$news->where('clientid = '.$logininfo->clientid);
			}
		}
		$news->andWhere('isdelete = 0');
		$news->orderBy($columnarray[$_GET['clm']]." ".$_GET['ord']);
		$newsexec = $news->execute();
		$newsarray = $newsexec->toArray();

		$limit = 25;
		$news->select("*,AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') as news_title,AES_DECRYPT(news_content,'".Zend_Registry::get('salt')."') as news_content");
		$news->from('News');
		if($logininfo->usertype!='SA')
		{
			$news->where('clientid = '.$logininfo->clientid);
			$news->andWhere('userid = '.$logininfo->userid);
		}
		$news->andWhere('isdelete = 0');
		$news->limit($limit);
		$news->offset($_GET['pgno']*$limit);

		$newslimitexec = $news->execute();
		$newslimit = $newslimitexec->toArray();

		$grid = new Pms_Grid($newslimit,1,$newsarray[0]['count'],"listnews.html");
		$this->view->newsgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("newsnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['newslist'] =$this->view->render('news/fetchlist.html');

		echo json_encode($response);
		exit;
	}

	public function deletenewsAction()
	{
		$this->_helper->viewRenderer('newslist');


		$logininfo= new Zend_Session_Namespace('Login_Info');

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		/*if($this->getRequest()->isPost())
		{
			if(count($_POST['newsid'])<1){
				$this->view->error_message =$this->view->translate('selectatleastone'); $error=1;
			}

			if($error==0)
			{
				foreach($_POST['newsid'] as $key=>$val)
				{
					$thrash = Doctrine::getTable('News')->find($val);
					$thrash->isdelete = 1;
					$thrash->save();
					$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				}
			}
		}*/
		
		$delids = $_POST['delids'];
		if($delids == "")
		{
			$this->view->error_message = $this->view->translate('selectatleastone');
			$this->_redirect(APP_BASE . 'news/newslist?mes='.urlencode($this->view->error_message));
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
				$fdoc = Doctrine::getTable('News')->find($delid);
		
				$fdoc->isdelete = 1;
				$fdoc->save();
			}
			
			if(count($delids) > 0)
			{				
				$q = Doctrine_Query::create()
				->delete('PopupVisibility')
				->whereIn('newsid', $delids);
				$q->execute();
			}
				
			$this->_redirect(APP_BASE . 'news/newslist?flg=suc&mes='.urlencode($this->view->error_message));
			//$this->_helper->viewRenderer('locationslist');
		}
	}

	public function activenewsAction()
	{
		$this->_helper->viewRenderer('newslist');

		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('news',$logininfo->userid,'candelete');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}

		if($_GET['id']>0)
		{

			$thrash = Doctrine::getTable('News')->find($_GET['id']);

			if($_GET['flg']=='ina')
			{
				$thrash->isactive = 1;
				$q = Doctrine_Query::create()
				->delete('PopupVisibility')
				->where('newsid = ?', $_GET['id']);
				$q->execute();
			}else{

				$thrash->isactive = 0;
			}
			$thrash->save();
			$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
			$this->_redirect(APP_BASE.'news/newslist?flg=suc&mes='.urlencode($this->view->error_message));
		}

	}


	public function newsAction()
	{
		$news = Doctrine_Query::create()
		->select("*,AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') as news_title,AES_DECRYPT(news_content,'".Zend_Registry::get('salt')."') as news_content")
		->from('News')
		->where('id= ?', $_GET['id']);
		$newsexec = $news->execute();
		if($newsexec)
		{
			$newsarray = $newsexec->toArray();

			$this->view->news_date=date('d.m.Y',strtotime($newsarray[0]['news_date']));
			$this->view->news_title = ucwords($newsarray[0]['news_title']);
			$this->view->news_content = $newsarray[0]['news_content'];

		}

	}

	public function systemnewsAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout_popup');

		if(strlen($_POST['btnlater'])>0)
		{
			$newlater = Doctrine::getTable('News')->find($_POST['newsid']);
			$laterarray = $newlater->toArray();
			if($laterarray['viewcount']<2 && $laterarray['acknowledge']!=1)
			{
				if($laterarray['viewcount']<2)
				{
					$new = Doctrine_Query::create()
					->update('News')
					->set('viewcount',$laterarray['viewcount']+1)
					->where("id= ?", $_POST['newsid']);
					$newexec = $new->execute();

					$logininfo= new Zend_Session_Namespace('Login_Info');
					$logininfo->setlater = "1";
					$this->view->closefrm = 'parent.disablePopup();';
				}else{
					$logininfo= new Zend_Session_Namespace('Login_Info');
					$logininfo->setlater = "0";
				}

			}
		}


		if(strlen($_POST['btnaccept'])>0)
		{
			$new = Doctrine_Query::create()
			->update('News')
			->set('acknowledge',1)
			->where("id= ?", $_POST['newsid']);
			$newexec = $new->execute();

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$logininfo->setlater = "1";
			$this->view->closefrm = 'parent.disablePopup();';
		}

		$news = Doctrine_Query::create()
		->select("*,AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') as news_title,AES_DECRYPT(news_content,'".Zend_Registry::get('salt')."') as news_content")
		->from('News')
		->where('clientid='.$logininfo->clientid .' and issystem=1 and acknowledge=0');
		$newsexec = $news->execute();
		if($newsexec)
		{
			$newsarray = $newsexec->toArray();

			$this->view->news_date=date('d.m.Y',strtotime($newsarray[0]['news_date']));
			$this->view->news_title = ucwords($newsarray[0]['news_title']);
			$this->view->news_content = strip_tags($newsarray[0]['news_content']);
			$this->view->newsid = $newsarray[0]['id'];

		}

	}

	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}

	}
	
	//get view list news
	public function newslistAction(){
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
					"1" => "news_title_name",
					"2" => "news_date",
					"4" => "status"
			);
			/*$columns_search_array = array(
					"1" => "news_title",
					"2" => "news_date",
					"4" => "status"
			);*/
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
			$fdoc1->select("count(*)");
			$fdoc1->from('News');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			if($logininfo->usertype!='SA')
			{
				$fdoc1->andWhere('userid = ?',$logininfo->userid);
			}
			
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
			/* ------------- Search options ------------------------- 
			if (isset($search_value) && strlen(trim($search_value)) > 0)
			{
				//$regexp_arr = array();
				$comma = '';
				$filter_string_all = '';
				
				foreach($columns_search_array as $ks=>$vs)
				{
					switch ($vs)
					{
						case 'news_title':
							$vs = 'AES_DECRYPT(news_title,"'.Zend_Registry::get('salt').'")';
							break;
						case 'status':
							$vs = '(CASE WHEN isactive = "1" THEN "Aktiv" ELSE "Inaktiv" end)';
							break;
						default:
					
							break;
					}
					$filter_string_all .= $comma.$vs;
					$comma = ',';
				}
				//var_dump($filter_string_all);
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
				//$fdoc1->andWhere("(lower(CONVERT(AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') using latin1)) like ? or news_date like ? or lower((CASE WHEN isactive = '1' THEN 'Aktiv' ELSE 'Inaktiv' end)) like ?)", array("%" . trim($search_value) . "%", "%" . trim($search_value) . "%", "%" . trim($search_value) . "%"));
			}
	
			$fdocarray = $fdoc1->fetchArray();
			$filter_count  = $fdocarray[0]['count'];*/
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select("*, CONVERT(AES_DECRYPT(news_content,'".Zend_Registry::get('salt')."') using latin1)  as news_content");
			$fdoc1->addSelect("(CONVERT(AES_DECRYPT(news_title,'".Zend_Registry::get('salt')."') using latin1))  as news_title_name");
			$fdoc1->addSelect("(CASE WHEN isactive = '0' THEN 'Aktiv' ELSE 'Inaktiv' end) as status");
			
			$fdoc1->orderBy($order_by_str);
			
			$fdoclimit = $fdoc1->fetchArray();
			
			if(trim($search_value) != "")
			{
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp);
			
				foreach($columns_search_array as $ks=>$vs)
				{
					$pairs[$vs] = trim(str_replace('\\', '',$regexp));
			
				}
				//var_dump($pairs);
				$fdocsearch = array();
				foreach ($fdoclimit as $skey => $sval) {
					foreach ($pairs as $pkey => $pval) {
						if($pkey == 'news_date')
						{
							$sval[$pkey] = date('d.m.Y H:i', strtotime($sval[$pkey]));
						}
							
						$pval_arr = explode('|', $pval);
			
						foreach($pval_arr as $kpval=>$vpval)
						{
							if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
								$fdocsearch[$skey] = $sval;
								break;
							}
						}
								
					}
							
				}					
			
				$fdoclimit = $fdocsearch;						
			}
			$filter_count  = count($fdoclimit);
		
			if($limit != "")
			{
				$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
				
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
				$resulted_data[$row_id]['news_title'] = sprintf($link,$mdata['news_title_name']);
				$resulted_data[$row_id]['news_date'] = sprintf($link, date('d.m.Y', strtotime($mdata['news_date'])));
	
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'news/editnews?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
				
				$resulted_data[$row_id]['status'] = '<a href="news/activenews?flg='.($mdata['isactive'] == 0 ? ina : act) .'&id='.$mdata['id'].'" title="'.($mdata['isactive'] == 0 ? "Click to deactivate" : "Click to activate").'" style="cursor:pointer;">'.$mdata['status'].'</a>';
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
