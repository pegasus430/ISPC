<?php

	require_once("Pms/Form.php");

	class Application_Form_GroupDefault extends Pms_Form {

		public function InsertData($post, $menus = false, $misc = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$q = Doctrine_Query::create()
				->delete('GroupDefaultPermissions')
				->where('master_group_id= ?', $_REQUEST['id'])
				->andWhere('clientid= ?', $clientid);
			//delete only what we need!
			if($misc === true)
			{
				$q->andWhere('misc_id > 0');
			}
			else if($menus === false)
			{
				$q->andWhere('pat_nav_id > 0');
			}
			else
			{
				$q->andWhere('menu_id > 0');
			}
			$q->execute();

			if($misc === true)
			{
				foreach($post['m_hdnmoduleid'] as $tabid)
				{
					if($post['m_canview'][$tabid] == '1' || $post['m_canedit'][$tabid] == '1' || $post['m_canadd'][$tabid] == '1' || $post['m_candelete'][$tabid] == '1')
					{
						$user = new GroupDefaultPermissions();
						$user->master_group_id = $_REQUEST['id'];
						$user->clientid = $clientid;
						$user->misc_id = $tabid;
						$user->canadd = $post['m_canadd'][$tabid];
						$user->canedit = $post['m_canedit'][$tabid];
						$user->canview = $post['m_canview'][$tabid];
						$user->candelete = $post['m_candelete'][$tabid];
						$user->save();
					}
				}
			}
			else
			{
				foreach($post['hdnmoduleid'] as $tabid)
				{
					if($post['canview'][$tabid] == '1' || $post['canedit'][$tabid] == '1' || $post['canadd'][$tabid] == '1' || $post['candelete'][$tabid] == '1')
					{
						$user = new GroupDefaultPermissions();
						$user->master_group_id = $_REQUEST['id'];
						$user->clientid = $clientid;

						if($menus === false)
						{
							$user->pat_nav_id = $tabid;
						}
						else
						{
							$user->menu_id = $tabid;
						}
						$user->canadd = $post['canadd'][$tabid];
						$user->canedit = $post['canedit'][$tabid];
						$user->canview = $post['canview'][$tabid];
						$user->candelete = $post['candelete'][$tabid];
						$user->save();
					}
				}
			}
		}

		public function InsertCourseData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$q = Doctrine_Query::create()
				->delete('GroupCourseDefaultPermissions')
				->where('master_group_id= ?', $_REQUEST['id'])
				->andWhere('clientid= ?', $clientid);

			$q->execute();

			foreach($post['hiddmodid'] as $tabid)
			{
				if($post['canview'][$tabid] == '1' || $post['canedit'][$tabid] == '1' || $post['canadd'][$tabid] == '1' || $post['candelete'][$tabid] == '1')
				{
					$user = new GroupCourseDefaultPermissions();
					$user->master_group_id = $_REQUEST['id'];
					$user->clientid = $clientid;
					$user->shortcutid = $tabid;
					$user->canadd = $post['canadd'][$tabid];
					$user->canedit = $post['canedit'][$tabid];
					$user->canview = $post['canview'][$tabid];
					$user->candelete = $post['candelete'][$tabid];
					$user->save();
				}
			}
		}

		public function insert_icon_data($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$q = Doctrine_Query::create()
				->delete('GroupIconsDefaultPermissions')
				->where('master_group_id= ?',  $_REQUEST['id'])
				->andWhere('clientid= ?', $clientid);
			$q->execute();

			//insert system icons perms
			foreach($post['hiddmodid']['system'] as $s_key => $tabid)
			{
				if($post['canview']['system'][$tabid] == '1')
				{
					$user = new GroupIconsDefaultPermissions();
					$user->master_group_id = $_REQUEST['id'];
					$user->clientid = $clientid;
					$user->icon = $tabid;
					$user->icon_type = 'system';
					$user->canadd = '0';
					$user->canedit = '0';
					$user->canview = $post['canview']['system'][$tabid];
					$user->candelete = '';
					$user->save();
				}
			}

			//insert custom icons perms
			foreach($post['hiddmodid']['custom'] as $c_key => $tabid)
			{
				if($post['canview']['custom'][$tabid] == '1')
				{
					$user = new GroupIconsDefaultPermissions();
					$user->master_group_id = $_REQUEST['id'];
					$user->clientid = $clientid;
					$user->icon = $tabid;
					$user->icon_type = 'custom';
					$user->canadd = '0';
					$user->canedit = '0';
					$user->canview = $post['canview']['custom'][$tabid];
					$user->candelete = '';
					$user->save();
				}
			}
		}


		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param unknown $post
		 */
		public function save_icon_data($post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $groupms = new GroupMaster();
		    $master_group_arr = $groupms->getGroupMaster();
		    $master_group = array_keys($master_group_arr);
		    
		    $q = Doctrine_Query::create()
		    ->delete('GroupIconsDefaultPermissions')
		    ->where('clientid= ?', $clientid);
		    $q->execute();
		  

		    foreach($post['hiddmodid']['system'] as $s_key => $tabid)
		    {
		        foreach ($master_group as $gr_id){
		            
		            if($post['canview'][$gr_id]['system'][$tabid] == '1')
		            {
		                $user = new GroupIconsDefaultPermissions();
		                $user->master_group_id = $gr_id;
		                $user->clientid = $clientid;
		                $user->icon = $tabid;
		                $user->icon_type = 'system';
		                $user->canadd = '0';
		                $user->canedit = '0';
		                $user->canview = $post['canview'][$gr_id]['system'][$tabid];
		                $user->candelete = '';
		                $user->save();
		            }
		        }

		    }
		    
		    foreach($post['hiddmodid']['custom'] as $c_key => $tabid)
		    {
		        foreach ($master_group as $gr_id){
		            
		            if($post['canview'][$gr_id]['custom'][$tabid] == '1')
		            {
		                $user = new GroupIconsDefaultPermissions();
		                $user->master_group_id = $gr_id;
		                $user->clientid = $clientid;
		                $user->icon = $tabid;
		                $user->icon_type = 'custom';
		                $user->canadd = '0';
		                $user->canedit = '0';
		                $user->canview = $post['canview'][$gr_id]['custom'][$tabid];
		                $user->candelete = '';
		                $user->save();
		            }
		            
		        }

		    }
		}
		
		//default perms are only for custom icons
		public function insert_default_perms($client = false, $icon = false, $icon_type = 'custom')
		{
			$group = Doctrine_Query::create()
				->select('*')
				->from('GroupMaster')
				->where('id !=  1 and id != 2');
			$groups_res = $group->fetchArray();

			if($groups_res && $client && $icon)
			{
				foreach($groups_res as $k_gr => $v_group_data)
				{
					$master_insert[] = array(
						'clientid' => $client,
						'master_group_id' => $v_group_data['id'],
						'icon' => $icon,
						'icon_type' => $icon_type,
						'canadd' => '0',
						'candedit' => '0',
						'canview' => '1',
						'candelete' => '0',
					);
				}

				if($master_insert)
				{
					$collection = new Doctrine_Collection('GroupIconsDefaultPermissions');
					$collection->fromArray($master_insert);
					$collection->save();
				}
			}
		}

		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param unknown $post
		 * @param boolean $menus
		 * @param boolean $misc
		 */
		public function SaveData($post, $menus=false, $misc = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $groupms = new GroupMaster();
		    $master_group_arr = $groupms->getGroupMaster();
		    $master_group = array_keys($master_group_arr);
		    
		    
		    $q = Doctrine_Query::create()
		    ->delete('GroupDefaultPermissions')
		    ->where('clientid= ?', $clientid);
		    
		    if($misc === true)
		    {
		        $q->andWhere('misc_id > 0');
		    }
		    else if($menus === false)
		    {
		        $q->andWhere('pat_nav_id > 0');
		    }
		    else
		    {
		        $q->andWhere('menu_id > 0');
		    }
		    $q->execute();
		    
		    
		    if($misc === true)
		    {
		        foreach($post['m_hdnmoduleid'] as $tabid)
		        { 
		            foreach ($master_group as $gr_id){
		               
		                if($post['m_canview'][$gr_id][$tabid] == '1' || $post['m_canedit'][$gr_id][$tabid] == '1' ){
		                    $user = new GroupDefaultPermissions();
		                    
		                    $user->master_group_id = $gr_id;
		                    $user->clientid = $clientid;
		                    $user->misc_id = $tabid;
		                    $user->canadd = $post['m_canadd'][$gr_id][$tabid];
		                    $user->canedit = $post['m_canedit'][$gr_id][$tabid];
		                    $user->canview = $post['m_canview'][$gr_id][$tabid];
		                    $user->candelete = $post['m_candelete'][$gr_id][$tabid];
		                    $user->save();
		                }
		            }
		        }        
		    } 
		    else 
		    { 
		        foreach($post['hdnmoduleid'] as $tabid)
		        {
		            foreach ($master_group as $gr_id){
		               
		                if($post['canview'][$gr_id][$tabid] == '1' || $post['canedit'][$gr_id][$tabid] == '1'){
		                    
		                    $user = new GroupDefaultPermissions();
		                    
		                    $user->master_group_id = $gr_id;
		                    $user->clientid = $clientid;
		                    
                            if($menus === false)
		                    {
		                        $user->pat_nav_id = $tabid;
		                    }
		                    else
		                    {
		                        $user->menu_id = $tabid;
		                    }
		                    $user->canadd = $post['canadd'][$gr_id][$tabid];
		                    $user->canedit = $post['canedit'][$gr_id][$tabid];
		                    $user->canview = $post['canview'][$gr_id][$tabid];
		                    $user->candelete = $post['candelete'][$gr_id][$tabid];
		                    $user->save();
		                }
		            }
		        }
		    }
		}
		
		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param unknown $post
		 */
		public function SaveCourseData($post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $groupms = new GroupMaster();
		    $master_group_arr = $groupms->getGroupMaster();
		    $master_group = array_keys($master_group_arr);
		    		    
		    $q = Doctrine_Query::create()
		    ->delete('GroupCourseDefaultPermissions')
		    ->Where('clientid= ?', $clientid);
		    
		    $q->execute();
		    
		    foreach($post['hiddmodid'] as $tabid)
		    {
		        foreach ($master_group as $gr_id){
		            
		            if($post['canview'][$gr_id][$tabid] == '1' || $post['canedit'][$gr_id][$tabid] == '1' || $post['canadd'][$gr_id][$tabid] == '1' || $post['candelete'][$gr_id][$tabid] == '1')
		            {
		                $user = new GroupCourseDefaultPermissions();
		                $user->master_group_id = $gr_id;
		                $user->clientid = $clientid;
		                $user->shortcutid = $tabid;
		                $user->canadd = $post['canadd'][$gr_id][$tabid];
		                $user->canedit = $post['canedit'][$gr_id][$tabid];
		                $user->canview = $post['canview'][$gr_id][$tabid];
		                $user->candelete = $post['candelete'][$gr_id][$tabid];
		                $user->save();
		            }
		        }
		        
		    }
		}

		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param boolean $mastergroup
		 * @param boolean $menus
		 * @param boolean $misc
		 */
		public function save_historyData($mastergroup = false, $menus = false, $misc = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $lastid_his = Doctrine_Query::create()
		    ->select('max(bulkid) as lastbulkid')
		    ->from('GroupDefaultPermissionsHistory');
		    $last_bulkid_arr = $lastid_his->fetchArray();
		    
		    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
		    $new_bulkid = $last_bulkid + 1 ;
		    
		    $Bulk = '1';
		    if ($mastergroup){
		        $Bulk = '0';
		    }
		    
		    $dates_for_hist = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupDefaultPermissions')
		    ->Where('clientid =?', $clientid) ;
		    
		    if(!empty($mastergroup)){
		        $dates_for_hist->andwhere('master_group_id= ?', $mastergroup);
		    }
		    if($misc === true)
		    {
		        $dates_for_hist->andWhere('misc_id > 0');
		    }
		    else if($menus === false)
		    {
		        $dates_for_hist->andWhere('pat_nav_id > 0');
		    }
		    else
		    {
		        $dates_for_hist->andWhere('menu_id > 0');
		    }
		    
		    $data_for_hist_array= $dates_for_hist->fetchArray();
		    
		    if(!empty($data_for_hist_array))
		    {
		        foreach($data_for_hist_array as $key => $vals)
		        {
		            $data_for_hist_array[$key]["id"] = NULL;
		            $data_for_hist_array[$key]["bulk"] = $Bulk;
		            $data_for_hist_array[$key]["bulkid"] = $new_bulkid;
		            $data_for_hist_array[$key]["id_gdp"] = $vals["id"];
		        }
		        
		        if(count($data_for_hist_array) > 0)
		        {
		            $collection = new Doctrine_Collection('GroupDefaultPermissionsHistory');
		            $collection->fromArray($data_for_hist_array);
		            $collection->save();
		        }
		        
		    }
		}
	

		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param boolean $mastergroup
		 */
		public function save_historyIconsData($mastergroup = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $lastid_his = Doctrine_Query::create()
		    ->select('max(bulkid) as lastbulkid')
		    ->from('GroupIconsDefaultPermissionsHistory');
		    $last_bulkid_arr = $lastid_his->fetchArray();
		    
		    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
		    $new_bulkid = $last_bulkid + 1 ;
		    
		    $Bulk = '1';
		    if ($mastergroup){
		        $Bulk = '0';
		    }
		    
		    $dates_for_hist = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupIconsDefaultPermissions')
		    ->Where('clientid =?', $clientid) ;
		    
		    if(!empty($mastergroup)){
		        $dates_for_hist->andwhere('master_group_id= ?', $mastergroup);
		    }
		    
		    $data_for_hist_array= $dates_for_hist->fetchArray();
		    
		    if(!empty($data_for_hist_array))
		    {
		        foreach($data_for_hist_array as $key => $vals)
		        {
		            $data_for_hist_array[$key]["id"] = NULL;
		            $data_for_hist_array[$key]["bulk"] = $Bulk;
		            $data_for_hist_array[$key]["bulkid"] = $new_bulkid;
		            $data_for_hist_array[$key]["id_gidp"] = $vals["id"];
		        }
		        
		        if(count($data_for_hist_array) > 0)
		        {
		            $collection = new Doctrine_Collection('GroupIconsDefaultPermissionsHistory');
		            $collection->fromArray($data_for_hist_array);
		            $collection->save();
		        }
		        
		    }
		}
		

		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 23.10.2019
		 * @param boolean $mastergroup
		 * @param boolean $menus
		 * @param boolean $misc
		 */
		public function save_coursehistoryData($mastergroup = false, $menus = false, $misc = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $lastid_his = Doctrine_Query::create()
		    ->select('max(bulkid) as lastbulkid')
		    ->from('GroupCourseDefaultPermissionsHistory');
		    $last_bulkid_arr = $lastid_his->fetchArray();
		    
		    $last_bulkid = $last_bulkid_arr[0]['lastbulkid'];
		    $new_bulkid = $last_bulkid + 1 ;
		    
		    $Bulk = '1';
		    if ($mastergroup){
		        $Bulk = '0';
		    }
		    
		    $dates_for_hist = Doctrine_Query::create()
		    ->select('*')
		    ->from('GroupCourseDefaultPermissions')
		    ->Where('clientid =?', $clientid) ;
		    
		    if(!empty($mastergroup)){
		        $dates_for_hist->andwhere('master_group_id= ?', $mastergroup);   
		    }
		    
		    $data_for_hist_array= $dates_for_hist->fetchArray();
		    
		    if(!empty($data_for_hist_array))
		    {
		        foreach($data_for_hist_array as $key => $vals)
		        {
		            $data_for_hist_array[$key]["id"] = NULL;
		            $data_for_hist_array[$key]["bulk"] = $Bulk;
		            $data_for_hist_array[$key]["bulkid"] = $new_bulkid;
		            $data_for_hist_array[$key]["id_gcdp"] = $vals["id"];
		        }
		        
		        if(count($data_for_hist_array) > 0)
		        {
		            $collection = new Doctrine_Collection('GroupCourseDefaultPermissionsHistory');
		            $collection->fromArray($data_for_hist_array);
		            $collection->save();
		        }
		        
		    }
		}
	}

?>