<?php
class SocialCodeController extends Pms_Controller_Action
{
// Maria:: Migration ISPC to CISPC 08.08.2020
	public function init ()
	{
		$this
		->setActionsWithJsFile([
				/*
				 * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		*/
				'addformblockcustomsettings',
				'formblockcustomsettings'
		]);
	}

	private function retainValues ( $values )
	{
		foreach ($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

	function getAllClientPatients ( $clientid, $whereepid )
	{
		$actpatient = Doctrine_Query::create()
		->select("p.ipid, e.epid, e.epid_num")
		->from('PatientMaster p');
		$actpatient->leftJoin("p.EpidIpidMapping e");
		$actpatient->where($whereepid . 'e.clientid = ' . $clientid);


		$actipidarray = $actpatient->fetchArray();

		return $actipidarray;
	}

	public function groupsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$socialcodegroups = new SocialCodeGroups();
		$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);

		$this->view->grouplist = $grouplist;

		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			

			if (!empty($_POST['group_order']))
			{
				foreach ($_POST['group_order'] as $gr_id => $gr_order)
				{
					if (!empty($gr_order))
					{
						$sph = Doctrine_Query::create()
						->update('SocialCodeGroups')
						->set('group_order', $gr_order)
						->where("id='" . $gr_id . "'");
						$sph->execute();
					}
				}

				$this->_redirect(APP_BASE . 'socialcode/groups?flg=suc');
				$this->view->error_message = $this->view->translate("ordersaved");
				exit;
			}
		}
	}

	public function addgroupAction ()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		
		if ($this->getRequest()->isPost())
		{
			$group_form = new Application_Form_SocialCodeGroups();
			$this->group = $group_form->validate($_POST);
			if ($this->group)
			{
				$group_form->InsertData($_POST);
				$this->_redirect(APP_BASE . 'socialcode/groups?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$group_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editgroupAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$this->_helper->viewRenderer('addgroup');

		$groupid = $_REQUEST['id'];
		$socialcodegroups = new SocialCodeGroups();
		$group_details = $socialcodegroups->getSocialCodeGroup($groupid);

		foreach ($group_details as $k => $group_d)
		{
			$group = $group_d;
		}
		$this->view->group = $group;

		if ($this->getRequest()->isPost())
		{
			$group_form = new Application_Form_SocialCodeGroups();
			$this->group = $group_form->validate($_POST);

			if ($this->group)
			{
				$group_form->UpdateData($_POST);
				$this->_redirect(APP_BASE . 'socialcode/groups?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$group_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function actionsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$socialcodeactions = new SocialCodeActions();
		$actionslist = $socialcodeactions->getCientSocialCodeActions($clientid);

		$this->view->actionslist = $actionslist;

		$socialcodegroups = new SocialCodeGroups();
		$groupdetails = $socialcodegroups->getSocialCodeGroupName($clientid);

		$this->view->groupdetails = $groupdetails;

		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if (!empty($_POST['action_del']))
			{
				$sph = Doctrine_Query::create()
				->update('SocialCodeActions')
				->set('isdelete', '1')
				->where("id=? ", $_POST['action_del']);
				$sph->execute();
			}

			$this->_redirect(APP_BASE . 'socialcode/actions?flg=suc');
			$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
		}
	}

	public function addactionAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$usertype = $logininfo->usertype;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$this->view->usertype = $usertype;
		$this->view->conditions = Pms_CommonData::getSgbvFillingConditions();

		$socialcodeactions = new SocialCodeActions();
		$actionslist = $socialcodeactions->getCientSocialCodeActions($clientid);

		$this->view->standard_actions = $actionslist;

		$socialcodegroups = new SocialCodeGroups();
		$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);

		$this->view->grouplist = $grouplist;

		if (!empty($_REQUEST['extra']) && !empty($_REQUEST['list']))
		{
			$extra = 1;
			$list = $_REQUEST['list'];
		}
		else
		{
			$extra = false;
			$list = false;
		}


		if ($this->getRequest()->isPost())
		{
			$action_form = new Application_Form_SocialCodeActions();
			$price_action = new Application_Form_SocialCodePriceList();
			$this->action = $action_form->validate($_POST);

			if ($this->action)
			{
				$action_id = $action_form->InsertData($_POST, $extra);

				if ($extra == 1 && $list !== false)
				{

					if ($action_id)
					{
						$price_action->add_pricelist_actions($action_id, $list);
					}

					$this->_redirect(APP_BASE . 'socialcode/pricelistdetails?list=' . $list . '');
				}
				else
				{
					$this->_redirect(APP_BASE . 'socialcode/actions?flg=suc');
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
			}
			else
			{
				$this->retainValues($_POST);
				$this->view->action = $_POST;
				$action_form->assignErrorMessages();
			}
		}
	}

	public function editactionAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$usertype = $logininfo->usertype;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		
		$this->view->usertype = $usertype;
		$this->view->conditions = Pms_CommonData::getSgbvFillingConditions();

		$this->_helper->viewRenderer('addaction');
		$socialcodegroups = new SocialCodeGroups();
		$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);

		$this->view->grouplist = $grouplist;

		$socialcodeactions = new SocialCodeActions();
		$actionslist = $socialcodeactions->getCientSocialCodeActions($clientid);

		$this->view->standard_actions = $actionslist;

		$actionid = $_REQUEST['id'];

		$socialcodeactions = new SocialCodeActions();
		$action_details = $socialcodeactions->getSocialCodeAction($actionid);

		foreach ($action_details as $k => $action_d)
		{
			$action = $action_d;
		}
		$this->view->action = $action;

		if (!empty($_REQUEST['list']))
		{
			$list = $_REQUEST['list'];
		}
		else
		{
			$list = false;
		}

		if ($this->getRequest()->isPost())
		{
			$action_form = new Application_Form_SocialCodeActions();
			$this->action = $action_form->validate($_POST);

			if ($this->action)
			{
				$action_form->UpdateData($_POST);

				if ($list !== false)
				{
					$this->_redirect(APP_BASE . 'socialcode/pricelistdetails?list=' . $list . '');
				}
				else
				{
					$this->_redirect(APP_BASE . 'socialcode/actions?flg=suc');
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
			}
			else
			{
				$action_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function formtypesAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$form_types = new FormTypes();
		$form_types_form = new Application_Form_FormTypes();
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		
		$form_type_actions = FormTypeActions::get_form_type_actions(1);
		$this->view->form_type_actions = $form_type_actions;
		
		if ($_REQUEST['op'] == 'del' && count($_REQUEST['ftid']) > 0)
		{
				//del
				$insert_form_type = $form_types_form->delete_form_type($_REQUEST['ftid'], $_POST);

				if ($insert_form_type)
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=err');
				}
		}
		
		
		
		
		if ($this->getRequest()->isPost())
		{
			if (count($_REQUEST['ftid']) > 0)
			{
				//edit
				if(!empty($_POST['form_type'])){
					$insert_form_type = $form_types_form->update_form_type($_REQUEST['ftid'], $_POST);
				} else{
					$insert_form_type = false;
				}

				if ($insert_form_type)
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=err');
				}
			}
			else
			{
				//insert new
				if(!empty($_POST['form_type'])){
					$insert_form_type = $form_types_form->insert_form_type($clientid, $_POST);
				} else{
					$insert_form_type = false;
				}
				
				
				if ($insert_form_type)
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=suc');
				}
				else
				{
					$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=err');
				}
			}
		}

		$client_form_types = $form_types->get_form_types($clientid);
		$this->view->form_types = $client_form_types;
		
		foreach($client_form_types as $k=>$tdata){
			$types_arr[] = $tdata['id'];
		}
		
		$used_types = array();
		// get used form types
		if(!empty($types_arr)){
			$contact_form_visits = Doctrine_Query::create()
			->select("id,form_type")
			->from("ContactForms")
			->whereIn('form_type', $types_arr)
			->andWhere('isdelete ="0"');
			$cf_res = $contact_form_visits->fetchArray();
			
			foreach($cf_res  as $k=>$cf_data){
				if(!in_array($cf_data['form_type'],$used_types)){
					$used_types[] = $cf_data['form_type'];
				}
			}

			//Maria:: Migration CISPC to ISPC 22.07.2020
			//START show how many forms of each type are saved yet
            $contact_forms_used_q = Doctrine_Query::create()
                ->select("count(id) as cnt,form_type")
                ->from("ContactForms")
                ->whereIn('form_type', $types_arr)
                ->andWhere('isdelete ="0"')
                ->groupBy('form_type');
            $cfusedcount = $contact_forms_used_q->fetchArray();
            $this->view->type_to_usedcount=array();
            foreach($cfusedcount  as $k=>$cf_data){
                $this->view->type_to_usedcount[$cf_data['form_type']]=$cf_data['cnt'];
            }
            //END show how many forms of each type are saved yet

		}
		$this->view->used_types = $used_types;

		if ($_REQUEST['ftid'])
		{
			$edit_form_type_data = $form_types->get_form_type($_REQUEST['ftid']);
			$this->view->edit_form_type = $edit_form_type_data[0];
		}
	}

	public function assignblocksAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$form_blocks_order = new FormBlocksOrder();
		$form_blocks_order_form = new Application_Form_FormBlocksOrder();

		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		if ($_REQUEST['mode'] == 'save')
		{

			$save_blocks_order = $form_blocks_order_form->save_form_blocks($clientid, $_REQUEST['ftid'], $_REQUEST['order']);

			echo json_encode($save_blocks_order[0]);
			exit;
		}
		$form_types = new FormTypes();
		$form_types_blocks = new FormBlocks2Type();
		$form_types_blocks_form = new Application_Form_FormBlocks2Type();
		$form_blocks_options = new FormBlocksOptions();
		$form_blocks_options_form = new Application_Form_FormBlocksOptions();


		$cl = new Client();
		$clientarray = $cl->getClientDataByid($clientid);
		$this->view->client_name = $clientarray[0]['client_name'];

		if ($_REQUEST['ftid'])
		{
			$form_type_id = $_REQUEST['ftid'];
		}
		else
		{
			$form_type_id = '0';
		}


		/* ------------------------------------------------ */
		$form_blocks_options_arr = $form_blocks_options->get_blocks_options($clientid, $form_type_id);

		foreach ($form_blocks_options_arr as $k => $open_block)
		{
			$opened_blocks[$open_block['block']] = $open_block['open'];
			
			//TODO-3843 Ancuta 11.02.2021
			$write2course[$open_block['block']]['write2recorddata'] = $open_block['write2recorddata'];
			$write2course[$open_block['block']]['write2recorddata_color'] = $open_block['write2recorddata_color'];

            //TODO-4035 Nico 12.04.2021
            $write2course[$open_block['block']]['write2shortcut'] = $open_block['write2shortcut'];
			//-- 
		}

		$this->view->opened_blocks = $opened_blocks;
		//TODO-3843 Ancuta 11.02.2021
		$this->view->write2course = $write2course;
		//--

		/* ------------------------------------------------ */
		$form_blocks_order_arr = $form_blocks_order->get_blocks_order($clientid, $form_type_id);
		$all_blocks = Pms_CommonData::contact_form_blocks();
		//ISPC-2454 -custom form block
		$this->view->custom_blocks = FormBlockCustomSettingsTable::findByClientid($clientid);
		$this->view->custom_abbrev = array_column($this->view->custom_blocks, 'block_abbrev');
		
		//TODO-3843 Ancuta 11.02.2021 
		$blocks2recorddata = Pms_CommonData::contact_form_blocks2recorddata();
		$this->view->blocks2recorddata = $blocks2recorddata;

        //TODO-4035 Nico 12.04.2021
        $blocks2shortcut = Pms_CommonData::contact_form_blocks2shortcut();
        $this->view->blocks2shortcut = $blocks2shortcut;

		
		//Maria:: Migration CISPC to ISPC 22.07.2020
		$more_blocks=array_keys(Application_Form_FormBlockKeyValue::get_simpleblocks_config());

		//ISPC-2577, elena, 07.09.2020
		$shortcutblocks = ShortcutTextBlock::getShortcutTextBlocks();
        $retArr = [];
        $shortcutblocks_names = [];
        foreach($shortcutblocks as $sblock){
            $retArr['block_block_shortcode_' . $sblock['id']] = $sblock['blockname'] ;
            $retArr['block_shortcode_' . $sblock['id']] = $sblock['blockname'] ;
        };

        if(count($shortcutblocks)) {
            //add translation from db (mapping block_shortcode_{id} to blockname)
            $this->translator->getTranslator()->addTranslation(array(
                'content' => $retArr,
                'locale' => 'de',
                'clear' => false
            ));

            foreach ($shortcutblocks as $shortcodeblock) {
                $shortcutblocks_names[] = 'block_shortcode_' . $shortcodeblock['id'];
            }
        }
        //print_r($shortcutblocks_names);
		//$all_blocks=array_merge($all_blocks,$more_blocks, $shortcutblocks_names);//TODO-3951,Elena,12.03.2021


        //ISPC-2698, elena, 22.12.2020
        $optblocks = ClientOptionsBlocks::getClientOptionsBlocks($clientid);
        $retArr = [];
        $optblocks_names = [];
        foreach($optblocks as $sblock){
            $retArr['block_block_opt_' . $sblock['id']] = $sblock['blockname'] ;
            $retArr['block_opt_' . $sblock['id']] = $sblock['blockname'] ;
        };

        if(count($optblocks)) {
            //add translation from db (mapping block_shortcode_{id} to blockname)
            $this->translator->getTranslator()->addTranslation(array(
                'content' => $retArr,
                'locale' => 'de',
                'clear' => false
            ));

            foreach ($optblocks as $optblock) {
                $optblocks_names[] = 'block_opt_' . $optblock['id'];
            }
        }

        $all_blocks=array_merge($all_blocks,$more_blocks, $shortcutblocks_names, $optblocks_names);


		//--
		if ($form_blocks_order_arr)
		{
			$saved_blocks = explode(',', $form_blocks_order_arr[0]['box_order']);

			//append new blocks to the end of saved blocks array
			foreach ($all_blocks as $k_ablock => $v_ablock)
			{
				if (!in_array($v_ablock, $saved_blocks))
				{
					$saved_blocks[] = $v_ablock;
				}
			}

			$this->view->blocks = $saved_blocks;
		}
		else
		{
			$this->view->blocks = $all_blocks;
		}

		$saved_blocks = $form_types_blocks->get_form_types_blocks($clientid, $form_type_id);

		foreach ($saved_blocks as $v_sblock)
		{
			$assigned_blockss[$v_sblock['form_block']] = $v_sblock;
		}

		$this->view->form_types_blocks = $assigned_blockss;


		$this->view->curent_form_type_details = $form_types->get_form_type($form_type_id);

		if ($this->getRequest()->isPost())
		{
			// Maria:: Migration CISPC to ISPC 22.07.2020
            $mypostdata=$_POST;
            if(isset($_POST['quickchange'])){
                $assign=explode(';',$_POST['quickassign']);
                $assign_arr=array();
                foreach($assign as $ass){
                    if(strlen($ass)>0) {
                        $assign_arr[$ass] = 1;
                    }
                }
                $open=explode(';',$_POST['quickassignopen']);
                $open_arr=array();
                foreach($open as $ass){
                    if(strlen($ass)>0) {
                        $open_arr[$ass] = 1;
                    }
                }

                $mypostdata=array('open'=>$open_arr, 'assign'=>$assign_arr);


                $blocks_order = new FormBlocksOrder();
                $blocks_order_res = $blocks_order->get_blocks_order($clientid, $form_type_id);
                $blocks_order = $blocks_order_res[0]['box_order'];
                $blocks_order_arr = explode(',',$blocks_order);
                $neworder=array();

                $i=array_search('time',$blocks_order_arr);
                unset($blocks_order_arr[$i]);
                $neworder[]='time';

                $assign = array_keys($assign_arr);
                foreach($assign as $ass){
                    $i=array_search($ass,$blocks_order_arr);
                    unset($blocks_order_arr[$i]);
                    $neworder[]=$ass;
                }
                foreach ($blocks_order_arr as $ass){
                    $neworder[]=$ass;
                }
                $form_blocks_order = new Application_Form_FormBlocksOrder();
                $save_blocks_order = $form_blocks_order->save_form_blocks($clientid, $form_type_id, $neworder);
            }

			$save_assignation = $form_types_blocks_form->assign_form_blocks($clientid, $form_type_id, $mypostdata);
			
			//TODO-3843 Ancuta 11.02.2021
			//$save_open_blocks = $form_blocks_options_form->open_form_blocks($clientid, $form_type_id, $mypostdata);
			$save_blocks_options = $form_blocks_options_form->save_form_blocks_options($clientid, $form_type_id, $mypostdata);
			//-- 
			
			$save_blocks_order = $form_blocks_order_form->save_form_blocks($clientid, $form_type_id, $mypostdata['order']);

			if ($save_assignation)
			{
				$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=suc');
			}
			else
			{
				$this->_redirect(APP_BASE . 'socialcode/formtypes?flg=err');
			}
		}
	}

	public function pricelistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$p_lists = new SocialCodePriceList();
		$plg = new SocialCodePriceListGroups();

		if ($clientid == '0')
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$shortcuts = Pms_CommonData::get_prices_shortcuts();
		//normal pricelist groups
		$pricelist_groups = $plg->get_groups($clientid, true, false);
		$this->view->pricelist_groups = $pricelist_groups;

		//private pricelist groups
		$pv_pricelist_groups = $plg->get_groups($clientid, true, true);
		$this->view->pv_pricelist_groups = $pv_pricelist_groups;


		if ($_REQUEST['list'])
		{
			//check if the list belongs to this client
			$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

			if ($p_lists_check)
			{
				$list = $_REQUEST['list'];
			}
			else
			{
				//get user out of here if the list does not belong to current clientid;
				$list = false; //just to be sure
				$this->_redirect(APP_BASE . "socialcode/pricelist");
			}
		}

		$this->view->listid = $list;
		$this->view->list_type = $list_type;

		if ($_REQUEST['op'] == 'del' && $list)
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$form_list = new Application_Form_SocialCodePriceList();
			$delete_list = $form_list->delete_price_list($list);

			$this->_redirect(APP_BASE . "socialcode/pricelist");
			exit;
		}

		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$form = new Application_Form_SocialCodePriceList();
			$form_actions = new Application_Form_SocialCodeActions();

			if (!empty($_POST['pv']) && $_POST['pv']['private'] == '1')
			{
				$post_list_data = $_POST['pv'];
			}
			else
			{
				$post_list_data = $_POST;
			}
			if ($form->validate_period($post_list_data))
			{
				if ($_REQUEST['op'] == 'edit' && $post_list_data['edit_period'] == '1')
				{
					$returned_list_id = $form->edit_list($post_list_data, $list);
				}
				else
				{
					$returned_list_id = $form->save_price_list($post_list_data);

					if ($returned_list_id)
					{
						$socialcodeactions = new SocialCodeActions();
						$standard_actions = $socialcodeactions->getAllAvailableActions($clientid);

						foreach ($standard_actions as $k_action => $v_action)
						{
							$action_details[$v_action['id']] = $v_action;
							$action_details[$v_action['id']]['parent_list'] = $returned_list_id;
							$action_details[$v_action['id']]['group'] = $v_action['groupid'];

							$default_actions[$v_action['id']]['actionid'] = $v_action['id'];
								
							$standard_action_ids[] = $form_actions->InsertData($action_details[$v_action['id']],true);
						}
						$save_default_actions = $form->save_prices_actions($standard_action_ids, $returned_list_id);
					}
				}

				$this->_redirect(APP_BASE . "socialcode/pricelist");
				exit;
			}
			else
			{
				$form->assignErrorMessages();
				if($_POST['pv']['private'] == '1')
				{
					$this->retainValues($_POST['pv']);
				}
				else
				{
					$this->retainValues($_POST);
				}
			}
		}

		$price_lists = $p_lists->get_lists($clientid);
		$pv_price_lists = $p_lists->get_private_lists($clientid);

		$this->view->price_list = $price_lists;
		$this->view->pv_price_list = $pv_price_lists;
	}

	public function pricelistdetailsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$p_lists = new SocialCodePriceList();
		$p_groups = new SocialCodePriceGroups();
		$p_bonuses = new SocialCodePriceBonuses();
		$p_actions = new SocialCodePriceActions();

		$socialcodegroups = new SocialCodeGroups();
		$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);

		foreach ($grouplist as $key => $gr)
		{
			$group_details[$gr['id']] = $gr['groupname'];
		}

		$this->view->group_details = $group_details;
		/* ----------------------------------------------------- */

		$socialcodebonuses = new SocialCodeBonuses();
		$bonuslist = $socialcodebonuses->getCientSocialCodeBonuses($clientid);

		foreach ($bonuslist as $bkey => $bn)
		{
			$bonus_details[$bn['id']] = $bn['bonusname'];
		}

		$this->view->bonus_details = $bonus_details;
		/* ----------------------------------------------------- */

		$pricel_actionslist = $p_actions->get_actions_price_list($_REQUEST['list'], $clientid);

		$this->view->actionslist = $pricel_actionslist;

		if ($clientid == '0')
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}
		if ($_REQUEST['list'])
		{
			//check if the list belongs to this client
			$p_lists_check = $p_lists->check_client_list($clientid, $_REQUEST['list']);

			if ($p_lists_check)
			{
				$list = $_REQUEST['list'];
			}
			else
			{
				//get user out of here if the list does not belong to current clientid;
				$list = false; //just to be sure
				$this->_redirect(APP_BASE . "error/previlege");
			}
		}

		$this->view->listid = $list;

		if ($this->getRequest()->isPost())
		{

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			$form = new Application_Form_SocialCodePriceList();

			if ($_POST['save_prices'])
			{
				foreach ($_POST as $key_table => $post_data)
				{
					if ($key_table != 'save_prices')
					{
						$save_function = 'save_prices_' . $key_table;

						$insert = $form->$save_function($post_data, $list);
					}
				}
				//avoid resubmit post
				$this->_redirect(APP_BASE . "socialcode/pricelistdetails?list=" . $list);
				exit;
			}
			else if ($_POST['delete_action'])
			{

				if (!empty($_POST['del_actions']) && is_array($_POST['del_actions']))
				{

					$q = Doctrine_Query::create()
					->delete('SocialCodePriceActions')
					->where("list= ?", $_REQUEST['list'])
					->andWhere("clientid= ?", $clientid)
					->andWhereIn('actionid', $_POST['del_actions']);
					$q->execute();
				}
				//avoid resubmit post
				$this->_redirect(APP_BASE . "socialcode/pricelistdetails?list=" . $list);
				exit;
			}
		}

		$price_groups = $p_groups->get_prices($list, $clientid, $group_details);
		$price_bonuses = $p_bonuses->get_prices($list, $clientid, $bonus_details);

		$price_lists = $p_lists->get_all_price_lists($logininfo->clientid); // get all including private lists

		$this->view->price_list = $price_lists;
		$this->view->price_groups = $price_groups;
		$this->view->price_bonuses = $price_bonuses;
	}

	public function bonusesAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$socialcodebonuses = new SocialCodeBonuses();
		$bonuseslist = $socialcodebonuses->getCientSocialCodeBonuses($clientid);

		$this->view->bonuslist = $bonuseslist;
	}

	public function addbonusAction ()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$bonus_form = new Application_Form_SocialCodeBonuses();
			$this->bonus = $bonus_form->validate($_POST);
			if ($this->bonus)
			{
				$bonus_form->InsertData($_POST);
				$this->_redirect(APP_BASE . 'socialcode/bonuses?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$bonus_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function editbonusAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$this->_helper->viewRenderer('addbonus');

		$bonusid = $_REQUEST['id'];

		$socialcodebonuses = new SocialCodeBonuses();
		$bonus_details = $socialcodebonuses->getSocialCodeBonus($bonusid);

		foreach ($bonus_details as $k => $bonus_d)
		{
			$bonus = $bonus_d;
		}
		$this->view->bonus = $bonus;

		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$bonus_form = new Application_Form_SocialCodeBonuses();
			$this->bonus = $bonus_form->validate($_POST);


			if ($this->bonus)
			{
				$bonus_form->UpdateData($_POST);
				$this->_redirect(APP_BASE . 'socialcode/bonuses?flg=suc');
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			else
			{
				$bonus_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
	}

	public function formblocksettingsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$blocks_settings = new FormBlocksSettings();
		$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);

		foreach ($blocks_settings_array as $key => $value)
		{
			$settings_array[$value['block']][] = $value;
		}
		$blocks_master = array('ebm', 'ebmii', 'goa', 'goaii');

		foreach ($blocks_master as $block)
		{
			$settings_master[$block] = $settings_array[$block];
		}
		$this->view->blocks_settings = $settings_master;

		if ($this->getRequest()->isPost())
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			if($_POST['save_changes'] == '1'){
				foreach($_POST['form_blocks'] as $blockid=>$block_options){
					foreach($block_options as $option_id => $option_values){
						$stmb = Doctrine::getTable('FormBlocksSettings')->find($option_id);
						$stmb->available = $option_values['available'];
						$stmb->shortcut = strtoupper($option_values['shortcut']);
						$stmb->save();
					}
				}
			}
			$this->_redirect(APP_BASE . 'socialcode/formblocksettings');
		}

	}

	public function blockmeasuresoptionsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$blocks_settings = new FormBlocksSettings();
		$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if($has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->view->has_edit_permissions = 1;
		}  else{
			$this->view->has_edit_permissions = 0;
		}
		
		
		foreach ($blocks_settings_array as $key => $value)
		{
			$settings_array[$value['block']][] = $value;
		}
		$blocks_master = array('measures');

		foreach ($blocks_master as $block)
		{
			$settings_master[$block] = $settings_array[$block];
		}

		if(empty($settings_master['measures']))
		{
			// 	add defaults - if no measures options exist
			$add_default_form =  new Application_Form_FormBlockDefaultOptions();
			$return = $add_default_form->save_default_options("measures");

			$this->_redirect(APP_BASE . 'socialcode/blockmeasuresoptions');
		}

		$measures_array['measures'] = $blocks_settings->get_block($clientid, "measures", true);

		$this->view->blocks_settings = $measures_array;

		if ($this->getRequest()->isPost())
		{
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_POST['save_changes'] == '1'){
				foreach($_POST['form_blocks'] as $blockid=>$block_options){
					foreach($block_options as $option_id => $option_values){
						if(!empty($option_values['option_name']) ){
							$stmb = Doctrine::getTable('FormBlocksSettings')->find($option_id);
							$stmb->option_name = $option_values['option_name'];
							$stmb->available = $option_values['available'];
							$stmb->shortcut = strtoupper($option_values['shortcut']);
							$stmb->save();
						}
					}
				}
			}
			$this->_redirect(APP_BASE . 'socialcode/blockmeasuresoptions');
		}

	}
	public function blocksgbxiactionsAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if($has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->view->has_edit_permissions = 1;
		}  else{
			$this->view->has_edit_permissions = 0;
		}
		
		
		$blocks_settings = new FormBlocksSettings();
		$blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);

		foreach ($blocks_settings_array as $key => $value)
		{
			$settings_array[$value['block']][] = $value;
		}
		$blocks_master = array('sgbxi_actions');

		foreach ($blocks_master as $block)
		{
			$settings_master[$block] = $settings_array[$block];
		}

		$sgbxi_actions['sgbxi_actions'] = $blocks_settings->get_block($clientid, "sgbxi_actions", true);

		$this->view->blocks_settings = $sgbxi_actions;

		if ($this->getRequest()->isPost())
		{
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			
			if($_POST['save_changes'] == '1'){
				foreach($_POST['form_blocks'] as $blockid=>$block_options){
					foreach($block_options as $option_id => $option_values){
						if(!empty($option_values['option_name']) ){
							$stmb = Doctrine::getTable('FormBlocksSettings')->find($option_id);
							$stmb->option_name = $option_values['option_name'];
							$stmb->available = $option_values['available'];
							$stmb->shortcut = strtoupper($option_values['shortcut']);
							$stmb->coordinator_notification = $option_values['coordinator_notification'];
							$stmb->save();
						}
					}
				}
			}
			$this->_redirect(APP_BASE . 'socialcode/blocksgbxiactions');
		}
	}


	public function addpricelistgroupAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$private = false;

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$plg = new SocialCodePriceListGroups();
		$plg_form = new Application_Form_SocialCodePriceListGroups();

		if ($this->getRequest()->isPost())
		{
			if($_POST['name'])
			{
				$plg_form->save_price_list_group($_POST);
			}
		}
	}

	public function listpricelistgroupAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$plg = new SocialCodePriceListGroups();
		$plg_form = new Application_Form_SocialCodePriceListGroups();

		if($_REQUEST['op'] == 'del' && strlen($_REQUEST['grid']) >'0')
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			$plg_form->delete_price_list_group($_REQUEST['grid']);
			$this->_redirect(APP_BASE.'socialcode/listpricelistgroup');
			exit;
		}

		$groups = $plg->get_all_groups($clientid);
		$this->view->groups = $groups;
	}

	public function editpricelistgroupAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$group = trim(rtrim($_REQUEST['grid']));

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$plg = new SocialCodePriceListGroups();
		$plg_form = new Application_Form_SocialCodePriceListGroups();

		if ($this->getRequest()->isPost())
		{
			if($_POST['name'])
			{
				$plg_form->edit_price_list_group($_POST, $group);
				$this->_redirect(APP_BASE.'socialcode/listpricelistgroup');
				exit;
			}
		}

		$groups = $plg->get_group_details($group);
		$this->retainValues($groups);
	}
	
	// Maria:: Migration ISPC to CISPC 08.08.2020
	//get view list of custom settings form blocks - ISPC-2454
	public function formblockcustomsettingsAction(){
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $this->logininfo->clientid;
	
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
	
		if($_GET['action']=='delete')
		{
			$entity = Doctrine::getTable('FormBlockCustomSettings')->find($_GET['id']);
			$entity->delete();
			$this->_redirect(APP_BASE . "socialcode/formblockcustomsettings");
		}
	
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$form_blocks_order = new FormBlocksOrder();
			$form_blocks_order_arr = $form_blocks_order->get_blocks_order($clientid);
			
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
	
			$columns_array = array(
					"0" => "block_name",
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
			$fdoc1->from('FormBlockCustomSettings');
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
				//$fdoc1->andWhere("(lower(name) like ?)", array("%" . trim($search_value) . "%"));
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
				$block_deleteable = true;
				foreach($form_blocks_order_arr as $fb)
				{
					if(strpos($fb['box_order'], $mdata['block_abbrev']) !== false)
					{
						$block_deleteable = false;
						break;
					}
				}

				$link = '%s';
				$resulted_data[$row_id]['block_name'] = sprintf($link,$mdata['block_name']);
	
				if($block_deleteable)
				{
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'socialcode/addformblockcustomsettings?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				}
				else 
				{
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'socialcode/addformblockcustomsettings?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
				}
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	public function shortcodeblocksAction(){
        $clientid = $this->logininfo->clientid;

        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        $shortcutblocks = ShortcutTextBlock::getShortcutTextBlocks();
        $this->view->shortcutblocks = $shortcutblocks;


    }

    public function addshortcodeblockAction(){

        $clientid = $this->logininfo->clientid;

        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        //$shortcutblock  = new ShortcutTextBlock();
        //print_r($shortcutblock->getSql());




        $this->view->shortcutsarr = Courseshortcuts::getFilterCourseData('canview', true);

        if($this->getRequest()->isPost())
        {
            $post = $post = $this->getRequest()->getPost('dynamic', null);
            $shortcutblock  = new ShortcutTextBlock();
            $shortcutblock->blockname = $post['blockname'];
            $shortcutblock->shortcut = $post['shortcut'];

            $shortcutblock->save();

            //var_dump($post); exit;
            $this->_redirect(APP_BASE . "socialcode/shortcodeblocks");


        }





    }
	
	// add/edit form block custom items - ISPC-2454
	public function addformblockcustomsettingsAction()
	{
		$clientid = $this->logininfo->clientid;
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
	
		if($_REQUEST['id'])
		{
			$id = $_REQUEST['id'];
			$block_editable = 'editable';
			
			$block2patient = FormBlockCustomTable::getInstance()->findBy('block_id', $id, Doctrine_Core::HYDRATE_ARRAY);
			
			if(!empty($block2patient))
			{
				$block_editable = 'noteditable';
			}
		}
		
		$saved_values = $this->_formblockcustomsettings_GatherDetails($id);

		$form = new Application_Form_FormBlockCustomItems(array(
				'_block_name'           => 'FormBlockCustomItems',
				'_block_editable' => $block_editable,
		));
			
		//print_r($saved_values); exit;
		$form->create_form_formblockcustomitems($saved_values);
			
			
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
			$post['id'] = $_POST['id'];
			$post['clientid'] = $clientid;
			$post['block_abbrev'] = $_POST['block_abbrev'];
			$post['block_name'] = $_POST['block_name'];
			$post['block_type'] = $_POST['content_item_type_hidd'];
			foreach($_POST['block_content'] as $vc)
			{
				$post['block_content'][] = $vc;
			}
	
			//var_dump($post); exit;
			$fbsave  = $form->save_form_formblockcustomsettings($post);
	
			if($_POST['id'])
			{
				$this->_redirect(APP_BASE . "socialcode/formblockcustomsettings");
			}
	
		}
	}
	
	private function _formblockcustomsettings_GatherDetails( $id = null)
	{
		$saved_formular_final = array();
		if($id)
		{
			$saved_formular= FormBlockCustomSettingsTable::getInstance()->findOneBy('id', $id, Doctrine_Core::HYDRATE_RECORD);
		}
		//print_r($saved_formular);exit;
		if(!$saved_formular)
		{
			$saved_formular= FormBlockCustomSettingsTable::getInstance()->getFieldNames();
				
			foreach($saved_formular as $kcol=>$vcol)
			{
				$saved_formular_final[$vcol]['colprop'] = FormBlockCustomSettingsTable::getInstance()->getDefinitionOf($vcol);
				$saved_formular_final[$vcol]['value'] = null;
			}
		}
		else
		{
			foreach($saved_formular as $kcol=>$vcol)
			{
				$saved_formular_final[$kcol]['colprop'] = FormBlockCustomSettingsTable::getInstance()->getDefinitionOf($kcol);
				$saved_formular_final[$kcol]['value'] = $vcol;
			}
		}
		//print_r($saved_formular_final); exit;
		return $saved_formular_final;
	}


    /**
     * ISPC-2698,elena,22.12.2020
     *
     * @throws Exception
     */
    public function addclientoptionsblockAction(){

        $clientid = $this->logininfo->clientid;

        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        //$optblock = new ClientOptionsBlocks();
        //print_r($optblock->getSql());


        $this->view->shortcutsarr = Courseshortcuts::getFilterCourseData('canview', true);

        if($this->getRequest()->isPost())
        {
            $post = $this->getRequest()->getPost('optionsblock', null);
            $optionsblock  = new ClientOptionsBlocks();
            $optionsblock->blockname = $post['blockname'];
            $optionsblock->headline = $post['headline'];
            $optionsblock->shortcut = $post['shortcut'];
            $optionsblock->options = json_encode($post['options']);
            $optionsblock->clientid = $clientid;


            $optionsblock->save();

            //var_dump($post); exit;
            $this->_redirect(APP_BASE . "socialcode/clientoptionsblocks");


        }





    }

    /**
     * ISPC-2698,elena,22.12.2020
     *
     * @throws Exception
     */
    public function editclientoptionsblockAction(){

        $clientid = $this->logininfo->clientid;

        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        //$optblock = new ClientOptionsBlocks();
        //print_r($optblock->getSql());


        $this->view->shortcutsarr = Courseshortcuts::getFilterCourseData('canview', true);
        $block_id = $this->getRequest()->getParam('id');
        $optionsblock = new ClientOptionsBlocks();
        $blockdata = $optionsblock->getTable()->find($block_id, Doctrine_Core::HYDRATE_ARRAY);
        $this->view->blockdata = $blockdata;
        #print_r($blockdata);


        if($this->getRequest()->isPost())
        {
            $post = $this->getRequest()->getPost('optionsblock', null);
            $optionsblock  = new ClientOptionsBlocks();
            $optionsblock->blockname = $post['blockname'];
            $optionsblock->headline = $post['headline'];
            $optionsblock->shortcut = $post['shortcut'];
            $optionsblock->options = json_encode($post['options']);
            $optionsblock->clientid = $clientid;
            $optionsblock->id = $block_id;


            $optionsblock->replace();

            //var_dump($post); exit;
            $this->_redirect(APP_BASE . "socialcode/clientoptionsblocks");


        }


    }

    /**
     * ISPC-2698,elena,22.12.2020
     */
    public function clientoptionsblocksAction(){

        $clientid = $this->logininfo->clientid;

        $has_edit_permissions = Links::checkLinkActionsPermission();
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        $clientoptionsblocks = ClientOptionsBlocks::getClientOptionsBlocks($clientid);
        $this->view->clientoptionsblocks = $clientoptionsblocks;


    }

}
?>