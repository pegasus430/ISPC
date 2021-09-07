<?php
class CareservicesController extends Zend_Controller_Action
{

    public function init()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->clientid = $logininfo->clientid;
        $this->userid = $logininfo->userid;
        
        if(!$logininfo->clientid)
        {
        	//redir to select client error
        	$this->_redirect(APP_BASE . "error/noclient");
        	exit;
        }
    }

    public function listoldAction()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
    }
	     

    public function getlistAction()
    {
        $clientid = $this->clientid;
        $this->_helper->viewRenderer->setNoRender();
         
        if(!$_REQUEST['length']){
            $_REQUEST['length'] = "100";
        }
        
        $limit = $_REQUEST['length'];
        $offset = $_REQUEST['start'];
        $search_value = $_REQUEST['search']['value'];
         
        if(!empty($_REQUEST['order'][0]['column'])){
            $order_column = $_REQUEST['order'][0]['column'];
        } else{
            $order_column = "1";
        }
        $order_dir = $_REQUEST['order'][0]['dir'];
         
        $columns_array = array(
            "1" => "groupname",
            "3" => "create_date",
            "2" => "create_user",
        );
         
        $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
         
        if ($clientid > 0)
        {
            $where = ' and client=' . $clientid;
        }
        else
        {
            $where = ' and client=0';
        }
         
        // ########################################
        // #####  Query for count ###############
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('count(*)');
        $fdoc1->from('CareservicesGroups');
        $fdoc1->where("isdelete = 0  " . $where." ");
        /* ------------- Search options ------------------------- */
        if (isset($search_value) && strlen($search_value) > 0)
        {
            $fdoc1->andWhere("groupname like ?", "%" . trim($search_value) . "%");
        }
        $fdoc1->orderBy($order_by_str);
        $fdocexec = $fdoc1->execute();
        $fdocarray = $fdocexec->toArray();
         
        $full_count  = $fdocarray[0]['count'];
         
        // ########################################
        // #####  Query for details ###############
        $vw_sql = '*,';
        $fdoc1->select($vw_sql);
        $fdoc1->where("isdelete = 0  " . $where." ");
        /* ------------- Search options ------------------------- */
        if (isset($search_value) && strlen($search_value) > 0)
        {
            $fdoc1->andWhere("groupname like ?", "%" . trim($search_value) . "%");
        }
        $fdoc1->limit($limit);
        $fdoc1->offset($offset);
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
        foreach($fdoclimit_arr as $report_id =>$report_data){
            $link = '%s ';
             
            $resulted_data[$row_id]['groupname'] = sprintf($link,$report_data['groupname']);
            $resulted_data[$row_id]['create_date'] = sprintf($link,date('d.m.Y H:i',strtotime($report_data['create_date'])));
            $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$report_data['create_user']]);
            $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$report_data['create_user']]);

            $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'careservices/editgroup?id='.$report_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$report_data['id'].'" id="delete_'.$report_data['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
            $row_id++;
        }
         
        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
        $response['recordsTotal'] = $full_count;
        $response['recordsFiltered'] = $full_count; // ??
        $response['data'] = $resulted_data;
         
        echo json_encode($response);
        exit;
    }

    public function addgroupAction()
    {
        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        	
        if($this->getRequest()->isPost())
        {
            $group_form = new Application_Form_CareservicesGroups();
    
            if($group_form->validate($_POST))
            {
                $group_id= $group_form->InsertData($_POST);
    
                if($group_id)
                {
                    
                    foreach($_POST['items'] as $item_id => $data_items)
                    {
                        $pfl_cl = new CareservicesItems();
                        $pfl_cl->item = $data_items['item'];
                        $pfl_cl->group_id = $group_id;
                        $pfl_cl->clientid = $this->clientid; //ISPC-2652, elena, 08.10.2020
                        $pfl_cl->save();
                     }
                }
            
            
                $this->view->error_message = $this->view->translate("recordinsertsucessfully");
            }
            else
            {
                $group_form->assignErrorMessages();
                $this->retainValues($_POST);
            }
    
        }
    
    }
    
    
    public function editgroupAction()
    {
        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        $this->_helper->viewRenderer('addgroup');

        
        
        
        if($this->getRequest()->isPost())
        {
            if ($_GET['id'] > 0)
            {
                $group_id = $_GET['id'];
            }
            
            
            $group_form = new Application_Form_CareservicesGroups();
    
            if($group_form->validate($_POST))
            {
                $_POST['id'] = $group_id;
                $group_form->UpdateData($_POST);
    
                                	
                if($group_id)
                {
                    foreach($_POST['items'] as $item_id => $data_item){
                    
                        if($data_item['custom'] == "0")
                        {
                            // update existing
                            $q = Doctrine_Query::create()
                            ->update('CareservicesItems')
                            ->set('item','?', $data_item["item"])
                            ->set('change_date','?', date('Y-m-d H:i:s'))
                            ->set('change_user','?',  $this->userid)
                            ->where('id="' .$item_id . '" and group_id="' . $group_id . '" ');
                            $q->execute();
                    
                        } 
                        else
                        {
                                $pfl_cl = new CareservicesItems();
                                $pfl_cl->item = $data_item['item'];
                                $pfl_cl->group_id = $group_id;
                                $pfl_cl->clientid = $this->clientid; //ISPC-2652, elena, 08.10.2020
                               $pfl_cl->save();
                        }
                    }
                
                    // delete associations to patients
                    if(strlen($_POST['delete_items']) > 0 )
                    {
                        $delete_ids = explode(',',$_POST['delete_items']);
                        
                        if(is_array($delete_ids))
                        {
                            $q = Doctrine_Query::create()
                            ->update('CareservicesItems')
                            ->set('isdelete', '1')
                            ->set('change_date', '"' . date('Y-m-d H:i:s'). '"')
                            ->set('change_user',  $this->userid)
                            ->whereIn('id',$delete_ids);
                            $q->execute();
                        }
                    }
                }
                //$this->view->error_message = $this->view->translate("recordinsertsucessfully");
                $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
                $this->_redirect(APP_BASE . 'careservices/list?flg=suc&mes='.urlencode($this->view->error_message));
            }
            else
            {
                $group_form->assignErrorMessages();
                $this->retainValues($_POST);
            }
    
        }
        
        if ($_GET['id'] > 0)
        {
            $gr_id = $_GET['id'];
            $fdoc = Doctrine::getTable('CareservicesGroups')->find($gr_id );
        
            if ($fdoc)
            {
                $fdocarray = $fdoc->toArray();
                $vw_details = $fdocarray;
                $this->retainValues($fdocarray);
                
                
                $group_items_arr = CareservicesItems::get_groups_items($gr_id );
                
                foreach($group_items_arr as $k=>$items_data)
                {
                    $group_items[$items_data['id']]['entry_id'] = $items_data['id'];
                    $group_items[$items_data['id']]['item'] = $items_data['item'];
                }

                $this->view->items = $group_items;
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
        
        if($_GET['id']){
            $thrash = Doctrine::getTable('CareservicesGroups')->find($_GET['id']);
            $thrash->isdelete = 1;
            $thrash->save();
            
            $this->view->error_message = $this->view->translate("recorddeletedsucessfully");
            $this->_redirect(APP_BASE . "careservices/list?flg=suc&mes=".urlencode($this->view->error_message));
            exit;
        }    
    }
    
       
    
    
    
    

    private function retainValues ( $values = array(), $prefix = '' )
    {
    
        foreach ($values as $key => $val)
        {
            if (!is_array($val))
            {
                $this->view->$key = $val;
            }
            else
            {//retain 1 level array used in multiple hospizvbulk form
                foreach ($val as $k_val => $v_val)
                {
                    if (!is_array($v_val))
                    {
                        $this->view->{$prefix . $key . $k_val} = $v_val;
                    }
                }
            }
        }
    }
    
    
    //get view list care services
    public function listAction(){
    	$clientid = $this->clientid;
    		
    	//populate the datatables
    	if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {    			
	    	$all_users = Pms_CommonData::get_client_users($clientid, true);
	        
	        foreach($all_users as $keyu => $user)
	        {
	            $all_users_array[$user['id']] = trim($user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name']);
	        }
    
    		$this->_helper->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    		if(!$_REQUEST['length']){
    			$_REQUEST['length'] = "25";
    		}
    		$limit = (int)$_REQUEST['length'];
    		$offset = (int)$_REQUEST['start'];
    		$search_value = addslashes($_REQUEST['search']['value']);
    		
    		$columns_array = array(
    				"0" => "groupname",
    				"1" => "create_date",
    				"2" => "created_by"
    
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
    		$fdoc1->from('CareservicesGroups');
    		$fdoc1->where("client = ?", $clientid);
    		$fdoc1->andWhere("isdelete = 0  ");
    
    		$fdocarray = $fdoc1->fetchArray();
    		$full_count  = $fdocarray[0]['count'];
    
    		// ########################################
    		// #####  Query for details ###############
    		$fdoc1->select('*');
    		
    		if($order_column != "2")
    		{
    			$fdoc1->orderBy($order_by_str);
    		}
    
    		$fdoclimit = $fdoc1->fetchArray();
    
    		foreach ($fdoclimit as $key=> $row) {
    			$row['created_by'] = $all_users_array[$row['create_user']];
    			$fdoclimit[$key] = $row;
    		}
    		
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
							if($pkey == 'create_date')
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
    		//var_dump($full_count);
    			
    		if($order_column == "2")
    		{    			
    			$sort_col = array();
    			foreach ($fdoclimit as $key=> $row)
    			{
    				$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
    				$fdoclimit[$key] = $row;
    				$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
    			}
    			
    			if($order_dir == 'desc')
    			{
    				$dir = SORT_DESC;
    			}
    			else
    			{
    				$dir = SORT_ASC;
    			}
    			array_multisort($sort_col, $dir, $fdoclimit);
    			
    			$keyw = $columns_array[$order_column].'_tr';
    			array_walk($fdoclimit, function (&$v) use ($keyw) {
    				unset($v[$keyw]);
    			});
    		}
    
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
    			$resulted_data[$row_id]['groupname'] = sprintf($link,$mdata['groupname']);
    			$resulted_data[$row_id]['create_date'] = sprintf($link,date('d.m.Y H:i', strtotime($mdata['create_date'])));
    			$resulted_data[$row_id]['created_by'] = sprintf($link,$mdata['created_by']);
   
    			$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'careservices/editgroup?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
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
