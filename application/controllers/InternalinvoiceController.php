<?php
	// Maria:: Migration ISPC to CISPC 08.08.2020
	class InternalinvoiceController extends Zend_Controller_Action {

		public function init()
		{

		    $this->logininfo = new Zend_Session_Namespace('Login_Info');

		    // ISPC-2609 Ancuta 29.09.2020
		    $this->user_print_jobs = 1;
		    //
		    
		}

		public function pricelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$form = new Application_Form_InternalInvoicePriceList();
			$p_lists = new InternalInvoicePriceList();


			if($clientid == '0')
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($_REQUEST['list'])
			{
				$list = $_REQUEST['list'];
			}

			if($_REQUEST['op'] == 'del' && $list)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$delete_list = $form->delete_price_list($list);

				$this->_redirect(APP_BASE . "internalinvoice/pricelist");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$post_list_data = $_POST;

				if($form->validate_period($post_list_data))
				{
					if($_REQUEST['op'] == 'edit' && $post_list_data['edit_period'] == '1')
					{
						$returned_list_id = $form->edit_list($post_list_data, $list);
					}
					else
					{
						$returned_list_id = $form->save_price_list($post_list_data);
					}

					$this->_redirect(APP_BASE . "internalinvoice/pricelist");
					exit;
				}
				else
				{
					$form->assignErrorMessages();
				}
			}

			$price_lists = $p_lists->get_lists($clientid);

			$this->view->listid = $list;
			$this->view->price_list = $price_lists;
		}

		public function specificvisitsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$usergroup = new Usergroup();
			$form_types = new FormTypes();

			
			$showtime_module = new Modules();
			if($showtime_module->checkModulePrivileges("78", $clientid))
			{
				$showtime_option = true;
			}
			else
			{
				$showtime_option = false;
			}
			$this->view->showtime_option = $showtime_option;
			
			
			$specific_products = new InternalInvoicesSpecificVisits();
			$form = new Application_Form_InternalInvoicesSpecificVisits();
			$p_lists = new InternalInvoicePriceList();

			//check if the list belongs to this client
			if($_REQUEST['list'])
			{
				$list = '';

				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "internalinvoice/pricelist");
				}

				//get curent list products
				if(strlen($list) > 0)
				{
					$products_list = $specific_products->get_list_products($list, $clientid);

					$this->view->products = $products_list;
				}
			}
			else
			{
				$this->_redirect(APP_BASE . 'internalinvoice/pricelist');
				exit;
			}



			//get client user groups
			$user_groups = $usergroup->getClientGroups($clientid);

			foreach($user_groups as $k_group => $v_group)
			{
				//used in js code (view)
				$user_groups_ids[0] = '';
				$user_groups_names[0] = $this->view->translate('select_group');

				$user_groups_ids[] = $v_group['id'];
				$user_groups_names[] = $v_group['groupname'];

				//used in php code (view)
				$user_groups_arr[$v_group['id']] = $v_group['groupname'];
			}

			$this->view->group_ids = $user_groups_ids;
			$this->view->group_names = $user_groups_names;
			$this->view->users_groups = $user_groups_arr;

			
			// visits types 
			$visit_types_array = Pms_CommonData::get_visit_types();
			
			$visits_names[0] = $this->view->translate('select_formular');
			$i = 1;
			foreach($visit_types_array as $visit){
			    $visit_types_ids[0] = "";
			    $visit_types_ids[] = $visit;
			    
    			$visits_names[$i] = $this->view->translate("visit_name_".$visit);
    			$visit_types[$visit] = $this->view->translate("visit_name_".$visit);
    			$i++;
			}

			
/* 			//get client form types
			$form_types = $form_types->get_form_types($clientid);

			foreach($form_types as $k_ft => $v_ft)
			{
				//used in js code (view)
				$form_types_ids[0] = '';
				$form_types_names[0] = $this->view->translate('select_formular');

				$form_types_ids[] = $v_ft['id'];
				$form_types_names[] = $v_ft['name'];

				//used in php code (view)
				$form_types_arr[$v_ft['id']] = $v_ft['name'];
				
				
			}

			$this->view->cf_ids = $form_types_ids;
			$this->view->cf_names = $form_types_names;
			$this->view->form_types = $form_types_arr;
 */
			
			$this->view->visit_ids = $visit_types_ids ;
			$this->view->visit_names = $visits_names;
			$this->view->visit_types = $visit_types;

			
			
			

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if($form->validate($_POST))
				{
					//do save && update
					$form->insert_product($_POST, $clientid, $list);
					$this->_redirect(APP_BASE . 'internalinvoice/specificvisits?list=' . $list);
					exit;
				}
				else
				{
					//assign errors
					$form->assignErrorMessages();

					foreach($_POST['update_pid'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						$pseudo_products[$k_row]['visit_type'] = $_POST['form_type'][$k_row];
						$pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
						$pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
						$pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
						$pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
						$pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
						$pseudo_products[$k_row]['price'] = $_POST['price'][$k_row];
						$pseudo_products[$k_row]['name'] = $_POST['name'][$k_row];
						$pseudo_products[$k_row]['code'] = $_POST['code'][$k_row];
						$pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
					}

					foreach($_POST['new_product'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						$pseudo_products[$k_row]['visit_type'] = $_POST['form_type'][$k_row];
						$pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
						$pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
						$pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
						$pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
						$pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
						$pseudo_products[$k_row]['price'] = $_POST['price'][$k_row];
						$pseudo_products[$k_row]['name'] = $_POST['name'][$k_row];
						$pseudo_products[$k_row]['code'] = $_POST['code'][$k_row];
						$pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
						$pseudo_products[$k_row]['asigned_users'] = $_POST['related_users'][$k_row];
						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
					}

					//retain submited values into view->products
					$this->view->products = $pseudo_products;
				}
			}
		}

		public function specificproductsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$usergroup = new Usergroup();
			$form_types = new FormTypes();

			
			$showtime_module = new Modules();
			if($showtime_module->checkModulePrivileges("78", $clientid))
			{
				$showtime_option = true;
			}
			else
			{
				$showtime_option = false;
			}
			$this->view->showtime_option = $showtime_option;
			
			
			$specific_products = new InternalInvoicesSpecificProducts();
			$form = new Application_Form_InternalInvoicesSpecificProducts();
			$p_lists = new InternalInvoicePriceList();

			//check if the list belongs to this client
			if($_REQUEST['list'])
			{
				$list = '';

				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "internalinvoice/pricelist");
				}

				//get curent list products
				if(strlen($list) > 0)
				{
					$products_list = $specific_products->get_list_products($list, $clientid);

					$this->view->products = $products_list;
				}
			}
			else
			{
				$this->_redirect(APP_BASE . 'internalinvoice/pricelist');
				exit;
			}



			//get client user groups
			$user_groups = $usergroup->getClientGroups($clientid);

			foreach($user_groups as $k_group => $v_group)
			{
				//used in js code (view)
				$user_groups_ids[0] = '';
				$user_groups_names[0] = $this->view->translate('select_group');

				$user_groups_ids[] = $v_group['id'];
				$user_groups_names[] = $v_group['groupname'];

				//used in php code (view)
				$user_groups_arr[$v_group['id']] = $v_group['groupname'];
			}

			$this->view->group_ids = $user_groups_ids;
			$this->view->group_names = $user_groups_names;
			$this->view->users_groups = $user_groups_arr;

			//get client form types
			$form_types = $form_types->get_form_types($clientid);

			foreach($form_types as $k_ft => $v_ft)
			{
				//used in js code (view)
				$form_types_ids[0] = '';
				$form_types_names[0] = $this->view->translate('select_formular');

				$form_types_ids[] = $v_ft['id'];
				$form_types_names[] = $v_ft['name'];

				//used in php code (view)
				$form_types_arr[$v_ft['id']] = $v_ft['name'];
			}

			$this->view->cf_ids = $form_types_ids;
			$this->view->cf_names = $form_types_names;
			$this->view->form_types = $form_types_arr;


			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if($form->validate($_POST))
				{
					//do save && update
					$form->insert_product($_POST, $clientid, $list);
					$this->_redirect(APP_BASE . 'internalinvoice/specificproducts?list=' . $list);
					exit;
				}
				else
				{
					//assign errors
					$form->assignErrorMessages();

					foreach($_POST['update_pid'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						$pseudo_products[$k_row]['contactform_type'] = $_POST['form_type'][$k_row];
						$pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
						$pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
						$pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
						$pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
						$pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
						$pseudo_products[$k_row]['price'] = $_POST['price'][$k_row];
						$pseudo_products[$k_row]['name'] = $_POST['name'][$k_row];
						$pseudo_products[$k_row]['code'] = $_POST['code'][$k_row];
						$pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
						$pseudo_products[$k_row]['showtime'] = $_POST['showtime'][$k_row];
					}

					foreach($_POST['new_product'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						$pseudo_products[$k_row]['contactform_type'] = $_POST['form_type'][$k_row];
						$pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
						$pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
						$pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
						$pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
						$pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
						$pseudo_products[$k_row]['price'] = $_POST['price'][$k_row];
						$pseudo_products[$k_row]['name'] = $_POST['name'][$k_row];
						$pseudo_products[$k_row]['code'] = $_POST['code'][$k_row];
						$pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
						$pseudo_products[$k_row]['asigned_users'] = $_POST['related_users'][$k_row];
						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
						$pseudo_products[$k_row]['showtime'] = $_POST['showtime'][$k_row];
					}

					//retain submited values into view->products
					$this->view->products = $pseudo_products;
				}
			}
		}

		public function dayproductsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;


			$usergroup = new Usergroup();
			$day_products = new InternalInvoicesDayProducts();
			$form = new Application_Form_InternalInvoicesDayProducts();
			$p_lists = new InternalInvoicePriceList();

			if($_REQUEST['list'])
			{
				$list = '';

				$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

				if($p_lists_check)
				{
					$list = $_REQUEST['list'];
				}
				else
				{
					//get user out of here if the list does not belong to current clientid;
					$list = false; //just to be sure
					$this->_redirect(APP_BASE . "internalinvoice/pricelist");
				}

				//get curent list products
				if(strlen($list) > 0)
				{
					$products_list = $day_products->get_list_products($list, $clientid);

					$this->view->products = $products_list;
				}
			}
			else
			{
				$this->_redirect(APP_BASE . 'internalinvoice/pricelist');
				exit;
			}


			//get client user groups
			$user_groups = $usergroup->getClientGroups($clientid);

			foreach($user_groups as $k_group => $v_group)
			{
				//used in js code (view)
				$user_groups_ids[] = $v_group['id'];
				$user_groups_names[] = $v_group['groupname'];

				//used in php code (view)
				$user_groups_arr[$v_group['id']] = $v_group['groupname'];
			}

			$this->view->group_ids = $user_groups_ids;
			$this->view->group_names = $user_groups_names;
			$this->view->users_groups = $user_groups_arr;

			//get sapvs
			$sapv_types = Pms_CommonData::getSapvCheckBox();
			$sapv_types['0'] = $this->view->translate('select_no_sapv');

			ksort($sapv_types);
			$this->view->sapv_ids = array_keys($sapv_types);
			$this->view->sapv_names = array_values($sapv_types);
			$this->view->sapv = $sapv_types;


			//check module permission
			$grouping_previleges = new Modules();
			if($grouping_previleges->checkModulePrivileges("76", $clientid))
			{
				$group_day_products = true;
				$this->view->grouping_option = '1';
			}
			else
			{
				$group_day_products = false;
				$this->view->grouping_option = '0';
			}
			
			//handle post
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				if($form->validate($_POST))
				{
					//do save && update
					$form->insert_product($_POST, $clientid, $list);
					$this->_redirect(APP_BASE . 'internalinvoice/dayproducts?list=' . $list);
					exit;
				}
				else
				{
					//assign errors
					$form->assignErrorMessages();

					foreach($_POST['update_pid'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						if($grouping_option)
						{
							$pseudo_products[$k_row]['grouped'] = $_POST['grouped'][$k_row];
						}
						$pseudo_products[$k_row]['sapv'] = $_POST['sapv'][$k_row];
						$pseudo_products[$k_row]['normal_price_name'] = $_POST['normal_price_name'][$k_row];
						$pseudo_products[$k_row]['normal_price'] = $_POST['normal_price'][$k_row];

						$pseudo_products[$k_row]['hosp_adm_price_name'] = $_POST['hosp_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_adm_price'] = $_POST['hosp_adm_price'][$k_row];

						$pseudo_products[$k_row]['hosp_price_name'] = $_POST['hosp_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_price'] = $_POST['hosp_price'][$k_row];

						$pseudo_products[$k_row]['hosp_dis_price_name'] = $_POST['hosp_dis_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_dis_price'] = $_POST['hosp_dis_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_adm_price_name'] = $_POST['hospiz_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_adm_price'] = $_POST['hospiz_adm_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_price_name'] = $_POST['hospiz_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_price'] = $_POST['hospiz_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_dis_price_name'] = $_POST['hospiz_dis_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_dis_price'] = $_POST['hospiz_dis_price'][$k_row];

						$pseudo_products[$k_row]['standby_price_name'] = $_POST['standby_price_name'][$k_row];
						$pseudo_products[$k_row]['standby_price'] = $_POST['standby_price'][$k_row];

						$pseudo_products[$k_row]['hosp_dis_hospiz_adm_price_name'] = $_POST['hosp_dis_hospiz_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_dis_hospiz_adm_price'] = $_POST['hosp_dis_hospiz_adm_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_dis_hosp_adm_price_name'] = $_POST['hospiz_dis_hosp_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_dis_hosp_adm_price'] = $_POST['hospiz_dis_hosp_adm_price'][$k_row];

						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
					}
					
					foreach($_POST['new_product'] as $k_row => $row_value)
					{
						$pseudo_products[$k_row]['id'] = $row_value;
						$pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
						
						if($grouping_option)
						{
							$pseudo_products[$k_row]['grouped'] = $_POST['grouped'][$k_row];
						}
						
						$pseudo_products[$k_row]['sapv'] = $_POST['sapv'][$k_row];

						$pseudo_products[$k_row]['normal_price_name'] = $_POST['normal_price_name'][$k_row];
						$pseudo_products[$k_row]['normal_price'] = $_POST['normal_price'][$k_row];

						$pseudo_products[$k_row]['hosp_adm_price_name'] = $_POST['hosp_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_adm_price'] = $_POST['hosp_adm_price'][$k_row];

						$pseudo_products[$k_row]['hosp_price_name'] = $_POST['hosp_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_price'] = $_POST['hosp_price'][$k_row];

						$pseudo_products[$k_row]['hosp_dis_price_name'] = $_POST['hosp_dis_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_dis_price'] = $_POST['hosp_dis_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_adm_price_name'] = $_POST['hospiz_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_adm_price'] = $_POST['hospiz_adm_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_price_name'] = $_POST['hospiz_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_price'] = $_POST['hospiz_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_dis_price_name'] = $_POST['hospiz_dis_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_dis_price'] = $_POST['hospiz_dis_price'][$k_row];

						$pseudo_products[$k_row]['standby_price_name'] = $_POST['standby_price_name'][$k_row];
						$pseudo_products[$k_row]['standby_price'] = $_POST['standby_price'][$k_row];

						$pseudo_products[$k_row]['hosp_dis_hospiz_adm_price_name'] = $_POST['hosp_dis_hospiz_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hosp_dis_hospiz_adm_price'] = $_POST['hosp_dis_hospiz_adm_price'][$k_row];

						$pseudo_products[$k_row]['hospiz_dis_hosp_adm_price_name'] = $_POST['hospiz_dis_hosp_adm_price_name'][$k_row];
						$pseudo_products[$k_row]['hospiz_dis_hosp_adm_price'] = $_POST['hospiz_dis_hosp_adm_price'][$k_row];

						$pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
					}

					//retain submited values into view->products
					$this->view->products = $pseudo_products;
				}
			}
		}
		
		public function actions2productsAction()
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    $usergroup = new Usergroup();
		    $form_types = new FormTypes();
		
		    	
		    $showtime_module = new Modules();
		    if($showtime_module->checkModulePrivileges("78", $clientid))
		    {
		        $showtime_option = true;
		    }
		    else
		    {
		        $showtime_option = false;
		    }
		    $this->view->showtime_option = $showtime_option;
		    	
		    	
		    $specific_products = new InternalInvoicesActionProducts();
		    $form = new Application_Form_InternalInvoicesActionProducts();
		    $p_lists = new InternalInvoicePriceList();
		
		    //check if the list belongs to this client
		    if($_REQUEST['list'])
		    {
		        $list = '';
		
		        $p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);
		
		        if($p_lists_check)
		        {
		            $list = $_REQUEST['list'];
		        }
		        else
		        {
		            //get user out of here if the list does not belong to current clientid;
		            $list = false; //just to be sure
		            $this->_redirect(APP_BASE . "internalinvoice/pricelist");
		        }
		
		        //get curent list products
		        if(strlen($list) > 0)
		        {
		            $products_list = $specific_products->get_list_products($list, $clientid);
            		foreach($products_list as $pid=>$pdata){
            		    $products_list[$pid]['actions'] = implode(',',array_unique($pdata['actions']));
            		}
		            
		            $this->view->products = $products_list;
		        }
		    }
		    else
		    {
		        $this->_redirect(APP_BASE . 'internalinvoice/pricelist');
		        exit;
		    }
		
		
		
		    //get client user groups
		    $user_groups = $usergroup->getClientGroups($clientid);
		
		    foreach($user_groups as $k_group => $v_group)
		    {
		        //used in js code (view)
		        $user_groups_ids[0] = '';
		        $user_groups_names[0] = $this->view->translate('select_group');
		
		        $user_groups_ids[] = $v_group['id'];
		        $user_groups_names[] = $v_group['groupname'];
		
		        //used in php code (view)
		        $user_groups_arr[$v_group['id']] = $v_group['groupname'];
		    }
		
		    $this->view->group_ids = $user_groups_ids;
		    $this->view->group_names = $user_groups_names;
		    $this->view->users_groups = $user_groups_arr;
		
		    //get client form types
		    $form_types = $form_types->get_form_types($clientid);
		
		    foreach($form_types as $k_ft => $v_ft)
		    {
		        //used in js code (view)
		        $form_types_ids[0] = '';
		        $form_types_names[0] = $this->view->translate('select_formular');
		
		        $form_types_ids[] = $v_ft['id'];
		        $form_types_names[] = $v_ft['name'];
		
		        //used in php code (view)
		        $form_types_arr[$v_ft['id']] = $v_ft['name'];
		    }
		    
		
		    $this->view->cf_ids = $form_types_ids;
		    $this->view->cf_names = $form_types_names;
		    $this->view->form_types = $form_types_arr;
		
		    $actions_data = XbdtActions::client_xbdt_actions($clientid);
		    foreach($actions_data as $k=>$ac){
		        $action_details[$ac['id']] = $ac;
		    
		        $usr['id'] = $ac['id'];
		        $usr['value'] = $ac['action_id'] . " | ". $ac['name'];
		    
		        $js_action_details[] = $usr;
		    }
		    
		    
		    $this->view->js_actions = $js_action_details;
		    
		
		    if($this->getRequest()->isPost())
		    {
		        $has_edit_permissions = Links::checkLinkActionsPermission();
		        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		        {
		            $this->_redirect(APP_BASE . "error/previlege");
		            exit;
		        }
		
		        
		        
		        if($form->validate($_POST))
		        {
		            //do save && update
		            $form->insert_product($_POST, $clientid, $list);
		            $this->_redirect(APP_BASE . 'internalinvoice/actions2products?list=' . $list);
		            exit;
		        }
		        else
		        {
		            //assign errors
		            $form->assignErrorMessages();
		
		            foreach($_POST['update_pid'] as $k_row => $row_value)
		            {
		                $pseudo_products[$k_row]['id'] = $row_value;
		                $pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
		                $pseudo_products[$k_row]['contactform_type'] = $_POST['form_type'][$k_row];
		                $pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
		                $pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
		                $pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
		                $pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
		                $pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
		                $pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
		                $pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
		                $pseudo_products[$k_row]['actions'] = implode(',',$_POST['actions'][$k_row]);
		            }
		
		            foreach($_POST['new_product'] as $k_row => $row_value)
		            {
		                $pseudo_products[$k_row]['id'] = $row_value;
		                $pseudo_products[$k_row]['usergroup'] = $_POST['user_group'][$k_row];
		                $pseudo_products[$k_row]['contactform_type'] = $_POST['form_type'][$k_row];
		                $pseudo_products[$k_row]['range_start'] = $_POST['range_start'][$k_row];
		                $pseudo_products[$k_row]['range_end'] = $_POST['range_end'][$k_row];
		                $pseudo_products[$k_row]['range_type'] = $_POST['range_type'][$k_row];
		                $pseudo_products[$k_row]['time_start'] = $_POST['time_start'][$k_row];
		                $pseudo_products[$k_row]['time_end'] = $_POST['time_end'][$k_row];
		                $pseudo_products[$k_row]['calculation_trigger'] = $_POST['trigger'][$k_row];
		                $pseudo_products[$k_row]['holiday'] = $_POST['holiday'][$k_row];
		                $pseudo_products[$k_row]['actions'] = implode(',',$_POST['actions'][$k_row]);
		            }
		
		            //retain submited values into view->products
		            $this->view->products = $pseudo_products;
		        }
		    }
		}		
		
		
		private function sv_rules($specific_products, $period, $ipid, $clientid, $users_ids_associated, $national_holidays, $previous_items, $active_patient_details, $pricelist_days, $selected_month = 0, $invoice_full_period=false)
		{
		    
		    // ISPC-2233  p.1
		    // added on 30.08.2018 by Ancuta
		    // force the active period to integrate the entire invoice period, not only active days
		    if ( $invoice_full_period ){
		        
		        // TODO-2334 -  added by Ancuta - 27.05.2019 - Allow ALL actions to be billed even after discharge - requested in ISPC-2233 in p.1
		        if($selected_month == "0"){
    		        $period['end'] = date("Y-m-d");
		        }
		        //--
		        $patientMaster = new PatientMaster();
		        $active_patient_details[$ipid]['active_days'] = array();
		        $active_patient_details[$ipid]['active_days'] = $patientMaster->getDaysInBetween($period['start'], $period['end'],false,"d.m.Y");
		    }
		    // -- END
		    
		    
		    
			//get patient visits, deleted from verlauf are excluded
			$pat_course = new PatientCourse();
			$excluded_visits = $pat_course->get_deleted_visits_multiple_patients(array($ipid));

// 			print_r($excluded_visits); exit;
			

			//get client user groups
			$grps = new UserGroup();
			$c_groups = $grps->getClientGroups($clientid);
			$c_groups_ids[] = '99999999999';
			foreach($c_groups as $k_group => $v_group)
			{
				$c_groups_ids[] = $v_group['id'];
			}

			//get groups users
			$groups_users = $grps->get_groups_users($c_groups_ids, $clientid);

			//get contact forms
			$cf = new ContactForms();
			$patient_working_cf = $cf->get_internal_invoice_contactforms($ipid, $excluded_cf[$ipid], $period);

			// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// new functions to get visits in period  - 
			
			foreach($specific_products as $key=>$pdetails){
			    $types[] = $pdetails['visit_type'];
			}
			$vtypes_array = array_unique($types);
			
			foreach($vtypes_array as $k=>$vtype){
			    
		        $visit_function = 'get_' . $vtype;
			    $patients_visits[$vtype] = $this->$visit_function ($ipid, $excluded_visits[$ipid][$vtype], $period);
			}
		
			if($_REQUEST['visits'] == "1")
			{
			    print_r(" \n types from price list \n ");
			    print_r($vtypes_array);
			    print_r(" \n all visits \n ");
			    print_r($patients_visits);
			    print_r(" \n previous_items \n ");
			    print_r($previous_items);
			}
			

			$condition_one = array();
			$condition_two = array();
			
			foreach($previous_items as $k_pitem => $v_pitem)
			{
				foreach($v_pitem['periods']['from_date'] as $k_date => $v_date)
				{
					//$arr[date][shortcutid] = qty;
					$previous_items_arr[strtotime($v_date)][$v_pitem['product']] = $v_pitem['qty'];
				}
			}
			
			foreach($specific_products as $k_product => $v_product_details)
			{
				//get client showtime module (no module means to act as not selected)
				$showtime_module = new Modules();
				if(!$showtime_module->checkModulePrivileges("78", $clientid))
				{
					//overwrite the saved value if any
					$v_product_details['showtime'] = '0';
				}

				$visit_type = $v_product_details['visit_type'];
				foreach($patients_visits[$visit_type] as $visit_id => $v_cf)
				{
					$cf_date = date('Y-m-d', strtotime($v_cf['date']));
 
					if($v_product_details['holiday'] == '1')
					{
						$check_holiday = true;
					}
					else
					{
						$check_holiday = false;
					}

					$time_diff = (strtotime($v_cf['end_date']) - strtotime($v_cf['start_date']));
					$v_cf['duration'] = ($time_diff / 60);
 
//					holiday debug
					if( in_array($cf_date,$pricelist_days[$v_product_details['list']]) &&
						(
							($check_holiday &&
								(in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) ||
								date('w', strtotime($v_cf['start_date'])) == '0' ||
								date('w', strtotime($v_cf['start_date'])) == '6')
							) || 
							(!$check_holiday &&
							!in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) && 
							date('w', strtotime($v_cf['start_date'])) != '0' && 
							date('w', strtotime($v_cf['start_date'])) != '6')
						)
					)
					{
					    
						//check range duration
						if($v_product_details['range_type'] == 'min')
						{
							if($v_cf['duration'] >= $v_product_details['range_start'] && $v_cf['duration'] <= $v_product_details['range_end'])
							{
								$condition_one[$visit_type][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check range distance
						if($v_product_details['range_type'] == 'km')
						{
							$clean_km_string = str_replace(' km', '', trim(rtrim($v_cf['fahrtstreke_km'])));

							if($clean_km_string >= $v_product_details['km_range_start'] && $clean_km_string <= $v_product_details['km_range_end'])
							{
								$condition_one[$visit_type][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check which time we use for reference
						$v_product_details_ts['time_start'] = strtotime('1970-01-01 ' . $v_product_details['time_start'] . ':00');
						$v_product_details_ts['time_end'] = strtotime('1970-01-01 ' . $v_product_details['time_end'] . ':00');
						$constant_midnight = strtotime('1970-01-01 00:00:00');
						
						if($v_product_details['calculation_trigger'] == 'time_start')
						{
							//use contact form start_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['start_date'])));
						}
						else if($v_product_details['calculation_trigger'] == 'time_end')
						{
							//use contact form end_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['end_date'])));
						}

						//hours condition
						if($v_product_details_ts['time_start'] < $v_product_details_ts['time_end']) //08-20 normal interval
						{
							if(($start_cf >= $v_product_details_ts['time_start'] && $start_cf < $v_product_details_ts['time_end']) && $condition_one[$visit_type][$v_cf['id']][$v_product_details['code']] == '1')
							{
								$condition_two[$visit_type][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}
						else if($v_product_details_ts['time_start'] > $v_product_details_ts['time_end']
							|| ($start_cf >= $constant_midnight && $start_cf < $v_product_details_ts['time_end'])
						) //20-08 interval (overnight)
						{
							if((($start_cf >= $v_product_details_ts['time_end'] && $start_cf >= $v_product_details_ts['time_start']) || ($start_cf < $v_product_details_ts['time_end']) ) && $condition_one[$visit_type][$v_cf['id']][$v_product_details['code']] == '1')
							{

								$condition_two[$visit_type][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}
						
						$reformated_date = date('d.m.Y', strtotime($v_cf['date']));
						
						//creator and aditional users
						if($condition_two[$visit_type][$v_cf['id']][$v_product_details['code']] == '1')
						{
								//check if creator is in same group as product requirement
								if(in_array($v_cf['create_user'], $groups_users[$v_product_details['usergroup']]) && in_array($reformated_date, $active_patient_details[$v_cf['ipid']]['active_days']))
								{
									$item_data = array();
									$formated_date = date('Y-m-d', strtotime($v_cf['date']));
									if(!empty($users_ids_associated[$v_cf['create_user']]))
									{
										//create master item for assigned creator user
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['p_type'] = $v_product_details['visit_type'];
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date; 
//										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
// 											$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
											$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['visits'][$v_cf['id']] = $v_cf;
										}
									}
									else
									{
										//create master item for creator user
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['p_type'] = $v_product_details['visit_type'];
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
//										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
// 											$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
											$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['visits'][$v_cf['id']] = $v_cf;
										}
									}
								}
						}
					}
				}
			}

			if($_REQUEST['visits'] == "1")
			{
			    print_r(" \n master_items \n ");
			    print_r($master_items);
			}
			
			
			//remove previous items qty
			foreach($master_items as $k_userid => $v_item_data)
			{
				foreach($v_item_data as $k_date => $v_item_details)
				{
					foreach($v_item_details as $k_shortcut_id => $v_values)
					{
						if(($v_values['normal'] - $previous_items_arr[$k_date][$k_shortcut_id]) > '0')
						{
							//remove qty
							$master_items[$k_userid][$k_date][$k_shortcut_id]['normal'] = ($v_values['normal'] - $previous_items_arr[$k_date][$k_shortcut_id]);
						}
						else
						{
							//remove shortcut if qty is negative
							unset($master_items[$k_userid][$k_date][$k_shortcut_id]);
						}
					}
				}
				
			}
			
			foreach($master_items as $k_userid => $v_item_data)
			{
				foreach($v_item_data as $k_date => $v_item_details)
				{
					if(empty($master_items[$k_userid][$k_date]))
					{
						unset($master_items[$k_userid][$k_date]);
					}
				}
				
				if(empty($master_items[$k_userid]))
				{
					unset($master_items[$k_userid]);
				}
			}
			return $master_items;
		}
		
		private function sp_rules($specific_products, $period, $ipid, $clientid, $users_ids_associated, $national_holidays, $previous_items, $active_patient_details, $pricelist_days,$selected_month = 0, $invoice_full_period = false)
		{
		    
            // ISPC-2233  p.1
            // added on 30.08.2018 by Ancuta
            // force the active period to integrate the entire invoice period, not only active days   
		    if ( $invoice_full_period ){
		        // TODO-2334 -  added by Ancuta - 27.05.2019 - Allow ALL actions to be billed even after discharge - requested in ISPC-2233 in p.1
		        if($selected_month =="0"){
    		        $period['end'] = date("Y-m-d");
		        }
		        $patientMaster = new PatientMaster();
		        $active_patient_details[$ipid]['active_days'] = array();
		        $active_patient_details[$ipid]['active_days'] = $patientMaster->getDaysInBetween($period['start'], $period['end'],false,"d.m.Y");
		    }
		    // -- END
		    		    
			//get patient contact forms, deleted from verlauf are excluded
			$pat_course = new PatientCourse();
			//TODO-4207 Ancuta 15.06.2021 :: use function from ContactForms
			//$excluded_cf = $pat_course->get_deleted_contactforms(array($ipid));
			$excluded_cf[$ipid] = ContactForms::get_deleted_contactforms_by_ipid(array($ipid));
            // -- 
            
			if($_REQUEST['visits'] == "1")
			{
			    echo "<pre>";
			    print_R(" \n ZXXXX excluded_cf \n");
			    print_R($excluded_cf);
			}
			
			
			//get client user groups
			$grps = new UserGroup();
			$c_groups = $grps->getClientGroups($clientid);
			$c_groups_ids[] = '99999999999';
			foreach($c_groups as $k_group => $v_group)
			{
				$c_groups_ids[] = $v_group['id'];
			}

			//get groups users
			$groups_users = $grps->get_groups_users($c_groups_ids, $clientid);

			//get contact forms
			$cf = new ContactForms();
			$patient_working_cf = $cf->get_internal_invoice_contactforms($ipid, $excluded_cf[$ipid], $period);


			$condition_one = array();
			$condition_two = array();
			
			foreach($previous_items as $k_pitem => $v_pitem)
			{
				foreach($v_pitem['periods']['from_date'] as $k_date => $v_date)
				{
					//$arr[date][shortcutid] = qty;
					$previous_items_arr[$v_pitem['user_invoice']][strtotime($v_date)][$v_pitem['product']] = $v_pitem['qty'];
				}
			}
			
			foreach($specific_products as $k_product => $v_product_details)
			{
				//get client showtime module (no module means to act as not selected)
				$showtime_module = new Modules();
				if(!$showtime_module->checkModulePrivileges("78", $clientid))
				{
					//overwrite the saved value if any
					$v_product_details['showtime'] = '0';
				}

				foreach($patient_working_cf as $k_cf => $v_cf)
				{
					$cf_date = date('Y-m-d', strtotime($v_cf['date']));
					if($v_product_details['holiday'] == '1')
					{
						$check_holiday = true;
					}
					else
					{
						$check_holiday = false;
					}

					$time_diff = (strtotime($v_cf['end_date']) - strtotime($v_cf['start_date']));
					$v_cf['duration'] = ($time_diff / 60);

//					holiday debug
					if($v_cf['form_type'] == $v_product_details['contactform_type'] && in_array($cf_date,$pricelist_days[$v_product_details['list']]) &&
						(
							($check_holiday &&
								(in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) ||
								date('w', strtotime($v_cf['start_date'])) == '0' ||
								date('w', strtotime($v_cf['start_date'])) == '6')
							) || 
							(!$check_holiday &&
							!in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) && 
							date('w', strtotime($v_cf['start_date'])) != '0' && 
							date('w', strtotime($v_cf['start_date'])) != '6')
						)
					)
					{
						//check range duration
						if($v_product_details['range_type'] == 'min')
						{
							if($v_cf['duration'] >= $v_product_details['range_start'] && $v_cf['duration'] <= $v_product_details['range_end'])
							{
								$condition_one[$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check range distance
						if($v_product_details['range_type'] == 'km')
						{
							$clean_km_string = str_replace(' km', '', trim(rtrim($v_cf['fahrtstreke_km'])));

							if($clean_km_string >= $v_product_details['km_range_start'] && $clean_km_string <= $v_product_details['km_range_end'])
							{
								$condition_one[$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check which time we use for reference
						$v_product_details_ts['time_start'] = strtotime('1970-01-01 ' . $v_product_details['time_start'] . ':00');
						$v_product_details_ts['time_end'] = strtotime('1970-01-01 ' . $v_product_details['time_end'] . ':00');
						$constant_midnight = strtotime('1970-01-01 00:00:00');
						
						if($v_product_details['calculation_trigger'] == 'time_start')
						{
							//use contact form start_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['start_date'])));
						}
						else if($v_product_details['calculation_trigger'] == 'time_end')
						{
							//use contact form end_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['end_date'])));
						}

						//hours condition
						if($v_product_details_ts['time_start'] < $v_product_details_ts['time_end']) //08-20 normal interval
						{
							if(($start_cf >= $v_product_details_ts['time_start'] && $start_cf < $v_product_details_ts['time_end']) && $condition_one[$v_cf['id']][$v_product_details['code']] == '1')
							{
								$condition_two[$v_cf['id']][$v_product_details['code']] = '1';
							}
						}
						else if($v_product_details_ts['time_start'] > $v_product_details_ts['time_end']
							|| ($start_cf >= $constant_midnight && $start_cf < $v_product_details_ts['time_end'])
						) //20-08 interval (overnight)
						{
							if((($start_cf >= $v_product_details_ts['time_end'] && $start_cf >= $v_product_details_ts['time_start']) || ($start_cf < $v_product_details_ts['time_end']) ) && $condition_one[$v_cf['id']][$v_product_details['code']] == '1')
							{

								$condition_two[$v_cf['id']][$v_product_details['code']] = '1';
							}
						}
						
						$reformated_date = date('d.m.Y', strtotime($v_cf['date']));
						
						//creator and aditional users
						if($condition_two[$v_cf['id']][$v_product_details['code']] == '1')
						{
							if($v_product_details['asigned_users'] == '1')
							{
								//create array with additional users which belong to the product group
								asort($v_cf['aditional_users']);
								asort($groups_users[$v_product_details['usergroup']]);

								$allowed_users[$v_cf['id']] = array_intersect($groups_users[$v_product_details['usergroup']], $v_cf['aditional_users']);

								//add create user if contactform has no aditional users
								if(empty($allowed_users[$v_cf['id']]))
								{
									$allowed_users[$v_cf['id']][] = $v_cf['create_user'];
								}

								//create master item for all aditional users which belong to product group
								if(!empty($allowed_users[$v_cf['id']]))
								{
									foreach($allowed_users[$v_cf['id']] as $k_group_user => $v_group_user)
									{
										$item_data = array();
										$formated_date = date('Y-m-d', strtotime($v_cf['date']));

										if(in_array($reformated_date, $active_patient_details[$v_cf['ipid']]['active_days']))
										{
											if(!empty($users_ids_associated[$v_group_user]))
											{
												//create master item for assigned user of allowed user
												$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
												$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
												$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['p_type'] = "contact_form";
												$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
	//											$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date] = $v_cf['id'];
												if($v_product_details['showtime'] == '1')
												{
													$master_items[$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
												}
											}
											else
											{
												//create master item for allowed user
												$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
												$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
												$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['p_type'] = "contact_form";
												$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
	//											$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date] = $v_cf['id'];
												if($v_product_details['showtime'] == '1')
												{
													$master_items[$v_group_user][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
												}
											}
										}
									}
								}
							}
							else
							{
								//check if creator is in same group as product requirement
								if(in_array($v_cf['create_user'], $groups_users[$v_product_details['usergroup']]) && in_array($reformated_date, $active_patient_details[$v_cf['ipid']]['active_days']))
								{
									$item_data = array();
									$formated_date = date('Y-m-d', strtotime($v_cf['date']));
									if(!empty($users_ids_associated[$v_cf['create_user']]))
									{
										//create master item for assigned creator user
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['p_type'] = "contact_form";
										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date; 
//										$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
											$master_items[$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
										}
									}
									else
									{
										//create master item for creator user
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['p_type'] = "contact_form";
										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
//										$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
											$master_items[$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
										}
									}
								}
							}
						}
					}
				}
			}
			//remove previous items qty
			foreach($master_items as $k_userid => $v_item_data)
			{
				foreach($v_item_data as $k_date => $v_item_details)
				{
					foreach($v_item_details as $k_shortcut_id => $v_values)
					{
						if(($v_values['normal'] - $previous_items_arr[$k_userid][$k_date][$k_shortcut_id]) > '0')
						{
							//remove qty
							$master_items[$k_userid][$k_date][$k_shortcut_id]['normal'] = ($v_values['normal'] - $previous_items_arr[$k_userid][$k_date][$k_shortcut_id]);
						}
						else
						{
							//remove shortcut if qty is negative
							unset($master_items[$k_userid][$k_date][$k_shortcut_id]);
						}
					}
				}
				
			}
			
			foreach($master_items as $k_userid => $v_item_data)
			{
				foreach($v_item_data as $k_date => $v_item_details)
				{
					if(empty($master_items[$k_userid][$k_date]))
					{
						unset($master_items[$k_userid][$k_date]);
					}
				}
				
				if(empty($master_items[$k_userid]))
				{
					unset($master_items[$k_userid]);
				}
			}
			return $master_items;
		}

		private function dp_rules($dayproducts, $period, $ipid, $clientid, $pinfo, $users_ids_associated, $national_holidays, $previous_items, $pricelist_days)
		{
			$grps = new UserGroup();
			$pm = new PatientMaster();
			$epid = Pms_CommonData::getEpid($ipid);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			//get client user groups
			$c_groups = $grps->getClientGroups($clientid);
			$c_groups_ids[] = '99999999999';
			foreach($c_groups as $k_group => $v_group)
			{
				$c_groups_ids[] = $v_group['id'];
			}

			//get groups users
			$groups_users = $grps->get_groups_users($c_groups_ids, $clientid);

			//get assigned users (from groups_users)
			$assigned_usr = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
				->where('epid="' . $epid . '"');
			$assigned_users = $assigned_usr->fetchArray();
			foreach($assigned_users as $k_usr => $v_usr)
			{
				$assignedusers[] = $v_usr['userid'];

				if(!empty($users_ids_associated[$v_usr['userid']]))
				{
					$assignedusers[] = $users_ids_associated[$v_usr['userid']];
				}
			}

			//get patient all sapv days
			$patient_sapv_days = $this->get_patient_all_sapv($ipid);

			//day products id mapped
			foreach($dayproducts as $k_d_pr => $v_d_pr)
			{
				$day_products[$v_d_pr['id']] = $v_d_pr;
				if($v_d_pr['grouped'] == '1')
				{
					$grouped_products[] = $v_d_pr['id'];
				}
			}

			foreach($previous_items as $k_item => $v_item_data)
			{
				$day_product_id = $v_item_data['product'];
				if(empty($previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days']))
				{
					$previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days'] = array();
				}
				
				if(empty($previous_items_arr['grouped_days']))
				{
					$previous_items_arr['grouped_days'] = array();
				}
				
				foreach($v_item_data['periods']['from_date'] as $k_period => $v_period_start)
				{
					if($v_item_data['type'] == 'dp')
					{
						if(date('Y-m-d', strtotime($v_item_data['periods']['till_date'][$k_period])) != '1970-01-01')
						{
							$previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days'] = array_merge($previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days'], $pm->getDaysInBetween($v_period_start, $v_item_data['periods']['till_date'][$k_period]));
						}
						else
						{
							$previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days'][] = date('Y-m-d', strtotime($v_period_start));
						}
						
						$previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days'] = array_values(array_unique($previous_items_arr[$day_product_id][$v_item_data['sub_item'].'_days']));
					}
					else if($v_item_data['type'] == 'gr')
					{
						if(strtotime(date('Y-m-d', strtotime($v_item_data['periods']['till_date'][$k_period]))) > strtotime('1970-01-01'))
						{
							$previous_items_arr['grouped_days'] = array_merge($previous_items_arr['grouped_days'] , $pm->getDaysInBetween($v_period_start, $v_item_data['periods']['till_date'][$k_period]));
						}
						else
						{
							$previous_items_arr['grouped_days'][] = date('Y-m-d', strtotime($v_period_start));
						}
						
						$previous_items_arr['grouped_days'] = array_values(array_unique($previous_items_arr['grouped_days']));
					}
					
				}
			}
			
			$period_days = $pm->getDaysInBetween($period['start'], $period['end']);

			//get patient treatment active days
			$patient_treatment = $pm->getTreatedDaysRealMultiple(array($ipid));

			if(count($patient_treatment[$ipid]['admissionDates']) > 0)
			{
				foreach($patient_treatment[$ipid]['admissionDates'] as $k_admission => $v_admission_data)
				{
					$start = date('Y-m-d', strtotime($v_admission_data['date']));
					$patient_treatment[$ipid]['admission_days'][] = $v_admission_data['date'];

					if(!empty($patient_treatment[$ipid]['dischargeDates'][$k_admission]['date']))
					{
						$end = date('Y-m-d', strtotime($patient_treatment[$ipid]['dischargeDates'][$k_admission]['date']));
						$patient_treatment[$ipid]['discharge_days'][] = $patient_treatment[$ipid]['dischargeDates'][$k_admission]['date'];
					}
					else
					{
						$end = date('Y-m-d', strtotime($patient_treatment[$ipid]['discharge_date']));
					}

					if(empty($patient_treatment[$ipid]['patient_treatment_days']))
					{
						$patient_treatment[$ipid]['patient_treatment_days'] = array();
					}

					$patient_treatment[$ipid]['patient_treatment_days'] = array_merge($patient_treatment[$ipid]['patient_treatment_days'], $pm->getDaysInBetween($start, $end));
				}
			}
			else
			{
				$start = date('Y-m-d', strtotime($patient_treatment[$ipid]['admission_date']));
				$end = date('Y-m-d', strtotime($patient_treatment[$ipid]['discharge_date']));

				$patient_treatment[$ipid]['admission_days'][0] = $start;
				$patient_treatment[$ipid]['discharge_days'][0] = $end;

				if(empty($patient_treatment[$ipid]['patient_treatment_days']))
				{
					$patient_treatment[$ipid]['patient_treatment_days'] = array();
				}

				$patient_treatment[$ipid]['patient_treatment_days'] = array_merge($patient_treatment[$ipid]['patient_treatment_days'], $pm->getDaysInBetween($start, $end));
			}


			//limit patient treatment period to the selected period
			$patient_treatment[$ipid]['patient_treatment_days'] = array_intersect($patient_treatment[$ipid]['patient_treatment_days'], $period_days);


			//get patient locations
			$patient_locations_days = $this->get_patient_locations_days($ipid, $clientid, $patient_treatment[$ipid]);
			if($_REQUEST['aq'])
			{
				print_r("patient_locations_days\n");
				print_r($patient_locations_days);
			}
			if(count($patient_locations_days['all_locations_days']) == '0')
			{
				$patient_locations_days['all_locations_days'] = array();
			}
			//construct no location patient days
			$patient_treatment[$ipid]['no_location_treatment_days'] = array_diff($patient_treatment[$ipid]['patient_treatment_days'], $patient_locations_days['all_locations_days']);
			asort($patient_treatment[$ipid]['no_location_treatment_days']);

			if($_REQUEST['aq'])
			{
				print_r("Patient Treatment\n");
				print_r($patient_treatment);
			}

			//put the remaining array days into the location normal days
			if(count($patient_locations_days['locations_days']['normal']) == '0')
			{
				$patient_locations_days['locations_days']['normal'] = array();
			}
			$patient_locations_days['locations_days']['normal'] = array_merge($patient_locations_days['locations_days']['normal'], $patient_treatment[$ipid]['no_location_treatment_days']);
			asort($patient_locations_days['locations_days']['normal']);
			$patient_locations_days['locations_days']['normal'] = array_values(array_unique($patient_locations_days['locations_days']['normal']));

			if($_REQUEST['aq'])
			{
				print_r("Patient Loc 2\n");
				print_r($patient_locations_days);
			}

			if(!empty($patient_locations_days['locations_days']['normal']))
			{
				$patient_locations_days['locations_days']['normal'] = array_values(array_unique($patient_locations_days['locations_days']['normal']));
			}

			//remove hospital adm/dis and hospiz adm/dis from normal days
			if(!empty($patient_locations_days['locations_days']['hosp']))
			{
				$patient_locations_days['locations_days']['hosp'] = array_values(array_unique($patient_locations_days['locations_days']['hosp']));

				$patient_locations_days['locations_days']['normal'] = array_diff($patient_locations_days['locations_days']['normal'], $patient_locations_days['locations_days']['hosp']);
			}

			if(!empty($patient_locations_days['locations_days']['hospiz']))
			{
				$patient_locations_days['locations_days']['hospiz'] = array_values(array_unique($patient_locations_days['locations_days']['hospiz']));

				$patient_locations_days['locations_days']['normal'] = array_diff($patient_locations_days['locations_days']['normal'], $patient_locations_days['locations_days']['hospiz']);
			}

			//get sapv patient details
			$vv_status2days = array('1' => 'be_days', '2' => 'ko_days', '3' => 'tv_days', '4' => 'vv_days');

			$patient_sapv_details = $this->get_patient_all_sapv($ipid);
			if($_REQUEST['dbgz'])
			{
				print_r("patient_locations_days\n");
				print_r($patient_locations_days);

				print_r("patient_treatment\n");
				print_r($patient_treatment);

				print_r("day_products\n");
				print_r($day_products);
				exit;
			}

			
			$user_replacements  = array();
			$u_vacantions = new UserVacations();
			$user_replacements= $u_vacantions->get_all_users_vacations($assignedusers,$ipid,$users_ids_associated);
				
			
			
			$vac_days_rpl_user = array();
			$replacement_users = array();
			$ureplacementp = array();
			$vac_days_rpl_user2user = array();
			foreach($assignedusers as $k=>$usera){
				
				if( ! empty($user_replacements[$usera][$ipid] ) ) {
					foreach($user_replacements[$usera][$ipid] as $rpl_user=>$vac_days){
						foreach($vac_days as $v_day){
							$vac_days_rpl_user[$v_day] = $rpl_user;
							$vac_days_rpl_user2user[$usera][$ipid][$v_day] = $rpl_user;
						}
					}
					$replacement_users = array_keys($user_replacements[$usera][$ipid]);
				}
			}

			$patient_treatment_days = array();
			foreach($patient_treatment[$ipid]['patient_treatment_days'] as $key => $v_day)
			{
				if(isset($vac_days_rpl_user[$v_day])){
					$patient_treatment_days[$ipid]['vacation'][$vac_days_rpl_user[$v_day]][] = $v_day;
				}
				else
				{
				
				}
				$patient_treatment_days[$ipid]['normal'][] = $v_day;
			
			}
			
			// ISPC-2151 
			$apply_vacation_days = "1";
			
			if($apply_vacation_days == "0"){
				$day_items = array();
				foreach($patient_treatment_days[$ipid]['normal'] as $key => $v_day)
				{
					foreach($day_products as $k_product => $v_product)
					{
						if(in_array($v_day, $pricelist_days[$v_product['list']]))
						{
							if($v_product['holiday'] == '1')
							{
								$check_holiday = true;
							}
							else
							{
								$check_holiday = false;
							}
				
							//holiday debug
							//LE: removed allready invoiced days
							if(($check_holiday && (in_array($v_day, $national_holidays) || date('w', strtotime($v_day)) == '0' || date('w', strtotime($v_day)) == '6')) || (!$check_holiday && !in_array($v_day, $national_holidays) && date('w', strtotime($v_day)) != '0' && date('w', strtotime($v_day)) != '6'))
							{
								$day_items[$v_product['id']]['pricelist_days'][$v_day] = $v_product['list'];
								if($v_product['sapv'] != '0' && in_array($v_day, $patient_sapv_details[$vv_status2days[$v_product['sapv']]])) //sapv product and patient treatment day is having product sapv
								{
									$day_items[$v_product['id']]['grouped'] = $v_product['grouped'];
				
									if($pinfo['isstandby'] == '1')
									{
										if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['standby_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")) //standby location day product
										{
											$day_items[$v_product['id']]['standby'] += '1';
											$day_items[$v_product['id']]['standby_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
									else
									{
				
										if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['normal_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))//normal location day product
										{
											$day_items[$v_product['id']]['normal'] += '1';
											$day_items[$v_product['id']]['normal_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									if(in_array($v_day, $patient_locations_days['locations_days']['hosp'])) //hospital location day product
									{
										if(in_array($v_day, $patient_locations_days['hosp_adm']) && $v_product['hosp_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_adm'] += '1';
											$day_items[$v_product['id']]['hosp_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(in_array($v_day, $patient_locations_days['hosp_dis']) && $v_product['hosp_dis_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_dis'] += '1';
											$day_items[$v_product['id']]['hosp_dis_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
										}
				
										if(!in_array($v_day, $day_items[$v_product['id']]['hosp_adm_days']) && !in_array($v_day, $day_items[$v_product['id']]['hosp_dis_days']) && $v_product['hosp_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp'] += '1';
											$day_items[$v_product['id']]['hosp_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									if(in_array($v_day, $patient_locations_days['locations_days']['hospiz'])) //hospiz location day product
									{
										//check if the day is admision or not
										if(in_array($v_day, $patient_locations_days['hospiz_adm']) && $v_product['hospiz_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_adm'] += '1';
											$day_items[$v_product['id']]['hospiz_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(in_array($v_day, $patient_locations_days['hospiz_dis']) && $v_product['hospiz_dis_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_dis'] += '1';
											$day_items[$v_product['id']]['hospiz_dis_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(!in_array($v_day, $day_items[$v_product['id']]['hospiz_adm_days']) && !in_array($v_day, $day_items[$v_product['id']]['hospiz_dis_days']) && $v_product['hospiz_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz'] += '1';
											$day_items[$v_product['id']]['hospiz_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									//					check if date is in both locations
									if(in_array($v_day, $patient_locations_days['locations_days']['hosp']) && in_array($v_day, $patient_locations_days['locations_days']['hospiz']))
									{
										//						hospital discharge - hospiz admision method
										if(in_array($v_day, $day_items[$v_product['id']]['hosp_dis_days']) && in_array($v_day, $day_items[$v_product['id']]['hospiz_adm_days']) && $v_product['hosp_dis_hospiz_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
											$day_items[$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
											//remove from array if verified
											unset($day_items[$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$v_product['id']]['hosp_dis_days'])]);
				
											$day_items[$v_product['id']]['hosp_dis'] -= 1;
											$day_items[$v_product['id']]['hospiz_adm'] -= 1;
				
											if(count($day_items[$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$v_product['id']]['hosp_dis'] < '0')
											{
												unset($day_items[$v_product['id']]['hosp_dis_days']);
												unset($day_items[$v_product['id']]['hosp_dis']);
											}
				
											unset($day_items[$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$v_product['id']]['hospiz_adm_days'])]);
				
											if(count($day_items[$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$v_product['id']]['hospiz_adm'] < '0')
											{
												unset($day_items[$v_product['id']]['hospiz_adm_days']);
												unset($day_items[$v_product['id']]['hospiz_adm']);
											}
										}
				
										//						hospiz discharge - hospital admision method
										if(in_array($v_day, $day_items[$v_product['id']]['hospiz_dis_days']) && in_array($v_day, $day_items[$v_product['id']]['hosp_adm_days']) && $v_product['hospiz_dis_hosp_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
											$day_items[$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
											//remove from array if verified
											unset($day_items[$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$v_product['id']]['hospiz_dis_days'])]);
											$day_items[$v_product['id']]['hospiz_dis'] -= 1;
											$day_items[$v_product['id']]['hosp_adm'] -= 1;
				
											if(count($day_items[$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$v_product['id']]['hospiz_dis'] < '0')
											{
												unset($day_items[$v_product['id']]['hospiz_dis_days']);
												unset($day_items[$v_product['id']]['hospiz_dis']);
											}
				
											unset($day_items[$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$v_product['id']]['hosp_adm_days'])]);
											if(count($day_items[$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$v_product['id']]['hosp_adm'] < '0')
											{
												unset($day_items[$v_product['id']]['hosp_adm_days']);
												unset($day_items[$v_product['id']]['hosp_adm']);
											}
										}
									}
									ksort($day_items[$v_product['id']]['product_all_days']);
								}
								else if($v_product['sapv'] == '0' && !in_array($v_day, $patient_sapv_details['sapv_days'])) //product without sapv
								{
				
									$day_items[$v_product['id']]['grouped'] = $v_product['grouped'];
				
									if($pinfo['isstandby'] == '1')
									{
										if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['standby_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))//standby location day product
										{
											$day_items[$v_product['id']]['standby'] += '1';
											$day_items[$v_product['id']]['standby_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
									else
									{
										if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['normal_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))//normal location day product
										{
											$day_items[$v_product['id']]['normal'] += '1';
											$day_items[$v_product['id']]['normal_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									if(in_array($v_day, $patient_locations_days['locations_days']['hosp'])) //hospital location day product
									{
										if(in_array($v_day, $patient_locations_days['hosp_adm']) && $v_product['hosp_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_adm'] += '1';
											$day_items[$v_product['id']]['hosp_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(in_array($v_day, $patient_locations_days['hosp_dis']) && $v_product['hosp_dis_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_dis'] += '1';
											$day_items[$v_product['id']]['hosp_dis_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(!in_array($v_day, $day_items[$v_product['id']]['hosp_adm_days']) && !in_array($v_day, $day_items[$v_product['id']]['hosp_dis_days'])  && $v_product['hosp_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp'] += '1';
											$day_items[$v_product['id']]['hosp_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									if(in_array($v_day, $patient_locations_days['locations_days']['hospiz'])) //hospiz location day product
									{
										//check if the day is admision or not
										if(in_array($v_day, $patient_locations_days['hospiz_adm']) && $v_product['hospiz_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_adm'] += '1';
											$day_items[$v_product['id']]['hospiz_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(in_array($v_day, $patient_locations_days['hospiz_dis']) && $v_product['hospiz_dis_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_dis'] += '1';
											$day_items[$v_product['id']]['hospiz_dis_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
				
										if(!in_array($v_day, $day_items[$v_product['id']]['hospiz_adm_days']) && !in_array($v_day, $day_items[$v_product['id']]['hospiz_dis_days']) && $v_product['hospiz_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz'] += '1';
											$day_items[$v_product['id']]['hospiz_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
										}
									}
				
									//					check if date is in both locations
									if(in_array($v_day, $patient_locations_days['locations_days']['hosp']) && in_array($v_day, $patient_locations_days['locations_days']['hospiz']))
									{
										//						hospital discharge - hospiz admision method
										if(in_array($v_day, $day_items[$v_product['id']]['hosp_dis_days']) && in_array($v_day, $day_items[$v_product['id']]['hospiz_adm_days']) && $v_product['hosp_dis_hospiz_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
											$day_items[$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
											//remove from array if verified
											unset($day_items[$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$v_product['id']]['hosp_dis_days'])]);
											$day_items[$v_product['id']]['hosp_dis'] -= 1;
											$day_items[$v_product['id']]['hospiz_adm'] -= 1;
				
											if(count($day_items[$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$v_product['id']]['hosp_dis'] < '0')
											{
												unset($day_items[$v_product['id']]['hosp_dis_days']);
												unset($day_items[$v_product['id']]['hosp_dis']);
											}
				
											unset($day_items[$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$v_product['id']]['hospiz_adm_days'])]);
											if(count($day_items[$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$v_product['id']]['hospiz_adm'] < '0')
											{
												unset($day_items[$v_product['id']]['hospiz_adm_days']);
												unset($day_items[$v_product['id']]['hospiz_adm']);
											}
										}
				
										//						hospiz discharge - hospital admision method
										if(in_array($v_day, $day_items[$v_product['id']]['hospiz_dis_days']) && in_array($v_day, $day_items[$v_product['id']]['hosp_adm_days']) && $v_product['hospiz_dis_hosp_adm_price'] != '0.00'
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0"))
										{
											$day_items[$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
											$day_items[$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
											$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
											$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
											//remove from array if verified
											unset($day_items[$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$v_product['id']]['hospiz_dis_days'])]);
											$day_items[$v_product['id']]['hospiz_dis'] -= 1;
											$day_items[$v_product['id']]['hosp_adm'] -= 1;
				
											if(count($day_items[$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$v_product['id']]['hospiz_dis'] < '0')
											{
												unset($day_items[$v_product['id']]['hospiz_dis_days']);
												unset($day_items[$v_product['id']]['hospiz_dis']);
											}
				
											unset($day_items[$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$v_product['id']]['hosp_adm_days'])]);
											if(count($day_items[$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$v_product['id']]['hosp_adm'] < '0')
											{
												unset($day_items[$v_product['id']]['hosp_adm_days']);
												unset($day_items[$v_product['id']]['hosp_adm']);
											}
										}
									}
									ksort($day_items[$v_product['id']]['product_all_days']);
								}
								if(empty($day_items[$v_product['id']]['product_all_days']))
								{
									unset($day_items[$v_product['id']]);
								}
							}
						}
					}
				}
					
				//assigned users get all products if they belongs to the product group
				foreach($assignedusers as $k_usr => $v_usr_id)
				{
					foreach($day_products as $k_prod => $v_prod)
					{
						if(in_array($v_usr_id, $groups_users[$v_prod['usergroup']]) && array_key_exists($v_prod['id'], $day_items))
						{
							if(!empty($users_ids_associated[$v_usr_id]))
							{
								$master_items[$users_ids_associated[$v_usr_id]][$v_prod['id']] = $day_items[$v_prod['id']];
							}
							else
							{
								$master_items[$v_usr_id][$v_prod['id']] = $day_items[$v_prod['id']];
							}
						}
					}
				}	
			}
			else
			{ // NEW  version with vacation replacements

				$master_items = array();
				$day_items = array();
				$current_date_user = "";
				
				foreach($patient_treatment_days[$ipid]['normal'] as $key => $v_day)
				{
					foreach($day_products as $k_product => $v_product)
					{
						foreach($assignedusers as $k_usr => $v_usr_id)
						{
							if(in_array($v_usr_id, $groups_users[$v_product['usergroup']]) && in_array($v_day, $pricelist_days[$v_product['list']]) )
							{
								// get replacement users check them first
								if($vac_days_rpl_user2user[$v_usr_id][$ipid][$v_day]){
									$v_usr_id = $vac_days_rpl_user2user[$v_usr_id][$ipid][$v_day];
								}
				
								if(!empty($users_ids_associated[$v_usr_id]))
								{
									$current_date_user = $users_ids_associated[$v_usr_id];
								}
								else
								{
									$current_date_user = $v_usr_id;
								}
				
				
								if($v_product['holiday'] == '1')
								{
									$check_holiday = true;
								}
								else
								{
									$check_holiday = false;
								}
				
								//holiday debug
								//LE: removed allready invoiced days
								if(($check_holiday 	&& (in_array($v_day, $national_holidays) || date('w', strtotime($v_day)) == '0' || date('w', strtotime($v_day)) == '6'))
										|| (!$check_holiday && !in_array($v_day, $national_holidays) && date('w', strtotime($v_day)) != '0' && date('w', strtotime($v_day)) != '6'))
								{
									$day_items[$current_date_user][$v_product['id']]['pricelist_days'][$v_day] = $v_product['list'];
									if($v_product['sapv'] != '0' && in_array($v_day, $patient_sapv_details[$vv_status2days[$v_product['sapv']]])) //sapv product and patient treatment day is having product sapv
									{
										$day_items[$current_date_user][$v_product['id']]['grouped'] = $v_product['grouped'];
				
										if($pinfo['isstandby'] == '1')
										{
											if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['standby_price'] != '0.00'
													&& !in_array($v_day, $previous_items_arr[$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
														
													&& 	!in_array($v_day,$day_items[$current_date_user][$v_product['id']]['standby_days']) ) //standby location day product
											{
												$day_items[$current_date_user][$v_product['id']]['standby'] += '1';
												$day_items[$current_date_user][$v_product['id']]['standby_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
										else
										{
				
											if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['normal_price'] != '0.00'
													&& !in_array($v_day, $previous_items_arr[$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
													&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['normal_days']))//normal location day product
											{
												$day_items[$current_date_user][$v_product['id']]['normal'] += '1';
												$day_items[$current_date_user][$v_product['id']]['normal_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
				
										// ---------------------------------
                                        // hospital location day product
										// ---------------------------------
										if( in_array($v_day, $patient_locations_days['locations_days']['hosp']) && !in_array($v_day, $patient_locations_days['locations_days']['hospiz'])) 
										{
										    // Hospital admission days
											if(    $v_product['hosp_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_adm']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_adm_days'])
											)
											{
												$day_items[$current_date_user][$v_product['id']]['hosp_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
											

											// Hospital discharge days											
											if(  $v_product['hosp_dis_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_dis']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_dis_days'])
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hosp_dis'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_dis_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}

											
											// Hospital Full days
											if(  
											    $v_product['hosp_price'] != '0.00'
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_adm_days']) 
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_dis_days']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_days'])
											)
											{
												$day_items[$current_date_user][$v_product['id']]['hosp'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}

										
										
										
										// ---------------------------------
										// Hospiz location day product
										// ---------------------------------
										if( in_array($v_day, $patient_locations_days['locations_days']['hospiz']) && !in_array($v_day, $patient_locations_days['locations_days']['hosp']) ) 
										{
											// Hospiz admission days 
											if(    $v_product['hospiz_adm_price'] != '0.00' 
											    && in_array($v_day, $patient_locations_days['hospiz_adm']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'])
											    
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
											
											
											// Hospiz discharge days 
											if(    $v_product['hospiz_dis_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hospiz_dis']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'])
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
				
											
											
											// Hospiz FULL days 
											if(    $v_product['hospiz_price'] != '0.00'
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']) 
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_days'])
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
				
										
										// ---------------------------------
										// Hospiz AND Hospital  location  - check if date is in both locations
										// ---------------------------------
										if(in_array($v_day, $patient_locations_days['locations_days']['hosp']) && in_array($v_day, $patient_locations_days['locations_days']['hospiz']))
										{
											// hospital discharge - hospiz admision method
											if(   $v_product['hosp_dis_hospiz_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_dis'])
											    && in_array($v_day, $patient_locations_days['hospiz_adm'])
											    && !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_hospiz_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm_days']) 
											    )
											{
											    $day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
											    $day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
											    
											    $day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
											    $day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;

												//remove from array if verified
							/* 					unset($day_items[$current_date_user][$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_dis_days'])]);
				
												$day_items[$current_date_user][$v_product['id']]['hosp_dis'] -= 1;
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm'] -= 1;
				
												if(count($day_items[$current_date_user][$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hosp_dis'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hosp_dis_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hosp_dis']);
												}
				
												unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'])]);
				
												if(count($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hospiz_adm'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm']);
												} */

											}
				
											
											
											
											//hospiz discharge - hospital admision method
											if(    $v_product['hospiz_dis_hosp_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hospiz_dis'])
											    && in_array($v_day, $patient_locations_days['hosp_adm'])
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_hosp_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm_days']) )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
												//remove from array if verified
												/* unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'])]);
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis'] -= 1;
												$day_items[$current_date_user][$v_product['id']]['hosp_adm'] -= 1;
				
												if(count($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hospiz_dis'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis']);
												}
				
												unset($day_items[$current_date_user][$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_adm_days'])]);
												if(count($day_items[$current_date_user][$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hosp_adm'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hosp_adm_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hosp_adm']);
												} */
											}
										}
										ksort($day_items[$current_date_user][$v_product['id']]['product_all_days']);
									}
									else if($v_product['sapv'] == '0' && !in_array($v_day, $patient_sapv_details['sapv_days'])) //product without sapv
									{
				
										$day_items[$current_date_user][$v_product['id']]['grouped'] = $v_product['grouped'];
				
										if($pinfo['isstandby'] == '1')
										{
											if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['standby_price'] != '0.00'
													&& !in_array($v_day, $previous_items_arr[$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
													&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['standby_days'])
											)//standby location day product
											{
												$day_items[$current_date_user][$v_product['id']]['standby'] += '1';
												$day_items[$current_date_user][$v_product['id']]['standby_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
										else
										{
											if(in_array($v_day, $patient_locations_days['locations_days']['normal']) && $v_product['normal_price'] != '0.00'
													&& !in_array($v_day, $previous_items_arr[$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
													&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['normal_days'])
											)//normal location day product
											{
												$day_items[$current_date_user][$v_product['id']]['normal'] += '1';
												$day_items[$current_date_user][$v_product['id']]['normal_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}

										// ---------------------------------
										// Hospital location day product  [without sapv]
										// ---------------------------------
										if(in_array($v_day, $patient_locations_days['locations_days']['hosp']) && !in_array($v_day, $patient_locations_days['locations_days']['hospiz']))
										{
										    // Hospital admission days [without sapv]
											if(    $v_product['hosp_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_adm']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_adm_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hosp_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
											
											// Hospital discharge days [without sapv]				
											if(    $v_product['hosp_dis_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_dis']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_dis_days'])  
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hosp_dis'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_dis_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}

											
											// Hospital FULL days [without sapv]
											if(    $v_product['hosp_price'] != '0.00'
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_adm_days']) 
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_dis_days'])  
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hosp'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
										
										

										// ---------------------------------
										// Hospiz location day product [without sapv]
										// ---------------------------------
										if(in_array($v_day, $patient_locations_days['locations_days']['hospiz']) && !in_array($v_day, $patient_locations_days['locations_days']['hosp'])) 
										{
											//Hospiz admission days [without sapv] 
											if(  
											    $v_product['hospiz_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hospiz_adm']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'])
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}

											
											//Hospiz discharge  days [without sapv]											
											if(    $v_product['hospiz_dis_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hospiz_dis']) 
                                                && !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
				
											
											//Hospiz FULL  days	 [without sapv]										
											if(	   $v_product['hospiz_price'] != '0.00'
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']) 
											    && !in_array($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']) 
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
											}
										}
				
										
										
										
										// ---------------------------------
										// Hospiz AND Hospital  location  - check if date is in both locations  [without sapv]			
										// ---------------------------------
										if(in_array($v_day, $patient_locations_days['locations_days']['hosp']) && in_array($v_day, $patient_locations_days['locations_days']['hospiz']))
										{
											//	Hospital discharge - hospiz admision method [without sapv]
											if(    $v_product['hosp_dis_hospiz_adm_price'] != '0.00'
											    && in_array($v_day, $patient_locations_days['hosp_dis'])
											    && in_array($v_day, $patient_locations_days['hospiz_adm'])
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hosp_dis_hospiz_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
												//remove from array if verified
												/* unset($day_items[$current_date_user][$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_dis_days'])]);
												$day_items[$current_date_user][$v_product['id']]['hosp_dis'] -= 1;
												$day_items[$current_date_user][$v_product['id']]['hospiz_adm'] -= 1;
				
												if(count($day_items[$current_date_user][$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hosp_dis'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hosp_dis_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hosp_dis']);
												}
				
												unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_adm_days'])]);
												if(count($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hospiz_adm'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_adm']);
												} */
											}
				
											
											//Hospiz discharge - hospital admision method [without sapv]
											if(    $v_product['hospiz_dis_hosp_adm_price'] != '0.00'   
											    && in_array($v_day, $patient_locations_days['hospiz_dis'])
											    && in_array($v_day, $patient_locations_days['hosp_adm'])
												&& !in_array($v_day, $previous_items_arr[$v_product['id']]['hospiz_dis_hosp_adm_days']) 
											    && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr['grouped_days'])) || $v_product['grouped'] == "0")
												&& !in_array($v_day,$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm_days']) 
											    )
											{
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
												$day_items[$current_date_user][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
												$day_items[$current_date_user][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
				
												//remove from array if verified
											/* 	unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hospiz_dis_days'])]);
												$day_items[$current_date_user][$v_product['id']]['hospiz_dis'] -= 1;
												$day_items[$current_date_user][$v_product['id']]['hosp_adm'] -= 1;
				
												if(count($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hospiz_dis'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hospiz_dis']);
												}
				
												unset($day_items[$current_date_user][$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$current_date_user][$v_product['id']]['hosp_adm_days'])]);
												if(count($day_items[$current_date_user][$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$current_date_user][$v_product['id']]['hosp_adm'] < '0')
												{
													unset($day_items[$current_date_user][$v_product['id']]['hosp_adm_days']);
													unset($day_items[$current_date_user][$v_product['id']]['hosp_adm']);
												} */
											}
										}
										ksort($day_items[$current_date_user][$v_product['id']]['product_all_days']);
									}
									if(empty($day_items[$current_date_user][$v_product['id']]['product_all_days']))
									{
										unset($day_items[$current_date_user][$v_product['id']]);
									}
								}
							}
						}
					}
				}
				
				$master_items = $day_items;
				
			}
			
			return $master_items;
		}

		public function get_kvno_nurse_form($ipid = false, $excluded = false, $period = false)
		{
		    $select = Doctrine_Query::create()
		    ->select('*,vizit_date as date')
		    ->from('KvnoNurse')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('isdelete="0"');
		    if($excluded)
		    {
		        $select->andWhereNotIn('id', $excluded);
		    }
		    if($period)
		    {
		        $select->andWhere('DATE(vizit_date) BETWEEN "' . $period['start'] . '" and "' . $period['end'] . '"');
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		    if($select_res)
		    {
		        $cf_ids[] = '99999999999';
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['id']] = $v_cf;
		            $cf_ids[] = $v_cf['id'];
		        }
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		public function get_kvno_doctor_form($ipid = false, $excluded = false, $period = false)
		{
		    $select = Doctrine_Query::create()
		    ->select('*,vizit_date as date')
		    ->from('KvnoDoctor')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('isdelete="0"');
		    if($excluded)
		    {
		        $select->andWhereNotIn('id', $excluded);
		    }
		    if($period)
		    {
		        $select->andWhere('DATE(vizit_date) BETWEEN "' . $period['start'] . '" and "' . $period['end'] . '"');
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		    if($select_res)
		    {
		        $cf_ids[] = '99999999999';
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['id']] = $v_cf;
		            $cf_ids[] = $v_cf['id'];
		        }
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		public function get_visit_koordination_form($ipid = false, $excluded = false, $period = false)
		{
		    $select = Doctrine_Query::create()
		    ->select('*,visit_date as date')
		    ->from('VisitKoordination')
		    ->where('ipid LIKE "' . $ipid . '"');
		    if($excluded)
		    {
		        $select->andWhereNotIn('id', $excluded);
		    }
		    if($period)
		    {
		        $select->andWhere('DATE(visit_date) BETWEEN "' . $period['start'] . '" and "' . $period['end'] . '"');
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		    if($select_res)
		    {
		        $cf_ids[] = '99999999999';
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['id']] = $v_cf;
		            $cf_ids[] = $v_cf['id'];
		        }
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		
		
		public function get_bayern_doctorvisit($ipid = false, $excluded = false, $period = false)
		{
		    $select = Doctrine_Query::create()
		    ->select('*,visit_date as date')
		    ->from('BayernDoctorVisit')
		    ->where('ipid LIKE "' . $ipid . '"');
		    if($excluded)
		    {
		        $select->andWhereNotIn('id', $excluded);
		    }
		    if($period)
		    {
		        $select->andWhere('DATE(visit_date) BETWEEN "' . $period['start'] . '" and "' . $period['end'] . '"');
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		    if($select_res)
		    {
		        $cf_ids[] = '99999999999';
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['id']] = $v_cf;
		            $cf_ids[] = $v_cf['id'];
		        }
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		
		
		private function get_patient_all_sapv($ipid)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $patientmaster = new PatientMaster();
		
		
		    $dropSapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->where('ipid LIKE "' . $ipid . '"')
		    ->andWhere('verordnungbis !="000-00-00 00:00:00" ')
		    ->andWhere('verordnungam !="000-00-00 00:00:00" ')
		    ->andWhere('isdelete=0')
		    // 				->andWhere('status != 1 ')
		    ->orderBy('verordnungam ASC');
		    $sapv_array = $dropSapv->fetchArray();
		
		    	
		    $fully_inactive = array();
		    foreach($sapv_array as $sapv_denied_key => $sapvvalue_denied)
		    {
		        if($sapvvalue_denied['status'] == "1" && ($sapvvalue_denied['verorddisabledate'] == "0000-00-00 00:00:00" || $sapvvalue_denied['verorddisabledate'] == "1970-01-01 00:00:00"  )){
		            $fully_inactive[] =  $sapvvalue_denied['id'];
		        }
		         
		        if($sapvvalue_denied['status'] == "1" && $sapvvalue_denied['verorddisabledate'] != "0000-00-00 00:00:00" && $sapvvalue_denied['verorddisabledate'] != "1970-01-01 00:00:00"){
		            $sapv_start[$sapvvalue_denied['id']] = strtotime(date('Y-m-d', strtotime($sapvvalue_denied['verordnungam'])));
		            $sapv_disabled_start[$sapvvalue_denied['id']] = strtotime(date('Y-m-d', strtotime($sapvvalue_denied['verorddisabledate'])));
		            if($sapv_disabled_start[$sapvvalue_denied['id']] < $sapv_start[$sapvvalue_denied['id']]){
		                array_push($fully_inactive,$sapvvalue_denied['id']); // if disable date is set befor star date
		            }
		        }
		    }
		    	
		    $s = 1;
		    $sapv['sapv_intervals'] = array();
		    foreach($sapv_array as $sapvkey => $sapvvalue)
		    {
		        if(!in_array($sapvvalue['id'],$fully_inactive)){ // if not fully inactive
		
		            if($sapvvalue['status'] == "1" && ($sapvvalue['verorddisabledate'] != "0000-00-00 00:00:00" && $sapvvalue['verorddisabledate'] != "1970-01-01 00:00:00"  )){
		                // inactive until - disabled date
		                $sapv_start[$sapvvalue['id']] = strtotime( date('Y-m-d', strtotime($sapvvalue['verordnungam'])));
		                $sapv_end[$sapvvalue['id']] = strtotime(date('Y-m-d', strtotime($sapvvalue['verordnungbis'])));
		
		                $sapv_disabled_start[$sapvvalue['id']] = strtotime(date('Y-m-d', strtotime($sapvvalue['verorddisabledate'])));
		                $sapv_disabled_end[$sapvvalue['id']] = strtotime(date('Y-m-d', strtotime($sapvvalue['verorddisabledate'])));
		                if(Pms_CommonData::isintersected($sapv_start[$sapvvalue['id']], $sapv_end[$sapvvalue['id']],  $sapv_disabled_start[$sapvvalue['id']], $sapv_disabled_end[$sapvvalue['id']])){
		                    $sapvvalue['verordnungbis'] = $sapvvalue['verorddisabledate'];
		                }
		            }
		
		            $sapv['patient_sapv'][$sapvvalue['id']]['all_types'] = explode(',', $sapvvalue['verordnet']);
		
		            $sapv['patient_sapv'][$sapvvalue['id']]['type'] = $sapvvalue['verordnet'];
		            $sapv['patient_sapv'][$sapvvalue['id']]['from'] = $sapvvalue['verordnungam'];
		            $sapv['patient_sapv'][$sapvvalue['id']]['till'] = $sapvvalue['verordnungbis'];
		
		
		            $sapv['sapv_start_days'][] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
		
		            $sapv['sapv_intervals'][$s]['start'] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
		            $sapv['sapv_intervals'][$s]['end'] = date('Y-m-d', strtotime($sapvvalue['verordnungbis']));
		
		            $patient_active_sapv[] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
		
		
		            if(in_array('1', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('2', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
		            {
		                $sapv_details['be_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
		            }
		
		            if(in_array('2', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
		            {
		                $sapv_details['ko_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
		            }
		
		            if(in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
		            {
		                $sapv_details['tv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
		            }
		            if(in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
		            {
		                $sapv_details['vv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
		            }
		
		            $s++;
		
		        }
		         
		    }
		    asort($sapv['sapv_start_days']);
		
		    foreach($sapv_details['be_days'] as $kbes => $be_intervals)
		    {
		        foreach($be_intervals as $be_days)
		        {
		            $sapv['be_days'][] = $be_days;
		        }
		    }
		
		    foreach($sapv_details['ko_days'] as $kkos => $ko_intervals)
		    {
		        foreach($ko_intervals as $ko_days)
		        {
		            $sapv['ko_days'][] = $ko_days;
		        }
		    }
		    asort($sapv['tv_days']);
		
		    foreach($sapv_details['tv_days'] as $ktvs => $tv_intervals)
		    {
		        foreach($tv_intervals as $tv_days)
		        {
		            $sapv['tv_days'][] = $tv_days;
		        }
		    }
		    asort($sapv['tv_days']);
		    $sapv['tv_days'] = array_unique($sapv['tv_days']);
		
		
		
		    foreach($sapv_details['vv_days'] as $kvvs => $vv_intervals)
		    {
		        foreach($vv_intervals as $vv_days)
		        {
		            $sapv['vv_days'][] = $vv_days;
		        }
		    }
		    asort($sapv['vv_days']);
		    $sapv['vv_days'] = array_unique($sapv['vv_days']);
		
		
		
		    foreach($patient_active_sapv as $sinter => $sinterval_days)
		    {
		        foreach($sinterval_days as $sdays)
		        {
		            $sapv['sapv_days_overall'][] = $sdays;
		        }
		    }
		    asort($sapv['sapv_days_overall']);
		    $sapv['sapv_days'] = array_unique($sapv['sapv_days_overall']);
		
		    return $sapv;
		}
		
		private function get_patient_all_sapv_151020($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$patientmaster = new PatientMaster();


			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('verordnungbis !="000-00-00 00:00:00" ')
				->andWhere('verordnungam !="000-00-00 00:00:00" ')
				->andWhere('isdelete=0')
				->andWhere('status != 1 ')
				->orderBy('verordnungam ASC');
			$sapv_array = $dropSapv->fetchArray();


			$s = 1;
			$sapv['sapv_intervals'] = array();
			foreach($sapv_array as $sapvkey => $sapvvalue)
			{

				$sapv['patient_sapv'][$sapvvalue['id']]['all_types'] = explode(',', $sapvvalue['verordnet']);

				$sapv['patient_sapv'][$sapvvalue['id']]['type'] = $sapvvalue['verordnet'];
				$sapv['patient_sapv'][$sapvvalue['id']]['from'] = $sapvvalue['verordnungam'];
				$sapv['patient_sapv'][$sapvvalue['id']]['till'] = $sapvvalue['verordnungbis'];

				$sapv['sapv_start_days'][] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));

				$sapv['sapv_intervals'][$s]['start'] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
				$sapv['sapv_intervals'][$s]['end'] = date('Y-m-d', strtotime($sapvvalue['verordnungbis']));

				$patient_active_sapv[] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);


				if(in_array('1', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('2', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['be_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}

				if(in_array('2', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['ko_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}

				if(in_array('3', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['tv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}
				if(in_array('4', $sapv['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details['vv_days'][] = $patientmaster->getDaysInBetween($sapv['sapv_intervals'][$s]['start'], $sapv['sapv_intervals'][$s]['end']);
				}



				$s++;
			}
			asort($sapv['sapv_start_days']);

			foreach($sapv_details['be_days'] as $kbes => $be_intervals)
			{
				foreach($be_intervals as $be_days)
				{
					$sapv['be_days'][] = $be_days;
				}
			}

			foreach($sapv_details['ko_days'] as $kkos => $ko_intervals)
			{
				foreach($ko_intervals as $ko_days)
				{
					$sapv['ko_days'][] = $ko_days;
				}
			}
			asort($sapv['tv_days']);

			foreach($sapv_details['tv_days'] as $ktvs => $tv_intervals)
			{
				foreach($tv_intervals as $tv_days)
				{
					$sapv['tv_days'][] = $tv_days;
				}
			}
			asort($sapv['tv_days']);
			$sapv['tv_days'] = array_unique($sapv['tv_days']);



			foreach($sapv_details['vv_days'] as $kvvs => $vv_intervals)
			{
				foreach($vv_intervals as $vv_days)
				{
					$sapv['vv_days'][] = $vv_days;
				}
			}
			asort($sapv['vv_days']);
			$sapv['vv_days'] = array_unique($sapv['vv_days']);



			foreach($patient_active_sapv as $sinter => $sinterval_days)
			{
				foreach($sinterval_days as $sdays)
				{
					$sapv['sapv_days_overall'][] = $sdays;
				}
			}
			asort($sapv['sapv_days_overall']);
			$sapv['sapv_days'] = array_unique($sapv['sapv_days_overall']);

			return $sapv;
		}

		private function get_patient_locations_days($ipid, $clientid)
		{
			$pl = new PatientLocation();
			$pm = new PatientMaster();


			//get client locations
			$c_locations = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where('client_id ="' . $clientid . '"');
				//->where('isdelete = 0')//ISPC-2612 Ancuta 27.06.2020 Locx
			$c_locations_array = $c_locations->fetchArray();

			foreach($c_locations_array as $k_loc => $v_loc)
			{
				$master_locations[$v_loc['id']]['type'] = $v_loc['location_type'];
				$master_locations[$v_loc['id']]['location_name'] = $v_loc['location'];
			}

			//get patient locations
			$patient_locations = $pl->get_valid_patient_locations($ipid);

			foreach($patient_locations as $k_patient_loc => $v_patient_loc)
			{
				$start = date('Y-m-d', strtotime($v_patient_loc['valid_from']));

				if($v_patient_loc['valid_till'] != '0000-00-00 00:00:00')
				{
					$end = date('Y-m-d', strtotime($v_patient_loc['valid_till']));
				}
				else
				{
					$end = date('Y-m-d', time());
				}

				if($master_locations[$v_patient_loc['location_id']]['type'] == '1') //hospital
				{
					if(empty($pat_days['hosp']))
					{
						$pat_days['hosp'] = array();
					}

					$pat_days['hosp'] = array_merge($pat_days['hosp'], $pm->getDaysInBetween($start, $end));

					$pat_locations['hosp_adm'][] = $start;
					$pat_locations['hosp_dis'][] = $end;
				}
				else if($master_locations[$v_patient_loc['location_id']]['type'] == '2') //hospiz
				{
					if(empty($pat_days['hospiz']))
					{
						$pat_days['hospiz'] = array();
					}

					$pat_days['hospiz'] = array_merge($pat_days['hospiz'], $pm->getDaysInBetween($start, $end));
					$pat_locations['hospiz_adm'][] = $start;
					$pat_locations['hospiz_dis'][] = $end;
				}
				else
				{
					if(empty($pat_days['normal']))
					{
						$pat_days['normal'] = array();
					}

					$pat_days['normal'] = array_merge($pat_days['normal'], $pm->getDaysInBetween($start, $end));
					$pat_locations['normal_adm'][] = $start;
					$pat_locations['normal_dis'][] = $end;
				}

				if(empty($pat_locations['all_locations_days']))
				{
					$pat_locations['all_locations_days'] = array();
				}

				$pat_locations['all_locations_days'] = array_merge($pat_locations['all_locations_days'], $pm->getDaysInBetween($start, $end));

				//uncomment to transfer original patient locations to main function
				//$pat_locations['locations'][] = $v_patient_loc;
			}

			$pat_locations['locations_days'] = $pat_days;

			return $pat_locations;
		}

		public function invoicesAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$internal_invoices = new InternalInvoices();
			$internal_invoices_form = new Application_Form_InternalInvoices();


			//ISPC-2609 Ancuta 24.09.2020
			//get printjobs - active or completed - for client, user and invoice type
			$allowed_invoice_name =  "internal_invoice";
			$userid = $logininfo->userid;
			$this->view->allowed_invoice = $allowed_invoice_name;
			$invoice_user_printjobs = PrintJobsBulkTable::_find_invoices_print_jobs($clientid,$userid,$allowed_invoice_name );
			
			$print_html = '<div class="print_jobs_div">';
			$print_html .= "<h3> ".$this->view->translate('print_job_table_headline')."</h3>";
			$print_html .= '<span id="clear_user_jobs" class="clear_user_jobs" data-user="'.$userid.'"  data-invoice_type="'.$allowed_invoice_name .'" data-client="'.$clientid.'"> '.$this->view->translate('Clear_all_prints')."</span>";
			$table_html = $this->view->tabulate($invoice_user_printjobs,array("class"=>"datatable",'id'=>'print_jobs_table','escaped'=>false));
			$print_html .= $table_html;
			$print_html .= '</div>';
			
			
			$this->view->print_html = '';
			if(count($invoice_user_printjobs) > 1 ){
			    //echo $print_html;
			    $this->view->print_html = $print_html;
			}
			
			$this->view->show_print_jobs = $this->user_print_jobs;
			
			//---
			
			if($this->getRequest()->isPost())
			{

				if($_POST['draftmore'] == "1")
				{
					$transform = $internal_invoices_form->ToggleStatusInvoices($_POST['document'], "2", $clientid);
				}
				elseif($_POST['delmore'] == "1" || $_POST['deletemore'] == "1")
				{
					$del_invoice = $internal_invoices_form->delete_multiple_invoices($_POST['document']);
				}
				//ISPC-2609 + ISPC-2000 Ancuta 22.09.2020
				elseif($_POST['batch_print_more'])
				{
				    $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
				    $params['batch_print'] = '1'; //enables batch print procedure
				    $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
				    $params['get_pdf'] = '0'; //stops downloading single pdf
				    
				    //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
				    if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
				        
				        
				        
				    } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
				        
				        $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
				        
				        $print_job_data = array();
				        $print_job_data['clientid'] = $clientid;
				        $print_job_data['user'] = $userid;
				        $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
				        $print_job_data['output_type'] = 'pdf';
				        $print_job_data['status'] = 'active';
				        $print_job_data['invoice_type'] = 'internal_invoice';
				        $print_job_data['print_params'] = serialize($params);
				        $print_job_data['print_function'] = 'editinvoiceAction';
				        $print_job_data['print_controller'] = "internalinvoice";
				        
				        foreach($_POST['document'] as $k=>$inv_id){
				            $print_job_data['PrintJobsItems'][] = array(
				                'clientid'=>$print_job_data['clientid'],
				                'user'=>$print_job_data['user'],
				                'invoice_id'=>$inv_id,
				                'invoice_type'=>$print_job_data['invoice_type'],
				                'status'=>"new"
				            );
				        }
				        
				        $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
				        $print_id = $PrintJobsBulk_obj->id;
				        
				        if($print_id){
				            $this->__StartPrintJobs();
				        }
				    }
				    
				}
				else
				{
					$new_payment = $internal_invoices_form->submit_payment($_POST);
				}
				
				//ISPC-2609 + ISPC-2000 Ancuta 22.09.2020
				$msg="";
				if($print_id){
				    $msg = '?flg=suc&msg=inform_print_job_created&jobid='.$print_id;
				}
				
				$this->_redirect(APP_BASE . 'internalinvoice/invoices'.$msg); //to avoid resubmission
				exit;
			}

			if($_REQUEST['mode'] == 'setstorno')
			{
				if(is_numeric($_REQUEST['inv_id']) && strlen($_REQUEST['inv_id']) > '0')
				{
					$invoiceid = $_REQUEST['inv_id'];
				}
				else
				{
					$invoiceid = '0';
				}

				if($invoiceid > '0')
				{
					$clone_record = $internal_invoices->create_storno_invoice($invoiceid);
					$this->_redirect(APP_BASE . 'internalinvoice/invoices?flg=suc');
					exit;
				}
			}

			if($_REQUEST['mode'] == 'delete' && $_REQUEST['invoiceid'])
			{
				$delete_invoice = $internal_invoices_form->delete_invoice($_REQUEST['invoiceid']);

				if($delete_invoice)
				{
					$this->_redirect(APP_BASE . 'internalinvoice/invoices?flg=delsuc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'internalinvoice/invoices?flg=delerr');
				}
			}


			//construct months array
			$start_period = '2010-01-01';
			$end_period = date('Y-m-d', time());
			$period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
			$month_select_array['99999999'] = '';
			foreach($period_months_array as $k_month => $v_month)
			{
				$month_select_array[$v_month] = $v_month;
			}

			//see how many days in selected month
			$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));

			if(!function_exists('cal_days_in_month'))
			{
				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
			}
			else
			{
				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
			}

			//construct selected month array (start, days, end)
			$months_details[$selected_month]['start'] = $selected_month . "-01";
			$months_details[$selected_month]['days_in_month'] = $month_days;
			$months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;

			krsort($month_select_array);

			$this->view->months_selector = $this->view->formSelect("selected_month", '', null, $month_select_array);



			/* --------------- Get all invoiced users ----------------------- */
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client='" . $clientid . "' ");
			$invoice_all = $invoices->fetchArray();
			foreach($invoice_all as $k => $inv_details)
			{
				$invoiced_users[] = $inv_details['user'];
			}
			$invoiced_users = array_unique($invoiced_users);
			$users = new User();
			$users_details = $users->getMultipleUserDetails($invoiced_users);

			$user_data['0'] = "";
			foreach($users_details as $user_id => $details)
			{
				$user_data[$user_id] = $details['last_name'] . ' ' . $details['first_name'];
			}

			$this->view->user_selector = $this->view->formSelect("invoice_user", '', null, $user_data);
		}

		public function fetchinvoicelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->_helper->layout->setLayout('layout_ajax');
			$hidemagic = Zend_Registry::get('hidemagic');
			$internal_invoices_payments = new InternalInvoicePayments();
			$users = new User();

			$limit = 50;
			$this->view->limit = $limit;
			$filters = array();
			
			
			$storno_invoices_q = Doctrine_Query::create()
			->select("*")
			->from('InternalInvoices')
			->where('client = "'.$clientid.'"  ')
			->andWhere('storno = 1')
			->andWhere('isdelete = 0');
			$storno_invoices_array = $storno_invoices_q->fetchArray();
			
			$storno_ids_str = '"XXXXXX",';
			foreach($storno_invoices_array as $k=>$st)
			{
				$storno_ids[] = $st['record_id'];
				$storno_ids_str .= '"'.$st['record_id'].'",';
			}
				
			if(empty($storno_ids)){
				$storno_ids[] = "XXXXXXX";
			}
				
			$storno_ids_str = substr($storno_ids_str,0,-1);
			

			// get client data
			$client_details_m = new Client();
			$client_details = $client_details_m->getClientDataByid($clientid);
			
			$client_invoice_due_days = $client_details[0]['invoice_due_days'];
			$plus_due_days = '+' . $invoice_due_days . ' days';
			$this->view->plus_due_days = $plus_due_days;
			
 
			$invoice_settings = array();
			$invoice_settings = InternalInvoiceSettings::get_users_InternalInvoiceSettings($clientid,array(),$client_invoice_due_days);
			
			$plus_due_days = array();
			if(!empty($invoice_settings)){
			    foreach($invoice_settings as $uid=>$uinv_sett){
        			$plus_due_days[$uid] = '+' . $uinv_sett['invoice_pay_days']. ' days';
			    }
			}
// 			$plus_due_days = '+' . $invoice_due_days . ' days';
// 			dd($plus_due_days);
			
			// calculate for all invoice is are overdue - and retrive ids - to add in filter
			$invoices_overdue_calculation = Doctrine_Query::create()
			->select("user,completed_date,status,isdelete,storno")
			->from('InternalInvoices INDEXBY id')
			->where("client=?",$clientid)
			->andWhere('status  = 2 OR status = 5 ')
			->fetchArray();
			
			$overdue_ids = array();
			$overdue_ids_str = "";
			foreach($invoices_overdue_calculation as $invoice_id => $ovi)
			{
			    $invoice_pay_due_days[$invoice_id] = $plus_due_days[$ovi['user']];
			    if(
			        ($ovi['status'] == "2" || $ovi['status'] == "5")
			        && $ovi['storno'] == "0"
			        && !in_array($ovi['id'], $storno_ids)
			        && $ovi['isdelete'] == "0"
			        && strtotime(date('Y-m-d', time())) > strtotime(date('Y-m-d', strtotime($plus_due_days[$ovi['user']], strtotime($ovi['completed_date']))))
			    ) {
			        $overdue_ids[] = $invoice_id;
			        $overdue_ids_str .= '"' . $ovi['id'] . '",';
			    }
			}
			$this->view->invoice_pay_due_days = $invoice_pay_due_days;
 
			if( strlen($overdue_ids_str) > 0 )
			{
			    $overdue_ids_str = substr($overdue_ids_str, 0, -1);
			    $overdue_ids_str_sql = " AND id IN (" . $overdue_ids_str . ")";
			} else {
			    $overdue_ids_str_sql = "";
			}
			
			//process tabs
			$filters['hiinvoice_search'] = '';
			switch($_REQUEST['f_status'])
			{
				case 'draft':
					$filters['hiinvoice'] = ' AND status ="1" AND isdelete=0';
					break;

				case 'unpaid':
					$filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN ('.$storno_ids_str.')  AND isdelete = 0 ';

					break;

				case 'paid':
					$filters['hiinvoice'] = ' AND status="3"  AND storno = 0 AND id NOT IN ('.$storno_ids_str.')  AND isdelete=0';
					break;

				case 'deleted':
					$filters['hiinvoice'] = ' AND (status="4" OR isdelete="1") ';
					break;

// 				case 'overdue':
// 					$filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN ('.$storno_ids_str.')  AND DATE(NOW()) > DATE(invoice_end)  AND isdelete=0';
// 					break;
				case 'overdue':
				    $filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0  AND id NOT IN ('.$storno_ids_str.')  '.$overdue_ids_str_sql.'   AND isdelete=0';
				    break;
					

				case 'all':
					$filters['hiinvoice'] = ' ';
					break;

				default: // unpaid- open
					$filters['hiinvoice'] = ' AND (status = "2" OR status = "5")   AND storno = 0 AND id NOT IN ('.$storno_ids_str.') AND isdelete = 0 ';
					break;
			}


			if(!empty($_REQUEST['last_name']))
			{
				$filters['patient_master'] = ' AND (CONCAT(AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['last_name']) . '%")';
			}

			if(!empty($_REQUEST['first_name']))
			{
				$filters['patient_master'] .= ' AND (CONCAT(AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['first_name']) . '%")';
			}

			if(!empty($_REQUEST['epid']))
			{
				$filters['patient_master'] .= ' AND ( e.epid LIKE "%' . addslashes($_REQUEST['epid']) . '%")';
			}

			if(!empty($_REQUEST['rnummer']))
			{
				### !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				###  this should also look for prefix - not only the number
				$filters['hiinvoice'] .= ' AND ( LOWER(CONCAT(`prefix`, CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
				$filters['hiinvoice_search'] .= ' AND ( LOWER(CONCAT(`prefix`, CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
			}


			if(!empty($_REQUEST['invoice_user']))
			{
				$filters['hiinvoice'] .= ' AND ( user = ' . $_REQUEST['invoice_user'] . ')';
				$filters['hiinvoice_search'] .= ' AND ( user = ' . $_REQUEST['invoice_user'] . ')';
			}


			if(!empty($_REQUEST['selected_month']) && $_REQUEST['selected_month'] != '99999999')
			{
				$filters['hiinvoice'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
				$filters['hiinvoice_search'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
			}

			//get invoice patients
			$sql = "p.id,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,
				CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "p.id,e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
			}


			//filter patients name/surname/epid
			$f_patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->where("p.isdelete =0")
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid . $filters['patient_master']);

			$f_patients_res = $f_patient->fetchArray();

			$f_patients_ipids[] = '9999999999999';
			foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
			{
				$f_patients_ipids[] = $v_f_pat_res['EpidIpidMapping']['ipid'];
				$patients_encrypted_ids[$v_f_pat_res['EpidIpidMapping']['ipid']] = Pms_Uuid::encrypt($v_f_pat_res['id']);
			}



			//all invoices for counting
			$invoices_counting = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->whereIn('ipid', $f_patients_ipids);
			$invoices_counting->andWhere("client='" . $clientid . "'" . $filters['hiinvoice_search']);

			$inv2count = $invoices_counting->fetchArray();

			$count_invoices = array();
			
			foreach($inv2count as $k_inv2count => $v_inv2count)
			{
				$count_invoices[$v_inv2count['status']][] = '1';
				
				if($v_inv2count['status'] == "1" && $v_inv2count['isdelete'] == "0"){
					$status_count_invoices["draft"][] = '1';
				}
				
				if( ($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5")  && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'],$storno_ids)  && $v_inv2count['isdelete'] == "0"){
					$status_count_invoices["unpaid"][] = '1';
				}

				if( $v_inv2count['status'] == "3"  && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'],$storno_ids)  && $v_inv2count['isdelete'] == "0"){
					$status_count_invoices["paid"][] = '1';
				}
				
				if( $v_inv2count['status'] == "4" || $v_inv2count['isdelete'] == "1"){
					$status_count_invoices["deleted"][] = '1';
				}
				
				if(
				    ($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") 
				    && $v_inv2count['storno'] == "0" 
				    && !in_array($v_inv2count['id'], $storno_ids) 
				    && $v_inv2count['isdelete'] == "0" 
				    && in_array($v_inv2count['id'],$overdue_ids)
			     ) { 
					$status_count_invoices["overdue"][] = '1';
				}
				$status_count_invoices["all"][] = '1';				
			}


			//deleted_invoices
			$del_invoices_counting = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->whereIn('ipid', $f_patients_ipids);
			$del_invoices_counting->andWhere("client='" . $clientid . "'" . $filters['hiinvoice_search']);
			$del_invoices_counting->andWhere("isdelete=1 or status=4");
			$del_inv2count = $del_invoices_counting->fetchArray();
			foreach($del_inv2count as $k_del_inv => $v_del_inv)
			{
				$counted_del_inv[$v_del_inv['status']][] = '1';
			}


			//filter invoices status/invoice_number/amount
			$invoices_nl = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->whereIn('ipid', $f_patients_ipids)
				->andWhere("client='" . $clientid . "'" . $filters['hiinvoice']);
			$invoices_no_limit = $invoices_nl->fetchArray();
			if($_REQUEST['dng'])
			{
				print_r($invoices_nl->getSqlQuery());
				exit;
			}
			$invoices_no_limit[] = "XXXXXX";

			$invoice_ipids[] = '99999999999999';
			foreach($invoices_no_limit as $k_nl_inv => $v_nl_inv)
			{
				$invoice_ipids[] = $v_nl_inv['ipid'];
			}



			$patient = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn("p.ipid", $f_patients_ipids)
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$patients_res = $patient->fetchArray();

			if($patients_res)
			{
				foreach($patients_res as $k_pat => $v_pat)
				{
					$patient_details[$v_pat['EpidIpidMapping']['ipid']] = $v_pat;
				}
			}

			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
			{
				$current_page = $_REQUEST['page'];
			}
			else
			{
				$current_page = 1;
			}

			if($_REQUEST['sort'] == 'asc')
			{
				$sort = 'asc';
			}
			else
			{
				$sort = 'desc';
			}

			switch($_REQUEST['ord'])
			{

				case 'id':
					$orderby = 'id ' . $sort;
					break;

				case 'ln':
					$orderby = 'epid ' . $sort;
					break;

				case 'nr':
					//$orderby = 'invoice_number ' . $sort;
					$orderby = 'full_invoice_number_sort ' . $sort;
					break;

				case 'invoice_user':
					$orderby = 'user ' . $sort;
					break;

				case 'date':
					$orderby = 'change_date, create_date ' . $sort;
					break;

				case 'amnt':
					$orderby = 'invoice_total ' . $sort;
					break;
				case 'invoice_date':
					$orderby = 'completed_date_sort ' . $sort;
					break;

				default:
                    //InternalInvoices
    	            //$orderby = 'id DESC'; // ISPC-2220: change_order_invoice :: @Ancuta 30.07.2018 [INTERNAL]
    	            $orderby = 'full_invoice_number_sort DESC'; //TODO-2073 ISPC: Invoices sorting not correct :: @Ancuta 22.01.2019
					break;
			}




			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort,concat(prefix,invoice_number) as full_invoice_number_sort")
				->from('InternalInvoices')
				->where("client='" . $clientid . "'" . $filters['hiinvoice'])
				->andwhereIn('ipid', $invoice_ipids);
			$invoices->orderby($orderby);
			$invoices->offset(($current_page - 1) * $limit);
			$invoices->limit($limit);
			$invoicelimit = $invoices->fetchArray();


			$invoice_uids[] = '9999999999';
			foreach($invoicelimit as $k_il => $v_il)
			{
				$invoice_ids[] = $v_il['id'];
				$invoice_uids[] = $v_il['create_user'];
				$invoice_uids[] = $v_il['change_user'];
				$invoice_uids[] = $v_il['user'];
				$invoicelimit[$k_il]['patient_id'] = $patients_encrypted_ids[$v_il['ipid']];
				$invoicelimit[$k_il]['invoiced_user'] = $patients_encrypted_ids[$v_il['ipid']];
			}
			$invoice_uids = array_values(array_unique($invoice_uids));
			$users_details = $users->getMultipleUserDetails($invoice_uids);
			foreach($invoicelimit as $k_il => $v_il)
			{
				$invoicelimit[$k_il]['invoiced_user'] = $users_details[$v_il['user']]['user_title'] . ' ' . $users_details[$v_il['user']]['last_name'] . ', ' . $users_details[$v_il['user']]['first_name'];
			}

			switch($_REQUEST['ord'])
			{

				case 'invoice_user':
					// 				$orderby = 'user ' . $sort;
					$invoicelimit = $this->array_sort($invoicelimit, 'invoiced_user', SORT_ . strtoupper($sort));
					break;
			}
			// 	print_r($invoicelimit); exit;
			
			
			//count tabs contents
			$invoice_tabs = array('unpaid', 'paid', 'draft', 'deleted', 'overdue', 'all');
			
			$counted = array();
			foreach($invoice_tabs as $tab){
				$counted[$tab] += count($status_count_invoices[$tab]);
			}




			$invoice_payments = $internal_invoices_payments->getInvoicesPaymentsSum($invoice_ids);

			$no_invoices = sizeof($invoices_no_limit) - 1; //substract dummy error control result
			$no_pages = ceil($no_invoices / $limit);
			
			
			$this->view->storned_invoices = InternalInvoices::get_storned_invoices($clientid);

			$this->view->invoicelist = $invoicelimit;
			$this->view->user_details = $users_details;
			$this->view->patient_details = $patient_details;
			$this->view->invoice_payments = $invoice_payments;
			$this->view->current_page = $current_page;
			$this->view->no_pages = $no_pages;
			$this->view->no_invoices = $no_invoices;
			$this->view->orderby = $_REQUEST['ord'];
			$this->view->sort = $_REQUEST['sort'];
			$this->view->counted = $counted;
		}

		//ISPC-2609 + ISPC-2000 Ancuta 29.09.2020
		public function editinvoiceAction($params=array())
		{
			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			//$clientid = $logininfo->clientid;
			
			
			if(isset($params) && !empty($params)){
			    $_REQUEST = $params;
			    $_REQUEST['pdf'] = 1;
			    $_REQUEST['mode'] = null;
			    $this->_helper->viewRenderer->setNoRender();
			}
			
			//ISPC-2609 + ISPC-2000 Ancuta 22.09.2020
			$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $logininfo->clientid;
			$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $logininfo->userid;
			
			
			
			$patientmaster = new PatientMaster();
			$client_details = new Client();
			$internal_invoices = new InternalInvoices();
			$internal_invoices_items = new InternalInvoiceItems();
			$internal_invoices_form = new Application_Form_InternalInvoices();
			$hi_perms = new HealthInsurancePermissions();
			$pflege = new PatientMaintainanceStage();
			$phelathinsurance = new PatientHealthInsurance();
			$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
			$boxes = new LettersTextBoxes();
			$letter_boxes_details = $boxes->client_letter_boxes($clientid);
			$allow_deleted = false;
			//get client user details
			$user = new User();
			$client_users_arr = $user->getUserByClientid($clientid, '0', true);

			foreach($client_users_arr as $k_client_user => $v_client_user)
			{
				$client_users[$v_client_user['id']] = $v_client_user;
			}

			
			
			//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
			if($_REQUEST['bulk_print'] == '1' && strlen($_REQUEST['invoiceid']) > 0){
			    $invoice_details = $internal_invoices->getInternalInvoice($_REQUEST['invoiceid']);
			    if($invoice_details['storno'] == '1'){
			        $_REQUEST['invoiceid'] = $invoice_details['record_id'];
			        $_REQUEST['stornopdf'] = 1;
			        $_REQUEST['storno'] = $invoice_details['id'];
			    }
			}
			//--
			
			if($_REQUEST['invoiceid'] && empty($_REQUEST['mode']))
			{
				//here get the invoice details
				$invoice_id = $_REQUEST['invoiceid'];
				if($_REQUEST['storno']>'0')
				{
					$allow_deleted = true;
				}
				//items sorted by first date of period 
				$invoice_data = $internal_invoices->getInternalInvoice($invoice_id, false, $allow_deleted);
				
				if(in_array($invoice_data['id'], InternalInvoices::get_storned_invoices($clientid)))
				{
					$this->view->has_storno = '1';
				}
				else
				{
					$this->view->has_storno = '0';
				}

				//get user details
				$user_details = $client_users[$invoice_data['user']];

				$user_details_str = '';
				
				// ISPC-2220 @Carmen 30.07.2018 -  pct 3) -- added title- Which now title is actualy salutation in user 
				/* if(strlen($user_details['title']) > '0')
				{
					$user_details_str .= $user_details['title'] . ' ';
				} */
				
				// TODO-1730 - @Ancuta 13.08.2018 - change from salutation to TITLE
				if(strlen($user_details['user_title']) > '0')
				{
					$user_details_str .= $user_details['user_title'] . ' ';
				}
				
				if(strlen($user_details['first_name']) > '0')
				{
					$user_details_str .= $user_details['first_name'] . ' ';
				}

				if(strlen($user_details['last_name']) > '0')
				{
					$user_details_str .= $user_details['last_name'];
				}
				$user_details_str .= "<br />";


				if(strlen($user_details['street1']) > '0')
				{
					$user_details_str .= $user_details['street1'];
				}
				$user_details_str .= "<br />";
				if(strlen($user_details['zip']) > '0')
				{
					$user_details_str .= $user_details['zip'] . " ";
				}
				if(strlen($user_details['city']) > '0')
				{
					$user_details_str .= $user_details['city'];
				}
				$user_details_str .= "<br />";
				
				$this->view->user_details = $user_details_str;
				$this->view->control_number = $user_details['control_number'];

				if(!$invoice_id && empty($invoice_data))
				{
					$this->_redirect(APP_BASE . 'internalinvoice/invoices');
				}

				//ISPC-2609 + ISPC-2000 Ancuta 29.09.2020
				if($_REQUEST['bulk_print'] == '1'){
				    $pdet = $patientmaster->get_patients_details_By_Ipids(array($invoice_data['ipid']));
				    $patient_details = $pdet[$invoice_data['ipid']];
				    $patient_details['patienten_id'] = $patient_details['EpidIpidMapping']['epid'];
				    $patient_details['birthd'] = date('d.m.Y',strtotime($patient_details['birthd']));
				}
				else 
				{
    				$patient_details = $patientmaster->getMasterData(Pms_CommonData::getIdfromIpid($invoice_data['ipid']), '0');
				}
		 
				$client_detail = $client_details->getClientDataByid($invoice_data['client']);
				$this->view->client_ik = $client_detail[0]['institutskennzeichen'];

				//health insurance
				$divisions = $hi_perms->getClientHealthInsurancePermissions($invoice_data['client']);
				$hi_perms_divisions = $divisions;

				//health insurance
				$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($invoice_data['ipid']);
				$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

				if(!empty($healthinsu_array[0]['companyid']) && $healthinsu_array[0]['companyid'] != 0)
				{
					$helathins = Doctrine::getTable('HealthInsurance')->find($healthinsu_array[0]['companyid']);
					$healtharray = $helathins->toArray();

					if(empty($healthinsu_array[0]['name']))
					{
						$healthinsu_array[0]['name'] = $healtharray['name'];
					}
					if(empty($healthinsu_array[0]['ins_street']))
					{
						$healthinsu_array[0]['ins_street'] = $healtharray['street1'];
					}
					if(empty($healthinsu_array[0]['ins_zip']))
					{
						$healthinsu_array[0]['ins_zip'] = $healtharray['zip'];
					}

					if(empty($healthinsu_array[0]['ins_city']))
					{
						$healthinsu_array[0]['ins_city'] = $healtharray['city'];
					}

					if(strlen($healthinsu_array[0]['institutskennzeichen']) == 0)
					{
						$this->view->health_insurance_ik = $healtharray['iknumber'];
					}

					if(strlen($healthinsu_array[0]['kvk_no']) == 0)
					{
						$this->view->health_insurance_kassenr = $healtharray['kvnumber'];
					}
				}


				if($hi_perms_divisions)
				{

					$healthinsu_subdiv_arr = $healthinsu_subdiv->get_hi_subdivisions($invoice_data['ipid'], $healthinsu_array[0]['companyid']);
				}

				$pathealthinsurancenr = "";
				if(count($healthinsu_array[0]))
				{
					$phi_details = $healthinsu_array[0]['name'];
				}
				else
				{
					$phi_details = "--";
				}

				$phi_details_sub = $healthinsu_subdiv_arr[3]['street1'] . '<br/> ' . $healthinsu_subdiv_arr[3]['zip'] . ' ' . $healthinsu_subdiv_arr[3]['city'];
				//$sapv_recipient = trim($healthinsu_subdiv_arr[3]['street1']) . "<br />" . trim($healthinsu_subdiv_arr[3]['zip'] . " " . $healthinsu_subdiv_arr[3]['city']);

				$this->view->health_insurance = $phi_details;
				$this->view->hi_subdiv_address = $phi_details_sub;


				//pflege
				//get pflegestuffe in current invoice period
				$pflege_arr = $pflege->getpatientMaintainanceStageInPeriod($invoice_data['ipid'], $invoice_data['invoice_start'], $invoice_data['invoice_end']);

				if($pflege_arr)
				{
					$last_pflege = end($pflege_arr);
					$this->view->patient_pflegestufe = $last_pflege['stage'];
				}
				else
				{
					$this->view->patient_pflegestufe = ' - ';
				}


				if(strlen($invoice_data['address']) == 0)
				{
					$invoice_data['address'] = $phi_details_sub;
				}

				if(strlen($invoice_data['footer']) == 0)
				{
					$invoice_data['footer'] = $letter_boxes_details[0]['sapv_invoice_footer'];
				}
				if($_REQUEST['pdfdbg'])
				{
					print_r("inv data\n");

					print_r($invoice_data['items']);
				}


				//create pdf items data
				foreach($invoice_data['items'] as $k_items => $v_item)
				{
					$invoice_data_pdf['invoice_items']['type'][$v_item['id']] = $v_item['type'];

					if(!empty($v_item['start_hours']))
					{
						$invoice_data_pdf['invoice_items']['start_hours'][$v_item['id']] = $v_item['start_hours'];
						$invoice_data_pdf['invoice_items']['end_hours'][$v_item['id']] = $v_item['end_hours'];
					}
					
					if(strlen($v_item['sub_item']) != '0')
					{
						foreach($v_item['periods']['from_date'] as $k_date => $v_date)
						{
							$invoice_data_pdf['invoice_items']['dates'][$v_item['id']][$v_item['sub_item']][$k_date][] = date('d.m.Y', strtotime($v_date));
							$invoice_data_pdf['invoice_items']['dates'][$v_item['id']][$v_item['sub_item']][$k_date][] = date('d.m.Y', strtotime($v_item['periods']['till_date'][$k_date]));
						}


						$invoice_data_pdf['invoice_items']['code'][$v_item['id']][$v_item['sub_item']] = $v_item['shortcut'];
						$invoice_data_pdf['invoice_items']['name'][$v_item['id']][$v_item['sub_item']] = $v_item['name'];
						$invoice_data_pdf['invoice_items']['qty'][$v_item['id']][$v_item['sub_item']] = $v_item['qty'];
						$invoice_data_pdf['invoice_items']['price'][$v_item['id']][$v_item['sub_item']] = $v_item['price'];
						$invoice_data_pdf['invoice_items']['total'][$v_item['id']][$v_item['sub_item']] = $v_item['total'];
					}
					else
					{
						foreach($v_item['periods']['from_date'] as $k_date => $v_date)
						{
							$invoice_data_pdf['invoice_items']['dates'][$v_item['id']][] = date('d.m.Y', strtotime($v_date));
							if($v_item['type'] == 'gr')
							{
								$invoice_data_pdf['invoice_items']['dates'][$v_item['id']][] = date('d.m.Y', strtotime($v_item['periods']['till_date'][$k_date]));								
							}
						}
						$invoice_data_pdf['invoice_items']['code'][$v_item['id']] = $v_item['shortcut'];
						$invoice_data_pdf['invoice_items']['name'][$v_item['id']] = $v_item['name'];
						$invoice_data_pdf['invoice_items']['qty'][$v_item['id']] = $v_item['qty'];
						$invoice_data_pdf['invoice_items']['price'][$v_item['id']] = $v_item['price'];
						$invoice_data_pdf['invoice_items']['total'][$v_item['id']] = $v_item['total'];
					}
				}
				
				$this->view->invoice_data = $invoice_data;
				$this->view->patient_details = $patient_details;
				$this->view->client_details = $client_detail[0];
				
				$this->view->read_only_items = '0';
				if($invoice_data['status'] == '2' || $invoice_data['status'] == '3' || $invoice_data['status'] == '5')
				{
					$this->view->read_only_items = '1';
				}
			}
			
			//get deleted items ids and add them to post
			if($this->getRequest()->isPost())
			{
				//process deleted sp,sv & dp items
				$excluded_sp_items_str = trim(rtrim(str_replace(' ','', str_replace('del_', '', $_POST['excluded_sp_items']))));
				
				$delete['sp_ids'][] = '999999999';
				if(strlen($excluded_sp_items_str)>0)
				{
					$sp_items_ids = explode(',', $excluded_sp_items_str);
					
					if(!empty($sp_items_ids) > '0')
					{
						$delete['sp_ids'] = array_merge($delete['sp_ids'], $sp_items_ids);
					}
				}

				
				$excluded_sv_items_str = trim(rtrim(str_replace(' ','', str_replace('del_', '', $_POST['excluded_sv_items']))));
				
				$delete['sv_ids'][] = '999999999';
				if(strlen($excluded_sv_items_str)>0)
				{
					$sv_items_ids = explode(',', $excluded_sv_items_str);
					
					if(!empty($sv_items_ids) > '0')
					{
						$delete['sv_ids'] = array_merge($delete['sv_ids'], $sv_items_ids);
					}
				}

				
				
				
				
				$excluded_dp_items_str = trim(rtrim(str_replace(' ','', str_replace('del_', '', $_POST['excluded_dp_items']))));
				
				$delete['dp_ids'][] = '999999999';
				if(strlen($excluded_dp_items_str)>0)
				{
					$dp_items_ids = explode(',', $excluded_dp_items_str);

					if(!empty($dp_items_ids)>'0')
					{
						$delete['dp_ids'] = array_merge($delete['dp_ids'], $dp_items_ids);
					}
				}
				
				$excluded_gr_items_str = trim(rtrim(str_replace(' ','', str_replace('del_', '', $_POST['excluded_gr_items']))));
				
				$delete['gr_ids'][] = '999999999';
				if(strlen($excluded_gr_items_str)>0)
				{
					$gr_items_ids = explode(',', $excluded_gr_items_str);

					if(!empty($gr_items_ids)>'0')
					{
						$delete['gr_ids'] = array_merge($delete['gr_ids'], $gr_items_ids);
					}
				}
				
				$_POST['delete_ids'] = $delete;
			}
			
			if($this->getRequest()->isPost() && !empty($_REQUEST['invoiceid']) && empty($_REQUEST['pdf']))
			{
				$status = '0';
				if(!empty($_POST['completed']))
				{
					$status = '2'; //unpaid
				}
				else if(!empty($_REQUEST['edit_invoice']))
				{
					$status = '1'; //draft
				}
				else if($_POST['deletemore'] == "1")
				{
					$status = '4'; //deleted
				}
				else if(!empty($_REQUEST['pdf']))
				{
					$status = '0'; //no change
				}
				else if(!empty($_REQUEST['users_invoice']))
				{
					$status = '0'; //no change
				}

				if(empty($_REQUEST['pdf']))
				{

					if($status == '2' && $invoice_data['status'] == '1' && $invoice_data['prefix'] == 'TEMP_') //completed aka not paid and not draft
					{
						$high_invoice_nr = $internal_invoices->get_next_invoice_number($clientid, $invoice_data['user']);
						$_POST['prefix'] = $high_invoice_nr['prefix'];
						$_POST['invoice_number'] = $high_invoice_nr['invoicenumber'];
					}
					else
					{
						$_POST['prefix'] = $invoice_data['prefix'];
						$_POST['invoice_number'] = $invoice_data['invoice_number'];
					}
					//save here
					if(strlen($_POST['invoice']['address']) > 0 ){
					    if(strpos($_POST['invoice']['address'],"style"))
					    {
					        $_POST['invoice']['address'] = preg_replace('/style=\"(.*)\"/i', '', $_POST['invoice']['address']);
					    }
					    $_POST['invoice']['address'] = str_replace(array("<p >","<p>"), "", $_POST['invoice']['address']);
					    $_POST['invoice']['address'] = str_replace("</p>", "<br/>", $_POST['invoice']['address']);
					}
					
					$edit_invoice = $internal_invoices_form->edit_invoice($_REQUEST['invoiceid'], $clientid, $_POST, $status);

					if($edit_invoice)
					{

						$this->_redirect(APP_BASE . 'internalinvoice/invoices?invoiceid=' . $_REQUEST['invoiceid'] . '&flg=edtsuc');
					}
					else
					{
						$this->_redirect(APP_BASE . 'internalinvoice/invoices?invoiceid=' . $_REQUEST['invoiceid'] . '&flg=edterr');
					}
				}
			}
			else if((!empty($_REQUEST['pdf']) || $_POST['pdf']) && !empty($_REQUEST['invoiceid']))
			{
			    
				if($invoice_data['status'] == '1')
				{
					$_POST['completed_date'] = ''; //is set as current date- for completion of invoice -  dont't send it to pdf
				}

				$pdf_data = $_POST;
				if($_REQUEST['dbgqz'])
				{
					print_r("pdf_data\n");
					print_r($pdf_data);
					print_r("invoice_data_pdf\n");
					print_r($invoice_data_pdf);

				}
				foreach($invoice_data_pdf['invoice_items']['type'] as $k_inv_id => $v_inv_id)
				{
					if(in_array($k_inv_id, $pdf_data['delete_ids']['sp_ids']) || in_array($k_inv_id, $pdf_data['delete_ids']['dp_ids']) || in_array($k_inv_id, $pdf_data['delete_ids']['gr_ids']))
					{
						unset($invoice_data_pdf['invoice_items']['type'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['dates'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['code'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['name'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['qty'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['price'][$k_inv_id]);
						unset($invoice_data_pdf['invoice_items']['total'][$k_inv_id]);
//						unset($invoice_data_pdf['invoice_items']['start_hours'][$k_inv_id]);
//						unset($invoice_data_pdf['invoice_items']['end_hours'][$k_inv_id]);
					}
					else if(array_key_exists($k_inv_id, $pdf_data['invoice_items']['type']))
					{
						$pdf_data['invoice_items']['type'][$k_inv_id] = $invoice_data_pdf['invoice_items']['type'][$k_inv_id];
						$pdf_data['invoice_items']['dates'][$k_inv_id] = $invoice_data_pdf['invoice_items']['dates'][$k_inv_id];
						$pdf_data['invoice_items']['code'][$k_inv_id] = $invoice_data_pdf['invoice_items']['code'][$k_inv_id];
						$pdf_data['invoice_items']['name'][$k_inv_id] = $invoice_data_pdf['invoice_items']['name'][$k_inv_id];
						$pdf_data['invoice_items']['qty'][$k_inv_id] = $invoice_data_pdf['invoice_items']['qty'][$k_inv_id];
						$pdf_data['invoice_items']['price'][$k_inv_id] = $invoice_data_pdf['invoice_items']['price'][$k_inv_id];
						$pdf_data['invoice_items']['total'][$k_inv_id] = $invoice_data_pdf['invoice_items']['total'][$k_inv_id];
						
						$pdf_data['invoice_items']['start_hours'][$k_inv_id] = $invoice_data_pdf['invoice_items']['start_hours'][$k_inv_id];
						$pdf_data['invoice_items']['end_hours'][$k_inv_id] = $invoice_data_pdf['invoice_items']['end_hours'][$k_inv_id];
					}
				}
				
				if($_REQUEST['dbgqz'])
				{
					print_r("pdf_data 2\n");
					print_r($pdf_data);
					exit;
				}
				
				if($_REQUEST['pdf'] == '1')
				{
					$pdf_data['invoice_items'] = $invoice_data_pdf['invoice_items'];
					$pdf_data['grand_total'] =  $invoice_data['invoice_total'];
				}

				//user details and user address
				$pdf_data['user_details'] = $user_details_str;
				$pdf_data['control_number'] = $user_details['control_number'];

				//prepare items and custom items for pdf
				$pdf_data['prefix'] = $invoice_data['prefix'];
				$pdf_data['invoice_number'] = $invoice_data['invoice_number'];
				$pdf_data['patientdetails'] = $patient_details;
				$pdf_data['client_details'] = $client_detail[0];
				
				$pdf_data['first_active_day'] = $invoice_data['start_active'];
				$pdf_data['last_active_day'] = $invoice_data['end_active'];


				if(strlen($_POST['invoice']['address']) > '0')
				{
					$pdf_data['address'] = $_POST['invoice']['address'];
				}
				else
				{
					$pdf_data['address'] = $invoice_data['address'];
				}

				if(strlen($_POST['footer']) > '0')
				{
					$pdf_data['sapv_footer'] = $_POST['footer'];
				}
				else
				{
					$pdf_data['sapv_footer'] = $invoice_data['footer'];
				}
				
				$pdf_data['footer'] = $pdf_data['sapv_footer'];
				
				$pdf_data['master_items'] = $invoice_data['items'];

				$pdf_data['client_ik'] = $this->view->client_ik;
				$pdf_data['patient_pflegestufe'] = $this->view->patient_pflegestufe;

				$pdf_data['insurance_no'] = $this->view->insurance_no;
				$pdf_data['clientid'] = $clientid;
				
				$pdf_data['unique_id'] = $invoice_data['id'];


				if($invoice_data['completed_date'] != '0000-00-00 00:00:00' && empty($pdf_data['completed_date']))
				{
					$pdf_data['completed_date'] = date('d.m.Y', strtotime($invoice_data['completed_date']));
				}
				else if(empty($pdf_data['completed_date']))
				{
					$pdf_data['completed_date'] = date('d.m.Y', strtotime($invoice_data['completed_date_sort']));
				}

				//ISPC-2747 Lore 27.11.2020
				if(strpos($invoice_data['show_boxes'], 'show_box_active') === false ){
				    $pdf_data['show_box_active'] = '0';
				}else {
				    $pdf_data['show_box_active'] = '1';
				}
				if(strpos($invoice_data['show_boxes'], 'show_box_patient') === false ){
				    $pdf_data['show_box_patient'] = '0';
				}else {
				    $pdf_data['show_box_patient'] = '1';
				}
				if(strpos($invoice_data['show_boxes'], 'show_box_sapv') === false ){
				    $pdf_data['show_box_sapv'] = '0';
				}else {
				    $pdf_data['show_box_sapv'] = '1';
				}
				
				if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'] > '0')
				{
					$storno_data = $internal_invoices->getInternalInvoice($_REQUEST['storno']);

					//ISPC-2532 Lore 09.11.2020
					$pdf_data['storned_invoice_number'] = $pdf_data['prefix'].$pdf_data['invoice_number'];
					
					$pdf_data['prefix'] = $storno_data['prefix'];
					$pdf_data['invoice_number'] = $storno_data['invoice_number'];
					$pdf_data['completed_date'] =  date('d.m.Y', strtotime($storno_data['completed_date']));// $storno_data['completed_date']; // TODO-2950 Ancuta 25.02.2020
// 					$pdf_data['grand_total'] =  Pms_CommonData::str2num($storno_data['invoice_total'])* (-1); //($storno_data['invoice_total'] * (-1));// TODO-2950 Ancuta 25.02.2020
					$pdf_data['grand_total'] =  "-".$storno_data['invoice_total']; //($storno_data['invoice_total'] * (-1));// TODO-2950 Ancuta 25.02.2020

					//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
					if($_REQUEST['bulk_print'] == '1'){
					    $pdf_data['unique_id'] = $storno_data['id'];
					}else{
					    $pdf_data['unique_id'] = $storno_data['record_id'];
					}
					
					$template = 'storno_internal_invoice_pdf.html';
				}
				else
				{
					$template = 'internal_invoice_pdf.html';
				}
				
				// ISPC-2747 Lore 16.12.2020  --- create the format for items that is used in templete
				if($invoice_data['custom_invoice'] == 'custom_invoice'){
				    foreach($invoice_data['items'] as $ky=>$vl){;
				        $pdf_data['invoice_items']['dates'][$vl['id']][$ky] = date('d.m.Y', strtotime($invoice_data['invoice_start']));
				        $pdf_data['invoice_items']['total_sorted'][$vl['id']] = $pdf_data['invoice_items']['total'][$vl['id']];
				        
				    }
				} else {
				    				
    				//sort items for pdf
    				$pdf_data_sorted = array_keys($this->array_sort($pdf_data['invoice_items']['dates'], '0', 'SORT_ASC'));
    				
    				foreach($pdf_data_sorted as $k_item => $item_data)
    				{
    					$pdf_data['invoice_items']['total_sorted'][$item_data] = $pdf_data['invoice_items']['total'][$item_data];
    				}
				
				}
				
				if($_REQUEST['pdfdbg'])
				{					
					print_r($pdf_data['invoice_items']);
					exit;
				}
				
				// ISPC-2472 @Ancuta 07.11.2019
				$pdf_file_name = "Invoice {$patient_details['epid']}";
				$invoice_number_full="";
				$invoice_number_full .=  (strlen($pdf_data['prefix']) > 0) ? $pdf_data['prefix'] : '';
				$invoice_number_full .= $pdf_data['invoice_number'];
				
				if(strlen($invoice_number_full) > 0 ){
				    $pdf_file_name = $invoice_number_full;
				}
				// --
				
				//ISPC-2233
				$template_data = InvoiceTemplates::get_template($clientid, false, '1', 'internal_invoice');
				if (isset($template_data[0])) {

				    $template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
				     
				    $tokenfilter = array();
				    
				    
				    $pdf_data['invoice_number'] = $pdf_data['prefix'] .$pdf_data['invoice_number'];
				    
				    $tokenfilter['invoice'] = $pdf_data;
				    $tokenfilter['invoice']['address'] = $pdf_data['address'];
				    $tokenfilter['invoice']['invoicenumber'] = $pdf_data['invoice_number'];
				    $tokenfilter['invoice']['invoicedate'] = $pdf_data['completed_date'];
				    $tokenfilter['invoice']['invoicedate'] = $pdf_data['completed_date'];
				    $tokenfilter['invoice']['internal_invoice_items_html'] = $html_items;
				    
				    $tokenfilter['patient'] = $patient_details; // send all patient details
				    $tokenfilter['client'] = $client_detail[0]; // send all client details
				    $tokenfilter['client']['client_ik'] = $client_detail[0]['institutskennzeichen'];
				    $tokenfilter['user'] = $user_details; // send all user details
				    
				    
				    $tokenfilter['invoice'] ['benutzer_adresse'] = $user_details_str;
				    
				   
				    $tokenfilter['invoice'] ['invoiced_period'] = date("d.m.Y",strtotime($invoice_data['invoice_start'])).' - '.date("d.m.Y",strtotime($invoice_data['invoice_end']));
				    
				     
				    $tokenfilter['default_tokens']['default_current_date'] = date("d.m.Y");
				    
				    $tokenfilter['invoice']['sapv_recipient'] = $phi_details_sub; //ISPC-1236
				    
	
                        // ITEMS
                    $products_array = array();
                    $kord = 0;
                    $invoice_total = 0;
                    foreach ($pdf_data['invoice_items']['type'] as $k_item => $v_item) {
                        if ($v_item == "dp") {
                            foreach ($pdf_data['invoice_items']['dates'][$k_item] as $k_v_item => $v_v_item) {
                                $products_array[$kord]['type'] = $v_item;
                                $datesstr = "";
                                foreach ($pdf_data['invoice_items']['dates'][$k_item][$k_v_item] as $gr => $dates) {
                                    if (count($dates) == "1") {
                                        //$datesstr .= date("D, d.m.Y", strtotime($dates[0])) . "<br/>";
                                        $datesstr .= strftime("%a,", strtotime($dates[0])).' '.date("d.m.Y", strtotime($dates[0])) . "<br/>";
                                    } else {
                                        //$datesstr .= date("D, d.m.Y", strtotime($dates[0])) . " - " . date("D, d.m.Y", strtotime($dates[1])) . "<br/>";
                                        //$datesstr .= strftime("%a,", strtotime($dates[0])).' '.date("d.m.Y", strtotime($dates[0])) . " - " . strftime("%a,", strtotime($dates[1])).' '.date("d.m.Y", strtotime($dates[1])) . "<br/>";
                                        $datesstr .= strftime("%a,", strtotime($dates[0])).' '.date("d.m.Y", strtotime($dates[0]));
                                        if(date("d.m.Y", strtotime($dates[1])) != "01.01.1970"){
                                            $datesstr .= " - " . strftime("%a,", strtotime($dates[1])).' '.date("d.m.Y", strtotime($dates[1])) . "";
                                        }
                                        $datesstr .= "<br/>";
                                    }
                                }
                                $products_array[$kord]['date_str'] = $datesstr;
                                
                                $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item][$k_v_item];
                                $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item][$k_v_item];
                                $products_array[$kord]['qty'] = $pdf_data['invoice_items']['qty'][$k_item][$k_v_item];
                                $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item][$k_v_item]);
                                $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item][$k_v_item]);
                            }
                        } elseif ($v_item == "gr") // handle insert of grouped items
                        {
                            foreach ($pdf_data['invoice_items']['dates'][$k_item] as $k_v_item => $v_v_item) {
                                $products_array[$kord]['type'] = $v_item;
                                // $products_array[$kord]['date'] = $pdf_data['invoice_items']['dates'][$k_item];
                                $datesstr = "";
                                if (count($pdf_data['invoice_items']['dates'][$k_item]) == "1") {
                                    //$datesstr .= date("D, d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . "<br/>";
                                    $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . "<br/>";
                                } else {
                                    //$datesstr .= date("D, d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . " - " . date("D, d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])) . "<br/>";
                                    //$datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . " - " . strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])) . "<br/>";
                                    $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) ;
                                    if(date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])) != "01.01.1970"){
                                        $datesstr .= " - " . strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][1]));
                                    }
                                    $datesstr .= "<br/>";
                                }
                                $products_array[$kord]['date_str'] = $datesstr;
                                
                                $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item];
                                $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item];
                                $products_array[$kord]['qty'] = $pdf_data['invoice_items']['qty'][$k_item];
                                $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item]);
                                $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item]);
                                $invoice_total = $invoice_total + $products_array[$kord]['total'];
                            }
                        }                         // specific products or custom items
                        else 
                            if ($v_item == 'sp' || $v_item == 'sv' || $v_item == 'cu' || $v_item == 'le') {
								//TODO-3012 Ancuta 20-23.03.2020 - added "le" type
                                $products_array[$kord]['type'] = $v_item;
                                $datesstr = "";
                                //$datesstr .= date("D, d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0]));
                                $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0]));
                                
                                if (! empty($pdf_data['invoice_items']['start_hours'][$k_item])) {
                                    foreach ($pdf_data['invoice_items']['start_hours'][$k_item] as $shk => $shs) {
										//TODO-3012 Ancuta 20-23.03.2020 special display for le type
                                        if($v_item == 'le'){
                                            $datesstr .= "<br/>" . date("H:i", strtotime($shs)) ." Uhr <br/>";
                                        }else{
                                            $datesstr .= "<br/>" . date("H:i", strtotime($shs)) . " - " . date("H:i", strtotime($pdf_data['invoice_items']['end_hours'][$k_item][$shk])) . " Uhr <br/>";
                                        }
                                    }
                                }
                                
                                $products_array[$kord]['date_str'] = $datesstr;
                                $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item];
                                $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item];
                                $products_array[$kord]['qty'] = $pdf_data['invoice_items']['qty'][$k_item];
                                $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item]);
                                $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item]);
                            }
                        $kord ++;
                    }
                    // dd($pdf_data['invoice_items']);
                    // dd($products_array, $pdf_data['invoice_items']);
                    
                    //ISPC-2747 Lore 11.12.2020 --- tokens pentru template
                    $tokenfilter['invoice']['sapv_approve_date'] = $invoice_data['sapv_approve_date'] !='0000-00-00 00:00:00' ? date('d.m.Y', strtotime($invoice_data['sapv_approve_date'])) :"";
                    $tokenfilter['invoice']['sapv_approve_nr']   = $invoice_data['sapv_approve_nr'] !=0 ? $invoice_data['sapv_approve_nr'] : "";
                    $tokenfilter['invoice']['insurance_no']   = $invoice_data['insurance_no'];
                    $tokenfilter['invoice']['debitoren_nummer_oder_pv'] = $invoice_data['debtor_number'];
                    $tokenfilter['invoice']['invoiced_month'] = "";
                    
                    
                    $rows = count($products_array);
                    $grid = new Pms_Grid($products_array, 1, $rows, "internal_invoice_items_list_pdf.html");
                    $grid->invoice_total = $pdf_data['grand_total'];
                    $grid->max_entries = $rows;
                    $html_items = $grid->renderGrid();
				
                    $tokenfilter['invoice']['internal_invoice_items_html'] = $html_items;
                    $tokenfilter['invoice']['invoice_items_html'] = ""; //TODO-2713 Ancuta 04.12.2019

                                       
                    //TODO-2713 Ancuta 04.12.2019 [Abrechnungszeitraum]
                    $Abrechnungszeitraum = "";
                    if($invoice_data['start_active'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['start_active'])) != "1970"){
                        $Abrechnungszeitraum .= date('d.m.Y', strtotime($invoice_data['start_active']));
                    } elseif($invoice_data['invoice_start'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['invoice_start'])) != "1970") {
                        $Abrechnungszeitraum .= date('d.m.Y', strtotime($invoice_data['invoice_start']));
                    }
                    
                    if($invoice_data['end_active'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['end_active'])) != "1970"){
                        $Abrechnungszeitraum .= ' - '.date('d.m.Y', strtotime($invoice_data['end_active']));
                    } elseif($invoice_data['invoice_end'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['invoice_end'])) != "1970") {
                        $Abrechnungszeitraum .= ' - '.date('d.m.Y', strtotime($invoice_data['invoice_end']));
                    }
                    
                    //    TODO-2777 Ancuta 08.01.2020
                                        
                    $all_produc_Dates = array();
                    foreach($pdf_data['invoice_items']['dates'] as $kd=>$dates_arr){
                        foreach($dates_arr as $pd => $date){
                            if(is_array($date)){
                                foreach($date as $kper=>$periods){
                                    $all_produc_Dates = array_merge($all_produc_Dates,$periods);
                                }
                            } else {
                                $all_produc_Dates[] = $date;
                            }
                        }
                    }
                    foreach($all_produc_Dates as $kk=>$datas){
                        if($datas == "01.01.1970" ){
                            unset($all_produc_Dates[$kk]);
                        }
                    }
                    
//                     unset($all_produc_Dates[ array_search("01.01.1970", $all_produc_Dates)] );
                    
                   
                    /*  if($logininfo->userid == "338"){
                    echo "<pre>";
                    print_R($pdf_data['invoice_items']); 
                    print_R($all_produc_Dates); 
                    exit;
                    } 
                    */
                    
                    usort($all_produc_Dates, array(new Pms_Sorter(), "_date_compare"));
                    
                    $products_invoiced_period = "";
                    if( ! empty( $all_produc_Dates )){
                        $products_invoiced_period .= $all_produc_Dates[0];
                        $products_invoiced_period .= ' - '.end($all_produc_Dates);
                    }
                    if(strlen($products_invoiced_period) > 0 && $products_invoiced_period != " - "){
                        $Abrechnungszeitraum = $products_invoiced_period;
                    }
                    // --                     
                    
                    if(strlen($Abrechnungszeitraum) > 0)
                    {
                        $tokenfilter['invoice']['Abrechnungszeitraum'] = $Abrechnungszeitraum;
                    } else {
                        $tokenfilter['invoice']['Abrechnungszeitraum'] = "";
                    }
                    
                    //TODO-2713 Ancuta 04.12.2019 [footer_text]
                   $tokenfilter['invoice']['footer_text'] = str_replace(array(" <p >"," <p>"," <p> ","<p >","<p>"),"",  $invoice_data['footer']);
                   $tokenfilter['invoice']['footer_text'] = str_replace(array("</p>"," </p>","</p> "),"",  $tokenfilter['invoice']['footer_text']);
                   $tokenfilter['invoice']['footer_text'] = html_entity_decode($tokenfilter['invoice']['footer_text']);

                   if($_REQUEST['bulk_print'] == '1'){
                       
                       $pseudo_post['bulk_print'] = 1;
                       $pseudo_post['batch_temp_folder'] = $_REQUEST['batch_temp_folder'];
                       $pseudo_post['unique_id'] =$tokenfilter['invoice']['unique_id'];
                       $pseudo_post['clientid'] = $clientid;
                       $pseudo_post['controller'] = "invoice";// ??? WHY??? Ancuta 
                       
                       $file_name = $this->_editinvoice_generate_pdf_and_download_pj($template, $tokenfilter, $pdf_file_name,$clientid,$pseudo_post);
                       
                       return $file_name;
                   }
                   else
                   {
//  				    dd( $tokenfilter);
				    //$this->_editinvoice_generate_pdf_and_download($template, $tokenfilter, "Invoice {$patient_details['epid']}");
				    $this->_editinvoice_generate_pdf_and_download($template, $tokenfilter, $pdf_file_name);
                   }
				    
				    
				    
				} else { 
				    
				    if($_REQUEST['bulk_print'] == '1'){
				        $pdf_data['bulk_print'] = 1;
				        $pdf_data['batch_temp_folder'] = $_REQUEST['batch_temp_folder'];
				        
				        $files = $this->generate_pdf($pdf_data, "InternalInvoice", $template, "P");
				        return $files;
				        exit();
				    }
				    else
				    {
        				$this->generate_pdf($pdf_data, "InternalInvoice", $template, "P");
				    }
				}
			}
		}

		public function patientinvoiceAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$epid = Pms_CommonData::getEpid($ipid);
			$patientmaster = new PatientMaster();
			$tab_menus = new TabMenus();
			$client = new Client();
			$client_details = $client->getClientDataByid($clientid);
			//Check patient permissions on controller and action
			$patient_privileges = PatientPermissions::checkPermissionOnRun();
			if(!$patient_privileges)
			{
				$this->_redirect(APP_BASE . 'error/previlege');
			}
			
			$module_obj = new Modules();
			if($module_obj->checkModulePrivileges("76", $clientid))
			{
				$group_day_products = true;
				$this->view->grouping_option = '1';
			}
			else
			{
				$group_day_products = false;
				$this->view->grouping_option = '0';
			}

			// ISPC-2233
			// Added on 30.08.2018 By Ancuta
			if($module_obj->checkModulePrivileges("174", $clientid))
			{
			    $invoice_full_period = true;
			}
			else
			{
			    $invoice_full_period = false;
			}
			
			// TODO-3012 Ancuta 20-23.03.2020 (start)
			// Invluce LE actions in internal invoice
			if($module_obj->checkModulePrivileges("224", $clientid))
			{
			    $include_le_actions = true;
			}
			else
			{
			    $include_le_actions = false;
			}
			
			
			$xbdt_acion_users = array();
			if($include_le_actions){
			    // get all users with le actions in patient 
			    $saved_xbdt_goa  =array();
			    $saved_xbdt_goa = PatientXbdtActions::get_actions($ipid);
			    $xbdt_acion_users  =array();
			    foreach($saved_xbdt_goa as $k=>$xks)
			    {
			        if($xks['file_id'] == 0 ){
			            $xbdt_acion_users[] = $xks['userid'];
			        }
			    }
			    
			}
			// TODO-3012 Ancuta 20-23.03.2020 (end)
			
			$internal_invoices = new InternalInvoices();
			$internal_invoice_settings = new InternalInvoiceSettings();

			$internal_invoices_items = new InternalInvoiceItems();
			$internal_invoices_form = new Application_Form_InternalInvoices();
			$hi_perms = new HealthInsurancePermissions();

			$pflege = new PatientMaintainanceStage();

			$phelathinsurance = new PatientHealthInsurance();

			$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();

			$boxes = new LettersTextBoxes();
			
			$letter_boxes_details = $boxes->client_letter_boxes($clientid);
			$dp_subproducts_days = array('normal_days', 'hosp_adm_days', 'hosp_days', 'hosp_dis_days', 'hospiz_adm_days', 'hospiz_days', 'hospiz_dis_days', 'standby_days', 'hosp_dis_hospiz_adm_days', 'hospiz_dis_hosp_adm_days');
			/*			 * ******* Patient Information ************ */
			$patient_details = $patientmaster->getMasterData($decid, 0);
			$this->view->patient_details = $patient_details;

			$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
			$this->view->tabmenus = $tab_menus->getMenuTabs();
			/*			 * *************************************** */


			//health insurance
			$divisions = $hi_perms->getClientHealthInsurancePermissions($clientid);
			$hi_perms_divisions = $divisions;

			//health insurance
			$healthinsu_array = $phelathinsurance->getPatientHealthInsurance($ipid);
			$this->view->insurance_no = $healthinsu_array[0]['insurance_no'];

			if(!empty($healthinsu_array[0]['companyid']) && $healthinsu_array[0]['companyid'] != 0)
			{
				$helathins = Doctrine::getTable('HealthInsurance')->find($healthinsu_array[0]['companyid']);
				$healtharray = $helathins->toArray();

				if(empty($healthinsu_array[0]['name']))
				{
					$healthinsu_array[0]['name'] = $healtharray['name'];
				}
				if(empty($healthinsu_array[0]['insurance_provider']))
				{
					$healthinsu_array[0]['insurance_provider'] = $healtharray['insurance_provider'];
				}
				if(empty($healthinsu_array[0]['ins_street']))
				{
					$healthinsu_array[0]['ins_street'] = $healtharray['street1'];
				}
				if(empty($healthinsu_array[0]['ins_zip']))
				{
					$healthinsu_array[0]['ins_zip'] = $healtharray['zip'];
				}

				if(empty($healthinsu_array[0]['ins_city']))
				{
					$healthinsu_array[0]['ins_city'] = $healtharray['city'];
				}

				if(strlen($healthinsu_array[0]['institutskennzeichen']) == 0)
				{
					$this->view->health_insurance_ik = $healtharray['iknumber'];
				}

				if(strlen($healthinsu_array[0]['kvk_no']) == 0)
				{
					$this->view->health_insurance_kassenr = $healtharray['provider'];
				}
			}

			if($hi_perms_divisions)
			{

				$healthinsu_subdiv_arr = $healthinsu_subdiv->get_hi_subdivisions($ipid, $healthinsu_array[0]['companyid']);
			}

			if(empty($healthinsu_subdiv_arr[3]))
			{
				$address = $healthinsu_array[0]['name'] . '<br/>' . $healthinsu_array[0]['insurance_provider'] . '<br/>' . $healthinsu_array[0]['ins_street'] . '<br/>' . $healthinsu_array[0]['ins_zip'] . ' ' . $healthinsu_array[0]['ins_city'];
			}
			else
			{
				$phi_details_sub = $healthinsu_subdiv_arr[3]['name'] . '<br/>' . $healthinsu_subdiv_arr[3]['insurance_provider'] . '<br/>' . $healthinsu_subdiv_arr[3]['street1'] . '<br/> ' . $healthinsu_subdiv_arr[3]['zip'] . ' ' . $healthinsu_subdiv_arr[3]['city'];
				$address = $phi_details_sub;
			}

			$pathealthinsurancenr = "";
			if(count($healthinsu_array[0]))
			{
				$phi_details = $healthinsu_array[0]['name'];
			}
			else
			{
				$phi_details = "--";
			}

			$phi_details_sub = $healthinsu_subdiv_arr[3]['street1'] . '<br/> ' . $healthinsu_subdiv_arr[3]['zip'] . ' ' . $healthinsu_subdiv_arr[3]['city'];

			$this->view->health_insurance = $phi_details;
			$this->view->hi_subdiv_address = $phi_details_sub;



			$invoice_data['address'] = $address;
			$this->view->address = $client_details[0]['team_name'] . "<br />" . $client_details[0]['street1'] . " " . $client_details['street2'] . "<br />" . $client_details[0]['postcode'] . " " . $client_details[0]['city'];

//RADU ADDED
		
			$period_start = "2009-01-01";
			$period_end = date("Y-m-d");
			$conditions['periods'] = array("0"=>array('start'=>$period_start,'end'=>$period_end));
			$conditions['client'] = $clientid;
			$conditions['ipids'] = array($ipid);
			$active_patient_details = Pms_CommonData::patients_days($conditions);
			
			//construct cycle periods
			$cycles = $patientmaster->getTreatedDaysRealMultiple(array($ipid));
			$months = array();
			if(!empty($cycles[$ipid]['admissionDates']))
			{
				foreach($cycles[$ipid]['admissionDates'] as $key_adm => $v_adm)
				{
					if(!empty($cycles[$ipid]['dischargeDates'][$key_adm]['date']))
					{
						$start_with_discharge = date('Y-m-d', strtotime($cycles[$ipid]['admissionDates'][$key_adm]['date']));
						$end_with_discharge = date('Y-m-d', strtotime($cycles[$ipid]['dischargeDates'][$key_adm]['date']));

						$period_months = $this->get_period_months($start_with_discharge, $end_with_discharge, "Y-m");
						$months = array_merge($months, $period_months);
					}
					else
					{
						$start_without_discharge = date('Y-m-d', strtotime($cycles[$ipid]['admissionDates'][$key_adm]['date']));
						$end_without_discharge = date('Y-m-d', time());

						$period_months_till_now = $this->get_period_months($start_without_discharge, $end_without_discharge, "Y-m");
						$months = array_merge($months, $period_months_till_now);
					}
				}
			}
			else
			{
				$cycle_start_period = date('Y-m-d', strtotime($cycles[$ipid]['admission_date']));

				if(empty($cycles[$ipid]['discharge_date']))
				{

					$cycle_end_period = date('Y-m-d', time());
				}
				else
				{
					$cycle_end_period = date('Y-m-d', strtotime($cycles[$ipid]['discharge_date']));
				}


				$period_months = $this->get_period_months($cycle_start_period, $cycle_end_period, "Y-m");
				$months = array_merge($months, $period_months);
			}

			$months = array_values(array_unique($months));
			$months_array = array_values(array_unique($months));
			$month_select_array[] = $this->view->translate('overall');
			foreach($months as $k_month => $v_month)
			{
				if(!function_exists('cal_days_in_month'))
				{
					$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
				}
				else
				{
					$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
				}

				$months_details[$v_month]['start'] = $v_month . "-01";
				$months_details[$v_month]['days_in_month'] = $month_days;
				$months_details[$v_month]['end'] = $v_month . '-' . $month_days;

				$formated_v_month = date('m-Y', strtotime($months_details[$v_month]['start']));

				$month_select_array[$v_month] = $formated_v_month;
				$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
			}


			//overall month details
			$end_o_months = end($months);

			if(!function_exists('cal_days_in_month'))
			{
				$o_month_days = date('t', mktime(0, 0, 0, date("n", strtotime($end_o_months . "-01")), 1, date("Y", strtotime($end_o_months . "-01"))));
			}
			else
			{
				$o_month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($end_o_months . "-01")), date("Y", strtotime($end_o_months . "-01")));
			}
			$months_details[0]['start'] = $months[0] . '-01';
			$months_details[0]['end'] = $end_o_months . '-' . $o_month_days;

			//check if a month is selected START
			if(empty($_REQUEST['list']) && strlen($list) == 0)
			{
				$selected_month = 0;
			}
			else
			{
				if(strlen($list) == 0)
				{
					$list = $_REQUEST['list'];
				}
				if(array_key_exists($list, $month_select_array))
				{
					$selected_month = $list;
				}
			}

			//check if a month is selected END
			//construct month_selector START
			$attrs['onChange'] = 'changeMonth(this.value);';
			$attrs['class'] = 'select_month_internalinvoice';
			$attrs['id'] = 'select_month';

			$this->view->months_selector = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);
			//construct month_selector END
			//set current period to work with
			$period = $months_details[$selected_month];

			//get client users + sadmins
			$user = new User();
			$client_users_arr = $user->getUserByClientid($clientid, '0', true);



			foreach($client_users_arr as $k_usr => $v_usr)
			{
				$client_users[$v_usr['id']] = $v_usr;
				$client_users_ids[] = $v_usr['id'];
			}

//			print_r($client_users_ids);

			//get client users associations
			$users_ids_associated = UsersAssociation::get_associated_user_multiple($client_users_ids);
//			print_r($users_ids_associated);

			//invoice gather data start
			$day_products = new InternalInvoicesDayProducts();
			$specific_products = new InternalInvoicesSpecificProducts();
			$internal_invoices_pricelist = new InternalInvoicePriceList();
			$nholiday = new NationalHolidays();


			//get all client national holidays
			$national_holidays_arr = $nholiday->getNationalHoliday($clientid, $current_period['start'], true);

			foreach($national_holidays_arr as $k_natholiday => $v_natholiday)
			{
				$national_holidays[] = date('Y-m-d', strtotime($v_natholiday['NationalHolidays']['date']));
			}
			asort($national_holidays);
			$national_holidays = array_values($national_holidays);

			//get completed/paid/partialy paid invoices in period for selected user
			if(strlen($_REQUEST['user']) > '0')
			{
				$used_users[] = $_REQUEST['user'];
				$used_users[] = $users_ids_associated[$_REQUEST['user']];
				
			}
			else
			{
				$used_users = $client_users_ids;

			}
				
			$previous_invoices_items = $internal_invoices->get_completed_previous_invoices($clientid, $ipid, $client_users_ids, $period);
			
//			print_r($previous_invoices_items);
//			exit;
			//get all client products in period
			$period_pricelist_products = $internal_invoices_pricelist->get_period_pricelist($period['start'], $period['end']);

			//apply sp product rules
			// added for ISPC-2233 p1 - $invoice_full_period:: module to calculate products outside the active period 
			$specific_products_applied = $this->sp_rules($period_pricelist_products['sp'], $period, $ipid, $clientid, $users_ids_associated, $national_holidays, $previous_invoices_items['items'], $active_patient_details, $period_pricelist_products['lists_days'],$selected_month,$invoice_full_period);

			
			//apply sp visits rules - TODO 290 - 17.05.2016 -ANCUTA
			// added for ISPC-2233 p1 - $invoice_full_period:: module to calculate products outside the active period 
			$specific_visits_applied = $this->sv_rules($period_pricelist_products['sv'], $period, $ipid, $clientid, $users_ids_associated, $national_holidays, $previous_invoices_items['items'], $active_patient_details, $period_pricelist_products['lists_days'],$selected_month,$invoice_full_period);

// 			print_r("period \n ");
// 			print_r($active_patient_details);
            
			// TODO-3012 Ancuta 20-23.03.2020 (start)
			if($include_le_actions && !empty($xbdt_acion_users)){
			    $used_users = array_merge($used_users,$xbdt_acion_users);
    			$p_xbdt_goaii = new PriceXbdtActions();
    			
    			//period
    			// $period
    			$p_list = new PriceList();
    			$price_list = $p_list->get_client_list_period($period['start'], $period['end']);
    			$cl_xa_goa = XbdtActions ::client_xbdt_actions ($clientid);
    			
    			
    			$pricelist_xbdt_goa = array();
    			foreach( $price_list as $k=>$pl){
    			    $pricelist_xbdt_goa[$pl['id']] = $p_xbdt_goaii->get_prices($pl['id'], $clientid);
    			    $price_list_days[$pl['id']] = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($pl['start'])), date('Y-m-d',strtotime($pl['end'])));
    			}
   			
    			$dates2pl = array();
    			foreach($price_list_days as $list_id=>$dates){
    			    foreach($dates as $k=>$pl_date){
    			        $dates2pl[$pl_date] = $list_id;
    			    }
    			}
    			foreach($cl_xa_goa as $k=>$goa_act){
    			    $xbdt_goa_list[$goa_act['id']] = $goa_act;
    			    $xbdt_goa_list[$goa_act['id']]['shortcut'] = $goa_act['action_id'];
    			    if(strlen($pricelist_xbdt_goa[$goa_act['id']]['price'])>0){
    			        $xbdt_goa_list[$goa_act['id']]['price'] = $pricelist_xbdt_goa[$goa_act['id']]['price'];
    			    } else{
    			        $xbdt_goa_list[$goa_act['id']]['price'] = '0.00';
    			    }
    			}
    			
    			// get users
    			// get patient actions
    			$act_cond['ipids'] = array($ipid);
    			$act_cond['client'] = $clientid;
    			$act_cond['start'] = $period['start'];
    			$act_cond['end'] = $period['end'];
    			/* 
    			$act_cond['not_invoiced_or_storno'] = true;
    			$storno_invoices_q = Doctrine_Query::create()
    			->select("id,record_id")
    			->from('InternalInvoices')
    			->where('client = ?', $clientid)
    			->andWhere('storno = 1')
    			->andWhere('isdelete = 0');
    			$storno_invoices_array = $storno_invoices_q->fetchArray();
    			$storno_ids = array();
    			foreach($storno_invoices_array as $k=>$st)
    			{
    			    $storno_ids[] = $st['record_id'];
    			}
    			if(!empty($storno_ids)){
    			    $act_cond['storno_internal_invoices'] = $storno_ids;
    			} */
    			
    			$patient_actions = PatientXbdtActions::get_actions_filtered($act_cond);
    			$xbdt_goa_actions = array();
    			$cxg_cnt=0;
    			$user_le_actions = array();
    			foreach($patient_actions as $kxg=>$cxg){
    			    $cxg_cnt++;
    			    if($cxg['file_id'] == "0"){
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt] = $cxg;
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['xbdt_action'] =  $cxg['id'];
    			        
    			        //$xbdt_goa_actions[$cxg['id']]['price'] =  $xbdt_goa_list[$cxg['action']]['price'];
    			        // get price list for action date
    			        if($pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price']){
    			            $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['price'] =  $pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price'];
    			        } else{
    			            $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['price'] =  '0.00';
    			        }
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['name'] =  $cxg['XbdtActions']['name'];
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['shortcut'] =  $cxg['XbdtActions']['action_id'];
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['from_date'][] =  date("Y-m-d H:i", strtotime($cxg['action_date']));
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['from_date_view'][] =  date("d.m.Y", strtotime($cxg['action_date']));
    			        $xbdt_goa_actions[$cxg['userid']][$cxg_cnt]['till_date'][] =  date("Y-m-d H:i", strtotime($cxg['action_date']));
    			        
//     			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['extra'] = $xbdt_goa_actions[$cxg['userid']][$cxg_cnt];
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['normal'] = 1;
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['p_id'] = $cxg['id'];
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['p_type'] ='le_action';
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['normal_days'][] =date("Y-m-d", strtotime($cxg['action_date']));
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['name'] =$cxg['XbdtActions']['name'];
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['code'] = $cxg['XbdtActions']['action_id'];
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['start_hours'][] =  $cxg['action_date'];
    			        $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['end_hours'][] =  $cxg['action_date'];
    			        if($pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price']){
    			            $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['price'] = $pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price'];
    			        } else{
    			            $user_le_actions[$cxg['userid']][strtotime($cxg['action_date'])][$cxg['id']]['price'] = '0.00';
    			        }
    			    }
    			}
    		}
			// TODO-3012 Ancuta 20-23.03.2020 (end)

    		
            if($_REQUEST['visits'] == "1")
            {
    			print_r("users_ids_associated\n ");
    			print_r($users_ids_associated);
    			print_r("contact form \n ");
    			print_r($specific_products_applied);
    			print_r("\n visits \n ");
    			print_r($specific_visits_applied); 
    			exit;
            }
			
			
			
			//apply dp product rules
			$day_products_applied = $this->dp_rules($period_pricelist_products['dp'], $period, $ipid, $clientid, $patient_details, $users_ids_associated, $national_holidays, $previous_invoices_items['items'], $period_pricelist_products['lists_days']);

//			print_r("specific_products_applied\n");
//			print_r($specific_products_applied);
//			
//			print_r("day_products_applied\n");
//			print_r($day_products_applied);
//			exit;
//print_r($all_products);exit;


			$patient_locations_days = $this->get_patient_locations_days($ipid, $clientid, $patient_treatment[$ipid]);

			//get users with products
			$product_users = array();

			$specific_products_users = array_keys($specific_products_applied);
			$specific_visits_users = array_keys($specific_visits_applied);
			$day_products_users = array_keys($day_products_applied);
			// TODO-3012 Ancuta 20-23.03.2020
			$xbdt_products_users = array_keys($user_le_actions);
			//--
			if($specific_products_users)
			{
				$product_users = array_merge($product_users, $specific_products_users);
			}

			if($day_products_users)
			{
				$product_users = array_merge($product_users, $day_products_users);
			}
			
			if($specific_visits_users)
			{
				$product_users = array_merge($product_users, $specific_visits_users);
			}

			// TODO-3012 Ancuta 20-23.03.2020 
			if($xbdt_products_users)
			{
			    $product_users = array_merge($product_users, $xbdt_products_users);
			}
            //--  
			
			foreach($product_users as $k_prod_user => $v_prod_user)
			{
				if(!array_key_exists($v_prod_user, $users_ids_associated))
				{
					$users_w_products[$v_prod_user] = $client_users[$v_prod_user]['user_title'] . ' ' . $client_users[$v_prod_user]['last_name'] . ', ' . $client_users[$v_prod_user]['first_name'];
				}
				else
				{
					$users_w_products[$users_ids_associated[$v_prod_user]] = $client_users[$users_ids_associated]['user_title'] . ' ' . $client_users[$users_ids_associated[$v_prod_user]]['last_name'] . ', ' . $client_users[$users_ids_associated[$v_prod_user]]['first_name'];
				}
			}

			asort($users_w_products);

			foreach($users_w_products as $k_uwp => $v_uwp)
			{
				$prod_usrs_sorted[] = $k_uwp;
			}
			$this->view->products_users = $users_w_products;

			if($_REQUEST['dbggx'])
			{
				print_r("users_ids_associated\n");
				print_r($users_ids_associated);
				print_r("day_products_users\n");
				print_r($day_products_users);
				print_r("day_products_applied\n");
				print_r($day_products_applied);
				print_r("users_w_products\n");
				print_r($users_w_products);
			}
			
			//group all gruped products into a "group" product
//			print_r($day_products_applied);
//			exit;
			if($group_day_products)
			{
				foreach($day_products_applied as $k_userid => $v_products)
				{
					$cnt_prod = '1';
					foreach($v_products as $k_prod_id => $prod_days)
					{
						if($prod_days['grouped'] == '1')
						{
							//get only first existing product name
							if(strlen($product_name)==0)
							{
									$product_name = $period_pricelist_products['dp'][$k_prod_id]['normal_price_name'];
							}
							
//							$day_products_applied[$k_userid]['grouped']['qty'] += count($prod_days['days_products_prices']);
							if(strlen($product_price)==0)
							{
								$product_price = $period_pricelist_products['dp'][$k_prod_id]['normal_price'];
								$day_products_applied[$k_userid]['grouped']['price'] = $product_price; 
							}
							
							

							foreach($dp_subproducts_days as $k_subprod => $v_subprod)
							{
								if(count($prod_days[$v_subprod])>0)
								{
									if(empty($day_products_applied[$k_userid]['grouped']['grouped_days']))
									{
										$day_products_applied[$k_userid]['grouped']['grouped_days'] = array();
									}
									
									if(empty($day_products_applied[$k_userid]['grouped']['grouped_days_prices']))
									{
										$day_products_applied[$k_userid]['grouped']['grouped_days_prices'] = array();
									}
									
									if(empty($day_products_applied[$k_userid]['grouped']['grouped_pricelist_days']))
									{
										$day_products_applied[$k_userid]['grouped']['grouped_pricelist_days'] = array();
									}

									$day_products_applied[$k_userid]['grouped']['grouped_pricelist_days'] = array_merge($day_products_applied[$k_userid]['grouped']['grouped_pricelist_days'], $prod_days['pricelist_days']);
									$day_products_applied[$k_userid]['grouped']['grouped_days_prices'] = array_merge($day_products_applied[$k_userid]['grouped']['grouped_days_prices'], $prod_days['days_products_prices']);
									$day_products_applied[$k_userid]['grouped']['grouped_days'] = array_merge($day_products_applied[$k_userid]['grouped']['grouped_days'], $prod_days[$v_subprod]);
									asort($day_products_applied[$k_userid]['grouped']['grouped_days']);
									$day_products_applied[$k_userid]['grouped']['grouped_days'] = array_values(array_unique($day_products_applied[$k_userid]['grouped']['grouped_days']));
									//do the sum here
									$day_products_applied[$k_userid]['grouped']['grouped'][$v_subprod][$k_prod_id] = $prod_days[str_replace('_days', '', $v_subprod)];

									$subproduct_price = $period_pricelist_products['dp'][$k_prod_id][str_replace('_days', '_price', $v_subprod)];

									
									if(strlen($product_name) > '0')
									{
										$day_products_applied[$k_userid]['grouped']['name'] = $product_name;
									}
									else
									{
										$day_products_applied[$k_userid]['grouped']['name'] = $this->view->translate('grouped_product_name');
									}
																		
//									$day_products_applied[$k_userid]['grouped']['total_dbg'][] = 'Product:'.$k_prod_id.'-> Subproduct:'.$v_subprod.' = '.$prod_days[str_replace('_days', '', $v_subprod)].'*'.$subproduct_price.'('.($prod_days[str_replace('_days', '', $v_subprod)]*$subproduct_price).')';
								}
							}
									$day_products_applied[$k_userid]['grouped']['total'] = number_format(($day_products_applied[$k_userid]['grouped']['price']*$day_products_applied[$k_userid]['grouped']['qty']), 2,'.','');
						}

						if($cnt_prod == count($v_products))
						{
							$group = '0';
							foreach($day_products_applied[$k_userid]['grouped']['grouped_days'] as $k_day => $v_day)
							{
								//used in days grouping
								$n_vday_ts = strtotime('+1 day', strtotime($v_day));
								$next_v_day_ts = strtotime($day_products_applied[$k_userid]['grouped']['grouped_days'][$k_day + 1]);
								$next_v_day = $day_products_applied[$k_userid]['grouped']['grouped_days'][$k_day + 1];
								
								//used in same pricelist grouping
								$curent_day_pricelist = $day_products_applied[$k_userid]['grouped']['grouped_pricelist_days'][$v_day];
								$next_day_pricelist = $day_products_applied[$k_userid]['grouped']['grouped_pricelist_days'][$next_v_day];

								$day_products_applied[$k_userid]['grouped']['grouped_days_periods'][$group][] = $v_day;
								$day_products_applied[$k_userid]['grouped']['grouped_qty'][$group] += 1;
//								print_r("C: ".$v_day." N: ".$next_v_day."(".$n_vday_ts." - ".$next_v_day_ts.") - ".$curent_day_price .' != '.$next_day_price);
//								var_dump($n_vday_ts != $next_v_day_ts || $curent_day_price != $next_day_price);

								if($n_vday_ts != $next_v_day_ts || $curent_day_pricelist != $next_day_pricelist)
								{
									$group++;
								}
							}
						}
						$cnt_prod++;
					}
				}
			}
			
			if($_REQUEST['ddd'])
			{
				print_r($day_products_applied);
				exit;
			}

			//get day items relevant periods			
			foreach($day_products_applied as $k_userid => $v_products)
			{
				foreach($v_products as $k_prod_id => $prod_days)
				{
					$group = '0';
					foreach($prod_days['normal_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['normal_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['normal_days_periods'][$group][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group++;
						}
					}

					$group_h = '0';
					foreach($prod_days['hosp_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['hosp_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['hosp_days_periods'][$group_h][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_h++;
						}
					}

					$group_hi = '0';
					foreach($prod_days['hospiz_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['hospiz_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['hospiz_days_periods'][$group_hi][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_hi++;
						}
					}

					$group_s = '0';
					foreach($prod_days['standby_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['standby_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['standby_days_periods'][$group_s][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_s++;
						}
					}
				}
			}

			
			if(strlen($_REQUEST['user']) > '0')
			{
				$requested_user = $_REQUEST['user'];
			}
			else
			{
				if(count($prod_usrs_sorted) > '0')
				{
					$requested_user = $prod_usrs_sorted[0];
				}
				else
				{
					$requested_user = '0';
				}
			}
			
			if($previous_invoices_items['invoices'])
			{
				foreach($previous_invoices_items['invoices'] as $k_prev_inv => $v_prev_inv)
				{
					if($v_prev_inv['user'] == $requested_user || $v_prev_inv['create_user'] == $requested_user)
					{
						$previous_invoices[] = $v_prev_inv;
					}
				}
				
				$this->view->previous_invoices_data = $previous_invoices;
			}
			
			
			$user_details = $client_users[$requested_user];

			$user_details_str = '';
			// ISPC-2220 @Carmen 30.07.2018 -  pct 3) -- added title- Which now title is actualy salutation in user
			/* 
			if(strlen($user_details['title']) > '0')
			{
				$user_details_str .= $user_details['title'] . ' ';
			} 
			*/
			// TODO-1730 - @Ancuta 17.08.2018 - change from salutation to TITLE
			if(strlen($user_details['user_title']) > '0')
			{
			    $user_details_str .= $user_details['user_title'] . ' ';
			}
				
			
			if(strlen($user_details['first_name']) > '0')
			{
				$user_details_str .= $user_details['first_name'] . ' ';
			}

			if(strlen($user_details['last_name']) > '0')
			{
				$user_details_str .= $user_details['last_name'];
			}
			$user_details_str .= "<br />";


			if(strlen($user_details['street1']) > '0')
			{
				$user_details_str .= $user_details['street1'];
			}
			$user_details_str .= "<br />";
			if(strlen($user_details['zip']) > '0')
			{
				$user_details_str .= $user_details['zip'] . " ";
			}
			if(strlen($user_details['city']) > '0')
			{
				$user_details_str .= $user_details['city'];
			}
			$user_details_str .= "<br />";

			$footer_txt = $this->view->translate('internal_invoice_footer_text');
			$footer_txt = str_replace('%bank_name', $user_details['bank_name'], $footer_txt);
			$footer_txt = str_replace('%iban', $user_details['iban'], $footer_txt);
			$footer_txt = str_replace('%bic', $user_details['bic'], $footer_txt);
			$invoice_data['footer'] = $footer_txt;

			$this->view->user_details = $user_details_str;
			$this->view->control_number = $user_details['control_number'];

			$internal_invoice_settings_arr = $internal_invoice_settings->getUserInternalInvoiceSettings($requested_user, $clientid);
			$temp_invoicenumber = $internal_invoices->get_next_invoice_number($clientid, $requested_user, true, $internal_invoice_settings_arr);

			$invoice_data['prefix'] = $temp_invoicenumber['prefix'];
			$invoice_data['invoice_number'] = $temp_invoicenumber['invoicenumber'];
			$invoice_data['invoice_pay_days'] = $internal_invoice_settings_arr[$requested_user]['invoice_pay_days'];
			//update footer text with user pay days
			$invoice_data['footer'] = str_replace('%paydays', $invoice_data['invoice_pay_days'], $invoice_data['footer']);
			

			$this->view->period_pricelist_products = $period_pricelist_products;

			$days_keys = array('normal_days', 'hosp_days', 'hosp_adm_days', 'hosp_dis_days', 'hospiz_adm_days', 'hospiz_days', 'hospiz_dis_days', 'standby_days'
			     ,'hosp_dis_hospiz_adm_days', 'hospiz_dis_hosp_adm_days'
			);

			
			if($requested_user != '0')
			{
				//final sort specific products
				//new
				ksort($specific_products_applied[$requested_user]);
				ksort($specific_visits_applied[$requested_user]);
				//old
//				foreach($specific_products_applied[$requested_user] as $k_id => $v_data)
//				{
//					asort($v_data['normal_days']);
//					$specific_products_applied[$requested_user][$k_id]['normal_days'] = array_values($v_data['normal_days']);
//				}

				//final sort day products
				foreach($day_products_applied[$requested_user] as $k_d_id => $v_d_data)
				{
					foreach($days_keys as $k_ds => $v_ds)
					{
						if(!empty($v_d_data[$v_ds]))
						{
							asort($v_d_data[$v_ds]);
							$day_products_applied[$requested_user][$k_d_id][$v_ds] = $v_d_data[$v_ds];
						}
					}
				}


				$this->view->requested_user = $requested_user;
			
				
				if(!empty($specific_products_applied[$requested_user]))
				{
					foreach($specific_products_applied[$requested_user] as $k_product_sp => $v_product_sp)
					{
						$v_product_sp = array_values($v_product_sp);
						foreach($v_product_sp as $key_prod => $v_prod)
						{	
							$all_products_sp[$requested_user][$k_product_sp.'_'.$key_prod] = $v_product_sp[$key_prod];
							$all_products_sp[$requested_user][$k_product_sp.'_'.$key_prod]['p_type'] = 'sp';
							$all_products_sp[$requested_user][$k_product_sp.'_'.$key_prod]['sort_date'] = $v_prod['normal_days'][0];
						}
					}
					$all_products_sp[$requested_user] = array_values($all_products_sp[$requested_user]);
				}
				
				// TODO-3012 Ancuta 20-23.03.2020 (start)
				if(!empty($user_le_actions[$requested_user]))
				{
				    foreach($user_le_actions[$requested_user] as $k_product_le => $v_product_le)
					{
						$v_product_le = array_values($v_product_le);
						foreach($v_product_le as $key_prod => $v_prod)
						{	
							$all_products_le[$requested_user][$k_product_le.'_'.$key_prod] = $v_product_le[$key_prod];
							$all_products_le[$requested_user][$k_product_le.'_'.$key_prod]['p_type'] = 'le';
							$all_products_le[$requested_user][$k_product_le.'_'.$key_prod]['sort_date'] = $v_prod['normal_days'][0];
						}
					}
					$all_products_le[$requested_user] = array_values($all_products_le[$requested_user]);
				}
				// TODO-3012 Ancuta 20-23.03.2020 (end)
				
				if(!empty($specific_visits_applied[$requested_user]))
				{
					foreach($specific_visits_applied[$requested_user] as $k_product_sv => $v_product_sv)
					{
						$v_product_sv = array_values($v_product_sv);
						foreach($v_product_sv as $key_prod => $v_prod)
						{	
							$all_products_sv[$requested_user][$k_product_sv.'_'.$key_prod] = $v_product_sv[$key_prod];
							$all_products_sv[$requested_user][$k_product_sv.'_'.$key_prod]['p_type'] = 'sv';
							$all_products_sv[$requested_user][$k_product_sv.'_'.$key_prod]['sort_date'] = $v_prod['normal_days'][0];
						}
					}
					$all_products_sv[$requested_user] = array_values($all_products_sv[$requested_user]);
				}
				
				
				$all_products[$requested_user] = array();
				
				if(!empty($all_products_sp[$requested_user])){
				    $all_products[$requested_user] = $all_products_sp[$requested_user];
				}
				
				// merge arrays
				if($all_products_sv[$requested_user]){
				    $all_products[$requested_user] = array_merge($all_products[$requested_user],$all_products_sv[$requested_user]);
				}
				
				// TODO-3012 Ancuta 20-23.03.2020
				if($all_products_le[$requested_user]){
				    $all_products[$requested_user] = array_merge($all_products[$requested_user],$all_products_le[$requested_user]);
				}
				// --
				
				
				
				if($_REQUEST['dbgg'])
				{
					print_r("Day Products");
					print_r($day_products_applied);
				}

				foreach($day_products_applied[$requested_user] as $k_prod => $v_sub_prod)
				{
					if($v_sub_prod['grouped'] == '0')
					{
						foreach($days_keys as $k_day => $v_day_key)
						{
							$subproduct_key = $k_prod.'_'.$k_day;
							$days_key = str_replace('_days', '', $v_day_key);

							if(!empty($v_sub_prod[$v_day_key]) && !empty($v_sub_prod[$days_key]))
							{
                      
								$all_products[$requested_user][$subproduct_key]['p_id'] = $k_prod;
								$all_products[$requested_user][$subproduct_key]['p_type'] = 'dp';
								$all_products[$requested_user][$subproduct_key]['grouped'] = $v_sub_prod['grouped'];
								$all_products[$requested_user][$subproduct_key]['product_all_days'] = $v_sub_prod['product_all_days'];
								$all_products[$requested_user][$subproduct_key]['days_products_prices'] = $v_sub_prod['days_products_prices'];
							}
							
							if(!empty($v_sub_prod[$v_day_key]))
							{
								$all_products[$requested_user][$subproduct_key][$v_day_key] = $v_sub_prod[$v_day_key];
								$all_products[$requested_user][$subproduct_key][$v_day_key.'_periods'] = $v_sub_prod[$v_day_key.'_periods'];
								$all_products[$requested_user][$subproduct_key]['sort_date'] = $v_sub_prod[$v_day_key][0];
							}
							
				 
							if(!empty($v_sub_prod[$days_key]))
							{
								$all_products[$requested_user][$subproduct_key][$days_key] = $v_sub_prod[$days_key];
							}
						}
						
						unset($all_products[$requested_user][$k_prod]);
					}
					elseif($v_sub_prod['grouped'] == '1')
					{
						if(!empty($day_products_applied[$requested_user]))
						{
							foreach($day_products_applied[$requested_user] as $k_product_dp => $v_product_dp)
							{
								$subproduct_key = $k_product_dp.'_'.$k_day;
								$days_key = str_replace('_days', '', $v_day_key);
								
								if(!empty($v_sub_prod[$k_product_dp]) || !empty($v_sub_prod[$days_key]))
								{
									if($k_product_dp == 'grouped')
									{
										$v_g_product_dp['p_type'] = 'dp';
										$v_g_product_dp['p_id'] = $k_product_dp;
										$v_g_product_dp['name'] = $v_product_dp['name'];
										
										foreach($v_product_dp['grouped_days_periods'] as $k_gr_period => $v_gr_period)
										{
											$v_g_product_dp['grouped_qty'][$k_gr_period] = $v_product_dp['grouped_qty'][$k_gr_period];
											
											$v_g_product_dp['price'] = $v_product_dp['grouped_days_prices'][$v_gr_period[0]];
											$v_g_product_dp['grouped_days_periods'][$k_gr_period] = $v_gr_period;
											$v_g_product_dp['sort_date'] = $v_gr_period[0];
											$all_products[$requested_user]['grouped_'.$k_gr_period] = $v_g_product_dp;
											unset($v_g_product_dp['grouped_days_periods']);
										}
									}
									else
									{
										$v_product_dp['p_type'] = 'dp';
										$v_product_dp['p_id'] = $k_product_dp;
										
									
										$v_product_dp['p_test'] = 'normal';
										$v_product_dp['product_all_days'] = array_values($v_product_dp['product_all_days']);
										$v_product_dp['sort_date'] = $v_product_dp['product_all_days'][0];
										$all_products[$requested_user][$subproduct_key] = $v_product_dp;
									}
								}
							}
						}
					}
				}

				if($_REQUEST['dbgg'])
				{
//					print_r("All_products - Unsorted\n");
//					print_r($all_products[$requested_user]);
				}

				$all_products[$requested_user] = array_values($all_products[$requested_user]);
				$all_products_sorted[$requested_user] = $this->array_sort($all_products[$requested_user], 'sort_date', 'SORT_ASC');
				$this->view->all_products = $all_products_sorted[$requested_user];
				
				if($_REQUEST['dbggx'])
				{
					print_r("All_products - Sorted\n");
					print_r($all_products_sorted);
					exit;
				}
			 
 
				if($_REQUEST['dbgxz'])
				{

					print_r("spec products\n");
					print_r($specific_products_applied[$requested_user]);
//					print_r("day products\n");
//					print_r($day_products_applied[$requested_user]);
				}
			}


			foreach($specific_products_applied[$requested_user] as $k_id_item => $v_items_data_arr)
			{
				foreach($v_items_data_arr as $k_item => $v_item_data)
				{
					if(in_array($k_item, $days_keys) && !empty($v_item_data))
					{
						if(empty($products_all_days))
						{
							$products_all_days = array();
						}
						if($_REQUEST['x'])
						{
							print_r("X\n");
							print_r($v_item_data);
						}
						$products_all_days = array_merge($products_all_days, $v_item_data);
					}
				}
			}
			// TODO-3012 Ancuta 20-23.03.2020 (start)
			foreach($user_le_actions[$requested_user] as $k_id_item => $v_items_data_arr)
			{
				foreach($v_items_data_arr as $k_item => $v_item_data)
				{
					if(  !empty($v_item_data))
					{
						if(empty($products_all_days))
						{
							$products_all_days = array();
						}
						if($_REQUEST['x'])
						{
							print_r("X\n");
							print_r($v_item_data);
						}
						$products_all_days = array_merge($products_all_days, $v_item_data);
					}
				}
			}
			// TODO-3012 Ancuta 20-23.03.2020(end)
 
			foreach($day_products_applied[$requested_user] as $k_id_item => $v_items_data_arr)
			{
				foreach($v_items_data_arr as $k_item => $v_item_data)
				{
					if(in_array($k_item, $days_keys) && !empty($v_item_data))
					{
						if(empty($products_all_days))
						{
							$products_all_days = array();
						}
						if($_REQUEST['x'])
						{
							print_r("XX\n");
							print_r($v_item_data);
						}
						$products_all_days = array_merge($products_all_days, $v_item_data);
					}
				}
			}

			asort($products_all_days);
			$products_all_days = array_values(array_unique($products_all_days));

			if(!empty($products_all_days))
			{
				$active['start_invoice'] = date('Y-m-d H:i:s', strtotime($products_all_days[0]));
				$active['end_invoice'] = date('Y-m-d H:i:s', strtotime(end($products_all_days)));
			}

			if($_REQUEST['xq'])
			{
				print_r("specific_products_applied\n");
				print_r($specific_products_applied);

				print_r("day_products_applied\n");
				print_r($day_products_applied);
				print_r("product all days\n");
				exit;
			}


			//invoice gather data end
//RADU END

			$this->view->invoice_data = $invoice_data;
			$this->view->patient_details = $patient_details;
			$this->view->client_details = $client_detail[0];

			if($this->getRequest()->isPost() && !empty($_POST['create_invoice']) && empty($_POST['pdf']))
			{
				$post = $_POST;
				if($_REQUEST['dbgqz'])
				{
					print_r($post);
					
				}
                // Clean styles from address  
// 				if(strlen($post['invoice']['address']) > 0 ){
// 				    if(strpos($post['invoice']['address'],"style"))
// 				    {
// 				        $post['invoice']['address'] = preg_replace('/style=\"(.*)\"/i', '', $post['invoice']['address']);
// 				    }
// 				    $post['invoice']['address'] = str_replace(array("<p >","<p>"), "", $post['invoice']['address']);
// 				    $post['invoice']['address'] = str_replace("</p>", "<br/>", $post['invoice']['address']);
// 				}
				
				
				if(strlen($post['invoice']['address']) > 0 ){
				    if(strpos($post['invoice']['address'],"style"))
				    {
				        $post['invoice']['address'] = preg_replace('/style=\"(.*)\"/i', '', $post['invoice']['address']);
				    }
				    $post['invoice']['address'] = str_replace(array(" <p >"," <p>"," <p> ","<p >","<p>"),"", $post['invoice']['address']);
				    $post['invoice']['address'] = str_replace(array("</p>"," </p>","</p> "),"", $post['invoice']['address']);
				    $post['invoice']['address'] = str_replace(array("\n"),"<br />", $post['invoice']['address']);
				}
				//create invoice
				$ins_inv = new InternalInvoices();
				$ins_inv->client = $clientid;
				$ins_inv->user = $requested_user;
				$ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($period['start']));
				$ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($period['end']));
                
                if(!empty($active['start_invoice']) && $active['start_invoice'] != "0000-00-00 00:00:00"){
				    $ins_inv->start_active = $active['start_invoice'] ;
                } else {
				    $ins_inv->start_active =  date('Y-m-d H:i:s', strtotime($period['start']));
                }
                
                if(!empty($active['end_invoice']) && $active['end_invoice'] != "0000-00-00 00:00:00"){
    				$ins_inv->end_active = $active['end_invoice'];
                } else{
    				$ins_inv->end_active = date('Y-m-d H:i:s', strtotime($period['end']));
                }
				$ins_inv->ipid = $ipid;
				$ins_inv->prefix = $invoice_data['prefix'];
				$ins_inv->invoice_number = $invoice_data['invoice_number'];
				$ins_inv->invoice_total = Pms_CommonData::str2num($post['grand_total']);
				$ins_inv->status = '1'; // DRAFT - ENTWURF
				$ins_inv->address = $post['invoice']['address'];
				$ins_inv->footer = $post['footer'];
				$ins_inv->save();

				$inserted_id = $ins_inv->id;

				if($inserted_id)
				{
					foreach($post['invoice_items']['type'] as $k_item => $v_item)
					{
						if($v_item == "dp")
						{
							foreach($post['invoice_items']['dates'][$k_item] as $k_v_item => $v_v_item)
							{
								$ins_inv_itm = new InternalInvoiceItems();
								$ins_inv_itm->invoice = $inserted_id;
								$ins_inv_itm->client = $clientid;
								$ins_inv_itm->shortcut = $post['invoice_items']['code'][$k_item][$k_v_item];
								$ins_inv_itm->product = $post['invoice_items']['p_id'][$k_item][$k_v_item];
								$ins_inv_itm->sub_item = $k_v_item;
								$ins_inv_itm->name = $post['invoice_items']['name'][$k_item][$k_v_item];
								$ins_inv_itm->type = $post['invoice_items']['type'][$k_item];
								$ins_inv_itm->qty = $post['invoice_items']['qty'][$k_item][$k_v_item];
								$ins_inv_itm->price = Pms_CommonData::str2num($post['invoice_items']['price'][$k_item][$k_v_item]);
								$ins_inv_itm->total = Pms_CommonData::str2num($post['invoice_items']['total'][$k_item][$k_v_item]);
								$ins_inv_itm->save();

								$inserted_item = $ins_inv_itm->id;

								if($inserted_item)
								{

									foreach($post['invoice_items']['dates'][$k_item][$k_v_item] as $k_date => $v_date)
									{
										$items_period_arr[] = array(
											'invoice' => $inserted_id,
											'item' => $inserted_item,
											'from_date' => date('Y-m-d H:i:s', strtotime($v_date[0])),
											'till_date' => date('Y-m-d H:i:s', strtotime($v_date[1])),
										);
									}

									$collection = new Doctrine_Collection('InternalInvoiceItemsPeriod');
									$collection->fromArray($items_period_arr);
									$collection->save();
								}
								unset($items_period_arr);
							}
						}
						elseif($v_item == "gr") //handle insert of grouped items
						{
//							foreach($post['invoice_items']['dates'] as $k_v_item => $v_v_item)
//							{
								
//								if($k_item == $k_v_item)
//								{
									$ins_inv_itm = new InternalInvoiceItems();
									$ins_inv_itm->invoice = $inserted_id;
									$ins_inv_itm->client = $clientid;
									$ins_inv_itm->shortcut = $post['invoice_items']['code'][$k_item];
									$ins_inv_itm->product = '0';
									$ins_inv_itm->sub_item = '';
									$ins_inv_itm->name = $post['invoice_items']['name'][$k_item];
									$ins_inv_itm->type = $post['invoice_items']['type'][$k_item];
									$ins_inv_itm->qty = $post['invoice_items']['qty'][$k_item];
									$ins_inv_itm->price = Pms_CommonData::str2num($post['invoice_items']['price'][$k_item]);
									$ins_inv_itm->total = Pms_CommonData::str2num($post['invoice_items']['total'][$k_item]);
									$ins_inv_itm->save();

									$inserted_item_gr = $ins_inv_itm->id;

									if($inserted_item_gr)
									{
										
										if($post['invoice_items']['dates'][$k_item][1])
										{
											$grouped_till = $post['invoice_items']['dates'][$k_item][1];
										}
										else
										{
											$grouped_till = '0000-00-00 00:00:00';
										}
										
										$items_period_arr[] = array(
											'invoice' => $inserted_id,
											'item' => $inserted_item_gr,
											'from_date' => date('Y-m-d H:i:s', strtotime($post['invoice_items']['dates'][$k_item][0])),
											'till_date' => date('Y-m-d H:i:s', strtotime($grouped_till)),
										);
										
										$collection = new Doctrine_Collection('InternalInvoiceItemsPeriod');
										$collection->fromArray($items_period_arr);
										$collection->save();
									}
									unset($inserted_item_gr);
									unset($items_period_arr);
//								}
//							}
							
						}
						//specific products or custom items
						// TODO-3012 Ancuta 20-23.03.2020 - added also "le" type
						else if($v_item == 'sp' || $v_item == 'sv' || $v_item == 'cu' || $v_item == 'le')
						{
							$ins_inv_itm_sp = new InternalInvoiceItems();
							$ins_inv_itm_sp->invoice = $inserted_id;
							$ins_inv_itm_sp->client = $clientid;
							$ins_inv_itm_sp->shortcut = $post['invoice_items']['code'][$k_item];
							$ins_inv_itm_sp->product = $post['invoice_items']['p_id'][$k_item];
							$ins_inv_itm_sp->name = $post['invoice_items']['name'][$k_item];
							$ins_inv_itm_sp->type = $post['invoice_items']['type'][$k_item];
							$ins_inv_itm_sp->qty = $post['invoice_items']['qty'][$k_item];
							$ins_inv_itm_sp->price = Pms_CommonData::str2num($post['invoice_items']['price'][$k_item]);
							$ins_inv_itm_sp->total = Pms_CommonData::str2num($post['invoice_items']['total'][$k_item]);
							$ins_inv_itm_sp->save();

							$inserted_item_sp = $ins_inv_itm_sp->id;
							
							
							
							if($inserted_item_sp)
							{
								foreach($post['invoice_items']['dates'][$k_item] as $k_date => $v_date)
								{
									$items_period_arr_sp[] = array(
										'invoice' => $inserted_id,
										'item' => $inserted_item_sp,
										'from_date' => date('Y-m-d H:i:s', strtotime($v_date)),
										'till_date' => '0000-00-00 00:00:00',
									);
								}
								$collection = new Doctrine_Collection('InternalInvoiceItemsPeriod');
								$collection->fromArray($items_period_arr_sp);
								$collection->save();
								unset($items_period_arr_sp);
								
								if($v_item == 'sp' && !empty($post['invoice_items']['start_hours'][$k_item]))
								{
									//insert specific products times
									foreach($post['invoice_items']['start_hours'][$k_item] as $k_st =>$v_st)
									{
										$items_times_period_arr[] = array(
											'invoice' => $inserted_id,
											'item' => $inserted_item_sp,
											'start_hours' => $v_st,
											'end_hours' => $post['invoice_items']['end_hours'][$k_item][$k_st]
										);
									}
								
									$collection = new Doctrine_Collection('InternalInvoiceItemsTimes');
									$collection->fromArray($items_times_period_arr);
									$collection->save();
									unset($items_times_period_arr);
								}
								
								
								if($v_item == 'sv' && !empty($post['invoice_items']['start_hours'][$k_item]))
								{
									//insert specific products times
									foreach($post['invoice_items']['start_hours'][$k_item] as $k_st =>$v_st)
									{
										$items_times_period_arr[] = array(
											'invoice' => $inserted_id,
											'item' => $inserted_item_sp,
											'start_hours' => $v_st,
											'end_hours' => $post['invoice_items']['end_hours'][$k_item][$k_st]
										);
									}
								
									$collection = new Doctrine_Collection('InternalInvoiceItemsTimes');
									$collection->fromArray($items_times_period_arr);
									$collection->save();
									unset($items_times_period_arr);
								}
								
								// TODO-3012 Ancuta 20-23.03.2020(start)
								if($v_item == 'le' && !empty($post['invoice_items']['start_hours'][$k_item]))
								{
									//insert specific products times
									foreach($post['invoice_items']['start_hours'][$k_item] as $k_st =>$v_st)
									{
										$items_times_period_arr[] = array(
											'invoice' => $inserted_id,
											'item' => $inserted_item_sp,
											'start_hours' => $v_st,
											'end_hours' => $post['invoice_items']['end_hours'][$k_item][$k_st]
										);
									}
								
									$collection = new Doctrine_Collection('InternalInvoiceItemsTimes');
									$collection->fromArray($items_times_period_arr);
									$collection->save();
									unset($items_times_period_arr);
								}
								

								if($v_item == 'le'){
								    // update le actions, mark as paid
								    $patient_le_Actions = Doctrine::getTable('PatientXbdtActions')->find($post['invoice_items']['p_id'][$k_item]);
								    if($patient_le_Actions){
								        $patient_le_Actions->file_id = $inserted_id;
								        $patient_le_Actions->edited_from = "internalinvoice";
								        $patient_le_Actions->save();
								    }
								}
								// TODO-3012 Ancuta 20-23.03.2020 (end)
							}
						}
					}
				}
	 			$this->_redirect(APP_BASE . 'patientcourse/patientcourse?id=' . $_REQUEST['id']);
                exit;

				//write verlauf
//	 			$cust = new PatientCourse();
//	 			$cust->ipid = $ipid;
//	 			$cust->course_date = date("Y-m-d H:i:s", time());
//	 			$cust->course_type = Pms_CommonData::aesEncrypt("K");
//	 			$cust->course_title = Pms_CommonData::aesEncrypt(addslashes('internal Rechnung hinzugefügt'));
//	 			$cust->user_id = $userid;
//	 			$cust->save();
//				print_r($_POST);exit;
			}
			else if($this->getRequest()->isPost() &&  !empty($_POST['pdf']))
			{
				 
				if($invoice_data['status'] == '1')
				{
					$_POST['completed_date'] = ''; //is set as current date- for completion of invoice -  dont't send it to pdf
				}
				$pdf_data = $_POST;
				$pdf_data['user_details'] = $this->view->user_details;
				$pdf_data['control_number'] = $this->view->control_number;

				//prepare items and custom items for pdf
				$pdf_data['prefix'] = $invoice_data['prefix'];
				$pdf_data['invoice_number'] = $invoice_data['invoice_number'];
				$pdf_data['patientdetails'] = $patient_details;
				$pdf_data['client_details'] = $client_detail[0];
				$pdf_data['invoice_total'] = $pdf_data['grand_total'];

				if(strlen($_POST['invoice']['address']) > '0')
				{
					$pdf_data['address'] = $_POST['invoice']['address'];
				}
				else
				{
					$pdf_data['address'] = $invoice_data['address'];
				}

				if(strlen($_POST['footer']) > '0')
				{
					$pdf_data['sapv_footer'] = $_POST['footer'];
				}
				else
				{
					$pdf_data['sapv_footer'] = $invoice_data['footer'];
				}
				$pdf_data['master_items'] = $invoice_data['items'];
				$pdf_data['client_ik'] = $this->view->client_ik;
				$pdf_data['patient_pflegestufe'] = $this->view->patient_pflegestufe;
				$pdf_data['insurance_no'] = $this->view->insurance_no;
				$pdf_data['clientid'] = $clientid;

				if($invoice_data['completed_date'] != '0000-00-00 00:00:00' && empty($pdf_data['completed_date']))
				{
					$pdf_data['completed_date'] = date('d.m.Y', strtotime($invoice_data['completed_date']));
				}
				else if(empty($pdf_data['completed_date']))
				{
					$pdf_data['completed_date'] = date('d.m.Y', strtotime($invoice_data['completed_date_sort']));
				}

				//sort items for pdf
				$pdf_data_sorted = array_keys($this->array_sort($pdf_data['invoice_items']['dates'], '0', 'SORT_ASC'));

				foreach($pdf_data_sorted as $k_item => $item_data)
				{
					$pdf_data['invoice_items']['total_sorted'][$item_data] = $pdf_data['invoice_items']['total'][$item_data];
				}
				
				if($_REQUEST['pdfdbgg'])
				{
					print_r($_POST);
					print_r("pdf data\n\n");
					print_r($pdf_data);
					exit;
				}
				
				//ISPC-2233
				// ITEMS 
				// Create Invoice array - and grid for printing
				$products_array = array();
				$kord = 0 ;
				$invoice_total = 0 ; 
                foreach($pdf_data['invoice_items']['type'] as $k_item => $v_item)
                {
                    if($v_item == "dp")
                    {
                        foreach($pdf_data['invoice_items']['dates'][$k_item] as $k_v_item => $v_v_item)
                        {
                            $products_array[$kord]['type'] = $v_item;
                            $datesstr = "";
                            foreach($pdf_data['invoice_items']['dates'][$k_item][$k_v_item] as $gr=>$dates){
                                if(count($dates) == "1"){
                                    //$datesstr .= date("D, d.m.Y",strtotime($dates[0]))."<br/>";
                                    $datesstr .= strftime("%a,", strtotime($dates[0])).' '.date("d.m.Y", strtotime($dates[0])) . "<br/>";
                                }else{
                                    //$datesstr .=  date("D, d.m.Y",strtotime($dates[0]))." - ".date("D, d.m.Y",strtotime($dates[1]))."<br/>";
                                    $datesstr .= strftime("%a,", strtotime($dates[0])).' '.date("d.m.Y", strtotime($dates[0])) . " - " . strftime("%a,", strtotime($dates[1])).' '.date("d.m.Y", strtotime($dates[1])) . "<br/>";
                                }
                            }
                            $products_array[$kord]['date_str'] = $datesstr ;
                            
                            $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item][$k_v_item];
                            $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item][$k_v_item];;
                            $products_array[$kord]['qty'] =$pdf_data['invoice_items']['qty'][$k_item][$k_v_item];
                            $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item][$k_v_item]);
                            $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item][$k_v_item]);
                        }
                    }
                    elseif($v_item == "gr") //handle insert of grouped items
                    {
                        foreach($pdf_data['invoice_items']['dates'][$k_item] as $k_v_item => $v_v_item)
                        {
                            $products_array[$kord]['type'] = $v_item;
                            $datesstr = "";
                            if(count($pdf_data['invoice_items']['dates'][$k_item]) == "1"){
                                //$datesstr .= date("D, d.m.Y",strtotime($pdf_data['invoice_items']['dates'][$k_item][0]))."<br/>";
                                $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . "<br/>";
                            }else{
                                //$datesstr .=  date("D, d.m.Y",strtotime($pdf_data['invoice_items']['dates'][$k_item][0]))." - ".date("D, d.m.Y",strtotime($pdf_data['invoice_items']['dates'][$k_item][1]))."<br/>";
                                $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])) . " - " . strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][1])) . "<br/>";
                            }
                            $products_array[$kord]['date_str'] = $datesstr ;
                        
                            $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item];
                            $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item];
                            $products_array[$kord]['qty'] =$pdf_data['invoice_items']['qty'][$k_item];
                            $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item]);
                            $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item]);
                            $invoice_total = $invoice_total+$products_array[$kord]['total'];
                        }
                	
                    }
                    //specific products or custom items
					// TODO-3012 Ancuta 20-23.03.2020 added also "le" type
                    else if($v_item == 'sp' || $v_item == 'sv' || $v_item == 'cu'  || $v_item == 'le')
                    {           
                        $products_array[$kord]['type'] = $v_item;
                        $datesstr ="";
                        //$datesstr .= date("D, d.m.Y",strtotime($pdf_data['invoice_items']['dates'][$k_item][0]));
                        $datesstr .= strftime("%a,", strtotime($pdf_data['invoice_items']['dates'][$k_item][0])).' '.date("d.m.Y", strtotime($pdf_data['invoice_items']['dates'][$k_item][0]));
                        if(!empty($pdf_data['invoice_items']['start_hours'][$k_item])){
                            foreach($pdf_data['invoice_items']['start_hours'][$k_item] as $shk=>$shs){
                                if($v_item == 'le'){
                                    $datesstr .= "<br/>".date("H:i",strtotime($shs))." Uhr <br/>";
                                } else{
                                    $datesstr .= "<br/>".date("H:i",strtotime($shs))." - ".date("H:i",strtotime($pdf_data['invoice_items']['end_hours'][$k_item][$shk])) ." Uhr <br/>";
                                }
                            }
                        }

                        $products_array[$kord]['date_str'] = $datesstr;
                        $products_array[$kord]['shortcut'] = $pdf_data['invoice_items']['code'][$k_item] ;
                        $products_array[$kord]['description'] = $pdf_data['invoice_items']['name'][$k_item];
                        $products_array[$kord]['qty'] =$pdf_data['invoice_items']['qty'][$k_item];
                        $products_array[$kord]['price'] = Pms_CommonData::str2num($pdf_data['invoice_items']['price'][$k_item]);
                        $products_array[$kord]['total'] = Pms_CommonData::str2num($pdf_data['invoice_items']['total'][$k_item]);
                    }
                    $kord++;
                }				
				
				$rows = count($products_array);
				$grid = new Pms_Grid($products_array, 1, $rows, "internal_invoice_items_list_pdf.html");
				$grid->invoice_total = $pdf_data['invoice_total'] ;
				$grid->max_entries = $rows;
				$html_items = $grid->renderGrid();
                // - End invoice items array				
				
				
				
				
				
				$template_data = InvoiceTemplates::get_template($clientid, false, '1', 'internal_invoice');
				
				if (isset($template_data[0])) {
				    	
				    $template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
				    	
				    $tokenfilter = array();
				
				    
				    $post = $pdf_data;
				    
				    $post['invoice_number'] = $post['prefix'] .$post['invoicenumber'];
				    $post['address'] = $post['healthinsurancename']
				    . PHP_EOL . $post['healthinsurancecontact']
				    . PHP_EOL . $post['healthinsurancestreet']
				    . PHP_EOL . $post['healthinsuranceaddress'];
				    	
				    $post['healthinsurance_debtor_number'] = $debtor_number;
				    	
				    $tokenfilter['invoice'] = $post;
				    $tokenfilter['invoice']['address'] = $post['invoice']['address'];
				    
				    $tokenfilter['invoice']['invoicenumber'] = $pdf_data['prefix'] .$pdf_data['invoice_number'];
				    $tokenfilter['invoice']['invoicedate'] = $pdf_data['completed_date'];
				    $tokenfilter['invoice']['invoicedate'] = $pdf_data['completed_date'];
				    $tokenfilter['invoice']['internal_invoice_items_html'] = $html_items;
				    
				    $tokenfilter['patient'] = $patient_details; // send all patient details
				    $tokenfilter['client'] = $client_details[0]; // send all client details
				    $tokenfilter['client']['client_ik'] = $client_details[0]['institutskennzeichen'];
				    $tokenfilter['user'] = $userid_details;
				    $tokenfilter['invoice']['benutzer_adresse'] = $pdf_data['user_details'];
				    $tokenfilter['invoice']['beneficiary_address'] = $pdf_data['user_details'];
				    
				    
				    
				    $tokenfilter['invoice'] ['invoiced_period'] = date("d.m.Y",strtotime($period['start'])).' - '.date("d.m.Y",strtotime($period['end']));
				    
				    $tokenfilter['default_tokens']['default_current_date'] = date("d.m.Y");

				    $this->_editinvoice_generate_pdf_and_download($template, $tokenfilter, "Invoice {$patient_details['epid']}");

				} else {
				    
    				$this->generate_pdf($pdf_data, "InternalInvoice", "internal_invoice_pdf.html", "P");
				}
				
				
			}
		}
		
		private function _editinvoice_generate_pdf_and_download($template = '', $tokenfilter = array(), $download_nice_name = 'Invoice')
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    //dd($tokenfilter);
		    //die(print_r(func_get_args(), true));
		    $docx_helper = $this->getHelper('CreateDocxFromTemplate');
		    $docx_helper->setTokenController('invoice');
		    //$docx_helper->test_all_invoice_templates($template, $tokenfilter , 'pdf');
		
		    $name = time();
		
		    $docx_helper->setOutputFile(PDFDOCX_PATH . '/' . $clientid . '/' . $name);
		
		    //do not add extension !
		    $docx_helper->setBrowserFilename($download_nice_name);
		    
		    if(APPLICATION_ENV == 'development'){
		      $docx_helper->create_docx ($template, $tokenfilter) ;
		    } else{
		      $docx_helper->create_pdf ($template, $tokenfilter) ;
		    }
		
		    $x = $docx_helper->file_save_on_ftp();
		    
		    // save $x if needed 
		
		    $docx_helper->download_file();
		}
		

		//ISPC-2609 + ISPC-2000 Ancuta - added clientid param
		public function _editinvoice_generate_pdf_and_download_pj($template = '', $tokenfilter = array(), $download_nice_name = 'Invoice', $clientid = false,$post_data = false)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    if(!$clientid){
		        $clientid = $logininfo->clientid;
		    }
		    //dd($post_data);
		    //dd($tokenfilter);
		    //die(print_r(func_get_args(), true));
		    
		    if($post_data){
		        $this->getRequest()->setParams($post_data);//ISPC-2609 Amcuta 26.09.2020
		    }
		    $docx_helper = $this->getHelper('CreateDocxFromTemplate');
		    //$docx_helper->test_all_invoice_templates($template, $tokenfilter , 'pdf');
		    
		    
		    $name = time();
		    if( $post_data['bulk_print'] == 1 && isset($post_data['batch_temp_folder'])) {
		        
		        /* $docx_helper->setOutputFile(PDFDOCX_PATH . '/' . $clientid . '/' . $name);
		        
		        
		        //do not add extension !
		        $docx_helper->setBrowserFilename($download_nice_name);
		        
		        if(APPLICATION_ENV == 'development'){
		        $docx_helper->create_docx ($template, $tokenfilter) ;
		        } else{
		        $docx_helper->create_pdf ($template, $tokenfilter) ;
		        }
		        $docx_helper->file_save_on_ftp();
		        
		        $docx_helper->download_file(); */
		        
		        
		        
		        $batch_temp_folder = $post_data['batch_temp_folder'];
		        
		        if(!is_dir(PDFDOCX_PATH))
		        {
		            while(!is_dir(PDFDOCX_PATH))
		            {
		                mkdir(PDFDOCX_PATH);
		                if($i >= 50)
		                {
		                    //exit; //failsafe
		                    break;
		                }
		                $i++;
		            }
		        }
		        
		        if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
		        {
		            while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
		            {
		                mkdir(PDFDOCX_PATH . '/' . $clientid);
		                if($i >= 50)
		                {
		                    //exit; //failsafe
		                    break;
		                }
		                $i++;
		            }
		        }
		        
		        
		        
		        if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
		        {
		            while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
		            {
		                mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
		                if($i >= 50)
		                {
		                    exit; //failsafe
		                }
		                $i++;
		            }
		        }
		        
		        
		        $destination_path = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/pdf_invoice_' . $post_data['unique_id'];
		        
		        
		        $docx_helper->setOutputFile($destination_path);
		        
		        
		        //do not add extension !
		        $docx_helper->setBrowserFilename($download_nice_name);
		        
		        $docx_helper->create_pdf ($template, $tokenfilter) ;
		        $docx_helper->file_save_on_ftp();
		        
		        return $destination_path;
		        
		        
		        
		        
		    } else {
		        
		        $docx_helper->setOutputFile(PDFDOCX_PATH . '/' . $clientid . '/' . $name);
		        
		        //             dd($download_nice_name,$name);
		        //do not add extension !
		        $docx_helper->setBrowserFilename($download_nice_name);
		        if(APPLICATION_ENV == 'development'){
		            $docx_helper->create_docx ($template, $tokenfilter) ;
		        } else{
		            $docx_helper->create_pdf ($template, $tokenfilter) ;
		        }
		        $docx_helper->file_save_on_ftp();
		        
		        $docx_helper->download_file();
		    }
		}
		
		
		
		public function invoicelistpaymentsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$this->_helper->viewRenderer->setNoRender();
			$internal_invoices = new InternalInvoices();
			$internal_invoices_payments = new InternalInvoicePayments();
			$internal_invoice_form = new Application_Form_InternalInvoices();

			$user = new User();

			if($_REQUEST['invoiceid'])
			{

				$payments = $internal_invoices_payments->getInvoicePayments($_REQUEST['invoiceid']);

				$users[] = '999999999999';
				foreach($payments as $k_payment => $v_payment)
				{
					$users[] = $v_payment['create_user'];
				}

				$users_list = $user->getMultipleUserDetails($users);

				foreach($users_list as $k_user => $v_user)
				{
					$users_list_details[$v_user['id']] = $v_user;
				}

				if($_REQUEST['op'] == 'del')
				{
					if(count($payments) == 1)
					{
						$next = '0';
					}
					else
					{
						$next = '1';
					}


					$del_payment = $internal_invoices_payments->delete_invoice_payment($_REQUEST['paymentid']);

					//update invoice status when deleting an payment
					if($del_payment)
					{
						$invoice_payments_sum = $internal_invoices_payments->getInvoicesPaymentsSum(array($_REQUEST['invoiceid']));
						$invoice_details = $internal_invoices->get_invoice($_REQUEST['invoiceid']);

						if($invoice_payments_sum)
						{
							if($invoice_payments_sum[$_REQUEST['invoiceid']]['paid_sum'] >= $invoice_details[0]['invoice_total'])
							{
								$status = '3'; //paid
							}
							else
							{
								$status = '5'; //not paid/partial paid
							}
						}
						else
						{
							//no payments => draft
							$status = '2';
						}
						$update_status = $internal_invoice_form->ToggleStatusInvoices(array($_REQUEST['invoiceid']), $status);
					}

					//reload the payments
					unset($payments);
					$payments = $internal_invoices_payments->getInvoicePayments($_REQUEST['invoiceid']);
				}

				$this->view->payments = $payments;
				$this->view->users_list = $users_list_details;
				$payments_list = $this->view->render('internalinvoice/invoicelistpayments.html');
				echo $payments_list;

				exit;
			}
			else
			{
				exit;
			}
		}

		public function invoicejournalAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$client = new Client();
			$client_data = $client->getClientDataByid($clientid);
			$this->view->client_details = $client_data;

			$internal_invoices = new InternalInvoices();
			$invoiced_users = $internal_invoices->get_internal_invoices_users($clientid);

			$modules = new Modules();
			$client_modules = $modules->get_client_modules();
			$this->view->client_modules = $client_modules;
			

			$client_user_array = User::getMultipleUserDetails($invoiced_users);

			foreach($client_user_array as $k => $us_val)
			{
				$user_details[$us_val['id']]['name'] = $us_val['last_name'] . ', ' . $us_val['first_name'];
				$user_select[$us_val['id']] = $us_val['last_name'] . ', ' . $us_val['first_name'];
			}

			$this->view->user_filter = $user_select;



			$period['start'] = date('Y-m-', time()) . '01';
			$period['end'] = date('Y-m-', time()) . date('t', time());

			$this->view->period = $period;

			$del_storno = 0;
			if($_REQUEST['mode'] == 'storno' && strlen($_REQUEST['inv_id']) > '0')
			{
				$invoice_arr = explode('-', $_REQUEST['inv_id']);
				$case = $invoice_arr[0];
				$invoiceid = $invoice_arr[1];

				if(strlen($invoice_arr[2]) > 0)
				{
					$del_storno = $invoice_arr[2];
				}

				if($del_storno == '0')
				{

					$internal_invoices = new InternalInvoices();
					$clone_record = $internal_invoices->create_storno_invoice($invoiceid);
				}
				else if($del_storno == '1')
				{
					$internal_invoices = new InternalInvoices();
					$clone_record = $internal_invoices->del_storno_invoice($invoiceid);
				}

				exit;
			}
		}

		public function fetchinvoicejournalAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->_helper->layout->setLayout('layout_ajax');
			$this->view->hidemagic = $hidemagic;
			$clientinfo = Pms_CommonData::getClientData($logininfo->clientid);
			$limit = 50;
			$this->view->limit = $limit;


			//offset
			if(strlen($_REQUEST['page']) == 0)
			{
				$offset = 0;
			}
			else
			{
				$offset = ($_REQUEST['page'] - 1) * $limit;
			}

			//current page
			if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
			{
				$current_page = $_REQUEST['page'];
			}
			else
			{
				$current_page = 1;
			}

			//sort direction
			if($_REQUEST['sort'] == 'asc')
			{
				$sort = 'asc';
			}
			else
			{
				$sort = 'desc';
			}

			if(strlen($_REQUEST['j_sortdir']) > 0)
			{
				$sort = $_REQUEST['j_sortdir'];
			}

			if(strlen($_REQUEST['ord']) > '0')
			{
				$order_by = $_REQUEST['ord'];
			}

			if(strlen($_REQUEST['j_sortby']) > '0')
			{
				$order_by = $_REQUEST['j_sortby'];
			}

			if(strlen($_REQUEST['j_sortby']) == '0' && strlen($_REQUEST['sortby']) == '0')
			{
				$order_by = 'inv_date';
			}

			//selected period
			if(strlen($_REQUEST['start_date']) == 0)
			{
				//get current month default
				$period['start'] = date('Y-m-', time()) . '01';
				$period['end'] = date('Y-m-', time()) . date('t', time());

				$item_period['start'] = date('Y-m-', time()) . '01';
				$item_period['end'] = date('Y-m-', time()) . date('t', time());
			}
			else
			{
				//get requested period
				$period['start'] = $_REQUEST['start_date'];
				$period['end'] = $_REQUEST['end_date'];

				$item_period['start'] = $_REQUEST['item_start_date'];
				$item_period['end'] = $_REQUEST['item_end_date'];
			}



			$where = "";
			//		Make sure we have client id selected
			if($logininfo->clientid > 0)
			{
				$where = "and e.clientid=" . $logininfo->clientid;
			}
			else
			{
				$where = 'and e.clientid =1';
			}

			//search filter data
			if(strlen($_REQUEST['invoice_number']) != '0')
			{
				$filter_data['invoice_number'][] = $_REQUEST['invoice_number'];
			}

			if(strlen($_REQUEST['storno']) != '0')
			{
				$filter_data['storno'][] = $_REQUEST['storno'];
			}

			$found_ipids[] = '99999999999';
			if(strlen($_REQUEST['health_insurance']) != '0')
			{

				//do health insurance search
				$drop = Doctrine_Query::create()
					->select('*')
					->from('HealthInsurance')
// 					->where("trim(lower(name)) like trim(lower('%" . $_REQUEST['health_insurance'] . "%'))")
					->where("trim(lower(name)) like ?", "%".trim($_REQUEST['health_insurance'])."%")
					->andWhere('isdelete="0"')
//				->andWhere('extra="0"') //extra 0 = no custom
					->andWhere('clientid="' . $logininfo->clientid . '" or clientid="0"')
					->orderBy('id ASC');

				$droparray = $drop->fetchArray();

				$hi_master_ids[] = '99999999';
				foreach($droparray as $k_hi_drop => $v_hi_drop)
				{
					$hi_master_ids[] = $v_hi_drop['id'];
				}

				$sql_hi = "ipid,AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
				$sql_hi.=",insurance_no as insurance_no";
				$sql_hi.=",institutskennzeichen as institutskennzeichen";
				$sql_hi.=",kvk_no as kvk_no, companyid";
				$sql_hi.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
				$sql_hi.=",privatepatient as privatepatient";
				$sql_hi.=",direct_billing as direct_billing";
				$sql_hi.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";

				$hi_pat = Doctrine_Query::create()
					->select($sql_hi)
					->from('PatientHealthInsurance')
					->whereIn('companyid', $hi_master_ids);
				$hi_pat_arr = $hi_pat->fetchArray();


				foreach($hi_pat_arr as $k_pat_hi => $v_pat_hi)
				{
					$found_ipids[] = $v_pat_hi['ipid'];
					$patient_health_insu[$v_pat_hi['ipid']] = $v_pat_hi['company_name'];
				}

				//			$this->view->patient2healthinsurance = $patient_health_insu;
			}

			//patient name search
			if(strlen($_REQUEST['patient_name']) != '0' || strlen($_REQUEST['health_insurance']) != '0')
			{
				//do patient search
				$whereln = " and (trim(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['patient_name'] . "%')) or trim(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) like trim(lower('" . $_REQUEST['patient_name'] . "%'))  or concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['patient_name'] . "%')) or
					concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),', ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['patient_name'] . "%')) or
					concat(lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['patient_name'] . "%')) or
					concat(lower(convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)),' ',lower(convert(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1))) LIKE trim(lower('%" . $_REQUEST['patient_name'] . "%')))";

				$resulted_patients_ipids[] = '99999999999';
				$patient = Doctrine_Query::create()
					->select('p.ipid, e.epid')
					->from('PatientMaster p')
					->where(" p.isdelete = 0");
				if(strlen($_REQUEST['health_insurance']) != '0' && count($found_ipids) > '0')
				{
					$patient->andWhereIn('ipid', $found_ipids);
				}

				$patient->leftJoin("p.EpidIpidMapping e");
				$patient->andwhere("e.clientid = " . $logininfo->clientid . " " . $wherefn . " " . $whereln);

				$resulted_patients_data = $patient->fetchArray();

				foreach($resulted_patients_data as $k_res_ipid => $v_res_ipid)
				{
					$resulted_patients_ipids[] = $v_res_ipid['ipid'];
				}
			}


			if(strlen($_REQUEST['amount']) != '0')
			{
				$filter_data['invoice_total'][] = $_REQUEST['amount'];
			}


			if(strlen($_REQUEST['user']) != '0')
			{
				$filter_data['user'][] = $_REQUEST['user'];
			}


			//get client invoice data
			//only used for client invoices data
			$filter_data['completed_date']['start_date'] = $period['start'];
			$filter_data['completed_date']['end_date'] = $period['end'];



			if(strlen($_REQUEST['item_date_filter']) != '0')
			{
				$filter_data['item_date']['item_start_date'] = $item_period['start'];
				$filter_data['item_date']['item_end_date'] = $item_period['end'];
			}

			$remove_drafts = true; // don't show drafts

			$internal_invoices = new InternalInvoices();
			$client_invoice_data_arr = $internal_invoices->get_all_client_internal_invoices($resulted_patients_ipids, $clientid, $filter_data, $offset, $limit, $order_by, $sort, $remove_drafts);

			$patients_details_ipids[] = '99999999999';
			$invoices_ids = array();
			foreach($client_invoice_data_arr as $k_client_inv => $v_client_inv)
			{
				$invoices_ids[] = $v_client_inv['id'];
				
				$patients_details_ipids[] = $v_client_inv['ipid'];
				$invoiced_users[] = $v_client_inv['user'];

				$client_invoice_data[$k_client_inv] = $v_client_inv;

				if($v_client_inv['storno'] == '1')
				{
					$client_invoice_data[$k_client_inv]['invoice_total'] = ($v_client_inv['invoice_total'] * -1);
				}

				if($v_client_inv['rnummer'])
				{
					$client_invoice_data[$k_client_inv]['invoice_number'] = $v_client_inv['rnummer'];
				}
				else
				{
					$client_invoice_data[$k_client_inv]['invoice_number'] = $v_client_inv['invoice_number'];
				}
			}


			//get all patients details
			$pat_details = Doctrine_Query::create()
				->select("*, e.epid as epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,
				AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,
				AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name")
				->from('PatientMaster p')
				->where('p.isdelete = 0 ' . $where)
				->andWhereIn('p.ipid', $patients_details_ipids);
			$pat_details->leftJoin("p.EpidIpidMapping e");
			$pat_details->andWhere('p.ipid = e.ipid');

			$pat_details_res = $pat_details->fetchArray();
			foreach($pat_details_res as $k_pat_det => $v_pat_det)
			{
				$patient_details[$v_pat_det['ipid']] = $v_pat_det;
			}

			if(strlen($_REQUEST['health_insurance']) == '0')
			{
				//get patients health insurance *listing only*
				$sql_hi = "ipid,AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as insurance_status";
				$sql_hi.=",insurance_no as insurance_no";
				$sql_hi.=",institutskennzeichen as institutskennzeichen";
				$sql_hi.=",kvk_no as kvk_no, companyid";
				$sql_hi.=",rezeptgebuhrenbefreiung as rezeptgebuhrenbefreiung";
				$sql_hi.=",privatepatient as privatepatient";
				$sql_hi.=",direct_billing as direct_billing";
				$sql_hi.=",AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name";

				$hi_pat = Doctrine_Query::create()
					->select($sql_hi)
					->from('PatientHealthInsurance')
					->whereIn('ipid', $patients_details_ipids);
				$hi_pat_arr = $hi_pat->fetchArray();
				$health_insurances[] = '99999999';
				foreach($hi_pat_arr as $k_hi_pat => $v_hi_pat)
				{
					$health_insurances[] = $v_hi_pat['companyid'];

					$ipid2healthinsurance[$v_hi_pat['ipid']] = $v_hi_pat['companyid'];
				}

				$drop = Doctrine_Query::create()
					->select('*')
					->from('HealthInsurance')
					->whereIn('id', $health_insurances)
					->andWhere('isdelete="0"')
					->andWhere('clientid="' . $logininfo->clientid . '" or clientid="0"')
					->orderBy('id ASC');
				$droparray = $drop->fetchArray();

				foreach($droparray as $k_hi_drop => $v_hi_drop)
				{
					$hi_array[$v_hi_drop['id']] = $v_hi_drop;
				}

				foreach($ipid2healthinsurance as $k_hi_ipid => $k_company)
				{
					$patients_health_insurances[$k_hi_ipid] = $hi_array[$k_company]['name'];

					if(array_key_exists($k_hi_ipid, $patient_details) && strlen($hi_array[$k_company]['name']) > 0)
					{
						$patient_details[$k_hi_ipid]['health_insurance'] = $hi_array[$k_company]['name'];
					}
				}
			}
			else
			{
				foreach($patient_health_insu as $k_ipid => $v_ipid)
				{
					if(array_key_exists($k_ipid, $patient_details))
					{
						$patient_details[$k_ipid]['health_insurance'] = $patient_health_insu[$k_ipid];
					}
				}
			}



			$client_user_array = User::getMultipleUserDetails($invoiced_users);
			foreach($client_user_array as $k => $us_val)
			{
				$user_details[$us_val['id']]['name'] = $us_val['last_name'] . ', ' . $us_val['first_name'];
			}

			//insert healthinsurance and patient name in invoice master data
			$patient_of_invoice = array();
			$user_of_invoice = array();
			foreach($client_invoice_data as $kc_inv_data => $vc_inv_data)
			{
				$client_invoice_data[$kc_inv_data]['health_insurance'] = $patient_details[$vc_inv_data['ipid']]['health_insurance'];
				$client_invoice_data[$kc_inv_data]['patient_name'] = $patient_details[$vc_inv_data['ipid']]['last_name'] . ', ' . $patient_details[$vc_inv_data['ipid']]['first_name'];
				$client_invoice_data[$kc_inv_data]['user_name'] = $user_details[$vc_inv_data['user']]['name'];
				
				$patient_of_invoice[$vc_inv_data['id']]['name'] = $patient_details[$vc_inv_data['ipid']]['last_name'] . ', ' . $patient_details[$vc_inv_data['ipid']]['first_name'];;
				$patient_of_invoice[$vc_inv_data['id']]['ipid'] = $vc_inv_data['ipid'];
				$patient_of_invoice[$vc_inv_data['id']]['epid'] = $patient_details[$vc_inv_data['ipid']]['epid'];
				
				$user_of_invoice[$vc_inv_data['id']]['id'] = $vc_inv_data['user'];
				$user_of_invoice[$vc_inv_data['id']]['user_name'] = $user_details[$vc_inv_data['user']]['name'];
				
				$date_of_invoice[$vc_inv_data['id']]  = date("d.m.Y",strtotime( $vc_inv_data['completed_date']));
				
				//ISPC-2312 Lore 08.12.2020
				$client_invoice_data[$kc_inv_data]['birthd'] = date("d.m.Y",strtotime($patient_details[$vc_inv_data['ipid']]['birthd']));
				$client_invoice_data[$kc_inv_data]['year_birthd'] = date("Y",strtotime($patient_details[$vc_inv_data['ipid']]['birthd']));
				
				
			}

			if($order_by == 'inv_hi')
			{
				$client_invoice_data = $this->array_sort($client_invoice_data, 'health_insurance', SORT_ . strtoupper($sort));
			}

			if($order_by == 'inv_pat')
			{
				$client_invoice_data = $this->array_sort($client_invoice_data, 'patient_name', SORT_ . strtoupper($sort));
			}
			//ISPC-2312 Lore 08.12.2020
			if($order_by == 'inv_birthd')
			{
			    $client_invoice_data = $this->array_sort($client_invoice_data, 'year_birthd', SORT_ . strtoupper($sort));
			}
			//		print_r($client_invoice_data);exit;
			$this->view->master_invoices_data = $client_invoice_data;

			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$post['invoice_data'] = $client_invoice_data;
				$post['clientinfo'] = $clientinfo[0];

				if($_POST['export_type'] == "csv")
				{
					$i = 1;
					
					foreach($client_invoice_data as $k => $export_values)
					{

					    
						$export_data[$i][$export_values['ipid']]['create_date_year'] = date('Y', strtotime($export_values['completed_date']));
						$export_data[$i][$export_values['ipid']]['create_date_month'] = date('m', strtotime($export_values['completed_date']));
						$export_data[$i][$export_values['ipid']]['invoice_date'] = date('d.m.Y', strtotime($export_values['completed_date']));

						if($export_values['invoice_total'] != "0.00")
						{
							$export_data[$i][$export_values['ipid']]['invoice_amount'] = number_format($export_values['invoice_total'], 2, ',', '');
						}
						else
						{
							$export_data[$i][$export_values['ipid']]['invoice_amount'] = "0";
						}

						$export_data[$i][$export_values['ipid']]['patient_name'] = $export_values['patient_name'];
						$export_data[$i][$export_values['ipid']]['patient_birth'] = $export_values['birthd'];  // ISPC-2312 Lore 08.12.2020
						$export_data[$i][$export_values['ipid']]['useer_name'] = $export_values['user_name'];
						$export_data[$i][$export_values['ipid']]['patient_number'] = strtoupper($patient_details[$export_values['ipid']]['epid']);
						$export_data[$i][$export_values['ipid']]['invoice_number'] = $export_values['invoice_number'];
						$export_data[$i][$export_values['ipid']]['invoice_dummy_number'] = "9999";
						$export_data[$i][$export_values['ipid']]['health_insurance'] = $export_values['health_insurance'];

						$i++;
					}

					$this->generateCSV($export_data, 'Rechnungsjournal.csv');
					exit;
				}

				else if($_POST['export_type'] == "bw_internal_csv")
				{
				    
				    $patientmaster = new PatientMaster();
					$i = 1;
 
					// get all items
					if(empty($invoices_ids)){
					    $this->_redirect(APP_BASE . "internalinvoice/invoicejournal");
					}
// 					$invoices_ids = array('3953');
					
					$InternalInvoices_obj = new InternalInvoices();
					$inv_ar = $InternalInvoices_obj->getMultipleInternalInvoice($invoices_ids,$clientid);
                    
					
					$invoice_items  = array();
					foreach($inv_ar as $k=>$invoice_values){
					    $invoice_items[$invoice_values['id']] = $invoice_values['items'];
					    
					    
					}
					
// 					if($userid == "338"){
// 					    echo "<pre>";
// 					    print_r($inv_ar);
// 					    print_r($invoice_items);
// 					    exit;
// 					}
					
					
					$i = 0;
					//Header
					$export_data[$i]["header"]['patient_number']  = $this->view->translate('bw_invoice_patient_number');
					$export_data[$i]["header"]['patient_name']  = $this->view->translate('bw_invoice_patient_name');
					$export_data[$i]["header"]['invoice_number']  = $this->view->translate('bw_invoice_number');
					$export_data[$i]["header"]['product_date']  = $this->view->translate('bw_product_date');
					$export_data[$i]["header"]['invoice_user']  =  $this->view->translate('bw_user_invoice');
					$export_data[$i]["header"]['invoice_date']  =  $this->view->translate('bw_invoice_date');
					$export_data[$i]["header"]['product_name']  =  $this->view->translate('bw_product_name');
					$export_data[$i]["header"]['product_amount']  =  $this->view->translate('bw_product_ammount');
					$export_data[$i]["header"]['product_price']  =  $this->view->translate('bw_product_price');
					
					$i++;
					$current_invoice_items = array();
					foreach($inv_ar as $in_id=>$inv_data){
   					    
					    $storno[$inv_data['id']] = ''; 
					    
					    $current_invoice_items = $inv_data['items'] ;
					    if($inv_data['storno'] == "1"){
					        $current_invoice_items = $invoice_items[$inv_data['record_id']];
    					    $storno[$inv_data['id']] = '-'; 
					    }
					     
					    
					    foreach($current_invoice_items as $item_k=>$item){
					        $item[$item['id']]['days'] = array();
					        
					        
					        if(!empty($item['periods'])){
					            
					            
					            if(isset($item['start_hours']) && !empty($item['start_hours'])){

					                foreach($item['start_hours'] as $start_k=>$datetime){
					                    
					                    if($item['end_hours'][$start_k] != "0000-00-00 00:00:00" && $item['end_hours'][$start_k] != "1970-01-01 01:00:00"){
					                        $item[$item['id']]['days'] = array_merge( $item[$item['id']]['days'],$patientmaster->getDaysInBetween($datetime, $item['end_hours'][$start_k]));
					                    } else {
					                        $item[$item['id']]['days'] = array_merge( $item[$item['id']]['days'],$patientmaster->getDaysInBetween($datetime,$datetime));
					                    }
					                }
					                
					            } else {
					                
        					        foreach($item['periods']['from_date'] as $per_k=>$date){
        					            if($item['periods']['till_date'][$per_k] != "0000-00-00 00:00:00" && $item['periods']['till_date'][$per_k] != "1970-01-01 01:00:00"){
        					              $item[$item['id']]['days'] = array_merge( $item[$item['id']]['days'],$patientmaster->getDaysInBetween($date, $item['periods']['till_date'][$per_k]));
        					            } else {
        					              $item[$item['id']]['days'] = array_merge( $item[$item['id']]['days'],$patientmaster->getDaysInBetween($date,$date));
        					            }
        					        }
					            }
    					        
					        }
					        
					        if(!empty($item[$item['id']]['days'])){
					            
					            foreach($item[$item['id']]['days'] as $action_date){
					                // if date in period
					                if(Pms_CommonData::isintersected(date("Y-m-d",strtotime($action_date)), date("Y-m-d",strtotime($action_date)),  
					                    date("Y-m-d",strtotime($_POST['item_start_date'])), 
					                    date("Y-m-d",strtotime($_POST['item_end_date'])))
					                    ){
					                
            					        $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['patient_number']  =  $patient_of_invoice[$inv_data['id']]['epid']; 
            					        $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['patient_name']  =  $patient_of_invoice[$inv_data['id']]['name']; 
            					        $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['invoice_number']  = $inv_data['prefix'].$inv_data['invoice_number']; 
            					        $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_date']  = date("d.m.Y",strtotime($action_date)); 
          					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['invoice_user']  = $user_of_invoice[$inv_data['id']]['user_name']; 
          					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['invoice_date']  = $date_of_invoice[$inv_data['id']]; 
          					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_name']  = $item['name'];
          					            
          					            
          					            if( 
          					                
          					                $item['type'] == "sp" 
          					                && $item['qty'] > 1 
          					                && (!isset($item['start_hours']) || empty($item['start_hours']))  
          					                ){
              					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_amount']  = $storno[$inv_data['id']].$item['qty']; 
          					            } else {
          					                $qty_per_day = $item['qty'] / count($item[$item['id']]['days']);
          					                
          					                if($item['type'] != "cu" &&  $qty_per_day == 1 ){
                  					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_amount']  = $storno[$inv_data['id']].$qty_per_day; // $item['qty']; // split amount per days  
          					                }
          					                 elseif($item['type'] == "cu") {
                  					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_amount']  = $storno[$inv_data['id']].$item['qty']; // $item['qty']; // split amount per days  
          					                }
          					                else 
          					                {
                  					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_amount']  = $storno[$inv_data['id']].$item['qty'].'  QTY ISSUE'; // $item['qty']; // split amount per days  
          					                }
          					                
          					            } 
          					            $export_data[$i][ $patient_of_invoice[$inv_data['id']]['ipid']]['product_price']  = number_format($item['price'], 2, ',', '.');
          					            $i++;
					                }
					            }
					        }
					    }
					}
 
// 					if($userid == "338"){
// 					    echo "<pre>";
// 					    print_r($export_data);
// 					    exit;
// 					}
					
					$this->generateCSV($export_data, 'Rechnungsjournal.csv');
					exit;
				}
							
				
				else if($_POST['export_type'] == "pdf")
				{
					$this->generate_pdf($post, "InvoiceJournal", "internal_invoicejournal_pdf.html", "p");
					exit;
				}
			}
		}

		public function generateCSV($data, $filename = 'export.csv')
		{
			$file = fopen('php://output', 'w');
			foreach($data as $key => $patient_data)
			{
				foreach($patient_data as $ipid => $values)
				{
					fputcsv($file, $values, ';', '"');
				}
			}
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-dType: application/octet-stream");
			header("Content-type: application/vnd.ms-excel; charset=utf-8");
			header("Content-Disposition: attachment; filename=" . $filename);
			exit;
		}

		private function generate_pdf($post_data, $pdfname, $filename, $orientation = false, $background_pages = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$pdf_names = array(
				// invoices
				'InvoiceJournal' => 'Rechnungsausgangsjournal',
				'InternalInvoicePdfs' => 'Internal Invoice Leistungsnachweis',
			);
			
			if($pdfname == 'InternalInvoice')
			{
			    if(strlen($post_data['invoice']['address']) > 0 ){
			        if(strpos($post_data['invoice']['address'],"style"))
			        {
			            $post_data['invoice']['address'] = preg_replace('/style=\"(.*)\"/i', '', $post_data['invoice']['address']);
			        }
			        $post_data['invoice']['address'] = str_replace(array("<p >","<p>"), "", $post_data['invoice']['address']);
			        $post_data['invoice']['address'] = str_replace("</p>", "<br/>", $post_data['invoice']['address']);
			    }
			    if(strlen($post_data['address']) > 0 ){
			        if(strpos($post_data['address'],"style"))
			        {
			            $post_data['address'] = preg_replace('/style=\"(.*)\"/i', '', $post_data['address']);
			        }
			        $post_data['address'] = str_replace(array("<p >","<p>"), "", $post_data['address']);
			        $post_data['address'] = str_replace("</p>", "<br/>", $post_data['address']);
			    }
			}
			
			if(is_array($filename))
			{
				foreach($filename as $k_file => $v_file)
				{
					$htmlform[$k_file] = Pms_Template::createTemplate($post_data, 'templates/' . $v_file);
					$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
				}
			}
			else
			{
				$htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);
				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
			}

			$pdf = new Pms_PDF('L', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->setDefaults(true); //defaults with header
			$pdf->setImageScale(1.6);
			$pdf->SetMargins(6, 5, 10); //reset margins
			$pdf->SetFont('dejavusans', '', 10);
			$pdf->setPrintFooter(false); // remove black line at bottom
			$pdf->SetAutoPageBreak(TRUE, 10);


			if($pdfname == 'InvoiceJournal')
			{
				if(strlen($post_data['start_date']) == 0)
				{
					//get current month default
					$post_data['start_date'] = date('Y-m-', time()) . '01';
					$post_data['end_date'] = date('Y-m-', time()) . date('t', time());
				}
				$pdf->SetMargins(10, 40, 10);
				$pdf->SetHeaderMargin(5);

				$header_text = '<table width="890" border="0">
                            <tr>
                                <td style="font-size: 14;" colspan="6"><strong>Rechnungsausgangsjournal</strong></td>
                                <td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['client_name'] . '</td>
                            </tr>
                            <tr>
                                <td style="font-size: 9;" colspan="2">Auswertung nach:</td>
                                <td style="font-size: 9;font-weight: bold;text-align: left;" colspan="2">Rechnungsdatum</td>
                                <td></td>
                                <td></td>
                                <td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2"></td>
                            </tr>
                            <tr>
                                <td style="font-size: 9;" colspan="2">Sortiert nach:</td>
                                <td style="font-size: 9;font-weight: bold;" colspan="2">' . $this->view->translate($post_data['j_sortby']) . '</td>
                                <td></td>
                                <td></td>
                                <td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['street1'] . $post_data['clientinfo']['street2'] . '</td>
                            </tr>
                            <tr>
                                <td style="font-size: 9;" colspan="2">Zeitraum:</td>
                                <td style="font-size: 9;font-weight: bold;" colspan="2">' . date('d.m.Y', strtotime($post_data['start_date'])) . ' - ' . date('d.m.Y', strtotime($post_data['end_date'])) . '</td>
                                <td></td>
                                <td></td>
                                <td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['city'] . '</td>
                            </tr>
                            <tr>
                                <td style="font-size: 9;" colspan="2">Druckdatum:</td>
                                <td style="font-size: 9;font-weight: bold;" colspan="2">' . date('d.m.Y H:i', time()) . '</td>
                                <td></td>
                                <td></td>
                                <td style="font-size: 9;" colspan="2"></td>
                            </tr>
                            <tr>
                                <td style="font-size: 9;" colspan="2">Seite:</td>
                                <td style="font-size: 9;font-weight: bold;text-align: left;" colspan="2">' . $pdf->getAliasNumPage() . '</td>
                                <td></td>
                                <td></td>
                                <td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">IK: ' . $post_data['clientinfo']['institutskennzeichen'] . '</td>
                            </tr>

                        </table>';

// 				$pdf->setHeaderFont(Array('arial', '', 11));
				$pdf->setHeaderFont(Array('dejavusans', '', 11));
				$pdf->HeaderText = $header_text;
			}


			//set page background for a defined page key in $background_pages array
			$bg_image = Pms_CommonData::getPdfBackground($post_data['clientid'], $pdf_type);
			if($bg_image !== false)
			{
				$bg_image_path = PDFBG_PATH . '/' . $post_data['clientid'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
				if(is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
			}

			if($_REQUEST['pdf_prev'])
			{
				print_r($html);
				exit;
			}
			if(is_array($html))
			{
				foreach($html as $k_html => $v_html)
				{
					if(is_array($orientation))
					{
						if(is_array($background_pages))
						{
							if(!in_array($k_html, $background_pages))
							{
								//unset page background for a nondefined page key in $background_pages array
								$pdf->setBackgroundImage();
							}
						}
						//each page has it`s own orientation
						$pdf->setHTML($v_html, $orientation[$k_html]);
					}
					else
					{
						//all pages one custom orientation
						$pdf->setHTML($v_html, $orientation);
					}
				}
			}
			else
			{
				if(empty($background_pages) && is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
				$pdf->setHTML($html, $orientation);
			}




// 			$tmpstmp = substr(md5(time() . rand(0, 999)), 0, 12);
// 			mkdir('uploads/' . $tmpstmp);
			$tmpstmp = $pdf->uniqfolder(PDF_PATH);
			
			
			if($post_data['bulk_print'] == 1){
			   
			    $batch_temp_folder = $post_data['batch_temp_folder'];
			    
			    if(!is_dir(PDFDOCX_PATH))
			    {
			        while(!is_dir(PDFDOCX_PATH))
			        {
			            mkdir(PDFDOCX_PATH);
			            if($i >= 50)
			            {
			                //exit; //failsafe
			                break;
			            }
			            $i++;
			        }
			    }
			    
			    if(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid']))
			    {
			        while(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid']))
			        {
			            mkdir(PDFDOCX_PATH . '/' . $post_data['clientid']);
			            if($i >= 50)
			            {
			                //exit; //failsafe
			                break;
			            }
			            $i++;
			        }
			    }
			    
			    
			    
			    if(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder))
			    {
			        while(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder))
			        {
			            mkdir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder);
			            if($i >= 50)
			            {
			                exit; //failsafe
			            }
			            $i++;
			        }
			    }
			    
			    
			    $destination_path = PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder . '/pdf_invoice_' . $post_data['unique_id'].'.pdf';
			    
			    $pdf->toFile($destination_path);
			    
			    return $destination_path;
			    
			} else {
                $pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
			}


			//$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
			/*
			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "uploads/" . $tmpstmp . ".zip";
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
			*/
			//internal_invoices
			$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , strtolower(__CLASS__), null, true );
				
			ob_end_clean();
			ob_start();


			// ISPC-2472 @Ancuta 07.11.2019
			$invoice_number_full="";
			$invoice_number_full .=  (strlen($post_data['prefix']) > 0) ? $post_data['prefix'] : '';
			$invoice_number_full .= $post_data['invoice_number'];
				
			if(strlen($invoice_number_full) > 0 ){
			    $pdfname = $invoice_number_full;
			}
			// --
			
			$pdf->toBrowser($pdfname . '.pdf', 'D');
			exit;
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();
			//array with subproducts used in internal invoices
			$subproducts = array('normal', 'hosp_adm', 'hosp', 'hosp_dis', 'hospiz_adm', 'hospiz', 'hospiz_dis', 'standby', 'hosp_dis_hospiz_adm', 'hospiz_dis_hosp_adm');
			
			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($_REQUEST['pdfdbg'])
							{
								print_r("V2: \n");
								print_r($v2);
								print_r("\n");

							}
							if($k2 == $on)
							{
								if($on == 'date' || $on == 'discharge_date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter' || $on == '0' || $on == 'sort_date')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
							else if(in_array($k2, $subproducts) && $on == '0') //sort if product has multiple periods
							{
								if($_REQUEST['pdfdbg'])
								{
									print_r("V2: \n");
									print_r($v2);
									print_r("\n");

								}
								$sortable_array[$k] = strtotime($v2[$on][0]);
							}
						}
					}
					else
					{
						if($on == 'date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter')
						{
							$sortable_array[$k] = strtotime($v);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}
				if($_REQUEST['pdfdbg'])
				{
					print_r("sortable_array\n");
					print_r($sortable_array);
				}


				switch($order)
				{
					case 'SORT_ASC':
//						asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case 'SORT_DESC':
//						arsort($sortable_array);
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

		private function get_period_months($date1, $date2, $format = "Ym")
		{
			$time1 = strtotime(date('Y-m', strtotime($date1) . "-01")); //
			$time2 = strtotime($date2);
			$my = date('mY', $time2);

			while($time1 < $time2)
			{
				if(!in_array(date($format, $time1), $months))
				{
					$months[] = date($format, $time1);
				}
				$time1 = strtotime(' +1 month', $time1);
			}

			if(!in_array(date($format, $time2), $months))
			{
				$months[] = date($format, $time2);
			}
			return $months;
		}

		
		/**
		 * Is this used ? ? ?  
		 * ISPC-667 > it seams it was added for this project
		 */
		public function invoicecontrolAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$epid = Pms_CommonData::getEpid($ipid);
			$patientmaster = new PatientMaster();
			$tab_menus = new TabMenus();
			$client_details = new Client();

			/*			 * ******* Patient Information ************ */
			$patient_details = $patientmaster->getMasterData($decid, 0);
			$this->view->patient_details = $patient_details;

			$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
			$this->view->tabmenus = $tab_menus->getMenuTabs();
			/*			 * *************************************** */
			//RADU ADDED
			//construct cycle periods
			$cycles = $patientmaster->getTreatedDaysRealMultiple(array($ipid));
			$months = array();
			if(!empty($cycles[$ipid]['admissionDates']))
			{
				foreach($cycles[$ipid]['admissionDates'] as $key_adm => $v_adm)
				{
					if(!empty($cycles[$ipid]['dischargeDates'][$key_adm]['date']))
					{
						$start_with_discharge = date('Y-m-d', strtotime($cycles[$ipid]['admissionDates'][$key_adm]['date']));
						$end_with_discharge = date('Y-m-d', strtotime($cycles[$ipid]['dischargeDates'][$key_adm]['date']));

						$period_months = $this->get_period_months($start_with_discharge, $end_with_discharge, "Y-m");
						$months = array_merge($months, $period_months);
					}
					else
					{
						$start_without_discharge = date('Y-m-d', strtotime($cycles[$ipid]['admissionDates'][$key_adm]['date']));
						$end_without_discharge = date('Y-m-d', time());

						$period_months_till_now = $this->get_period_months($start_without_discharge, $end_without_discharge, "Y-m");
						$months = array_merge($months, $period_months_till_now);
					}
				}
			}
			else
			{
				$cycle_start_period = date('Y-m-d', strtotime($cycles[$ipid]['admission_date']));

				if(empty($cycles[$ipid]['discharge_date']))
				{

					$cycle_end_period = date('Y-m-d', time());
				}
				else
				{
					$cycle_end_period = date('Y-m-d', strtotime($cycles[$ipid]['discharge_date']));
				}


				$period_months = $this->get_period_months($cycle_start_period, $cycle_end_period, "Y-m");
				$months = array_merge($months, $period_months);
			}

			$months = array_values(array_unique($months));
			$months_array = array_values(array_unique($months));

			foreach($months as $k_month => $v_month)
			{
				if(!function_exists('cal_days_in_month'))
				{
					$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
				}
				else
				{
					$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
				}

				$months_details[$v_month]['start'] = $v_month . "-01";
				$months_details[$v_month]['days_in_month'] = $month_days;
				$months_details[$v_month]['end'] = $v_month . '-' . $month_days;

				$formated_v_month = date('m-Y', strtotime($months_details[$v_month]['start']));

				$month_select_array[$v_month] = $formated_v_month;
				$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
			}


//		overall month details
			$end_o_months = end($months);

			if(!function_exists('cal_days_in_month'))
			{
				$o_month_days = date('t', mktime(0, 0, 0, date("n", strtotime($end_o_months . "-01")), 1, date("Y", strtotime($end_o_months . "-01"))));
			}
			else
			{
				$o_month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($end_o_months . "-01")), date("Y", strtotime($end_o_months . "-01")));
			}
			$months_details[0]['start'] = $months[0] . '-01';
			$months_details[0]['end'] = $end_o_months . '-' . $o_month_days;

			//check if a month is selected START
			if(empty($_REQUEST['list']) && strlen($list) == 0)
			{
				$selected_month = $months_array[0];
			}
			else
			{
				if(strlen($list) == 0)
				{
					$list = $_REQUEST['list'];
				}
				if(array_key_exists($list, $month_select_array))
				{
					$selected_month = $list;
				}
			}
			//check if a month is selected END
			//construct month_selector START
			$attrs['onChange'] = 'changeMonth(this.value);';
			$attrs['class'] = 'select_month_internalinvoice';
			$attrs['id'] = 'select_month';

			$this->view->months_selector = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);
			//construct month_selector END
			//set current period to work with
			$period = $months_details[$selected_month];
			$this->view->month_days = $month_days_arr[str_replace('-', '', $selected_month)];

			$patient_locations = PatientLocation::getPatientLocationsPeriods($ipid, $period);
			$this->view->patient_locations = $patient_locations;



			//get client users + sadmins
			$user = new User();
			$client_users_arr = $user->getUserByClientid($clientid, '0', true);

			foreach($client_users_arr as $k_usr => $v_usr)
			{
				$client_users[$v_usr['id']] = $v_usr;
				$client_users_ids[] = $v_usr['id'];
			}

			//get client users associations
			$users_ids_associated = UsersAssociation::get_associated_user_multiple($client_users_ids);



			//invoice gather data start
			$internal_invoices_pricelist = new InternalInvoicePriceList();

			$day_products = new InternalInvoicesDayProducts();
			$specific_products = new InternalInvoicesSpecificProducts();
			$nholiday = new NationalHolidays();


			//get all client national holidays
			$national_holidays_arr = $nholiday->getNationalHoliday($clientid, $current_period['start'], true);

			foreach($national_holidays_arr as $k_natholiday => $v_natholiday)
			{
				$national_holidays[] = date('Y-m-d', strtotime($v_natholiday['NationalHolidays']['date']));
			}
			asort($national_holidays);
			$national_holidays = array_values($national_holidays);
			$this->view->national_holidays = $national_holidays;

			//get all client products in period
			$period_pricelist_products = $internal_invoices_pricelist->get_period_pricelist($period['start'], $period['end']);

			//apply sp product rules
			$specific_products_applied = $this->sp_rules($period_pricelist_products['sp'], $period, $ipid, $clientid, $users_ids_associated, $national_holidays);

			//apply dp product rules
			$day_products_applied = $this->dp_rules($period_pricelist_products['dp'], $period, $ipid, $clientid, $patient_details, $users_ids_associated, $national_holidays);


			//get users with products
			$product_users = array();

			$specific_products_users = array_keys($specific_products_applied);
			$day_products_users = array_keys($day_products_applied);

			if($specific_products_users)
			{
				$product_users = array_merge($product_users, $specific_products_users);
			}

			if($day_products_users)
			{
				$product_users = array_merge($product_users, $day_products_users);
			}

			foreach($product_users as $k_prod_user => $v_prod_user)
			{
				if(!array_key_exists($v_prod_user, $users_ids_associated))
				{
					$users_w_products[$v_prod_user] = $client_users[$v_prod_user]['last_name'] . ' ' . $client_users[$v_prod_user]['first_name'];
				}
				else
				{
					$users_w_products[$users_ids_associated[$v_prod_user]] = $client_users[$users_ids_associated[$v_prod_user]]['last_name'] . ' ' . $client_users[$users_ids_associated[$v_prod_user]]['first_name'];
				}
			}

			asort($users_w_products);

			foreach($users_w_products as $k_uwp => $v_uwp)
			{
				$prod_usrs_sorted[] = $k_uwp;
			}
			$this->view->products_users = $users_w_products;

			//get curent selected user
			if(strlen($_REQUEST['user']) > '0')
			{
				$requested_user = $_REQUEST['user'];
			}
			else
			{
				if(count($prod_usrs_sorted) > '0')
				{
					$requested_user = $prod_usrs_sorted[0];
				}
				else
				{
					$requested_user = '0';
				}
			}

			//get day items relevant periods
			foreach($day_products_applied as $k_userid => $v_products)
			{
				foreach($v_products as $k_prod_id => $prod_days)
				{
					$group = '0';
					foreach($prod_days['normal_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['normal_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['normal_days_periods'][$group][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group++;
						}
					}

					$group_h = '0';
					foreach($prod_days['hosp_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['hosp_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['hosp_days_periods'][$group_h][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_h++;
						}
					}

					$group_hi = '0';
					foreach($prod_days['hospiz_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['hospiz_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['hospiz_days_periods'][$group_hi][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_hi++;
						}
					}

					$group_s = '0';
					foreach($prod_days['standby_days'] as $k_day => $v_day)
					{
						$n_vday_ts = strtotime('+1 day', strtotime($v_day));
						$next_v_day_ts = strtotime($prod_days['standby_days'][$k_day + 1]);

						$day_products_applied[$k_userid][$k_prod_id]['standby_days_periods'][$group_s][] = $v_day;

						if($n_vday_ts != $next_v_day_ts)
						{
							$group_s++;
						}
					}
				}
			}

			$this->view->period_pricelist_products = $period_pricelist_products;


			$hide_control_table = true;
			if($requested_user != '0')
			{
				$this->view->requested_user = $requested_user;
				$this->view->specific_products = $specific_products_applied[$requested_user];
				$this->view->day_products = $day_products_applied[$requested_user];


				$dp_subproducts_days = array('normal_days', 'hosp_adm_days', 'hosp_days', 'hosp_dis_days', 'hospiz_adm_days', 'hospiz_days', 'hospiz_dis_days', 'standby_days', 'hosp_dis_hospiz_adm_days', 'hospiz_dis_hosp_adm_days');
				$this->view->dp_subproduct_days = $dp_subproducts_days;

				$hide_control_table = false;
			}

			$this->view->hide_control = $hide_control_table;

			if($this->getRequest()->isPost())
			{
				if($_POST['pdf'])
				{
					$pseudo_post['month_days'] = $this->view->month_days;
					$pseudo_post['patient_locations'] = $patient_locations;
					$pseudo_post['dp_subproduct_days'] = $dp_subproducts_days;
					$pseudo_post['national_holidays'] = $national_holidays;
					$pseudo_post['patient_details'] = $patient_details;
					$pseudo_post['period_pricelist_products'] = $period_pricelist_products;
					$pseudo_post['specific_products'] = $this->view->specific_products;
					$pseudo_post['day_products'] = $this->view->day_products;


					foreach($this->view->day_products as $k_dp_prods => $v_dp_prods)
					{
						foreach($v_dp_prods as $k_d_subproduct => $v_d_products)
						{
							if(in_array($k_d_subproduct, $this->view->dp_subproduct_days))
							{
								$totals['dp_' . $k_dp_prods] += $v_dp_prods[str_replace('_days', '', $k_d_subproduct)];
							}
						}
					}

					$pseudo_post['totals'] = $totals;
					$template_files = array('invoicecontrolpdf.html');
					$orientation = array('L');


					$this->generate_pdf($pseudo_post, "InternalInvoicePdfs", $template_files, $orientation, $background_pages);
				}
			}

			if($_REQUEST['xq'])
			{
				print_r($period_pricelist_products);
				print_r("specific_products_applied\n");
				print_r($specific_products_applied);

				print_r("day_products_applied\n");
				print_r($day_products_applied);
				exit;
			}
			//invoice gather data end
		}

		
		public function pricelistcopyAction(){
			$this->_helper->viewRenderer->setNoRender();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$p_lists = new InternalInvoicePriceList();
			
			
			if(!empty($_REQUEST['list'])){
				$list = $_REQUEST['list']; 
				$price_lists = $p_lists->get_lists($clientid);
				
				if(!empty($price_lists[$list])){
					
					$current_list = $price_lists[$list];
					// get all connected lists
					$specific_products = new InternalInvoicesSpecificProducts();
					$products['InternalInvoicesSpecificProducts'] = $specific_products->get_list_products($list, $clientid);

					$specific_v_products = new InternalInvoicesSpecificVisits();
					$products['InternalInvoicesSpecificVisits'] = $specific_v_products->get_list_products($list, $clientid);
					
					$specific_a_products = new InternalInvoicesActionProducts();
					$products['InternalInvoicesActionProducts'] = $specific_a_products->get_list_products($list, $clientid);
					
					$day_products = new InternalInvoicesDayProducts();
					$products['InternalInvoicesDayProducts']= $day_products->get_list_products($list, $clientid);
					
					if($_REQUEST['dbg'] == "1"){
					
						foreach($products as $table_name =>$prods){
							print_R($table_name.'-> '.count($prods));
							print_R("\n");
						}
						exit;
					}
							
					
					
					if(!empty($current_list)){
						$p_list = new InternalInvoicePriceList();
						$p_list->clientid = $clientid;
						$p_list->price_sheet = $current_list['price_sheet']." (Copy-)".date('d.m.Y H:i');
						$p_list->start = $current_list['start'];
						$p_list->end = $current_list['end'];
						$p_list->save();
						$new_list = $p_list->id;
					}
					
					if(!empty($new_list) && !empty($products) ){
						foreach($products as $table_name =>$prods){
							
							if($table_name == "InternalInvoicesActionProducts"){
								foreach($prods as $pr_id=>$pr_details){
									// insert in InternalInvoicesActionProducts
									
									
									$new_a_product = new $table_name();
									foreach($pr_details as $field=>$value){
										if($field == "list"){
											$new_a_product->list = $new_list;
										}elseif(!in_array($field,array('id','create_date','change_date','create_user','change_user','actions'))){
											$new_a_product->{$field} = $value;
										}
									}
									$new_a_product->save();
									$a_product_id = $new_a_product->id;
									
									if($a_product_id ){
									
										$actions_array = array();
										$search_key="";
										$action2products = array();
										foreach($pr_details['actions'] as $k => $action_id){
									
											$search_key = $new_list.$a_product_id.$action_id;
											if(!in_array($search_key,$actions_array)){
									
												$action2products[] = array(
														'client' => $clientid,
														'list' => $new_list,
														'product_id' => $a_product_id,
														'action_id' => $action_id
												);
												$actions_array[] = $search_key;
											}
										}
									
										if(!empty($action2products)){
											$collection = new Doctrine_Collection('InternalInvoicesAction2Products');
											$collection->fromArray($action2products);
											$collection->save();
										}
									}
								}
								
							} else{
								
								foreach($prods as $pr_k=>$pr_values){
									$new_product = new $table_name();
									foreach($pr_values as $field=>$value){
										if($field == "list"){
											$new_product->list = $new_list;
										}elseif(!in_array($field,array('id','create_date','change_date','create_user','change_user'))){
											$new_product->{$field} = $value;
										}
									}
									$new_product->save();
								}
							}
						}
					}
				}
				
				$this->_redirect(APP_BASE . "internalinvoice/pricelist");
				exit;
				
			} else{
				
			}
			
		}
		
		
		public function usershiftsassignmentAction(){
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $patient_master = new PatientMaster();
		    
		    if(empty($clientid))
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    // POST
		    $request = $this->getRequest();
		    if ($request->isPost()) {
		        
		        $post = $request->getPost();

		        
	            if(empty($_REQUEST['month'])){
	                $post_month = date("Y-m");
	            } else{
	                $post_month = $_REQUEST['month'];
	            }
	            
		        if(empty($post['form_data'])){
		            
		            $this->_redirect(APP_BASE . 'internalinvoice/usershiftsassignment?month=' . $post_month);
		            exit;
		        }
		        
		        $form = new Application_Form_Usershifts2patients();
		        $result = $form->save_form_data($clientid,$post);
		        
		        $this->_redirect(APP_BASE . 'internalinvoice/usershiftsassignment?month=' . $post_month);
		        exit;
    		    
		    }
		    else
		    {
		        
		        //RETRIVE DATA
    		    $data = array();

    		    //MONTHS ARRAY START
    		    $start_period = '2010-01-01';
//     		    $end_period = date('Y-m-d', time());
    		    $end_period = date('Y-m-d', strtotime('+2 months'));//TODO-1933 @Ancuta   16.11.2018
    		    $months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
    		    
    		    $data['current_month'][date("Y-m")] = date("Y-m");
    		    foreach($months_array as $mnth){
    		        $data['months'][$mnth]=$mnth;
    		    }
    		    //MONTHS ARRAY - END
    		    
    		    
    		    // SELECTED MONTH DETAILS
    		    $months_details = array();
    		    $selected_month = date('Y-m', time());
    		    
    		    if(isset($_REQUEST['month'])){
    		        $selected_month = $_REQUEST['month'];
    		    }
    		    $data['current_month'] = $selected_month;
    		    
    		    if(!function_exists('cal_days_in_month'))
    		    {
    		        $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
    		    }
    		    else
    		    {
    		        $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
    		    }
    		    
    		    $months_details[$selected_month]['selected_month'] = $selected_month;
    		    $months_details[$selected_month]['start'] = $selected_month . "-01";
    		    $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
    		    $months_details[$selected_month]['days_in_month'] = $month_days;
    		    $months_details[$selected_month]['month_days'] = $patient_master->getDaysInBetween( $months_details[$selected_month]['start'],  $months_details[$selected_month]['end']);
    		    
    		    $data['selected_month_details'] = $months_details[$selected_month];
    		    
    		    // SELECTED MONTH DETAILS - END
    		    
    		    
    		    
    		    
    		    
    		    
    		    // get all client users
    		    $data['users']  = array();
    		    $UserObj = new User();
    		    $client_users = $UserObj->get_AllByClientid($clientid,array('usercolor','groupid','isactive_date'));
    
    		    usort($client_users, array(new Pms_Sorter('last_name'), "_strcmp"));
    		    
    		    $data['users_select_box'] = array(""=>$this->view->translate('select user'));
    		    
    		    //ISPC-2510 Carmen 26.02.2020 add a new group other users that can be billed as doctors or nurses
    		    /* $usergroup = new Usergroup();
    		    $clientGroups = array();
    		    $clientGroups = $usergroup->getUserGroups(array('4','5'), $clientid);
    		    $doctors = array();
    		    $nurses  = array();

    		    $group_name = array();
    		    foreach($clientGroups as $k=>$grm_data){
    		        $group_name[$grm_data['id']] =  $grm_data['groupname'];
    		        if($grm_data['groupmaster'] == "4"){
                        $doctors[] = $grm_data['id'];
                        $allowed[] = $grm_data['id'];
    		        }
    		        if($grm_data['groupmaster'] == "5"){
                        $nurses[] = $grm_data['id'];
                        $allowed[] = $grm_data['id'];
    		        }
    		    } */
    		    
    		    $groupms = new GroupMaster();
    		    $groupmaster = $groupms->getGroupMaster();
    		    $groupmaster_ids = array_keys($groupmaster);
    		    	
    		    $usergroup = new Usergroup();
    		    $clientGroups = array();
    		    $clientGroups = $usergroup->getUserGroups($groupmaster_ids, $clientid);
    		    
    		    $doctors = array();
    		    $nurses  = array();
    		    $others = array();
    		    
    		    $group_name = array();
    		    foreach($clientGroups as $k=>$grm_data){
    		    	$group_name[$grm_data['id']] =  $grm_data['groupname'];
    		    	if($grm_data['groupmaster'] == "4"){
    		    		$doctors[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    	elseif($grm_data['groupmaster'] == "5"){
    		    		$nurses[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    	else 
    		    	{
    		    		$group_name['others'] =  $this->view->translate('other_users');
    		    		$others[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    }
    		    
    		    //ISPC-2510 Carmen 08.01.2020
    		    //get users to be billed setted up in mandanten
    		    $shuser = new ShShiftsInternalUsers();
    		    $shinternal_users_ids = $shuser->get_shinternal_users($clientid);
    		    
    		    
    		    $user_select[0] =  $this->view->translate('please_select_user');

    		    
    		    $data['doctor_users'] = array();
    		    $data['nurse_users'] = array();
    		    foreach($client_users as $k=>$udata){
    		        //TODO-3929 Ancuta 08.03.2021
    		        $udata['nice_name'] =  str_replace("'"," ",$udata['nice_name']);
    		        $udata['first_name'] =  str_replace("'"," ",$udata['first_name']);
    		        $udata['last_name'] =  str_replace("'"," ",$udata['last_name']);
    		        //--

    		        if($udata['isdelete'] == "0" && in_array($udata['groupid'],$allowed) )
    		        {
    		            
   		                
   		                $all_users[$udata['id']] = $udata;
    		            if( empty($udata['usercolor'])){
        		            $all_users[$udata['id']]['usercolor'] =   "69d620";// Some color added by Ancuta! 
    		            }
    		            if(in_array($udata['groupid'],$doctors)){
    		                $data['doctor_users'][] =  $udata['id'];
    		                $all_users[$udata['id']]['system_group'] = "doctor";
    		            }
    		            
    		            if(in_array($udata['groupid'],$nurses)){
    		                $data['nurse_users'][] =  $udata['id'];
    		                $all_users[$udata['id']]['system_group'] = "pflege";
    		            }
    		            
    		            if(in_array($udata['groupid'],$others)){
    		            	$data['other_users'][] =  $udata['id'];
    		            	$all_users[$udata['id']]['system_group'] = "other";
    		            }
    		            
    		            if($udata['isactive'] == 1 && $udata['isactive_date'] != "0000-00-00"){
    		                $all_users[$udata['id']]['valid_till_date'] = strtotime($udata['isactive_date']);
    		            }
    		            
    		            
    		            // Array for select
    		            //ISPC-2510 Carmen 08.01.2020
    		            //if($udata['isactive'] == 0){
    		            if($udata['isactive'] == 0 && in_array($udata['id'], $shinternal_users_ids)){
    		            	if(in_array($udata['groupid'],$others))
    		            	{
    		            		$user_select[$group_name['others']][$udata['id']] = $udata['nice_name'];
    		            	}
    		            	else 
    		            	{
    		                
    		            		$user_select[$group_name[$udata['groupid']]][$udata['id']] = $udata['nice_name'];
    		            	}
    		            }
    		            elseif($udata['isactive'] == 1 && in_array($udata['id'], $shinternal_users_ids)){
	    		            if (isset($udata['isactive_date'] ) && $udata['isactive_date'] != "0000-00-00") {
	    		            	if(in_array($udata['groupid'],$others))
	    		            	{
	    		            		$user_select[$group_name['others']][$udata['id']] = $udata['nice_name'];
	    		            	}
	    		            	else
	    		            	{
	    		            		$user_select[$group_name[$udata['groupid']]][$udata['id']] = $udata['nice_name'];
	    		            	}
	    		                if( strtotime($months_details[$selected_month]['start']) >= strtotime($udata['isactive_date']) ){
	    		                    unset ($user_select[$group_name[$udata['groupid']]][$udata['id']]);
	    		                } elseif(strtotime($months_details[$selected_month]['end']) >= strtotime($udata['isactive_date'])) { 
	    		                	if(in_array($udata['groupid'],$others))
	    		                	{
	    		                		$user_select[$group_name['others']][$udata['id']] .= " (bis ". date("d.m.Y", strtotime($udata['isactive_date'])) .")";
	    		                	}
	    		                	else
	    		                	{
	                              		$user_select[$group_name[$udata['groupid']]][$udata['id']] .= " (bis ". date("d.m.Y", strtotime($udata['isactive_date'])) .")";
	    		                	}
	    		                }
	    		            }
    		            }
    		        }
    		    }
    		    
    		    $data['users'] = $all_users;
    		    $data['users_select_box'] = $user_select;



                //NATIONAL HOLIDAYS
                $nh = new NationalHolidays();
                $national_holiday = $nh->getNationalHoliday($clientid, $months_details[$selected_month]['start'], true);
                
                foreach($national_holiday as $k_holiday => $v_holiday)
                {
                    $holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
                }
                $data['national_holidays'] = $holiday_dates;
                //NATIONAL HOLIDAYS -END
                
                
                
    		    // ACTIVE PATIENTS IN CURRENT MONTH
    		    $data['patients'] = array();
    		    $conditions = array();
    		    $patient_days = array();
    		    
    		    $conditions['periods'] = array('0' => array('start' => $months_details[$selected_month]['start'], 'end' => $months_details[$selected_month]['end']));
    		    $conditions['client'] = $clientid;
    		    $sql = 'e.epid, p.ipid, e.ipid,';
    		    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		    
    		    $user_patients_details = PatientUsers::getUserPatients($userid);
    		    if($user_patients_details['bypass'] != "1" && !empty($user_patients_details['patients'])){
    		        $conditions['ipids'] = array_keys($user_patients_details['patients']);
    		    }
    		    
    	
    		    //be aware of date d.m.Y format here
    		    $patient_days = Pms_CommonData::patients_days($conditions, $sql);
    		    $ipids = array_keys($patient_days);
    		    
    		    $data['patients'] = array();
    		    $data['epids'] = array();
    		    $data['epid2ipid'] = array();
    		    
    		    $patients_details_arr= array();
    		    foreach($patient_days as $ipid=>$pdata){
    		        
    		        $patients_details_arr[$ipid]['enc_id'] = Pms_Uuid::encrypt($pdata['details']['id']);
    		        $patients_details_arr[$ipid]['epid'] = $pdata['details']['epid'];
    		        $patients_details_arr[$ipid]['ipid'] = $ipid;
    		        $patients_details_arr[$ipid]['epid2ipid'] = $pdata['details']['epid'];
    		        $data['epids'][] = $pdata['details']['epid'];
    		        $data['epid2ipid'][$pdata['details']['epid']] = $ipid;
    		        $patients_details_arr[$ipid]['last_name'] = $pdata['details']['last_name']; 
    		        $patients_details_arr[$ipid]['first_name'] = $pdata['details']['first_name'];
    		        $patients_details_arr[$ipid]['nice_name'] = $pdata['details']['last_name'].', '.$pdata['details']['first_name'];
    		        
    		        $patients_details_arr[$ipid]['real_active_days'] = array_values($pdata['real_active_days']);
    		        
    		        foreach( $pdata['real_active_days'] as $k => $rday ){
    		           $patients_details_arr[$ipid]['active_dats_Ymd'][] = date('Y-m-d',strtotime($rday));
    		        }
    		    }
    		    

    		    //  TODO-2083 - ISPC:Behandlungsplan Sorting  Ancuta 28.01.2019
    		    
    		    usort($patients_details_arr, array(new Pms_Sorter('last_name'), "_strcmp"));
    		    
    		    $patients_details_sorted = array();
    		    foreach($patients_details_arr as $no_ipid_key => $patient_row){
    		        $patients_details[$patient_row['ipid']] = $patient_row;
    		    }
    		    // --
    		    $data['patients'] = $patients_details;
    		    // ACTIVE PATIENTS IN CURRENT MONTH - END

    		    
    		    //get assigned users (from groups_users)
    		    $assigned_users2patients = array();
    		    
    		    if ( ! empty($data['epids']) && ! empty($data['nurse_users']) ) {
    		        
    		        $fdoc = Doctrine_Query::create()
    		        ->select("*")
    		        ->from('PatientQpaLeading')
    		        ->whereIn("ipid", $ipids)
    		        ->andWhereIn("userid", $data['nurse_users'])
    		        ->andWhere('clientid = ?',$clientid)
    		        ->andWhere('ipid != ""');
    		        $star_users = $fdoc->fetchArray();
    		        
    		        
                    if( ! empty($star_users)){
    		            
    		            foreach( $star_users as $k => $suval){
    		                $star_users2patients[$suval['ipid']][] = $suval['userid'];
    		            }
    		            
        		        $assigned_usr = Doctrine_Query::create()
            		    ->select('*')
            		    ->from('PatientQpaMapping')
            		    ->whereIn('epid', $data['epids']);
            		    $assigned_users = $assigned_usr->fetchArray();
            		    
            		    if( !empty( $assigned_users ) )
            		    {
                		    foreach( $assigned_users as $k_usr => $v_usr )
                		    {
                		        if( 
                		            in_array($v_usr['userid'], $data['nurse_users']) 
                		            && in_array($v_usr['userid'], $star_users2patients[$data['epid2ipid'][$v_usr['epid']]]) 
                		            ){
                    		        $assigned_users2patients[$data['epid2ipid'][$v_usr['epid']]] =  $v_usr['userid'];
                		        }
                		    }
            		    }
    		        }        		    
    		    }

    		    $data['saved_data'] = array();
    		    
    		    // GET SAVED DATA
    		    $Usershifts2patients = new Usershifts2patients();
    		    $saved_data_array = $Usershifts2patients->get_saved_data($clientid,$months_details[$selected_month],$ipids);
    		    
    		    $saved_pre_month = array();
    		    
    		    if(!empty($saved_data_array)){
    		        foreach($saved_data_array  as $row_id => $row){
    		            $data['saved_data'][$patients_details[$row['ipid']]['epid']][$row['shift_type']][$row['shift_date']] = $row['userid'];
    		            
    		            $saved_pre_month[$patients_details[$row['ipid']]['epid']][date('Y-m',strtotime($row['shift_date']))] [$row['shift_type']] [] = $row['userid'];

    		        }
    		    }
    		    
    		    if( ! empty ($assigned_users2patients) ) {
        		    foreach ( $assigned_users2patients as $pipid => $star_user) {
        		        foreach($months_details[$selected_month]['month_days'] as $mday){
        		            if(in_array($mday,$patients_details[$pipid]['active_dats_Ymd'])){
        		                
        		                if(empty($saved_pre_month[$patients_details[$pipid]['epid']][date('Y-m',strtotime($mday))] ['pflege']))
        		                {
                                    $data['saved_data'][$patients_details[$pipid]['epid']]['pflege'][$mday] = $star_user;
        		                }
        		            }
        		        }
        		    }
    		    }

    		    $this->view->data = $data;
		    }
		}

		/**
		 * TODO-2696
		 * Ancuta 04.12.2019
		 * copy of fn usershiftsassignment
		 * 
		 */
		
		public function userassignmentAction(){
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $patient_master = new PatientMaster();
		    
		    if(empty($clientid))
		    {
		        $this->_redirect(APP_BASE . "error/previlege");
		        exit;
		    }
		    
		    // POST
		    $request = $this->getRequest();
		    if ($request->isPost()) {
		        
		        $post = $request->getPost();

		        
	            if(empty($_REQUEST['month'])){
	                $post_month = date("Y-m");
	            } else{
	                $post_month = $_REQUEST['month'];
	            }
	            
		        if(empty($post['form_data'])){
		            
		            $this->_redirect(APP_BASE . 'internalinvoice/userassignment?month=' . $post_month);
		            exit;
		        }
		        
		        $form = new Application_Form_Usershifts2patients();
		        $result = $form->save_form_data($clientid,$post);
		        
		        $this->_redirect(APP_BASE . 'internalinvoice/userassignment?month=' . $post_month);
		        exit;
    		    
		    }
		    else
		    {
		        
		        //RETRIVE DATA
    		    $data = array();

    		    //MONTHS ARRAY START
    		    $start_period = '2010-01-01';
//     		    $end_period = date('Y-m-d', time());
    		    $end_period = date('Y-m-d', strtotime('+2 months'));//TODO-1933 @Ancuta   16.11.2018
    		    $months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
    		    
    		    $data['current_month'][date("Y-m")] = date("Y-m");
    		    foreach($months_array as $mnth){
    		        $data['months'][$mnth]=$mnth;
    		    }
    		    //MONTHS ARRAY - END
    		    
    		    
    		    // SELECTED MONTH DETAILS
    		    $months_details = array();
    		    $selected_month = date('Y-m', time());
    		    
    		    if(isset($_REQUEST['month'])){
    		        $selected_month = $_REQUEST['month'];
    		    }
    		    $data['current_month'] = $selected_month;
    		    
    		    if(!function_exists('cal_days_in_month'))
    		    {
    		        $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
    		    }
    		    else
    		    {
    		        $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
    		    }
    		    
    		    $months_details[$selected_month]['selected_month'] = $selected_month;
    		    $months_details[$selected_month]['start'] = $selected_month . "-01";
    		    $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
    		    $months_details[$selected_month]['days_in_month'] = $month_days;
    		    $months_details[$selected_month]['month_days'] = $patient_master->getDaysInBetween( $months_details[$selected_month]['start'],  $months_details[$selected_month]['end']);
    		    
    		    $data['selected_month_details'] = $months_details[$selected_month];
    		    
    		    // SELECTED MONTH DETAILS - END
    		    
    		    
    		    
    		    
    		    
    		    
    		    // get all client users
    		    $data['users']  = array();
    		    $UserObj = new User();
    		    $client_users = $UserObj->get_AllByClientid($clientid,array('usercolor','groupid','isactive_date'));
    
    		    usort($client_users, array(new Pms_Sorter('last_name'), "_strcmp"));
    		    
    		    $data['users_select_box'] = array(""=>$this->view->translate('select user'));
    		    
		    	//ISPC-2510 Carmen 26.02.2020 add a new group other users that can be billed as doctors or nurses
    		    /* $usergroup = new Usergroup();
    		    $clientGroups = array();
    		    $clientGroups = $usergroup->getUserGroups(array('4','5'), $clientid);
    		    $doctors = array();
    		    $nurses  = array();

    		    $group_name = array();
    		    foreach($clientGroups as $k=>$grm_data){
    		        $group_name[$grm_data['id']] =  $grm_data['groupname'];
    		        if($grm_data['groupmaster'] == "4"){
                        $doctors[] = $grm_data['id'];
                        $allowed[] = $grm_data['id'];
    		        }
    		        if($grm_data['groupmaster'] == "5"){
                        $nurses[] = $grm_data['id'];
                        $allowed[] = $grm_data['id'];
    		        }
    		    } */
    		    
    		    $groupms = new GroupMaster();
    		    $groupmaster = $groupms->getGroupMaster();
    		    $groupmaster_ids = array_keys($groupmaster);
    		    	
    		    $usergroup = new Usergroup();
    		    $clientGroups = array();
    		    $clientGroups = $usergroup->getUserGroups($groupmaster_ids, $clientid);
    		    
    		    $doctors = array();
    		    $nurses  = array();
    		    $others = array();
    		    
    		    $group_name = array();
    		    foreach($clientGroups as $k=>$grm_data){
    		    	$group_name[$grm_data['id']] =  $grm_data['groupname'];
    		    	if($grm_data['groupmaster'] == "4"){
    		    		$doctors[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    	elseif($grm_data['groupmaster'] == "5"){
    		    		$nurses[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    	else 
    		    	{
    		    		$group_name['others'] =  $this->view->translate('other_users');
    		    		$others[] = $grm_data['id'];
    		    		$allowed[] = $grm_data['id'];
    		    	}
    		    }
    		    
    		    //ISPC-2510 Carmen 08.01.2020
    		    //get users to be billed setted up in mandanten
    		    $shuser = new ShShiftsInternalUsers();
    		    $shinternal_users_ids = $shuser->get_shinternal_users($clientid);
    		    
    		    
    		    $user_select[0] =  $this->view->translate('please_select_user');

    		    
    		    $data['doctor_users'] = array();
    		    $data['nurse_users'] = array();
    		    foreach($client_users as $k=>$udata){
    		        //TODO-3929 Ancuta 08.03.2021
    		        $udata['nice_name'] =  str_replace("'"," ",$udata['nice_name']);
    		        $udata['first_name'] =  str_replace("'"," ",$udata['first_name']);
    		        $udata['last_name'] =  str_replace("'"," ",$udata['last_name']);
    		        //--
    		        
    		        if($udata['isdelete'] == "0" && in_array($udata['groupid'],$allowed) )
    		        {
    		            
   		                
   		                $all_users[$udata['id']] = $udata;
    		            if( empty($udata['usercolor'])){
        		            $all_users[$udata['id']]['usercolor'] =   "69d620";// Some color added by Ancuta! 
    		            }
    		            if(in_array($udata['groupid'],$doctors)){
    		                $data['doctor_users'][] =  $udata['id'];
    		                $all_users[$udata['id']]['system_group'] = "doctor";
    		            }
    		            
    		            if(in_array($udata['groupid'],$nurses)){
    		                $data['nurse_users'][] =  $udata['id'];
    		                $all_users[$udata['id']]['system_group'] = "pflege";
    		            }
    		            
    		            if(in_array($udata['groupid'],$others)){
    		            	$data['other_users'][] =  $udata['id'];
    		            	$all_users[$udata['id']]['system_group'] = "other";
    		            }
    		            
    		            if($udata['isactive'] == 1 && $udata['isactive_date'] != "0000-00-00"){
    		                $all_users[$udata['id']]['valid_till_date'] = strtotime($udata['isactive_date']);
    		            }
    		            
    		            
    		        	// Array for select
    		            //ISPC-2510 Carmen 08.01.2020
    		            //if($udata['isactive'] == 0){
    		            if($udata['isactive'] == 0 && in_array($udata['id'], $shinternal_users_ids)){
    		            	if(in_array($udata['groupid'],$others))
    		            	{
    		            		$user_select[$group_name['others']][$udata['id']] = $udata['nice_name'];
    		            	}
    		            	else
    		            	{
    		            		$user_select[$group_name[$udata['groupid']]][$udata['id']] = $udata['nice_name'];
    		            	}
    		            }
    		            elseif($udata['isactive'] == 1 && in_array($udata['id'], $shinternal_users_ids)){
	    		            if (isset($udata['isactive_date'] ) && $udata['isactive_date'] != "0000-00-00") {
	    		            	if(in_array($udata['groupid'],$others))
	    		            	{
	    		            		$user_select[$group_name['others']][$udata['id']] = $udata['nice_name'];
	    		            	}
	    		            	else
	    		            	{
	    		            		$user_select[$group_name[$udata['groupid']]][$udata['id']] = $udata['nice_name'];
	    		            	}
	    		                if( strtotime($months_details[$selected_month]['start']) >= strtotime($udata['isactive_date']) ){
	    		                    unset ($user_select[$group_name[$udata['groupid']]][$udata['id']]);
	    		                } elseif(strtotime($months_details[$selected_month]['end']) >= strtotime($udata['isactive_date'])) { 
	    		                	if(in_array($udata['groupid'],$others))
	    		                	{
	    		                		$user_select[$group_name['others']][$udata['id']] .= " (bis ". date("d.m.Y", strtotime($udata['isactive_date'])) .")";
	    		                	}
	    		                	else
	    		                	{ 
	                              		$user_select[$group_name[$udata['groupid']]][$udata['id']] .= " (bis ". date("d.m.Y", strtotime($udata['isactive_date'])) .")";
	    		                	}
	    		                }
	    		            }
    		            }
    		        }
    		    }
    		    
    		    $data['users'] = $all_users;
    		    $data['users_select_box'] = $user_select;



                //NATIONAL HOLIDAYS
                $nh = new NationalHolidays();
                $national_holiday = $nh->getNationalHoliday($clientid, $months_details[$selected_month]['start'], true);
                
                foreach($national_holiday as $k_holiday => $v_holiday)
                {
                    $holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
                }
                $data['national_holidays'] = $holiday_dates;
                //NATIONAL HOLIDAYS -END
                
                
                
    		    // ACTIVE PATIENTS IN CURRENT MONTH
    		    $data['patients'] = array();
    		    $conditions = array();
    		    $patient_days = array();
    		    
    		    $conditions['periods'] = array('0' => array('start' => $months_details[$selected_month]['start'], 'end' => $months_details[$selected_month]['end']));
    		    $conditions['client'] = $clientid;
    		    $sql = 'e.epid, p.ipid, e.ipid,';
    		    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		    
    		    $user_patients_details = PatientUsers::getUserPatients($userid);
    		    if($user_patients_details['bypass'] != "1" && !empty($user_patients_details['patients'])){
    		        $conditions['ipids'] = array_keys($user_patients_details['patients']);
    		    }
    		    $conditions['limit'] = '100';
    	
    		    //be aware of date d.m.Y format here
    		    $patient_days = Pms_CommonData::patients_days($conditions, $sql);
    		    $ipids = array_keys($patient_days);
    		    
    		    $data['patients'] = array();
    		    $data['epids'] = array();
    		    $data['epid2ipid'] = array();
    		    
    		    $patients_details_arr= array();
    		    foreach($patient_days as $ipid=>$pdata){
    		        
    		        $patients_details_arr[$ipid]['enc_id'] = Pms_Uuid::encrypt($pdata['details']['id']);
    		        $patients_details_arr[$ipid]['epid'] = $pdata['details']['epid'];
    		        $patients_details_arr[$ipid]['ipid'] = $ipid;
    		        $patients_details_arr[$ipid]['epid2ipid'] = $pdata['details']['epid'];
    		        $data['epids'][] = $pdata['details']['epid'];
    		        $data['epid2ipid'][$pdata['details']['epid']] = $ipid;
    		        $patients_details_arr[$ipid]['last_name'] = $pdata['details']['last_name']; 
    		        $patients_details_arr[$ipid]['first_name'] = $pdata['details']['first_name'];
    		        $patients_details_arr[$ipid]['nice_name'] = $pdata['details']['last_name'].', '.$pdata['details']['first_name'];
    		        
    		        $patients_details_arr[$ipid]['real_active_days'] = array_values($pdata['real_active_days']);
    		        
    		        foreach( $pdata['real_active_days'] as $k => $rday ){
    		           $patients_details_arr[$ipid]['active_dats_Ymd'][] = date('Y-m-d',strtotime($rday));
    		        }
    		    }
    		    

    		    //  TODO-2083 - ISPC:Behandlungsplan Sorting  Ancuta 28.01.2019
    		    
    		    usort($patients_details_arr, array(new Pms_Sorter('last_name'), "_strcmp"));
    		    
    		    $patients_details_sorted = array();
    		    foreach($patients_details_arr as $no_ipid_key => $patient_row){
    		        $patients_details[$patient_row['ipid']] = $patient_row;
    		    }
    		    // --
    		    $data['patients'] = $patients_details;
    		    // ACTIVE PATIENTS IN CURRENT MONTH - END

    		    
    		    //get assigned users (from groups_users)
    		    $assigned_users2patients = array();
    		    
    		    if ( ! empty($data['epids']) && ! empty($data['nurse_users']) ) {
    		        
    		        $fdoc = Doctrine_Query::create()
    		        ->select("*")
    		        ->from('PatientQpaLeading')
    		        ->whereIn("ipid", $ipids)
    		        ->andWhereIn("userid", $data['nurse_users'])
    		        ->andWhere('clientid = ?',$clientid)
    		        ->andWhere('ipid != ""');
    		        $star_users = $fdoc->fetchArray();
    		        
    		        
                    if( ! empty($star_users)){
    		            
    		            foreach( $star_users as $k => $suval){
    		                $star_users2patients[$suval['ipid']][] = $suval['userid'];
    		            }
    		            
        		        $assigned_usr = Doctrine_Query::create()
            		    ->select('*')
            		    ->from('PatientQpaMapping')
            		    ->whereIn('epid', $data['epids']);
            		    $assigned_users = $assigned_usr->fetchArray();
            		    
            		    if( !empty( $assigned_users ) )
            		    {
                		    foreach( $assigned_users as $k_usr => $v_usr )
                		    {
                		        if( 
                		            in_array($v_usr['userid'], $data['nurse_users']) 
                		            && in_array($v_usr['userid'], $star_users2patients[$data['epid2ipid'][$v_usr['epid']]]) 
                		            ){
                    		        $assigned_users2patients[$data['epid2ipid'][$v_usr['epid']]] =  $v_usr['userid'];
                		        }
                		    }
            		    }
    		        }        		    
    		    }

    		    $data['saved_data'] = array();
    		    
    		    // GET SAVED DATA
    		    $Usershifts2patients = new Usershifts2patients();
    		    $saved_data_array = $Usershifts2patients->get_saved_data($clientid,$months_details[$selected_month],$ipids);
    		    
    		    
//     		    dd($saved_data_array);
    		    $saved_pre_month = array();
    		    
    		    if(!empty($saved_data_array)){
    		        foreach($saved_data_array  as $row_id => $row){
    		            $data['saved_data'][$patients_details[$row['ipid']]['epid']][$row['shift_type']][$row['shift_date']] = $row['userid'];
    		            
    		            $saved_pre_month[$patients_details[$row['ipid']]['epid']][date('Y-m',strtotime($row['shift_date']))] [$row['shift_type']] [] = $row['userid'];

    		        }
    		    }
    		    
    		    if( ! empty ($assigned_users2patients) ) {
        		    foreach ( $assigned_users2patients as $pipid => $star_user) {
        		        foreach($months_details[$selected_month]['month_days'] as $mday){
        		            if(in_array($mday,$patients_details[$pipid]['active_dats_Ymd'])){
        		                
        		                if(empty($saved_pre_month[$patients_details[$pipid]['epid']][date('Y-m',strtotime($mday))] ['pflege']))
        		                {
                                    $data['saved_data'][$patients_details[$pipid]['epid']]['pflege'][$mday] = $star_user;
        		                }
        		            }
        		        }
        		    }
    		    }

    		    $this->view->data = $data;
		    }
		}

		
		public function fixinvoicesAction(){

		    exit;
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $this->_helper->viewRenderer->setNoRender();
		    $internal_invoices = new InternalInvoices();
		    
		    
		    $all_internal = Doctrine_Query::create()
		    ->select('id,client,invoice_total,invoice_number')
		    ->from('InternalInvoices INDEXBY id')
// 		    ->whereIn('client',array("184"))
		    ->andWhereNotIn('client',array("1","32"))
// 		    ->andWhere('id = 12386')
		    ->andWhere('isdelete=0')
		    ->andWhere('storno=0')
		    ->fetchArray();
		    
		    
		    
		    $all_internal_ityems = Doctrine_Query::create()
		    ->select('*')
		    ->from('InternalInvoiceItems')
// 		    ->whereIn('client',array("184"))
		    ->andWhereNotIn('client',array("1","32"))
// 		    ->andWhere('invoice = 12386')
		    ->andWhere('isdelete=0')
		    ->fetchArray();
		    
// 		    dd($all_internal_ityems);
		    
		    foreach($all_internal_ityems as $k=>$iv){
// 		        $all_internal[$iv['invoice']]['products_total'] += $iv['total']; 
// 		        $all_internal[$iv['invoice']]['products_total'] += $iv['total']; 
// 		        $all_internal[$iv['invoice']]['products_total'] += round(($iv['qty']*$iv['price']), 2); 
// 		        $all_internal[$iv['invoice']]['products_total'] += floatval($iv['qty']*$iv['price']); 
// 		        $all_internal[$iv['invoice']]['products_total'] += number_format(($iv['qty']*$iv['price']), 2, '.', '');;

		        if(!empty($iv['invoice'])){
    		        $all_internal[$iv['invoice']]['products_total'] += $iv['qty']*$iv['price']; 
		        }
		        
		    }
		    foreach($all_internal as $invoice_id=>$inv_data)
		    {
// 		        echo $inv_data['id'].'------------- '.floatval($inv_data['invoice_total']).' - '.floatval($inv_data['products_total'])."<br/>";
		        
		        if(floatval($inv_data['invoice_total']) != floatval(number_format($inv_data['products_total'], 2, '.', ''))) 
		        {
		            $update[$inv_data['id']] = $inv_data;
		            $update[$inv_data['id']]['products_total_update'] = number_format($inv_data['products_total'], 2, '.', '');

		            if(!empty($inv_data['id'])){
    		            $delInvoices = Doctrine_Query::create()
    		            ->update("InternalInvoices")
    		            ->set('invoice_total',"?",$update[$inv_data['id']]['products_total_update'])
    		            ->where('id = ?', $inv_data['id'])
    		            ->andWhere('isdelete =0');
    		            $d = $delInvoices->execute();
		            }
		        }
		    }
		        
		    
		    echo "<pre>";
		    print_R(count($update));
		    print_R($update);
		    exit;
		    
		    
		    
		}

		public function userclsAction(){
		    exit;
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $this->_helper->viewRenderer->setNoRender();
		    
		    $client_array = array(29,44,56,95,161,249);
		    
		    $client_q = Doctrine_Query::create()
		      ->select("id,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name")
              ->from('Client INDEXBY id')
              ->whereIn('id ', $client_array)
              ->fetchArray();
		    
		    $clients_array = array_keys($client_q); 
// 		    dd($client_q);

		    $master_groups_first = array('4');
		    $client_user_groups = Usergroup::getUserGroups($master_groups_first,$clients_array);
		    
		    $docs = array();
		    foreach($client_user_groups as $k=>$ginfo){
		        $docs[] = $ginfo['id'];
		        $group_info[$ginfo['id']]['gr_name'] = $ginfo['groupname']; 
		    }
		    
		    
// 		    dd($client_user_groups);
		    
		    $user_q = Doctrine_Query::create()
		    ->select("clientid,username,last_name,first_name,emailid,groupid")
		    ->from('User INDEXBY id')
		    ->whereIn('clientid ', $client_array)
		    ->andWhereIn('groupid ', $docs)
		    ->fetchArray();
		    
		    
		    
		    foreach($user_q as $uid=>$udata){
		        $user_q[$uid]['client_name'] =  $client_q[$udata['clientid']]['client_name'];
		        $user_q[$uid]['group_name'] = $group_info[$udata['groupid']]['gr_name'];
		    }

		    $html ="";
		    $html .="<table border='1'  >";
		    foreach($user_q as $us=>$det){
		        $html .="<tr>";
		        $html .="<td>".$det['client_name']."</td>";
		        $html .="<td>".$det['group_name']."</td>";
		        $html .="<td>".$det['username']."</td>";
		        $html .="<td>".$det['last_name']."</td>";
		        $html .="<td>".$det['first_name']."</td>";
		        $html .="<td>".$det['emailid']."</td>";
		        $html .="</tr>";
		        
		    }
		    $html .="</table>";
		    
		    echo $html; exit;
		    
		    
		}
	
		
		
		
		
		public function demstepcareinternalAction()
		{
		    
		    //general data
		    $patientmaster = new PatientMaster();
		    $client_details = new Client();
		    $clientid = $this->clientid;
		    
		    //get allowed client invoices
		    $this->view->allowed_invoice = demstepcare_internal_invoice;
		    
		 
		    //bre_kinder_invoice data - ISPC-2214
		    //introduce - one table to rulle them all :: 19.06.2018
		    // includes ISPC-2286 nr_invoice
		    $invoices_system = new InvoiceSystem();
		    $invoices_system_items = new InvoiceSystemItems();
		    $invoices_system_form = new Application_Form_InvoiceSystem();
		    
		    
            $invoice_type = 'demstepcare_internal_invoice';
            
            //mark invoice as paid from invoices list link
            if(!empty($_REQUEST['mode']) && !empty($_REQUEST['iid']) && $_REQUEST['iid'] > '0')
            {
                if($_REQUEST['mode'] == "paid")
                {
                    //mark as paid
                    $invoice_pay_data['invoiceId'] = $_REQUEST['iid'];
                    $invoice_pay_data['paymentAmount'] = '0.00';
                    $invoice_pay_data['paymentComment'] = "";
                    $invoice_pay_data['paymentDate'] = date('Y-m-d H:i:s', time());
                    $invoice_pay_data['mark_as_paid'] = "1";
                    
                    
                    $new_payment = $invoices_system_form->submit_payment($invoice_type,$invoice_pay_data);
                    $this->_redirect(APP_BASE . 'invoicenew/demstepcareinternal');
                    exit;
                }
            }
            
            if($this->getRequest()->isPost())
            {
                if($_POST['draftmore'] == "1")
                {
                    $transform = $invoices_system_form->ToggleStatusInvoices($invoice_type,$_POST['document'], "2", $clientid);
                }
                elseif($_POST['delmore'] == "1" || $_POST['deletemore'] == "1")
                {
                    $del_invoice = $invoices_system_form->delete_multiple_invoices($invoice_type,$_POST['document']);
                }
                elseif($_POST['archive_invoices_more'] == "1")
                {
                    $archive = $invoices_system_form->archive_multiple_invoices($invoice_type,$_POST['document'], $clientid);
                }
                elseif($_POST['warningmore'] == "1")
                {
                    $invoiceids_to_warn = implode(',', $_POST['document']);
                    
                    $this->forward('generatereminderinvoice', null, null, array('oldaction' => 'invoicesnew'));
                    
                    return;
                }
                elseif(!empty($_POST['batch_print_more']))
                {
                    $params['invoice_type'] = $invoice_type; //invoice_type
                    $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                    $params['batch_print'] = '1'; //enables batch print procedure
                    $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                    $params['get_pdf'] = '0'; //stops downloading single pdf
                    
                    $this->generate_systeminvoice($params);
                }
                else if(!empty($_POST['invoiceId']))
                {
                    $post = $_POST;
                    $post["mark_as_paid"] = "0";
                    $new_payment = $invoices_system_form->submit_payment($invoice_type,$post);
                }
                $this->_redirect(APP_BASE . 'invoicenew/demstepcareinternal'); //to avoid resubmission
                exit;
            }
            
            if($_REQUEST['mode'] == 'setstorno')
            {
                if(is_numeric($_REQUEST['inv_id']) && strlen($_REQUEST['inv_id']) > '0')
                {
                    $invoiceid = $_REQUEST['inv_id'];
                }
                else
                {
                    $invoiceid = '0';
                }
                
                if($invoiceid > '0')
                {
                    $clone_record = $invoices_system->create_storno_invoice($invoice_type,$invoiceid);
                    $this->_redirect(APP_BASE . 'invoicenew/demstepcareinternal?flg=suc');
                    exit;
                }
            }
            
            if($_REQUEST['mode'] == 'delete' && $_REQUEST['invoiceid'])
            {
                $delete_invoice = $invoices_system_form->delete_invoice($invoice_type,$_REQUEST['invoiceid']);
                
                if($delete_invoice)
                {
                    $this->_redirect(APP_BASE . 'invoicenew/demstepcareinternal?flg=delsuc');
                }
                else
                {
                    $this->_redirect(APP_BASE . 'invoicenew/demstepcareinternal?flg=delerr');
                }
            }
            
            //construct months array
            $start_period = '2010-01-01';
            $end_period = date('Y-m-d', time());
            $period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
            $month_select_array['99999999'] = '';
            foreach($period_months_array as $k_month => $v_month)
            {
                $month_select_array[$v_month] = $v_month;
            }
            
            //see how many days in selected month
            $this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
            
            if(!function_exists('cal_days_in_month'))
            {
                $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
            }
            else
            {
                $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
            }
            
            //construct selected month array (start, days, end)
            $months_details[$selected_month]['start'] = $selected_month . "-01";
            $months_details[$selected_month]['days_in_month'] = $month_days;
            $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
            
            krsort($month_select_array);
            
            $this->view->months_selector = $this->view->formSelect("selected_month", '', null, $month_select_array);
		 
		}
		
		public function fetchshinvoicelistAction()
		{
		    $this->_helper->layout->setLayout('layout_ajax');
		    $hidemagic = Zend_Registry::get('hidemagic');
		    
		    $users = new User();
		    $warnings = new RemindersInvoice();
		    $modules = new Modules();
		    $sh_invoices_payments = new ShInvoicePayments();
		    
		    $clientid = $this->clientid;
		    
		    if($modules->checkModulePrivileges("170", $clientid))
		    {
		        $this->view->create_bulk_warnings = "1";
		    }
		    else
		    {
		        $this->view->create_bulk_warnings = "0";
		    }
		    
		    $limit = 50;
		    $this->view->limit = $limit;
		    $filters = array();
		    
		    $storno_invoices_q = Doctrine_Query::create()
		    ->select("*")
		    ->from('ShInvoices')
		    ->where('client = "' . $clientid . '"  ')
		    ->andWhere('storno = 1')
		    ->andWhere('isdelete = 0');
		    $storno_invoices_array = $storno_invoices_q->fetchArray();
		    
		    $storno_ids_str = '"XXXXXX",';
		    foreach($storno_invoices_array as $k => $st)
		    {
		        $storno_ids[] = $st['record_id'];
		        $storno_ids_str .= '"' . $st['record_id'] . '",';
		    }
		    
		    if(empty($storno_ids))
		    {
		        $storno_ids[] = "XXXXXXX";
		    }
		    
		    $storno_ids_str = substr($storno_ids_str, 0, -1);
		    
		    // get client data
		    $client_details_m = new Client();
		    $client_details = $client_details_m->getClientDataByid($clientid);
		    
		    $invoice_due_days = $client_details[0]['invoice_due_days'];
		    $plus_due_days = '+' . $invoice_due_days . ' days';
		    $this->view->plus_due_days = $plus_due_days;
		    
		    //process tabs
		    $filters['hiinvoice_search'] = '';
		    switch($_REQUEST['f_status'])
		    {
		        case 'draft':
		            $filters['hiinvoice'] = ' AND status ="1" AND isdelete=0 AND isarchived ="0"';
		            break;
		            
		        case 'unpaid':
		            $filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ') AND isdelete = 0 AND isarchived ="0"';
		            
		            break;
		            
		        case 'paid':
		            $filters['hiinvoice'] = ' AND status="3"  AND storno = 0  AND id NOT IN (' . $storno_ids_str . ') AND isdelete=0 AND isarchived ="0"';
		            break;
		            
		        case 'deleted':
		            $filters['hiinvoice'] = ' AND (status="4" OR isdelete="1") AND isarchived ="0"';
		            break;
		            
		        case 'overdue':
		            // 					$filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ')  AND DATE(NOW()) > DATE(completed_date)  AND isdelete=0 AND isarchived ="0"';
		            $filters['hiinvoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 AND id NOT IN (' . $storno_ids_str . ')  AND   DATE(NOW()) >  DATE_ADD(DATE(completed_date), INTERVAL ' . $invoice_due_days . ' DAY)   AND isdelete=0 AND isarchived ="0"';
		            break;
		            
		        case 'all':
		            $filters['hiinvoice'] = ' AND isarchived ="0"';
		            break;
		        case 'archived':
		            $filters['hiinvoice'] = ' AND isarchived ="1" AND isdelete=0';
		            break;
		            
		        default: // unpaid- open
		            $filters['hiinvoice'] = ' AND (status = "2" OR status = "5")   AND storno = 0 AND id NOT IN (' . $storno_ids_str . ') AND isdelete = 0 AND isarchived ="0"';
		            break;
		    }
		    
		    if(!empty($_REQUEST['last_name']))
		    {
		        $filters['patient_master'] = ' AND (CONCAT(AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['last_name']) . '%")';
		    }
		    
		    if(!empty($_REQUEST['first_name']))
		    {
		        $filters['patient_master'] .= ' AND (CONCAT(AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['first_name']) . '%")';
		    }
		    
		    if(!empty($_REQUEST['epid']))
		    {
		        $filters['patient_master'] .= ' AND ( e.epid LIKE "%' . addslashes($_REQUEST['epid']) . '%")';
		    }
		    
		    if(!empty($_REQUEST['rnummer']))
		    {
		        $filters['hiinvoice'] .= ' AND ( LOWER(CONCAT(`prefix`,CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
		        $filters['hiinvoice_search'] .= ' AND ( LOWER(CONCAT(`prefix`,CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
		    }
		    if(!empty($_REQUEST['selected_month']) && $_REQUEST['selected_month'] != '99999999')
		    {
		        $filters['hiinvoice'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
		        $filters['hiinvoice_search'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
		    }
		    
		    //get invoice patients
		    $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,
			CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
		    $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
		    $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
		    $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
		    $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
		    
		    // if super admin check if patient is visible or not
		    if($this->usertype == 'SA')
		    {
		        $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
		        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
		        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
		        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
		        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
		        $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
		    }
		    
		    if(!empty($_REQUEST['epid']) || !empty($_REQUEST['last_name']) || !empty($_REQUEST['first_name']))
		    {
		        //filter patients name/surname/epid
		        $f_patient = Doctrine_Query::create()
		        ->select($sql)
		        ->from('PatientMaster p')
		        ->where("p.isdelete =0")
		        ->leftJoin("p.EpidIpidMapping e")
		        ->andWhere('e.clientid = ' . $clientid . $filters['patient_master']);
		        $f_patients_res = $f_patient->fetchArray();
		        
		        $f_patients_ipids[] = '9999999999999';
		        foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
		        {
		            $f_patients_ipids[] = $v_f_pat_res['EpidIpidMapping']['ipid'];
		        }
		    }
		    
		    //all invoices for counting
		    $invoices_counting = Doctrine_Query::create()
		    ->select("*")
		    ->from('ShInvoices')
		    ->where("client='" . $clientid . "'" . $filters['hiinvoice_search']);
		    if(!empty($_REQUEST['epid']) || !empty($_REQUEST['last_name']) || !empty($_REQUEST['first_name']))
		    {
		        $invoices_counting->andWhereIn('ipid', $f_patients_ipids);
		    }
		    
		    $inv2count = $invoices_counting->fetchArray();
		    
		    $count_invoices = array();
		    
		    
		    
		    foreach($inv2count as $k_inv2count => $v_inv2count)
		    {
		        
		        $count_invoices[$v_inv2count['status']][] = '1';
		        
		        if($v_inv2count['status'] == "1" && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["draft"][] = '1';
		        }
		        
		        if(($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["unpaid"][] = '1';
		        }
		        
		        if($v_inv2count['status'] == "3" && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["paid"][] = '1';
		        }
		        
		        if($v_inv2count['status'] == "4" || $v_inv2count['isdelete'] == "1" && $v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["deleted"][] = '1';
		        }
		        
		        if(($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && strtotime(date('Y-m-d', time())) > strtotime(date('Y-m-d', strtotime($plus_due_days, strtotime($v_inv2count['completed_date'])))) && $v_inv2count['isarchived'] == "0")
		        // 				if(($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && strtotime(date('Y-m-d', time())) > strtotime(date('Y-m-d', strtotime($v_inv2count['completed_date']))) && $v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["overdue"][] = '1';
		        }
		        
		        if($v_inv2count['isarchived'] == "0")
		        {
		            $status_count_invoices["all"][] = '1';
		        }
		        
		        if($v_inv2count['isarchived'] == "1")
		        {
		            $status_count_invoices["archived"][] = '1';
		        }
		    }
		    //deleted_invoices
		    $del_invoices_counting = Doctrine_Query::create()
		    ->select("*")
		    ->from('ShInvoices')
		    ->where("client='" . $clientid . "'" . $filters['hiinvoice_search']);
		    if(!empty($_REQUEST['epid']) || !empty($_REQUEST['last_name']) || !empty($_REQUEST['first_name']))
		    {
		        $del_invoices_counting->andWhereIn('ipid', $f_patients_ipids);
		    }
		    $del_invoices_counting->andWhere("isdelete=1 or status=4");
		    $del_inv2count = $del_invoices_counting->fetchArray();
		    foreach($del_inv2count as $k_del_inv => $v_del_inv)
		    {
		        $counted_del_inv[$v_del_inv['status']][] = '1';
		    }
		    
		    //filter invoices status/invoice_number/amount
		    $invoices_nl = Doctrine_Query::create()
		    ->select("*")
		    ->from('ShInvoices')
		    ->where("client='" . $clientid . "'" . $filters['hiinvoice']);
		    if(!empty($_REQUEST['epid']) || !empty($_REQUEST['last_name']) || !empty($_REQUEST['first_name']))
		    {
		        $invoices_nl->andWhereIn('ipid', $f_patients_ipids);
		    }
		    $invoices_no_limit = $invoices_nl->fetchArray();
		    $invoices_no_limit[] = "XXXXXX";
		    
		    
		    $invoice_ipids[] = '99999999999999';
		    foreach($invoices_no_limit as $k_nl_inv => $v_nl_inv)
		    {
		        $invoice_ipids[] = $v_nl_inv['ipid'];
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select($sql)
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->where('e.clientid = ' . $clientid);
		    if(!empty($_REQUEST['epid']) || !empty($_REQUEST['last_name']) || !empty($_REQUEST['first_name']))
		    {
		        $patient->andWhereIn('p.ipid', $f_patients_ipids);
		    }
		    $patients_res = $patient->fetchArray();
		    
		    if($patients_res)
		    {
		        foreach($patients_res as $k_pat => $v_pat)
		        {
		            $patient_details[$v_pat['EpidIpidMapping']['ipid']] = $v_pat;
		        }
		    }
		    
		    if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
		    {
		        $current_page = $_REQUEST['page'];
		    }
		    else
		    {
		        $current_page = 1;
		    }
		    
		    if($_REQUEST['sort'] == 'asc')
		    {
		        $sort = 'asc';
		    }
		    else
		    {
		        $sort = 'desc';
		    }
		    
		    switch($_REQUEST['ord'])
		    {
		        
		        case 'id':
		            $orderby = 'id ' . $sort;
		            break;
		            
		        case 'ln':
		            $orderby = 'epid ' . $sort;
		            break;
		            
		        case 'nr':
		            //$orderby = 'invoice_number ' . $sort;
		            $orderby = 'full_invoice_number_sort ' . $sort; //TODO-2073 ISPC: Invoices sorting not correct :: @Ancuta 22.01.2019
		            break;
		            
		        case 'date':
		            $orderby = 'change_date, create_date ' . $sort;
		            break;
		            
		        case 'amnt':
		            $orderby = 'invoice_total ' . $sort;
		            break;
		        case 'invoice_date':
		            $orderby = 'completed_date_sort ' . $sort;
		            break;
		            
		        default:
		            //ShInvoices
		            $orderby = 'id DESC'; // ISPC-2220: change_order_invoice :: @Ancuta 30.07.2018 [NEW]
		            $orderby = 'full_invoice_number_sort DESC'; //TODO-2073 ISPC: Invoices sorting not correct :: @Ancuta 22.01.2019
		            
		            break;
		    }
		    
		    
		    
		    $invoices = Doctrine_Query::create()
		    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort,concat(prefix,invoice_number) as full_invoice_number_sort")
		    ->from('ShInvoices')
		    ->where("client='" . $clientid . "'" . $filters['hiinvoice'])
		    ->andwhereIn('ipid', $invoice_ipids);
		    $invoices->orderby($orderby);
		    $invoices->offset(($current_page - 1) * $limit);
		    $invoices->limit($limit);
		    $invoicelimit = $invoices->fetchArray();
		    
		    
		    $invoice_uids[] = '9999999999';
		    foreach($invoicelimit as $k_il => $v_il)
		    {
		        $invoice_ids[] = $v_il['id'];
		        $invoice_uids[] = $v_il['create_user'];
		        $invoice_uids[] = $v_il['change_user'];
		    }
		    
		    
		    //count tabs contents
		    $invoice_tabs = array('unpaid', 'paid', 'draft', 'deleted', 'overdue', 'all', 'archived');
		    
		    $counted = array();
		    foreach($invoice_tabs as $tab)
		    {
		        $counted[$tab] += count($status_count_invoices[$tab]);
		    }
		    
		    $invoice_uids = array_values(array_unique($invoice_uids));
		    $users_details = $users->getMultipleUserDetails($invoice_uids);
		    
		    
		    $invoice_payments = $sh_invoices_payments->getInvoicesPaymentsSum($invoice_ids);
		    
		    $no_invoices = sizeof($invoices_no_limit) - 1; //substract dummy error control result
		    $no_pages = ceil($no_invoices / $limit);
		    
		    $all_warnings = $warnings->get_reminders($invoice_ids, 'sh_invoice', $clientid);
		    
		    foreach ($invoicelimit as &$row) {
		        if (isset($all_warnings[$row['id']])) {
		            $row['InvoiceWarnings'] = $all_warnings[$row['id']];
		        }
		    }
		    //var_dump($invoicelimit); exit;
		    
		    $this->view->storned_invoces = ShInvoices::get_storned_invoices($clientid);
		    
		    $this->view->invoicelist = $invoicelimit;
		    $this->view->user_details = $users_details;
		    $this->view->patient_details = $patient_details;
		    $this->view->invoice_payments = $invoice_payments;
		    $this->view->current_page = $current_page;
		    $this->view->no_pages = $no_pages;
		    $this->view->no_invoices = $no_invoices;
		    $this->view->orderby = $_REQUEST['ord'];
		    $this->view->sort = $_REQUEST['sort'];
		    $this->view->counted = $counted;
		}
		
		
		
		
		
		
		/**
		 * ISPC-2609 + ISPC-2000 Ancuta 24.09.2020
		 */
		public function __StartPrintJobs(){
		    $appInfo = Zend_Registry::get('appInfo');
		    $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
		    
		    $function_path = $app_path.'/cron/processprintjobs';
		    popen('curl -s '.$function_path.' &', 'r');
		}
		
		
		
	}

?>