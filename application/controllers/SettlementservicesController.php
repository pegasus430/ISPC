<?php

	class SettlementServicesController extends Zend_Controller_Action {
		public function init()
		{
			
			/* Initialize action controller here */
			setlocale(LC_ALL, 'de_DE.utf-8');
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->clientid = $logininfo->clientid;
			$this->userid = $logininfo->userid;
			$this->usertype = $logininfo->usertype;
			$this->filepass = $logininfo->filepass;
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
		}

		public function viewAction()
		{
			set_time_limit(0);
			$clientid = $this->clientid;
		}
		
		private function get_raw_sql($query) {
			if(!($query instanceof Doctrine_Query)) {
				throw new Exception('Not an instanse of a Doctrine Query');
			}
		
			$query->limit(0);
		
			if(is_callable(array($query, 'buildSqlQuery'))) {
				$queryString = $query->buildSqlQuery();
				$query_params = $query->getParams();
				$params = $query_params['where'];
			} else {
				$queryString = $query->getSql();
				$params = $query->getParams();
			}
		
			$queryStringParts = split('\?', $queryString);
			$iQC = 0;
		
			$queryString = "";
		
			foreach($params as $param) {
				if(is_numeric($param)) {
					$queryString .= $queryStringParts[$iQC] . $param;
				} elseif(is_bool($param)) {
					$queryString .= $queryStringParts[$iQC] . $param*1;
				} else {
					$queryString .= $queryStringParts[$iQC] . '\'' . $param . '\'';
				}
		
				$iQC++;
			}
			for($iQC;$iQC < count($queryStringParts);$iQC++) {
				$queryString .= $queryStringParts[$iQC];
			}
		
			echo $queryString."\n\n\n";
		}
		
		
		private function retainValues($values, $prefix = '')
		{
			foreach($values as $key => $val)
			{
				if(!is_array($val))
				{
					$this->view->$key = $val;
				}
				else
				{
					//retain 1 level array used in multiple hospizvbulk form
					foreach($val as $k_val => $v_val)
					{
						if(!is_array($v_val))
						{
							$this->view->{$prefix . $key . $k_val} = $v_val;
						}
					}
				}
			}
		}	
		
		public function getviewAction()
		{
			if (!$this->getRequest()->isXmlHttpRequest()) {
				die('!isXmlHttpRequest');
			}
			
		    $clientid = $this->clientid;
		    $this->_helper->layout()->disableLayout();
		    $this->_helper->viewRenderer->setNoRender(true);
		     
		    if(!$_REQUEST['length'])
		    {
		        $_REQUEST['length'] = "100";
		    }
		
		    $limit = $_REQUEST['length'];
		    $offset = $_REQUEST['start'];
		    $search_value = $_REQUEST['search']['value'];

		    
		    
		    if(!empty($_REQUEST['order'][0]['column']))
		    {
		        $order_column = $_REQUEST['order'][0]['column'];
		    } 
		    else
		    {
		        $order_column = "1";
		    }
		    
		    $order_dir = $_REQUEST['order'][0]['dir'];
		     
		    $columns_array = array(
		        "1" => "action_id",
		        "2" => "description"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		 
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('SettlementServices');
		    $fdoc1->where("clientid = ?", $clientid);
		    $fdoc1->andWhere("isdelete = 0  "); 
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("action_id like ?","%".trim($search_value)."%");
		    }
		    $fdoc1->orderBy($order_by_str);   
		    //$this->get_raw_sql($fdoc1);die();
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		  	/*
		  	 * SELECT COUNT(*) AS s__0 FROM settlement_services s WHERE (s.clientid = 1 AND s.isdelete = 0 AND s.action_id
 			 *	like '%s%') ORDER BY s.action_id asc  
 			*/   
		    //$this->get_raw_sql($fdoc1);die();
		    //echo $fdoc1->getSqlQuery();die();
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $fdoc1->select('*');
		    $fdoc1->Where("clientid = ".$clientid);
		    $fdoc1->andWhere("isdelete = 0  ");
		    
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("action_id like ? or description like ?",array("%".trim($search_value)."%","%".trim($search_value)."%") );
		    }
		    $fdoc1->limit($limit);
		    $fdoc1->offset($offset);
		   
		    /*SELECT *
FROM information_schema.tables
WHERE table_schema = 'ispc_mdat'
LIMIT 0 , 30*/
		    //') UNION (SELECT 1 from dual);#
		    //$this->get_raw_sql($fdoc1);die();
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		     
		    $report_ids[] = '99999999999999';
		    
		    foreach ($fdoclimit as $key => $report)
		    {
		        $fdoclimit_arr[$report['id']] = $report;
		        $report_ids[] = $report['id'];
		    }
		
		    $all_users = Pms_CommonData::get_client_users($clientid, true);
		
		    foreach($all_users as $keyu => $user)
		    {
		        $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
		    }
		
		
		    $row_id = 0;
		    $link = "";
		    $resulted_data = array();
		    foreach($fdoclimit_arr as $report_id =>$mdata)
		    {
		        $link = '%s';
		         
		        $resulted_data[$row_id]['action_id'] = sprintf($link,$mdata['action_id']);
		        $resulted_data[$row_id]['description'] = sprintf($link,$mdata['description']);
		        
		        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'settlementservices/edit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		     
		    //header("Content-type: application/json; charset=UTF-8");
		    //echo json_encode($response);
		    //echo $this->_helper->json($response);
		    $this->_helper->json->sendJson($response);			
		}
	
		public function editAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    $this->_helper->viewRenderer('add');
			
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $action_id= $_GET['id'];
		        }
		
		        $form = new Application_Form_SettlementServices();
		
		        if($form->validate($_POST))
		        {
		            $_POST['id'] = $action_id;
		            $form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('SettlementServices')->find($u_id );
		
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
		        }
		    }
		}
		
		public function addAction()
		{
		    $has_edit_permissions = Links::checkLinkActionsPermission();
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    if($this->getRequest()->isPost())
		    {
		        $form = new Application_Form_SettlementServices();
		
		        if($form->validate($_POST))
		        {
		            $form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		}
		
		public function deleteAction ()
		{
			$this->_helper->viewRenderer('list');
			$has_edit_permissions = Links::checkLinkActionsPermission();
		
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			if($_GET['id'])
			{
		
				$thrash = Doctrine::getTable('SettlementServices')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
		
				$this->_redirect(APP_BASE . "settlementservices/view");
				exit;
			}
		}
		
		
	}