<?php
	class XbdtnewactionsController extends Zend_Controller_Action {

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

		/* ##################################################################### */
		/* ############ XBDT action list :: Leistungen  ####################### */
		/* ##################################################################### */
		
		public function actionlistAction()
		{
		    set_time_limit(0);
		    $clientid = $this->clientid;
		    

		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('*');
		    $fdoc1->from('XbdtActions');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("block_option_id != 0");
		    $existing_cf_blocks_options = $fdoc1->fetchArray();
		    
		    $used_blocks_actions = array();
		    foreach($existing_cf_blocks_options as $k=>$data){
		        $used_blocks_actions[] = $data['block_option_id'];
		    }
		    $this->view->used_blocks_actions = $used_blocks_actions;
		    
    
		    
		    $blocks_settings = new FormBlocksSettings();
		    $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);
		    
		    $block_actions= array();
		    
		    $allowed_blocks_obj = new XbdtActions();
		    $allowed_blocks = $allowed_blocks_obj->xbdt_contact_form_blocks();
		    $this->view->allowed_cf_blocks = $allowed_blocks['contact_form_blocks'];
		    $this->view->allowed_xbdt_groups = $allowed_blocks['xbdt_groups'];
		    
		    foreach($blocks_settings_array as $key => $value)
		    {
		        if(!in_array($value['id'],$used_blocks_actions) && array_key_exists($value['block'], $allowed_blocks['contact_form_blocks'])  ){
    		        $block_actions[$value['id']] = $value;
    		        if($value['valid_till'] == "0000-00-00 00:00:00"){
        		        $block_actions[$value['id']]['active'] = "1";
    		        } else{
        		        $block_actions[$value['id']]['active'] = "0";
    		        }
		        }
		    }
		    
// 		    dd($block_actions);
		    
		    
		    $this->view->blocks_settings = $block_actions;

		}
		
		
		public function getactionlistAction()
		{
		    $clientid = $this->clientid;
		    
		    $this->_helper->viewRenderer->setNoRender();
		     
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
		        "2" => "name",
		        "3" => "groupname",
		        "4" => "available"
		    );
		     
		    $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		     
		    // ########################################
		    // #####  Query for count ###############
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select('count(*)');
		    $fdoc1->from('XbdtActions');
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("name like ? ","%" . trim($search_value) . "%");
		    }
		    $fdoc1->orderBy($order_by_str);
		    $fdocexec = $fdoc1->execute();
		    $fdocarray = $fdocexec->toArray();
		     
		    $full_count  = $fdocarray[0]['count'];
		     
		    // ########################################
		    // #####  Query for details ###############
		    $sql = '*,';
		    $fdoc1->select($sql);
		    $fdoc1->where("isdelete = 0  ");
		    $fdoc1->andWhere("clientid = ".$clientid);
		    $fdoc1->andWhere("extra = 0");
		    /* ------------- Search options ------------------------- */
		    if (isset($search_value) && strlen($search_value) > 0)
		    {
		        $fdoc1->andWhere("name like ? ","%" . trim($search_value) . "%");
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
		    foreach($fdoclimit_arr as $report_id =>$mdata)
		    {
		        $link = '%s ';
		         
		        $resulted_data[$row_id]['action_id'] = sprintf($link,$mdata['action_id']);
		        $resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
		        $resulted_data[$row_id]['groupname'] = sprintf($link,$mdata['groupname']);
		        if($mdata['available'] == "1"){
    		        $resulted_data[$row_id]['available'] = "Ja";
		        } else{
    		        $resulted_data[$row_id]['available'] = "Nein";
		        }
		        $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'xbdtnewactions/edit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
		        $row_id++;
		    }
		     
		    $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		    $response['recordsTotal'] = $full_count;
		    $response['recordsFiltered'] = $full_count; // ??
		    $response['data'] = $resulted_data;
		     
		    header("Content-type: application/json; charset=UTF-8");
		    
		    echo json_encode($response);
		    exit;
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
		        $med_form = new Application_Form_XbdtActions();
		
		        if($med_form->validate($_POST))
		        {
		            $med_form->insert($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }
		    
		    $allowed_blocks_obj = new XbdtActions();
		    $allowed_blocks = $allowed_blocks_obj->xbdt_contact_form_blocks();
		    $this->view->allowed_cf_blocks = $allowed_blocks['contact_form_blocks'];
		    
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
		
		        $med_form = new Application_Form_XbdtActions();
		
		        if($med_form->validate($_POST))
		        {
		            $_POST['id'] = $action_id;
		            $med_form->update($_POST);
		            $this->view->error_message = $this->view->translate("recordinsertsucessfully");
		        }
		        else
		        {
		            $med_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		    if ($_GET['id'] > 0)
		    {
		        $u_id = $_GET['id'];
		        $fdoc = Doctrine::getTable('XbdtActions')->find($u_id );
		        $allowed_blocks_obj = new XbdtActions();
		        $allowed_blocks = $allowed_blocks_obj->xbdt_contact_form_blocks();
		        $this->view->allowed_cf_blocks = $allowed_blocks['contact_form_blocks'];
		        
		        if ($fdoc)
		        {
		            $fdocarray = $fdoc->toArray();
		            $this->retainValues($fdocarray);
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

		        $thrash = Doctrine::getTable('XbdtActions')->find($_GET['id']);
		        $thrash->isdelete = 1;
		        $thrash->save();
		
		        $this->_redirect(APP_BASE . "xbdtnewactions/actionlist");
		        exit;
		    }
		}

		
		public function copyactionsAction ()
		{
		    $this->_helper->viewRenderer->setNoRender();

		    $has_edit_permissions = Links::checkLinkActionsPermission();
		
		    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    if($this->getRequest()->isPost())
		    {
		        if(!empty($_POST['actions'])){
		            foreach($_POST['actions'] as $action_id => $act_details){
		                if(isset($act_details['value']) && $act_details['value'] == "1"){

		                    
		                    $search2groups_arr[] = array(
		                        "action_id" => $act_details['shortcut'],
		                        "name" => $act_details['name'],
		                        "groupname" => $act_details['group'],
		                        "clientid" => $this->clientid,
		                        "block_option_id" => $action_id,
		                        "contact_form_block" => $act_details['contact_form_block'],
		                        "available" => $act_details['available']
		                    );
		                }
		            }

		            if (! empty($search2groups_arr)) {
		                $collection = new Doctrine_Collection('XbdtActions');
		                $collection->fromArray($search2groups_arr);
		                $collection->save();
		            }
		        }
		        
		        echo json_encode("1");
                exit;
		    }
		}
		
	}

?>