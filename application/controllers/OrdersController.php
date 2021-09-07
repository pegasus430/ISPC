<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
class OrdersController extends Pms_Controller_Action 
{
    public function init()
    {
    	/* Initialize action controller here */
    	$this->setActionsWithJsFile([
    			"ordersoverview", //ISPC - 2281
    			"overview", //ISPC - 2281
    			"order", //ISPC - 2281
    			"materialslist", //ISPC - 2281
    			"recipientslist", //ISPC - 2281
    	]);
    	//phtml is the default for zf1 ... but on bootstrap you manualy set html :(
    	$this->getHelper('viewRenderer')->setViewSuffix('phtml');
    	 
    }
    
    public function materialslistAction()
    {
    	$this->view->category = $this->getParam('category');
    	$this->view->usertype = $this->logininfo->usertype;
    	
    	if($_REQUEST['action'])
    	{
    		if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
    		{
    			$matord = new ClientOrderMaterials();
    			$matr = $matord->getTable()->find($_REQUEST['id'], Doctrine_Core::HYDRATE_RECORD);
    			
    			$matr->isdelete = '1';
    			$matr->save();
    			
    			$category = $matr->category;
    			$this->_redirect(APP_BASE . "orders/".$category."list");
    		}
    	}
    	
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    		
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    		
    		$sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
    		$sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
    		
    		$sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
    		$sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
    		
    		$limit = $this->getRequest()->getPost('iDisplayLength');
    		$offset = $this->getRequest()->getPost('iDisplayStart');
    		
    		$category = $this->getRequest()->getPost('category');
    		
    		$search_value = $this->getRequest()->getPost('sSearch');
    		
    		$columns_array = array(
				"0" => "title",
				"1" => "unit",
				"2" => "pzn",
				"3" => "pieces",
				"4" => "quantity"
			);
    		$columns_search_array = $columns_array;
    		
    		$order_by = '';
    		
    		$tobj = new ClientOrderMaterials(); //obj used as table
    		
    		
    		if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
    			//$order_by = $sort_col_name . ' ' . $sort_col_dir;
    		
	    		$chars[ 'Ä' ] = 'Ae';
	    		$chars[ 'ä' ] = 'ae';
	    		$chars[ 'Ö' ] = 'Oe';
	    		$chars[ 'ö' ] = 'oe';
	    		$chars[ 'Ü' ] = 'Ue';
	    		$chars[ 'ü' ] = 'ue';
	    		$chars[ 'ß' ] = 'ss';
	    		 
	    		$colch =addslashes(htmlspecialchars($sort_col_name));
	    		
	    		foreach($chars as $kch=>$vch)
	    		{
	    			$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
	    		}
	    		 
	    		$order_by ='LOWER('.$colch.') '.$sort_col_dir;
    		}
    		
    		$tcol = $tobj->getTable()->createQuery('q');
    		$tcol->select('*');
    		$tcol->where('clientid = ?' , $this->logininfo->clientid);
    		$tcol->andWhere("category =?", $category);
    		$tcol->andWhere("isdelete = 0");// ISPC-2464  // Maria:: Migration ISPC to CISPC 08.08.2020
    		
    		$full_count  = $tcol->count();
    	
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
    			$tcol->andWhere($filter_search_value_arr[0] , $regexp_arr);
    			//$search_value = strtolower($search_value);
    			//$fdoc1->andWhere("(lower(name) like ?)", array("%" . trim($search_value) . "%"));
    			$filter_count  = $tcol->count();
    		}
    		else 
    		{
    			$filter_count = $full_count;
    		}
    		
    		if ( ! empty($order_by)) {
    			$tcol->orderBy($order_by);
    		}
    		
    		if ( ! empty($limit)) {
    			$tcol->limit((int)$limit);
    		}
    		
    		if ( ! empty($offset)) {
    			$tcol->offset((int)$offset);
    		}
    		
    		$tcol_arr = $tcol->fetchArray();
    		
    		$resulted_data = array();
    		foreach($tcol_arr as $row)
    		{
    			$data = array(
    				'title' => 	$row['title'],
    				'unit' => $row['unit'],
    				'pzn' => $row['pzn'],
    				'pieces' => $row['pieces'],
    				'quantity' => $row['quantity'],
    				'actions' => '<a href="'.APP_BASE .'orders/addmaterial?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
    			);
    			array_push($resulted_data, $data);
    		}
    		
    		$result = array(
    			'draw' => $this->getRequest()->getPost('sEcho'),
    			'recordsTotal' => $full_count,
    			'recordsFiltered' => $filter_count,
    			'data' => $resulted_data    				
    		);
    		
    		$this->_helper->json->sendJson($result);
    		exit; //for readability
    	}
    	
    	
    }
    
    public function dressingslistAction()
    {
    	$has_edit_permissions = Links::checkLinkActionsPermission();
    	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    	{
    		$this->_redirect(APP_BASE . "error/previlege");
    		exit;
    	}
    	
    	$this->forward('materialslist', null, null, array(
    			'category'	  => 'dressings'
    	));
    	
    	return;
    }
    
    public function drugslistAction()
    {
    	$has_edit_permissions = Links::checkLinkActionsPermission();
    	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    	{
    		$this->_redirect(APP_BASE . "error/previlege");
    		exit;
    	}
    	 
    	$this->forward('materialslist', null, null, array(
    			'category'	  => 'drugs'
    	));
    	 
    	return;
    }
    
    public function auxiliarieslistAction()
    {
    	$has_edit_permissions = Links::checkLinkActionsPermission();
    	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    	{
    		$this->_redirect(APP_BASE . "error/previlege");
    		exit;
    	}
    
    	$this->forward('materialslist', null, null, array(
    			'category'	  => 'auxiliaries'
    	));
    
    	return;
    }
    
    public function nursingauxiliarieslistAction()
    {
    	$has_edit_permissions = Links::checkLinkActionsPermission();
    	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    	{
    		$this->_redirect(APP_BASE . "error/previlege");
    		exit;
    	}
    
    	$this->forward('materialslist', null, null, array(
    			'category'	  => 'nursingauxiliaries'
    	));
    
    	return;
    }
    
    public function addmaterialAction()
    {
    	$has_edit_permissions = Links::checkLinkActionsPermission();
    	if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
    	{
    		$this->_redirect(APP_BASE . "error/previlege");
    		exit;
    	}
    		
    	if($_REQUEST['id'])
    	{
    		$id = $_REQUEST['id'];
    	}
    	
    	$saved_values = $this->_orderMaterials_GatherDetails($id);
    	
    	if($_REQUEST['category'])
    	{
    		$category = $this->view->category = $_REQUEST['category'];
    	}
    	else
    	{
    		$category = $this->view->category= $saved_values['category']['value'];
    	}
    	
    	$form = new Application_Form_ClientOrderMaterials(array(
    			'_block_name'           => 'ORDERMATERIALS',
    			'_category'  => $category,
    	));
    		
    	
    	$form->create_form_ordermaterials($saved_values);
    		
    		
    	//@todo : move messages in layout
    	$this->view->SuccessMessages = array_merge(
    			$this->_helper->flashMessenger->getMessages('SuccessMessages'),
    			$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
    			);
    	$this->view->ErrorMessages = array_merge(
    			$this->_helper->flashMessenger->getMessages('ErrorMessages'),
    			$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
    			);
    		
    	$this->_helper->flashMessenger->clearMessages('ErrorMessages');
    	$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
    		
    	$this->_helper->flashMessenger->clearMessages('SuccessMessages');
    	$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
    		
    	$this->view->form = $form;
    
    	if($this->getRequest()->isPost())
    	{
    		foreach($_POST['order_table'] as $kr=>$vr)
    		{
    			foreach($vr as $ki=>$vi)
    			{
    				$post[$ki] = $vi;
    			}
    		}
    		$post['clientid'] = $_POST['clientid'];
    		$post['id'] = $_POST['id'];
    		$post['category'] = $_POST['category'];
    		$ordm  = $form->save_form_ordermaterials($post);
    
    		if($_POST['id'])
    		{
    			$this->_redirect(APP_BASE . "orders/".$post['category']."list");
    		}
    
    	}
    }
    
    private function _orderMaterials_GatherDetails( $id = null)
    {
    	$entity  = new ClientOrderMaterials();
    	$saved_formular_final = array();
    	if ( !empty($id))
		{
			$saved_formular = $entity->getTable()->findOneBy('id', $id, Doctrine_Core::HYDRATE_RECORD);
		}
    	
    	if(!$saved_formular)
    	{
    		$cols= $entity->getTable()->getFieldNames();
    		foreach($cols as $kr=>$vr)
    		{
    			$saved_formular[$vr] = null;
    		}
    	}
    	//print_r($saved_formular);exit;
    	foreach($saved_formular as $kcol=>$vcol)
    	{
    		if($kcol == 'create_date' || $kcol == 'create_user' ||$kcol == 'change_date' ||$kcol == 'change_user' || $kcol == 'isdelete') continue;
    		$saved_formular_final[$kcol]['colprop'] = $entity->getTable()->getDefinitionOf($kcol);
    		
    		$saved_formular_final[$kcol]['value'] = $vcol;
    		
    	}
    	
    	//print_r($saved_formular_final); exit;
    	return $saved_formular_final;
    }
    
    public function adddefaultAction()
    {
    	//exit;
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    	
    	if($_REQUEST['category'])
    	{
    		$category = $_REQUEST['category'];
    	}
    	else 
    	{
    		return;
    	}
    	
    	$mato = new ClientOrderMaterials();
    	$matdefault = $mato->client_order_materials_by_category("0", $category);
    	
    	$data = array();
    	if($matdefault)
    	{
    		foreach($matdefault as $kr=>$vr)
    		{
	    		$data[] = array(
	    			'clientid' => $this->logininfo->clientid,
	    			'category' => $category,
	    			'title' => $vr['title'],	    			
	    			'unit' => $vr['unit'],
	    			'pzn' => $vr['pzn'],
	    			'pieces' => $vr['pieces'],
	    			'quantity' => $vr['quantity']
	    		);
    		}
    		//print_R($data); exit;
    		$collection = new Doctrine_Collection('ClientOrderMaterials');
    		$collection->fromArray($data);
    		$collection->save();
    	}
    	
    	$this->_redirect(APP_BASE . "orders/".$_REQUEST['category']."list");
    }
    
    

    public function recipientslistAction(){
        setlocale(LC_ALL, 'de_DE.UTF8');
        $step = null;
    
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('op', null);
        }
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('op');
        }
 
        switch ($step) {
    
            case "users" :
                $this->_user_list();
                break;
            case "pseudousers" :
                $this->_pseudo_groups_list();
                break;    
                
            case "save_recipients" :
                
                $this->_save_recipiets($_POST);
                break;    
    
            default:
                $this->_user_list();
                break;
        }
    }
    
    private function _save_recipiets($post){
        
            if($post['users'])
            {
                $post['clientid'] = $this->logininfo->clientid;
                if(empty($_POST['users']))
                {
                    $this->view->error_message = $this->view->translate('selectatleastone');
                    $this->_redirect(APP_BASE . 'orders/recipientslist?mes='.urlencode($this->view->error_message));
                }
                else
                {
                    $post['recids'] = $post['users'];
                    $recform =  new Application_Form_ClientOrderRecipients(array());
                    $recform->save_client_recipients($post);
    
                    $this->_redirect(APP_BASE . 'orders/recipientslist?flg=suc&mes='.urlencode($this->view->error_message));
                }
            }
        
    }
    
    private function _user_list()
    {
 
    	/* $user_pseudo =  new UserPseudoGroup();
    	$user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->logininfo->clientid);
    	
    	$pseudogrouparraytodo = array();
    	if ( ! empty ($user_ps)) {
    	    	
    	    //pseudogroup must have users in order to display
    	    $user_ps_ids =  array_column($user_ps, 'id');
    	    $user_pseudo_users = new PseudoGroupUsers();
    	    $users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);
    	    
    	    foreach($user_ps as $row) {
    	        if ( ! empty($users_in_pseudogroups[$row['id']])){
    	            	
    	            //Hack for JS (TODO - 1145) -> you should not fix a js problem by changing the php like that. just a specific case fix
    	            $pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = str_replace('"', ' ', str_replace("'", " ", $row['servicesname'])); // Hack for JS (TODO - 1145)
    	        }
    	    }
    	
    	    $todousersarr[$this->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
    	} */
    	
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		$sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
    		$sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
    
    		$sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
    		$sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
    
    		$columns_array = array(
    				"0" => "username",
    				"1" => "user_title",
    				"2" => "last_name",
    				"3" => "first_name"
    		);
    		$columns_search_array = $columns_array;
    
    		$order_by = '';
    
    		$tobj = new User(); //obj used as table
    
    
    		if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
    			//$order_by = $sort_col_name . ' ' . $sort_col_dir;
    
    			$chars[ 'Ä' ] = 'Ae';
    			$chars[ 'ä' ] = 'ae';
    			$chars[ 'Ö' ] = 'Oe';
    			$chars[ 'ö' ] = 'oe';
    			$chars[ 'Ü' ] = 'Ue';
    			$chars[ 'ü' ] = 'ue';
    			$chars[ 'ß' ] = 'ss';
    
    			$colch =addslashes(htmlspecialchars($sort_col_name));
    	   
    			foreach($chars as $kch=>$vch)
    			{
    				$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
    			}
    
    			$order_by ='LOWER('.$colch.') '.$sort_col_dir;
    		}
    		$clrec = new ClientOrderRecipients();
    		$client_recipients = $clrec->get_client_recipients($this->logininfo->clientid);
    		
    		$tcol = $tobj->getTable()->createQuery('q');
    		$tcol->select('*');
    		$tcol->where('clientid = ?' , $this->logininfo->clientid);
    		$tcol->andWhere('usertype != ?', 'SA');
        	$tcol->andWhere('isactive="0"');
        	$tcol->andWhere('isdelete="0"');
   
    		$full_count  = $tcol->count();
    		$filter_count = $full_count;
    
    		if ( ! empty($order_by)) {
    			$tcol->orderBy($order_by);
    		}
    
    		if ( ! empty($limit)) {
    			$tcol->limit((int)$limit);
    		}
    
    		if ( ! empty($offset)) {
    			$tcol->offset((int)$offset);
    		}
    		
    		$tcol_arr = $tcol->fetchArray();
    
    		$resulted_data = array();
    		foreach($tcol_arr as $row)
    		{
    			if(in_array('u'.$row['id'], $client_recipients))
    			{
    				$recipient = 'checked="checked"';
    				$value = "1";
    			}
    			else 
    			{
    				$recipient = "";
    				$value = "0";
    			}
    			$data = array(
    					'userid' => 	'u'.$row['id'],
    					'username' => 	$row['username'],
    					'user_title' => $row['user_title'],
    					'last_name' => $row['last_name'],
    					'first_name' => $row['first_name'],
    					'checked' =>  $value
    			);
    			array_push($resulted_data, $data);
    		}
    
    		$result = array(
    				'draw' => $this->getRequest()->getPost('sEcho'),
    				'recordsTotal' => $full_count,
    				'recordsFiltered' => $filter_count,
    				'data' => $resulted_data
    		);
    
    		$this->_helper->json->sendJson($result);
    		exit; //for readability
    	}
    	 
    	 
    }
        
        // get view list servicesname
    private function _pseudo_groups_list()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        
        // populate the datatables
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
         	$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		$sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
    		$sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
    
    		$sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
    		$sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
    		
    		$columns_array = array(
    				"0" => "servicesname",
    		);
    		$columns_search_array = $columns_array;
    
    		$order_by = '';
    
    		$tobj = new UserPseudoGroup(); //obj used as table
    
    
    		if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
    			//$order_by = $sort_col_name . ' ' . $sort_col_dir;
    
    			$chars[ 'Ä' ] = 'Ae';
    			$chars[ 'ä' ] = 'ae';
    			$chars[ 'Ö' ] = 'Oe';
    			$chars[ 'ö' ] = 'oe';
    			$chars[ 'Ü' ] = 'Ue';
    			$chars[ 'ü' ] = 'ue';
    			$chars[ 'ß' ] = 'ss';
    
    			$colch =addslashes(htmlspecialchars($sort_col_name));
    	   
    			foreach($chars as $kch=>$vch)
    			{
    				$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
    			}
    
    			$order_by ='LOWER('.$colch.') '.$sort_col_dir;
    		}
    		
            // ########################################
            // ##### Query for count ###############
            $fdoc1 = Doctrine_Query::create();
            $fdoc1->select('count(*)');
            $fdoc1->from('UserPseudoGroup');
            $fdoc1->where("clientid = ?", $clientid);
            $fdoc1->andWhere("isdelete = 0 ");
            $fdoc1->andWhere("makes_visits != 'tours'");
            $fdocarray = $fdoc1->fetchArray();
            $full_count = $fdocarray[0]['count'];
            
            $fdocarray = $fdoc1->fetchArray();
            $filter_count = $fdocarray[0]['count'];
            
            // ########################################
            // ##### Query for details ###############
            $fdoc1->select('*');

            if ( ! empty($order_by)) {
                $fdoc1->orderBy($order_by);
            }

            if ( ! empty($limit)) {
                $fdoc1->limit((int)$limit);
            }
            
            if ( ! empty($offset)) {
                $fdoc1->offset((int)$offset);
            }
            $fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
            
            
            $fdoclimit_arr = array();
            $user_ps_ids = array();
            foreach ($fdoclimit as $key => $report) {
                $fdoclimit_arr[$report['id']] = $report;
                $user_ps_ids[] = $report['id'];
            }
            
            $user_pseudo_users = new PseudoGroupUsers();
            $users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);
            $users2pseudo = array();
            foreach($users_in_pseudogroups as $ps_id=>$ps_users){
                foreach($ps_users as $k=>$pu_data){
                    $users2pseudo[$pu_data['pseudo_id']][] = $pu_data['user_id'];
                }
            }
            
            $clrec = new ClientOrderRecipients();
            $client_recipients = $clrec->get_client_recipients($this->logininfo->clientid);
            
            $resulted_data = array();
            foreach ($fdoclimit_arr as $row) {
                if (in_array('pseudogroup_'.$row['id'], $client_recipients)) {
                    $recipient = 'checked="checked"';
                    $value = "1";
                } else {
                    $recipient = "";
                    $value = "0";
                }
                $data = array(
                    'userid' => 'pseudogroup_'.$row['id'],
                    'system_id' => $row['id'],
                    'servicesname' => $row['servicesname'],
                    'assigned_users' => !empty($users2pseudo[$row['id']]) ? implode(',',$users2pseudo[$row['id']]) : "",
                    'checked' => $value
                );
                array_push($resulted_data, $data);
            }
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' => $full_count,
                'recordsFiltered' => $filter_count,
                'data' => $resulted_data
            );
            
            $this->_helper->json->sendJson($result);
            exit(); // for readability
        }
    }    
    
    
    public function recipientslistOldAction()
    {
    	if($_REQUEST['action'])
    	{
    		if($_REQUEST['action'] == 'setrecipients')
    		{
    			$post['clientid'] = $this->logininfo->clientid;
    			if($_POST['recids'] == "")
    			{
    				$this->view->error_message = $this->view->translate('selectatleastone');
    				$this->_redirect(APP_BASE . 'orders/recipientslist?mes='.urlencode($this->view->error_message));
    			}
    			else
    			{
    				$post['recids'] = explode('|', $_POST['recids']);
    				if(count($delids) > 1)
    				{
    					$this->view->error_message = $this->view->translate("recordsupdatedsuccessfully");
    				}
    				else
    				{
    					$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
    				}
    				$recform =  new Application_Form_ClientOrderRecipients(array());
    				$recform->save_client_recipients($post);
    				
    				 
    				$this->_redirect(APP_BASE . 'orders/recipientslist?flg=suc&mes='.urlencode($this->view->error_message));
    			}
    		}
    	}
    	
    	 
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
    
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    
    		$sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
    		$sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
    
    		$sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
    		$sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
    
    		$search_value = $this->getRequest()->getPost('sSearch');
    
    		$columns_array = array(
    				"0" => "username",
    				"1" => "user_title",
    				"2" => "last_name",
    				"3" => "first_name"
    		);
    		$columns_search_array = $columns_array;
    
    		$order_by = '';
    
    		$tobj = new User(); //obj used as table
    
    
    		if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
    			//$order_by = $sort_col_name . ' ' . $sort_col_dir;
    
    			$chars[ 'Ä' ] = 'Ae';
    			$chars[ 'ä' ] = 'ae';
    			$chars[ 'Ö' ] = 'Oe';
    			$chars[ 'ö' ] = 'oe';
    			$chars[ 'Ü' ] = 'Ue';
    			$chars[ 'ü' ] = 'ue';
    			$chars[ 'ß' ] = 'ss';
    
    			$colch =addslashes(htmlspecialchars($sort_col_name));
    	   
    			foreach($chars as $kch=>$vch)
    			{
    				$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
    			}
    
    			$order_by ='LOWER('.$colch.') '.$sort_col_dir;
    		}
    		$clrec = new ClientOrderRecipients();
    		$client_recipients = $clrec->get_client_recipients($this->logininfo->clientid);
    		
    		$tcol = $tobj->getTable()->createQuery('q');
    		$tcol->select('*');
    		$tcol->where('clientid = ?' , $this->logininfo->clientid);
    		$tcol->andWhere('usertype != ?', 'SA');
        	$tcol->andWhere('isactive="0"');
        	$tcol->andWhere('isdelete="0"');
   
    		$full_count  = $tcol->count();
    		 
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
    			$tcol->andWhere($filter_search_value_arr[0] , $regexp_arr);
    			//$search_value = strtolower($search_value);
    			//$fdoc1->andWhere("(lower(name) like ?)", array("%" . trim($search_value) . "%"));
    			$filter_count  = $tcol->count();
    		}
    		else
    		{
    			$filter_count = $full_count;
    		}
    
    		if ( ! empty($order_by)) {
    			$tcol->orderBy($order_by);
    		}
    
    		if ( ! empty($limit)) {
    			$tcol->limit((int)$limit);
    		}
    
    		if ( ! empty($offset)) {
    			$tcol->offset((int)$offset);
    		}
    		
    		$tcol_arr = $tcol->fetchArray();
    
    		$resulted_data = array();
    		foreach($tcol_arr as $row)
    		{
    			if(in_array($row['id'], $client_recipients))
    			{
    				$recipient = 'checked="checked"';
    				$value = "1";
    			}
    			else 
    			{
    				$recipient = "";
    				$value = "";
    			}
    			$data = array(
    					'recipient' => '<input class="recipient"'. $recipient . ' name="recipient[]" id="'.$row['id'].'" type="checkbox" rel="'.$row['id'].'" value="' . $value . '"  />',
    					'username' => 	$row['username'],
    					'user_title' => $row['user_title'],
    					'last_name' => $row['last_name'],
    					'first_name' => $row['first_name']
    			);
    			array_push($resulted_data, $data);
    		}
    
    		$result = array(
    				'draw' => $this->getRequest()->getPost('sEcho'),
    				'recordsTotal' => $full_count,
    				'recordsFiltered' => $filter_count,
    				'data' => $resulted_data
    		);
    
    		$this->_helper->json->sendJson($result);
    		exit; //for readability
    	}
    	 
    	 
    }
     

    public function overviewAction(){
        setlocale(LC_ALL, 'de_DE.UTF8');
        $step = null;
        
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('step', null);
        }
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('step');
        }
        
        if($_REQUEST['action'])
        {
        	$step = $_REQUEST['action'];
        }
        
        if(!empty($_REQUEST['patient'])){
           $patient = $_REQUEST['patient'];
        } 
        
        if(!empty($_REQUEST['order_id'])){
           $order_id = $_REQUEST['order_id'];
        } 
       // dd($_REQUEST);

        switch ($step) {
        
            case "add_patient2active_grid" :
                $this->add_patient2active_grid();
                break;
            case "remove_patient_from_grid" :
                $this->_remove_patient_from_grid();
                break;
            // ISPC-2464 
            case "current_order_dialog" :
                $this->_save_current_order_details();
            break;
            
            case "get_following_saved_orders" :
                $this->_get_following_saved_orders();
            break;
            //-- 
            
                
            case "order_dialog" :
                $this->_save_order_details();
                break;
                
            case "intubated_medication_list" :
                $this->_intubated_medication_list($patient);
                break;

                // ISPC-2548 TODO-2848 Lore 06.02.2020 
            case "actual_medication_list" :
                $this->_actual_medication_list($patient);
                break;
                
            case "bedarfs_medication_list" :
                $this->_bedarfs_medication_list($patient);
                break;
                
            case "krisen_medication_list" :
                $this->_krisen_medication_list($patient);
                break;
                
            case "iv_medication_list" :
                $this->_iv_medication_list($patient);
                break;
                
            case "isnutrition_medication_list" :
                $this->_isnutrition_medication_list($patient);
                break;
                
            case "intervall_medication_list" :
                $this->_intervall_medication_list($patient);
                break;
            //
            
            case "pause_patient_orders" :
                $this->_pause_patient_orders();
                break;
                
            case "stop_patient_orders" :
                $this->_stop_patient_orders();
                break;
  
            case "order_management_tabs" :
                $this->_order_management_tabs();
                break;
  
            case "last_patient_order" :
                $this->_last_patient_order();
                break;
  
            case "generate_order_pdf" :
                $this->_generate_order_pdf($order_id,$patient);
                break;
  
            //ISPC-2369 Carmen 16.07.2020
            case "generate_from_order_pdf" :            	
            	$fromto = 'ordertobrowser'; 
            	parse_str($_POST[order_data], $order_data);
            	
            	
            	foreach($order_data['data'] as $kr => &$vr)
            	{
            		if($kr == 'PatientsOrdersDetails')
            		{
            			foreach($vr as $kd => &$vd)
            			{
            				if($kd == 'order_interval_options' && $order_data['data']['PatientsOrdersDetails']['order_interval'] == 'selected_days_of_the_week')
            				{
            					$order_options_str = implode(',', $vd);
            					$order_data['data'][$kr][$kd] = $order_options_str;
            				}
            			}
            		}
            		if($kr == 'PatientsOrdersMedication')
            		{
            			foreach($vr as $km => &$vm)
            			{
            				if(!array_key_exists('patient_drugplan_id', $vm))
            				{
            					unset($order_data['data'][$kr][$km]);
            				}
            			}
            		}
            		if($kr == 'PatientsOrdersMaterials')
            		{
            			foreach($vr as $ko => $vo)
            			{
            				if(is_int($ko))
            				{
            					$kon = '';
            					unset($order_data['data'][$kr][$ko]);
            					$order_data['data'][$kr][$kon] = $vo;
            				}
            			}
            				
            		}
            	}
            	//print_r($order_data['data']); exit;
               	$this->_generate_order_pdf('',$patient, $fromto, $order_data);
               	 if($order_data['data']['PatientsOrdersDeliveryDates']['delivery_date'] != "")
               	{ 
               		$pdfname_translated = $this->view->translate ( 'order_pdf_title' ) . " (". date("d.m.Y",strtotime($order_data['data']['PatientsOrdersDeliveryDates']['delivery_date'])) .").pdf";
               	 }
               	else
               	{
               		$pdfname_translated = $this->view->translate ( 'order_pdf_title' ) . ".pdf";
               	} 
               
               	$result = array('pdfurl' => $_SESSION['filename'], 'pdfname' => $pdfname_translated);
               	echo json_encode($result);
               	exit;
               	break;
            //--
                
            default:
                $this->_order_management_tabs();
                break;
        }
    }
    
    /**
     * @author Ancuta
     * 
     * TODO-2872 Ancuta 24.03.2020 - added 2 params $patient and order_date 
     * 
     * @param boolean $patient
     * @param boolean $order_date
     */
    private function _order_management_tabs($patient=false,$order_date=false,$return_tab = false){
        
        setlocale(LC_ALL, 'de_DE.UTF-8');
        $clientid = $this->logininfo->clientid;
    
        if(!empty($_REQUEST['year'])){
            $selected_year = $_REQUEST['year'];
        } else{
            $selected_year = date("Y");
        }
        
		//TODO-2872 Ancuta 24.03.2020 
        if($order_date){
            $selected_year = date("Y",strtotime($order_date));
        }
		// --
        
        $selected_period['year'] = $selected_year;
        
        
        if(!empty($_REQUEST['month'])){
            $selected_month = $_REQUEST['month'];
        } else{
            $selected_month = date("m");
        }
        
		//TODO-2872 Ancuta 24.03.2020 
        if($order_date){
            $selected_month = date("m",strtotime($order_date));
        }
		//--
        
        
        
        $selected_period['month'] = $selected_month;
        $selected_period['year-month'] = $selected_year.'-'.$selected_month;
        $month_days= 0 ;
        if(!function_exists('cal_days_in_month'))
        {
            $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_period['year-month'] . "-01")), 1, date("Y", strtotime($selected_period['year-month'] . "-01"))));
        }
        else
        {
            $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_period['year-month'] . "-01")), date("Y", strtotime($selected_period['year-month'] . "-01")));
        }
        $selected_period['start'] = $selected_period['year-month'] . "-01";
        $selected_period['end'] = $selected_period['year-month'] . '-' . $month_days;
        $selected_period['days_in_month'] = $month_days;
        
        $patientmaster = new PatientMaster();
        $selected_period['days']= $patientmaster->getDaysInBetween( $selected_period['start'], $selected_period['end']);
        
        $this->view->selected_period = $selected_period;

        $month_arr = array(
            "01" => $this->translate('January'),
            "02" => $this->translate('February'),
            "03" => $this->translate('March'),
            "04" => $this->translate('April'),
            "05" => $this->translate('May'),
            "06" => $this->translate('June'),
            "07" => $this->translate('July'),
            "08" => $this->translate('August'),
            "09" => $this->translate('September'),
            "10" => $this->translate('Octomber'),
            "11" => $this->translate('November'),
            "12" => $this->translate('December')
        );
        $this->view->month_arr = $month_arr;
        
        
    // OWN = "assigned to mee" and created by me 
    // ALL
    // CLOSED =  al closed
        
        //get client national hollidays
        $nhollyday = new NationalHolidays();
        $national_holidays_arr = $nhollyday->getNationalHoliday($clientid , $selected_period['start'], true);
        
        foreach($national_holidays_arr as $k_natholliday => $v_natholliday)
        {
//             $national_holidays[] = strtotime(date('Y-m-d', strtotime($v_natholliday['NationalHolidays']['date'])));
            $national_holidays[] = date('Y-m-d', strtotime($v_natholliday['NationalHolidays']['date']));
        }
        $this->view->national_holidays = $national_holidays;

      
        $ipids = array();
        $ipidsWithOrders = array();
        $patients_scheduled4order = PatientsOrdersAllowedTable::find_patients_inPeriod($selected_period);
        if(!empty($patients_scheduled4order)){
            foreach($patients_scheduled4order as $k=>$p_o){
                $ipids[] = $p_o['ipid'];
            }
        }
		//TODO-2872 Ancuta 24.03.2020 
        if($patient && !empty($patient)){
            $ipids = array($patient);
        }
		//--        
        
        $order_grid_patients = array();
        if (! empty($ipids)) {
            // patient days
            $sql = 'e.epid, p.ipid, e.ipid,';
            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
            $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
            $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
            $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
            
            $conditions['periods'][0]['start'] = "2008-01-01";
            $conditions['periods'][0]['end'] = date("Y-m-d");
            // $conditions['periods'][0]['start'] = $selected_period['start'];
            // $conditions['periods'][0]['end'] = $selected_period['end'];
            $conditions['client'] = $clientid;
            $conditions['ipids'] = $ipids;
            
            // get only ipids of client
            
            // beware of date d.m.Y format here
            $patient_days = Pms_CommonData::patients_days($conditions, $sql);
            $allowed_ipids = array_keys($patient_days);
            
            
            // find patients with orders
//             $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders($allowed_ipids,$selected_period);

            $patients_periods_statuses = PatientsOrdersPeriodsTable::find_all_patients_periods($allowed_ipids);
            
            $pp = 0 ;
            $pat_periods = array();
            $period_start_date = "";
            $period_end_date = "";
            foreach ($patients_periods_statuses as $k => $p) {
                $period_start_date = $p['start_date'];
                $pat_periods[$p['action_status']][$p['ipid']][$pp]['start'] = $p['start_date'];
                
                if (! empty($p['end_date'])) {
                    $period_end_date = $p['end_date'];
                    $pat_periods[$p['action_status']][$p['ipid']][$pp]['end'] = $p['end_date'];
                    $pat_periods[$p['action_status']][$p['ipid']][$pp]['period_opened'] = "no";
                } else {
                    $period_end_date = $selected_period['end'];
                    $pat_periods[$p['action_status']][$p['ipid']][$pp]['end'] = $selected_period['end'];
                    $pat_periods[$p['action_status']][$p['ipid']][$pp]['period_opened'] = "yes";
                }
                
                $pat_periods[$p['action_status']][$p['ipid']][$pp]['days'] = $patientmaster->getDaysInBetween($period_start_date, $period_end_date);
                $pp ++;
                
                if(!is_array($pat_periods[$p['action_status']][$p['ipid']]['overall_days'])){
                    $pat_periods[$p['action_status']][$p['ipid']]['overall_days'] = array();
                }
                
                $pat_periods[$p['action_status']][$p['ipid']]['overall_days'] = array_merge($pat_periods[$p['action_status']][$p['ipid']]['overall_days'],$patientmaster->getDaysInBetween($period_start_date, $period_end_date));
                
            }
            
//             dd($pat_periods);
            
            $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders($allowed_ipids);
            $overall_order_details  = array();
            $order_2date = array();
            $ipidsWithOrders = array();
//             dd($patients_with_Orders);
            $current_user =  $this->logininfo->userid;
            $overal_orders_statuses = array();
//             dd($patients_with_Orders);
            foreach($patients_with_Orders as $k => $ord_details_all){
                $ipidsWithOrders[] = $ord_details_all['ipid'];
                $overall_order_details[$ord_details_all['ipid']][$ord_details_all['id']] = $ord_details_all;
                
                // get all active or paused
                if($ord_details_all['action_status'] == "opened" || $ord_details_all['action_status'] == "paused"){
                    if($ord_details_all['action_status'] == "opened"){
                        
                        $overal_orders_statuses['opened'][] = $ord_details_all['id'];
                        $overal_orders_patients_statuses['opened'][] = $ord_details_all['ipid'];
                        
                    } else {
                        
                        $overal_orders_statuses['paused'][] = $ord_details_all['id'];
                        $overal_orders_patients_statuses['paused'][] = $ord_details_all['ipid'];
                        
                    }
                }  
                else 
                {
                    $overal_orders_statuses['closed'][] = $ord_details_all['id'];
                    $overal_orders_patients_statuses['closed'][] = $ord_details_all['ipid'];
                }
                
                // get all OWN orders
				// change so own are taken - as the value from db comes like this  u.4535		//TODO-2872 Ancuta 24.03.2020 
                foreach($ord_details_all['PatientsOrdersRecipients'] as $k=>$reciepient){
                    if('u'.$current_user == $reciepient['recipient_id']){  //TODO-2872 Ancuta 24.03.2020 
                        $own[]  = $reciepient['order_id'];
                        $overal_orders_statuses['own'][] = $ord_details_all['ipid'];
                        $overal_orders_patients_statuses['own'][] = $ord_details_all['ipid'];
                    }
                }
            }
            
//             dd($overal_orders_patients_statuses);
            
            $ipidsWithOrders = array_unique($ipidsWithOrders);
//             dd($overall_order_details);
//             dd($overal_orders_statuses);
            // get order with childs
            
            $order_child_dates = array();
            $order_2date = array();
            $order_dates_of_parent = array();
            $order_dates_of_patient = array();
            foreach($patients_with_Orders as $k => $ord_details){
                
                $order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']][] = $ord_details['order_date'];;
                $order_dates_of_patient[$ord_details['ipid']][$ord_details['id']][] = $ord_details['order_date'];;
                
//                 $order_2date[$ord_details['id']] = $ord_details['order_date'];
                if(!empty($ord_details['parent_id'])){
//                     $order_child_dates[$ord_details['ipid']][$ord_details['parent_id']][$ord_details['id']] = $ord_details['order_date'];
                    $order_child_dates[$ord_details['ipid']][$ord_details['parent_id']][] = $ord_details['order_date'];
                    $date_2order[$ord_details['ipid']][$ord_details['parent_id']][ $ord_details['order_date']] = $ord_details['id'];
                } else{
//                     $order_child_dates[$ord_details['ipid']][$ord_details['id']][$ord_details['id']] = $ord_details['order_date'];
                    $order_child_dates[$ord_details['ipid']][$ord_details['id']][] = $ord_details['order_date'];
                    $date_2order[$ord_details['ipid']][$ord_details['id']][ $ord_details['order_date']] = $ord_details['id'];
                }
            }
            
//             dd($order_child_dates);
//             dd($order_dates_of_parent);
// //             dd( $date_2order);
            
            // create  child intervals
            $full_order_intervals = array();
            $parent_order_intervals = array();
            foreach($order_child_dates as $oipid=>$ord_data){
                foreach($ord_data as $parent_order_id=> $child_dates){
                    
                    if(!is_array($parent_order_intervals[$oipid][$parent_order_id])){
                        $parent_order_intervals[$oipid][$parent_order_id]  =array();
                    }
                    
                    if(count($child_dates) >1 ){
                        foreach($child_dates as $order_child => $child_date)
                        {
                            $child_order_id = 0 ;
                            $child_order_id = $date_2order[$oipid][$parent_order_id][$child_date];

                            if(isset($child_dates[$order_child+1]) &&  $child_date < $child_dates[$order_child+1])
                            {
                                $next_child_date = date("Y-m-d",strtotime("-1 day",strtotime($child_dates[$order_child+1])));
                                if($child_date > $next_child_date){
                                    $next_child_date = $child_date;
                                }
                                $order_intervals[$oipid][$parent_order_id][$child_order_id]   = $patientmaster->getDaysInBetween($child_date, $next_child_date);
                                $parent_order_intervals[$oipid][$parent_order_id]   =array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_date, $next_child_date));
                                // end one day before next child date
                                $full_order_intervals[$oipid][$child_order_id]   = $patientmaster->getDaysInBetween($child_date, $next_child_date);
                                
                            } else{
                                
                                $extra_months = date("Y-m-d",strtotime("+ 3 months",strtotime($child_date)));
                                $order_intervals[$oipid][$parent_order_id][$child_order_id]  = $patientmaster->getDaysInBetween($child_date, $extra_months);
                                $parent_order_intervals[$oipid][$parent_order_id]   =array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_date, $extra_months));
                                $full_order_intervals[$oipid][$child_order_id]  = $patientmaster->getDaysInBetween($child_date, $extra_months);
                            }
                        }
                    } else {
                        $child_order_id = 0 ;
                        $child_order_id = $date_2order[$oipid][$parent_order_id][$child_dates[0]];
                        if($overall_order_details[$oipid][$parent_order_id]['order_interval'] != "once"){
                            $extra_months = date("Y-m-d",strtotime("+ 3 months",strtotime($child_dates[0])));
                            $order_intervals[$oipid][$parent_order_id][$child_order_id]   = $patientmaster->getDaysInBetween($child_dates[0], $extra_months);
                            $parent_order_intervals[$oipid][$parent_order_id]   =array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_dates[0], $extra_months));
                            $full_order_intervals[$oipid][$child_order_id]   = $patientmaster->getDaysInBetween($child_dates[0], $extra_months);
                        } else {
                            $order_intervals[$oipid][$parent_order_id][$child_order_id]   = $child_dates[0];
                            $parent_order_intervals[$oipid][$parent_order_id]   =array_merge($parent_order_intervals[$oipid][$parent_order_id], array($child_dates[0]));
                            $full_order_intervals[$oipid][$child_order_id][]   = $child_dates[0];
                        }
                    }
                }
            }
            
//             dd($full_order_intervals); exit;
            
//             dd($parent_order_intervals);
//             dd($ord_int,$order_2date,$order_intervals);
            //
//             dd($order_intervals, $patients_with_Orders);
 
            
            // do not allow orders to overlay
            
            $order_delivery_dates = array();
            $Xplanned_orders_details = array();
            foreach($patients_with_Orders as $k => $ord_details){
                
                $xdays_counter = 0;
                $order_interval_options = array_map('trim', explode(",", $ord_details['order_interval_options']));//only used for days of week

                $current_parent[$ord_details['id']] = 0 ;
                $current_parent[$ord_details['id']] = !empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                $first_ever_parent_date[$ord_details['id']] = $parent_order_intervals[$ord_details['ipid']][ $current_parent[$ord_details['id']] ] [0];
                
                
                // First order of parent
                
                
                // get all child orders
                
                //get next order
                
                // get al delivery dates
                if(!empty($ord_details['PatientsOrdersDeliveryDates'])){
                   $order_delivery_dates[$ord_details['id']][] = $ord_details['PatientsOrdersDeliveryDates']['delivery_date'];
                }
                // do we need it? 
                
                // GET HERE PAUSED PERIODS
                /* if(!empty($ord_details['PatientsOrdersPausedDates'])){
                    foreach($ord_details['PatientsOrdersPausedDates'] as $k=>$pdeli){
                        $paused_period[$pdeli['order_id']][] = $pdeli;
                    }
                } */
//      dd($order_dates_of_parent,$order_dates_of_patient);
                
                foreach($selected_period['days'] as $period_day){
                    $period_day_strtotime = strtotime($period_day);
                    $order_id2date[$ord_details['id']][] = $period_day;
                    
 
                    foreach($order_intervals[$ord_details['ipid']][$ord_details['id']] as  $child =>$child_interval_Dates ){
                        if(in_array($period_day,$child_interval_Dates)){
                            $day2child[$ord_details['id']][$period_day] = $child_interval_Dates;
                        }
                    }
                    
                    
                    switch( $ord_details['order_interval']) {
                        case"once":{
                            
                            if($period_day == $ord_details['order_date'] && $ord_details['status'] != "canceled"){//TODO-2872 Ancuta 26.03.2020  do not add to array if canceled 
                                $orders_details[$ord_details['ipid']][$ord_details['order_date']] = $ord_details;
                                $orders_details[$ord_details['ipid']][$ord_details['order_date']]['order_id'] = $ord_details['id'];
                                $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'once';
                                $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = '1';
                                
                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']] = $ord_details;
                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['order_id'] = $ord_details['id'];
                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_type'] = 'once';
                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_options'] = '1';
                                
                                
                                if(  $period_day == $ord_details['order_date'] && $period_day  == $first_ever_parent_date[$ord_details['id']] )
                                {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "yes";
                                } else {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "no";
                                }
                                
                                if(in_array($period_day,$order_delivery_dates[$ord_details['id']]) && $ord_details['status'] == 'verified')
                                {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'verified';
                                }
                                elseif($ord_details['status'] =="canceled")
                                {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = $ord_details['status'];
                                }
                                else
                                {
                                    if(in_array($period_day,$pat_periods['paused'][$ord_details['ipid']]['overall_days']) && !in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'paused';
                                    }
                                    elseif(in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'stopped';
                                    }
                                    else
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'active';
                                    }
                                }
                                
                            }
                            
                            
                    
                        }break;
                        	
                        case"every_x_days":{
                            
                            if(	strtotime($ord_details['order_date']) <= $period_day_strtotime && (int)$ord_details['order_interval_options'] > 0
                                &&  in_array($period_day, $full_order_intervals[$ord_details['ipid']][$ord_details['id']])
                                ) {
                                if ( $xdays_counter % $ord_details['order_interval_options'] == 0 && $ord_details['status'] != "canceled") {//TODO-2872 Ancuta 26.03.2020  do not add to array if canceled 
                                    
                                    $orders_details[$ord_details['ipid']][$period_day] = $ord_details;
                                    $orders_details[$ord_details['ipid']][$period_day]['current_order_id'] = $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'every_x_days';
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = $ord_details['order_interval_options'];
                                    
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']] = $ord_details;
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['current_order_id'] = $ord_details['id'];
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_type'] = 'every_x_days';
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_options'] = $ord_details['order_interval_options'];
                                        
                                        if(    (!empty($ord_details['parent_id']) &&  in_array($period_day,$order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']]))
                                            || ( in_array($period_day,$order_dates_of_patient[$ord_details['ipid']][$ord_details['id']])))
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id']; // order can be edited
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['order_id'] = $ord_details['id']; // order can be edited
                                        } 
                                        else
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;// -> new step is added
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['order_id'] = 0;// -> new step is added
                                        }
                                        
                                        
                                        
                                    if(  $period_day == $ord_details['order_date'] && $period_day  == $first_ever_parent_date[$ord_details['id']] )
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
//                                         $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = 0;
                                        
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "yes";
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['parent_id'] = $ord_details['id'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['step_parent_id'] = 0;
                                        
                                    } else {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
//                                         $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] =  !empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] =  !empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (!empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                        
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "no";
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['parent_id'] =  !empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['step_parent_id'] =  !empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (!empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                    }
                                    
                                    
//                                     if( empty($ord_details['parent_id'])){
//                                         $orders_details[$ord_details['ipid']][$period_day]['child_id'] = $child;
//                                     } 
                                    
                                    
                                    if(in_array($period_day,$order_delivery_dates[$ord_details['id']]) && $ord_details['status'] == 'verified')
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'verified';
                                    } 
                                    elseif($ord_details['status'] =="canceled")
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = $ord_details['status'];
                                    } 
                                    else
                                    {
                                        if(in_array($period_day,$pat_periods['paused'][$ord_details['ipid']]['overall_days']) && !in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'paused';
                                        } 
                                        elseif(in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'stopped';
                                        } 
                                        else
                                        {
                                            if($ord_details['status'] == "active" && $period_day == $ord_details['order_date']){
                                                
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'active';
                                            } else{
                                                
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'planned';
                                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'planned';
                                            }
                                        }
                                    } 
                                        
                                }
                                $xdays_counter++;
                            }
                        }break;
                        	
                        case"selected_days_of_the_week":{
                    
                            if(	$query_date_strtotime <= $period_day_strtotime
                                && strtotime($ord_details['order_date']) <= $period_day_strtotime
                                && (in_array( date("N", $period_day_strtotime), $order_interval_options) )
                                &&  in_array($period_day, $full_order_intervals[$ord_details['ipid']][$ord_details['id']])
                                && $ord_details['status'] != "canceled"
                                )
                                { //TODO-2872 Ancuta 26.03.2020  do not add to array if canceled 
                                    $orders_details[$ord_details['ipid']][$period_day] = $ord_details;
                                    $orders_details[$ord_details['ipid']][$period_day]['current_order_id'] = $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'selected_days_of_the_week';
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = $ord_details['order_interval_options'];

                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']] = $ord_details;
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['current_order_id'] = $ord_details['id'];
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_type'] = 'selected_days_of_the_week';
                                    $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['interval_options'] = $ord_details['order_interval_options'];
                                        
                                    if(    (!empty($ord_details['parent_id']) &&  in_array($period_day,$order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']]))
                                        || ( in_array($period_day,$order_dates_of_patient[$ord_details['ipid']][$ord_details['id']])))
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id']; // order can be edited
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['order_id'] = $ord_details['id']; // order can be edited
                                    } 
                                    else
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;// -> new step is added
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['order_id'] = 0;// -> new step is added
                                    }
                                    
                                        
                                        
                                    if(  $period_day == $ord_details['order_date'] && $period_day  == $first_ever_parent_date[$ord_details['id']] )
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
//                                         $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = 0;
                                        
                                        
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "yes";
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['parent_id'] = $ord_details['id'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['step_parent_id'] = 0;
                                        
                                    } else {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
//                                         $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] =  !empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] =  !empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (!empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                        
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['is_parent'] = "no";
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['parent_id'] =  !empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['step_parent_id'] =  !empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (!empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                    }
                                    
                                    
                                    if(in_array($period_day,$order_delivery_dates[$ord_details['id']]) && $ord_details['status'] == 'verified')
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'verified';
                                    }
                                    elseif($ord_details['status'] =="canceled")
                                    {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                        $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = $ord_details['status'];
                                    } 
                                    else
                                    {
                                        if(in_array($period_day,$pat_periods['paused'][$ord_details['ipid']]['overall_days'])  && !in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'paused';
                                        } 
                                        elseif(in_array($period_day,$pat_periods['stopped'][$ord_details['ipid']]['overall_days']))
                                        {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                            $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'stopped';
                                        } 
                                        else
                                        {
                                            //$orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                            if($ord_details['status'] == "active" && $period_day == $ord_details['order_date']){
                                            
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'active';
                                            } else{
                                            
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'planned';
                                                $Xplanned_orders_details[$ord_details['ipid']][$period_day][$ord_details['id']]['status'] = 'planned';
                                            }
                                        }
                                    } 
                                     
                                    
                            }
                        }break;
                    }
                }
            }
//             dd($selected_period['days'],$orders_details);
//             dd($current_order_date,$next_order_date);
//             dd($day2child);
//             dd($orders_details);

            // if active :: 
            // not in closed 
//             $overal_orders_statuses
            
            foreach ($patient_days as $ipid => $patient_details) {
//                 $pat_periods
                $patient_opened_pause[$ipid] = 0 ; 
                if(array_key_exists($ipid, $pat_periods['paused'])){
                    // if period is opened - the the patient must be listed in the bottom
                    foreach($pat_periods['paused'][$ipid] as $kp=>$pause_per){
                        if($pause_per['period_opened'] == "yes" ){
                            $patient_opened_pause[$ipid]  += 1;
                        }
                    }
                    
                }
                
                if(in_array($ipid,$overal_orders_patients_statuses['closed'])){

                    $st = "closed";
                    $order_grid_patients[$st][$ipid]['pat_id_enc'] = Pms_Uuid::encrypt($patient_details['details']['id']);
                    $order_grid_patients[$st][$ipid]['nice_name'] = $patient_details['details']['last_name'] . ', ' . $patient_details['details']['first_name'];
                    
                    $nice_name = array();
                    $nice_name = mb_substr($order_grid_patients[$st][$ipid]['nice_name'], 0, 25, "UTF-8");
                    if (strlen($order_grid_patients[$ipid]['nice_name']) > '25') {
                        $order_grid_patients[$st][$ipid]['nice_name_small'] =  $nice_name . '...';
                    } else {
                        $order_grid_patients[$st][$ipid]['nice_name_small'] =  $order_grid_patients[$st][$ipid]['nice_name'];
                    }
                    if (in_array($ipid, $ipidsWithOrders)) {
                        $order_grid_patients[$st][$ipid]['has_orders'] = "1";
                        $order_grid_patients[$st][$ipid]['order_scheduled_details'] = $orders_details[$ipid];
                        $order_grid_patients[$st][$ipid]['Xorder_scheduled_details'] = $Xplanned_orders_details[$ipid];
                    } else {
                        $order_grid_patients[$st][$ipid]['has_orders'] = "0";
                    }
                    
                    
                }else{
                    
                
                if(in_array($ipid,$overal_orders_patients_statuses['own']) || !in_array($ipid, $ipidsWithOrders)){
                        if(!empty($patient_opened_pause[$ipid] )){
                            $st = "own_paused";
                        } else{
                            $st = "own";
                        }
                        $order_grid_patients[$st][$ipid]['pat_id_enc'] = Pms_Uuid::encrypt($patient_details['details']['id']);
                        
                        $order_grid_patients[$st][$ipid]['nice_name'] = $patient_details['details']['last_name'] . ', ' . $patient_details['details']['first_name'];
                        $nice_name = array();
                        $nice_name = mb_substr($order_grid_patients[$st][$ipid]['nice_name'], 0, 25, "UTF-8");
                        if (strlen($order_grid_patients[$ipid]['nice_name']) > '25') {
                            $order_grid_patients[$st][$ipid]['nice_name_small'] =  $nice_name . '...';
                        } else {
                            $order_grid_patients[$st][$ipid]['nice_name_small'] =  $order_grid_patients[$st][$ipid]['nice_name'];
                        }
                        if (in_array($ipid, $ipidsWithOrders)) {
                            $order_grid_patients[$st][$ipid]['has_orders'] = "1";
                            $order_grid_patients[$st][$ipid]['order_scheduled_details'] = $orders_details[$ipid];
                            $order_grid_patients[$st][$ipid]['Xorder_scheduled_details'] =  $Xplanned_orders_details[$ipid];
                        } else {
                            $order_grid_patients[$st][$ipid]['has_orders'] = "0";
                        }
                        
                    }
                
                    if(!empty($patient_opened_pause[$ipid] )){
                        $st = "all_paused";
                    } else{
                        $st = "all_active";
                    }
                    $order_grid_patients[$st][$ipid]['pat_id_enc'] = Pms_Uuid::encrypt($patient_details['details']['id']);
                    
                    $order_grid_patients[$st][$ipid]['nice_name'] = $patient_details['details']['last_name'] . ', ' . $patient_details['details']['first_name'];
                    $nice_name = array();
                    $nice_name = mb_substr($order_grid_patients[$st][$ipid]['nice_name'], 0, 25, "UTF-8");
                    if (strlen($order_grid_patients[$ipid]['nice_name']) > '25') {
                        $order_grid_patients[$st][$ipid]['nice_name_small'] =  $nice_name . '...';
                    } else {
                        $order_grid_patients[$st][$ipid]['nice_name_small'] =  $order_grid_patients[$st][$ipid]['nice_name'];
                    }
                    if (in_array($ipid, $ipidsWithOrders)) {
                        $order_grid_patients[$st][$ipid]['has_orders'] = "1";
                        $order_grid_patients[$st][$ipid]['order_scheduled_details'] = $orders_details[$ipid];
                        $order_grid_patients[$st][$ipid]['Xorder_scheduled_details'] =  $Xplanned_orders_details[$ipid];
                    } else {
                        $order_grid_patients[$st][$ipid]['has_orders'] = "0";
                    }
                
                
             }
                
                
                
            }
        }
		//TODO-2872 Ancuta 24.03.2020 
        if($patient && !empty($return_tab)){
            return $order_grid_patients[$return_tab];
        }
		//--

        $this->view->order_grid_patients = $order_grid_patients;
        
    }

    
    
    private function add_patient2active_grid() {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        if (! empty($_REQUEST)) {
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patientid']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            if(!empty($ipid)){
                
                $data['ipid'] = $ipid;
                $data['start_date'] = $_REQUEST['start_date'];
                $save_patient2grid = PatientsOrdersAllowedTable::getInstance()->findOrCreateOneBy('ipid', array(
                    $ipid
                ), $data);
                
                $saved_patient = $save_patient2grid->toArray();
    
                if(!empty($saved_patient)){
                    $success = true;
                } else{
                    $success = false;
                }
    
                $result = array(
                    'success' => $success
                );
                
                $this->_helper->getHelper('json')->sendJson($result);
                exit; //for readability
            } else{

                $success = false;
                $result = array(
                    'success' => $success
                );
                
                $this->_helper->getHelper('json')->sendJson($result);
                exit; //for readability
                
            }
            
        }
    }
    
    private function _remove_patient_from_grid() {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        if (! empty($_REQUEST)) {
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patientid']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            if( ($x = PatientsOrdersAllowedTable::getInstance()->findOneBy('ipid',$ipid )) != null){
                $x->delete();
                $success = true;
            }
 
            $result = array(
                'success' => $success
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
        }
    }
    
    
    private function _intubated_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
//        dd($_REQUEST);
       
        //also the order id
        if (! empty($_REQUEST)) {
        
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            

            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);

            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
//                 dd($order_meds);
            }
            
            
            
            $m_medication = new PatientDrugPlan();
            $medication_array = array();
            $medication_array = $m_medication->find_patient_isintubated($decid,true,true);
            
            
            // get packaging 
            $packaging_array = PatientDrugPlanExtra::intubated_packaging();
            
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
            
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                
                $data =  array(
                    'debug'                                  => '1', //add debug info on devmode
                    'patient_drugplan_id'                    => $row['id'], //int
                    'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication']) ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                    'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage'] != $row['dosage']) ? $order_meds[$row['id']]['dosage'] : $row['dosage']   , //string
                    'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                    'order_medication_packaging'             => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['packaging'] != $packaging_array[$row['extra']['packaging']]) ? $order_meds[$row['id']]['packaging']: $packaging_array[$row['extra']['packaging']],// string
                    'order_medication_volume'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['volume'] != $row['extra']['volume']) ? $order_meds[$row['id']]['volume'] : $row['extra']['volume']  , //string
                    'order_medication_kcal'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['kcal'] != $row['extra']['kcal']) ? $order_meds[$row['id']]['kcal'] : $row['extra']['kcal'], //string
                    'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                );
                array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
            
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    private function _actual_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
               
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
                                 
            }
                     
            
            $medication_array = array();               
            $medis = Doctrine_Query::create()
                ->select('*')
                ->from('PatientDrugPlan indexBy medication_master_id')
                ->WhereIn("ipid" ,$ipid)
                ->andWhere("isdelete = 0")
                ->andWhere("isbedarfs = 0")
                ->andWhere("iscrisis = 0")
                ->andWhere("isivmed = 0")
                ->andWhere("isschmerzpumpe = 0")
                ->andWhere("ispumpe = 0")//ISPC-2833 Ancuta 26.02.2021
                ->andWhere("treatment_care = 0")
                ->andWhere("isnutrition = 0")
                ->andWhere("isintubated = 0")
                ->andWhere("scheduled = 0");
              $medication_array = $medis->fetchArray();

              
              $master_medi_ids = array_keys($medication_array);
              
              $med = new Medication();
              $master_medi_array = $med->getMedicationById($master_medi_ids,true); 

              $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
              
              
              foreach ($medication_array as $key => $val) {                 
                  $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']]['name'];
                  $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                  $medication_array[$key]['unit'] = $packaging_array[$val['id']]['unit'];
                  $medication_array[$key]['concentration'] = $packaging_array[$val['id']]['concentration'];
                  $medication_array[$key]['dosage_form'] = $packaging_array[$val['id']]['dosage_form'];       
              }
                  
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                            => '1', //add debug info on devmode
                        'patient_drugplan_id'              => $row['id'], //int
                        'order_medication_name'            => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication'])    ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'            => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['drug']            != $row['drug'])          ? $order_meds[$row['id']]['drug']            : $row['drug'],  //string
                        'order_medication_dosage'          => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']          != $row['dosage'])        ? $order_meds[$row['id']]['dosage']          : $row['dosage'],  //string
                        'order_medication_dosage_refr'     =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_comments'        => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']        != $row['comments'])      ? $order_meds[$row['id']]['comments']        : $row['comments'],  //string
                        'order_medication_concentration'   => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['volume']          != $row['concentration']) ? $order_meds[$row['id']]['concentration']   : $row['concentration'],  //string
                        'order_medication_dosage_form'     => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_form']     != $row['dosage_form'])   ? $order_meds[$row['id']]['dosage_form']     : $row['dosage_form'],  //string
                        'order_medication_unit'            => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['unit']            != $row['unit'])          ? $order_meds[$row['id']]['unit']            : $row['unit'],  //string
                        'selected'                         => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : "" , //string
                        
                                               
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
        }
    }
    
    
    private function _krisen_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
                
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
            }
            
            $medication_array = array();
            $medis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan indexBy medication_master_id')
            ->WhereIn("ipid" ,$ipid)
            ->andWhere("isdelete = 0")
            ->andWhere("iscrisis = 1");
            $medication_array = $medis->fetchArray();
            
            
            $master_medi_ids = array_keys($medication_array);
            
            $med = new Medication();
            $master_medi_array = $med->getMedicationById($master_medi_ids,true);
            
            $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
            
            
            foreach ($medication_array as $key => $val) {
                $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']]['name'];
                $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                $medication_array[$key]['unit'] = $packaging_array[$val['id']]['unit'];
                $medication_array[$key]['concentration'] = $packaging_array[$val['id']]['concentration'];
                $medication_array[$key]['dosage_form'] = $packaging_array[$val['id']]['dosage_form'];
            }
            
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                                  => '1', //add debug info on devmode
                        'patient_drugplan_id'                    => $row['id'], //int
                        'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication'])      ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_drug'] != $row['drug'])            ? $order_meds[$row['id']]['medication_drug'] : $row['drug'], //string
                        'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']          != $row['dosage'])          ? $order_meds[$row['id']]['dosage']          : $row['dosage']   , //string
                        'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_comments'              => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']        != $row['comments'])        ? $order_meds[$row['id']]['comments']        : $row['comments']   , //string
                        'order_medication_dosage_interval'       => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_interval'] != $row['dosage_interval']) ? $order_meds[$row['id']]['dosage_interval'] : $row['dosage_interval']   , //string
                        'order_medication_concentration'         => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['concentration']   != $row['concentration'])   ? $order_meds[$row['id']]['concentration']   : $row['concentration']  , //string
                        'order_medication_dosage_form'           => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_form']     != $row['dosage_form'])     ? $order_meds[$row['id']]['dosage_form']     : $row['dosage_form']   , //string
                        'order_medication_unit'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['unit']            != $row['unit'])            ? $order_meds[$row['id']]['unit']            : $row['unit'], //string
                        'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    
    private function _bedarfs_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
            }
                 
            
            $medication_array = array();
            $medis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan indexBy medication_master_id')
            ->WhereIn("ipid" ,$ipid)
            ->andWhere("isdelete = 0")
            ->andWhere("isbedarfs = 1");
            $medication_array = $medis->fetchArray();
            
            
            $master_medi_ids = array_keys($medication_array);
            
            $med = new Medication();
            $master_medi_array = $med->getMedicationById($master_medi_ids,true);
            
            $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
            
            
            foreach ($medication_array as $key => $val) {
                $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']]['name'];
                $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                $medication_array[$key]['unit'] = $packaging_array[$val['id']]['unit'];
                $medication_array[$key]['concentration'] = $packaging_array[$val['id']]['concentration'];
                $medication_array[$key]['dosage_form'] = $packaging_array[$val['id']]['dosage_form'];
            }
            
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                                  => '1', //add debug info on devmode
                        'patient_drugplan_id'                    => $row['id'], //int
                        'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication'])      ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_drug'] != $row['drug'])            ? $order_meds[$row['id']]['medication_drug'] : $row['drug'], //string
                        'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']          != $row['dosage'])          ? $order_meds[$row['id']]['dosage']          : $row['dosage']   , //string
                        'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_comments'              => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']        != $row['comments'])        ? $order_meds[$row['id']]['comments']        : $row['comments']   , //string
                        'order_medication_dosage_interval'       => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_interval'] != $row['dosage_interval']) ? $order_meds[$row['id']]['dosage_interval'] : $row['dosage_interval']   , //string
                        'order_medication_concentration'         => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['concentration']   != $row['concentration'])   ? $order_meds[$row['id']]['concentration']   : $row['concentration']  , //string
                        'order_medication_dosage_form'           => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_form']     != $row['dosage_form'])     ? $order_meds[$row['id']]['dosage_form']     : $row['dosage_form']   , //string
                        'order_medication_unit'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['unit']            != $row['unit'])            ? $order_meds[$row['id']]['unit']            : $row['unit'], //string
                        'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    private function _iv_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
            }
            
            $medication_array = array();
            $medis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan indexBy medication_master_id')
            ->WhereIn("ipid" ,$ipid)
            ->andWhere("isdelete = 0")
            ->andWhere("isivmed = 1");
            $medication_array = $medis->fetchArray();
            
            
            $master_medi_ids = array_keys($medication_array);
            
            $med = new Medication();
            $master_medi_array = $med->getMedicationById($master_medi_ids,true);
            
            $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
            
            
            foreach ($medication_array as $key => $val) {
                $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']]['name'];
                $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                $medication_array[$key]['unit'] = $packaging_array[$val['id']]['unit'];
                $medication_array[$key]['concentration'] = $packaging_array[$val['id']]['concentration'];
                $medication_array[$key]['dosage_form'] = $packaging_array[$val['id']]['dosage_form'];
            }
            
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                                  => '1', //add debug info on devmode
                        'patient_drugplan_id'                    => $row['id'], //int
                        'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication'])      ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_drug'] != $row['drug'])            ? $order_meds[$row['id']]['medication_drug'] : $row['drug'], //string
                        'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']          != $row['dosage'])          ? $order_meds[$row['id']]['dosage']          : $row['dosage']   , //string
                        'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_comments'              => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']        != $row['comments'])        ? $order_meds[$row['id']]['comments']        : $row['comments']   , //string
                        'order_medication_concentration'         => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['concentration']   != $row['concentration'])   ? $order_meds[$row['id']]['concentration']   : $row['concentration']  , //string
                        'order_medication_dosage_form'           => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_form']     != $row['dosage_form'])     ? $order_meds[$row['id']]['dosage_form']     : $row['dosage_form']   , //string
                        'order_medication_unit'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['unit']            != $row['unit'])            ? $order_meds[$row['id']]['unit']            : $row['unit'], //string
                        'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    private function _isnutrition_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
            }
            
            $medication_array = array();
            $medis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan indexBy medication_master_id')
            ->WhereIn("ipid" ,$ipid)
            ->andWhere("isdelete = 0")
            ->andWhere("isnutrition = 1");
            $medication_array = $medis->fetchArray();
            
            
            $master_medi_ids = array_keys($medication_array);
            
            $med = new Nutrition();
            $master_medi_array = $med->master_medications_nutrition_get($master_medi_ids,true);
            
/*             echo "<pre/>";
            print_r($master_medi_array);exit(); */
            
            $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
            
            
            foreach ($medication_array as $key => $val) {
                $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']];
                $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                $medication_array[$key]['unit'] = $packaging_array[$val['id']]['unit'];
                $medication_array[$key]['concentration'] = $packaging_array[$val['id']]['concentration'];
                $medication_array[$key]['dosage_form'] = $packaging_array[$val['id']]['dosage_form'];
            }
            
            //count total meds
            $total_meds = count($medication_array);
            

            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                                  => '1', //add debug info on devmode
                        'patient_drugplan_id'                    => $row['id'], //int
                        'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name'] != $row['medication'])      ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_drug'] != $row['drug'])            ? $order_meds[$row['id']]['medication_drug'] : $row['drug'], //string
                        'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']          != $row['dosage'])          ? $order_meds[$row['id']]['dosage']          : $row['dosage']   , //string
                        'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_comments'              => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']        != $row['comments'])        ? $order_meds[$row['id']]['comments']        : $row['comments']   , //string
                        'order_medication_concentration'         => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['concentration']   != $row['concentration'])   ? $order_meds[$row['id']]['concentration']   : $row['concentration']  , //string
                        'order_medication_dosage_form'           => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage_form']     != $row['dosage_form'])     ? $order_meds[$row['id']]['dosage_form']     : $row['dosage_form']   , //string
                        'order_medication_unit'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['unit']            != $row['unit'])            ? $order_meds[$row['id']]['unit']            : $row['unit'], //string
                        'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    
    private function _intervall_medication_list()   {
        
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        //also the order id
        if (! empty($_REQUEST)) {
            
            
            $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
            $ipid = Pms_CommonData::getIpid($decid);
            $clientid = $this->clientid;
            
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            
            // get saved medications
            $order_meds = array();
            if( ! empty($_REQUEST['order_id'])){
                $order_id = $_REQUEST['order_id'];
                
                
                $order_details = array();
                $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
                
                if(!empty($order_details['PatientsOrdersMedication'])){
                    foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                        $order_meds[$med['patient_drugplan_id']] = $med;
                    }
                    
                }
            }
            
            $medication_array = array();
            $medis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlan indexBy medication_master_id')
            ->WhereIn("ipid" ,$ipid)
            ->andWhere("isdelete = 0")
            ->andWhere("scheduled = 1");
            $medication_array = $medis->fetchArray();
            
            
            $master_medi_ids = array_keys($medication_array);
            
            $med = new Medication();
            $master_medi_array = $med->getMedicationById($master_medi_ids,true);

            $packaging_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
/*             echo "<pre/>";
            print_r($packaging_array);exit(); */
            
            foreach ($medication_array as $key => $val) {
                $medication_array[$key]['medication'] = $master_medi_array[$val['medication_master_id']]['name'];
                $medication_array[$key]['drug'] = $packaging_array[$val['id']]['drug'];
                $medication_array[$key]['indication'] = $packaging_array[$val['id']]['indication']['name'];               
            }
            
            //count total meds
            $total_meds = count($medication_array);
            
            $all_meds = array();
            
            foreach ($medication_array as $row) {
                
                if( $row['isdelete'] == "0" || ($row['isdelete'] == "1" && array_key_exists($row['id'], $order_meds) ) )
                {
                    
                    $data =  array(
                        'debug'                                  => '1', //add debug info on devmode
                        'patient_drugplan_id'                    => $row['id'], //int
                        'order_medication_name'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_name']     != $row['medication'])      ? $order_meds[$row['id']]['medication_name'] : $row['medication'], //string
                        'order_medication_drug'                  => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['medication_drug']     != $row['drug'])            ? $order_meds[$row['id']]['medication_drug'] : $row['drug'], //string
                        'order_medication_dosage'                => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['dosage']              != $row['dosage'])          ? $order_meds[$row['id']]['dosage']          : $row['dosage']   , //string
                        'order_medication_dosage_refr'           =>  $row['dosage'],  //ISPC-2639 pct.1 Lore 23.07.2020
                        'order_medication_indication'            => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['indication']          != $row['indication'])      ? $order_meds[$row['id']]['indication']      : $row['indication']   , //string
                        'order_medication_comments'              => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['comments']            != $row['comments'])        ? $order_meds[$row['id']]['comments']        : $row['comments']   , //string
                        'order_medication_days_interval'         => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['days_interval']       != $row['days_interval'])   ? $order_meds[$row['id']]['days_interval']     : $row['days_interval']   , //string
                        'order_medication_administration_date'   => (!empty($order_meds[$row['id']]) && $order_meds[$row['id']]['administration_date'] != $row['administration_date']) ? date('d.m.Y',strtotime($order_meds[$row['id']]['administration_date'])) : date('d.m.Y',strtotime($row['administration_date'])), //string
                        'selected'                               => !(empty($order_meds[$row['id']])) ? 'checked="checked"' : ""   , //string
                    );
                    array_push($all_meds, $data);
                }
            }
            
            if($sort_col_dir == 'asc'){
                $all_meds_sort = Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_ASC);
            } else{
                $all_meds_sort= Pms_CommonData::array_sort($all_meds, $sort_col_name, SORT_DESC);
            }
            
            $all_meds = array_values($all_meds_sort);
            
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' =>  $total_meds,
                'recordsFiltered' => $total_meds, //TODO add a search in the datatable
                'data' => $all_meds
                
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
            
            
            
        }
    }
    
    
    private function _save_order_details(){
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        $return = array();
        if ($this->getRequest()->isPost()) {
            $post_data = array();
            
            $order_id = 0;
            $post_str = $_POST['order_data'];
            parse_str($_POST['order_data'],$post_data);
            //TODO-2872 Ancuta 24.03.2020
            // changes:
            // pct1)  allow "once" only, orders to be added EVEN if other orders already exist 
            // pct2)
            // pct3) a VERIFIED(green)  order  can  be set to OPEN(orange) - so user needs to verify again
            // pct5) Allow an order to be deleted ??!?!?!?
            
            
            // if patient has an opened paused/closed period
            // if the  new selected order date is before this period has started - then - marck period as deleted
            // if is olther then the period start - close te period 
            
           
            
            $order = array();
            if( ! empty($post_data['data']) && ! empty($post_data['data']['PatientsOrdersDetails'])){
                
                // first check id
                if( ! empty($post_data['data']['PatientsOrdersDetails']['id'])){
                    $order_id  = $post_data['data']['PatientsOrdersDetails']['id'];
                    $post_data['data']['id'] = $post_data['data']['PatientsOrdersDetails']['id'];
                
                    $order[$order_id]['id']= $post_data['data']['PatientsOrdersDetails']['id'];
                    $action_type = "edit";
                }

                //TODO-2872 Ancuta 25.03.2020 pct 5 - allow deletion of future verified orders
                if(!empty($order_id) && $post_data['data']['PatientsOrdersDetails']['status'] == 'canceled'){
                    $m_existing_ord = new PatientsOrdersDetails();
                    $existing_ord = $m_existing_ord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                    if($existing_ord){
                        $existing_ord_array = $existing_ord->toArray();

                        if($existing_ord_array['status'] == 'verified' && $existing_ord_array['order_date'] < date('Y-m-d')){
                            $return['error'] = '30';
                            $return['error_msgs'][] =  $this->translate('It is not allowed to delete VERIFIED order from the past! ');
                        } else {
                            // allow deleted??? 
                        }
                    }
                    
                }
                //-- 
                
                    
                
                
                
                // Check if patient is filled
                if( ! empty($post_data['data']['PatientsOrdersDetails']['patient'])){
                
                    $patientid = $post_data['data']['PatientsOrdersDetails']['patient'];
                    $decid = Pms_Uuid::decrypt($post_data['data']['PatientsOrdersDetails']['patient']);
                    $ipid = Pms_CommonData::getIpid($decid);
                
                    $post_data['data']['ipid'] = $ipid;
                    $order[$order_id]['ipid'] = $ipid;
                
                } else {
                    $return['error'] = '1';
                    $return['error_msgs'][] =  $this->translate('Patient is mandatory!');
                }
                
             
                
                // Check if order date is filled
                $order_Date = "";
                if( ! empty($post_data['data']['PatientsOrdersDetails']['order_date'])){
                    $post_data['data']['order_date'] = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));
                    $order_Date = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));
                    $order[$order_id]['order_date'] = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));;
                } else {
                    $return['error'] = '2';
                    $return['error_msgs'][] = $this->translate('Select order date!');
                }
        

                // get all orders 
                if( ! empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                    || (
                        empty($post_data['data']['PatientsOrdersDetails']['id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['step_id'])
                        )
                    ){
                    
                    $parent_verified_orders = array();
                    
                    if(! empty($post_data['data']['PatientsOrdersDetails']['parent_id'])){
                        $patent_order_details = PatientsOrdersDetailsTable::find_patient_ordersByParent($ipid,$post_data['data']['PatientsOrdersDetails']['parent_id']);
                        
                        if(!empty($patent_order_details))
                        {
                            foreach($patent_order_details as $or_id=>$ordata)
                            {
                                if($ordata['order_date'] > $order_Date && $ordata['status'] == "verified"){
                                    $parent_verified_orders[] = $ordata['id'];
                                }
                            }
                        }
                        
                        if ( !empty($parent_verified_orders) && $post_data['data']['PatientsOrdersDetails']['order_interval'] != "once" ){ //TODO-2872 Ancuta 24.03.2020 pct1 
                            $return['error'] = '12';
                            $return['error_msgs'][] = $this->translate('Orders that are after this were already verified/delivered - changes are NOT allowed!');
                        }
                        
                    } elseif (
                        empty($post_data['data']['PatientsOrdersDetails']['id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['step_id'])
                        )
                    {
                        $patent_order_details = PatientsOrdersDetailsTable::find_patient_following_orders($ipid,false,$order_Date);

                        $patient_has_future_orders = array();
                        if(!empty($patent_order_details))
                        {
                            foreach($patent_order_details as $or_id=>$ordata)
                            {
                                if(  $ordata['status'] != "canceled"  &&  $ordata['order_interval'] != "once"  ){ //TODO-3319 Ancuta 31.07.2020
                                    $patient_has_future_orders[] = $ordata['id'];
                                }
                            } 
                        }
                        
                        
                        if ( !empty($patient_has_future_orders) && $post_data['data']['PatientsOrdersDetails']['order_interval'] != "once" ){ //TODO-2872 Ancuta 24.03.2020 pct1 
                            $return['error'] = '12';
                            //$return['error_msgs'][] = $this->translate('Patient already has active orders in future. New order is not allowed here!');
                            $return['error_msgs'][] = $this->translate('Patient already has active orders in future. Only Bestell-Intervall:  Einmalig  are allowed');
                        }
                    }
                    
                } 
                else 
                {
                    
                }
                
                // needed for  messages
                if(empty($post_data['data']['PatientsOrdersDetails']['parent_id'])){
                    $order_action_type = 'parent_order_was_created';
                    
                } else{
                    if(empty($post_data['data']['PatientsOrdersDetails']['id'])){
                        $order_action_type = 'child_order_was_created';
                    }else{
                        $order_action_type = 'child_order_was_edited';
                    }
                }
                
                // check if order is parent 
                 if( ! empty($post_data['data']['PatientsOrdersDetails']['is_parent'])){
                     $is_parent = $post_data['data']['PatientsOrdersDetails']['is_parent'];
                 }    
                 
                 if($is_parent == "no" && $post_data['data']['PatientsOrdersDetails']['id'] == $post_data['data']['PatientsOrdersDetails']['parent_id'])
                 {
                     $post_data['data']['PatientsOrdersDetails']['id'] = 0 ;
                     $post_data['data']['parent_id'] = $post_data['data']['PatientsOrdersDetails']['parent_id'];
                     unset($post_data['data']['PatientsOrdersDetails']['id']);
                     
                 }
                 
                
                if( ! empty( $post_data['data']['PatientsOrdersDetails']['parent_id'])){
                    $order[$order_id]['parent_id']= $post_data['data']['PatientsOrdersDetails']['parent_id'];
                }
                
                
                // Check if order interval is filled
                if( ! empty($post_data['data']['PatientsOrdersDetails']['order_interval'])){
                    $post_data['data']['order_interval'] = $post_data['data']['PatientsOrdersDetails']['order_interval'];
                    
                    $order[$order_id]['order_interval'] = $post_data['data']['PatientsOrdersDetails']['order_interval'];
                    
                    if($post_data['data']['order_interval'] != "once" && empty($post_data['data']['PatientsOrdersDetails']['order_interval_options'])) {
                        $return['error'] = '4';
                        $return['error_msgs'][] =  $this->translate('Select order interval - options!');
                    } else {
                        if( is_array($post_data['data']['PatientsOrdersDetails']['order_interval_options']) ){
                            $post_data['data']['order_interval_options'] = implode(",",$post_data['data']['PatientsOrdersDetails']['order_interval_options']);
                            $order[$order_id]['order_interval_options'] = implode(",",$post_data['data']['PatientsOrdersDetails']['order_interval_options']);
                        } else {
                            $post_data['data']['order_interval_options'] =  $post_data['data']['PatientsOrdersDetails']['order_interval_options'];
                            $order[$order_id]['order_interval_options'] =  $post_data['data']['PatientsOrdersDetails']['order_interval_options'];
                        }
                        
                        
                    }
                    
                } else {
                    $return['error'] = '3';
                    $return['error_msgs'][] =  $this->translate('Select order interval!');
                }
                
                // Check if order status is filled 
                if( ! empty($post_data['data']['PatientsOrdersDetails']['status'])){
                    $post_data['data']['status'] = $post_data['data']['PatientsOrdersDetails']['status'];
                    $order[$order_id]['status'] =  $post_data['data']['PatientsOrdersDetails']['status'];
                    
                    if($post_data['data']['PatientsOrdersDetails']['status'] == "canceled" && empty($post_data['data']['PatientsOrdersDetails']['order_comment'])){
                        $return['error'] = '6';
                        $return['error_msgs'][] =  $this->translate('Please provide reason of cancel !');
                    } 
                    
                    if( ! empty($post_data['data']['PatientsOrdersDetails']['order_comment']))
                    {
                        $order[$order_id]['order_comment'] = $post_data['data']['PatientsOrdersDetails']['order_comment'];
                    }
                    
                } 
                
                
                if( empty($post_data['data']['PatientsOrdersDeliveryDates']['delivery_date'])){
                    if($post_data['data']['status'] == "verified"){
                        $return['error'] = '4';
                        $return['error_msgs'][] =  $this->translate('Select delivery date!');
                    }
                } else {
                    $order[$order_id]['PatientsOrdersDeliveryDates']['delivery_date'] = date("Y-m-d", strtotime($post_data['data']['PatientsOrdersDeliveryDates']['delivery_date']));
                }
                
                // Check and procces medication 
                if( ! empty($post_data['data']['PatientsOrdersMedication'])){
                    $med = 0 ; 
                    foreach($post_data['data']['PatientsOrdersMedication'] as $mrow=>$mval){
                        if( ! isset($mval['patient_drugplan_id'])){
                            unset($post_data['data']['PatientsOrdersMedication'][$mrow]);
                        }
                        
                        if( isset($mval['patient_drugplan_id'])){
                            $order[$order_id]['PatientsOrdersMedication'][$med] =  $mval;
                            $med++;
                        }
                    }
                    
                    if(empty($post_data['data']['PatientsOrdersMedication'])){
//                         $return['error'] = '3';
//                         $return['error_msgs'][] =  $this->translate('Select medications!');
                    }   
                } 
                else {
                   
//                     $return['error'] = '3';
//                     $return['error_msgs'][] =  $this->translate('Select medications!');
                }
                
                // Check and procces materials                    
                if( ! empty($post_data['data']['PatientsOrdersMaterials'])){
                    
                    $matr = Doctrine_Query::create()
                    ->select('*')
                    ->from('ClientOrderMaterials')
                    ->where('clientid =?', $this->logininfo->clientid)
                    ->fetchArray();
                    
                    $client_materials = array();
                    foreach($matr as $mk=>$mv){
                        $client_materials[$mv['category']][$mv['id']] = $mv['title'];
                        /* $client_materials[$mv['category']][$mv['id']] .= ' ( Einheit: '.$mv['unit'];
                        $client_materials[$mv['category']][$mv['id']] .= ' | PZN: '.$mv['pzn'];
                        $client_materials[$mv['category']][$mv['id']] .= ' | Stück/VE: '.$mv['pieces'].')'; */
                    }
                    
                    $post_data['data']['PatientsOrdersMaterials_post'] = $post_data['data']['PatientsOrdersMaterials'];
                    $post_data['data']['PatientsOrdersMaterials'] = array();
                    $m=0;
                    
                    //ISPC-2639 pct.3 Lore 23.07.2020
/*                     $quantity = array();
                    foreach($post_data['data']['PatientsOrdersMaterials_post']  as $category=>$items){
                        foreach($items as $item_id ){
                            $quantity['data']['PatientsOrdersMaterials_post'][$category][$item_id] += 1 ;
                        }
                    }
                    foreach($quantity['data']['PatientsOrdersMaterials_post']  as $category => $items){
                        foreach($items as $item_id => $item_id_val){
                            $post_data['data']['PatientsOrdersMaterials'][] = array(
                                'material_id'=>$item_id,
                                'material_category'=>$category,
                                'material_name'=>$client_materials[$category][$item_id],
                                'quantity'=> $item_id_val
                            );
                            
                            $order[$order_id]['PatientsOrdersMaterials'][] =  array(
                                'material_id'=>$item_id,
                                'material_category'=>$category,
                                'material_name'=>$client_materials[$category][$item_id],
                                'quantity'=> $item_id_val
                            );
                            
                        }
                    } */
                    //.
                    
                    foreach($post_data['data']['PatientsOrdersMaterials_post']  as $category=>$items){
                        
                        foreach($items as $item_id => $item_id_val){
                            if(!empty($item_id_val)){
                                $post_data['data']['PatientsOrdersMaterials'][] = array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             //ISPC-2639 pct.3 Lore 10.07.2020
                                );
                                
                                
                                $order[$order_id]['PatientsOrdersMaterials'][] =  array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             //ISPC-2639 pct.3 Lore 10.07.2020
                                );
                            }
                        }
                    }
                    
                } 
                else
                {
                    //NOT MANDATORY
                    //$return['error'] = '5';
                    //$return['error_msgs'][] =  $this->translate('Select additional materials!');
                }
                
                    
                // Check and procces recipients
                                    
                if( ! empty($post_data['data']['PatientsOrdersRecipients'])){
                    $recipient_inc = 0 ;
                    foreach($post_data['data']['PatientsOrdersRecipients'] as $rrow=>$rval){
                        
                        $order[$order_id]['PatientsOrdersRecipients'][$recipient_inc]['recipient_id'] =  $rval;
                        $recipient_inc++;
                        
                    }
                    
                    foreach($post_data['data']['PatientsOrdersRecipients'] as $rrow=>$rval){
                        unset($post_data['data']['PatientsOrdersRecipients'][$rrow]);
                        $post_data['data']['PatientsOrdersRecipients'][$rrow]['recipient_id'] = $rval;
                    }
                    
                } else { 
                    $return['error'] = '4';
                    $return['error_msgs'][] =  $this->translate('Select pharmacy!');
                }
                    
                if( ! isset( $return['error']) ||  $return['error'] == 0){
                    
                    $message_action_done = "";
                    // first we close period - if needed
                    $patients_periods_statuses = PatientsOrdersPeriodsTable::find_all_patients_periods(array($ipid),false,true);// retrive only opened
                    
                    
                    if(!empty($ipid) && !empty($patients_periods_statuses)){
                    
                        $last_active_period = array();
                        if(!empty($patients_periods_statuses) && count($patients_periods_statuses) == "1"){

                            
                            $last_active_period = $patients_periods_statuses[0];
                            $period_details = array();
                            $period_details['ipid']  = $ipid;
                            $period_details['action_status']  = $last_active_period['action_status'];
                            $period_details['start_date']  = $last_active_period['start_date'];
                            
                            if($order_Date < $last_active_period['start_date']){
                                $period_details['isdelete'] = "1";
                            }else{
                                $period_details['end_date'] = $order_Date;
                            }
                            if($last_active_period['action_status'] == "stopped"){
                                $q = Doctrine_Query::create()
                                ->update('PatientsOrdersDetails')
                                ->set('action_status', '?', 'opened')
                                ->where("ipid= ?", $ipid);
                                $q->execute();
                                // IS THIS NEEDED????
//                                 $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
                            }
                            
                    
                            if(!empty($period_details)){
                                $x_close_period =  PatientsOrdersPeriodsTable::getInstance()->findOrCreateOneBy('id', $last_active_period['id'], $period_details);
                                
                                // SEND REACTIVATION MESSAGE
                                $message_action_done = "orders_reactivated";
                                
                            }
                        } else {
                            foreach($patients_periods_statuses as $k=>$op_periods){
                    
                                $period_details = array();
                                $period_details['ipid']  = $ipid;
                                $period_details['action_status']  = $op_periods['action_status'];
                                $period_details['start_date']  = $op_periods['start_date'];
                                
                                if($order_Date < $op_periods['start_date']){
                                    $period_details['isdelete'] = "1";
                                }else{
                                    $period_details['end_date'] = $order_Date;
                                }
                                if(!empty($period_details)){
                                    $x_close_period =  PatientsOrdersPeriodsTable::getInstance()->findOrCreateOneBy('id', $op_periods['id'], $period_details);
                                    if($last_active_period['action_status'] == "stopped"){
                                        $q = Doctrine_Query::create()
                                        ->update('PatientsOrdersDetails')
                                        ->set('action_status', '?', 'opened')
                                        ->where("ipid= ?", $ipid);
                                        $q->execute();
                                        // IS THIS NEEDED????
                                        //                                 $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
                                    }
                                    
                                }
                            }
                            // SEND REACTIVATION MESSAGE
                            $message_action_done = "orders_reactivated";
                            
                        }
                        
                    }
                    
                  	// ISPC-2464  
                    $parent_id_of_order = $order[$order_id]['parent_id'];
                    
                    // if future orders  are only one time - to not update 
                    $patient_PN_orders = array();
                    $patient_PN_orders = $this->get_previous_and_next_order($ipid,$order_Date,$parent_id_of_order,true);

                   
                    // check if any NEXT order is saved
                    if(!empty($patient_PN_orders[$ipid]['next']) && $post_data['data']['PatientsOrdersDetails']['order_interval'] != "once"  ){ //TODO-2872 Ancuta 24.03.2020 pct1)  
                        
                        foreach($patient_PN_orders[$ipid]['next'] as $kn=>$next_ord){
//                             if($next_ord['order_is_saved'] == "1" && ($next_ord['status'] != 'planned' || $next_ord['status'] == null) ){
//                             if($next_ord['order_is_saved'] == "1" && ($next_ord['status'] != '$next_ord['status'] != 'planned'' || $next_ord['status'] == null) ){
                            if($next_ord['order_is_saved'] == "1" && $next_ord['order_interval'] != "once"){//TODO-3319 Ancuta 31.07.2020                                
                                
                                foreach($next_ord['PatientsOrdersRecipients'] as $kr=>$reciepient){
                                    $recipients[]  = $reciepient['recipient_id'];
                                }
                                
                                if($order[$order_id]['status'] == "canceled"){
                                    $message_action_done_future = "order_was_canceled";
                                } else{
                                    $message_action_done_future = "order_was_edited_by_updateing_prevoius";
                                }
                                
                                $order_creator_next = $next_ord['create_user'];
                                if(empty($order_creator_next)){
                                    $order_creator_next = 0 ;
                                }
                                if( ! empty($message_action_done_future)  )
                                {
                                    $send_msg = new Messages();
                                    $msgs = $send_msg->order_action_messages($ipid,$message_action_done_future, $recipients, $order_creator_next);
                                }
                                
                                // ADD TO LOG  !!!!!!!!!!!!!!!!!!!!!!!
                                $new_pause = new  PatientsOrdersDetailsLog();
                                $new_pause->order_id = $order_id;
                                $new_pause->details = serialize($next_ord);
                                $new_pause->save();
                                
                                
                                // DELETE following orders
                                $matord = new PatientsOrdersDetails();
                                $matr = $matord->getTable()->find($next_ord['current_order_id'], Doctrine_Core::HYDRATE_RECORD);
                                $matr->isdelete = 1;
                                $matr->save();
                            }
                        }
                    }
                    //--
                    
                    $status_of_order = "";
                    if( empty($order_id)) {
                        
                        $parent_of_order = $order[$order_id]['parent_id'];
                        $step_parent_of_order= $order[$order_id]['step_parent_id'];
                        $status_of_order =  $order[$order_id]['status'];
                        
                        // check if order has parrent!
                        if(!empty($parent_of_order)){
                            $po = Doctrine_Core::getTable('PatientsOrdersDetails')->findBy('id', $parent_of_order);
                            $parent_order = $po->toArray();
                            $order_creator = $parent_order[0]['create_user'];
                            
                        }    
                    
                        
                        if($order[$order_id]['status'] != "canceled"){
               
                            $xx = new Doctrine_Collection('PatientsOrdersDetails');
                            $xx->fromArray($order);
                            $xx->save();
                        
                            $order_keys = $xx->getPrimaryKeys();
                            $order_id = $order_keys[0];
                            
                            
                            $matord = new PatientsOrdersDetails();
                            $matr = $matord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                            $matr->step_parent_id = $order_id;
                            $matr->save();
                            
                            $action_type = "new";
                            
                            // SEND SAVED MESSAGE
                            if(empty($message_action_done)){
                                
                                if(empty($parent_of_order) && empty($step_parent_of_order)){
                                    $message_action_done = "new_order_was_".$status_of_order;
                                } 
                                else
                                {
                                    $message_action_done = "following_order_was_".$status_of_order;
                                }
                            }
                            
                        }  
                        else
                        {
                            if( ! empty($order[$order_id]['parent_id'] )){
                                // cancel order
                                $xx = new Doctrine_Collection('PatientsOrdersDetails');
                                $xx->fromArray($order);
                                $xx->save();
                                
                                $order_keys = $xx->getPrimaryKeys();
                                $order_id = $order_keys[0];
                                
                                
                                $matord = new PatientsOrdersDetails();
                                $matr = $matord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                                $matr->step_parent_id = $order_id;
                                $matr->save();
                                
                                $action_type = "new_child";
                                
                                $message_action_done = "order_was_canceled";
                            }          
                        }       
                    }
                    else
                    {
                        
                        // update Medications - set isdelete   1
                        $q = Doctrine_Query::create()
                        ->update('PatientsOrdersMedication')
                        ->set('isdelete','1')
                        ->where("order_id= ?", $order_id);
                        $q->execute();
                        
                        
                        // update Materials - set isdelete   1
                        $q = Doctrine_Query::create()
                        ->update('PatientsOrdersMaterials')
                        ->set('isdelete','1')
                        ->where("order_id= ?", $order_id);
                        $q->execute();
                        
                        
                        $x =  PatientsOrdersDetailsTable::getInstance()->findOrCreateOneBy('id', $order_id, $order[$order_id]);
                        $edited = $x->toArray();
           
                        
                        $status_of_order =  $edited['status'];
                        $order_creator = $edited['create_user'];
                        if($order[$order_id]['status'] == "canceled"){
                            $message_action_done = "order_was_canceled";
                        } else{
                            $message_action_done = "order_was_edited";
                        }
                    }
                    
                    $recipients = array();
                    if(isset($order_id) && !empty($order_id)){
                        $order_details = array();
                        $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
             
                        if(!empty($order_details)){
                        
                            if(!empty($order_details['PatientsOrdersRecipients'])){
                                foreach($order_details['PatientsOrdersRecipients'] as $kr=>$reciepient){
                                        $recipients[]  = $reciepient['recipient_id'];
                                }
                            }
                        }
                    }
                    
                    if(empty($order_creator)){
                        $order_creator = 0 ;
                    }
                    if( ! empty($message_action_done)  ) 
                    {
                        $send_msg = new Messages();
                        $msgs = $send_msg->order_action_messages($ipid,$message_action_done, $recipients, $order_creator);
                    }
                    
                    
                    
                    
                    $return['error'] = '0';
                    $return['success'] = '1';
                    
                    // save_log
                    $new_pause = new  PatientsOrdersDetailsLog();
                    $new_pause->order_id = $order_id;
                    $new_pause->details = $post_str;
                    $new_pause->save();
                    
                    
                    if($status_of_order == "active" || $status_of_order == "verified" ){
                        
                        $current_user  = User::getUsersNiceName(array($this->logininfo->userid));
                        $current_user_name = $current_user [$this->logininfo->userid]['nice_name'];

                        $course_title = "";
                        if($status_of_order == "active"){
                            $course_title = "Es wurde durch ".$current_user_name." eine Bestellung ausgelöst. ";
                        } else {
                            $course_title = "Es wurde durch ".$current_user_name." eine Bestellung bestätigt.";
                        }
                        
                        
                        $cust = array();
                        $cust['ipid'] = $ipid;
                        $cust['course_date'] = date("Y-m-d H:i");
                        $cust['course_type'] = "BS";
                        $cust['course_title'] = $course_title;
                        $cust['tabname'] = "order_management";
                        $cust['user_id'] = $this->logininfo->userid;
                        $cust['recordid'] = $order_id;
                        $cust['done_name'] = "order_management_".$status_of_order;
                        $pc = new PatientCourse();
                        $pc_id_save = $pc->set_new_record($cust);
                        
                    }
          
                    //generate pdf - in patient
                    if($status_of_order == "verified"){
                        $this->_generate_order_pdf($order_id, $patientid);
                    }
                }
                    
                if(!empty($return['error_msgs'])){
                    $return['errors'] = implode('<br/>',$return['error_msgs']); 
                }
                
                $this->_helper->getHelper('json')->sendJson($return);
                exit; //for readability
            }
            else
            {
                $return['error'] = '1';
                $return['error_msgs'][] =  "DATA MISSSING ";
                
                if(!empty($return['error_msgs'])){
                    $return['errors'] = implode('<br/>',$return['error_msgs']);
                }
                $this->_helper->getHelper('json')->sendJson($return);
                exit; //for readability
            }
        }
        
        
        
    }
    
    /**
     * @author Ancuta
     * // ISPC-2464 
     * @throws Zend_Exception
     */
    private function _save_current_order_details(){
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        $return = array();
        if ($this->getRequest()->isPost()) {
            $post_data = array();
            
            $order_id = 0;
            $post_str = $_POST['order_data'];
            parse_str($_POST['order_data'],$post_data);
            
 
            
            // if patient has an opened paused/closed period
            // if the  new selected order date is before this period has started - then - marck period as deleted
            // if is olther then the period start - close te period 
 
            $order = array();
            if( ! empty($post_data['data']) && ! empty($post_data['data']['PatientsOrdersDetails'])){
                
                // first check id
                if( ! empty($post_data['data']['PatientsOrdersDetails']['id'])){
                    $order_id  = $post_data['data']['PatientsOrdersDetails']['id'];
                    $post_data['data']['id'] = $post_data['data']['PatientsOrdersDetails']['id'];
                
                    $order[$order_id]['id']= $post_data['data']['PatientsOrdersDetails']['id'];
                    $action_type = "edit";
                }
                
                // Check if patient is filled
                if( ! empty($post_data['data']['PatientsOrdersDetails']['patient'])){
                
                    $patientid = $post_data['data']['PatientsOrdersDetails']['patient'];
                    $decid = Pms_Uuid::decrypt($post_data['data']['PatientsOrdersDetails']['patient']);
                    $ipid = Pms_CommonData::getIpid($decid);
                
                    $post_data['data']['ipid'] = $ipid;
                    $order[$order_id]['ipid'] = $ipid;
                
                } else {
                    $return['error'] = '1';
                    $return['error_msgs'][] =  $this->translate('Patient is mandatory!');
                }
                
             
                
                // Check if order date is filled
                $order_Date = "";
                if( ! empty($post_data['data']['PatientsOrdersDetails']['order_date'])){
                    $post_data['data']['order_date'] = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));
                    $order_Date = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));
                    $order[$order_id]['order_date'] = date("Y-m-d",strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));;
                } else {
                    $return['error'] = '2';
                    $return['error_msgs'][] = $this->translate('Select order date!');
                }
        
                
                //TODO-2872 Ancuta 25.03.2020 pct 5 - allow deletion of future verified orders
                if(!empty($order_id) && $post_data['data']['PatientsOrdersDetails']['status'] == 'canceled'){
                    $m_existing_ord = new PatientsOrdersDetails();
                    $existing_ord = $m_existing_ord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                    if($existing_ord){
                        $existing_ord_array = $existing_ord->toArray();
                        
                        if($existing_ord_array['status'] == 'verified' && $existing_ord_array['order_date'] < date('Y-m-d')){
                            $return['error'] = '30';
                            $return['error_msgs'][] =  $this->translate('It is not allowed to delete VERIFIED order from the past! ');
                        } else {
                            // allow deleted???
                        }
                    }
                    
                }
                //-- 

                // get all orders 
                if( ! empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                    || (
                        empty($post_data['data']['PatientsOrdersDetails']['id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['step_id'])
                        )
                    ){
                    
                    $parent_verified_orders = array();
                    
                    if(! empty($post_data['data']['PatientsOrdersDetails']['parent_id'])){
                        //IF CURRENT ONLY - we allow change- even if verified in future 
/*                         $patent_order_details = PatientsOrdersDetailsTable::find_patient_ordersByParent($ipid,$post_data['data']['PatientsOrdersDetails']['parent_id']);
                        
                        if(!empty($patent_order_details))
                        {
                            foreach($patent_order_details as $or_id=>$ordata)
                            {
                                if($ordata['order_date'] > $order_Date && $ordata['status'] == "verified"){
                                    $parent_verified_orders[] = $ordata['id'];
                                }
                            }
                        }
                        
                        if ( !empty($parent_verified_orders) ){
                            $return['error'] = '12';
                            $return['error_msgs'][] = $this->translate('Orders that are after this were already verified/delivered - changes are NOT allowed!');
                        } */
                        
                    } elseif (
                        empty($post_data['data']['PatientsOrdersDetails']['id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['parent_id'])
                        && empty($post_data['data']['PatientsOrdersDetails']['step_id'])
                        )
                    {
                        $patent_order_details = PatientsOrdersDetailsTable::find_patient_following_orders($ipid,false,$order_Date);

                        $patient_has_future_orders = array();
                        if(!empty($patent_order_details))
                        {
                            foreach($patent_order_details as $or_id=>$ordata)
                            {
                                if(  $ordata['status'] != "canceled"){
                                    $patient_has_future_orders[] = $ordata['id'];
                                }
                            } 
                        }
                        
                        
                        if ( !empty($patient_has_future_orders) ){
                            $return['error'] = '12';
                            $return['error_msgs'][] = $this->translate('Patient already has active orders in future. New order is not allowed here!');
                        }
                    }
                    
                } 
                else 
                {
                    
                }
                
                
                
                // needed for  messages
                if(empty($post_data['data']['PatientsOrdersDetails']['parent_id'])){
                    $order_action_type = 'parent_order_was_created';
                    
                } else{
                    if(empty($post_data['data']['PatientsOrdersDetails']['id'])){
                        $order_action_type = 'child_order_was_created';
                    }else{
                        $order_action_type = 'child_order_was_edited';
                    }
                }
                
                // check if order is parent 
                 if( ! empty($post_data['data']['PatientsOrdersDetails']['is_parent'])){
                     $is_parent = $post_data['data']['PatientsOrdersDetails']['is_parent'];
                 }    
                 
                 if($is_parent == "no" && $post_data['data']['PatientsOrdersDetails']['id'] == $post_data['data']['PatientsOrdersDetails']['parent_id'])
                 {
                     $post_data['data']['PatientsOrdersDetails']['id'] = 0 ;
                     $post_data['data']['parent_id'] = $post_data['data']['PatientsOrdersDetails']['parent_id'];
                     unset($post_data['data']['PatientsOrdersDetails']['id']);
                     
                 }
                 
                
                if( ! empty( $post_data['data']['PatientsOrdersDetails']['parent_id'])){
                    $order[$order_id]['parent_id']= $post_data['data']['PatientsOrdersDetails']['parent_id'];
                }
                
                
                // Check if order interval is filled
                if( ! empty($post_data['data']['PatientsOrdersDetails']['order_interval'])){
                    $post_data['data']['order_interval'] = $post_data['data']['PatientsOrdersDetails']['order_interval'];
                    
                    $order[$order_id]['order_interval'] = $post_data['data']['PatientsOrdersDetails']['order_interval'];
                    
                    if($post_data['data']['order_interval'] != "once" && empty($post_data['data']['PatientsOrdersDetails']['order_interval_options'])) {
                        $return['error'] = '4';
                        $return['error_msgs'][] =  $this->translate('Select order interval - options!');
                    } else {
                        if( is_array($post_data['data']['PatientsOrdersDetails']['order_interval_options']) ){
                            $post_data['data']['order_interval_options'] = implode(",",$post_data['data']['PatientsOrdersDetails']['order_interval_options']);
                            $order[$order_id]['order_interval_options'] = implode(",",$post_data['data']['PatientsOrdersDetails']['order_interval_options']);
                        } else {
                            $post_data['data']['order_interval_options'] =  $post_data['data']['PatientsOrdersDetails']['order_interval_options'];
                            $order[$order_id]['order_interval_options'] =  $post_data['data']['PatientsOrdersDetails']['order_interval_options'];
                        }
                        
                        
                    }
                    
                } else {
                    $return['error'] = '3';
                    $return['error_msgs'][] =  $this->translate('Select order interval!');
                }
                
                // Check if order status is filled 
                if( ! empty($post_data['data']['PatientsOrdersDetails']['status'])){
                    $post_data['data']['status'] = $post_data['data']['PatientsOrdersDetails']['status'];
                    $order[$order_id]['status'] =  $post_data['data']['PatientsOrdersDetails']['status'];
                    
                    if($post_data['data']['PatientsOrdersDetails']['status'] == "canceled" && empty($post_data['data']['PatientsOrdersDetails']['order_comment'])){
                        $return['error'] = '6';
                        $return['error_msgs'][] =  $this->translate('Please provide reason of cancel !');
                    } 
                    
                    if( ! empty($post_data['data']['PatientsOrdersDetails']['order_comment']))
                    {
                        $order[$order_id]['order_comment'] = $post_data['data']['PatientsOrdersDetails']['order_comment'];
                    }
                    
                } 
                
                
                if( empty($post_data['data']['PatientsOrdersDeliveryDates']['delivery_date'])){
                    if($post_data['data']['status'] == "verified"){
                        $return['error'] = '4';
                        $return['error_msgs'][] =  $this->translate('Select delivery date!');
                    }
                } else {
                    $order[$order_id]['PatientsOrdersDeliveryDates']['delivery_date'] = date("Y-m-d", strtotime($post_data['data']['PatientsOrdersDeliveryDates']['delivery_date']));
                }
                
                // Check and procces medication 
                if( ! empty($post_data['data']['PatientsOrdersMedication'])){
                    $med = 0 ; 
                    foreach($post_data['data']['PatientsOrdersMedication'] as $mrow=>$mval){
                        if( ! isset($mval['patient_drugplan_id'])){
                            unset($post_data['data']['PatientsOrdersMedication'][$mrow]);
                        }
                        
                        if( isset($mval['patient_drugplan_id'])){
                            $order[$order_id]['PatientsOrdersMedication'][$med] =  $mval;
                            $med++;
                        }
                    }
                    
                    if(empty($post_data['data']['PatientsOrdersMedication'])){
//                         $return['error'] = '3';
//                         $return['error_msgs'][] =  $this->translate('Select medications!');
                    }   
                } 
                else {
                   
//                     $return['error'] = '3';
//                     $return['error_msgs'][] =  $this->translate('Select medications!');
                }
                
                // Check and procces materials                    
                if( ! empty($post_data['data']['PatientsOrdersMaterials'])){
                    
                    $matr = Doctrine_Query::create()
                    ->select('*')
                    ->from('ClientOrderMaterials')
                    ->where('clientid =?', $this->logininfo->clientid)
                    ->fetchArray();
                    
                    $client_materials = array();
                    foreach($matr as $mk=>$mv){
                        $client_materials[$mv['category']][$mv['id']] = $mv['title'];
                        /* $client_materials[$mv['category']][$mv['id']] .= ' ( Einheit: '.$mv['unit'];
                        $client_materials[$mv['category']][$mv['id']] .= ' | PZN: '.$mv['pzn'];
                        $client_materials[$mv['category']][$mv['id']] .= ' | Stück/VE: '.$mv['pieces'].')'; */
                    }
                    
                    $post_data['data']['PatientsOrdersMaterials_post'] = $post_data['data']['PatientsOrdersMaterials'];
                    $post_data['data']['PatientsOrdersMaterials'] = array();
                    $m=0;
                    
                    //ISPC-2639 pct.3 Lore 23.07.2020
/*                     $quantity = array();
                    foreach($post_data['data']['PatientsOrdersMaterials_post']  as $category=>$items){
                        foreach($items as $item_id ){
                            $quantity['data']['PatientsOrdersMaterials_post'][$category][$item_id] += 1 ;
                        }
                    }
                    foreach($quantity['data']['PatientsOrdersMaterials_post']  as $category => $items){
                        foreach($items as $item_id => $item_id_val){
                                $post_data['data']['PatientsOrdersMaterials'][] = array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             
                                );
                                
                                $order[$order_id]['PatientsOrdersMaterials'][] =  array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             
                                );
                            
                        }
                    } */
                    //.
                    foreach($post_data['data']['PatientsOrdersMaterials_post']  as $category=>$items){
                        
                        foreach($items as $item_id => $item_id_val){
                            if(!empty($item_id_val)){
                                $post_data['data']['PatientsOrdersMaterials'][] = array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             //ISPC-2639 pct.3 Lore 10.07.2020
                                );
                                
                                
                                $order[$order_id]['PatientsOrdersMaterials'][] =  array(
                                    'material_id'=>$item_id,
                                    'material_category'=>$category,
                                    'material_name'=>$client_materials[$category][$item_id],
                                    'quantity'=> $item_id_val                                             //ISPC-2639 pct.3 Lore 10.07.2020
                                );
                            }
                        }
                    }
                } 
                else
                {
                    //NOT MANDATORY
                    //$return['error'] = '5';
                    //$return['error_msgs'][] =  $this->translate('Select additional materials!');
                }
                
                    
                // Check and procces recipients
                                    
                if( ! empty($post_data['data']['PatientsOrdersRecipients'])){
                    $recipient_inc = 0 ;
                    foreach($post_data['data']['PatientsOrdersRecipients'] as $rrow=>$rval){
                        
                        $order[$order_id]['PatientsOrdersRecipients'][$recipient_inc]['recipient_id'] =  $rval;
                        $recipient_inc++;
                        
                    }
                    
                    foreach($post_data['data']['PatientsOrdersRecipients'] as $rrow=>$rval){
                        unset($post_data['data']['PatientsOrdersRecipients'][$rrow]);
                        $post_data['data']['PatientsOrdersRecipients'][$rrow]['recipient_id'] = $rval;
                    }
                    
                } else { 
                    $return['error'] = '4';
                    $return['error_msgs'][] =  $this->translate('Select pharmacy!');
                }
                    
                if( ! isset( $return['error']) ||  $return['error'] == 0){
                    
                    $message_action_done = "";
                    // first we close period - if needed
                    $patients_periods_statuses = PatientsOrdersPeriodsTable::find_all_patients_periods(array($ipid),false,true);// retrive only opened
                    
                    $patient_PN_orders = array();
                    $patient_PN_orders = $this->get_previous_and_next_order($ipid,$order_Date,false);

//                    dd($patient_PN_orders[$ipid]['next']['order_is_saved']);
//                    dd($patient_PN_orders[$ipid]['previous']);

                    // check if followeing orders ia already saved. If it is saved do nothing
                    // if it is not saved - get data from last parent with stand_alone = 0 
                    if($patient_PN_orders[$ipid]['next']['order_is_saved'] == 0 ){ // It has following ORDERS
                        
                        // proccess data and save for the next 
                        $next_order_arr = array();
                        $next_order_arr[0]['ipid'] = $ipid; 
                        $next_order_arr[0]['parent_id'] = $patient_PN_orders[$ipid]['previous']['parent_id']; 
                        $next_order_arr[0]['order_date'] = $patient_PN_orders[$ipid]['next']['current_order_date']; 
                        $next_order_arr[0]['order_interval'] = $patient_PN_orders[$ipid]['previous']['order_interval']; 
                        $next_order_arr[0]['order_interval_options'] = $patient_PN_orders[$ipid]['previous']['order_interval_options']; 
                        $next_order_arr[0]['order_comment'] = $patient_PN_orders[$ipid]['previous']['order_comment']; 
                        $next_order_arr[0]['status'] = null;
                         
                        $kx = 0 ;
                        foreach($patient_PN_orders[$ipid]['previous']['PatientsOrdersMedication'] as $k_pvm=>$pvm){
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['patient_drugplan_id'] = $pvm['patient_drugplan_id']; 
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['medication_name'] = $pvm['medication_name']; 
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['dosage'] = $pvm['dosage']; 
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['packaging'] = $pvm['packaging']; 
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['volume'] = $pvm['volume']; 
                            $next_order_arr[0]['PatientsOrdersMedication'][$kx]['kcal'] = $pvm['kcal']; 
                            $kx++;
                            
                        }
                        $kxm = 0 ;
                        foreach($patient_PN_orders[$ipid]['previous']['PatientsOrdersMaterials'] as $k_pvmat=>$pvmat){
                            $next_order_arr[0]['PatientsOrdersMaterials'][$kxm]['material_id'] = $pvmat['material_id']; 
                            $next_order_arr[0]['PatientsOrdersMaterials'][$kxm]['material_category'] = $pvmat['material_category']; 
                            $next_order_arr[0]['PatientsOrdersMaterials'][$kxm]['material_name'] = $pvmat['material_name']; 
                            $kxm++;
                        }
                        $kxu = 0 ;
                        foreach($patient_PN_orders[$ipid]['previous']['PatientsOrdersRecipients'] as $k_pvr=>$pvr){
                            $next_order_arr[0]['PatientsOrdersRecipients'][$kxu]['recipient_id'] = $pvr['recipient_id']; 
                            $kxu++;
                        }
                        
//                         dd($patient_PN_orders[$ipid]['previous'],$next_order_arr);
//                         dd($next_order_arr);
                        $xx_next = new Doctrine_Collection('PatientsOrdersDetails');
                        $xx_next->fromArray($next_order_arr);
                        $xx_next->save();
                        
                        $next_order_keys = $xx_next->getPrimaryKeys();
                        $next_order_id = $next_order_keys[0];
                        
                        
                        $matord_next = new PatientsOrdersDetails();
                        $matr_next = $matord_next->getTable()->find($next_order_id, Doctrine_Core::HYDRATE_RECORD);
                        $matr_next->step_parent_id = $next_order_id;
                        $matr_next->save();
                    }
                    
                    if(!empty($ipid) && !empty($patients_periods_statuses)){
                    
                        $last_active_period = array();
                        if(!empty($patients_periods_statuses) && count($patients_periods_statuses) == "1"){

                            
                            $last_active_period = $patients_periods_statuses[0];
                            $period_details = array();
                            $period_details['ipid']  = $ipid;
                            $period_details['action_status']  = $last_active_period['action_status'];
                            $period_details['start_date']  = $last_active_period['start_date'];
                            
                            if($order_Date < $last_active_period['start_date']){
                                $period_details['isdelete'] = "1";
                            }else{
                                $period_details['end_date'] = $order_Date;
                            }
                            if($last_active_period['action_status'] == "stopped"){
                                $q = Doctrine_Query::create()
                                ->update('PatientsOrdersDetails')
                                ->set('action_status', '?', 'opened')
                                ->where("ipid= ?", $ipid);
                                $q->execute();
                                // IS THIS NEEDED????
//                                 $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
                            }
                            
                    
                            if(!empty($period_details)){
                                $x_close_period =  PatientsOrdersPeriodsTable::getInstance()->findOrCreateOneBy('id', $last_active_period['id'], $period_details);
                                
                                // SEND REACTIVATION MESSAGE
                                $message_action_done = "orders_reactivated";
                                
                            }
                        } else {
                            foreach($patients_periods_statuses as $k=>$op_periods){
                    
                                $period_details = array();
                                $period_details['ipid']  = $ipid;
                                $period_details['action_status']  = $op_periods['action_status'];
                                $period_details['start_date']  = $op_periods['start_date'];
                                
                                if($order_Date < $op_periods['start_date']){
                                    $period_details['isdelete'] = "1";
                                }else{
                                    $period_details['end_date'] = $order_Date;
                                }
                                if(!empty($period_details)){
                                    $x_close_period =  PatientsOrdersPeriodsTable::getInstance()->findOrCreateOneBy('id', $op_periods['id'], $period_details);
                                    if($last_active_period['action_status'] == "stopped"){
                                        $q = Doctrine_Query::create()
                                        ->update('PatientsOrdersDetails')
                                        ->set('action_status', '?', 'opened')
                                        ->where("ipid= ?", $ipid);
                                        $q->execute();
                                        // IS THIS NEEDED????
                                        //                                 $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
                                    }
                                    
                                }
                            }
                            // SEND REACTIVATION MESSAGE
                            $message_action_done = "orders_reactivated";
                            
                        }
                        
                    }
                    
                    
                    $status_of_order = "";
                    if( empty($order_id)) {
                        
                        $parent_of_order = $order[$order_id]['parent_id'];
                        $step_parent_of_order= $order[$order_id]['step_parent_id'];
                        $status_of_order =  $order[$order_id]['status'];
                        
                        // check if order has parrent!
                        if(!empty($parent_of_order)){
                            $po = Doctrine_Core::getTable('PatientsOrdersDetails')->findBy('id', $parent_of_order);
                            $parent_order = $po->toArray();
                            $order_creator = $parent_order[0]['create_user'];
                        }
                        
                        // save curent as stand alone - also save 
                        if($order[$order_id]['status'] != "canceled"){
               
                            $order[$order_id]['stand_alone'] = "1";
                            
                            $xx = new Doctrine_Collection('PatientsOrdersDetails');
                            $xx->fromArray($order);
                            $xx->save();
                        
                            $order_keys = $xx->getPrimaryKeys();
                            $order_id = $order_keys[0];
                            
                            
                            $matord = new PatientsOrdersDetails();
                            $matr = $matord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                            $matr->step_parent_id = $order_id;
                            $matr->save();
                            
                            $action_type = "new";
                            
                            // SEND SAVED MESSAGE
                            if(empty($message_action_done)){
                                
                                if(empty($parent_of_order) && empty($step_parent_of_order)){
                                    $message_action_done = "new_order_was_".$status_of_order;
                                } 
                                else
                                {
                                    $message_action_done = "following_order_was_".$status_of_order;
                                }
                            }
                            
                        }  
                        else
                        {
                            if( ! empty($order[$order_id]['parent_id'] )){
                                // cancel order
                                $xx = new Doctrine_Collection('PatientsOrdersDetails');
                                $xx->fromArray($order);
                                $xx->save();
                                
                                $order_keys = $xx->getPrimaryKeys();
                                $order_id = $order_keys[0];
                                
                                
                                $matord = new PatientsOrdersDetails();
                                $matr = $matord->getTable()->find($order_id, Doctrine_Core::HYDRATE_RECORD);
                                $matr->step_parent_id = $order_id;
                                $matr->save();
                                
                                $action_type = "new_child";
                                
                                $message_action_done = "order_was_canceled";
                            }          
                        }       
                    }
                    else
                    {
                        
                        // update Medications - set isdelete   1
                        $q = Doctrine_Query::create()
                        ->update('PatientsOrdersMedication')
                        ->set('isdelete','1')
                        ->where("order_id= ?", $order_id);
                        $q->execute();
                        
                        
                        // update Materials - set isdelete   1
                        $q = Doctrine_Query::create()
                        ->update('PatientsOrdersMaterials')
                        ->set('isdelete','1')
                        ->where("order_id= ?", $order_id);
                        $q->execute();
                        
                        $order[$order_id]['stand_alone'] = "1"; // ISPC-2464
                        
                        $x =  PatientsOrdersDetailsTable::getInstance()->findOrCreateOneBy('id', $order_id, $order[$order_id]);
                        $edited = $x->toArray();
           
                        $status_of_order =  $edited['status'];
                        $order_creator = $edited['create_user'];
                        if($order[$order_id]['status'] == "canceled"){
                            $message_action_done = "order_was_canceled";
                        } else{
                            $message_action_done = "order_was_edited";
                        }
                    }
                        
                    $recipients = array();
                    if(isset($order_id) && !empty($order_id)){
                        $order_details = array();
                        $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
             
                        if(!empty($order_details)){
                        
                            if(!empty($order_details['PatientsOrdersRecipients'])){
                                foreach($order_details['PatientsOrdersRecipients'] as $kr=>$reciepient){
                                        $recipients[]  = $reciepient['recipient_id'];
                                }
                            }
                        }
                    }
                    
                    if(empty($order_creator)){
                        $order_creator = 0 ;
                    }
                    if( ! empty($message_action_done)  ) 
                    {
                        $send_msg = new Messages();
                        $msgs = $send_msg->order_action_messages($ipid,$message_action_done, $recipients, $order_creator);
                    }
                    
                    
                    
                    
                    $return['error'] = '0';
                    $return['success'] = '1';
                    
                    // save_log
                    $new_pause = new  PatientsOrdersDetailsLog();
                    $new_pause->order_id = $order_id;
                    $new_pause->details = $post_str;
                    $new_pause->save();
                    
                    
                    if($status_of_order == "active" || $status_of_order == "verified" ){
                        
                        $current_user  = User::getUsersNiceName(array($this->logininfo->userid));
                        $current_user_name = $current_user [$this->logininfo->userid]['nice_name'];

                        $course_title = "";
                        if($status_of_order == "active"){
                            $course_title = "Es wurde durch ".$current_user_name." eine Bestellung ausgelöst. ";
                        } else {
                            $course_title = "Es wurde durch ".$current_user_name." eine Bestellung bestätigt.";
                        }
                        
                        
                        $cust = array();
                        $cust['ipid'] = $ipid;
                        $cust['course_date'] = date("Y-m-d H:i");
                        $cust['course_type'] = "BS";
                        $cust['course_title'] = $course_title;
                        $cust['tabname'] = "order_management";
                        $cust['user_id'] = $this->logininfo->userid;
                        $cust['recordid'] = $order_id;
                        $cust['done_name'] = "order_management_".$status_of_order;
                        $pc = new PatientCourse();
                        $pc_id_save = $pc->set_new_record($cust);
                        
                    }
          
                    //generate pdf - in patient
                    if($status_of_order == "verified"){
                        $this->_generate_order_pdf($order_id, $patientid);
                    }
                }
                    
                if(!empty($return['error_msgs'])){
                    $return['errors'] = implode('<br/>',$return['error_msgs']); 
                }
                
                $this->_helper->getHelper('json')->sendJson($return);
                exit; //for readability
            }
            else
            {
                $return['error'] = '1';
                $return['error_msgs'][] =  "DATA MISSSING ";
                
                if(!empty($return['error_msgs'])){
                    $return['errors'] = implode('<br/>',$return['error_msgs']);
                }
                $this->_helper->getHelper('json')->sendJson($return);
                exit; //for readability
            }
        }
        
        
        
    }
    
    
    public function orderAction(){
        setlocale(LC_ALL, 'de_DE.UTF-8');
        $this->_helper->layout->setLayout('layout_ajax');
        
        if(!isset($_REQUEST['patient']) && !isset($_REQUEST['date'])){
            echo $this->view->translate('[please select patient and date]');
            return;
        }
       
        
        $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
        $ipid = Pms_CommonData::getIpid($decid);
        $order_date = $_REQUEST['date'];
        
        
        if(!empty($_REQUEST['order_id'])){
            $order_id  = $_REQUEST['order_id'];
        } 
        
        else if(!empty($_REQUEST['step_parent_id']))
        {
            $order_id  = $_REQUEST['step_parent_id'];
        } 
        else if(!empty($_REQUEST['parent_id']))
        {
            $order_id  = $_REQUEST['parent_id'];
        } 

        $this->view->days_of_week_arr = Pms_CommonData::getDaysOfWeek();
        $this->view->patient_enc_id = $_REQUEST['patient'];
        $this->view->ipid = $ipid;
        
        $clientid = $this->logininfo->clientid;
        /* ================ MEDICATION BLOCK VISIBILITY AND OPTIONS ======================= */
        $modules = new Modules();
        $medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","scheduled");
        $medication_blocks[] = "isintubated";
        
        /* IV BLOCK -  i.v. / s.c. */
        $iv_medication_block = $modules->checkModulePrivileges("53", $clientid);
        if(!$iv_medication_block){
            $medication_blocks = array_diff($medication_blocks,array("isivmed"));
        }
 
        
        /* NUTRITION  BLOCK - Ernahrung */
        $nutrition_block = $modules->checkModulePrivileges("103", $clientid);
        if(!$nutrition_block){
            $medication_blocks = array_diff($medication_blocks,array("isnutrition"));
        }
 
        // SCHEDULED  BLOCK - Intervall Medis
        $scheduled_block = $modules->checkModulePrivileges("143", $clientid);
        if(!$scheduled_block){
            $medication_blocks = array_diff($medication_blocks,array("scheduled"));
        }
        
        /* CRISIS  BLOCK  */
        $crisis_block = $modules->checkModulePrivileges("144", $clientid);
        if(!$crisis_block ){
            $medication_blocks = array_diff($medication_blocks,array("iscrisis"));
        }
        
        /* INTUBETED/INFUSION MEDICATION  BLOCK */
        $intubated_block = $modules->checkModulePrivileges("167", $clientid);
        if(!$intubated_block){
            $medication_blocks = array_diff($medication_blocks,array("isintubated"));
        }
        $this->view->medication_blocks = $medication_blocks; 
        
        
        // get patient details
        $patientmaster = new PatientMaster();
        $patientinfo = $patientmaster->getMasterData($decid,0);
        $this->view->patient_details = $patientinfo; 
        
        $order_data = array();
        
        $order_data['PatientsOrdersDetails']['patient_name'] = $patientinfo['nice_name_epid']; 
        $order_data['PatientsOrdersDetails']['patient'] = $_REQUEST['patient'];
        $order_data['PatientsOrdersDetails']['order_date'] = date("d.m.Y",strtotime($_REQUEST['date'])); 
        
        
        //Client recipients 
        $clrec = new ClientOrderRecipients();
        $client_recipients = $clrec->get_client_recipients($this->logininfo->clientid);
        
        
        /* $tobj = new User(); //obj used as table
        $tcol = $tobj->getTable()->createQuery('q');
        $tcol->select('*');
        $tcol->where('clientid = ?' , $this->logininfo->clientid);
        $tcol->andWhere('usertype != ?', 'SA');
        $tcol->andWhere('isactive="0"');
        $tcol->andWhere('isdelete="0"');
        if(!empty($client_recipients)){
            $tcol->andWhereIn('id',$client_recipients);
        }
        $tcol_arr = $tcol->fetchArray();

        
        $recipients[0] = "";
        foreach($tcol_arr as $k=>$udetails){
            $recipients[$udetails['id']]  = $udetails['last_name'].', '.$udetails['first_name'];
        } */
        
        
        $recipients_all = Pms_CommonData::get_nice_name_multiselect($this->logininfo->clientid);
        foreach($recipients_all as $group => $users){
            if($group == "Gruppe" ){
                unset($recipients_all['Gruppe']);
            }
        }
        
        foreach ($recipients_all as $group => $userss) {
            foreach ($userss as $uid => $uname) {
                if (! in_array($uid, $client_recipients)) {
                    unset($recipients_all[$group][$uid]);
                }
            }
        }
        $this->view->recipients = $recipients_all;
//         get_nice_name_multiselect
        
        //ISPC-2612 Ancuta 30.04.2020 
        // get client marterials
        $cats = array('drugs','auxiliaries','nursingauxiliaries','dressings');
        
        $client_materials = array();
        
        foreach($cats as $category_name){
            $follower[$category_name]  = ConnectionMasterTable::_check_client_connection_follower2category('ClientOrderMaterials',$this->logininfo->clientid,$category_name);
            
            $matr_q = Doctrine_Query::create()
            ->select('*')
            ->from('ClientOrderMaterials')
            ->where('clientid =?', $this->logininfo->clientid);
            if($follower[$category_name]){
                $matr_q->andWhere('connection_id is NOT null');
                $matr_q->andWhere('master_id is NOT null');
            }
            $matr_q->andWhere('isdelete = 0');
            $matr = $matr_q->fetchArray();
            
            
            foreach($matr as $mk=>$mv){
                $client_materials[$mv['category']][$mv['id']] = $mv['title'];
            }
        }
        
        /* 
        $matr = Doctrine_Query::create()
        ->select('*')
        ->from('ClientOrderMaterials')
        ->where('clientid =?', $this->logininfo->clientid)
        ->andWhere('isdelete = 0')
        ->fetchArray();
        $client_materials = array();
        foreach($matr as $mk=>$mv){
            $client_materials[$mv['category']][$mv['id']] = $mv['title'];
            /* 
            $client_materials[$mv['category']][$mv['id']] .= ' ( Einheit: '.$mv['unit'];
            $client_materials[$mv['category']][$mv['id']] .= ' | PZN: '.$mv['pzn'];
            $client_materials[$mv['category']][$mv['id']] .= ' | Stück/VE: '.$mv['pieces'].')';
        }
             */
        $this->view->client_materials = $client_materials; 

        if(isset($order_id) && !empty($order_id)){
            // get order data
            $order_details = array();
            $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
//             dd($order_details);
            if(!empty($order_details)){
                
                if(!empty($_REQUEST['parent_id']) && $_REQUEST['parent_id'] == $order_details['parent_id']){
                    
                    $patent_order_details = PatientsOrdersDetailsTable::find_patient_ordersByParent($ipid,$order_details['parent_id']);
                    $parent_deliveries = array();
                    $previous_parent_deliveries = array();
                    if(!empty($patent_order_details)){
                        foreach($patent_order_details as $or_id=>$ordata){
                            
                            if(!empty($ordata['PatientsOrdersDeliveryDates'])){
                                $current_parent_deliveries[]  = date('d.m.Y',strtotime($ordata['PatientsOrdersDeliveryDates']['delivery_date']));
                                if($ordata['PatientsOrdersDeliveryDates']['delivery_date'] < date("Y-m-d",strtotime($_REQUEST['date']))){
                                    
                                    $previous_parent_deliveries[]  = date('d.m.Y',strtotime($ordata['PatientsOrdersDeliveryDates']['delivery_date']));
                                }
                            }
                            
                        }
                        
                    }
                    rsort($previous_parent_deliveries);
                    rsort($current_parent_deliveries);
                }    
                
                if(!empty($_REQUEST['order_id'])){
                    $order_data['PatientsOrdersDetails']['id'] = $order_id;
                } else if(!empty($_REQUEST['parent_id'])){
                    $order_data['PatientsOrdersDetails']['id'] = 0;
                }
                $order_data['PatientsOrdersDetails']['parent_id'] = (!empty($order_details['parent_id'])) ? $order_details['parent_id'] : $order_id;
                $order_data['PatientsOrdersDetails']['step_parent_id'] = $order_details['step_parent_id'];
                $order_data['PatientsOrdersDetails']['order_date'] = (!empty($_REQUEST['date'])) ? date("d.m.Y",strtotime($_REQUEST['date'])) :  date("d.m.Y",strtotime($order_details['order_date']));
                $order_data['PatientsOrdersDetails']['order_interval'] = $order_details['order_interval'];
                $order_data['PatientsOrdersDetails']['order_interval_options'] = $order_details['order_interval_options'];
                $order_data['PatientsOrdersDetails']['order_comment'] = $order_details['order_comment'];
                
                if(!empty($order_details['PatientsOrdersRecipients'])){
                    foreach($order_details['PatientsOrdersRecipients'] as $k=>$ord_recipient){
                        $order_data['PatientsOrdersRecipients'][] = $ord_recipient['recipient_id'];
                    }
                }
                
                if(!empty($order_details['PatientsOrdersRecipients'])){
                    foreach($order_details['PatientsOrdersRecipients'] as $k=>$ord_recipient){
                        $order_data['PatientsOrdersRecipients'][] = $ord_recipient['recipient_id'];
                    }
                }
                
                //dd($order_details['PatientsOrdersMaterials']);
                $used_materials_in_order= array();
                if(!empty($order_details['PatientsOrdersMaterials'])){
                    foreach($order_details['PatientsOrdersMaterials'] as $k=>$ord_mat){
                        //ISPC-2639 pct.3 Lore 23.07.2020
/*                         for($i = 0; $i < $ord_mat['quantity']; $i++){
                            $order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][] = $ord_mat['material_id'];
                        } */
                        //$order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][] = $ord_mat['material_id'];
                        //ISPC-2639 pct.3 Lore 10.07.2020
						$order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][$ord_mat['material_id']]['id'] = $ord_mat['material_id'];
                        $order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][$ord_mat['material_id']]['quantity'] = $ord_mat['quantity'];
                        $used_materials_in_order[] = $ord_mat['material_id'];
                    }
                }
                //dd($order_data['PatientsOrdersMaterials']);
                if(!empty($_REQUEST['order_id'])){
                    if(!empty($order_details['PatientsOrdersDeliveryDates']['delivery_date'])){
                         $order_data['PatientsOrdersDeliveryDates']['delivery_date'] = date('d.m.Y',strtotime($order_details['PatientsOrdersDeliveryDates']['delivery_date']));
                    }
                }
                
                if(!empty($current_parent_deliveries)){
                    $order_data['PatientsOrdersDeliveryDates']['order_deliveries'] = $previous_parent_deliveries;
                }
 
            }
            
            if(!empty($used_materials_in_order)){
                $used_materials_in_order_str="";
                $used_materials_in_order_str =  implode(',',$used_materials_in_order);
            // show all including deleted
                $matr_all = Doctrine_Query::create()
                ->select('*')
                ->from('ClientOrderMaterials')
                ->where('clientid =?', $this->logininfo->clientid)
                ->andWhere('isdelete = 0 OR (isdelete = 1 AND id IN ('.$used_materials_in_order_str.') )')
                ->fetchArray();
                $all_client_materials = array();
                foreach($matr_all as $mk=>$mv){
                    $sfollower[$mv['category']]  = ConnectionMasterTable::_check_client_connection_follower2category('ClientOrderMaterials',$this->logininfo->clientid,$mv['category']);
                    if($sfollower[$mv['category']]){
                        
                        if($mv['isdelete'] == 1){
                            $mv['title'] = $mv['title'].'('.$this->translate('deleted').')';
                        }
                        if($mv['connection_id'] != null && $mv['master_id']!=null){
                            
                        $all_client_materials[$mv['category']][$mv['id']] = $mv['title'];
                        }
                    } else{
                        $all_client_materials[$mv['category']][$mv['id']] = $mv['title'];
                    }
                }
                $this->view->client_materials = $all_client_materials;
            }
            
        }
//         dd($order_data);
        $this->view->order_data = $order_data;
        
        
    }
       
 
    private function _pause_patient_orders(){

        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        if (! empty($_REQUEST)) {
        
            $decid = Pms_Uuid::decrypt($_REQUEST['patientid']);
            $ipid = Pms_CommonData::getIpid($decid);
        
            // get opened orders of patient 
            
            if(!empty($_REQUEST['start_date'])){
                $start_pause  = date("Y-m-d", strtotime($_REQUEST['start_date']));
            }
            $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
            // check if patients has opened orders
            
            $has_verified_in_future = array(); 
            $recipients = array();
            
            foreach($patients_with_Orders as $ord_id=>$ord_data){
                if($ord_data['status'] == "verified" && $ord_data['order_date'] >= $start_pause ){
                    $has_verified_in_future[] = $ord_id;
                }
                
                if($ord_data['status'] == "active" && $ord_data['order_date'] <= $start_pause && $ord_data['order_interval'] != 'once'){
                    
                    $active_in_future[] = $ord_id;
                    if($ord_data['create_user'] != $this->logininfo->userid ){
                        $recipients[] = $ord_data['create_user'];
                    }
                    
                    if(!empty($ord_data['PatientsOrdersRecipients'])){
                        foreach($ord_data['PatientsOrdersRecipients'] as $kr=>$reciepient){
                            $recipients[]  = $reciepient['recipient_id']; 
                        } 
                    }
                }
            }
            
            
            if( ! empty($has_verified_in_future)){
                $success = false;
                $errors = $this->translate('Please check dates, there were orders verified/delivered'); 
            } else {
                
                // check if patient already has pause period ?????
                $new_pause = new  PatientsOrdersPeriods();
                $new_pause->ipid = $ipid;
                $new_pause->start_date = $start_pause;
                $new_pause->action_status = "paused";
                $new_pause->save();
  
                $message_action_done = "order_was_paused";
                
                if(empty($order_creator)){
                    $order_creator = 0 ;
                }
                
                if( ! empty($message_action_done))
                {
                    $send_msg = new Messages();
                    $msgs = $send_msg->order_action_messages($ipid,$message_action_done, $recipients, $order_creator);
                }
                
                
                $errors = "SUCCESS";
                $success = true;
            }

        
            $result = array(
                'success' => $success,
                'errors' => $errors
            );
        
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
        
        }
        
    }
    
    
    private function _stop_patient_orders(){

        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        if (! empty($_REQUEST)) {
        
            $decid = Pms_Uuid::decrypt($_REQUEST['patientid']);
            $ipid = Pms_CommonData::getIpid($decid);
        
            // get opened orders of patient 
            
            if(!empty($_REQUEST['start_date'])){
                $start_pause  = date("Y-m-d", strtotime($_REQUEST['start_date']));
            }
            $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders(array($ipid));
            // check if patients has opened orders
            $has_verified_in_future = array(); 
            $all_active = array(); 
            $recipients = array(); 
            
            foreach($patients_with_Orders as $ord_id=>$ord_data){
                if($ord_data['status'] == "verified" and $ord_data['order_date'] >= $start_pause ){
                    $has_verified_in_future[] = $ord_id;
                }
                
                if($ord_data['action_status'] == "opened" || $ord_data['action_status'] == "paused"){
                    $all_active[] = $ord_id;
                }
                
                

               if($ord_data['status'] == "active" && $ord_data['order_date'] <= $start_pause && $ord_data['order_interval'] != 'once'){
                    
                    $active_in_future[] = $ord_id;
                    if($ord_data['create_user'] != $this->logininfo->userid ){
                        $recipients[] = $ord_data['create_user'];
                    }
                    
                    if(!empty($ord_data['PatientsOrdersRecipients'])){
                        foreach($ord_data['PatientsOrdersRecipients'] as $kr=>$reciepient){
                            $recipients[]  = $reciepient['recipient_id']; 
                        } 
                    }
                }
            }
             
            if( ! empty($has_verified_in_future)){
                $success = false;
                $errors = $this->translate("Please check dates, there were orders verified/delivered"); 
                
            } else {
                
                
                // check if patient already has pause period ?????
                $new_pause = new  PatientsOrdersPeriods();
                $new_pause->ipid = $ipid;
                $new_pause->start_date = $start_pause;
                $new_pause->action_status = "stopped";
                $new_pause->save();
                
                
                // get all opened values and set as stopped
                if(!empty($all_active))
                {
                    $q = Doctrine_Query::create()
                    ->update('PatientsOrdersDetails')
                    ->set('action_status', '?', 'stopped')
                    ->where("ipid= ?", $ipid);
                    $q->execute();
                    // IS THIS NEEDED????
                    
                }
                
                
                $message_action_done = "order_was_stopped";
                
                if($active_in_future){
                
                    if(empty($order_creator)){
                        $order_creator = 0 ;
                    }
                    if( ! empty($message_action_done))
                    {
                        $send_msg = new Messages();
                        $msgs = $send_msg->order_action_messages($ipid,$message_action_done, $recipients, $order_creator);
                    }
                }
                
                
                $errors = "SUCCESS";
                $success = true;
            }

        
            $result = array(
                'success' => $success,
                'errors' => $errors
            );
        
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
        
        }
        
    }

    
    
    private function _last_patient_order(){

        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        if (! empty($_REQUEST)) {
        
            $decid = Pms_Uuid::decrypt($_REQUEST['patientid']);
            $ipid = Pms_CommonData::getIpid($decid);
            
            // this is needed to know what period to stop
            if( ! empty($_REQUEST['patient_status'])){
                    
            }
            $last_order_array = PatientsOrdersDetailsTable::find_patient_lastOrder(array($ipid));

            $last_order = array();
            if(!empty($last_order_array) && count($last_order_array)=="1"){
                foreach($last_order_array as $order_id => $od ){
                    
                    $last_order['order_id'] = "";
                    $last_order['parent_id'] = $od['parent_id'];
                    $last_order['step_parent_id'] = $od['step_parent_id'];
                    $last_order['patient_id'] = $_REQUEST['patientid'];
                    $last_order['order_date'] = date('d.m.Y');
                }
            }
            
            $errors = "SUCCESS";
            $success = true;
            
            $result = array(
                'success' => $success,
                'order_id'=>  $last_order['order_id'],
                'parent_id'=>  $last_order['parent_id'],
                'step_parent_id'=>  $last_order['step_parent_id'],
                'patient_id'=>  $last_order['patient_id'],
                'order_date'=>  $last_order['order_date'],
                'errors' => $errors
            );
            
            $this->_helper->getHelper('json')->sendJson($result);
            exit; //for readability
        
        }
        
    }

    
    private function _sendMessages($ipid, $sendTo, $message_action, $order_creator){
    
        //         dd(func_get_args());
        //            echo $ipid;
        //            echo $message_action;
    
    
    }
    
    
    
    
    public function orderlogAction(){
        
        if (! empty($_REQUEST)) {
            
            if( empty($_REQUEST['order_id'])){
                return ;
            }
            
            $order_id =  $_REQUEST['order_id'];
            
            $order_details = array();
            $order_details = PatientsOrdersDetailsLogTable::find_log_by_order($order_id);
            
            $log_items = array();
            foreach($order_details as $k=>$log){
                $log_items[$k] = $log;
                parse_str($log['details'],$log_items[$k]['details']);
            }
            
        }
        
    }
    
    
    public function _generate_order_pdf($order_id, $patientid, $fromto = null, $pdforder_data){ //ISPC-2369 Carmen 16.07.2020
    	//ISPC-2639 Carmen 16.07.2020
    	if(!$fromto)
    	{
	        if(empty($order_id) || empty($patientid)){
	            return;
	        }
    	}
    	//--
        $decid = Pms_Uuid::decrypt($patientid);
        $ipid = Pms_CommonData::getIpid($decid);
        
     
        if(empty($ipid) || empty($decid)){
            return;
        }
        
        //ISPC-2639 Carmen 16.07.2020
        if(!$fromto)
        {
        $order_data = array();
        
        // get order data
        $order_details = array();
        $order_details = PatientsOrdersDetailsTable::find_patient_order($ipid,$order_id);
        
        if(!empty($order_details)){
            
            $patent_order_details = PatientsOrdersDetailsTable::find_patient_ordersByParent($ipid,$order_details['parent_id']);
            
            $parent_deliveries = array();
            $previous_parent_deliveries = array();
            if(!empty($patent_order_details)){
                foreach($patent_order_details as $or_id=>$ordata){
                    
                    if(!empty($ordata['PatientsOrdersDeliveryDates'])){
                        $current_parent_deliveries[]  = date('d.m.Y',strtotime($ordata['PatientsOrdersDeliveryDates']['delivery_date']));
                        if($ordata['PatientsOrdersDeliveryDates']['delivery_date'] < date("Y-m-d",strtotime($order_details['order_date']))){
                            
                            $previous_parent_deliveries[]  = date('d.m.Y',strtotime($ordata['PatientsOrdersDeliveryDates']['delivery_date']));
                        }
                    }
                    
                }
                
            }
            rsort($previous_parent_deliveries);
            rsort($current_parent_deliveries);
            
            $order_data['PatientsOrdersDetails']['id'] = $order_id;
            $order_data['PatientsOrdersDetails']['parent_id'] = (!empty($order_details['parent_id'])) ? $order_details['parent_id'] : $order_id;
            $order_data['PatientsOrdersDetails']['step_parent_id'] = $order_details['step_parent_id'];
            $order_data['PatientsOrdersDetails']['order_date'] = date("d.m.Y",strtotime($order_details['order_date']));
            $order_data['PatientsOrdersDetails']['order_interval'] = $order_details['order_interval'];
            $order_data['PatientsOrdersDetails']['order_interval_options'] = $order_details['order_interval_options'];
            $order_data['PatientsOrdersDetails']['order_comment'] = $order_details['order_comment'];
            
            
            if(!empty($order_details['PatientsOrdersMedication'])){
                foreach($order_details['PatientsOrdersMedication'] as $k=>$med){
                    $order_data['PatientsOrdersMedication'][$med['patient_drugplan_id']] = $med;
                }
            
            }
            if(!empty($order_details['PatientsOrdersRecipients'])){
                foreach($order_details['PatientsOrdersRecipients'] as $k=>$ord_recipient){
                    $order_data['PatientsOrdersRecipients'][] = $ord_recipient['recipient_id'];
                }
            }
            
            if(!empty($order_details['PatientsOrdersRecipients'])){
                foreach($order_details['PatientsOrdersRecipients'] as $k=>$ord_recipient){
                    $order_data['PatientsOrdersRecipients'][] = $ord_recipient['recipient_id'];
                }
            }

            
            if(!empty($order_details['PatientsOrdersMaterials'])){
                foreach($order_details['PatientsOrdersMaterials'] as $k=>$ord_mat){
                    //$order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][] = $ord_mat['material_id'];
                    //ISPC-2639 pct.3 Lore 24.07.2020
                    $order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][$ord_mat['material_id']]['id'] = $ord_mat['material_id'];
                    $order_data['PatientsOrdersMaterials'][$ord_mat['material_category']][$ord_mat['material_id']]['quantity'] = $ord_mat['quantity'];
                    
                }
            }
            
            if(!empty($order_details['PatientsOrdersDeliveryDates']['delivery_date'])){
                 $order_data['PatientsOrdersDeliveryDates']['delivery_date'] = date('d.m.Y',strtotime($order_details['PatientsOrdersDeliveryDates']['delivery_date']));
            }
            
            if(!empty($current_parent_deliveries)){
                $order_data['PatientsOrdersDeliveryDates']['order_deliveries'] = $previous_parent_deliveries;
            }

        }
      }
      else 
      {
      	$order_data = $pdforder_data['data'];
      }
      //--
     
        // get patient details
        $patientmaster = new PatientMaster();
        $patientinfo = $patientmaster->getMasterData($decid,0);
        $order_data['PatientsOrdersDetails']['patient_name'] = $patientinfo['nice_name_epid'];
//         $order_data['PatientsOrdersDetails']['patient'] = $_REQUEST['patient'];
//         $order_data['PatientsOrdersDetails']['order_date'] = date("d.m.Y",strtotime($_REQUEST['date']));
        
        
        //Client recipients
        $clrec = new ClientOrderRecipients();
        $client_recipients = $clrec->get_client_recipients($this->logininfo->clientid);
        $recipients_all = Pms_CommonData::get_nice_name_multiselect($this->logininfo->clientid);
        
        $client_recipients_array = array();
        foreach ($recipients_all as $group => $users) {
            foreach ($users as $k => $uidnp) {
                
                $client_recipients_array[$k] = $uidnp;
            }
        }
        
        $order_data['recipients'] = $client_recipients_array;
        
        
        // get client marterials
        $matr = Doctrine_Query::create()
        ->select('*')
        ->from('ClientOrderMaterials')
        ->where('clientid =?', $this->logininfo->clientid)
        ->fetchArray();
        
        $client_materials = array();
        foreach($matr as $mk=>$mv){
            $client_materials[$mv['category']][$mv['id']] = $mv['title'];
        
        }
        $order_data['client_materials'] = $client_materials;
        
        $order_data['days_of_week_arr'] = Pms_CommonData::getDaysOfWeek();
        $order_data['patient_enc_id'] = $_REQUEST['patient'];
        $order_data['ipid'] = $ipid;
//         dd($order_data);
        $current_user  = User::getUsersNiceName(array($this->logininfo->userid));
        $current_user_name = $current_user [$this->logininfo->userid]['nice_name'];
        $order_data['current_user_name'] = $current_user_name; 
        
        $post = $order_data;
        $post['order_data'] = $order_data;
        $pdfname = "order_pdf"; // used also for tabname
        $filename = "order_pdf.phtml";
        
        $pdf_title = $this->view->translate ( 'order_pdf_title' );
        if(isset($post['order_data']['PatientsOrdersDeliveryDates']['delivery_date']) && strlen($post['order_data']['PatientsOrdersDeliveryDates']['delivery_date']) > 0  ){
            $pdf_title .= " (" . date("d.m.Y",strtotime($post['order_data']['PatientsOrdersDeliveryDates']['delivery_date'])) . ")";
        }
        
        $record_id = $this->generatePdfNew_2017($post, $pdfname, $filename,$extra = array (
            "pdfname" => $pdf_title,
        	"fromto" => $fromto	//ISPC-2369 Carmen 16.07.2020
        )
        ); 
	    
        return true;

    }

    private function generatePdfNew_2017($post, $pdfname, $filename, $extra_settings =  array())
    {
        $pdfname_translated = $this->view->translate($pdfname);
        
        $decid = Pms_Uuid::decrypt($post['patient_end_id']);
        $decid = empty($decid) ? 0 : $decid;
    
        
        if (is_null($post['ipid'])) {
            $ipid = Pms_CommonData::getIpid($decid);
            $ipid = empty($ipid) ? 0 : $ipid;
        } else {
            $ipid = $post['ipid'] ;
        }
       
        $patientmaster = new PatientMaster();
        $patientMasterData = $patientmaster->getMasterData($decid, 0);
        $this->_patientMasterData = $patientmaster->get_patientMasterData();
        	
        	
        $excluded_css_cleanup_pdfs = array();// remove style attribute from elemenets
        	
        $excluded_keys = array();// htmlspecialchars view values
    
        //pdf setings for each form
        switch($pdfname) {
            
            case "order_pdf":
//                 $final_action = 'return';
                	
                $orientation = 'P';
                $bottom_margin = '20';
                $format = "A4";
                	
                $excluded_keys [] = "key_to_exclude_from_htmlspecialchars";
                $excluded_css_cleanup_pdfs[] = $pdfname;
                $pdfname_translated = $extra_settings['pdfname'];
                
                break;
    
            default:
                	
                $final_action = 'return';
                	
                $orientation = 'P';
                $bottom_margin = '20';
                $format = "A4";
                	
                $excluded_keys [] = "key_to_exclude_from_htmlspecialchars";
                	
                $excluded_css_cleanup_pdfs[] = $pdfname; // remove style attribute from elemenets
                	
                break;
    
        }
    
    
        	
        $post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
    
        $htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
        	
        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
        	
        
        $html =  Pms_CommonData::html_prepare_dompdf($html,"14px",'auto',false);
        	
        if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
        {
            $html = preg_replace('/style=\"(.*)\"/i', '', $html);
        }
        	
        //create the pdf
        $pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
        $pdf->SetMargins(10, 5, 10); //reset margins
        $pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
        $pdf->setImageScale(1.6);
        $pdf->format = $format;
        $pdf->HeaderText = false;
        $pdf->setPrintFooter(false);
        	
        $pdf->SetAutoPageBreak(true , 10);
        $pdf->SetMargins(10, 10, 10, true); //reset margins $left, $top, $right=-1, $keepmargins=false
        $pdf->setCellHeightRatio(1);
        $pdf->SetFont('helvetica', '', 10);
//         echo $html; exit; 
        $pdf->setHTML($html);
        
        //ISPC-2639 Carmen 17.07.2020
        if(!$extra_settings['fromto'])
        {
	        //save pdf 2 ftp and add entry to patient Dokument tab
	        $ftp_filename = $pdf->toFTP($pdfname_translated);
	        
	        if($ftp_filename !== false)
	        {
	            $cust = new PatientFileUpload ();
	          
	            $recordid = $cust->set_new_record(
	                array(
	                    "title" => $pdfname_translated,
	                    "ipid" => $ipid,
	                    "file_name" => $ftp_filename,
	                    "file_type" => 'PDF',
	                    //"recordid" => $record_id,
	                    "tabname" => $pdfname,
	                    "system_generated" => "0",
	                ));
	        }
        }
        else 
        {
        	
        		$tmpstmp = uniqid(time());
// 				mkdir("uploads/" . $tmpstmp);
				mkdir(PDF_PATH. "/" . $tmpstmp);
// 				$pdf->Output('uploads/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$pdf->Output(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', 'F');
				$_SESSION['filename'] = PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf';
        }
        //--
        
        ob_end_clean();
        ob_start();
        	
        switch($final_action){
            case"download":{
                $pdf->toBrowser($pdfname . '.pdf', "D");//D: Download PDF as file
                
            }break;
    
            case"view":{
                $pdf->toBrowser($pdfname . '.pdf', "I"); //I: Send PDF to the standard output
            }break; 
            
           /*  //ISPC-2639 Carmen 16.07.2020
            case"view":{
            	$pdf->toBrowser($pdfname . '.pdf', "S");
            }break; */
            //--
            
            default:
                // F, FI, FD: Save PDF to a local file. +inline, +download
                // E: Return PDF as base64 mime multi-part email attachment (RFC 2045)
                // S: Returns PDF as a string
                break;
        }
        
//         	return true;
// //         return $recordid;
    }
    

    /**
     * @author Ancuta
     * // ISPC-2464  31.10.2019 
     * @param unknown $ipid
     * @param unknown $order_date
     * @param string $show_all
     * @return void|mixed|boolean
     */
  public function get_previous_and_next_order($ipid, $order_date, $parent_id = false,$show_all = false)
  {
      
      if(empty($ipid)){
          return;
      }
      
      if(empty($order_date)){
          return;
      }
      
      
      if(empty($parent_id) || strlen($parent_id) == 0 ){
          $parent_id  = false;
      }

      
     // create_dates from order date 
      $selected_period['start'] =date("Y-m-d", strtotime("-30 days", strtotime($order_date)));;;
      $selected_period['end'] = date("Y-m-d", strtotime("+30 days", strtotime($order_date)));;
      
      $patientmaster = new PatientMaster();
      $selected_period['days']= $patientmaster->getDaysInBetween( $selected_period['start'], $selected_period['end']);
      
      
      
      if(!is_array($ipid)){
          $allowed_ipids = array($ipid);
      } else{
          $allowed_ipids = $ipid;
      }
      
//         $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders($allowed_ipids);
        $patients_with_Orders = PatientsOrdersDetailsTable::find_patients_withOrders($allowed_ipids,  false,  false,  false,  '',$parent_id);

        $overall_order_details = array();
        $order_2date = array();
        $ipidsWithOrders = array();
        // dd($patients_with_Orders);
        $current_user = $this->logininfo->userid;
        $overal_orders_statuses = array();
        $order_2_ipid_2_id_2_date = array();
        // dd($patients_with_Orders);
        foreach ($patients_with_Orders as $k => $ord_details_all) {
            
            $order_2_ipid_2_id_2_date[$ord_details_all['ipid']][$ord_details_all['id']][$ord_details_all['order_date']] = $ord_details_all;
            
            
            $ipidsWithOrders[] = $ord_details_all['ipid'];
            $overall_order_details[$ord_details_all['ipid']][$ord_details_all['id']] = $ord_details_all;
            
            // get all active or paused
            if ($ord_details_all['action_status'] == "opened" || $ord_details_all['action_status'] == "paused") {
                if ($ord_details_all['action_status'] == "opened") {
                    
                    $overal_orders_statuses['opened'][] = $ord_details_all['id'];
                    $overal_orders_patients_statuses['opened'][] = $ord_details_all['ipid'];
                } else {
                    
                    $overal_orders_statuses['paused'][] = $ord_details_all['id'];
                    $overal_orders_patients_statuses['paused'][] = $ord_details_all['ipid'];
                }
            } else {
                $overal_orders_statuses['closed'][] = $ord_details_all['id'];
                $overal_orders_patients_statuses['closed'][] = $ord_details_all['ipid'];
            }
            
            // get all OWN orders
            foreach ($ord_details_all['PatientsOrdersRecipients'] as $k => $reciepient) {
                if ($current_user == $reciepient['recipient_id']) {
                    $own[] = $reciepient['order_id'];
                    $overal_orders_statuses['own'][] = $ord_details_all['ipid'];
                    $overal_orders_patients_statuses['own'][] = $ord_details_all['ipid'];
                }
            }
        }
        
        
        $ipidsWithOrders = array_unique($ipidsWithOrders);
        
        $order_child_dates = array();
        $order_2date = array();
        $order_dates_of_parent = array();
        $order_dates_of_patient = array();
        foreach ($patients_with_Orders as $k => $ord_details) {
            
            $order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']][] = $ord_details['order_date'];
            ;
            $order_dates_of_patient[$ord_details['ipid']][$ord_details['id']][] = $ord_details['order_date'];
            ;
            
            // $order_2date[$ord_details['id']] = $ord_details['order_date'];
            if (! empty($ord_details['parent_id'])) {
                // $order_child_dates[$ord_details['ipid']][$ord_details['parent_id']][$ord_details['id']] = $ord_details['order_date'];
                $order_child_dates[$ord_details['ipid']][$ord_details['parent_id']][] = $ord_details['order_date'];
                $date_2order[$ord_details['ipid']][$ord_details['parent_id']][$ord_details['order_date']] = $ord_details['id'];
            } else {
                // $order_child_dates[$ord_details['ipid']][$ord_details['id']][$ord_details['id']] = $ord_details['order_date'];
                $order_child_dates[$ord_details['ipid']][$ord_details['id']][] = $ord_details['order_date'];
                $date_2order[$ord_details['ipid']][$ord_details['id']][$ord_details['order_date']] = $ord_details['id'];
            }
        }
        
        
        // create child intervals
        $full_order_intervals = array();
        $parent_order_intervals = array();
        foreach ($order_child_dates as $oipid => $ord_data) {
            foreach ($ord_data as $parent_order_id => $child_dates) {
                
                if (! is_array($parent_order_intervals[$oipid][$parent_order_id])) {
                    $parent_order_intervals[$oipid][$parent_order_id] = array();
                }
                
                if (count($child_dates) > 1) {
                    foreach ($child_dates as $order_child => $child_date) {
                        $child_order_id = 0;
                        $child_order_id = $date_2order[$oipid][$parent_order_id][$child_date];
                        
                        if (isset($child_dates[$order_child + 1]) && $child_date < $child_dates[$order_child + 1]) {
                            $next_child_date = date("Y-m-d", strtotime("-1 day", strtotime($child_dates[$order_child + 1])));
                            if ($child_date > $next_child_date) {
                                $next_child_date = $child_date;
                            }
                            $order_intervals[$oipid][$parent_order_id][$child_order_id] = $patientmaster->getDaysInBetween($child_date, $next_child_date);
                            $parent_order_intervals[$oipid][$parent_order_id] = array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_date, $next_child_date));
                            // end one day before next child date
                            $full_order_intervals[$oipid][$child_order_id] = $patientmaster->getDaysInBetween($child_date, $next_child_date);
                        } else {
                            
                            $extra_months = date("Y-m-d", strtotime("+ 3 months", strtotime($child_date)));
                            $order_intervals[$oipid][$parent_order_id][$child_order_id] = $patientmaster->getDaysInBetween($child_date, $extra_months);
                            $parent_order_intervals[$oipid][$parent_order_id] = array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_date, $extra_months));
                            $full_order_intervals[$oipid][$child_order_id] = $patientmaster->getDaysInBetween($child_date, $extra_months);
                        }
                    }
                } else {
                    $child_order_id = 0;
                    $child_order_id = $date_2order[$oipid][$parent_order_id][$child_dates[0]];
                    if ($overall_order_details[$oipid][$parent_order_id]['order_interval'] != "once") {
                        $extra_months = date("Y-m-d", strtotime("+ 3 months", strtotime($child_dates[0])));
                        $order_intervals[$oipid][$parent_order_id][$child_order_id] = $patientmaster->getDaysInBetween($child_dates[0], $extra_months);
                        $parent_order_intervals[$oipid][$parent_order_id] = array_merge($parent_order_intervals[$oipid][$parent_order_id], $patientmaster->getDaysInBetween($child_dates[0], $extra_months));
                        $full_order_intervals[$oipid][$child_order_id] = $patientmaster->getDaysInBetween($child_dates[0], $extra_months);
                    } else {
                        $order_intervals[$oipid][$parent_order_id][$child_order_id] = $child_dates[0];
                        $parent_order_intervals[$oipid][$parent_order_id] = array_merge($parent_order_intervals[$oipid][$parent_order_id], array(
                            $child_dates[0]
                        ));
                        $full_order_intervals[$oipid][$child_order_id][] = $child_dates[0];
                    }
                }
            }
        }
        
        
        // do not allow orders to overlay
        $order_delivery_dates = array();
        foreach ($patients_with_Orders as $k => $ord_details) {
            
            $xdays_counter = 0;
            $order_interval_options = array_map('trim', explode(",", $ord_details['order_interval_options'])); // only used for days of week
            
            $current_parent[$ord_details['id']] = 0;
            $current_parent[$ord_details['id']] = ! empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
            $first_ever_parent_date[$ord_details['id']] = $parent_order_intervals[$ord_details['ipid']][$current_parent[$ord_details['id']]][0];
            
            // First order of parent
            
            // get all child orders
            
            // get next order
            
            // get al delivery dates
            if (! empty($ord_details['PatientsOrdersDeliveryDates'])) {
                $order_delivery_dates[$ord_details['id']][] = $ord_details['PatientsOrdersDeliveryDates']['delivery_date'];
            }
            // do we need it?
 
            
            //check_pateint oreders
            foreach ($selected_period['days'] as $period_day) {
                $period_day_strtotime = strtotime($period_day);
                $order_id2date[$ord_details['id']][] = $period_day;
                
                foreach ($order_intervals[$ord_details['ipid']][$ord_details['id']] as $child => $child_interval_Dates) {
                    if (in_array($period_day, $child_interval_Dates)) {
                        $day2child[$ord_details['id']][$period_day] = $child_interval_Dates;
                    }
                }
                if(!empty($order_2_ipid_2_id_2_date[$ipid][$ord_details['id']][$period_day])){
                    //$ord_details['order_is_saved']  = $order_2_ipid_2_id_2_date[$ipid][$ord_details['id']][$period_day];
                    $ord_details['order_is_saved']  = 1;
                } else{
                    $ord_details['order_is_saved']  = 0;
                }
                
                
                switch ($ord_details['order_interval']) {
                    case "once":
                        {
                            
                            if ($period_day == $ord_details['order_date']) {
                                $orders_details[$ord_details['ipid']][$ord_details['order_date']] = $ord_details;
                                $orders_details[$ord_details['ipid']][$ord_details['order_date']]['order_id'] = $ord_details['id'];
                                $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'once';
                                $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = '1';
                                
                                if ($period_day == $ord_details['order_date'] && $period_day == $first_ever_parent_date[$ord_details['id']]) {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
                                } else {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
                                }
                                
                                if (in_array($period_day, $order_delivery_dates[$ord_details['id']])) {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                } elseif ($ord_details['status'] == "canceled") {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                } else {
                                    if (in_array($period_day, $pat_periods['paused'][$ord_details['ipid']]['overall_days']) && ! in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                    } elseif (in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                    } else {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                    }
                                }
                            }
                        }
                        break;
                    
                    case "every_x_days":
                        {
                            
                            if (strtotime($ord_details['order_date']) <= $period_day_strtotime && (int) $ord_details['order_interval_options'] > 0 && in_array($period_day, $full_order_intervals[$ord_details['ipid']][$ord_details['id']])) {
                                if ($xdays_counter % $ord_details['order_interval_options'] == 0) {
                                    
                                    if($ord_details['order_is_saved'] == 0){ // order does not exists in  DB
                                        $ord_details['stand_alone'] = 0 ;
                                    }
                                    
                                    $orders_details[$ord_details['ipid']][$period_day] = $ord_details;
                                    $orders_details[$ord_details['ipid']][$period_day]['current_order_date'] = $period_day;
                                    $orders_details[$ord_details['ipid']][$period_day]['current_order_id'] = $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'every_x_days';
                                    $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = $ord_details['order_interval_options'];
                                    
                                    if ((! empty($ord_details['parent_id']) && in_array($period_day, $order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']])) || (in_array($period_day, $order_dates_of_patient[$ord_details['ipid']][$ord_details['id']]))) {
                                        $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id']; // order can be edited
                                    } else {
                                        $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0; // -> new step is added
                                    }
                                    
                                    if ($period_day == $ord_details['order_date'] && $period_day == $first_ever_parent_date[$ord_details['id']]) {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
                                        // $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = 0;
                                    } else {
                                        $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
                                        // $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;
                                        $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = ! empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                        $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = ! empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (! empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                    }
                                    
                                    // if( empty($ord_details['parent_id'])){
                                    // $orders_details[$ord_details['ipid']][$period_day]['child_id'] = $child;
                                    // }
                                    
                                    if (in_array($period_day, $order_delivery_dates[$ord_details['id']])) {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                    } elseif ($ord_details['status'] == "canceled") {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                    } else {
                                        if (in_array($period_day, $pat_periods['paused'][$ord_details['ipid']]['overall_days']) && ! in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                        } elseif (in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                        } else {
                                            if ($ord_details['status'] == "active" && $period_day == $ord_details['order_date']) {
                                                
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                            } else {
                                                
                                                $orders_details[$ord_details['ipid']][$period_day]['status'] = 'planned';
                                            }
                                        }
                                    }
                                }
                                $xdays_counter ++;
                            }
                        }
                        break;
                    
                    case "selected_days_of_the_week":
                        {
                            
                            if ($query_date_strtotime <= $period_day_strtotime && strtotime($ord_details['order_date']) <= $period_day_strtotime && (in_array(date("N", $period_day_strtotime), $order_interval_options)) && in_array($period_day, $full_order_intervals[$ord_details['ipid']][$ord_details['id']])) {
                                
                                if($ord_details['order_is_saved'] == 0){ // order does not exists in  DB
                                    $ord_details['stand_alone'] = 0 ;
                                }
                                
                                $orders_details[$ord_details['ipid']][$period_day] = $ord_details;
                                $orders_details[$ord_details['ipid']][$period_day]['current_order_id'] = $ord_details['id'];
                                $orders_details[$ord_details['ipid']][$period_day]['current_order_date'] = $period_day;
                                $orders_details[$ord_details['ipid']][$period_day]['interval_type'] = 'selected_days_of_the_week';
                                $orders_details[$ord_details['ipid']][$period_day]['interval_options'] = $ord_details['order_interval_options'];
                                
                                if ((! empty($ord_details['parent_id']) && in_array($period_day, $order_dates_of_parent[$ord_details['ipid']][$ord_details['parent_id']])) || (in_array($period_day, $order_dates_of_patient[$ord_details['ipid']][$ord_details['id']]))) {
                                    $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id']; // order can be edited
                                } else {
                                    $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0; // -> new step is added
                                }
                                
                                if ($period_day == $ord_details['order_date'] && $period_day == $first_ever_parent_date[$ord_details['id']]) {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "yes";
                                    // $orders_details[$ord_details['ipid']][$period_day]['order_id'] = $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = 0;
                                } else {
                                    $orders_details[$ord_details['ipid']][$period_day]['is_parent'] = "no";
                                    // $orders_details[$ord_details['ipid']][$period_day]['order_id'] = 0;
                                    $orders_details[$ord_details['ipid']][$period_day]['parent_id'] = ! empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id'];
                                    $orders_details[$ord_details['ipid']][$period_day]['step_parent_id'] = ! empty($ord_details['step_parent_id']) ? $ord_details['step_parent_id'] : (! empty($ord_details['parent_id']) ? $ord_details['parent_id'] : $ord_details['id']);
                                }
                                
                                if (in_array($period_day, $order_delivery_dates[$ord_details['id']])) {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = 'verified';
                                } elseif ($ord_details['status'] == "canceled") {
                                    $orders_details[$ord_details['ipid']][$period_day]['status'] = $ord_details['status'];
                                } else {
                                    if (in_array($period_day, $pat_periods['paused'][$ord_details['ipid']]['overall_days']) && ! in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'paused';
                                    } elseif (in_array($period_day, $pat_periods['stopped'][$ord_details['ipid']]['overall_days'])) {
                                        $orders_details[$ord_details['ipid']][$period_day]['status'] = 'stopped';
                                    } else {
                                        // $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                        if ($ord_details['status'] == "active" && $period_day == $ord_details['order_date']) {
                                            
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'active';
                                        } else {
                                            
                                            $orders_details[$ord_details['ipid']][$period_day]['status'] = 'planned';
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }
        
        
        foreach($orders_details as $ipid=>$odetails){
            foreach($odetails as $o_Date=>$o_data_arr){
                if($o_Date > $order_date){
                    $following_orders[$ipid][] = $o_data_arr;
                }
                if($o_Date < $order_date && $o_data_arr['stand_alone'] == 0){
                    $pevious_orders[$ipid][] = $o_data_arr;
                }
            }
        }
        
        $patient_orders = array();
        if($show_all){
            foreach($allowed_ipids as $p_ipid){
                $patient_orders[$p_ipid]['next'] = $following_orders[$ipid]; 
                $patient_orders[$p_ipid]['previous'] = $pevious_orders[$ipid];
            } 
        } else{
            foreach($allowed_ipids as $p_ipid){
                $patient_orders[$p_ipid]['next'] = $following_orders[$ipid][0]; 
                $patient_orders[$p_ipid]['previous'] = end($pevious_orders[$ipid]); 
            }
        }
        
        
        if(!empty($patient_orders)){
            return $patient_orders;
        } else{
            return false;
        }
        
    }
    
    /**
     * @author Ancuta
     * ISPC-2464
     * @throws Zend_Exception
     */
    public function  _get_following_saved_orders(){
        if (! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        $return = array();
        $return['following_saved'] = 0;
        if ($this->getRequest()->isPost()) {
            $post_data = array();
            
            $order_id = 0;
            $post_str = $_POST['order_data'];
            parse_str($_POST['order_data'], $post_data);
            
            if (! empty($post_data['data']['PatientsOrdersDetails']['patient'])) {
                
                $patientid = $post_data['data']['PatientsOrdersDetails']['patient'];
                $decid = Pms_Uuid::decrypt($post_data['data']['PatientsOrdersDetails']['patient']);
                $ipid = Pms_CommonData::getIpid($decid);
                
                $order_Date = date("Y-m-d", strtotime($post_data['data']['PatientsOrdersDetails']['order_date']));
                $patent_order_details = PatientsOrdersDetailsTable::find_patient_following_orders($ipid, false, $order_Date);
                
                usort($patent_order_details, array( new Pms_Sorter('order_date'), "_date_compare"));

                if (! empty($patent_order_details)) {
                    
                    $return['following_saved'] = 1;
//                     $return['following_saved_orders']= $this->view->translate('<br/>Orders that will be changed if "change all" is selected - all planned and including saved ones: ');
                    $return['following_saved_orders']= $this->view->translate('This are the SAVED orders that will also be changed if \"change all\" is selected:');
                    $following_saved_orders = array();
                    foreach($patent_order_details as $oid=>$odata){
                        
                        if( ($odata['stand_alone'] == "1" || $odata['status'] != null) && $odata['order_date'] > $order_Date ){
                            $following_saved_orders[]= date('d.m.Y',strtotime($odata['order_date']));
                        }
                    }
                    
                    if( ! empty($following_saved_orders)){
                        $following_saved_orders = array_unique($following_saved_orders );
                        $return['following_saved_orders'] .= implode("; ",$following_saved_orders);  
                    }
                    
                } else {
                    $return['following_saved'] = 0;
                }
            }
        } 
        
        
        $this->_helper->json->sendJson($return);
    	exit; //for readability
    }
    
  
    /**
     * @author Ancuta
     * TODO-2872 Ancuta 24.03.2020 pct1)
     */
    public function chooseorderAction(){
        setlocale(LC_ALL, 'de_DE.UTF-8');
        $this->_helper->layout->setLayout('layout_ajax');
        
        if(!isset($_REQUEST['patient']) && !isset($_REQUEST['date'])){
            echo $this->view->translate('[please select patient and date]');
            return;
        }
        
        $decid = Pms_Uuid::decrypt($_REQUEST['patient']);
        $ipid = Pms_CommonData::getIpid($decid);
        $order_date = $_REQUEST['date'];
        
        
        $ord = $this->_order_management_tabs($ipid,$order_date,'all_active');

        $current_Date_orders = array();
        if( ! empty($ord[$ipid]['Xorder_scheduled_details']) && !empty($ord[$ipid]['Xorder_scheduled_details'][$order_date])){
            $current_Date_orders = $ord[$ipid]['Xorder_scheduled_details'][$order_date];
        }

        $this->view->current_Date = $order_date;
        $this->view->current_patient = $_REQUEST['patient'];
        $this->view->current_patient_name = $ord[$ipid]['nice_name'];
        $this->view->current_Date_orders = $current_Date_orders;
       
    }
    
    //ISPC-2639 Carmen 17.07.2020
    public function pdforderdownloadAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout->setLayout('layout_ajax');
    	$file = $_SESSION['filename'];
    	$pdfname_translated = $_REQUEST['pdfname'];
    	
    	// Header content type
    	header('Content-type: application/pdf');
    	
    	header('Content-Disposition: attachment; filename="' . $pdfname_translated . '"');
    	
    	header('Content-Transfer-Encoding: binary');
    	
    	header('Accept-Ranges: bytes');
    	
    	// Read the file
    	@readfile($file);
    	
    }
    //--
    
}
?>