<?php

class OverviewController extends Pms_Controller_Action 
{

    public function init()
    {
    	/* Initialize action controller here */
    	$this->view->getheight = "onload='getheight();'";
    	
    	
    	$this
    	->setActionsWithPatientinfoAndTabmenus([
    	    /*
    	     * actions that have the patient header
    	     */
    	])
    	->setActionsWithJsFile([
    	    /*
    	     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
    	     */
    	    'wallnews',
    	    'overviewefa',
    	])
    	->setActionsWithLayoutNew([
    	    /*
    	     * actions that will use layout_new.phtml
    	     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
    	     */
    	    'wallnews',
    	])
    	;
    }

		public function indexAction()
		{
		    // Ancuta 02.06.2020  // Maria:: Migration ISPC to CISPC 08.08.2020
		    $this->_redirect(APP_BASE . "overview/overview"); 
		    exit;
		    // --
			$this->_helper->layout->setLayout('layout_index');
			$this->_helper->viewRenderer->setNoRender();
			$menuarr = Menus::getLeftParentMenus(1);

			// @TODO proper query here
			foreach($menuarr as $key => $val)
			{
				$temp = array();
				$temp['text'] = $val['menu_title'];

				$fdoc = Doctrine_Query::create()
					->select('*')
					->from('Menus')
					->where('isdelete = ?', 0)
					->andWhere("parent_id='" . $val['id'] . "'")
					->andWhere('left_position=1');
				$submenuarr = $fdoc->fetchArray();

				if($submenuarr)
				{
					$items = array();
					for($i = 0; $i < count($submenuarr); $i++)
					{
						$items[] = array('text' => $submenuarr[$i]['menu_title'], 'source' => $submenuarr[$i]['menu_link']);
					}
					if(count($items) > 0)
					{
						$temp['items'] = $items;
					}
					else
					{
						$temp['source'] = $val['menu_link'];
					}
				}

				$finalarr[] = $temp;
			}

			$this->view->jsonmenu = json_encode($finalarr);
		}

		public function overviewAction()
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    //ISPC-2827 Ancuta 27.03.2021
		    if($logininfo->isEfaClient == '1' && $logininfo->isEfaUser == '1'){
		        $this->_redirect(APP_BASE . "overview/overviewefa");
		    }
		    
		    /**
		     * ISPC-2282
		     * i've added box Pinnwand , where user can live add new, 
		     * save is performed by same action, so no need for other permisions
		     */
		    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('__action')) {
		        
		        $responsArr = $this->wallnewsAction();
		        
		        $this->wallnewsAction();
		        
		        switch ($this->getRequest()->getParam('__action')) {
		            
		            case "addWallNews":
		                
		                $responsArr = $this->_wallnews_addWallNews($this->getRequest()->getPost());
		               
		                break;
		            
		            case "deleteWallNews":
		                
		                $responsArr = $this->_wallnews_deleteWallNews($this->getRequest()->getPost());
		               
		                break;
		            
		        }
		        
		        Zend_Json::$useBuiltinEncoderDecoder = true;
		        $responsJson =  Zend_Json::encode($responsArr);
		         
		        $this->getResponse()
		        ->setHeader('Content-Type', 'application/json')
		        ->setBody($responsJson)
		        ->sendResponse();
		        
		        exit; //for readability
		    }
		    
		    
		    set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$default_boxes = array('1', '2', '3', '4', '5', '6', '8');

			$this->view->client_id = $clientid;
			$this->view->user_id = $userid;
			$this->view->userid = $logininfo->userid;
			$this->view->hidemagic = $hidemagic;
			$this->view->clientid = $logininfo->clientid;

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('overview', $logininfo->userid, 'canview');
			$Notifications_arr  = new Notifications();
			$user_notifications_settings = $Notifications_arr ->get_notification_settings($userid);
			$p_users = new PatientUsers();
			$user_patients = $p_users->getUserPatients($logininfo->userid); //get user's patients by permission

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			
			$modules = new Modules();
			$this->view->ModulePrivileges = $modules->get_client_modules($clientid);
			//156 = Dashboard :: Grouped actions by patient
			if($user_notifications_settings[$userid]['dashboard_grouped'] == '1' && $this->view->ModulePrivileges['156'] == "1"){
				$this->view->dashboard_grouped = "1";
			} else{
				$this->view->dashboard_grouped = "0";
			}
			
			//hospiz hack
			if($logininfo->hospiz != 1 || $logininfo->client == 32)
			{
				$cover = Doctrine::getTable('Overview')->findBy('clientid', $logininfo->clientid);
				if($cover)
				{
				    
				    // get user setting -  TODO-1967 
				    $oc = new OverviewCookie();
				    $u_overview_box_setting_array = $oc->getCookieData($logininfo->userid, "overview");
				    $overview_user_setting = $u_overview_box_setting_array[0]['useroption'];
				    //-- 
					$carray = $cover->toArray();
					if(count($carray) > 0 && strlen($carray[0]['overviewboxid']) > '0')
					{
						$boxary = explode(",", $carray[0]['overviewboxid']);
						$boxcondition = isset($overview_user_setting) && !empty($overview_user_setting) ? $overview_user_setting: $carray[0]['boxconditions']; // change to user condition - TODO-1967
					}
				}
			}
			else
			{
				$perms = Pms_CommonData::getHospizMenus();
				$carray = '';
				$boxary = $perms['overviewboxes'];
				$boxcondition = '';
			}
//			if no box data found then get defaults
			if(strlen($boxary) == '0')
			{
				$boxary = $default_boxes;
			}

			if($logininfo->usertype != 'SA')
			{
				$eipd = Doctrine_Query::create()
					->select('epid')
					->from('PatientQpaMapping')
					->where('userid =' . $logininfo->userid)
					->orderBy('id DESC');
				$epidarray = $eipd->fetchArray();

				$comma = ",";
				$epidval = "'0'";
				foreach($epidarray as $key => $val)
				{
					$epidval .= $comma . "'" . $val['epid'] . "'";
					$comma = ",";
				}
			}
			else
			{
				if($logininfo->clientid > 0)
				{
					$eipd = Doctrine_Query::create()
						->select('epid')
						->from('PatientQpaMapping')
						->where('clientid =' . $logininfo->clientid)
						->orderBy('id DESC')
						->limit(15);
					$epidarray = $eipd->fetchArray();

					$comma = ",";
					$epidval = "'0'";
					foreach($epidarray as $key => $val)
					{
						$epidval .= $comma . "'" . $val['epid'] . "'";
						$comma = ",";
					}
				}
				else
				{
					$epidval = "'0'";
				}
			}

			$ipidq = Doctrine_Query::create()
				->select('e.ipid')
				->from('EpidIpidMapping e')
				->where("e.epid in (" . $epidval . ")")
				->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
				->orderby('e.id DESC');
			$ipidarray = $ipidq->fetchArray();

			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}
			$columnarray = array("pk" => "id", "ln" => "last_name");


			$lastipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping e')
				->where("e.clientid = " . $logininfo->clientid)
				->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
				->orderby('e.id DESC')
				->limit(15);
			$lastipidarray = $lastipid->fetchArray();

			$comma = ",";
			$newipidval = "'0'";
			foreach($lastipidarray as $key => $val)
			{
				$newipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			$user = Doctrine_Query::create()
				->select("*")
				->from('User')
				->where('clientid = ' . $logininfo->clientid . ' or usertype="SA"')
				->andWhere('isactive=0 and isdelete = 0 and no10contactsbox = 0')
				->orderBy('last_name ASC');
			$userarray = $user->fetchArray();

			$comma = ",";
			$usercomma = "'0'";
			if(count($userarray) > 0)
			{
				foreach($userarray as $key => $valu)
				{
					$clientUsersArray[$valu['id']] = $valu;
					$usercomma .= $comma . "'" . $valu['id'] . "'";
					$comma = ",";
				}
			}

			//NOT USED??
			
//			$userdel = Doctrine_Query::create()
//				->select("*")
//				->from('User')
//				->where('clientid = ' . $logininfo->clientid . ' or usertype="SA"')
//				->andWhere('isactive=0 and isdelete = 1')
//				->orderBy('last_name ASC');
//			$deluserarray = $userdel->fetchArray();
//
//
//			$comma = ",";
//			$deleteusercomma = "'0'";
//			if(count($deluserarray) > 0)
//			{
//				foreach($deluserarray as $key => $valu)
//				{
//					$deleteusercomma .= $comma . "'" . $valu['id'] . "'";
//					$comma = ",";
//				}
//			}

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray['DESC'];

			$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
			$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
			$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
			$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
			$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
			$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*,";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			/* ------------ BOX "ID:1" -  "Letzten "XX" Kontakte" Start---------------- */
			$clientarr = Pms_CommonData::getClientData($logininfo->clientid);
			$ust = new UserSettings();
			$ustarr = $ust->getUserSettings($userid);
			
			if(in_array('1', $boxary))
			{
				$patient = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster pm')
					->andWhere('isdelete = 0 and last_update_user in(' . $usercomma . ')')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $logininfo->clientid)
					->andWhere('ep.ipid=pm.ipid')
					->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')')
					->orderBy('pm.last_update DESC');
				if($ustarr['entries_last_xx_days'] > 0)
				{
					$comp_date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $ustarr['entries_last_xx_days'], date('Y')));
					$patient->andWhere('DATE(pm.last_update) >=?', $comp_date);
				}
					
				$patientarray = $patient->fetchArray();
				$maxcontact = $clientarr[0]['maxcontact'];
				
				if(!empty($ustarr['last_xx_entries']))
				{
					$limit = $ustarr['last_xx_entries'];
				}
				else 
				{
					$limit = 10;
					if($maxcontact > 0)
					{
						$limit = $maxcontact;
					}
				}

				$patient->select($sql);
				if($logininfo->hospiz == 1)
				{
					$patient->where('ishospiz = 1');
				}
				
				$patient->orderBy('last_update DESC');
				$patient->limit($limit);
				$patient->offset($_GET['pgno'] * $limit);
				$patientlimit = $patient->fetchArray();

				//get last update users and patient ipids
				$last_update_user[] = '99999999';
				$patient_ipids[] = '99999999';

				foreach($patientlimit as $k_pat_limit => $v_pat_limit)
				{
					$last_update_users[] = $v_pat_limit['last_update_user'];
					$patient_ipids[] = $v_pat_limit['ipid'];
				}

				$last_update_users = array_values(array_unique($last_update_users));
				$patient_ipids = array_values(array_unique($patient_ipids));

				//get last update users data
				$last_update_users_data = Pms_CommonData::getUsersData($last_update_users);

				//get patients healthinsurance
				$phelathinsurance = new PatientHealthInsurance();
				$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($patient_ipids);

				foreach($healthinsu_array as $k_health_insu => $v_health_insu)
				{
					$healthinsu_arr[$v_health_insu['ipid']] = $v_health_insu;
				}

				$this->view->last_update_users_data = $last_update_users_data;
				$this->view->patient_health_insurances = $healthinsu_arr;

				if($patientarray[0]['count'] > 0)
				{
					$openbox .= "overviewcolumn_content_item1,";
				}

				$grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "lastcontactgrid.html");


				$this->view->contentitem1 = $grid->renderGrid();
				
				
				$contenttitleitem1 = "";
				$contenttitleitem1 = "Letzten " . $limit . " Kontakte";
				if(!empty($ustarr['entries_last_xx_days'])){
    				$contenttitleitem1 .= " / Letzten " . $ustarr['entries_last_xx_days'] . " Tage";
				}
				$this->view->contenttitleitem1 = $contenttitleitem1;
				
			}
			/* ------------ BOX -  "Letzten "XX" Kontakte" END---------------- */

			/* ------------ BOX -  "Statistik" START---------------- */
			if(in_array('8', $boxary))
			{
				$patientqury = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster p')
					->where('isdelete = 0 and isstandbydelete = 0');
				$patientqury->leftJoin("p.EpidIpidMapping e");
				$patientqury->andWhere('e.clientid = ' . $logininfo->clientid);
				$patientcount = $patientqury->fetchArray();

				$this->view->noofpatients = $patientcount[0]['count']; // total patients (NO deleted, NO standby)
				$totnoofpatients +=$noofpatients;

				$actpatientqury = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster p')
					->where('isdelete=0 and isdischarged=0 and isstandby=0 and isstandbydelete = 0');
				$actpatientqury->leftJoin("p.EpidIpidMapping e");
				$actpatientqury->andWhere('e.clientid = ' . $logininfo->clientid);
				$actpatientcount = $actpatientqury->fetchArray();

				$this->view->noofactpatients = $actpatientcount[0]['count']; // total active patients
				$totnoofactpatients += $noofactpatients;

				$dispatientqury = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster p')
					->where('isdelete=0 and isdischarged=1');
				$dispatientqury->leftJoin("p.EpidIpidMapping e");
				$dispatientqury->andWhere('e.clientid = ' . $logininfo->clientid);
				$dispatientcount = $dispatientqury->fetchArray();

				$this->view->noofdispatients = $dispatientcount[0]['count']; // total discharge patients
				$totnoofdispatients += $noofdispatients;

				$standbypatientqury = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster p')
					->where('isdelete=0 and isstandby=1 and isdischarged=0');
				$standbypatientqury->leftJoin("p.EpidIpidMapping e");
				$standbypatientqury->andWhere('e.clientid = ' . $logininfo->clientid);
				$standpatientcount = $standbypatientqury->fetchArray();

				$this->view->noofstandbypatients = $standpatientcount[0]['count']; // total standby patients
				$totalstandby += $noofstandbypatients;

				$this->view->contentitem8 = $this->view->render('overview/overviewstats.html');
				$this->view->contenttitleitem8 = $this->view->translate('Statistik');
			}
			/* ------------ BOX -  "Statistik" END---------------- */

			/* ------------ BOX -  "Aufnahmen der letzten 7 Tage" START---------------- */
			if(in_array('7', $boxary))
			{
				$patient1 = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster pm')
					->where("admission_date between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))) . "'")
					->andWhere('pm.isdelete = 0 and pm.isstandbydelete = 0 and pm.isstandby = 0')
					->andWhere('pm.isdelete = 0')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $logininfo->clientid)
					->andWhere('ep.ipid=pm.ipid')
					->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')')
					->orderBy('pm.admission_date DESC');

				$patientarray2 = $patient1->fetchArray();

				$limit1 = 50;
				$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
				$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
				$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
				$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
				$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
				$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
				$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
				$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
				$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql = "*,";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				}

				$patient1->select($sql);
				$patient1->orderBy('admission_date DESC');
				$patient1->limit($limit1);
				$patient1->offset($_GET['pgno'] * $limit1);

				$patientlimit2 = $patient1->fetchArray();

				$pat_limit_create_users_ids[] = '99999999';
				foreach($patientlimit2 as $k_pat_limit2 => $v_pat_limit2)
				{
					$pat_limit_create_users_ids[] = $v_pat_limit2['create_user'];
				}

				$patient_create_users = Pms_CommonData::getUsersData($pat_limit_create_users_ids);
				$this->view->patient_create_users = $patient_create_users;

				if(count($patientlimit2) > 0)
				{
					$openbox .= "overviewcolumn_content_item7,";
				}
				$grid = new Pms_Grid($patientlimit2, 1, $patientarray2[0]['count'], "lastadmissionsgrid.html");

				$this->view->contentitem7 = $grid->renderGrid();
				$this->view->contenttitleitem7 = $this->view->translate('admissionsofthelast7days');
			}
			/* ------------ BOX -  "Aufnahmen der letzten 7 Tage" END---------------- */

			/* ------------ BOX - "Letzte 5 Aufnahmen" START---------------- */
			if(in_array('2', $boxary))
			{
				$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
				$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
				$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
				$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
				$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
				$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
				$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
				$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
				$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql = "*,";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				}

				$assign = Doctrine_Query::create()
					->select($sql)
					->from('PatientMaster')
					->where("ipid in (" . $ipidval . ") and isdelete=0  and isstandby = 0 and isstandbydelete = 0 ")
					->orderBy('admission_date DESC')
					->limit('5');
				$assignarray = $assign->fetchArray();

				$assigned_ipids[] = '9999999999';
				foreach($assignarray as $k_assign_pat => $v_assign_pat)
				{
					$assigned_ipids[] = $v_assign_pat['ipid'];
				}

				//get patients healthinsurance
				$phelathinsurance = new PatientHealthInsurance();
				$healthinsu_array = $phelathinsurance->get_patients_healthinsurance($assigned_ipids);

				foreach($healthinsu_array as $k_health_insu => $v_health_insu)
				{
					$healthinsu_assigned[$v_health_insu['ipid']] = $v_health_insu;
				}

				$this->view->healthinsurance_assigned_pat = $healthinsu_assigned;

				if(count($assignarray) > 0)
				{
					$openbox .= "overviewcolumn_content_item2,";
				}
				$grid = new Pms_Grid($assignarray, 1, count($assignarray), "lastupdategrid.html");
				$this->view->contentitem2 = $grid->renderGrid();
				$this->view->contenttitleitem2 = "Meine letzten 5 Aufnahmen";
			}
			/* ------------ BOX - "Letzte 5 Aufnahmen" END---------------- */

			/* ------------ BOX - "Bereitschaft "XX" KW" START---------------- */
			if(in_array('4', $boxary))
			{
				$rosterarray = $this->orverviewroster(0, '-7', '+7');
				if(count($rosterarray) > 0)
				{
					$openbox .= "overviewcolumn_content_item4,";
				}
				$this->view->contentitem4 = $rosterarray;
				$this->view->week = date("W", time());
				$this->view->contenttitleitem4 = "Bereitschaft " . date("W", time()) . ". KW";

				if($logininfo->clientid > 0)
				{
					$clientid = $logininfo->clientid;
				}
				else
				{
					$clientid = 0;
				}
			}
			/* ------------ BOX - "Bereitschaft "XX" KW" END---------------- */

			/* ------------ BOX - "Latest News & Events" START---------------- */
			if(in_array('3', $boxary))
			{
				$newmap = Doctrine_Query::create()
					->select("*")
					->from('NewsMaping')
					->where('clientid =' . $clientid . ' and ( userid= 0 or userid=' . $logininfo->userid . ')');
				$maparray = $newmap->fetchArray();

				$comma = ",";
				$newsid = "'0'";
				foreach($maparray as $key => $val)
				{
					$newsid .=$comma . "'" . $val['newsid'] . "'";
				}

				if($logininfo->usertype == 'SA')
				{
					if($logininfo->clientid > 0)
					{
						$where = "clientid=" . $logininfo->clientid . " and isactive=0 and isdelete=0";
//						$where = "clientid=" . $logininfo->clientid . " and isactive=0 and isdelete=0 or ( issystem=1 and isactive=0 and isdelete=0)";
					}
					else
					{
						$where = "isactive=0 and isdelete=0";
					}
				}
				else
				{
					$where = "id in (" . $newsid . ") and  isactive=0 and isdelete=0 or ( issystem=1 and isactive=0 and isdelete=0)";
				}
				//ISPC - 2300 - system bug news
				$news = Doctrine_Query::create()
					->select("*,AES_DECRYPT(news_title,'" . Zend_Registry::get('salt') . "') as news_title,AES_DECRYPT(news_content,'" . Zend_Registry::get('salt') . "') as news_content")
					->from('News')
					->where($where)
					//->orderBy("create_date DESC limit 10");
					->orderBy("create_date DESC");

				$newsarray_all = $news->fetchArray();
				//$this->view->news_array_all = $newsarray;

				$news->limit("10");
				$newsarray = $news->fetchArray();
				$this->view->news_array = $newsarray;
				
				$previleges = new Pms_Acl_Assertion();
				$return = $previleges->checkPrevilege('news', $logininfo->userid, 'canview');

				if($return)
				{
					if(count($newsarray) > 0)
					{
						$openbox .= "overviewcolumn_content_item3,";
					}

					$grid = new Pms_Grid($newsarray, 1, count($newsarray), "newsgrid.html");
					$this->view->contentitem3 = $grid->renderGrid();
				}
				$this->view->contenttitleitem3 = "Latest News & Events";
			}

			//popup settings
			$pop_vis = new PopupVisibility();
			$user_popup_settings = $pop_vis->getUserPopupSettings($logininfo->userid, $logininfo->clientid);
			foreach($user_popup_settings as $kr=>$vr)
			{
				$user_seen_news[] = $vr['newsid'];
			}
			$news_unseen = array();
			
			// Limit date- is the upload date 01.03.2019 ( UPLOAD was done on 14.03.2019 ) 
			foreach($newsarray_all as $kr=>$vr)
			{
				if(strtotime($vr['news_date']) >= strtotime(date('2019-03-01 00:00:00')) && !in_array($vr['id'], $user_seen_news)) 
				{
					
					$news_unseen[] = $vr;
				}
			}
			//print_r(count($news_unseen)); exit;
			if(count($news_unseen) > 0)
			{
				$this->view->news_unseen = $news_unseen;
				$this->view->user_news_popup_settings = 0;
			}
			else 
			{
				$this->view->user_news_popup_settings = 1;
			}
			
			$this->view->user_popup_settings = $user_popup_settings;
			/* ------------ BOX - "Latest News & Events" END---------------- */

			/* ------------ BOX - "Allgemeiner Support &amp; Kontakt" START---------------- */
			if(in_array('5', $boxary))
			{
				$cont = Doctrine_Query::create()
					->select("*,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
					->from('OverviewContact')
					->where('id=1');
				$contarray = $cont->fetchArray();

				if(count($contarray) > 0)
				{
					$openbox .= "overviewcolumn_content_item5,";
				}
				$this->view->contact = stripslashes($contarray[0]['content']);

				$this->view->contentitem5 = $this->view->render('overview/contactaddress.html');
				$this->view->contenttitleitem5 = "Allgemeiner Support &amp; Kontakt";
			}
			/* ------------ BOX - "Allgemeiner Support &amp; Kontakt" END---------------- */

			/* ------------ BOX - "Verordnungen" START---------------- */
			if(in_array('6', $boxary))
			{
			    $vermod = new SapvVerordnung();
			    $veripid = $vermod->get_active_ipid_client($logininfo->clientid); // no delete, no discharge, no standby, no deleted standby
			    $veripid_arr = explode(',', str_replace("'", "", $veripid));
			    unset($veripid_arr[0]);
			    $veripid_arr = array_values($veripid_arr);
			
			
			    if(!empty($veripid_arr))
			    {
			        $pt = Doctrine_Query::create()
			        ->select($sql)
			        ->from('PatientMaster pm')
			        ->where('pm.isdelete=0')
			        ->andWhere('pm.isdischarged=0')
			        ->andWhere('pm.isstandby=0')
			        ->andWhereIn('pm.ipid', $veripid_arr);
			        $pt_array = $pt->fetchArray();
			        	
			        foreach($pt_array as $patient)
			        {
			            $patients_act[$patient['ipid']] = $patient;
			        }
			        $this->view->patients_act = $patients_act;
			        	
			        $vermodarray = $vermod->getLastSapvVerordnungsortDate($veripid);
			        if($user_notifications_settings[$userid]['sapv_enabled'] == '1') //chekc if sapv popup is enabled!!!
			        {
			            $verordnung_popup = $vermod->getlastsapvpopups($veripid, $user_notifications_settings[$userid]['sapv_popup']);
			
			            if($verordnung_popup)
			            {
			                $this->view->sapv_verordnung_popup = $verordnung_popup;
			            }
			        }
			        	
			        //ISPC - 2125 - alerts if a verordnung is after XX days still in mode "Keine Angabe"
			        if($user_notifications_settings[$userid]['sapv_noinf_enabled'] == '1') //chekc if sapv no information popup is enabled!!!
			        {
			            $verordnung_noinf_popup = $vermod->getsapvnoinfpopups($veripid_arr, $user_notifications_settings[$userid]['sapv_noinf_popup']);
			
			            if($verordnung_noinf_popup)
			            {
			                $this->view->sapv_verordnung_noinf_popup = $verordnung_noinf_popup;
			            }
			        }
			
			        if($vermodarray)
			        {
			            foreach($vermodarray as $k_verordnung_data => $v_verordnung_data)
			            {
			                $patients_verordnung_ipids[] = $v_verordnung_data['ipid'];
			            }
			            //get patients healthinsurance
			            $healthinsu_array = array();
			
			            $phelathinsurance = new PatientHealthInsurance();
			            $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($patients_verordnung_ipids);
			
			            $healthinsu_verordnung = array();
			            foreach($healthinsu_array as $k_health_insu_v => $v_health_insu_v)
			            {
			                $healthinsu_verordnung[$v_health_insu_v['ipid']] = $v_health_insu_v;
			            }
			
			            $this->view->healthinsurance_verordnung_pat = $healthinsu_verordnung;
			
			            $openbox .= "overviewcolumn_content_item6,";
			            $grid = new Pms_Grid($vermodarray, 1, count($vermodarray), "verordnungengrid.html");
			            $this->view->contentitem6 = $grid->renderGrid();
			        }
			    }
			    $this->view->contenttitleitem6 = "Verordnungen";
			}
			
			
			/* ------------ BOX - "Verordnungen" END---------------- */

			// Maria:: Migration ISPC to CISPC 08.08.2020
			/*ISCP-2401 pct. 8 Lore 18.09.2019  */
			/* ------------ BOX - "VW Status change"---------------- */
			if(in_array('22', $boxary))
			{
			    
			    $connected_client = VwGroupAssociatedClients::connected_parent($this->clientid);
			    if($connected_client){
			        $vw_clientid = $connected_client;
			    } else{
			        $vw_clientid = $this->clientid;
			    }
			    
			    $vw_colorstatus = VwColorStatuses::get_color_statuses_change($vw_clientid);
			    
			    $comma = ",";
			    $vw_colorstatus_ipid = "";
			    $last_colorstatus_ids = "";
			    foreach($vw_colorstatus as $key => $val)
			    {
			        $vw_ids[]=$val['vw_id'];
			    }
			   	    
			    if(!empty($vw_ids))
			    {
			        $pt = Doctrine_Query::create()
			        ->select('first_name, last_name')
			        ->from('Voluntaryworkers ')
			        ->WhereIn('id', $vw_ids);
			        $pt_array = $pt->fetchArray();
			        
			        $vw_color_array = array();
			        foreach($pt_array as $vw)
			        {
			            $vw_color_array[$vw['id']] = $vw;
			        }
			        
			        // get the old color status for $vw_ids  
			        $drop = Doctrine_Query::create()
			        ->select('*')
			        ->from('VwColorStatuses')
			        ->where('isdelete = 0')
			        ->andWhereIn('vw_id', $vw_ids)
			        ->andWhere('clientid = ?', $vw_clientid )
			        ->orderBy('start_date DESC');
			        $old_vwcs = $drop->fetchArray();
			        
			        $vw_id2color_statuses = array();
			        foreach ($old_vwcs as $key => $vws_vals){
			            $vw_id2color_statuses[$vws_vals['vw_id']][] = $vws_vals;
			        }
			       

			        foreach ($vw_ids as $vw_id){
			            // sortem arrayul de statu
			            usort($vw_id2color_statuses[$vw_id], array(new Pms_Sorter('start_date'), "_date_compare"));
			            $lastk = end(array_keys($vw_id2color_statuses[$vw_id]));
			            $penultimul[$vw_id] = $vw_id2color_statuses[$vw_id][$lastk -1];
			        }
			        
			        
			        $color_statuses = array();
			        $all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($vw_clientid);
			        $saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($vw_clientid);
			        
			        if(!empty($saved_colors)){
			            foreach($saved_colors as $csk=>$csvalue){
			                $all_colors[$csvalue['color']] = $csvalue['colorname'];
			            }
			        }
			        
			        $datavw_cs = array();
			        foreach($vw_colorstatus as $vc=>$vwc){
			            $datavw_cs[$vc]['vw_id']      = $vwc['vw_id'];
			            $datavw_cs[$vc]['status_new'] = $vwc['status'];
			            $datavw_cs[$vc]['color_new']  = $all_colors[$vwc['status']];
			            $datavw_cs[$vc]['status_old'] = !empty($penultimul[$vwc['vw_id']]) ? $penultimul[$vwc['vw_id']]['status'] : '-';
			            $datavw_cs[$vc]['color_old']  = !empty($penultimul[$vwc['vw_id']]) ? $all_colors[$penultimul[$vwc['vw_id']]['status']] : '-';
			            $datavw_cs[$vc]['first_name'] = $vw_color_array[$vwc['vw_id']]['first_name'];
			            $datavw_cs[$vc]['last_name']  = $vw_color_array[$vwc['vw_id']]['last_name'];
			            $datavw_cs[$vc]['nume']       = $vw_color_array[$vwc['vw_id']]['last_name'].' '. $vw_color_array[$vwc['vw_id']]['first_name']; 
			            //ISPC-2316 @Lore 09.10.2019            
			            //$datavw_cs[$vc]['date']       = ($vwc['change_date'] =='0000-00-00 00:00:00') ? date('d.m.Y', strtotime($vwc['create_date'])) : date('d.m.Y', strtotime($vwc['change_date']));
			            $datavw_cs[$vc]['date']       = ($vwc['start_date'] =='0000-00-00 00:00:00') ? date('d.m.Y', strtotime($vwc['start_date'])) : date('d.m.Y', strtotime($vwc['start_date']));
			            
			        }
			          
			    }
			    $datavw_cs = $this->array_sort($datavw_cs, "date", SORT_DESC);
			    //dd($datavw_cs);
			    	    
			    if(count($datavw_cs) > 0)
			    {
			        $openbox .= "overviewcolumn_content_item22,";
			    }
			    $this->view->datavw_cs = $datavw_cs;
			    
			    $this->view->contentitem22 = $this->view->render('overview/vwstatuscolor.html');
			    
			    $this->view->contenttitleitem22 = "Ehrenamtliche Statusänderung";
			}
			/* ------------ BOX - "VW Status change" END---------------- */
			
			/* ------------ BOX - "VOLLVERSORGUNG  wl" START---------------- */
			if(in_array('12', $boxary))
			{
				$vollver = Doctrine_Query::create()
					->select($sql)
					->from('PatientMaster pm')
					->where('pm.isdelete=0 and pm.isstandby=0 and pm.isdischarged=0 and pm.vollversorgung=1')
					->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')')
					->orderBy('id DESC');
				$vollverarray = $vollver->fetchArray();

				if(count($vollverarray) > 0)
				{

					$openbox .= "overviewcolumn_content_item12,";

					$grid = new Pms_Grid($vollverarray, 1, count($vollverarray), "vollversorgunggrid.html");
					$this->view->contentitem12 = $grid->renderGrid();
				}
				$this->view->contenttitleitem12 = "Vollversorgung";
			}
			
			
			/* ------------ BOX - "VOLLVERSORGUNG  wl" END---------------- */
			//add STTI method there <= DONE
			$pd = new PatientDischarge();
			$pd->checkSttiDischarge($clientid); 

			//anlage 6 weks mail
			$mess_wl = new Messages();
			
			
			if($_REQUEST['generate_from_overview'] == "1"){
    			$mess_wl->anlage6weeks();
    			$mess_wl->anlage4weeks();
    			$mess_wl->anlage25days();
    			$mess_wl->send_coordinator_todos();
			}
			/* VERANSTALTUNGEN ->upcoming events from calendar -> End */
			/* ------------ BOX - "TERMINE " END---------------- */
			

			/* ------------ ORGANISATION CHART sTART---------------- */
            if($_REQUEST['generate_from_overview'] == "1"){
                    
    			$modules = new Modules();
    			$modules_array = array('72');
    			$organisation_clients = $modules->clients2modules($modules_array);
    
    
    			if(!empty($organisation_clients))
    			{
    				$file_location = APPLICATION_PATH . '/../public/run/';
    				$lock_filename = 'org_path.lockfile';
    				$lock_file = false;
    
    				//check lock file
    				if(file_exists($file_location . $lock_filename))
    				{
    					//lockfile exists
    					$lock_file = true;
    				}
    				else
    				{
    					//no lock file exists, create it
    					$handle = fclose(fopen($file_location . $lock_filename, 'x'));
    					$lock_file = false;
    				}
    				
    				//skip organisation path todos only if lockfile exists
    				if($lock_file === false)
    				{
    					$client_id_arr[] = '9999999999';
    					$organisation_clients[] = '99999999999';
    					foreach($organisation_clients as $client_id)
    					{
    						$client_id_str .= '"' . $client_id . '",';
    						$client_id_arr[] = $client_id;
    					}
    
    					$users_groups = new Usergroup();
    					$ClientGroups = $users_groups->get_clients_groups($organisation_clients);
    
    					foreach($ClientGroups as $kh => $gr_details)
    					{
    						$grup_details[$gr_details['clientid']][$gr_details['groupmaster']][] = $gr_details['id'];
    					}
    
    					$hidemagic = Zend_Registry::get('hidemagic');
    					$this->view->hidemagic = $hidemagic;
    
    
    					$sqlh = "ipid,isdischarged,e.clientid,e.epid,";
    					$sqlh .= "AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
    					$sqlh .= "AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
    
    					$org_patients_query = Doctrine_Query::create()
    						->select($sqlh)
    						->from('PatientMaster p')
    						->where('isdelete=0')
    						->andWhere('isstandby=0')
    						->andWhere('isstandbydelete="0"');
    					$org_patients_query->leftJoin("p.EpidIpidMapping e");
    					$org_patients_query->andWhere('e.clientid  IN (' . substr($client_id_str, 0, -1) . ') ');
    					$org_patients_array = $org_patients_query->fetchArray();
    
    
    					$all_ipids[] = '99999999999';
    					foreach($org_patients_array as $k_pat_limit => $v_pat_limit)
    					{
    						$pat_name[$v_pat_limit['ipid']] = $v_pat_limit['last_name'] . ', ' . $v_pat_limit['first_name'];
    						$pat_epid[$v_pat_limit['ipid']] = $v_pat_limit['EpidIpidMapping']['epid'];
    
    						$patient_details[$v_pat_limit['ipid']]['name'] = $v_pat_limit['last_name'] . ', ' . $v_pat_limit['first_name'];
    						$patient_details[$v_pat_limit['ipid']]['epid'] = $v_pat_limit['EpidIpidMapping']['epid'];
    						$patient_details[$v_pat_limit['ipid']]['clientid'] = $v_pat_limit['EpidIpidMapping']['clientid'];
    
    						$ipids2client[$v_pat_limit['EpidIpidMapping']['clientid']][] = $v_pat_limit['ipid'];
    						$all_ipids[] = $v_pat_limit['ipid'];
    					}
    
    					//get discharge dead patients
    					$distod = Doctrine_Query::create()
    						->select("*")
    						->from('DischargeMethod')
    						->where("isdelete = 0")
    						->andWhere("abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN' or abbr='TODENT' or abbr='Todent' or abbr='todent'")
    						->andWhereIn('clientid', $organisation_clients);
    					$todarray = $distod->fetchArray();
    
    					$todIds[] = "9999999999999";
    					foreach($todarray as $todmethod)
    					{
    						$todIds[] = $todmethod['id'];
    					}
    
    					$dispat = Doctrine_Query::create()
    						->select("*")
    						->from("PatientDischarge")
    						->whereIn('ipid', $all_ipids)
    						->andWhere('isdelete = 0')
    						->andWhereIn("discharge_method", $todIds);
    					$discharged_arr = $dispat->fetchArray();
    
    					$discharged_patients_dead[] = '99999999999';
    					foreach($discharged_arr as $k_dis_pat => $v_dis_pat)
    					{
    						$discharged_patients_dead[] = $v_dis_pat['ipid'];
    					}
    
    					if(count($pat_ipids) == '0')
    					{
    						$pat_ipids[] = '99999999999';
    					}
    
    					$paths = new OrgPaths();
    					$clients_paths = $paths->get_clients_paths($organisation_clients);
    
    					$steps = new OrgSteps();
    					$get_paths_steps = $steps->get_clients_paths_steps($client_id_str);
    				
    					$org_step_permissions = new OrgStepsPermissions();
    					$steps2group_permissions = $org_step_permissions->clients_steps2groups($organisation_clients);
    					foreach($steps2group_permissions as $client_k => $steps_per)
    					{
    						foreach($steps_per as $kstep => $vgroup_array)
    						{
    							foreach($vgroup_array as $v_group_id)
    							{
    								if(empty($step2clientgroup_permission[$client_k][$kstep]))
    								{
    									$step2clientgroup_permission[$client_k][$kstep] = array();
    						}
    								if(!empty($grup_details[$client_k][$v_group_id]))
    								{
    									$step2clientgroup_permission[$client_k][$kstep] = array_merge($step2clientgroup_permission[$client_k][$kstep], $grup_details[$client_k][$v_group_id]);
    					}
    							}
    						}
    					}
    					
    					foreach($get_paths_steps as $client_steps => $vstep_array)
    					{
    						foreach($vstep_array as $ks => $vstep)
    						{
    							$steps_ids[$client_steps][] = $vstep['id'];
    							$shortcut2stepid[$client_steps][$vstep['shortcut']] = $vstep['id'];
    							$todo2stepid[$client_steps][$vstep['shortcut']]['todo_text'] = $vstep['todo_text'];
    							$stepid2shortcut[$client_steps][$vstep['id']] = $vstep['shortcut'];
    						}
    					}
    
    					foreach($clients_paths as $k_cl_path => $v_cl_path)
    					{
    						$all_clients_path_arr[$v_cl_path['client']][] = $v_cl_path['function'];
    					}
    
    					if($_REQUEST['mode'] != 'old')
    					{
    						//new and fast way
    						$data = $paths->get_org_data_overview($all_ipids, $all_clients_path_arr);
    					}
    					else
    					{
    						//old and slow way
    						foreach($clients_paths as $k_c_path => $v_c_path)
    						{
    							if(!in_array($v_c_path['function'], $executed_functions[$v_c_path['client']]))
    							{
    
    								if(empty($data))
    								{
    									$data = array();
    								}
    
    								$executed_functions[$v_c_path['client']][] = $v_c_path['function'];
    								$retrived_data = $paths->{$v_c_path['function']}($ipids2client[$v_c_path['client']], $v_c_path['client']);
    
    								if($retrived_data)
    								{
    									$data = array_merge_recursive($data, $retrived_data);
    								}
    							}
    						}
    					}
    // print_R($data); exit;
    					foreach($data as $k_ipid => $v_function_data)
    					{
    						if($k_ipid != '99999999999')
    						{
    							foreach($v_function_data as $k_function => $v_function_arr)
    							{
    								foreach($v_function_arr as $k_short => $v_short_status)
    								{
    									if($v_short_status['status'] == "red")
    									{
    										$red_step2ipid4groups[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $step2clientgroup_permission [$patient_details [$k_ipid] ['clientid']] [$shortcut2stepid [$patient_details [$k_ipid] ['clientid']] [$k_short]];
    										if($v_short_status['extra_info'] && !empty($v_short_status['extra_info']))
    										{
    											$extra_info[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $v_short_status['extra_info'];
    										}
    										$step_identification[$patient_details [$k_ipid] ['clientid']] [$k_ipid] [$k_short] = $v_short_status['step_identification'];
    									}
    								}
    							}
    						}
    					}
    					//2567bd95670c061ac1595af482334cd82da92a2c
    // print_r($red_step2ipid4groups); exit;
    					$text = "";
    					foreach($red_step2ipid4groups as $clientk => $ipid_values)
    					{
    						foreach($ipid_values as $ipid_key => $sh_values)
    						{
    							foreach($sh_values as $sh_key => $sh2group)
    							{
    								if(!empty($sh2group))
    								{
    									foreach($sh2group as $gk => $group_id)
    									{
    										//exclude dead patients which are not having E1 shortcut
    										$exclude_patient = false;
    										if(in_array($ipid_key, $discharged_patients_dead) && $sh_key != 'E1')
    										{
    											$exclude_patient = true;
    										}
    
    										if(!$exclude_patient)
    										{
    											if($extra_info [$clientk] [$ipid_key] [$sh_key] && !empty($extra_info [$clientk] [$ipid_key] [$sh_key]))
    											{
    												$text = $patient_details[$ipid_key]['name'] . ' -  ' . $todo2stepid[$clientk][$sh_key]['todo_text'] . ' (' . $extra_info [$clientk] [$ipid_key] [$sh_key] . ')';
    											}
    											else
    											{
    												$text = $patient_details[$ipid_key]['name'] . ' -  ' . $todo2stepid[$clientk][$sh_key]['todo_text'];
    											}
    
    
    											$uk = $ipid_key . 'system_step_' . $shortcut2stepid[$clientk][$sh_key] . '_' . $sh_key . '' . $group_id;
    
    											$records_todo[$uk] = array(
    												"client_id" => $clientk,
    												"user_id" => '0',
    												"group_id" => $group_id,
    												"ipid" => $ipid_key,
    												"todo" => $text,
    												"triggered_by" => 'system_step_' . $shortcut2stepid[$clientk][$sh_key] . '_' . $sh_key,
    												"patient_step_identification" => $step_identification [$clientk] [$ipid_key] [$sh_key],
    												"create_date" => date('Y-m-d H:i:s', time()),
    												"until_date" => date('Y-m-d H:i:s', time())
    											);
    
    											$step_validation[$uk] = $step_identification [$clientk] [$ipid_key] [$sh_key];
    										}
    									}
    								}
    							}
    						}
    					}
    				}
    				
    				if($_REQUEST['path'] == '2')
    				{
    					print_r($records_todo);
    				}
    // 				print_r($records_todo); exit;
    
    				if(!empty($records_todo))
    				{
    					$record_keys = array_values(array_unique(array_keys($records_todo)));
    
    					$sapv = Doctrine_Query::create()
    						->select("CONCAT(ipid, triggered_by, group_id) as key_value, patient_step_identification")
    						->from('ToDos')
    						->where('isdelete = 0')
    						->andWhere('iscompleted = 0')
    						->andWhere('triggered_by != ""')
    						->andWhere('group_id != "0"')
    						->andWhereIn('client_id', $organisation_clients)
    						->andWhereIn('CONCAT(ipid, triggered_by, group_id)', $record_keys);
    					$sapv_res = $sapv->fetchArray();
    // 					print_R($sapv_res); exit;
    
    					foreach($sapv_res as $k => $v)
    					{
    						// if  patient_step_identification != 0 we compare  the itentification from new todos with the identification from existing todos  
    						if( $v['patient_step_identification'] != "0" ){
    							if($v['patient_step_identification'] == $step_validation[$v['key_value']] ){
    								unset($records_todo[$v['key_value']]);
    							}	
    						} else { // if patient_step_identification == 0 - this means that we already have a todo for this step and we don't insert a new one
    							unset($records_todo[$v['key_value']]);
    						}
    					}
    
    					if(count($records_todo) > 0)
    					{
    						$collection = new Doctrine_Collection('ToDos');
    						$collection->fromArray($records_todo);
    						$collection->save();
    					}
    				}
    				unlink($file_location . $lock_filename);
    			}
             }
			/* ------------ ORGANISATION CHART END---------------- */

			/* ------------ BOX - "KRISE "  START---------------- */
			if(in_array('9', $boxary))
			{
				$krismod = new PatientCourse();
				$krisipid = $krismod->getipidfromclientid($logininfo->clientid);

				$krismod2 = new PatientMaster();
				$krismodarray = $krismod2->getkrisepatients($krisipid);
				
				foreach($krismodarray as $k =>$pat_val){
					$krise_ipids[] = $pat_val['ipid'];
				}
				
				if(empty($krise_ipids)){
					$krise_ipids[] ="XXXXXX";
				}


				$locmaster = Doctrine_Query::create()
				->select('id, aes_decrypt(location,"encrypt") as name, client_id, location_type')
				->from('Locations')
				->where('client_id =' . $logininfo->clientid . '')
				->andWhere("isdelete='0' ");
				$lmarray = $locmaster->fetchArray();
				
				foreach($lmarray as $kl=>$lv){
					$location_type[$lv['location_type']][] = $lv['id'];
				}
				
				
				$patloc = Doctrine_Query::create()
				->select('ipid, location_id, clientid')
				->from('PatientLocation')
				->whereIn('ipid',$krise_ipids)
				->andWhere("valid_till='0000-00-00 00:00:00' ")
				->andWhere("isdelete='0' ")
				->groupBy('location_id');
				$plarray = $patloc->fetchArray();

				foreach($plarray as $kp=>$pl){
					if(in_array($pl['location_id'],$location_type["1"]) // hospital
						|| in_array($pl['location_id'],$location_type["7"])	// paliative station
					    || in_array($pl['location_id'],$location_type["11"])	// ISPC-1948 Ancuta 
							)
					{
					$in_hospital[]= $pl['ipid'];
						
					}
				}
				if(empty($in_hospital)){
					$in_hospital[]="XXXXXX";
				}

				foreach($krismodarray as $kkr=>$krval){
					if(in_array($krval['ipid'],$in_hospital)){
						unset($krismodarray[$kkr]);
					}	
				}


				if(count($krismodarray) > 0)
				{

					$openbox .= "overviewcolumn_content_item9,";

					$grid = new Pms_Grid($krismodarray, 1, count($krismodarray), "krisegrid.html");
					$this->view->contentitem9 = $grid->renderGrid();
				}
				$this->view->contenttitleitem9 = '<span style="color: #a00">Krise</span>';
			}
			/* ------------ BOX - "KRISE "  END---------------- */

			/* ------------ BOX - "TO DO "  START---------------- */
			if(in_array("11", $boxary))
			{
				$todo = Doctrine_Query::create()
					->select("*")
					->from('ToDos')
					->where('client_id="' . $logininfo->clientid . '" and isdelete="0" and iscompleted="0"')
					->orderBy('create_date DESC')
					->limit(300);
				$todoarray = $todo->fetchArray();
				$this->view->clientidoverview = $logininfo->clientid;

				$todo_ipids[] = '99999999999';
				$users_ids[] = '99999999999';
				$users_group_ids[] = '99999999999';

				//get todos patient ipids and users group ids
				foreach($todoarray as $k_todo => $v_todo)
				{
					$todo_ipids[] = $v_todo['ipid'];
					$users_ids[] = $v_todo['user_id'];
					$users_group_ids[] = $v_todo['group_id'];
				}

				$todo_ipids = array_values(array_unique($todo_ipids));
				$users_ids = array_values(array_unique($users_ids));
				$users_group_ids = array_values(array_unique($users_group_ids));

				$sql_pat_todos = "ipid,isdischarged,e.clientid,e.epid,";
				$sql_pat_todos .= "AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
				$sql_pat_todos .= "AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";

				//get todos patients
				$patients_todos = Doctrine_Query::create()
					->select($sql_pat_todos)
					->from('PatientMaster p')
					->where('isdelete="0"')
					->leftJoin("p.EpidIpidMapping e")
					->andWhereIn('ipid', $todo_ipids);
				$todo_patients = $patients_todos->fetchArray();

				foreach($todo_patients as $k_todo_pat => $v_todo_pat)
				{
					$todo_patients_arr[$v_todo_pat['ipid']] = $v_todo_pat;
				}

				//get todos groups details
				$grp = new Usergroup();
				$groups_data = $grp->getUserGroupMultiple($users_group_ids);

				//get todos users details
				$users_data = Pms_CommonData::getUsersData($users_ids);
				$this->view->todo_patient_details = $todo_patients_arr;
				$this->view->groups_details = $groups_data;
				$this->view->users_details = $users_data;

				if(count($todoarray) > 0)
				{
					$openbox .= "overviewcolumn_content_item11,";
					$grid = new Pms_Grid($todoarray, 1, count($todoarray), "todogrid.html");
					$grid->usertype = $logininfo->usertype;

					$grid->userid = $logininfo->userid;
					$grid->groupid = $logininfo->groupid;
					$this->view->contentitem11 = $grid->renderGrid();
				}
				$this->view->contenttitleitem11 = 'TO DO';
			}
			/* ------------ BOX - "TO DO " END---------------- */

			/* ------------ BOX - "Standorte "(location)  START---------------- */
			if(in_array("10", $boxary))
			{
				if($logininfo->clientid > 0)
				{
					$clientid = $logininfo->clientid;
				}
				else
				{
					$clientid = 0;
				}

				$client_location = array();
				$patient = Doctrine_Query::create()
					->select('ipid')
					->from('PatientMaster p')
					->where('isdelete = 0 and isdischarged = 0 and isstandby = 0 and isstandbydelete = 0');
				$patient->leftJoin("p.EpidIpidMapping e");
				$patient->andWhere('p.ipid = e.ipid and e.clientid = ' . $clientid);
				$patient->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')');
				$patarray = $patient->fetchArray();
				$all_patients = count($patarray);

				if(sizeof($patarray) > 0)
				{
					foreach($patarray as $ipid)
					{
						$ipid_str .= '"' . $ipid['ipid'] . '",';
					}

					$patloc = Doctrine_Query::create()
						->select('count(*) as number, location_id, clientid')
						->from('PatientLocation')
						->where('ipid IN (' . substr($ipid_str, 0, -1) . ')')
						->andWhere("valid_till='0000-00-00 00:00:00' ")
						->andWhere("isdelete='0' ")
						->groupBy('location_id');
					$plarray = $patloc->fetchArray();

					$locmaster = Doctrine_Query::create()
						->select('id, aes_decrypt(location,"encrypt") as name, client_id')
						->from('Locations')
						->where('client_id =' . $clientid . '')
						->andWhere("isdelete='0' ");
					$lmarray = $locmaster->fetchArray();

					foreach($plarray as $nr_value)
					{

						foreach($lmarray as $name_value)
						{
							if($nr_value['location_id'] == $name_value['id'])
							{
								$client_location[$nr_value['location_id']]['name'] = $name_value['name'];
								$client_location[$nr_value['location_id']]['number'] = $nr_value['number'];
							}
						}
					
						//this is for patients that have the current location in location type "CONTACTPERSON".
						if(substr($nr_value['location_id'], 0, 4) == '8888')
						{
							$contact_person_location += $nr_value['number'];
							$client_location['8888']['name'] = 'bei Kontaktperson';
							$client_location['8888']['number'] = $contact_person_location;
						}
						$patientsWithlocation += $nr_value['number'];
					}
				}

				$patientsNolocation = $all_patients - $patientsWithlocation;

				$client_location['9999']['number'] = $patientsNolocation;
				$client_location['9999']['name'] = 'keine Angabe';

				if(count($client_location) > 0)
				{
					$openbox .= "overviewcolumn_content_item10,";

					$grid = new Pms_Grid($client_location, 1, count($client_location), "overviewcllocationgrid.html");
					$this->view->contentitem10 = $grid->renderGrid();
				}
				$this->view->contenttitleitem10 = 'Standorte';
			}
			/* ------------ BOX - "Standorte "(location)  END---------------- */

			/* ------------ BOX - "Anlage 4 WL Vollversorgung" START---------------- */
			if(in_array("14", $boxary))
			{
				$cpatients = Doctrine_Query::create()
					->select($sql)
					->from('PatientMaster pm')
					->andWhere('isdelete = 0 and isstandby = 0 and isstandbydelete =0 and isdischarged = 0 and vollversorgung="1"')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $logininfo->clientid)
					->andWhere('ep.ipid=pm.ipid');
				$vv_patients = $cpatients->fetchArray();

				if(count($vv_patients) > 0)
				{
					$openbox .= "overviewcolumn_content_item14,";
				}

				if($vv_patients)
				{
					$pm = new PatientMaster();
					foreach($vv_patients as $k_vv_pat => $v_vv_pat)
					{

						$start = date('Y-m-d', strtotime($v_vv_pat['vollversorgung_date']));
						$end = date('Y-m-d');
						$vv_patients[$k_vv_pat]['a4wlvvperiod'] = $pm->getDaysDiff($start, $end);
					}
				}

				$grid = new Pms_Grid($vv_patients, 1, count($vv_patients), "anlagevvbox.html");
				$this->view->contentitem14 = $grid->renderGrid();
				$this->view->contenttitleitem14 = "Anlage 4 WL Vollversorgung";
			}
			/* ------------ BOX - "Anlage 4 WL Vollversorgung" END---------------- */

			/* ------------ BOX -  "Entlassungen der letzten XX Tage" START---------------- */
			if(in_array("15", $boxary))
			{
				// discharge patients
				/* $discharged_patients = Doctrine_Query::create()
					->select('ipid')
					->from('PatientMaster pm')
					->where('isdelete=0 and isdischarged=1')
					->leftJoin('pm.EpidIpidMapping ep')
					->andWhere('ep.clientid=' . $logininfo->clientid)
					->andWhere('ep.ipid=pm.ipid');
				$discharge_patients_array = $discharged_patients->fetchArray();
 */
				
				$patient_d = Doctrine_Query::create()
				->select('ipid')
				->from('PatientMaster pm')
				->where('pm.isdelete = 0')
				->andWhere('pm.isdelete = 0 and pm.isstandbydelete = 0 and pm.isstandby = 0 and pm.isdischarged = 1')
				->leftJoin('pm.EpidIpidMapping ep')
				->andWhere('ep.clientid=' . $logininfo->clientid)
				->andWhere('ep.ipid=pm.ipid')
				->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')');
				$discharge_patients_array = $patient_d->fetchArray();
				
				foreach($discharge_patients_array as $dkey => $dis_ipid)
				{
					$discharge_pat_array[] = $dis_ipid['ipid'];
				}
				$discharge_pat_array[] = '99999999999';

				$period_days = 10;

				$period_days = $clientarr[0]['discharge_day_period'];

				if(empty($period_days))
				{
					$period_days = 10;
				}

				$discharge = Doctrine_Query::create()
					->select("ipid,discharge_date")
					->from('PatientDischarge d')
					->whereIn('d.ipid', $discharge_pat_array)
					->andWhere('discharge_date >= "' . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $period_days, date('Y'))) . ' 00:00:00" AND  discharge_date <= "' . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'))) . ' 00:00:00" ')
					->orderBy('discharge_date DESC');
					//->limit(50);//TODO-3335 Ancuta 17.08.2020 :: the limit is the issue  // TODO-3335 Ancuta 24.08.2020 - removed limit
				$discharge_data = $discharge->fetchArray();

				foreach($discharge_data as $fdkey => $fdis_ipid)
				{
					$final_discharge_pat_array[] = $fdis_ipid['ipid'];
					$patient_details_dis[$fdis_ipid['ipid']]['discharge_date'] = date("d.m.Y", strtotime($fdis_ipid['discharge_date']));
				}

				$final_discharge_pat_array[] = '99999999999';
				$patient_dis = Doctrine_Query::create()
					->select('count(*)')
					->from('PatientMaster pm')
					->whereIN('ipid', $final_discharge_pat_array)
					->andWhere('isdelete = 0')
					->andWhere('isstandby = 0')
					->andWhere('isstandbydelete = 0');
				$patient_disarray2 = $patient_dis->fetchArray();

				//$limit1 = 50;// TODO-3335 Ancuta 24.08.2020 - removed limit
				$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
				$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
				$sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
				$sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
				$sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
				$sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
				$sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
				$sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
				$sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";

				// if super admin check if patient is visible or not
				if($logininfo->usertype == 'SA')
				{
					$sql = "*,";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					$sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
				}

				$patient_dis->select($sql);
				//$patient_dis->limit($limit1);// TODO-3335 Ancuta 24.08.2020 - removed limit
				//$patient_dis->offset($_GET['pgno'] * $limit1);// TODO-3335 Ancuta 24.08.2020 - removed limit

				$patient_discharge_limit = $patient_dis->fetchArray();

				foreach($patient_discharge_limit as $kpat => $vpat)
				{
					$patient_discharge_details[$vpat['ipid']]['id'] = $vpat['id'];
					$patient_discharge_details[$vpat['ipid']]['ipid'] = $vpat['ipid'];
					$patient_discharge_details[$vpat['ipid']]['last_name'] = $vpat['last_name'];
					$patient_discharge_details[$vpat['ipid']]['first_name'] = $vpat['first_name'];
					$patient_discharge_details[$vpat['ipid']]['discharge_date'] = $patient_details_dis[$vpat['ipid']]['discharge_date'];
				}
				$patient_discharge_details = $this->array_sort($patient_discharge_details, "discharge_date", SORT_DESC);

				if(count($patient_discharge_details) > 0)
				{
					$openbox .= "overviewcolumn_content_item15,";
					$grid = new Pms_Grid($patient_discharge_details, 1, $patient_disarray2[0]['count'], "latestdischarge.html");
					$this->view->contentitem15 = $grid->renderGrid();
				}
				$this->view->contenttitleitem15 = "Entlassungen der letzten " . $period_days . " Tage";
			}
			/* ------------ BOX -  "Entlassungen der letzten XX Tage" END---------------- */

			
			//ISPC-2282
			/* ------------ BOX - "Pinnwand Latest News & Events" START ---------------- */
            if(in_array('21', $boxary))
            {
                
                /**
                 * TODO: CHANGE THIS BOX TO LOAD VIA AJAX:
                 * use overview/wallnewsAction with _POST [ __action=fetchWallNewsList && limitLast14Days = true]  
                 * it will return result from $this->_wallnews_fetchWallNewsList(true) .. datatables..
                 */
                
                
                /**
                 * TODO-2042
                 * change to  GENERAL view rights 
                 */
                
                // if super admin check if patient is visible or not
                if ($logininfo->usertype == 'SA') {
                    $sql = "id, ipid";
                    $sql .= ", IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name";
                    $sql .= ", IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name";
                    $sql .= ", IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name";
                } else {
                    $sql = "id, ipid";
                    $sql .= ", AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
                    $sql .= ", AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
                    $sql .= ", AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
                }
                //TODO-2782 Ancuta - added isarchived = 0 condition // Maria:: Migration ISPC to CISPC 08.08.2020
                $users_patients = Doctrine_Query::create()
                ->select($sql)
                ->from('PatientMaster')
                ->where('ipid IN (' . $user_patients['patients_str'] . ')')
                ->andWhere("isdelete = 0")
                ->andWhere("isstandby = 0 ")
                ->andWhere("isstandbydelete = 0 ")
                ->andWhere("isarchived = 0 ")
                ->orderBy('id DESC')
                ->fetchArray();
                
                PatientMaster::beautifyName($users_patients);
                array_walk($users_patients, function(&$row) {$row['enc_id'] = Pms_Uuid::encrypt($row['id']);});
                
                $ipids_as_array = array();
                if(!empty($users_patients)){
                    $ipids_as_array = array_column($users_patients, 'ipid');
                }
                
                //get wallnews
                $wallnews_dql = "clientid = ? ";
                $wallnews_dql .= " AND (ipid IS NULL" . ( ! empty($ipids_as_array) ? " OR ipid IN (". implode(', ', array_fill(0, count($ipids_as_array), "?")) . ")" : "") . ")";
                $wallnews_dql .= " AND news_date >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)";
                $wallnews_dql .= " ORDER BY news_date DESC, id DESC";
                
                $wallnews_dql_params = ! empty($ipids_as_array) ? array_merge([$this->logininfo->clientid], $ipids_as_array) : [$this->logininfo->clientid];
                
                $wallnews = WallnewsTable::getInstance()->findByDql($wallnews_dql, $wallnews_dql_params, Doctrine_Core::HYDRATE_ARRAY);
                
                if ( ! empty($wallnews)) {
                    $users = array_column($wallnews, 'userid');                    
                    $users = User::getUsersDetails($users);
                    User::beautifyName($users);
                    
                    $users_patients_key_ipid =  array_column($users_patients, 'nice_name', 'ipid');
                    $patients_enc_id_key_ipid =  array_column($users_patients, 'enc_id', 'ipid');
                    array_walk($wallnews, function(&$row) use ($users, $users_patients_key_ipid, $patients_enc_id_key_ipid) { 
                        $row['user_nice_name'] = $users[$row['userid']]['nice_name'];
                        if ( ! empty($row['ipid'])) {
                            $row['patient_nice_name'] = $users_patients_key_ipid[$row['ipid']];
                            $row['patient_id'] = $patients_enc_id_key_ipid[$row['ipid']];
                            
                        }
//                         array_filter($users_patients, function ($var) use ($row) {
//                             return ($var['ipid'] == $row['userid']);
//                         });
                        
                    });
                }
                
                
                $users_patients_arr =  ['0' => $this->translate('Generic WallNews')];
                if(!empty($users_patients)){
                    $users_patients_arr =  array_merge($users_patients_arr, array_column($users_patients, 'nice_name', 'enc_id')); 
                } 
                
                $this->view->box21 = [
                    'users_patients' =>  $users_patients_arr, 
                    'wallnews' => $wallnews,
                ];
                 
                
                $openbox .=  ! empty($wallnews) ? "overviewcolumn_content_item21," : "";
                $this->view->user_id = $this->logininfo->userid;// Added this as a hotfix - until next UPLOAD
                $this->view->contentitem21 = $this->view->render('overview/overview.box.21.phtml');
                $this->view->contenttitleitem21 = $this->translate("Pinnwand Latest News & Events");
            }
			/* ------------ BOX - "Pinnwand Latest News & Events" END ---------------- */
			
			
			/* ------------ SORTING of BOXES---------------- */
			$obx = new OverviewCookie();
			$obxarr = $obx->getCookieData($logininfo->userid, "overviewposition");
			$boxposition = $obxarr[0]['cookie'];

			$this->view->allowed_boxes = $boxary;
			if(count($boxary) > 0)
			{
				$splitposition = explode("&", $boxposition);
				$excluded_box_ids = array('20'); //exclude dashboard box id from columns
				$onepositin = explode(",", str_replace("column1=", "", $splitposition[0]));
				$twopositin = explode(",", str_replace("column2=", "", $splitposition[1]));

				$comma = "";
				$cnter = 1;

				foreach($onepositin as $key => $val)
				{
					if(in_array(str_replace("item", "", $val), $boxary) && !in_array(str_replace("item", "", $val), $excluded_box_ids))
					{
						$itemone .= $comma . $val;
					}

					$comma = ",";
				}

				foreach($twopositin as $key => $val)
				{
					if(in_array(str_replace("item", "", $val), $boxary) && !in_array(str_replace("item", "", $val), $excluded_box_ids))
					{
						$itemtwo .= $comma . $val;
					}
					$comma = ",";
				}

				$tempchk = $itemone . "," . $itemtwo;
				$tempchkarr = explode(",", $tempchk);
				$comma = ",";

				foreach($boxary as $key => $val)
				{
					if(!in_array("item" . $val, $tempchkarr))
					{
						if(!in_array($val, $excluded_box_ids))
						{
							$itemone .= $comma . "item" . $val;
						}
					}
				}
				$sortorder = "column1=" . $itemone . "&column2=" . $itemtwo;
			}
			else
			{
//			uncomment if default boxes are used
			$sortorder = "column1=item1,item2,item3&column2=item4,item5,item6,item8";
//			disable default boxes glitch
//			$sortorder = "column1=&column2=";
			}

			if($boxcondition == 10)
			{
				$detailscookies = "''";
				$oc = new OverviewCookie();
				$ocarr = $oc->getCookieData($logininfo->userid, "overview");
				$detailscookies = $ocarr[0]['cookie'];
				$this->view->detailscookies = "'" . $detailscookies . ",'";
				
			}
			else
			{
				if($boxcondition == 9)
				{
					$this->view->detailscookies = "'" . $openbox . "'";
				}
				else
				{
					$this->view->detailscookies = "''";
				}
			}

			$columns = explode("&", $sortorder);
			$viewcolumns = array();
			foreach($columns as $key => $val)
			{
				$split = explode("=", $val);

				$items = explode(",", $split[1]);
				$itemarray = array();

				foreach($items as $itemkey => $itemval)
				{
					if(strlen(trim($itemval)) > 0)
					{
						$itemarray[] = array('item' => $itemval);
					}
				}
				if(count($itemarray) > 0)
				{
					$viewcolumns[$key] = $itemarray;
				}
			}

			$grid = new Pms_Grid($viewcolumns[0], 1, count($viewcolumns[0]), "overviewcolumn1.html");
			$this->view->column1 = $grid->renderGrid();

			$grid = new Pms_Grid($viewcolumns[1], 1, count($viewcolumns[1]), "overviewcolumn2.html");
			$this->view->column2 = $grid->renderGrid();

			if(count($boxary) == '0')
			{
				$this->view->no_boxes_message = $this->view->translate('no_overview_boxes');
			}

			//ISPC-2239
			$this->view->msgnotify = '';
			$this->view->msgcount = 0;
			/*
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$notify = Pms_CommonData::getNewmsg($logininfo->userid, 0);
			if($notify > 0)
			{
				$this->view->msgnotify = '<a href=\'message/inbox\' class=\'jlink\'>' . $this->view->translate('youhave') . ' ' . $notify . ' ' . (intval($notify) > 1 ? $this->view->translate('newmails') : $this->view->translate('newmail')) . '</a>';
				$this->view->msgcount = "(" . $notify . ")";
			}
			*/
		}
		
		
		

		public function rostergridAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();
			$this->view->userid = $logininfo->userid;
			$rosterarray = $this->orverviewroster($_GET['pgno'], $_GET['pgno'] - 7, $_GET['pgno'] + 7);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();

			$response['callBackParameters']['rosterlist'] = $rosterarray;

			echo json_encode($response);
			exit;
		}

		public function fetchcontactlistAction()
		{
			$this->_helper->layout->setLayout('layout_ajax');
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($logininfo->usertype != 'SA')
			{
				$where = 'clientid=' . $logininfo->clientid;
			}
			else
			{
				$where = 'clientid=' . $logininfo->clientid;
			}

			$ipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->where($where);
			$ipidarray = $ipid->fetchArray();

			$comma = ",";
			$ipidval = "'0'";
			foreach($ipidarray as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			$columnarray = array("pk" => "id", "ln" => "last_namee");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order_direction = $orderarray[$_REQUEST['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			$user = Doctrine_Query::create()
				->select("*")
				->from('User')
				->where('clientid = ' . $logininfo->clientid . ' or usertype="SA"')
				->andWhere('isactive=0 and isdelete = 0');
			$userarray = $user->fetchArray();

			$comma = ",";
			$usercomma = "'0'";
			if(count($userarray) > 0)
			{
				foreach($userarray as $key => $valu)
				{
					$clientUsersArray[$valu['id']] = $valu;
					$usercomma .= $comma . "'" . $valu['id'] . "'";
					$comma = ",";
				}
			}

			$patient = Doctrine_Query::create()
				->select('count(*)')
				->from('PatientMaster pm')
				->andWhere('isdelete = 0 and isstandby = 0 and last_update_user in(' . $usercomma . ')')
				->leftJoin('pm.EpidIpidMapping ep')
				->andWhere('ep.clientid=' . $logininfo->clientid)
				->andWhere('ep.ipid=pm.ipid')
				->andWhere('pm.ipid IN (' . $ipidval . ')')
				->orderBy('pm.last_update DESC');
			$patientarray = $patient->fetchArray();

			$clientarr = Pms_CommonData::getClientData($logininfo->clientid);
			$maxcontact = $clientarr[0]['maxcontact'];

			$limit = 10;
			if($maxcontact > 0)
			{
				$limit = $maxcontact;
			}

			$hidemagic = "XXXXXX";
			$sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_namee";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			if($logininfo->usertype == 'SA')
			{
				$sql = "*,";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_namee, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
			}

			$patient->select($sql)
				->from('PatientMaster pm')
				->where('pm.ipid IN (' . $ipidval . ')')
				->leftJoin('pm.EpidIpidMapping ep')
				->andWhere('ep.clientid=' . $logininfo->clientid)
				->andWhere('ep.ipid=pm.ipid')
				->orderBy('pm.last_update DESC')
				->limit($limit);
			$patient_limit = $patient->fetchArray();

			$patients_ipids_limt[] = '999999999';
			foreach($patient_limit as $k_limit_pat => $v_limit_pat)
			{
				$patients_ipids_limt[] = $v_limit_pat['ipid'];
			}

			$patients = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster')
				->whereIn('ipid', $patients_ipids_limt)
				->orderBy($columnarray[$_GET['clm']] . "  " . $_GET['ord'])
				->offset($_GET['pgno'] * $limit);
			$patientlimit = $patients->fetchArray();

			$grid = new Pms_Grid($patientlimit, 1, $limit, "lastcontactgrid.html");
			$contact_grid = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "contactcallBack";
			$response['callBackParameters']['contactlist'] = $contact_grid;


			echo json_encode($response);
		}

		private function orverviewroster($limit, $prev, $next)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			/* ----------- get all groups that have permission to be displayed on overview ----------------------------- */
			$docquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid = ' . $logininfo->clientid . '')
				->andWhere('startpage_duty = 1')
				->andWhere('isdelete = 0');
			$groups = $docquery->fetchArray();

			foreach($groups as $key => $val)
			{
				$group_details[$val['id']] = $val;
				$group_ids[] = $val['id'];
			}

			if(empty($group_ids))
			{
				$group_ids[] = "XXXX";
			}

			/* ----------- get all users from available groups  ----------------------------- */
			$doc = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn('groupid', $group_ids)
				->andWhere('isdelete=0')
				->andWhere('isactive=0')
				->orderBy('last_name ASC');
			$docarray = $doc->fetchArray();

			/* ----------- prepare data for display  ----------------------------- */
			$doctorarray = array("0" => "");
			$groupsarr = array();
			$titlerow = '<tr class="row"><td>&nbsp;</td>';
			foreach($groups as $key => $val)
			{
				$grouparray['id'] = $val['id'];
				$grouparray['groupname'] = $val['groupname'];

				$titlerow .='<td align="center"><b>' . ucfirst($val['groupname']) . '</b></td>';

				foreach($docarray as $dockey => $docval)
				{
					$groupdoctor[$docval['id']] = mb_substr($docval['first_name'], 0, 1, "UTF-8") . ". " . $docval['last_name'];
				}

				$grouparray['users'] = $groupdoctor;
				$groupsarr[] = $grouparray;
			}
			$titlerow .='</tr>';

			$this->view->doctorarray = $groupsarr;

			if(strlen($_POST['month']) > 0)
			{
				$valarray = $_POST;
				$this->view->curmonth = $_POST['month'];
			}
			else
			{
				$valarray = array("month" => date("Y_m", time()));
				$this->view->curmonth = date("Y_m", time());
			}

			$timestamp = strtotime(str_replace("_", "-", $valarray['month'] . "_1"));
			$dutystart = date("Y-m-d", $timestamp);
			$totaldays = date("t", $timestamp);
			$endtimestamp = strtotime(str_replace("_", "-", $valarray['month'] . "_" . $totaldays));
			$dutyend = date("Y-m-d", $endtimestamp);
			$weekstart = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $limit, date('Y')));
			$weekend = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $limit + 7, date('Y')));

			
			$shift = Doctrine_Query::create()
			       ->select('*')
			       ->from('ClientShifts')
		           ->where('client = ' . $logininfo->clientid)
			       ->andWhere('isdelete = "0"')
		           ->andWhere('isholiday=  "1" ');
			$shiftarray = $shift->fetchArray();
			
			$shift_isholiday[] = '99999999';
			foreach($shiftarray as $key_shift => $val_shift)
			{
				$shift_isholiday[]= $val_shift['id'];
			}
			   
			        
			        
			$docid = Doctrine_Query::create()
				->select('*')
				->from('Roster')
				->where('clientid = ' . $logininfo->clientid . " and duty_date between '" . $weekstart . "' and '" . $weekend . "'")
				->andWhere('isdelete = "0"')
			    ->andWhereNotIN("shift",$shift_isholiday);
			$rostarray = $docid->fetchArray();

			foreach($rostarray as $key => $val)
			{
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][] = $val['userid'];
			}

			$monthdays = $this->getMonthdays($weekstart, $weekend, $valarray);
			$grid = new Pms_Grid($monthdays, 1, count($monthdays), "newrostergrid.html");
			$grid->gridview->userid = $logininfo->userid;
			$grid->gridview->preno = $prev;
			$grid->gridview->nextno = $next;
			$grid->gridview->titlerow = $titlerow;
			$rostergrid = $grid->renderGrid();

			return $rostergrid;
		}

		private function getMonthdays($weekstart, $weekend, $post)
		{
			$split = explode("-", $weekstart);
			$timestamp = mktime(0, 0, 0, $split[1], $split[2], $split[0]);
			$noofdays = 7;
			$daysaray = array();
			for($i = 0; $i < $noofdays; $i++)
			{
				$curtimestamp = mktime(0, 0, 0, $split[1], $split[2] + $i, $split[0]);
				$daysaray[] = array(
					"day" => $this->view->translate(date("D", $curtimestamp)) . "&nbsp;" . date("d.m.Y", $curtimestamp),
					"doctor" => $post['doctor_' . str_replace("-", "_", date("Y-m-d", $curtimestamp))],
					"docdd" => 'doctor_' . str_replace("-", "_", date("Y-m-d", $curtimestamp)),
				);
			}
			return $daysaray;
		}

		public function overviewboxAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$doc = Doctrine_Query::create()
					->delete('Overview')
					->where("clientid='" . $clientid . "'");
				$doc->execute();

				$over = new Overview();
				$over->clientid = $clientid;
				$over->overviewboxid = join(",", $_POST['boxid']);
				$over->save();

				$this->_redirect(APP_BASE . 'overview/overviewbox');
				exit;
			}
			$cover = Doctrine::getTable('Overview')->findBy('clientid', $clientid);
			$carray = $cover->toArray();
			$this->view->boxjs = $carray[0]['overviewboxid'];
		}

		public function overviewboxsettingsAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				
				// TODO-1967, do not change per client
				/* if($_POST['hidrecid'])
				{
					$cover = Doctrine::getTable('Overview')->findOneById($_POST['hidrecid']);
					$cover->boxconditions = $_POST['closebox'];
					$cover->save();
				} */
				
				$oc = new Application_Form_OverviewCookie();
				$a_post['cookie'] = join(",", $_POST['boxid']);
				$a_post['useroption'] = $_POST['closebox'];
				$a_post['page_name'] = "overview";
				$oc->InsertData($a_post);

				$a_post['cookie'] = join(",", $_POST['useroptionadmission']);
				$a_post['useroption'] = $_POST['admissionbox'];
				$a_post['page_name'] = "admission";
				$oc->InsertData($a_post);

				$a_post['cookie'] = join(",", $_POST['useroptiondetails']);
				$a_post['useroption'] = $_POST['detailsbox'];
				$a_post['page_name'] = "patientdetails";
				$oc->InsertData($a_post);

				$a_post['cookie'] = join(",", $_POST['useroptionstamm']);
				$a_post['useroption'] = $_POST['stammdatenbox'];
				$a_post['page_name'] = "stammdatenerweitert";
				$oc->InsertData($a_post);
			}
			
			$cover = Doctrine::getTable('Overview')->findBy('clientid', $clientid);
			$carray = $cover->toArray();
			$this->view->boxjs = $carray[0]['overviewboxid'];
			$this->view->recid = $carray[0]['id'];
			
			// Get Overview settings per user 
			// TODO-1967 Ancuta 12.12.2018
			$oc = new OverviewCookie();
			$ocarr = $oc->getCookieData($logininfo->userid, "overview");
			$this->view->detailscookiejs = explode(",", $ocarr[0]['cookie']);

			$overview_open_settings = !empty($ocarr[0]['useroption']) ? $ocarr[0]['useroption'] : $carray[0]['boxconditions'];
			$this->view->closebox = $overview_open_settings;
			
			
			$ef = new ExtraFormsClient();
			$this->view->detailboxstr = $ef->getExtraFormsClientQuammasep($clientid);

		  	if($overview_open_settings != 10)
			{
				$this->view->styedis = 'style="display:none;"';
			}


			$ob = new OverviewCookie();
			$this->view->radiochoices = $ob->getAdmissionRadioChoice();
			$this->view->detailsradiochoices = $ob->getRadioChoice();
			$this->view->blockarr = $ob->getdivNames();

			$admisionstr = $ob->getCookieData($logininfo->userid, "admission");
			$this->view->admisionstr = $admisionstr;
			$this->view->admissionbox = $admisionstr[0]['useroption'];

			$detailsstr = $ob->getCookieData($logininfo->userid, "patientdetails");
			$this->view->detailsstr = $detailsstr;
			$this->view->detailsbox = $detailsstr[0]['useroption'];

			$stamstr = $ob->getCookieData($logininfo->userid, "stammdatenerweitert");

			$this->view->stamstr = $stamstr;
			$this->view->stammdatenbox = $stamstr[0]['useroption'];

			$ds = new ExtraForms();
			$dsarr = $ds->getExtraFormsAdmission();

			$comma = "";
			foreach($dsarr as $key => $val)
			{
				if($val['id'] == '59') continue; // ISPC-1757
				$admforms.= $comma . $val['id'];
				$comma = ",";
			}

			$ds = new ExtraForms();
			$dsarr = $ds->getExtraFormsDetails();

			$comma = "";
			foreach($dsarr as $key => $val)
			{
				$dtlforms.= $comma . $val['id'];
				$comma = ",";
			}

			$ds = new ExtraForms();
			$dsarr = $ds->getExtraFormsStammdatenerweitert();

			$comma = "";
			foreach($dsarr as $key => $val)
			{
				$dtlstamm.= $comma . $val['id'];
				$comma = ",";
			}

			$mn = new TabMenus();
			$mnarr = $mn->getMenubyLink("patient/stammdatenerweitert");


			$med = Doctrine_Query::create()
				->select('count(*)')
				->from('TabMenuClient')
				->where("menu_id= ? ", $mnarr[0]['id'])
				->andWhere("clientid= ? ", $logininfo->clientid);
			$medexec = $med->execute();
			if($medexec)
			{
				$medarray = $medexec->toArray();
			}

			$showstammdaten = 0;
			if($medarray[0]['count'] > 0)
			{
				$showstammdaten = 1;
			}

			$ef = new ExtraFormsClient();
			$efarr_adm = $ef->getExtraForms($clientid, $admforms);
			$efarr_dtl = $ef->getExtraForms($clientid, $dtlforms);

			$grid = new Pms_Grid($efarr_adm, 1, count($efarr_adm), "overviewadmissionformgrid.html");
			$this->view->admissionform = $grid->renderGrid();

			$grid = new Pms_Grid($efarr_dtl, 1, count($efarr_dtl), "overviewdetailsformgrid.html");
			$this->view->detailsform = $grid->renderGrid();
		}

		public function setoverviewcookieAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();
			$cookie = $_GET['ck'];
			$oc = new Application_Form_OverviewCookie();
			$a_post['cookie'] = $cookie;
			$a_post['page_name'] = "overview";
			$oc->InsertData($a_post);
		}

		public function setboxpositionAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();
			$oc = new Application_Form_OverviewCookie();
			if(strlen($_GET['sortorder']) > 0)
			{
				$a_post['cookie'] = $_GET['sortorder'] . "&column2=" . $_GET['column2'];
				$a_post['page_name'] = "overviewposition";
				$oc->InsertData($a_post);
			}
		}

		public function changestatusAction()
		{
			if(!empty($_REQUEST['id']))
			{
				$id = $_REQUEST['id'];
			}
			exit;
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'stratDate' || $on == 'discharge_date')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								elseif($on == 'epid')
								{
									$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'stratDate' || $on == 'discharge_date')
						{
							$sortable_array[$k] = strtotime($v);
						}
						elseif($on == 'epid')
						{
							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case SORT_ASC:
//						asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case SORT_DESC:
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
		
		public function dashboardhistoryAction ()
		{
		
			set_time_limit(0);
			$this->_helper->layout->setLayout('layout_ajax');
			
		}
		
		public function getdashboardhistoryAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;
			$user_type = $logininfo->usertype;
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$hidemagic = Zend_Registry::get('hidemagic');
		
			setlocale(LC_ALL, 'de_DE.UTF-8');
		
			$done_events = new DashboardActionsDone();
			$labels_form = new Application_Form_DashboardActions();
			$wlprevileges = new Modules();
			
				
			$this->_helper->viewRenderer->setNoRender();
				
			if(!$_REQUEST['length'])
			{
				$_REQUEST['length'] = "150";
			}
				
			$limit = $_REQUEST['length'];
			$offset = $_REQUEST['start'];
			//$search_value = $_REQUEST['search']['value'];
				
			if(!empty($_REQUEST['order'][0]['column']))
			{
				$order_column = $_REQUEST['order'][0]['column'];
			}
			else
			{
				$order_column = "4";
			}
			$order_dir = $_REQUEST['order'][0]['dir'];
								
			$columns_array = array(
					"2" => "column_title",
					"3" => "create_user",
					"4" => "due_date",
					"5" => "create_date"
			);
		
			//load excluded events
			$history_events = $done_events->getClientDashboardActions($clientid, false);
			$this->view->history_events = $history_events;
			$history_events['anlage']['ids'][] = '999999999999999';
			$history_events['asses']['ids'][] = '999999999999999';
			$history_events['reasses']['ids'][] = '999999999999999';
			$history_events['custom_team_event']['ids'][] = '999999999999999';
			$history_events['custom_doctor_event']['ids'][] = '999999999999999';
			$history_events['custom_doctor_event_team']['ids'][] = '999999999999999';
			$history_events['todo']['ids'][] = '999999999999999';
			$history_events['sgbxi']['ids'][] = '999999999999999';
			$history_events['patient_birthday']['ids'][] = '999999999999999';
		
			$user = Doctrine_Query::create()
			->select("*")
			->from('User')
			->where('clientid = ' . $clientid . ' or usertype="SA"')
			->andWhere('isactive=0 and isdelete = 0')
			->orderBy('last_name ASC');
			$userarray = $user->fetchArray();
		
			if(count($userarray) > 0)
			{
				foreach($userarray as $u_key => $u_value)
				{
					$client_users_arr[$u_value['id']] = $u_value;
				}
			}
			$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			$modules = new Modules();
		
			if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
			{
				$this->view->acknowledge_func = "1";
				if(in_array($userid,$approval_users)){
					$this->view->approval_rights = "1";
				} else{
					$this->view->approval_rights = "0";
				}
		
			}
		
			else
			{
				$this->view->acknowledge_func = "0";
			}
		
				
				
			//anlage module
			$wl_perms = $wlprevileges->checkModulePrivileges("51", $clientid);
		
			if($wl_perms && count($history_events['anlage']['ids']) > 0)
			{
				$patientwl = Doctrine_Query::create()
				->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, e.epid")
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('admission_date < DATE(NOW())')
				->andWhereIn('id', $history_events['anlage']['ids']);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
		
				//LEft join cu patient qupa mapping cu userid logininfo
				if($client_users_arr[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl->leftJoin("e.PatientQpaMapping q");
					$patientwl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all = $patientwl->fetchArray();
		
				foreach($patientidwlarray_all as $k_pat => $v_pat)
				{
					$anlage_events[$v_pat['patientId']] = $v_pat;
				}
			}
		
			//team_custom_events
			$team_custom_event = Doctrine_Query::create()
			->select("*")
			->from('TeamCustomEvents')
			->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_team_event']['ids']);
			$team_custom_event_res = $team_custom_event->fetchArray();
		
		
			foreach($team_custom_event_res as $k_team => $v_team)
			{
				$team_cust_events[$v_team['id']] = $v_team;
			}
		
			//custom doctor
			$doc_custom_event = Doctrine_Query::create()
			->select("*")
			->from('DoctorCustomEvents')
			->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_doctor_event']['ids']);
			$doc_custom_event_res = $doc_custom_event->fetchArray();
		
		
			foreach($doc_custom_event_res as $k_doc => $v_doctor)
			{
				$doctor_cust_events[$v_doctor['id']] = $v_doctor;
			}
			//custom doctor team
			$doc_custom_event_team = Doctrine_Query::create()
			->select("*")
			->from('DoctorCustomEvents')
			->where("clientid='" . $clientid . "'")
			->andWhereIn('id', $history_events['custom_doctor_event_team']['ids']);
			$doc_custom_event_team_res = $doc_custom_event_team->fetchArray();
		
			foreach($doc_custom_event_team_res as $k_doc => $v_doctor)
			{
				$doctor_cust_events_team[$v_doctor['id']] = $v_doctor;
			}
		
			//todo
			$todo = Doctrine_Query::create()
			->select("*")
			->from('ToDos')
			->where('client_id="' . $clientid . '" and isdelete="0"')
			->andWhereIn('id', $history_events['todo']['ids']);
		
			$todo_array = $todo->fetchArray();
		
			foreach($todo_array as $k_todo => $v_todo)
			{
				$todos_arr[$v_todo['id']] = $v_todo;
			}
		
			//sgb xi
			$event_tabname = "sgbxi";
			$sgbxi = Doctrine_Query::create()
			->select("*")
			->from('DashboardEvents')
			->where('client_id="' . $clientid . '" and isdelete="0"')
			->andWhere('tabname = "' . $event_tabname . '" ')
			->andWhereIn('id', $history_events['sgbxi']['ids']);
			$sgbxi_array = $sgbxi->fetchArray();
		
			foreach($sgbxi_array as $k_sgbxi => $v_sgbxi)
			{
				$sgbxi_arr[$v_sgbxi['id']] = $v_sgbxi;
			}
		
			$assessment_events = Doctrine_Query::create()
			->select("*")
			->from('KvnoAssessment ')
			->whereIn('id', $history_events['asses']['ids'])
			->andWhere('iscompleted="1"');
			$assessment_events_arr = $assessment_events->fetchArray();
		
			$assessments_ipids[] = '9999999999999';
			foreach($assessment_events_arr as $k_asses_arr => $v_asses_arr)
			{
				$assessments_ipids[] = $v_asses_arr['ipid'];
				$assessment_arr[$v_asses_arr['id']] = $v_asses_arr;
			}
		
			//assessment patients
			$assessment_pat = Doctrine_Query::create()
			->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, e.epid")
			->from('PatientMaster as p')
			->andWhereIn('ipid', $assessments_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
			$assessment_pat_res = $assessment_pat->fetchArray();
		
			foreach($assessment_pat_res as $k_assessment => $v_assessment)
			{
				$asses_pat_det[$v_assessment['ipid']] = $v_assessment;
			}
		
			//patient birthday
		
			$notifications = new Notifications();
			$user_notification_settings = $notifications->get_notification_settings($userid);
		
			if(count($history_events['patient_birthday']['ids']) > 0)
			{
				$patients_birth = Doctrine_Query::create()
				->select("p.ipid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, birthd, e.epid")
				->from('PatientMaster as p')
				->andWhereIn('id', $history_events['patient_birthday']['ids']);
				$patients_birth->leftJoin("p.EpidIpidMapping e");
				$patients_birth->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
				$patients_birth_arr = $patients_birth->fetchArray();
		
		
				foreach($patients_birth_arr as $k_pat => $v_pat)
				{
					$birth_ipids[] = $v_pat['ipid'];
					$patients_birthds[$v_pat['ipid']] = $v_pat;
					$birthds_events[$v_pat['id']] = $v_pat;
				}
				//print_r($patients_birthds);exit;
			}
			$i = 1;
			$todos_ipids[] = '999999999999999';
			$sgbxi_ipids[] = '999999999999999';
		
			$triggered_by_arr = array();
				
			foreach($history_events as $tab_name => $v_history_events)
			{
				foreach($v_history_events['details'] as $k_event => $v_event)
				{
					$create_date = date('Y-m-d', strtotime($v_event['create_date']));
					if($v_event['done_date'] != '0000-00-00 00:00:00')
					{
						$due_date = date('d.m.Y', strtotime($v_event['done_date']));
					}
					else
					{
						$due_date = '-';
					}
		
					$master_data[strtotime($create_date)][$i]['id'] = $v_event['id'];
					$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
		
		
					//anlage
					if($tab_name == 'anlage')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_event['event']) . '">' . $anlage_events[$v_event['event']]['last_name'] . ', ' . $anlage_events[$v_event['event']]['first_name'] . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//team event
					if($tab_name == 'custom_team_event')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $team_cust_events[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//doctor event
					if($tab_name == 'custom_doctor_event')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $doctor_cust_events[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					//doctor event team
					if($tab_name == 'custom_doctor_event_team')
					{
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $doctor_cust_events_team[$v_event['event']]['eventTitle'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					if($tab_name == 'todo')
					{
						$due_date = date('d.m.Y', strtotime($v_todo['until_date']));
						$todos_ipids[] = $todos_arr[$v_event['event']]['ipid'];
						if($todos_arr[$v_event['event']]['triggered_by'] != 'system_medipumps')
						{
		
							$triggered_by_arr[$i] = explode("-",$v_todo['triggered_by']);
		
							if($triggered_by_arr[$i][0] == "medacknowledge")
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'medacknowledge';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
								if(strlen($triggered_by_arr[$i][1]) > 0){
									$master_data[strtotime($create_date)][$i]['drugplan_id'] = $triggered_by_arr[$i][1];
								}
								$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
							}
							elseif($triggered_by_arr[$i][0] == "pumpmedacknowledge")
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'pumpmedacknowledge';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
								$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
								if(strlen($triggered_by_arr[$i][1]) > 0){
									$master_data[strtotime($create_date)][$i]['cocktail_id'] = $triggered_by_arr[$i][1];
								}
							}
							else
							{
								$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
								$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
								$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
								$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
								$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
							}
		
		
							$master_data[strtotime($create_date)][$i]['alt_id'] = $v_todo['record_id'];
							$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
							$master_data[strtotime($create_date)][$i]['ipid'] = $todos_arr[$v_event['event']]['ipid'];
							$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
							$master_data[strtotime($create_date)][$i]['event_title'] = $todos_arr[$v_event['event']]['todo'];
							$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
							$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
							$master_data[strtotime($create_date)][$i]['user_id'] = $todos_arr[$v_event['event']]['user_id'];
							$master_data[strtotime($create_date)][$i]['group_id'] = $todos_arr[$v_event['event']]['group_id'];
							$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
							$master_data[strtotime($create_date)][$i]['triggered_by'] = $todos_arr[$v_event['event']]['triggered_by'];
							$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
						}
						else if($todos_arr[$v_event['event']]['group_id'] == $groupid || $user_type == 'SA')
						{
							$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
							$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
							$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
							$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
		
		
							$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
							$master_data[strtotime($create_date)][$i]['ipid'] = $todos_arr[$v_event['event']]['ipid'];
							$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
							$master_data[strtotime($create_date)][$i]['event_title'] = $todos_arr[$v_event['event']]['todo'];
							$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
							$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
							$master_data[strtotime($create_date)][$i]['user_id'] = $todos_arr[$v_event['event']]['user_id'];
							$master_data[strtotime($create_date)][$i]['group_id'] = $todos_arr[$v_event['event']]['group_id'];
							$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
							$master_data[strtotime($create_date)][$i]['triggered_by'] = $todos_arr[$v_event['event']]['triggered_by'];
							$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
						}
						$todos_skip[] = $v_event['event'];
					}
		
		
					if($tab_name == 'sgbxi')
					{
						$sgbxi_ipids[] = $sgbxi_arr[$v_event['event']]['ipid'];
						$master_data[strtotime($create_date)][$i]['tabname'] = $tab_name;
						$master_data[strtotime($create_date)][$i]['ipid'] = $sgbxi_arr[$v_event['event']]['ipid'];
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_title'] = $sgbxi_arr[$v_event['event']]['title'];
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['user_id'] = $sgbxi_arr[$v_event['event']]['user_id'];
						$master_data[strtotime($create_date)][$i]['group_id'] = $sgbxi_arr[$v_event['event']]['group_id'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['triggered_by'] = $sgbxi_arr[$v_event['event']]['triggered_by'];
						$master_data[strtotime($create_date)][$i]['done_date'] = date('d.m.Y', strtotime($sgbxi_arr[$v_event['event']]['until_date']));
						$sgbxi_skip[] = $v_event['event'];
					}
		
					if($tab_name == 'asses')
					{
						$ipid = $assessment_arr[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patientform/kvnoassessment?id=' . Pms_Uuid::encrypt($asses_pat_det[$ipid]['id']) . '">' . strtoupper($asses_pat_det[$ipid]['EpidIpidMapping']['epid']) . " - " . ucfirst($asses_pat_det[$ipid]['last_name']) . ", " . ucfirst($asses_pat_det[$ipid]['first_name']) . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					if($tab_name == 'reasses')
					{
						$ipid = $assessment_arr[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = '<a href="patientform/reassessment?id=' . Pms_Uuid::encrypt($patientsFinalIds[$assess['ipid']]['id']) . '">' . strtoupper($asses_pat_det[$ipid]['epid']) . " - " . ucfirst($asses_pat_det[$ipid]['last_name']) . ", " . ucfirst($asses_pat_det[$ipid]['first_name']) . '</a>';
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
					if($tab_name == 'patient_birthday')
					{
						$ipid = $birthds_events[$v_event['event']]['ipid'];
		
						$master_data[strtotime($create_date)][$i]['event_id'] = $v_event['event'];
						$master_data[strtotime($create_date)][$i]['event_ipid'] = $ipid;
						$master_data[strtotime($create_date)][$i]['event_title'] = strtoupper($patients_birthds[$ipid]['EpidIpidMapping']['epid']) . ' - ' . ucfirst($patients_birthds[$ipid]['last_name']) . ", " . ucfirst($patients_birthds[$ipid]['first_name']);
						$master_data[strtotime($create_date)][$i]['event_source'] = $v_event['source'];
						$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_event['user']]['user_title'] . ' ' . $client_users_arr[$v_event['user']]['last_name'] . ', ' . $client_users_arr[$v_event['user']]['first_name'];
						$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
						$master_data[strtotime($create_date)][$i]['triggered_by'] = 'system';
						$master_data[strtotime($create_date)][$i]['done_date'] = $due_date;
					}
		
					$i++;
				}
				$i++;
			}
			$todos_ipids = array_values(array_unique($todos_ipids));
			$sgbxi_ipids = array_values(array_unique($sgbxi_ipids));
		
			$old_events = $todos->getCompletedTodosByClientId($clientid, $todos_skip);
		
			if($_REQUEST['dbgz'])
			{
				print_r($old_events);
			}
		
		
			foreach($old_events as $k_old_event => $v_old_event)
			{
				$create_date = date('Y-m-d', strtotime($v_old_event['create_date']));
		
				if(date('Y-m-d', strtotime($v_old_event['until_date'])) != '1970-01-01' && $v_old_event['until_date'] != '0000-00-00 00:00:00')
				{
					$done_date = date('d.m.Y', strtotime($v_old_event['until_date']));
				}
				else
				{
					$done_date = '-';
				}
		
				$todos_ipids[] = $v_old_event['ipid'];
		
				if($v_old_event['triggered_by'] != 'system_medipumps')
				{
		
		
					$triggered_by_arr[$i] = explode("-",$v_old_event['triggered_by']);
		
					if($triggered_by_arr[$i][0] == "medacknowledge")
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'medacknowledge';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
						if(strlen($triggered_by_arr[$i][1]) > 0){
							$master_data[strtotime($create_date)][$i]['drugplan_id'] = $triggered_by_arr[$i][1];
						}
						$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
					}
					elseif($triggered_by_arr[$i][0] == "pumpmedacknowledge")
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = 'pumpmedacknowledge';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '1';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '1';
						$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
						if(strlen($triggered_by_arr[$i][1]) > 0){
							$master_data[strtotime($create_date)][$i]['cocktail_id'] = $triggered_by_arr[$i][1];
						}
					}
					else
					{
						$master_data[strtotime($create_date)][$i]['triggered_by_info'] = '0';
						$master_data[strtotime($create_date)][$i]['medical_change'] = '0';
						$master_data[strtotime($create_date)][$i]['hide_checkbox'] = '0';
						$master_data[strtotime($create_date)][$i]['drugplan_id'] = '0';
						$master_data[strtotime($create_date)][$i]['cocktail_id'] = '0';
					}
		
		
		
					$master_data[strtotime($create_date)][$i]['tabname'] = 'old_todo';
					$master_data[strtotime($create_date)][$i]['event_id'] = $v_old_event['id'];
					$master_data[strtotime($create_date)][$i]['ipid'] = $v_old_event['ipid'];
					$master_data[strtotime($create_date)][$i]['event_title'] = $v_old_event['todo'];
					$master_data[strtotime($create_date)][$i]['event_source'] = 'u';
					$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_old_event['complete_user']]['user_title'] . ' ' . $client_users_arr[$v_old_event['complete_user']]['last_name'] . ', ' . $client_users_arr[$v_old_event['complete_user']]['first_name'];
					$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_old_event['create_date']));
					$master_data[strtotime($create_date)][$i]['done_date'] = $done_date;
				}
				$i++;
			}
		
		
		
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
		
		
			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}
		
			$patients = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->whereIn("p.ipid", $todos_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
			$patients_res = $patients->fetchArray();
			foreach($patients_res as $k_pat_todo => $v_pat_todo)
			{
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
				$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
			}
		
			/* ###################### SGB XI####################### */
		
			$sgbxi_old_events = $dashboard_events->get_completed_dashboard_events($clientid, $sgbxi_skip);
		
			if($_REQUEST['dbgz'])
			{
				print_r($sgbxi_old_events);
			}
		
			foreach($sgbxi_old_events as $k_sold_event => $v_sgbxi_old_event)
			{
				$create_date = date('Y-m-d', strtotime($v_sgbxi_old_event['create_date']));
		
				if(date('Y-m-d', strtotime($v_sgbxi_old_event['until_date'])) != '1970-01-01' && $v_sgbxi_old_event['until_date'] != '0000-00-00 00:00:00')
				{
					$done_date = date('d.m.Y', strtotime($v_sgbxi_old_event['until_date']));
				}
				else
				{
					$done_date = '-';
				}
		
				$sgbxi_ipids[] = $v_sgbxi_old_event['ipid'];
		
		
				$master_data[strtotime($create_date)][$i]['tabname'] = 'old_sgbxi';
				$master_data[strtotime($create_date)][$i]['event_id'] = $v_sgbxi_old_event['id'];
				$master_data[strtotime($create_date)][$i]['ipid'] = $v_sgbxi_old_event['ipid'];
				$master_data[strtotime($create_date)][$i]['event_title'] = $v_sgbxi_old_event['todo'];
				$master_data[strtotime($create_date)][$i]['event_source'] = 'u';
				$master_data[strtotime($create_date)][$i]['create_user'] = $client_users_arr[$v_sgbxi_old_event['complete_user']]['user_title'] . ' ' . $client_users_arr[$v_sgbxi_old_event['complete_user']]['last_name'] . ', ' . $client_users_arr[$v_sgbxi_old_event['complete_user']]['first_name'];
				$master_data[strtotime($create_date)][$i]['create_date'] = date('d.m.Y', strtotime($v_sgbxi_old_event['create_date']));
				$master_data[strtotime($create_date)][$i]['done_date'] = $done_date;
				$i++;
			}
		
			$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
		
			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}
		
			$patients_sgbxi_q = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->whereIn("p.ipid", $sgbxi_ipids)
			->leftJoin("p.EpidIpidMapping e")
			->andWhere('e.clientid = ' . $clientid);
			$patients_sgbxi_res = $patients_sgbxi_q->fetchArray();
			foreach($patients_sgbxi_res as $k_pat_sgbxi => $v_pat_sgbxi)
			{
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']] = $v_pat_sgbxi;
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_sgbxi['EpidIpidMapping']['epid']);
				$sgbxi_patients[$v_pat_sgbxi['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_sgbxi['id']);
			}
			
			//$this->view->master_data = $master_data;
			//$this->view->todo_patients = $todo_patients;
			//$this->view->sgbxi_patients = $sgbxi_patients;
			$dashdata = array();
			
			foreach($master_data as $day_event=> $events)
			{
				foreach($events as $k_event => $v_event)
				{						
					$dashdata[$k_event]['label'] =  'name_'.$v_event['tabname'];
		
					if($v_event['triggered_by'] == 'system_medipumps')
					{
						$dashdata[$k_event]['column_title'] = $todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].' - '. $v_event['event_title'];
					}
					elseif($v_event['tabname']=='custom_team_event')
					{
						$dashdata[$k_event]['column_title'] = $v_event['event_title'];
					}
					elseif($v_event['tabname']=='custom_doctor_event_team')
					{
						$dashdata[$k_event]['column_title'] = $v_event['event_title'];
					}
					elseif(strlen($v_event['triggered_by'])=='0' && $v_event['triggered_by_info'] == "medacknowledge")
					{
						$dashdata[$k_event]['column_title'] = $v_event['event_title'];
					}
					elseif(strlen($v_event['triggered_by'])=='0')
					{
						$dashdata[$k_event]['column_title'] =  $todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].' - '. $v_event['event_title'];
					}
					else 
					{
						$dashdata[$k_event]['column_title'] = $v_event['event_title'];
					}
					
					$v_eventarr['create_user'] = explode(',', $v_event['create_user']);
					if($v_eventarr['create_user'][0] == ' ')
					{
						$dashdata[$k_event]['create_user'] = '';
					}
					else
					{
						$dashdata[$k_event]['create_user'] = $v_event['create_user'];
					}					
					$dashdata[$k_event]['due_date'] = $v_event['done_date'];
					$dashdata[$k_event]['create_date'] = date('d.m.Y', strtotime($v_event['create_date']));
					$dashdata[$k_event]['event_source'] = $v_event['event_source'];
					$dashdata[$k_event]['hide_checkbox'] = $v_event['hide_checkbox'];
					$dashdata[$k_event]['tabname'] = $v_event['tabname'];
					$dashdata[$k_event]['event_id'] = $v_event['event_id'];
					
				}
			}
				
			foreach($dashdata as $keyd=>$vald)
			{
				if($columns_array[$order_column] == 'due_date' || $columns_array[$order_column] == 'create_date')
				{
					$colord[$keyd] = strtotime($vald[$columns_array[$order_column]]);
				}
				else
				{
					$colord[$keyd] = $vald[$columns_array[$order_column]];
				}
		
			}
			
			$dashkeys = array_keys($dashdata);
			if($order_dir == 'desc') {
			array_multisort($colord, SORT_DESC, $dashdata);
			}
			else {
			array_multisort($colord, SORT_ASC, $dashdata);
			}
			$dashdata = array_combine($dashkeys, $dashdata);
			
			$full_count = count($dashdata);
			
			$nrcrt = 1;
			$dashdatalimit_arr = array();
			
			foreach ($dashdata as $key => $report)
			{
				$dashdatalimit_arr[$key]['nr'] = $nrcrt;
				$dashdatalimit_arr[$key]['label'] = $report['label'];
				$dashdatalimit_arr[$key]['column_title'] = $report['column_title'];
				$dashdatalimit_arr[$key]['create_user'] = $report['create_user'];
				$dashdatalimit_arr[$key]['due_date'] = $report['due_date'];
				$dashdatalimit_arr[$key]['create_date'] = $report['create_date'];
				$dashdatalimit_arr[$key]['event_source'] = $report['event_source'];
				$dashdatalimit_arr[$key]['hide_checkbox'] = $report['hide_checkbox'];
				$dashdatalimit_arr[$key]['tabname'] = $report['tabname'];
				$dashdatalimit_arr[$key]['event_id'] = $report['event_id'];
				$nrcrt++;
			}
			
			if($limit != "" && $offset != "")
			{				
				$dashdatalimit = array_slice($dashdatalimit_arr, $offset, $limit, true);
				$dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
			}
			else
			{
				$dashdatalimit = $dashdatalimit_arr;
				$dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
			}
			
			$row_id = 0;
			$link = "";
			$resulted_data = array();
			
			foreach($dashdatalimit as $report_id =>$mdata)
			{
				$link = '%s ';
				$resulted_data[$row_id]['nr'] = sprintf($link,$mdata['nr']);
				$resulted_data[$row_id]['label'] = sprintf($link,$this->view->translate($mdata['label']));
				if($mdata['tabname'] == 'old_todo')
				{
					$resulted_data[$row_id]['column_title'] = $mdata['column_title']
					.'<input type="hidden" value="'.$mdata['event_id'].'" id="event_history_id_'.$mdata['nr'].'" name="event_history_id[]"/>
					<input type="hidden" value="'.$mdata['tabname'].'" id="un_tabname_'.$mdata['nr'].'" name="tabname[]"/>';
				}
				else 
				{
					$resulted_data[$row_id]['column_title'] = $mdata['column_title']
					.'<input type="hidden" value="'.$mdata['event_id'].'" id="event_history_id_'.$mdata['nr'].'" name="event_history_id[]"/>
					<input type="hidden" value="'.$mdata['tabname'].'" id="un_tabname_'.$mdata['nr'].'" name="tabname[]"/>';
				}
				$resulted_data[$row_id]['create_user'] = sprintf($link,$mdata['create_user']);
				$resulted_data[$row_id]['due_date'] = sprintf($link,$mdata['due_date']);
				$resulted_data[$row_id]['create_date'] = sprintf($link,$mdata['create_date']);
				if($mdata['event_source'] != "s")
				{
					if($mdata['hide_checkbox'] == "0")
					{
						if($mdata['tabname'] == 'old_todo')
						{
							$resulted_data[$row_id]['action'] = '<input type="checkbox" id="undone_event_'.$mdata["nr"].'" value="1" name="undone[event]" class="undone_old_event" rel="'.$mdata["nr"].'" />';
						}
						else {
							$resulted_data[$row_id]['action'] = '<input type="checkbox" id="undone_event_'.$mdata["nr"].'" value="1" name="undone[event]" class="undoneevent" rel="'.$mdata["nr"].'" />';
						}
					}
					else 
					{
						$resulted_data[$row_id]['action'] = '';
					}
				}
				$row_id++;
			}
			
			$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $full_count; // ??
			$response['data'] = $resulted_data;
			
			header("Content-type: application/json; charset=UTF-8");
			
			echo json_encode($response);
			exit;
		}
		
		public function dashboardlistAction ()
		{
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_ajax');
			
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$labels_form = new Application_Form_DashboardActions();
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$extra = array();
			$data['client'] = $clientid;
			$data['user'] = $userid;
			$data['event'] = $_REQUEST['eventid'];
			$data['tabname'] = $_REQUEST['tabname'];
			$data['source'] = 'u'; //aded by user interactions
			$data['done_date'] = $_REQUEST['donedate']; //aded by user interactions
			
			if($_REQUEST['tabname'] == "anlage" || $_REQUEST['tabname'] == "anlage4awl"){

				$data['triggered_by'] = "forced_system";
				$extra  = explode("_",$_REQUEST['extra']);
				$decid = Pms_Uuid::decrypt(end($extra));
				$extra[5] = $decid; // replace encrypted id  with normal id
				$ipid = Pms_CommonData::getIpid($decid);
				$data['ipid'] = $ipid;
				$data['extra'] = implode('_',$extra);
				
			} else {
				
				$data['extra'] = $_REQUEST['extra'];
			}
			$data['due_date'] = date("Y-m-d 00:00:00",strtotime($_REQUEST['donedate'])); 
			
			$dashboard_labels = new DashboardLabels();
			$action_last_label = $dashboard_labels->getActionsLastLabel();
			$action_last_label['custom_doctor_event_team'] = $action_last_label['custom_team_event'];
			
			$labels_f['0'] = $this->view->translate('select');
			foreach($action_last_label as $k_act_label => $v_act_label)
			{
				if($k_act_label != "custom_doctor_event_team")
				{
					$labels_f[$k_act_label] = $v_act_label['name'];
				}
			}
			
			if($_REQUEST['label_filter'] && $_REQUEST['label_filter'] != '0' && $_REQUEST['label_filter'] != 'undefined')//0=all
			{
				$this->view->label_filter_selected = $_REQUEST['label_filter'];				
			}
			
			$this->view->sort_order_selected = $_REQUEST['sort_order'];
			
			$sort_arr = array('asc' => $this->view->translate('asc_sort'), 'desc' => $this->view->translate('desc_sort'));
			$this->view->date_sort = $this->view->formSelect("date_sort", $_REQUEST['sort_order'], '', $sort_arr);
			$this->view->label_filter = $this->view->formSelect("label_filter", $_REQUEST['label_filter'], '', $labels_f);
			
			if($_REQUEST['mode'] == 'undone')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//changed to delete a comment in verlauf entry
					//$save_todo = $todos->uncompleteTodo($_REQUEST['eventid']);
					$save_todo = $todos->uncompleteTodonew($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'custom_doctor_event_team')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
			
				$done_entry_id = $_REQUEST['eventid'];
				$labels_event_form = $labels_form->delete_done_entry($done_entry_id);
				echo '1';
				exit;
			}
			elseif($_REQUEST['mode'] == 'done')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//$save_todo = $todos->completeTodo($_REQUEST['eventid']);
					//changed to write a comment in verlauf entry
					$save_todo = $todos->completeTodonew($_REQUEST['eventid'], $_REQUEST['event_comment']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_sgbxi = $dashboard_events->complete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'anlage' || $_REQUEST['tabname'] == 'anlage4awl')
				{
					$save_anlage = $dashboard_events->create_dashboard_event($data,true);
				}
			
				$labels_event_form = $labels_form->add_done_entry($data);
				echo '1';
				exit;
			}
		}
		
		
		public function dashboardlistgroupedAction ()
		{
			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->_helper->layout->setLayout('layout_ajax');
			
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$labels_form = new Application_Form_DashboardActions();
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$extra = array();
			$data['client'] = $clientid;
			$data['user'] = $userid;
			$data['event'] = $_REQUEST['eventid'];
			$data['tabname'] = $_REQUEST['tabname'];
			$data['source'] = 'u'; //aded by user interactions
			$data['done_date'] = $_REQUEST['donedate']; //aded by user interactions
			
			if($_REQUEST['tabname'] == "anlage" || $_REQUEST['tabname'] == "anlage4awl"){

				$data['triggered_by'] = "forced_system";
				$extra  = explode("_",$_REQUEST['extra']);
				$decid = Pms_Uuid::decrypt(end($extra));
				$extra[5] = $decid; // replace encrypted id  with normal id
				$ipid = Pms_CommonData::getIpid($decid);
				$data['ipid'] = $ipid;
				$data['extra'] = implode('_',$extra);
				
			} else {
				
				$data['extra'] = $_REQUEST['extra'];
			}
			$data['due_date'] = date("Y-m-d 00:00:00",strtotime($_REQUEST['donedate'])); 
			
			$dashboard_labels = new DashboardLabels();
			$action_last_label = $dashboard_labels->getActionsLastLabel();
			$action_last_label['custom_doctor_event_team'] = $action_last_label['custom_team_event'];
			
			$labels_f['0'] = $this->view->translate('select');
			foreach($action_last_label as $k_act_label => $v_act_label)
			{
				if($k_act_label != "custom_doctor_event_team")
				{
					$labels_f[$k_act_label] = $v_act_label['name'];
				}
			}
			
			if($_REQUEST['label_filter'] && $_REQUEST['label_filter'] != '0' && $_REQUEST['label_filter'] != 'undefined')//0=all
			{
				$this->view->label_filter_selected = $_REQUEST['label_filter'];				
			}
			
			$this->view->sort_order_selected = $_REQUEST['sort_order'];
			
			$sort_arr = array('asc' => $this->view->translate('asc_sort'), 'desc' => $this->view->translate('desc_sort'));
			$this->view->date_sort = $this->view->formSelect("date_sort", $_REQUEST['sort_order'], '', $sort_arr);
			$this->view->label_filter = $this->view->formSelect("label_filter", $_REQUEST['label_filter'], '', $labels_f);
			
			if($_REQUEST['mode'] == 'undone')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//changed to delete a comment in verlauf entry
					//$save_todo = $todos->uncompleteTodo($_REQUEST['eventid']);
					$save_todo = $todos->uncompleteTodonew($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'custom_doctor_event_team')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_todo = $dashboard_events->uncomplete_dashboard_event($_REQUEST['eventid']);
				}
			
				$done_entry_id = $_REQUEST['eventid'];
				$labels_event_form = $labels_form->delete_done_entry($done_entry_id);
				echo '1';
				exit;
			}
			elseif($_REQUEST['mode'] == 'done')
			{
				if($_REQUEST['tabname'] == 'todo' || $_REQUEST['tabname'] == 'old_todo')
				{
					//$save_todo = $todos->completeTodo($_REQUEST['eventid']);
					//changed to write a comment in verlauf entry
					$save_todo = $todos->completeTodonew($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'sgbxi' || $_REQUEST['tabname'] == 'old_sgbxi')
				{
					$save_sgbxi = $dashboard_events->complete_dashboard_event($_REQUEST['eventid']);
				}
				elseif($_REQUEST['tabname'] == 'anlage' || $_REQUEST['tabname'] == 'anlage4awl')
				{
					$save_anlage = $dashboard_events->create_dashboard_event($data,true);
				}
			
				$labels_event_form = $labels_form->add_done_entry($data);
				echo '1';
				exit;
			}
		}
		
		public function getdashboardlistAction()
		{
			setlocale(LC_ALL, 'de_DE.UTF-8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
		
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$this->view->userid = $userid;
		
			$groupid = $logininfo->groupid;
			$user_type = $logininfo->usertype;
			$done_events = new DashboardActionsDone();
			$labels_form = new Application_Form_DashboardActions();
			$todos = new ToDos();
			$dashboard_events = new DashboardEvents();
			$wlprevileges = new Modules();
			$pm = new PatientMaster();
			
			$user_c = new User();
			$user_c_details = $user_c->getUserDetails($userid);
			$client_users = $user_c->getUserByClientid($clientid, 0, true);
			foreach($client_users as $k_c_usr => $v_c_usr)
			{
				$client_users_arr[$v_c_usr['id']] = $v_c_usr;
			}
			$data['client'] = $clientid;
			$data['user'] = $userid;
			$data['event'] = $_REQUEST['eventid'];
			$data['tabname'] = $_REQUEST['tabname'];
			$data['source'] = 'u'; //aded by user interactions
			$data['done_date'] = $_REQUEST['donedate']; //aded by user interactions
			
			if(!$_REQUEST['length'])
			{
				$_REQUEST['length'] = "150";
			}
			
			$limit = $_REQUEST['length'];
			$offset = $_REQUEST['start'];
			
			$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
			$modules = new Modules();
				
			if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
			{
				$this->view->acknowledge_func = "1";
				if(in_array($userid,$approval_users)){
					$this->view->approval_rights = "1";
				} else{
					$this->view->approval_rights = "0";
				}
					
			}				
			else
			{
				$this->view->acknowledge_func = "0";
			}
			
			//load excluded events
			$excluded_events = $done_events->getClientDashboardActions($clientid, true);
			$all_excluded_events = $done_events->getClientDashboardActions($clientid,false, true);
		
			
			foreach($all_excluded_events as $k=>$done_action){
				if($done_action['tabname'] == "anlage" || $done_action['tabname'] == "anlage4awl" ){
					$last_excluded[$done_action['tabname']]  [$done_action['event']] = $done_action; // add last event
											
					if(!empty($done_action['extra'])){
						$done_acts[$done_action['tabname']][] =  $done_action['extra'];
						$done_acts_events[$done_action['tabname']][$done_action['event']][] =  $done_action['extra'];
					} 
				}
			}
			$start_before7days = date("d.m.Y", strtotime("-7 days",time() ));
			$end_after7days = date("d.m.Y", strtotime("+7 days",time() ));
			$days_arr = $pm->getDaysInBetween($start_before7days, $end_after7days,false,"d.m.Y");
 
			/* ------------ BOX - "User Dashboard " START---------------- */
			$label_actions = Pms_CommonData::get_dashboard_actions();
		
			$dashboard_labels = new DashboardLabels();
			$label_details = $dashboard_labels->getClientLabels();
		
			foreach($label_details as $k_label => $v_label)
			{
				$labels[$v_label['id']] = $v_label;
			}
		
		
			//ANLAGE 4aWL
			$user = Doctrine_Query::create()
			->select("*")
			->from('User')
			->where('clientid = ' . $clientid . ' or usertype="SA"')
			->andWhere('isactive=0 and isdelete = 0')
			->orderBy('last_name ASC');
			$userarray = $user->fetchArray();
		
			$comma = ",";
			$usercomma = "'0'";
			if(count($userarray) > 0)
			{
				foreach($userarray as $key => $valu)
				{
					$clientUsersArray[$valu['id']] = $valu;
					$usercomma .= $comma . "'" . $valu['id'] . "'";
					$comma = ",";
				}
			}
			$key_start = 0;
			$wl_perms = $wlprevileges->checkModulePrivileges("51", $clientid);
			if($wl_perms)
			{
		
				$sqlWeekDays = "";
				$sqlHaving = "";
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `admission_date` ) , 56 ) AS sixWeeks" . $i . " ,";
					$sqlHaving .= "sixWeeks" . $i . " = 0 OR ";
				}
				$sqlHaving = substr($sqlHaving, 0, -4);
		
				$patientwl = Doctrine_Query::create()
				->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date, " . $sqlWeekDays . " e.epid")
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('admission_date < DATE(NOW())')
				->having($sqlHaving);
				$patientwl->leftJoin("p.EpidIpidMapping e");
				$patientwl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
		
				//LEft join cu patient qupa mapping cu userid logininfo
				if($clientUsersArray[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl->leftJoin("e.PatientQpaMapping q");
					$patientwl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all = $patientwl->fetchArray();
		
				$pat_array[] = '999999999';
				foreach($patientidwlarray_all as $k_pat => $v_pat)
				{
					$pat_array[] = $v_pat['ipid'];
				}
		
		
		
				//private patients
				$health = Doctrine_Query::create();
				$health->select("*")
				->from('PatientHealthInsurance')
				->whereIn('ipid', $pat_array)
				->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();
		
				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				

				//get hospiz location
				$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where("location_type=2")
				->andWhere('isdelete=0')
				->andWhere("client_id=?", $clientid)
				->orderBy('location ASC');
				$lochospizarr = $fdoc->fetchArray();
				
				foreach($lochospizarr as $k_hospiz => $v_hospiz)
				{
					$locid_hospiz[] = $v_hospiz['id'];
				}
				
				//get patient with location active Hospiz
				if(!empty($locid_hospiz)){
					$patlocs = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $pat_array)
					->andWhereIn('location_id', $locid_hospiz)
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');
					$patloc_hospizarr = $patlocs->fetchArray();

					foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
					{
						$ipids_hospiz[] = $v_pathospiz['ipid'];
					}
				}		  
				
				//remove private patients
				$patientwl->andWhereNotIn('p.ipid', $privat_patient);
				//remove patients with hospiz location
				if(!empty($ipids_hospiz)){
					$patientwl->andWhereNotIn('p.ipid', $ipids_hospiz);
				}
				$patientidwlarray = $patientwl->fetchArray();

				//process anlage 4 result array
				foreach($patientidwlarray as $k_pat_today => $v_pat_today)
				{
					$tabname = 'anlage';
					if(isset($last_excluded[$tabname][$v_pat_today['id']]['create_date']) ) {
						$done_date[$v_pat_today['id']] = date('d.m.Y',strtotime($last_excluded[$tabname][$v_pat_today['id']]['create_date']));
					} else {
						$done_date[$v_pat_today['id']] ="";
					}
 
					if($v_pat_today['sixWeeks0'] == 0)
					{ //today
						$due_date = date("d.m.Y");
						$date =  $pm->getDaysDiff(date("d.m.Y",strtotime($v_pat_today['admission_date'])), $due_date);
						$extra_key[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".$v_pat_today['patientId'];
						$extra_key_enc[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".Pms_Uuid::encrypt($v_pat_today['patientId']);
 
						if(!in_array($extra_key[$v_pat_today['id']], $done_acts[$tabname]))
						{
							if($_REQUEST['done'] == "x" ){
								print_R( $last_excluded[$tabname][$v_pat_today['id']]['create_date'] );
								print_R( $done_acts_events[$done_action['tabname']][$v_pat_today['id']] );
							}
							
// 							if(empty($done_acts_events[$done_action['tabname']][$v_pat_today['id']])  && isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {
							if(isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {
							
								$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
								$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
								$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '" data-event="anlage6weeks_56days">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . '</a>';
								$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($v_pat_today['EpidIpidMapping']['epid']).' - '.$v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'];
								$master_data[strtotime($due_date)][$key_start]['due_date'] = date("d.m.Y");
								$master_data[strtotime($due_date)][$key_start]['extra'] = $extra_key_enc[$v_pat_today['id']];
								$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_pat_today['ipid'];
								$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
								$key_start++;
							}
						}
					}
					else
					{ //next week //no need to check any further cause all next 7 days are selected from query
						$curentDay = array_search("0", $v_pat_today, true);
						$curentDay = str_replace('sixWeeks', '', $curentDay);

						$due_date = date("d.m.Y", strtotime("+ " . $curentDay . " day"));
						$date =  $pm->getDaysDiff(date("d.m.Y",strtotime($v_pat_today['admission_date'])), $due_date);
						$extra_key[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".$v_pat_today['patientId'];
						$extra_key_enc[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".Pms_Uuid::encrypt($v_pat_today['patientId']);
						
						if($curentDay != "0" && !in_array($extra_key[$v_pat_today['id']], $done_acts[$tabname])  )
						{
// 							if(empty($done_acts_events[$tabname][$v_pat_today['id']])  && isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {
							if(isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {

								$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
			
								$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
								$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4wl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '"  data-event="anlage6weeks_56days">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . '</a>';
								$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($v_pat_today['EpidIpidMapping']['epid']).' - '.$v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'];
								$master_data[strtotime($due_date)][$key_start]['due_date'] = $due_date;
								$master_data[strtotime($due_date)][$key_start]['extra'] = $extra_key_enc[$v_pat_today['id']];
								$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_pat_today['ipid'];
								$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
								$key_start++;
							}
						}
					}
				}
			}
			//ANLAGE 4a WL END

			if($wl_perms)
			{
		
				$sqlWeekDays = "";
				$sqlHaving = "";
				for($i = 0; $i <= 8; $i++)
				{
					$sqlWeekDays .= "MOD( DATEDIFF(  '" . date("Y-m-d", strtotime("+ " . $i . " day")) . "',  `vollversorgung_date` ) , 28 ) AS fourWeeks" . $i . " ,";
					$sqlHaving .= "fourWeeks" . $i . " = 0 OR ";
				}
				$sqlHaving = substr($sqlHaving, 0, -4);
		
				$patientidwlarray_all_4awl = array();
				$patientwl_4awl = Doctrine_Query::create()
				->select("id as patientId, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid,vollversorgung_date, admission_date, " . $sqlWeekDays . " e.epid")
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere('vollversorgung = 1')
				->andWhere('vollversorgung_date < DATE(NOW())')
				->having($sqlHaving);
				$patientwl_4awl->leftJoin("p.EpidIpidMapping e");
				$patientwl_4awl->andWhere('e.ipid = p.ipid and e.clientid = ' . $clientid);
		
				//LEft join cu patient qupa mapping cu userid logininfo
				if($clientUsersArray[$userid]['onlyAssignedPatients'] == 1)
				{
					$patientwl_4awl->leftJoin("e.PatientQpaMapping q");
					$patientwl_4awl->andWhere("q.userid = '" . $userid . "'");
				}
				$patientidwlarray_all_4awl = $patientwl_4awl->fetchArray();
		
				$pat_array = array();
				$pat_array[] = '999999999';
				foreach($patientidwlarray_all_4awl as $k_pat => $v_pat)
				{
					$pat_array[] = $v_pat['ipid'];
				}
		
				//private patients
				$patientidwlarray_4awl = array();
				$health = Doctrine_Query::create();
				$health->select("*")
				->from('PatientHealthInsurance')
				->whereIn('ipid', $pat_array)
				->andWhere('privatepatient="1"');
				$health_arr = $health->fetchArray();
		
				$privat_patient[] = '99999999';
				foreach($health_arr as $k_health => $v_health)
				{
					$privat_patient[] = $v_health['ipid'];
				}
				//remove private patients
				$patientwl_4awl->andWhereNotIn('p.ipid', $privat_patient);
				
				//get hospiz location
				$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where("location_type=2")
				->andWhere('isdelete=0')
				->orderBy('location ASC');
				$lochospizarr = $fdoc->fetchArray();
				
				foreach($lochospizarr as $k_hospiz => $v_hospiz)
				{
					$locid_hospiz[] = $v_hospiz['id'];
				}
				
				//get patient with location active Hospiz
				if(!empty($locid_hospiz)){
				
					$patlocs = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $pat_array)
					->andWhereIn('location_id', $locid_hospiz)
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');
					$patloc_hospizarr = $patlocs->fetchArray();
						
					foreach($patloc_hospizarr as $k_pathospiz => $v_pathospiz)
					{
						$ipids_hospiz[] = $v_pathospiz['ipid'];
					}
				}
				
				//remove patients with hospiz location
				if(!empty($ipids_hospiz)){
					$patientwl->andWhereNotIn('p.ipid', $ipids_hospiz);
				}
				
				$patientidwlarray_4awl = $patientwl_4awl->fetchArray();
		
				//process anlage 4 result array
				foreach($patientidwlarray_4awl as $k_pat_today => $v_pat_today)
				{
					$tabname = 'anlage4awl';
					if(isset($last_excluded[$tabname][$v_pat_today['id']]['create_date']) ) {
						$done_date[$v_pat_today['id']] = date('d.m.Y',strtotime($last_excluded[$tabname][$v_pat_today['id']]['create_date']));
					} else {
						$done_date[$v_pat_today['id']] ="";
					}
					
					if($v_pat_today['fourWeeks0'] == 0)
					{ //today
						
						$due_date = date("d.m.Y");
						$date =  $pm->getDaysDiff(date("d.m.Y",strtotime($v_pat_today['vollversorgung_date'])), $due_date);
						$extra_key[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".$v_pat_today['patientId'];
						$extra_key_enc[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".Pms_Uuid::encrypt($v_pat_today['patientId']) ;

						if(!in_array($extra_key[$v_pat_today['id']], $done_acts[$tabname]))
						{
// 							if(empty($done_acts_events[$tabname][$v_pat_today['id']])  && isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) 
							if(isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) 
							{
								$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
								$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
								$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '"   data-event="anlage4weeks_28days">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . ' </a>';
								$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($v_pat_today['EpidIpidMapping']['epid']).' - '.$v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'];
								$master_data[strtotime($due_date)][$key_start]['due_date'] = date("d.m.Y");
								$master_data[strtotime($due_date)][$key_start]['extra'] = $extra_key_enc[$v_pat_today['id']];
								$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_pat_today['ipid'];
								$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
								$key_start++;
							}
						}
					}
					else
					{ //next week //no need to check any further cause all next 7 days are selected from query
						$curentDay = array_search("0", $v_pat_today, true);
						$curentDay = str_replace('fourWeeks', '', $curentDay);
						
						$due_date = date("d.m.Y", strtotime("+ " . $curentDay . " day"));
						$date =  $pm->getDaysDiff(date("d.m.Y",strtotime($v_pat_today['vollversorgung_date'])), $due_date);
						$extra_key[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".$v_pat_today['patientId'];
						$extra_key_enc[$v_pat_today['id']] = "days_".$date."_due-date_".$due_date."_pat_".Pms_Uuid::encrypt($v_pat_today['patientId']);
						
						if($curentDay != "0" && !in_array($extra_key[$v_pat_today['id']], $done_acts[$tabname])  )
						{
// 							if(empty($done_acts_events[$tabname][$v_pat_today['id']])  && isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {
							if(isset($done_date[$v_pat_today['id']]) && !in_array($done_date[$v_pat_today['id']],$days_arr)) {
						
								$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
								$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_pat_today['id'];
								$master_data[strtotime($due_date)][$key_start]['event_title'] = '<a href="patient/anlage4awl?id=' . Pms_Uuid::encrypt($v_pat_today['patientId']) . '"  data-event="anlage4weeks_28days">' . $v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'] . ' </a>';
								$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($v_pat_today['EpidIpidMapping']['epid']).' - '.$v_pat_today['last_name'] . ', ' . $v_pat_today['first_name'];
								$master_data[strtotime($due_date)][$key_start]['due_date'] = $due_date;
								$master_data[strtotime($due_date)][$key_start]['extra'] = $extra_key_enc[$v_pat_today['id']];
								$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_pat_today['ipid'];
								$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
								$key_start++;
							}
						}
					}
				}
			}
			//ANLAGE 4a WL END
			//Assessment
			$client_patients_q = Doctrine_Query::create()
			->select('pm.ipid,ep.epid')
			->from('PatientMaster pm')
			->where('pm.isdelete = 0')
			->andWhere('pm.isstandbydelete = 0')
			->andWhere('pm.isstandby = 0')
			->andWhere('pm.isdischarged = 0')
			->leftJoin('pm.EpidIpidMapping ep')
			->andWhere('ep.clientid=' . $logininfo->clientid)
			->andWhere('ep.ipid=pm.ipid');
			$clipids = $client_patients_q->fetchArray();
		
			$client_ipids_arr[] = "'9999999999999999999999999999'";
			foreach($clipids as $clipi)
			{
				$client_ipids_arr[] = $clipi['ipid'];
				$patientsEpidsFinal[$clipi['ipid']] = $clipi;
			}
		
			$assessment_events = Doctrine_Query::create()
			->select("*")
			->from('KvnoAssessment ')
			->whereIn('ipid', $client_ipids_arr)
			->andwhere("reeval between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
			->andWhere('iscompleted="1"');
			$assessment_events_arr = $assessment_events->fetchArray();
		
			$ipidass_arr[] = '9999999999999999999999999999';
			foreach($assessment_events_arr as $dvisit)
			{
				$ipidass_arr[] = $dvisit['ipid'];
			}
		
			$pm = Doctrine_Query::create()
			->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date")
			->from('PatientMaster')
			->whereIn('ipid', $ipidass_arr);
			$patientsids = $pm->fetchArray();
		
			foreach($patientsids as $patient)
			{
				$patientsFinalIds[$patient['ipid']] = $patient;
			}
		
			$reassessments_ids = array();
			if(!empty($assessment_events_arr))
			{
				//				$tabname = 'asses';
				$tabname = 'reasses';
				foreach($assessment_events_arr as $key => $assess)
				{
					if(!in_array($assess['id'], $excluded_events[$tabname]))
					{
						$due_date = date('Y-m-d', strtotime($assess['reeval']));
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $assess['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($patientsEpidsFinal[$assess['ipid']]['EpidIpidMapping']['epid']) . " - " . ucfirst($patientsFinalIds[$assess['ipid']]['last_name']) . ", " . ucfirst($patientsFinalIds[$assess['ipid']]['first_name']);
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$reassessments_ids[] = $assess['id']; 
						$key_start++;
					}
				}
			}
				
			/* //Assesment second
			 	
			$assessment_events_sec = Doctrine_Query::create()
			->select("*")
			->from('KvnoAssessment ')
			->whereIn('ipid', $client_ipids_arr)
			->andwhere("DATE(DATE_ADD(reeval,INTERVAL 14 DAY)) between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
			->andWhere('iscompleted="1"');
			$assessment_eventsec_arr = $assessment_events_sec->fetchArray();
				
			$ipidass_sec_arr[] = '9999999999999999999999999999';
			foreach($assessment_eventsec_arr as $dvisit)
			{
			$ipidass_sec_arr[] = $dvisit['ipid'];
			}
				
			$pm_s = Doctrine_Query::create()
			->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid, admission_date")
			->from('PatientMaster')
			->whereIn('ipid', $ipidass_sec_arr);
			$patientsids_sec = $pm_s->fetchArray();
				
			foreach($patientsids_sec as $patient)
			{
			$patientsFinalIds_sec[$patient['ipid']] = $patient;
			}
			//print_r($assessment_eventsec_arr);exit;
			if(!empty($assessment_eventsec_arr))
			{
			//	$tabname = 'asses';
			$tabname = 'reasses';
			foreach($assessment_eventsec_arr as $key => $assess_sec)
			{
			if(!in_array($assess_sec['id'], $excluded_events[$tabname]))
			{
			$due_date = date('Y-m-d',strtotime("+14 days",strtotime($assess_sec['reeval'])));
			$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
			$master_data[strtotime($due_date)][$key_start]['event_id'] = $assess['id'];
			$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($patientsEpidsFinal[$assess_sec['ipid']]['EpidIpidMapping']['epid']) . " - " . ucfirst($patientsFinalIds_sec[$assess_sec['ipid']]['last_name']) . ", " . ucfirst($patientsFinalIds_sec[$assess_sec['ipid']]['first_name']);
			$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
			$key_start++;
			}
			}
			} */
		
			//Assessment END
			//Re-Assessment
			$reassessmentweekall = array();
			$reassessmenttoday = array();
			$reassesmentprv = new Modules();
			$reass_mod = $reassesmentprv->checkModulePrivileges("56", $logininfo->clientid);
		
			//check reasessment module
			if($reass_mod)
			{
				//------------------------
				//Re-Assessment 14 days event
				//------------------------
				// get type of user, if Koordinator show all reassesments
		
				$usergroup = new Usergroup();
				$master_groups = array("6"); // Koordinator
				$usersgroups = $usergroup->getUserGroups($master_groups);
				if(count($usersgroups) > 0)
				{
					foreach($usersgroups as $group)
					{
						$groupsarray[] = $group['id'];
					}
				}
				$usrs = new User();
				$koord_array = $usrs->getuserbyGroupId($groupsarray, $clientid);
		
				foreach($koord_array as $user)
				{
					$koords[] = $user['id'];
				}
		
		
				$allpatk = Doctrine_Query::create()
				->select('pm.ipid')
				->from('PatientMaster pm')
				->where('pm.isdelete = 0 and pm.isstandbydelete = 0 and pm.isstandby = 0')
				->andWhere('isdischarged = 0')
				->leftJoin('pm.EpidIpidMapping ep')
				->andWhere('ep.clientid=' . $clientid)
				->andWhere('ep.ipid=pm.ipid');
				$allpatkoor = $allpatk->fetchArray();
		
		
				$re_ipidval_arr[] = '9999999999999';
				if(in_array($userid, $koords))
				{
					$comma = ",";
					$re_ipidval = "'0'";
					foreach($allpatkoor as $key => $val)
					{
						$re_ipidval_arr[] = $val['ipid'];
						$re_ipidval .= $comma . "'" . $val['ipid'] . "'";
						$comma = ",";
					}
				}
				$reassessment_week = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment ')
				->whereIn("ipid", $re_ipidval_arr)
				->andwhere("reeval between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
				->andWhere('iscompleted="1"');
				$reassessment_week_array = $reassessment_week->fetchArray();
		
		
				$ipids_reassessmet[] = '99999999999999999';
				foreach($reassessment_week_array as $dvisit)
				{
					$ipids_reassessmet[] = $dvisit['ipid'];
				}
		
				$repatientsipidepid = Doctrine_Query::create()
				->select('ipid,epid')
				->from('EpidIpidMapping')
				->whereIn('ipid', $ipids_reassessmet);
				$repatientsepids = $repatientsipidepid->fetchArray();
		
		
				$repm = Doctrine_Query::create()
				->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
				->from('PatientMaster')
				->whereIn('ipid', $ipids_reassessmet);
				$repatientsids = $repm->fetchArray();
		
		
				foreach($repatientsids as $patient)
				{
					$repatientsFinalIds[$patient['ipid']] = $patient;
				}
		
				foreach($repatientsepids as $pat)
				{
					$repatientsEpidsFinal[$pat['ipid']] = $pat;
				}
		
				if(!empty($reassessment_week_array))
				{
					$tabname = 'reasses';
					foreach($reassessment_week_array as $rekey => $reassess)
					{
						if(!in_array($reassess['id'], $excluded_events[$tabname]) && !in_array($reassess['id'], $reassessments_ids) )
						{
							$due_date = date('Y-m-d', strtotime($reassess['reeval']));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $reassess['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($repatientsEpidsFinal[$reassess['ipid']]['epid']) . " - " . ucfirst($repatientsFinalIds[$reassess['ipid']]['last_name']) . ", " . ucfirst($repatientsFinalIds[$reassess['ipid']]['first_name']);
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
				//Re-assesment 28 days
		
				$reassessment_week_sec = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment ')
				->whereIn("ipid", $re_ipidval_arr)
				->andwhere("DATE(DATE_ADD(reeval,INTERVAL 14 DAY)) between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
				->andWhere('iscompleted="1"');
				$reassessment_weeksec_array = $reassessment_week_sec->fetchArray();
		
				$ipids_reassessmet_sec[] = '99999999999999999';
				foreach($reassessment_weeksec_array as $rassm)
				{
					$ipids_reassessmet_sec[] = $rassm['ipid'];
				}
		
				$repatientsipidepid_sec = Doctrine_Query::create()
				->select('ipid,epid')
				->from('EpidIpidMapping')
				->whereIn('ipid', $ipids_reassessmet_sec);
				$repatientsepids_sec = $repatientsipidepid_sec->fetchArray();
		
				$repm_sec = Doctrine_Query::create()
				->select("id, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
				->from('PatientMaster')
				->whereIn('ipid', $ipids_reassessmet_sec);
				$repatientsids_sec = $repm_sec->fetchArray();
		
		
				foreach($repatientsids_sec as $patient)
				{
					$repatientsFinalIds_sec[$patient['ipid']] = $patient;
				}
		
				foreach($repatientsepids_sec as $pat)
				{
					$repatientsEpidsFinal_sec[$pat['ipid']] = $pat;
				}
					
				if(!empty($reassessment_weeksec_array))
				{
					$tabname = 'reasses';
					foreach($reassessment_weeksec_array as $rekey_sec => $reassess_sec)
					{
						if(!in_array($reassess_sec['id'], $excluded_events[$tabname]))
						{
							$due_date = date('Y-m-d',strtotime("+14 days",strtotime($reassess_sec['reeval'])));
							$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
							$master_data[strtotime($due_date)][$key_start]['event_id'] = $reassess_sec['id'];
							$master_data[strtotime($due_date)][$key_start]['event_title'] = strtoupper($repatientsEpidsFinal_sec[$reassess_sec['ipid']]['epid']) . " - " . ucfirst($repatientsFinalIds_sec[$reassess_sec['ipid']]['last_name']) . ", " . ucfirst($repatientsFinalIds_sec[$reassess_sec['ipid']]['first_name']);
							$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
							$key_start++;
						}
					}
				}
			}
			//Re-Assessment END
			//Custom events
			$team_event_types = array(
					'12' => 'Ferien: ',
					'13' => 'Team Sitzungen: ',
					'14' => 'Fortbildung: ',
					'15' => 'Supervision: ',
					'16' => 'Kongress: ',
					'17' => 'Rufbereitschaft: ',
					'18' => 'Urlaub / Vertretung: ',
					'20' => 'Einsatzleitung: ',
					'21' => 'Termin: ',
					'22' => 'Freier Termin: ',
			);
		
			//ISPC-311 - comments - show all team events
			$team_events = Doctrine_Query::create()
			->select("*")
			->from('TeamCustomEvents')
			->where('clientid ="' . $clientid . '"')
			->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
			$team_events_res = $team_events->fetchArray();
		
		
			$shown_todos[] = '999999999999';
			foreach($team_events_res as $k_team_events => $v_team_events)
			{
		
				if($v_team_events['user_id'] > '0' && $v_team_events != $userid && array_key_exists($v_team_events['userid'], $client_users_arr))
				{
					$user_details = $client_users_arr[$v_team_events['userid']]['user_title'] . ' ' . $client_users_arr[$v_team_events['userid']]['last_name'] . ', ' . $client_users_arr[$v_team_events['userid']]['first_name'];
				}
				else
				{
					$user_details = '';
				}
		
				//					$tabname = 'team_events';
				$tabname = 'custom_team_event';
				if(!in_array($v_team_events['id'], $excluded_events[$tabname]))
				{
					$due_date = date('Y-m-d', strtotime($v_team_events['endDate']));
					$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
					$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_team_events['id'];
					$master_data[strtotime($due_date)][$key_start]['event_type'] = $team_event_types[$v_team_events['eventType']];
					$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_team_events['eventTitle'];
					$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
					$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
					$key_start++;
		
					$shown_todos[] = $v_team_events['id'];
				}
			}
		
			//usergroup team events
			$m_group = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('id = "' . $groupid . '"');
			$group_details = $m_group->fetchArray();
		
			if($group_details[0]['indashboard'] == "1")
			{
    			//get doctor custom events
    			$doc_custom_events = new DoctorCustomEvents();
    			$doc_custom_events_arr = $doc_custom_events->get_doc_team_all_custom_events($clientid);
    			foreach($doc_custom_events_arr as $k_doc_event => $v_doc_event)
    			{
    
    				if($v_doc_event['user_id'] > '0' && $v_doc_event != $userid && array_key_exists($v_doc_event['userid'], $client_users_arr))
    				{
    					$user_details = $client_users_arr[$v_doc_event['userid']]['user_title'] . ' ' . $client_users_arr[$v_doc_event['userid']]['last_name'] . ', ' . $client_users_arr[$v_doc_event['userid']]['first_name'];
    				}
    				else
    				{
    					$user_details = '';
    				}
    
    				//					$tabname = 'team_events';
    				$tabname = 'custom_doctor_event_team';
    				if(!in_array($v_doc_event['id'], $excluded_events[$tabname]))
    				{
    					$doc_due_date = date('Y-m-d', strtotime($v_doc_event['endDate']));
    					$master_data[strtotime($doc_due_date)][$key_start]['tabname'] = $tabname;
    					$master_data[strtotime($doc_due_date)][$key_start]['event_id'] = $v_doc_event['id'];
    					$master_data[strtotime($doc_due_date)][$key_start]['event_type'] = $team_event_types[$v_doc_event['eventType']];
    					$master_data[strtotime($doc_due_date)][$key_start]['event_title'] = $v_doc_event['eventTitle'];
    					$master_data[strtotime($doc_due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($doc_due_date));
    					$master_data[strtotime($doc_due_date)][$key_start]['todo_user'] = $user_details;
    					$key_start++;
    				}
    			}
			}
		
        	//get custom team events if profile settings allows it
        	if($user_c_details[0]['show_custom_events'] == '1')
        	{
        		$doctor_event_types = array('10' => 'Termin: ', '11' => 'Notiz: ');
        
        		$team_custom_event = Doctrine_Query::create()
        		->select("*")
        		->from('TeamCustomEvents')
        		->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
        		->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'");
        		$team_custom_event_res = $team_custom_event->fetchArray();
        
        		//				Process team custom events (curent user added events)
        		foreach($team_custom_event_res as $k_team_event => $v_team_event)
        		{
        			$tabname = 'custom_team_event';
        			if(!in_array($v_team_event['id'], $excluded_events[$tabname]) && !in_array($v_team_event['id'], $shown_todos))
        			{
        				$due_date = date('Y-m-d', strtotime($v_team_event['endDate']));
        				$master_data[strtotime($due_date)][$key_start]['tabname'] = 'custom_team_event';
        				$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_team_event['id'];
        				$master_data[strtotime($due_date)][$key_start]['event_type'] = $team_event_types[$v_team_event['eventType']];
        				$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_team_event['eventTitle'];
        				$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        				$key_start++;
        			}
        		}
        
        		// doctor custom events
        		$doc_custom_event = Doctrine_Query::create()
        		->select("*")
        		->from('DoctorCustomEvents')
        		->where("create_user='" . $userid . "' and clientid='" . $clientid . "'")
        		->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
        		->andWhere('viewForAll != "1"');
        
        		$doc_custom_event_res = $doc_custom_event->fetchArray();
        
        		//get doctor event ipids
        		$doc_ipids[] = '99999999999';
        		foreach($doc_custom_event_res as $kdoc_event => $vdoc_event)
        		{
        			$doc_ipids[] = $vdoc_event['ipid'];
        		}
        
        		$doc_ipid_epid = Doctrine_Query::create()
        		->select('ipid,epid')
        		->from('EpidIpidMapping')
        		->whereIn('ipid', $doc_ipids);
        		$doc_ipid_epid_res = $doc_ipid_epid->fetchArray();
        
        
        		$doc_cust_evts_pat = Doctrine_Query::create()
        		->select("id,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name, AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name, ipid ")
        		->from('PatientMaster')
        		->whereIn('ipid', $doc_ipids);
        		$doctors_ipid_pats = $doc_cust_evts_pat->fetchArray();
        		foreach($doctors_ipid_pats as $k_doc => $v_doc)
        		{
        			$doctors_cust_ev_patients[$v_doc['ipid']] = $v_doc;
        		}
        
        		foreach($doc_ipid_epid_res as $k_doc_epid => $v_doc_epid)
        		{
        			$doc_ipids_epids[$v_doc_epid['ipid']]['epid'] = $v_doc_epid['epid'] . ' - ' . $doctors_cust_ev_patients[$v_doc_epid['ipid']]['last_name'] . ', ' . $doctors_cust_ev_patients[$v_doc_epid['ipid']]['first_name'];
        		}
        
        		//process doc custom events
        		foreach($doc_custom_event_res as $k_doc_event => $v_doc_event)
        		{
        			$tabname = 'custom_doctor_event';
        			if(!in_array($v_doc_event['id'], $excluded_events[$tabname]))
        			{
        				$due_date = date('Y-m-d', strtotime($v_doc_event['endDate']));
        
        				$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
        				$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_doc_event['id'];
        				$master_data[strtotime($due_date)][$key_start]['event_type'] = $doctor_event_types[$v_doc_event['eventType']];
        				$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_doc_event['eventTitle'];
        				$master_data[strtotime($due_date)][$key_start]['event_patient'] = $doc_ipids_epids[$v_doc_event['ipid']]['epid'];
        				$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_doc_event['ipid'];
        				$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        				$key_start++;
        			}
        		}
        	}
        
        	//TODO
        	//Client Users
        	$this->view->userid = $userid;
        	$this->view->groupid = $groupid;
        	$this->view->user_type = $user_type;
        		
        	$users2groups[] = '9999999999999';
        	foreach($client_users as $k_user => $v_user)
        	{
        		$todo_users[$v_user['id']] = $v_user;
        		$client_users[$v_user['id']] = $v_user;
        		$users2groups[$v_user['id']] = $v_user['groupid'];
        		$groups2users[$v_user['groupid']][] = $v_user['id'];
        	}
        
        	$current_user_group_asignees = $groups2users[$groupid];
        	$current_user_group_asignees[] = '999999999';
        	$current_user_group_asignees[] = '9999999';
        
        	$this->view->group2users = $groups2users;
        	if($_REQUEST['dbgz'])
        	{
        		print_r($groupid);
        		print_r($groups2users);
        	}
        
        	//get client coord groups
        	$usergroup = new Usergroup();
        	$MasterGroups = array("6"); // Koordinator
        	$coord_groups[] = '999999999';
        	$usersgroups = $usergroup->getUserGroups($MasterGroups);
        	if(count($usersgroups) > 0)
        	{
        		foreach($usersgroups as $group)
        		{
        			$coord_groups[] = $group['id'];
        		}
        	}
        
        	//todos
        		
        	$all_client_patients_q = Doctrine_Query::create()
        	->select('pm.ipid,ep.epid')
        	->from('PatientMaster pm')
        	->where('pm.isdelete = 0')
        	->leftJoin('pm.EpidIpidMapping ep')
        	->andWhere('ep.clientid=' . $logininfo->clientid)
        	->andWhere('ep.ipid=pm.ipid');
        	$all_clipids = $all_client_patients_q->fetchArray();
        		
        	$all_client_ipids_arr[] = "9999999999999999999999999999";
        	foreach($all_clipids as $clipi)
        	{
        		$all_client_ipids_arr[] = $clipi['ipid'];
        	}
        		
        	$todo = Doctrine_Query::create()
        	->select("*")
        	->from('ToDos')
        	->where('client_id="' . $clientid . '"')
        	->andWhere('isdelete="0"')
        	->andWhereIn('ipid',$all_client_ipids_arr)
        	->andWhere('iscompleted="0"')
        	->orderBy('create_date DESC');
        	if($user_type != 'SA')
        	{
        		if(!in_array($groupid, $coord_groups))
        		{
        			$todo->andWhere('triggered_by !="system"');
        		}
        
        		if($groupid > 0)
        		{
        			$sql_group = ' OR group_id = "' . $groupid . '"';
        		}
        		$todo->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
        	}
        
        	$todo_array = $todo->fetchArray();
        
        	$todo_ipids[] = '99999999999';
        	$receipt_ids[] = '99999999999999';
        	if(count($todo_array) > 0)
        	{
        		//first todo foreach to gather all ipids to avoid 10 second loading as in old todo!
        		//here ... catch all receipts ids too .. in this way we have the receipt creator //only for "triggered_by = newreceipt_1 and newreceipt_2"
        		$triggered_by_arr = "";
        		foreach($todo_array as $k_todo_d => $v_todo_d)
        		{
        			$todo_ipids[] = $v_todo_d['ipid'];
        				
        			if($v_todo_d['triggered_by'] == "newreceipt_1")
        			{
        				$print_receipt_ids[] = $v_todo_d['record_id'];
        			}
        			else if($v_todo_d['triggered_by'] == "newreceipt_2")
        			{
        				$fax_receipt_ids[] = $v_todo_d['record_id'];
        			}
        				
        
        		}
        
        		//query to get all receipts involved
        		//				$receipts_creators = Receipts::get_multiple_receipts_creators($receipt_ids, $clientid);
        		$receipt_creators_print = Receipts::get_multiple_receipt_print_assign_creators($print_receipt_ids, $clientid);
        		$receipt_creators_fax = Receipts::get_multiple_receipt_fax_assign_creators($fax_receipt_ids, $clientid);
        
        		//				print_r($receipt_creators_print);
        		//				print_r($receipt_creators_fax);
        		//				exit;
        		//second todo foreach to append data to master_data
        		$tabname = 'todo';
        		$triggered_by_arr = array();
        		$triggered_by_arr[0] = "";
        		$triggered_by_arr[1] = "";
        
        
        		foreach($todo_array as $k_todo => $v_todo)
        		{
        			if(!in_array($v_todo['id'], $excluded_events[$tabname]))
        			{
        				if($v_todo['record_id'] != '0')
        				{
        					if(($v_todo['triggered_by'] == "newreceipt_1" && !empty($receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']])))
        					{
        						$creator_details = $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_print[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
        					}
        					else if(($v_todo['triggered_by'] == "newreceipt_2"  && !empty($receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']])))
        					{
        						$creator_details = $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['user_title'] . ' ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['last_name'] . ', ' . $todo_users[$receipt_creators_fax[$v_todo['record_id']][$v_todo['user_id']]]['first_name'];
        					}
        					else
        					{
        						$creator_details = '';
        					}
        				}
        
        				if($v_todo['user_id'] > '0')
        				{
        					$user_details = $todo_users[$v_todo['user_id']]['user_title'] . ' ' . $todo_users[$v_todo['user_id']]['last_name'] . ', ' . $todo_users[$v_todo['user_id']]['first_name'];
        				}
        				else
        				{
        					$user_details = '';
        				}
        				$todo_ipids[] = $v_todo['ipid'];
        
        				if($v_todo['triggered_by'] != 'system_medipumps')
        				{
        					if(($v_todo['group_id'] == $groupid && $v_todo['group_id'] != '0') || $v_todo['user_id'] == $userid)
        					{
        						$triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);
        						$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
        
        
        
        
        						if($triggered_by_arr[$key_start][0] == "medacknowledge")
        						{
        							$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
        							$master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
        							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
        							if(strlen($triggered_by_arr[$key_start][1]) > 0){
        								$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
        							}
        							$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
        						}
        
        						elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
        						{
        							$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
        							$master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
        							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
        							$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
        							if(strlen($triggered_by_arr[$key_start][1]) > 0){
        								$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
        							}
        						}
        						else
        						{
        							$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
        							$master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
        							$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
        							$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
        							$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
        						}
        
        						$master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
        						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
        						$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
        						$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
        						$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
        						$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
        						$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
        						$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
        						$master_data[strtotime($due_date)][$key_start]['receipt_creator_user'] = $creator_details;
        						$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
        						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        						$key_start++;
        					}
        				}
        				else if($v_todo['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_todo['group_id'] (koord)
        				{
        					$due_date = date('Y-m-d', strtotime($v_todo['until_date']));
        						
        					$triggered_by_arr[$key_start] = explode("-",$v_todo['triggered_by']);
        						
        					if($triggered_by_arr[$key_start][0] == "medacknowledge")
        					{
        						$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'medacknowledge';
        						$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
        						$master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
        						$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
        						if(strlen($triggered_by_arr[$key_start][1]) > 0){
        							$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = $triggered_by_arr[$key_start][1];
        						}
        					}
        					elseif($triggered_by_arr[$key_start][0] == "pumpmedacknowledge")
        					{
        						$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = 'pumpmedacknowledge';
        						$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '1';
        						$master_data[strtotime($due_date)][$key_start]['medical_change'] = '1';
        						$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
        						if(strlen($triggered_by_arr[$key_start][1]) > 0){
        							$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = $triggered_by_arr[$key_start][1];
        						}
        					}
        					else
        					{
        						$master_data[strtotime($due_date)][$key_start]['triggered_by_info'] = '0';
        						$master_data[strtotime($due_date)][$key_start]['hide_checkbox'] = '0';
        						$master_data[strtotime($due_date)][$key_start]['medical_change'] = '0';
        						$master_data[strtotime($due_date)][$key_start]['drugplan_id'] = '0';
        						$master_data[strtotime($due_date)][$key_start]['cocktail_id'] = '0';
        					}
        
        					$master_data[strtotime($due_date)][$key_start]['alt_id'] = $v_todo['record_id'];
        					$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
        					$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_todo['ipid'];
        					$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_todo['id'];
        					$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_todo['todo'];
        					$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_todo['user_id'];
        					$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_todo['group_id'];
        					$master_data[strtotime($due_date)][$key_start]['todo_user'] = $user_details;
        					$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_todo['triggered_by'];
        					$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        					$key_start++;
        				}
        			}
        		}
        	}
        
        	$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
        	$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
        	$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
        	$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        	$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        	$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
        	$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
        	$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
        	$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
        	$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
        	$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
        	$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
        
        
        	// if super admin check if patient is visible or not
        	if($logininfo->usertype == 'SA')
        	{
        		$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
        	}
        
        	$patients = Doctrine_Query::create()
        	->select($sql)
        	->from('PatientMaster p')
        	->whereIn("p.ipid", $todo_ipids)
        	->leftJoin("p.EpidIpidMapping e")
        	->andWhere('e.clientid = ' . $clientid);
        	$patients_res = $patients->fetchArray();
        	foreach($patients_res as $k_pat_todo => $v_pat_todo)
        	{
        		$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']] = $v_pat_todo;
        		$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_todo['EpidIpidMapping']['epid']);
        		$todo_patients[$v_pat_todo['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_todo['id']);
        	}
        
        
        	//TODO END
        	//SGB XI START
        	$show_to_group_users = false;
        	$event_tabname = 'sgbxi';
        	$sgbxi_events = Doctrine_Query::create()
        	->select("*")
        	->from('DashboardEvents')
        	->where('client_id="' . $clientid . '"')
        	->andWhere('tabname="' . $event_tabname . '" ')
        	->andWhere('isdelete="0"')
        	->andWhere('iscompleted="0"')
        	->orderBy('create_date DESC');
        
        	if($user_type != 'SA')
        	{
        		if($show_to_group_users)
        		{
        
        			if(!in_array($groupid, $coord_groups))
        			{
        				$sgbxi_events->andWhere('triggered_by !="system"');
        			}
        
        			if($groupid > 0)
        			{
        				$sql_group = ' OR group_id = "' . $groupid . '"';
        			}
        			$sgbxi_events->andWhere('user_id IN(' . implode(', ', $current_user_group_asignees) . ') ' . $sql_group . '');
        		}
        		else
        		{
        			$sgbxi_events->andWhere('user_id = "' . $userid . '" ');
        		}
        	}
        
        
        	$sgbxi_events_array = $sgbxi_events->fetchArray();
        
        	$sgbxi_events_ipids[] = '99999999999';
        	if(count($sgbxi_events_array) > 0)
        	{
        		//first event foreach to gather all ipids to avoid 10 second loading as in old events!
        		foreach($sgbxi_events_array as $k_s_d => $v_sgbxi_events_d)
        		{
        			$sgbxi_events_ipids[] = $v_sgbxi_events_d['ipid'];
        		}
        
        
        		//second sgbxi_events foreach to append data to master_data
        		$tabname = 'sgbxi';
        
        		foreach($sgbxi_events_array as $k_sgbxi_events => $v_sgbxi_events)
        		{
        			if(!in_array($v_sgbxi_events['id'], $excluded_events[$tabname]))
        			{
        				if($v_sgbxi_events['user_id'] > '0')
        				{
        					$sgbxi_user_details = $client_users[$v_sgbxi_events['user_id']]['user_title'] . ' ' . $client_users[$v_sgbxi_events['user_id']]['last_name'] . ', ' . $client_users[$v_sgbxi_events['user_id']]['first_name'];
        				}
        				else
        				{
        					$sgbxi_user_details = '';
        				}
        				$sgbxi_events_ipids[] = $v_sgbxi_events['ipid'];
        
        				if($v_sgbxi_events['group_id'] == $groupid || $user_type == 'SA') //show system_medipumps only to $v_sgbxi_events['group_id'] (koord)
        				{
        					$due_date = date('Y-m-d', strtotime($v_sgbxi_events['until_date']));
        					$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
        					$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_sgbxi_events['ipid'];
        					$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_sgbxi_events['id'];
        					$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_sgbxi_events['title'];
        					$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_sgbxi_events['user_id'];
        					$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_sgbxi_events['group_id'];
        					$master_data[strtotime($due_date)][$key_start]['todo_user'] = $sgbxi_user_details;
        					$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_sgbxi_events['triggered_by'];
        					$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        					$key_start++;
        				}
        				else
        				{
        					$due_date = date('Y-m-d', strtotime($v_sgbxi_events['until_date']));
        					$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
        					$master_data[strtotime($due_date)][$key_start]['ipid'] = $v_sgbxi_events['ipid'];
        					$master_data[strtotime($due_date)][$key_start]['event_id'] = $v_sgbxi_events['id'];
        					$master_data[strtotime($due_date)][$key_start]['event_title'] = $v_sgbxi_events['title'];
        					$master_data[strtotime($due_date)][$key_start]['user_id'] = $v_sgbxi_events['user_id'];
        					$master_data[strtotime($due_date)][$key_start]['group_id'] = $v_sgbxi_events['group_id'];
        					$master_data[strtotime($due_date)][$key_start]['todo_user'] = $sgbxi_user_details;
        					$master_data[strtotime($due_date)][$key_start]['triggered_by'] = $v_sgbxi_events['triggered_by'];
        					$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
        					$key_start++;
        				}
        			}
        		}
        	}
        
        	$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as firstname,";
        	$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
        	$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
        	$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        	$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        	$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
        	$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
        	$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
        	$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
        	$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
        	$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
        	$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";
        
        
        	// if super admin check if patient is visible or not
        	if($logininfo->usertype == 'SA')
        	{
        		$sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
        		$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
        	}
        
        	$sgbxi_patients_q = Doctrine_Query::create()
        	->select($sql)
        	->from('PatientMaster p')
        	->whereIn("p.ipid", $sgbxi_events_ipids)
        	->leftJoin("p.EpidIpidMapping e")
        	->andWhere('e.clientid = ' . $clientid);
        	$sgbxi_patients_res = $sgbxi_patients_q->fetchArray();
        	foreach($sgbxi_patients_res as $k_pat_sgbxi_events => $v_pat_sgbxi_events)
        	{
        		$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']] = $v_pat_sgbxi_events;
        		$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['epid'] = strtoupper($v_pat_sgbxi_events['EpidIpidMapping']['epid']);
        		$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['last_name'] = $v_pat_sgbxi_events['last_name'];
        		$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['first_name'] = $v_pat_sgbxi_events['first_name'];
        		$sgbxi_events_patients[$v_pat_sgbxi_events['EpidIpidMapping']['ipid']]['enc_id'] = Pms_Uuid::encrypt($v_pat_sgbxi_events['id']);
        	}
        	//SGB XI  END
							
							
							
			//Patient birthday

			$notifications = new Notifications();
			$user_notification_settings = $notifications->get_notification_settings($userid);


			//excluded evnts
			$clist = Doctrine_Query::create()
			->select("*")
			->from('DashboardActionsDone')
			->where('client = "' . $clientid . '"')
			->andWhere('user = "' . $userid . '"')
			->andWhere("tabname = 'patient_birthday'")
			/* ->andWhere("create_date <= '" . date('Y-m-d H:i:s', time()) . "'") */
			->andWhere("YEAR(create_date) = '" . date('Y', time()) . "'");
			$client_excluded_events = $clist->fetchArray();

			if($client_excluded_events)
			{
				foreach($client_excluded_events as $k_excluded => $v_excluded)
				{
					$excluded_birth[$v_excluded['tabname']][] = $v_excluded['event'];
					//$excluded_birth[$v_excluded['tabname']] = array_unique($excluded_events[$v_excluded['tabname']]); // ISSUE raised  by Flo on 05.04.2018 
					$excluded_birth['excluded_date'][$v_excluded['event']] = $v_excluded['create_date'];
				}
			}

			$sql = "AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .=",AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA' && $clone === false)
			{
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
			}

			if($user_notification_settings[$userid]['dashboard_display_patbirthday'] == 'assigned')
			{
				$fdoc = Doctrine_Query::create()
				->select("*,q.userid, e.epid, e.ipid")
				->from('EpidIpidMapping e')
				->andWhere('e.epid!=""')
				->leftJoin('e.PatientQpaMapping q')
				->where('e.epid = q.epid')
				->andWhere("q.userid =" . $userid)
				->andWhere('e.clientid = ' . $clientid);
				$doc_assigned_patients = $fdoc->fetchArray();


				$asigned_patients[] = '999999999999';
				foreach($doc_assigned_patients as $doc_patient)
				{
					foreach($doc_patient['PatientQpaMapping'] as $k_doc => $v_doc)
					{
						$users_patients[$v_doc['userid']][] = $doc_patient['ipid'];
						$asigned_patients[] = $doc_patient['ipid'];
					}
				}

				//2. Get patients with birthday in next 7 days
				//2.1 Patients asigned to users wich must receive the messajes

				$patient_dasboard = Doctrine_Query::create()
				->select("p.ipid,p.birthd,ep.epid," . $sql)
				->from('PatientMaster as p')
				->where('isdelete = 0')
				->andWhereIn('ipid', $asigned_patients)
				->andWhere('isdischarged = 0')
				->andWhere('isstandby = 0')
				->andWhere('isarchived = 0')
				->andWhere('isstandbydelete = 0')
				->andWhere("date_format( `birthd` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 7 DAY ) , '%m-%d' )")
				->leftJoin('p.EpidIpidMapping ep')
				->andWhere('ep.clientid = ' . $clientid)
				->andWhere('ep.ipid=p.ipid');;
				$patient_dashboard_assigned = $patient_dasboard->fetchArray();
				//print_r($patient_dashboard_assigned);exit;
				foreach($patient_dashboard_assigned as $key_pat => $val_pat)
				{
					$patients_birthdays[$val_pat['ipid']] = date('d.m.Y', strtotime($val_pat['birthd']));
				}

				foreach($patient_dashboard_assigned as $key_pat_birth => $val_pat_birth)
				{
					$patient_birthday = date('d.m.Y', strtotime($val_pat_birth['birthd']));
					$patbirthd_arr = explode(".", $patient_birthday);
					$due_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date("Y")));
					$tabname = 'patient_birthday';
					if(!in_array($val_pat_birth['id'], $excluded_birth[$tabname]))
					{
						//$due_date = date("d.m.Y");
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $val_pat_birth['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($val_pat_birth['EpidIpidMapping']['epid']).' - '.$val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));
						$master_data[strtotime($due_date)][$key_start]['ipid'] = $val_pat_birth['ipid'];
						$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
						$key_start++;
					}
				}
			}
			//print_r($patient_dashboard_assigned);exit;
			//2.2 All patients wich have birtday in next 7 days
			else if($user_notification_settings[$userid]['dashboard_display_patbirthday'] == 'all')
			{
				$patient_dasboard = Doctrine_Query::create()
				->select("p.ipid,p.birthd,ep.epid,ep.clientid," . $sql)
				->from('PatientMaster as p')
				->where('p.isdelete = 0')
				->andWhere('p.isdischarged = 0')
				->andWhere('p.isstandby = 0')
				->andWhere('p.isarchived = 0')
				->andWhere('p.isstandbydelete = 0')
				->andWhere("date_format( `birthd` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 7 DAY ) , '%m-%d' )")
				->leftJoin('p.EpidIpidMapping ep')
				->andWhere('ep.clientid = ' . $clientid)
				->andWhere('ep.ipid=p.ipid');
				$patient_dashboard_all = $patient_dasboard->fetchArray();

				//print_r($patient_dashboard_all)	;exit;

				foreach($patient_dashboard_all as $key_pat_birth => $val_pat_birth)
				{
					$patient_birthday = date('d.m.Y', strtotime($val_pat_birth['birthd']));
					$patbirthd_arr = explode(".", $patient_birthday);
					//print_r($patient_birthday);
					$due_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date("Y")));
					//print_r($due_date);exit;
					$tabname = 'patient_birthday';
					if(!in_array($val_pat_birth['id'], $excluded_birth[$tabname]))
					{
						//$due_date = date("d.m.Y");
						$master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
						$master_data[strtotime($due_date)][$key_start]['event_id'] = $val_pat_birth['id'];
						$master_data[strtotime($due_date)][$key_start]['event_title'] = $val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['event_title_short'] = strtoupper($val_pat_birth['EpidIpidMapping']['epid']).' - '.$val_pat_birth['last_name'] . ', ' . $val_pat_birth['first_name'];
						$master_data[strtotime($due_date)][$key_start]['due_date'] = date("d.m.Y",strtotime($due_date));
						$master_data[strtotime($due_date)][$key_start]['ipid'] = $val_pat_birth['ipid'];
						$master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
						$key_start++;
					}
				}
			}
			//End Patient birthday
			//==================================================================================================
							

			//excluded evnts
			$mi_clist = Doctrine_Query::create()
			->select("*")
			->from('DashboardActionsDone')
			->where('client = "' . $clientid . '"')
			->andWhere('user = "' . $userid . '"')
			->andWhere("tabname = 'medications'")
			->andWhere("YEAR(create_date) = '" . date('Y', time()) . "'");
			$mi_client_excluded_events = $mi_clist->fetchArray();
			
			if($mi_client_excluded_events)
			{
			    foreach($mi_client_excluded_events as $k_excluded => $v_excluded)
			    {
			        $excluded_medication_interval[$v_excluded['tabname']][] = $v_excluded['event'].'_'.$v_excluded['extra'];
			        $excluded_medication_interval[$v_excluded['tabname']] = array_unique($excluded_events[$v_excluded['tabname']]);
			    }
			}
			
			
			
			if(!empty($user_notification_settings[$userid]) && $user_notification_settings[$userid]['medication_interval'] !="none")
			{
			    

			    $sql_mi = "AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			    $sql_mi .=",AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			    $sql_mi .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			    
			    // if super admin check if patient is visible or not
			    if($logininfo->usertype == 'SA' && $clone === false)
			    {
			        $sql_mi .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
			        $sql_mi .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
			        $sql_mi .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
			    }
			    
			    
			    if($user_notification_settings[$userid]['medication_interval'] == 'assigned') {
				    $mi_patquery = Doctrine_Query::create()
				    ->select("e.ipid")
				    ->from('EpidIpidMapping e')
				    ->andWhere('e.epid!=""')
				    ->leftJoin('e.PatientQpaMapping q')
				    ->where('e.epid = q.epid');
				    $mi_patquery->andWhere("q.userid =" . $userid);
				    $mi_patquery->andWhere('e.clientid = ' . $clientid);
				    $mi_patients_array = $mi_patquery->fetchArray();
			    }
			    
			    $mi_assigned_patients[] = "99999999";
			    foreach($mi_patients_array as $k=>$p){
			        $mi_assigned_patients[] = $p['ipid'];
			        // 							        $pdetails[$p['ipid']]
			    }
			    

			    $patient_mi_dasboard = Doctrine_Query::create()
			    ->select("p.ipid,p.birthd," . $sql_mi)
			    ->from('PatientMaster  p')
			    ->where('p.isdelete = 0');
			    if($user_notification_settings[$userid]['medication_interval'] == 'assigned') {
				    $patient_mi_dasboard ->andWhereIn('p.ipid', $mi_assigned_patients);
			    }
			    $patient_mi_dasboard ->andWhere('p.isdischarged = 0')
			    ->andWhere('p.isstandby = 0')
			    ->andWhere('p.isarchived = 0')
			    ->andWhere('p.isstandbydelete = 0')
			    ->leftJoin('p.EpidIpidMapping ep')
			    ->andWhere('ep.clientid=' . $logininfo->clientid);
			    $patient_mi_dashboard_array = $patient_mi_dasboard->fetchArray();
			    
			    
			    foreach($patient_mi_dashboard_array as $k=>$p){
			        $mi_patients[] = $p['ipid'];
			        $pdetails[$p['ipid']] = $p;
			        $pdetails[$p['ipid']]['enc_id'] = Pms_Uuid::encrypt($p['id']);
			    }
			    
			    if(!empty($mi_patients))
			    {
			        $interval = array("before"=>"1","after"=>"7");
			       	$interval_meds = PatientDrugPlan::get_scheduled_medication($mi_patients,$interval);
				    

				    foreach($interval_meds['scheduled_medication_data'] as $miipid => $drugs_array)
				    {
				        foreach($drugs_array as $mid=>$mdata )
				        {
					        $due_date = date("Y-m-d", strtotime($mdata['due_date']));
					        $tabname = 'medications';
					        
					           $event_ident = $mid.''.date('d.m.Y', strtotime($due_date));
                            if(!in_array($event_ident, $excluded_medication_interval[$tabname]))
                            {
// 					            $event_title =  '<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$pdetails[$miipid]['enc_id'].'">'.$pdetails[$miipid]['last_name'] . ', ' . $pdetails[$miipid]['first_name'].'</a> This patient has scheduled medication for this day';
					            $event_title =  '<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$pdetails[$miipid]['enc_id'].'">'.$pdetails[$miipid]['last_name'] . ', ' . $pdetails[$miipid]['first_name'].'</a> - Die Medikation '.$mdata['medication_name'].' muss laut Intervall heute verabreicht werden.';
					            $master_data[strtotime($due_date)][$key_start]['tabname'] = $tabname;
					            $master_data[strtotime($due_date)][$key_start]['event_id'] = $mid;
					            $master_data[strtotime($due_date)][$key_start]['event_title'] = $event_title;
					            $master_data[strtotime($due_date)][$key_start]['event_title_short'] = $pdetails[$miipid]['last_name'] . ', ' . $pdetails[$miipid]['first_name'];
					            $master_data[strtotime($due_date)][$key_start]['due_date'] = date('d.m.Y', strtotime($due_date));;
					            $master_data[strtotime($due_date)] = array_values($master_data[strtotime($due_date)]);
					            $key_start++;
					        }
				        }
				    }
				    
			    }
			    
			}
							
			//End Patient medication_interval
			//==================================================================================================
			
			
			$action_last_label = $dashboard_labels->getActionsLastLabel();
			
			$action_last_label['custom_doctor_event_team'] = $action_last_label['custom_team_event'];

			$labels_f['0'] = $this->view->translate('select');
			foreach($action_last_label as $k_act_label => $v_act_label)
			{
				if($k_act_label != "custom_doctor_event_team")
				{
					$labels_f[$k_act_label] = $v_act_label['name'];
				}
			}
							
		
			if($_REQUEST['label_filter'] && $_REQUEST['label_filter'] != '0' && $_REQUEST['label_filter'] != 'undefined') //0=all
			{				
				foreach($master_data as $k_master_data_date => $v_master_data)
				{
					foreach($v_master_data as $k_data => $v_data)
					{
						if($v_data['tabname'] == $_REQUEST['label_filter'] || ($_REQUEST['label_filter'] == "custom_team_event" && ($v_data['tabname'] == "custom_doctor_event_team" || $v_data['tabname'] == "custom_team_event")))
						{
							$master_data_filtered[$k_master_data_date][$k_data] = $v_data;
						}
					}
				}

				$master_data = array();
				$master_data = $master_data_filtered;
			}



			$grouped_dashbord = "0";
			$ModulePrivileges = $modules->get_client_modules($clientid);
			if($user_notification_settings[$userid]['dashboard_grouped'] == '1' && $ModulePrivileges['156'] == "1" )
			{
				$grouped_dashbord = '1';
			}
			$this->view->grouped_dashbord = $grouped_dashbord;
			
			//LIMIT & SORT MASTER DATA
			$user_dash_limit = $user_c_details[0]['dashboard_limit'];

			
			
			if($_REQUEST['sort_order'] == 'desc')
			{
				krsort($master_data);
			}
			else
			{				
				ksort($master_data);
			}
				
			
			
			if($grouped_dashbord == "0"){
				
				if($user_dash_limit != '0')
				{
					$incr = 1;
					foreach($master_data as $k_tabname => $v_events)
					{
						foreach($v_events as $k_event => $v_event)
						{
	
							if($incr <= $user_dash_limit)
							{
								$master_data_final[$k_tabname][$k_event] = $v_event;
							}
							$incr++;
						}
					}
				}
				else
				{
					$master_data_final = $master_data;
				}
			} else {
				$master_data_final = $master_data;
			}
			
			
			
			/*
			if($_REQUEST['sort_order'] == 'desc')
			{
				krsort($master_data_final);
			}
			else
			{				
				ksort($master_data_final);
			}
			*/			
			$dashdata = array();
			$keydashdata = 1;
							
			// get all ipids
			$all_dash_ipids = array();
			foreach($master_data_final as $day_event=> $events)
			{
				foreach($events as $k_event => $v_event)
				{
					if(isset($v_event['ipid']) && !empty($v_event['ipid'])){
						if(!in_array($v_event['ipid'],$all_dash_ipids)){
							$all_dash_ipids[]	 = $v_event['ipid'];					
						}
					}
				}
			}
			
			$ipid2enc_id = array();
			if(!empty($all_dash_ipids)){
				$idiipid_sql = Doctrine_Query::create()
				->select('id,ipid')
				->from('PatientMaster')
				->whereIn("ipid",$all_dash_ipids);
				$idiipid_array = $idiipid_sql->fetchArray();
				
				
				foreach($idiipid_array as $k => $ipval){
					$ipid2enc_id[$ipval['ipid']] = Pms_Uuid::encrypt($ipval['id']);
				}
			}
			
			foreach($master_data_final as $day_event=> $events)
			{			 		
				foreach($events as $k_event => $v_event)
				{
					$dashdata[$keydashdata]['nr'] = $keydashdata;
					$dashdata[$keydashdata]['label'] =  $action_last_label[$v_event['tabname']]['name'];
					$dashdata[$keydashdata]['color'] =  $action_last_label[$v_event['tabname']]['color'];
					$dashdata[$keydashdata]['font_color'] =  $action_last_label[$v_event['tabname']]['font_color'];
							
					if($v_event['triggered_by'] == 'system_medipumps' && $v_event['tab_name'] == 'todo')
					{									
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];
						$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
					}
					elseif(strlen($v_event['triggered_by']) == 0 && $v_event['tabname'] == 'todo')
					{										
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];	
						$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];	
					}
					elseif(strlen($v_event['triggered_by']) == 0  && $v_event['triggered_by_info'] =="medacknowledge" && $v_event['tabname'] == 'todo')
					{
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_title'];
						$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title'];
					}
					elseif(strlen($v_event['triggered_by']) == 0  && ($v_event['tabname'] == 'team_events' || $v_event['tabname'] == 'custom_doctor_event' ||  $v_event['tabname'] == 'custom_team_event'))
					{
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_type'].$v_event['event_title'];
						$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_type'].$v_event['event_title'];
					}
					elseif($v_event['triggered_by'] == 'teammeeting_completed' && $v_event['tabname'] == 'todo')
					{										
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> <br />'. $v_event['event_title'];
						$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
					}
					else
					{
						$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$v_event['event_title'];
						
						if($v_event['tabname'] == "todo" ){

							if($grouped_dashbord == '0')
							{
								$dashdata[$keydashdata]['column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.'<a style="float: none !important;" href="'.APP_BASE.'patientcourse/patientcourse?id='.$todo_patients[$v_event['ipid']]['enc_id'].'">'.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'].'</a> - '. $v_event['event_title'];
							}
							$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$todo_patients[$v_event['ipid']]['epid']. ' - '.$todo_patients[$v_event['ipid']]['lastname'].', '.$todo_patients[$v_event['ipid']]['firstname'];
						}
						elseif($v_event['tabname'] == "sgbxi" )
						{
							$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$sgbxi_events_patients[$v_event['ipid']]['epid']. ' - '.$sgbxi_events_patients[$v_event['ipid']]['lastname'].', '.$sgbxi_events_patients[$v_event['ipid']]['firstname'];
						}
						elseif(in_array($v_event['tabname'],array("anlage4awl","anlage","patient_birthday")) )
						{
							$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title_short'];
						}
						else{
							$dashdata[$keydashdata]['short_column_title'] = '<div class="description_container">'.$v_event['event_title'];
						}
						
					}

					if(strlen($v_event['todo_user'])>0 && trim($v_event['todo_user']) != ',')
					{
						$dashdata[$keydashdata]['column_title'] .= '<br /><i>Wer '.$v_event['todo_user'].'</i>';
					}
					elseif(strlen($v_event['event_patient'])>0)
					{
						$dashdata[$keydashdata]['column_title'] .= '<i>Patient: '.strtoupper($v_event['event_patient']).'</i>';
						$dashdata[$keydashdata]['short_column_title'] .= '<i>Patient: '.strtoupper($v_event['event_patient']).'</i>';
					}														
					elseif($this->userid != $v_event['todo_user']  && ($v_event['tabname'] == 'team_events' || $v_event['tabname'] == 'custom_doctor_event' ||  $v_event['tabname'] == 'custom_team_event'))
					{
						//gol				
					}
														
					if(($v_event['triggered_by'] == "newreceipt_1" || $v_event['triggered_by'] == 'newreceipt_2') && strlen(trim(rtrim($v_event['receipt_creator_user']))) > '0')
					{
// 						$dashdata[$keydashdata]['column_title'] .= '<br style="clear:both;"/><div class="width:100%;"><i>'.$this->translate('who_todo_receipt').' '.$v_event['receipt_creator_user'].'</i></div>';
						$dashdata[$keydashdata]['column_title'] .= '<br style="clear:both;"/><div class="width:100%;"><i>die '.$v_event['receipt_creator_user'].'</i></div>';
						$dashdata[$keydashdata]['short_column_title'] .= '';
					}
					if($v_event['tabname'] == "anlage" || $v_event['tabname'] == "anlage4awl")
					{
						$dashdata[$keydashdata]['extra'] .= $v_event['extra'];
					}
					
					$dashdata[$keydashdata]['due_date'] = $v_event['due_date'];
					$dashdata[$keydashdata]['user_id'] = $v_event['user_id'];
					$dashdata[$keydashdata]['group_id'] = $v_event['group_id'];			
					$dashdata[$keydashdata]['hide_checkbox'] = $v_event['hide_checkbox'];
					$dashdata[$keydashdata]['tabname'] = $v_event['tabname'];
					$dashdata[$keydashdata]['event_id'] = $v_event['event_id'];
					$dashdata[$keydashdata]['triggered_by'] = $v_event['triggered_by'];
					$dashdata[$keydashdata]['triggered_by_info'] = $v_event['triggered_by_info'];
					$dashdata[$keydashdata]['medical_change'] = $v_event['medical_change'];
					$dashdata[$keydashdata]['ipid'] = $v_event['ipid'];
					$dashdata[$keydashdata]['drugplan_id'] = $v_event['drugplan_id'];
					$dashdata[$keydashdata]['alt_id'] = $v_event['alt_id'];
					$dashdata[$keydashdata]['cocktail_id'] = $v_event['cocktail_id'];
					$keydashdata++;
				}							
			}

			
			if($grouped_dashbord == '1')
			{
				
				foreach($dashdata as $key => $dval){
					$dash_grouped[$dval['ipid']]['by_tabname'][$dval['tabname']][] = $dval;
					$dash_grouped[$dval['ipid']]['kids'][] = $dval;
					$exp_ipids[] = $dval['ipid'];
				}			
				foreach($exp_ipids as $k=>$ipid){
					usort($dash_grouped[$ipid]['kids'], array(new Pms_Sorter('due_date'), "_date_compare"));
				}
			
				
				$incr2pat = 1;
				foreach($dash_grouped as $ipid=>$val_arr){
					
					foreach($val_arr['kids'] as $kk=>$kv){
						
						if($user_dash_limit != '0')
						{
						
							if($incr2pat<= $user_dash_limit) {
								if($kk == 0 ){
									$dash_arr[$ipid] = $kv;
								}
								
								$dash_arr[$ipid]['child_rows'][] = $kv;
								$dash_arr[$ipid]['by_tabname'][$kv['tabname']][] = $kv;
							}
						} 
						else
						{
							
							if($kk == 0 ){
								$dash_arr[$ipid] = $kv;
							}
							
							$dash_arr[$ipid]['child_rows'][] = $kv;
							$dash_arr[$ipid]['by_tabname'][$kv['tabname']][] = $kv;
						}
					}
					$incr2pat++;
				}
			
			
				$full_count = count($dash_arr);
				
				if($limit != "" && $offset != "")
				{
					$dashdatalimit = array_slice($dash_arr, $offset, $limit, true);
					$dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
				}
				else
				{
					$dashdatalimit = $dash_arr;
				}
				
				
				$row_id = 0;
				$link = "";
				$resulted_data = array();

				foreach($dashdatalimit as $report_id =>$mdata)
				{
// 					$link = '';
						
					$resulted_data[$row_id]['nr'] = $mdata['nr'];
					//$resulted_data[$row_id]['action'] = '';
					if(isset($mdata['ipid']) && !empty($mdata['ipid'])){
						$resulted_data[$row_id]['column_title'] = sprintf('<a href="'.APP_BASE.'patientcourse/patientcourse?id=%s">%s</a>',$ipid2enc_id[$mdata['ipid']],$mdata['short_column_title']);
					} else{
						$resulted_data[$row_id]['column_title'] = $mdata['short_column_title'];
					}
// 					foreach ($mdata['by_tabname'] as $tbname=>$values){
// 						$resulted_data[$row_id]['column_title'] .='<br/>('.count($values).') '.$this->view->translate('dashboard_event'.$tbname);
// 					}
					//$resulted_data[$row_id]['due_date'] = '<div class="done_container">'.$mdata['due_date'].'</div>';
// 					$resulted_data[$row_id]['child_rows'] = $mdata['child_rows'];
					
					
					$child_row_id = 0;
					foreach($mdata['child_rows'] as $ch_id =>$child_data)
					{
						$link = '%s ';
						$resulted_data[$row_id]['child_rows'][$child_row_id]['nr'] = sprintf($link,$child_data['nr']);
						$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] = '<div id="preview-label" class="dashboard_label" style="float: left;background:'.$child_data['color'].'"><span style="width: 20px; float:left;">';
						if($child_data['hide_checkbox'] != '1')
						{
							if($child_data['tabname'] == 'todo')
							{
								if($user_type == 'SA' || $user_type == 'CA' || $userid == $child_data['user_id'] || $groupid == $child_data['group_id'] || in_array($userid, $groups2users[$groupid]))
								{
									$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<input type="checkbox" name="select_done" value="1" class="done_event" id="done_event_'.$child_data["nr"].'" rel="'.$child_data["nr"].'" /></span>';
								}
							}
							elseif($child_data['tabname'] == 'medications'){
								$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= ' </span>';
							}
							else
							{
								$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<input type="checkbox" name="select_done" value="1" class="done_event" id="done_event_'.$child_data["nr"].'" rel="'.$child_data["nr"].'" /></span>';
							}
							
							if($child_data['tabname'] == 'anlage' || $child_data['tabname'] == 'anlage4awl' )
							{
								$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<input type="hidden" name="extra" value="'.$child_data["extra"].'" class="" id="event_extra_'.$child_data["nr"].'" rel="'.$child_data["nr"].'" /></span>';
							}
							
						}
						else
						{
							$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '</span>';
						}
						
						
						
						$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<span id="preview-font" style="color:'.$child_data['font_color'].'">'.$child_data['label'].'</span></div>
														 <input type="hidden" id="event_done_'.$child_data['nr'].'" value="'.$child_data['event_id'].'" style="width:2px!important" />
														 <input type="hidden" id="tabname_'.$child_data['nr'].'" value="'.$child_data['tabname'].'" style="width:2px!important"/>
														 <input type="hidden" id="done_date_'.$child_data['nr'].'" value="'.$child_data['due_date'].'"  style="width:2px!important"/>
														 <input type="hidden" id="completecomment_'.$child_data['nr'].'" name="completecomment_'.$child_data['nr'].'" value="" />';
						$resulted_data[$row_id]['child_rows'][$child_row_id]['action'] .= '<div class="loading_div" id="loading_div_'.$child_data["nr"].'" style="display: none;">'.$this->view->translate('loadingpleasewait').'</div>';
						
						$resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] = $child_data['column_title'];
						if(strlen($child_data['triggered_by']) != 0 && $child_data['triggered_by_info'] =="medacknowledge" && $child_data['tabname'] == 'todo')
						{
							if($child_data['medical_change'] == '1' && $this->view->approval_rights =="1")
							{
								$resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] .= '<br style="clear:both;"/><button id="med_approv_'.$child_data["nr"].'" class="med_approve_rights approvem" data-row_id="'.$child_data["nr"].'" data-todoid="'.$child_data['event_id'].'" data-action="approve" data-patid = "'.$todo_patients[$child_data['ipid']]['enc_id']
								.'" data-recordid="'.$child_data['drugplan_id'].'" data-alt_id="'.$child_data['alt_id'].'">'.$this->view->translate("Approve").'</button><button id="med_decl_'.$child_data["nr"].'" class="med_approve_rights denym"   data-row_id="'.$child_data["nr"]
								.'"	data-todoid="'.$child_data['event_id'].'" data-action="decline"  data-patid = "'.$todo_patients[$child_data['ipid']]['enc_id']
								.'" data-recordid="'.$child_data['drugplan_id'].'" data-alt_id="'.$child_data['alt_id'].'">'.$this->view->translate("Decline").'</button></div>';
							}
						}
						if(strlen($child_data['triggered_by']) != 0 && $child_data['triggered_by_info'] =="pumpmedacknowledge" && $child_data['tabname'] == 'todo')
						{
							if($child_data['medical_change'] == '1' && $this->view->approval_rights =="1")
							{
								$resulted_data[$row_id]['child_rows'][$child_row_id]['column_title'] .='<br style="clear:both;"/><button id="pump_approv_'.$child_data["nr"].'" class="pump_med_approve_rights approvem" data-row_id="'.$child_data["nr"].'" data-todoid="'.$child_data['event_id']
								.'" data-action="approve" data-patid = "'.$todo_patients[$child_data['ipid']]['enc_id'].'" data-recordid="'.$child_data['cocktail_id'].'" data-alt_id="'.$child_data['alt_id']
								.'">'.$this->view->translate("Approve").'</button><button id="pump_decl_'.$child_data["nr"].'" class="pump_med_approve_rights denym"   data-row_id="'.$child_data["nr"].'"   data-todoid="'.$child_data['event_id']
								.'" data-action="decline"  data-patid = "'.$todo_patients[$child_data['ipid']]['enc_id'].'" data-recordid="'.$child_data['cocktail_id'].'" data-alt_id="'.$child_data['alt_id'].'">'
										.$this->view->translate("Decline").'</button></div>';
							}
						}
						
						$resulted_data[$row_id]['child_rows'][$child_row_id]['due_date'] = '<div class="done_container">'.$child_data['due_date'].'</div>';
						
						$child_row_id++;
					}
					
					$row_id++;
				}
				
			} else {
				
				$full_count = count($dashdata);
				if($limit != "" && $offset != "")
				{
					$dashdatalimit = array_slice($dashdata, $offset, $limit, true);
					$dashdatalimit = Pms_CommonData::array_stripslashes($dashdatalimit);
				}
				else
				{
					$dashdatalimit = $dashdata;
				}
				//TODO-2033 - commented by Ancuta 24.01.2019
				//$dashdatalimit = $dashdata;
				//--
				
				$row_id = 0;
				$link = "";
				$resulted_data = array();
				foreach($dashdatalimit as $report_id =>$mdata)
				{
					$link = '%s ';
					$resulted_data[$row_id]['nr'] = sprintf($link,$mdata['nr']);
					$resulted_data[$row_id]['action'] = '<div id="preview-label" class="dashboard_label" style="float: left;background:'.$mdata['color'].'"><span style="width: 20px; float:left;">';
					if($mdata['hide_checkbox'] != '1')
					{
						if($mdata['tabname'] == 'todo')
						{
							if($user_type == 'SA' || $user_type == 'CA' || $userid == $mdata['user_id'] || $groupid == $mdata['group_id'] || in_array($userid, $groups2users[$groupid]))
							{
								$resulted_data[$row_id]['action'] .= '<input type="checkbox" name="select_done" value="1" class="done_event" id="done_event_'.$mdata["nr"].'" rel="'.$mdata["nr"].'" /></span>';
							}
						}
						elseif($mdata['tabname'] == 'medications'){
							$resulted_data[$row_id]['action'] .= ' </span>';										
						}
						else 
						{
							$resulted_data[$row_id]['action'] .= '<input type="checkbox" name="select_done" value="1" class="done_event" id="done_event_'.$mdata["nr"].'" rel="'.$mdata["nr"].'" /></span>';										
						}
	
						if($mdata['tabname'] == 'anlage' || $mdata['tabname'] == 'anlage4awl' )
						{
							$resulted_data[$row_id]['action'] .= '<input type="hidden" name="extra" value="'.$mdata["extra"].'" class="" id="event_extra_'.$mdata["nr"].'" rel="'.$mdata["nr"].'" /></span>';										
						}
						
					}
					else 
					{
						$resulted_data[$row_id]['action'] .= '</span>';
					}
	
					
												
					$resulted_data[$row_id]['action'] .= '<span id="preview-font" style="color:'.$mdata['font_color'].'">'.$mdata['label'].'</span></div>
														 <input type="hidden" id="event_done_'.$mdata['nr'].'" value="'.$mdata['event_id'].'" style="width:2px!important" />
														 <input type="hidden" id="tabname_'.$mdata['nr'].'" value="'.$mdata['tabname'].'" style="width:2px!important"/>
														 <input type="hidden" id="done_date_'.$mdata['nr'].'" value="'.$mdata['due_date'].'"  style="width:2px!important"/>
														 <input type="hidden" id="completecomment_'.$mdata['nr'].'" name="completecomment_'.$mdata['nr'].'" value="" />';								
					$resulted_data[$row_id]['action'] .= '<div class="loading_div" id="loading_div_'.$mdata["nr"].'" style="display: none;">'.$this->view->translate('loadingpleasewait').'</div>';			
									
					$resulted_data[$row_id]['column_title'] = $mdata['column_title'];
					if(strlen($mdata['triggered_by']) != 0 && $mdata['triggered_by_info'] =="medacknowledge" && $mdata['tabname'] == 'todo')
					{
						if($mdata['medical_change'] == '1' && $this->view->approval_rights =="1")
						{
							$resulted_data[$row_id]['column_title'] .= '<br style="clear:both;"/><button id="med_approv_'.$mdata["nr"].'" class="med_approve_rights approvem" data-row_id="'.$mdata["nr"].'" data-todoid="'.$mdata['event_id'].'" data-action="approve" data-patid = "'.$todo_patients[$mdata['ipid']]['enc_id']
																	.'" data-recordid="'.$mdata['drugplan_id'].'" data-alt_id="'.$mdata['alt_id'].'">'.$this->view->translate("Approve").'</button><button id="med_decl_'.$mdata["nr"].'" class="med_approve_rights denym"   data-row_id="'.$mdata["nr"]
																	.'"	data-todoid="'.$mdata['event_id'].'" data-action="decline"  data-patid = "'.$todo_patients[$mdata['ipid']]['enc_id']
																	.'" data-recordid="'.$mdata['drugplan_id'].'" data-alt_id="'.$mdata['alt_id'].'">'.$this->view->translate("Decline").'</button></div>';																																											
						}
					}																					
					if(strlen($mdata['triggered_by']) != 0 && $mdata['triggered_by_info'] =="pumpmedacknowledge" && $mdata['tabname'] == 'todo')
					{
						if($mdata['medical_change'] == '1' && $this->view->approval_rights =="1")
							{
							$resulted_data[$row_id]['column_title'] .='<br style="clear:both;"/><button id="pump_approv_'.$mdata["nr"].'" class="pump_med_approve_rights approvem" data-row_id="'.$mdata["nr"].'" data-todoid="'.$mdata['event_id']
											.'" data-action="approve" data-patid = "'.$todo_patients[$mdata['ipid']]['enc_id'].'" data-recordid="'.$mdata['cocktail_id'].'" data-alt_id="'.$mdata['alt_id']
											.'">'.$this->view->translate("Approve").'</button><button id="pump_decl_'.$mdata["nr"].'" class="pump_med_approve_rights denym"   data-row_id="'.$mdata["nr"].'"   data-todoid="'.$mdata['event_id']
											.'" data-action="decline"  data-patid = "'.$todo_patients[$mdata['ipid']]['enc_id'].'" data-recordid="'.$mdata['cocktail_id'].'" data-alt_id="'.$mdata['alt_id'].'">'
											.$this->view->translate("Decline").'</button></div>';
							}
						}			
																											
						$resulted_data[$row_id]['due_date'] = '<div class="done_container">'.$mdata['due_date'].'</div>';
						
						$row_id++;
					}
				}
				
// 				print_r($resulted_data); exit;
				//var_dump($resulted_data); exit;
				$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
				$response['recordsTotal'] = $full_count;
				$response['recordsFiltered'] = $full_count; // ??
				$response['data'] = $resulted_data;
				
			/*	$sort_arr = array('asc' => $this->view->translate('asc_sort'), 'desc' => $this->view->translate('desc_sort'));
				$this->view->date_sort = $this->view->formSelect("date_sort", $_REQUEST['sort_order'], '', $sort_arr);
				$this->view->label_filter = $this->view->formSelect("label_filter", $_REQUEST['label_filter'], '', $labels_f);
				$this->view->dasboard_events = $master_data_final;
				$this->view->action_label = $action_last_label;
				$this->view->todo_patients = $todo_patients;
				$this->view->sgbxi_events_patients = $sgbxi_events_patients;*/
					
				header("Content-type: application/json; charset=UTF-8");
					
				echo json_encode($response);
				exit;
				/* ------------ BOX - "User Dashboard" END---------------- */
			}
			
			

			

			
			/**
			 * @author @cla
			 */
			public function wallnewsAction()
			{
			    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('__action')) {
			    
			        $responsArr = [];
			    
			        switch ($this->getRequest()->getParam('__action')) {
			    
			            case "addWallNews":
			    
			                $responsArr = $this->_wallnews_addWallNews($this->getRequest()->getPost());
			                 
			                break;
			    
			            case "deleteWallNews":
			    
			                $responsArr = $this->_wallnews_deleteWallNews($this->getRequest()->getPost());
			                 
			                break;
			                
			            case "fetchWallNewsList":
			    
			                $responsArr = $this->_wallnews_fetchWallNewsList($this->getRequest()->getPost());
			                 
			                break;
			        }
			    
			        Zend_Json::$useBuiltinEncoderDecoder = true;
			        $responsJson =  Zend_Json::encode($responsArr);
			         
			        $this->getResponse()
			        ->setHeader('Content-Type', 'application/json')
			        ->setBody($responsJson)
			        ->sendResponse();
			    
			        exit; //for readability
			    }
			    
			    $users_patients =  $this->_wallnews_usersPatients();
			    
			    $this->view->users_patients = array_merge(['0' => $this->translate('Generic WallNews')], array_column($users_patients, 'nice_name', 'enc_id'));
			    
			    
			}
			
			
			/**
			 *
			 * @author @cla
			 *
			 * @param array $data
			 * @return multitype:boolean string
			 */
			private function _wallnews_deleteWallNews($data)
			{
			    $result = ['success' => false, 'message' => 'general error delete'];
			    	
			    if (empty($data['wallNews'])) {
			        return $result; //nothing to save
			    }
			    	
			    if ( ! empty($data['wallNews']['id'])) {
			        	
			        $deleteResult = false;
			        if ($wallNews_Obj = WallnewsTable::getInstance()->findOneByIdAndUserid($data['wallNews']['id'], $this->logininfo->userid)) {
			            $deleteResult = $wallNews_Obj->delete();
			        }
			        	
			        $result = [
			            'success' => (bool)$deleteResult,
			            'message' => 'ok',
			        ];
			    }
			    	
			    return $result;
			}
			
			
			
			/**
			 * @author @cla
			 *
			 * @param array $data
			 * @return multitype:boolean string |multitype:boolean string NULL
			 */
			private function _wallnews_addWallNews($data)
			{
			    $result = ['success' => false, 'message' => 'general error'];
			    	
			    if (empty($data['wallNews'])) {
			        return $result; //nothing to save
			    }
			    	
			    	
			    $id = ! empty($data['wallNews']['id']) ? ($data['wallNews']['id']) : null;
			    	
			    $rowData = [
			        'userid'      => $this->logininfo->userid,
			        'clientid'    => $this->logininfo->clientid,
			        	
			        'ipid'        => null,
			        	
			        'news_content'=> $data['wallNews']['content'],
			        'news_date'   => new Doctrine_Expression('CURDATE()'),
			    ];
			    	
			    	
			    if ( ! empty($data['wallNews']['patient'])) {
			        //this is about a specific patient, validate user is allowed to patient
			        	
			        $dec_id = Pms_Uuid::decrypt($data['wallNews']['patient']);
			        	
			        $ipid = $dec_id ? Pms_CommonData::getIpid($dec_id) : null;
			        	
			        $user_patients = PatientUsers::getUserPatients($this->logininfo->userid , true);
			        	
			        	
			        if (is_null($ipid)
			            || empty($user_patients)
			            || ($user_patients['bypass'] !== true && ! in_array($ipid, $user_patients['patients'])))
			        {
			            return $result; //if user has no visibility, bail out
			        }
			        	
			        $rowData['ipid'] = $ipid;
			    }
			    	
			    	
			    //you are allowed to add
			    $wallNews_Obj = WallnewsTable::getInstance()->findOrCreateOneBy(['id', 'userid'], [$id, $this->logininfo->userid], $rowData);
			    	
			    $result = [
			        'success'         => true,
			        'message'         => 'ok',
			        '_id'             => $wallNews_Obj->id,
			        'news_date'       => $wallNews_Obj->news_date,
			        'user_nice_name'  => $this->logininfo->loguname,
			        'userid'          => $this->logininfo->userid,
			        'patientid'       => $data['wallNews']['patient'],
			        '_debug'  => APPLICATION_ENV == 'development' ? $wallNews_Obj->toArray() : null,
			    ];
			    	
			    	
			    return $result;
			    	
			}
			
			/**
			 * @author @cla
			 */
			private function _wallnews_fetchWallNewsList()
			{
			    $users_patients = $this->_wallnews_usersPatients();
			    
			    $ipids_as_array = ! empty($users_patients) ? array_column($users_patients, 'ipid') : [];
		        
		        $wallnews_dql_params = ! empty($ipids_as_array) ? array_merge([$this->logininfo->clientid], $ipids_as_array) : [$this->logininfo->clientid];
		        
		        //count wallnews
		        $wallnewsCountQ = Doctrine_Query::create()
		        ->select("count(*) as cnt")
		        ->from('Wallnews')
		        ->where('clientid = ?')
		        ->andWhere('(ipid IS NULL ' . ( ! empty($ipids_as_array) ? " OR ipid IN (". implode(', ', array_fill(0, count($ipids_as_array), "?")) . ")" : "") . ")")
		        ;
		        if (null !== ($limitLast14Days =  $this->getRequest()->getParam('limitLast14Days')) && $limitLast14Days) {
		            $wallnewsCountQ->andWhere("news_date >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)");
		        }
		        
		        $wallnewsCount = $wallnewsCountQ->fetchOne($wallnews_dql_params, Doctrine_Core::HYDRATE_ARRAY);
		        
		        $wallnewsCount = isset($wallnewsCount['cnt']) ? $wallnewsCount['cnt'] : 0;
		        
		        
		        $wallnews = null;
		        
		        if ($wallnewsCount > 0) {
		            
		            //get wallnews
		            $wallnews_dql = "clientid = ? ";
		            $wallnews_dql .= " AND (ipid IS NULL" . ( ! empty($ipids_as_array) ? " OR ipid IN (". implode(', ', array_fill(0, count($ipids_as_array), "?")) . ")" : "") . ")";
		            if (null !== ($limitLast14Days =  $this->getRequest()->getParam('limitLast14Days')) && $limitLast14Days) {
                        $wallnews_dql .= " AND news_date >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)";
		            }
		            
		            
		            if (null !== ($limit =  $this->getRequest()->getParam('iDisplayLength')) && $limit > 0) {
		                $wallnews_dql .= " LIMIT " . (int)$limit;
		                
		                if (null !== ($offset =  $this->getRequest()->getParam('iDisplayStart')) && $offset > 0) {
		                    $wallnews_dql .= " OFFSET " . (int)$limit;
		                }
		            }
		            
		            
		            $wallnews_dql .= " ORDER BY news_date DESC, id DESC";
		            
		            $wallnews = WallnewsTable::getInstance()->findByDql($wallnews_dql, $wallnews_dql_params, Doctrine_Core::HYDRATE_ARRAY);
		             
		        }
		        
                
                if ( ! empty($wallnews)) {
                    
                    $users = array_column($wallnews, 'userid');
                    $users = User::getUsersDetails($users);
                    User::beautifyName($users);
        
                    $users_patients_key_ipid =  array_column($users_patients, 'nice_name', 'ipid');
                    $patients_enc_id_key_ipid =  array_column($users_patients, 'enc_id', 'ipid');
                    array_walk($wallnews, function(&$row) use ($users, $users_patients_key_ipid, $patients_enc_id_key_ipid) {
                        $row['user_nice_name'] = $users[$row['userid']]['nice_name'];
                        
                        $row['patient_nice_name'] = '';
                        $row['patient_id'] = '';
                        
                        if ( ! empty($row['ipid'])) {
                            $row['patient_nice_name'] = $users_patients_key_ipid[$row['ipid']];
                            $row['patient_id'] = $patients_enc_id_key_ipid[$row['ipid']];
                        }
                        
                        //filter keys to not send to browser unwanted
                        $row = array_intersect_key($row, array_flip([
                            'id',
                            'userid',
                            'user_nice_name',
                            'news_date',
                            'patient_nice_name',
                            'patient_id',
                            'news_content'
                        ]));

                    });
                    
                }
			    
                 

                $response = array();
                $response['draw'] = (int)$this->getRequest()->getParam('draw'); //? get the sent draw from data table
                $response['recordsTotal'] = $wallnewsCount;
                $response['recordsFiltered'] = $wallnewsCount;
                $response['data'] = $wallnews;
        
			    
			    return $response;
			    
			    
			}
			
			/**
			 * it will return a simple array with [encoded_patientMaster_Id => patient nice_name_epid]
			 * it was made to populate in view->formSelect()
			 * Deprecated since 15.01.2019 - Ancuta
			 */
			private function _wallnews_usersPatients_190115()
			{

			    $p_users = new PatientUsers();
			    $user_patients = $p_users->getUserPatients($this->logininfo->userid); //get user's patients by permission
			     
			    if ($this->logininfo->usertype != 'SA')  {
			    
			        $epidarray = Doctrine_Query::create()
			        ->select('epid')
			        ->from('PatientQpaMapping')
			        ->where('userid = ?', $this->logininfo->userid)
			        ->fetchArray();
			    
			    } elseif ($this->logininfo->clientid > 0) {
			         
			        $epidarray = Doctrine_Query::create()
			        ->select('epid')
			        ->from('PatientQpaMapping')
			        ->where('clientid = ?', $this->logininfo->clientid)
			        ->fetchArray();
			         
			    } else {
			         
			        $epidarray = [];
			    }
			    
			    $epidval = array_column($epidarray, 'epid');
			     
			    $ipids_as_array = [];
			     
			    if ( ! empty($epidval)) {
			    
			        $ipidarray = Doctrine_Query::create()
			        ->select('e.ipid')
			        ->from('EpidIpidMapping e')
			        ->whereIn("e.epid in (" . $epidval . ")")
			        ->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
			        ->fetchArray();
			    
			        $ipids_as_array = array_column($ipidarray, 'ipid');
			    }
			    
			    $users_patients = [];
			    
			    if ( ! empty($ipids_as_array)) {
			         
			        // if super admin check if patient is visible or not
			        if ($logininfo->usertype == 'SA') {
			            $sql = "pm.id, pm.ipid, eim.epid";
			            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name";
			            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name";
			            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name";
			        } else {
			            $sql = "pm.id, pm.ipid, eim.epid";
			            $sql .= ", AES_DECRYPT(pm.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			            $sql .= ", AES_DECRYPT(pm.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			            $sql .= ", AES_DECRYPT(pm.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			        }
			        $users_patients = Doctrine_Query::create()
			        ->select($sql)
			        ->from('PatientMaster pm')
			        ->leftJoin("pm.EpidIpidMapping eim")
			        ->whereIn('pm.ipid', $ipids_as_array)
			        ->andWhere("pm.isdelete = 0")
			        ->andWhere("pm.isstandby = 0 ")
			        ->andWhere("pm.isstandbydelete = 0 ")
			        ->orderBy('pm.id DESC')
			        ->fetchArray();
			         
			        PatientMaster::beautifyName($users_patients);
			        array_walk($users_patients, function(&$row) {$row['enc_id'] = Pms_Uuid::encrypt($row['id']);});
			    
			    }
			    
			    return $users_patients;
			}
			
			private function _wallnews_usersPatients()
			{

			    $p_users = new PatientUsers();
			    $user_patients = $p_users->getUserPatients($this->logininfo->userid); //get user's patients by permission

			    $users_patients = [];
			    
			         
		        // if super admin check if patient is visible or not
		        if ($logininfo->usertype == 'SA') {
		            $sql = "pm.id, pm.ipid, eim.epid";
		            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name";
		            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name";
		            $sql .= ", IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(pm.last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name";
		        } else {
		            $sql = "pm.id, pm.ipid, eim.epid";
		            $sql .= ", AES_DECRYPT(pm.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
		            $sql .= ", AES_DECRYPT(pm.middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
		            $sql .= ", AES_DECRYPT(pm.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
		        }
                //TODO-2782 Ancuta - added isarchived = 0 condition
		        $users_patients = Doctrine_Query::create()
		        ->select($sql)
		        ->from('PatientMaster pm')
		        ->leftJoin("pm.EpidIpidMapping eim")
		        ->where('pm.ipid IN (' . $user_patients['patients_str'] . ')')
		        ->andWhere("pm.isdelete = 0")
		        ->andWhere("pm.isarchived = 0")
		        ->orderBy('pm.id DESC')
		        ->fetchArray();
		         
		        PatientMaster::beautifyName($users_patients);
		        array_walk($users_patients, function(&$row) {$row['enc_id'] = Pms_Uuid::encrypt($row['id']);});
		    
			    
			    return $users_patients;
			}
	
			
			
			/**
			 * ISPC-2440 
			 * @author Loredana
			 * 05.09.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
			 */
			
			public function lastcontactsAction() {
			    $this->_helper->layout->setLayout('layout_ajax');
			    
			    $logininfo = new Zend_Session_Namespace('Login_Info');
			    $hidemagic = Zend_Registry::get('hidemagic');
			    $clientid = $logininfo->clientid;
			    $userid = $logininfo->userid;
			    		    
			    $p_users = new PatientUsers();
			    $user_patients = $p_users->getUserPatients($logininfo->userid); //get user's patients by permission
			   
			    
			    // get all user details 
			    $user = Doctrine_Query::create()
			    ->select("*")
			    ->from('User')
			    ->where('clientid = ' . $logininfo->clientid . ' or usertype="SA"')
			    ->andWhere('isactive=0 and isdelete = 0 and no10contactsbox = 0')
			    ->orderBy('last_name ASC');
			    $userarray = $user->fetchArray();
			    
			    $comma = ",";
			    $usercomma = "'0'";
			    $username = array();
			    if(count($userarray) > 0)
			    {
			        foreach($userarray as $key => $valu)
			        {
			            
			            $username[$valu['id']] =  $valu['user_title'] . ' ' . $valu['last_name'] . ', ' . $valu['first_name']; 
			            $usercomma .= $comma . "'" . $valu['id'] . "'";
			            $comma = ",";
			        }
			    }
			    
			    /* ----------------- Get Contact form types -------------------- */
			    $types = Doctrine_Query::create()
			    ->select('*')
			    ->from('FormTypes')
			    ->where('clientid =?', $clientid)
			    ->andWhere('isdelete =?','0');
			    $types_res = $types->fetchArray();
			    
			    $form_type = array();
			    $form_type_ids = array();
			    foreach($types_res as $k_ft => $v_ft)
			    {
			        $form_type[$v_ft['id']] = $v_ft['name'];
			        $form_type_ids[] = $v_ft['id']; ///  // This is  the case  when all =1
			    }
			    $this->view->form_typess = $form_type;
			    $client_form_type_ids = $form_type_ids;
			    
			    // This is also the case  when all =1
			    $shortcuts = array('U','V','XT');  // prima data cand intri  sa fie toate
			    
			    $ust = new UserSettings();
			    $ustarr = $ust->getUserSettings($userid);
			    			    
			    $period = array();
			    $checked_all = '';
			    
			    if(!empty($ustarr['entries_last_xx_days'])) {
			        $xx_days = $ustarr['entries_last_xx_days'];
			        $period['start'] = date('Y-m-d H:i:s', strtotime("-".$xx_days." days"));			        
			    } 
			    /* else {
 			        $period['start'] = date('Y-m-d H:i:s', strtotime("-2 days"));
 			       // $checked_all = 'nu';
			    } */
			    //$period['end'] =  date('Y-m-d H:i:s');
			    
			    // ############################################################
			    // FILTERS
			    // ############################################################
			    
			    
			    // get the user filters if is saved - ISPC-2440 Lore 12.03.2020
			    $all_sh_filter = 1;
			    $all_time_filter = 1;
			    $times_sh_filter = array();
			    $modules = new Modules();
			    $total_display = 0;
			    if( $modules->checkModulePrivileges("222", $clientid) ){
			        
			        $m_user_filters = new UserLastContactsFilters();
			        $user_filter_q = $m_user_filters->get_user_filter();
			        
			        if(!empty($user_filter_q)){
			            
			            $total_display = 1;
			            $shortcuts = array();
			            $form_type_ids = array();
			            
			            foreach($user_filter_q as $key=> $valfilter){
			                
			                if($valfilter['shortcut'] == '12h' || $valfilter['shortcut'] == '24h' ){
			                    $all_time_filter = 0;
			                    
			                    if($valfilter['shortcut'] == '12h' ){
			                        $period['start'] = date('Y-m-d H:i:s', strtotime("-12 hours"));
			                        $times_sh_filter[] =  $valfilter['shortcut'];
			                        $checked_all = 'nu';
			                    }
			                    if($valfilter['shortcut'] == '24h' ){
			                        $period['start'] = date('Y-m-d H:i:s', strtotime("-24 hours"));
			                        $times_sh_filter[] =  $valfilter['shortcut'];
			                        $checked_all = 'nu';
			                    }
			                } else {
			                    
								$all_sh_filter = 0;

			                    if($valfilter['shortcut'] == 'U' || $valfilter['shortcut'] == 'V' || $valfilter['shortcut'] == 'XT'){
			                        $shortcuts[] =  $valfilter['shortcut'];
			                    } else {
			                        $form_type_ids[] =  $valfilter['shortcut'] ;
			                    }
			                }
			            }
			        }
			    }
		        $this->view->shortcuts_sh_filter = $shortcuts ;
		        $this->view->times_sh_filter = $times_sh_filter ;
		        $this->view->form_type_sh_filter = $form_type_ids ;
		        $this->view->all_sh_filter = $all_sh_filter ;
		        $this->view->all_time_filter = $all_time_filter ;
			    //. now i have the user filters if it was saved - ISPC-2440 Lore 12.03.2020
			    
			    
			    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			        $post_data = $_POST['filters_data'];
			        parse_str($_POST['filters_data'],$post_data);
			        
			        if(!isset($post_data['actions']) || $post_data['actions'] == "0"){
			            $shortcuts = $post_data['shortcuts'];
			        }
			        
			        if(isset($post_data['form_types']) ){
			            $form_type_ids=$post_data['form_types'];
			        }elseif(!isset($post_data['actions']) || $post_data['actions'] == "0"){
			             $form_type_ids = array();
			        }else {
 			            $form_type_ids = $client_form_type_ids;
			        }
			        
			        
                    if($post_data['time_period'] == '2'){
			            $period['start'] = date('Y-m-d H:i:s', strtotime("-12 hours"));
			            $checked_all = 'nu';
			            
			        } elseif($post_data['time_period'] == '3'){
			            $period['start'] = date('Y-m-d H:i:s', strtotime("-24 hours"));	
			            $checked_all = 'nu';
			        } 
			        
			    }
			    //dd($form_type_ids);
			    $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			    $this->view->order = $orderarray['DESC'];
			    
			    $sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			    $sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			    $sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			    $sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			    $sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			    $sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
			    $sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
			    $sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
			    $sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
			    $sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
			    $sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
			    $sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
			    
			    // if super admin check if patient is visible or not
			    if($logininfo->usertype == 'SA')
			    {
			        $sql = "*,";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
			        $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			    }

			    
			    $clientarr = Pms_CommonData::getClientData($logininfo->clientid);
			    
			    $patient = Doctrine_Query::create()
			    ->select('count(*)')
			    ->from('PatientMaster pm')
			    ->andWhere('isdelete = 0 and last_update_user in(' . $usercomma . ')')
			    ->leftJoin('pm.EpidIpidMapping ep')
			    ->andWhere('ep.clientid=' . $logininfo->clientid)
			    ->andWhere('ep.ipid=pm.ipid')
			    ->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')');

			    if(!empty($checked_all)){
			        $patient->andWhere('pm.last_update >= ?',$period['start']);
			    }
			    $patient->orderBy('pm.last_update DESC');
			    
			    $maxcontact = $clientarr[0]['maxcontact'];
			    
			    if(!empty($ustarr['last_xx_entries']))
			    {
			        $limit = $ustarr['last_xx_entries'];
			    }
			    else
			    {
			        $limit = 10;
			        if($maxcontact > 0)
			        {
			            $limit = $maxcontact;
			        }
			    }
			    
			    $patient->select($sql);
			    if($logininfo->hospiz == 1)
			    {
			        $patient->where('ishospiz = 1');
			    }
			    
			    $patient->orderBy('last_update DESC');
			    $patient->limit($limit);
			    $patient->offset($_GET['pgno'] * $limit);
			    $patientlimit = $patient->fetchArray();
			    
			    $patientDetails = array();
			    foreach($patientlimit as $ipid => $p_details)
			    {
			        $patientDetails [$p_details ['ipid']] ['ipid'] = $p_details ['ipid'];
			        $patientDetails [$p_details ['ipid']] ['lastname'] = $p_details ['last_name'];
			        $patientDetails [$p_details ['ipid']] ['firstname'] = $p_details ['first_name'];
			        $patientDetails [$p_details ['ipid']] ['last_update_user'] = $p_details ['last_update_user'];
			        $patientDetails [$p_details ['ipid']] ['last_update'] = date('d.m.Y', strtotime($p_details ['last_update']));
			        $patientDetails [$p_details ['ipid']] ['last_update_time'] = date('H:i', strtotime($p_details ['last_update']));
			        
			    }
			    
			    //get last update users and patient ipids
			    $last_update_users = array();  
			    $patient_ipids = array();     
			    
			    foreach($patientlimit as $k_pat_limit => $v_pat_limit)
			    {
			        $last_update_users[] = $v_pat_limit['last_update_user'];
			        $patient_ipids[] = $v_pat_limit['ipid'];
			    }
			    
			    $last_update_users = array_values(array_unique($last_update_users));
			    $patient_ipids = array_values(array_unique($patient_ipids));
			    
			    

			    
			    
			    $all_sh = array();
			    if(!empty($shortcuts) && !empty($patient_ipids))
			    { 
    			    $sql_courses = array();
    			    if($shortcuts && is_array($shortcuts))
    			    {
    			        foreach($shortcuts as $k_short => $v_short)
    			        {
    			            $sql_courses[] = 'course_type="' . addslashes(Pms_CommonData::aesEncrypt($v_short)) . '" ';
    			        }
    			    }
    			    $sql_courses_str = implode("OR ", $sql_courses);
    			    $course_data = Doctrine_Query::create()
    			    ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
    			    ->from('PatientCourse')
     			    ->whereIn('ipid', $patient_ipids)
    			    ->andWhere('source_ipid = ""')
    			    ->andWhere($sql_courses_str);
    			    
    			    if(!empty($checked_all)){
    			        $course_data->andWhere(' `done_date` >=?', $period['start'] );
    			    }

    			    $all_sh = $course_data->fetchArray();
			     }
			     
			     $minutes_array = array();
			     
			     if(!empty($form_type_ids)  && !empty($patient_ipids)){
    			    /* ----------------------Get all "deleted visits"  from patients------------------------------------- */
    			    $deleted_visits = Doctrine_Query::create()
    			    ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
    			    ->from('PatientCourse')
    			    ->where('wrong=1')
    			    ->andWhere('course_type=?', addslashes(Pms_CommonData::aesEncrypt("F")))
    			    ->andWhere("tabname=?", addslashes(Pms_CommonData::aesEncrypt('contact_form')))
    			    ->andWhereIn('ipid', $patient_ipids);
    			    
    			    if(!empty($checked_all)){
    			         $deleted_visits->andWhere('`done_date` >=?', $period['start'] );
    			    }
    			    
    			    $deleted_visits_array = $deleted_visits->fetchArray();
    			    
    			    $del_visits = array();
    			    foreach($deleted_visits_array as $k_del_visit => $v_del_visit)
    			    {
    			        if( ! empty($v_del_visit['recordid'])){
    			            $del_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
    			        }
    			    }
    			   
			   
			    /* ----------------------Get all "doctor visits" details from patients------------------------------------- */
			    
			        $doctor_visits = Doctrine_Query::create()
			        ->select("*,c.ipid,c.id")
			        ->from("ContactForms c")
			        ->WhereIn('c.ipid', $patient_ipids)
			        ->andwhereIn('c.form_type', $form_type_ids);
			        
			        if(!empty($checked_all)){
			            $doctor_visits->andWhere(' `billable_date` >=?', $period['start'] );
			        }
			        
			        if( !empty($del_visits['contact_form'])){
			            $doctor_visits->andWhereNotIn('c.id', $del_visits['contact_form']);
			        }
			        $doctor_visits->andWhere("isdelete = 0");
			        $doctor_visits->orderBy('start_date ASC');
			        $doctor_visits_arr = $doctor_visits->fetchArray();
			        
			      
			        foreach($doctor_visits_arr as $doc => $value_doc)
			        {
			            $start_date = strtotime($value_doc['start_date']);
			            $end_date = strtotime($value_doc['end_date']);
			            $doc_visit_minutes = round(($end_date - $start_date) / 60);
			            
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['action_date'] = date('d.m.Y', strtotime($value_doc['start_date']));
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['user_name'] = $username[$value_doc['create_user']];
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['action_time'] = date('H:i', strtotime($value_doc['start_date'])) . ' - ' . date('H:i', strtotime($value_doc['end_date']));
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['action_type'] = $form_type[$value_doc['form_type']];
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['minutes'] = $doc_visit_minutes;
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['action_full_date'] = $value_doc['start_date'];
			            $minutes_array[$value_doc['ipid']]['visit_' . $value_doc['id']]['action_full_datetime'] = strtotime($value_doc['start_date']);
			        }
			    }

			    
			    
			    foreach($all_sh as $all_sh_key => $all_sh_val)
			    {
		            $all_sh_arr = explode("|", $all_sh_val['course_title']);
		            $xt_array[$patient_array[$all_sh_val['ipid']]['epid']][] = $all_sh_val['course_title'];
		            // Ancuta 03.04.2019 -  change from course_date to done date
		            if(!empty($all_sh_val['done_date']) && $all_sh_val['done_date'] != "0000-00-00 00:00:00"){
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_date'] = date('d.m.Y', strtotime($all_sh_val['done_date']));
		                
		            } else {
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_date'] = date('d.m.Y', strtotime($all_sh_val['course_date']));
		            }
		            
		            if ($all_sh_val['course_type']=='XT'){
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_type'] = "Telefon";
		            } elseif ($all_sh_val['course_type']=='V'){
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_type'] = "Koordination";
		            } elseif ($all_sh_val['course_type']=='U'){
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_type'] = "Beratung";
		            } else {
		                $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_type'] = "other action";
		            }
		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['user_name'] = $username[$all_sh_val['create_user']];
		            if ($all_sh_val['course_type']=='U'){
    		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['minutes'] = intval($all_sh_arr[1]);
		            } else{
    		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['minutes'] = intval($all_sh_arr[0]);
		            }
		            
		            
		            $from_till ="";
		            $duration = 0 ;
		            $duration = $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['minutes'];
		            $starts_at = '';
		            
		            if(!empty($all_sh_val['done_date']) && $all_sh_val['done_date'] != "0000-00-00 00:00:00"){
		                $starts_at = date('H:i', strtotime($all_sh_val['done_date']));
		            }
		            
		            $minutes = "";
		            $minutes = "+".$duration." minutes";
		            
		            $ends_at = '';
		            $ends_at = date("H:i",strtotime($minutes, strtotime($all_sh_val['done_date'])));
		            $from_till = $starts_at ." - ".$ends_at;
		            
		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_time'] = $from_till;
		            
		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_full_date'] = $all_sh_val['done_date'];
		            $minutes_array[$all_sh_val['ipid']][$all_sh_val['course_type']."_" . $all_sh_val['id']]['action_full_datetime'] = strtotime($all_sh_val['done_date']);
			            
			    }
			    
		
			    
			    
			    $user_documentation = array();
			    foreach($minutes_array as $patient_ipid => $values)
			    {
			        usort($values, array(new Pms_Sorter('action_full_datetime'), "_number_desc"));
			        
			        $x = 0;
			        foreach($values as $action_identification => $vls)
			        {
			            $user_documentation[$patient_ipid][$x]['user_name'] = $vls['user_name'];
			            $user_documentation[$patient_ipid][$x]['action_type_user'] = $vls['action_type'];
			            $user_documentation[$patient_ipid][$x]['date_of_action'] = $vls['action_date'];
			            $user_documentation[$patient_ipid][$x]['time_of_action'] = $vls['action_time'];
                        $user_documentation[$patient_ipid][$x]['time_duration'] = $vls['minutes'];
			                                    
                        if($vls['minutes'])
                        {
                            $user_documentation[$patient_ipid][$x]['time_duration'] = $vls['minutes'];
                        }
                        else
                        {
                            $user_documentation[$patient_ipid][$x]['time_duration'] = "-";
                        }
			            $x++;
			        }
			    }
			    
			    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			        
    			    /* ------------------------------------Display all results for SEARCH ------------------------------------------ */
			        $MasterData = array();
    			    if(!empty($patientDetails)){
    		        
    			        foreach($patientDetails as $keypatient => $patient)
    			        {
			                if($post_data['actions'] == 1){
			                    
			                    if ($user_documentation[$patient['ipid']]){
			                        $MasterData[$patient['ipid']]['ipid'] = $patient['ipid'];
			                        $MasterData[$patient['ipid']]['lastname'] = $patient['lastname'];
			                        $MasterData[$patient['ipid']]['firstname'] = $patient['firstname'];
			                        $MasterData[$patient['ipid']]['last_update_user'] = $username[$patient['last_update_user']];
			                        
			                        $MasterData[$patient['ipid']]['patient_actions'] = $user_documentation[$patient['ipid']];
			                    } else {
			                        if ($total_display == 0){
			                            $MasterData[$patient['ipid']]['ipid'] = $patient['ipid'];
			                            $MasterData[$patient['ipid']]['lastname'] = $patient['lastname'];
			                            $MasterData[$patient['ipid']]['firstname'] = $patient['firstname'];
			                            $MasterData[$patient['ipid']]['last_update_user'] = $username[$patient['last_update_user']];
			                            
			                            $MasterData[$patient['ipid']]['patient_actions'] = array(array(
			                                "user_name"=>$username[$patient['last_update_user']],
			                                "action_type_user"=>'-',
			                                "date_of_action"=>$patient['last_update'],
			                                "time_of_action"=>$patient['last_update_time'],
			                                "time_duration"=>'-' ));
			                        }
			                    }
			                    
			                } else{
			                     if ($user_documentation[$patient['ipid']]){
        			                $MasterData[$patient['ipid']]['ipid'] = $patient['ipid'];
        			                $MasterData[$patient['ipid']]['lastname'] = $patient['lastname'];
        			                $MasterData[$patient['ipid']]['firstname'] = $patient['firstname'];
        			                $MasterData[$patient['ipid']]['last_update_user'] = $username[$patient['last_update_user']];
    			                    
        			                $MasterData[$patient['ipid']]['patient_actions'] = $user_documentation[$patient['ipid']];
                                 }
			                 }
    			        }
	         			        
    			    }
    			    
    			
    			        
     			        $return['htmlx']  ='<table class="last_contact_action" id="last_contact_action">';
     			        $return['htmlx'] .='<thead >';
 	
     			        $return['htmlx'] .='<tr><th width="1%" rowspan="2">'.$this->view->translate("no").' </th>';
 			            $return['htmlx'] .='<th width="1%" rowspan="2">'.$this->view->translate("firstname").'</th>';
 			            $return['htmlx'] .='<th width="1%" rowspan="2">'.$this->view->translate("lastname").'</th>';
 			            $return['htmlx'] .='<th width="1%" colspan="5">'.$this->view->translate("Contact details").'</th></tr>';
 		                $return['htmlx'] .='<tr><th width="1%" >'.$this->view->translate("user_name").'</th>';
 			            $return['htmlx'] .='<th width="1%"  >'.$this->view->translate("action_type_user").'</th>';
 			            $return['htmlx'] .='<th width="1%"  >'.$this->view->translate("date_of_action").'</th>';
 			            $return['htmlx'] .='<th width="1%"  >'.$this->view->translate("time_of_action").'</th>';
 			            $return['htmlx'] .='<th width="1%"  >'.$this->view->translate("duration").'</th></tr>';
 			            $return['htmlx'] .='</thead><tbody>';
 			            
 			            $rowcount = 1;
 			            if(!empty($MasterData)){
 			                
     			            foreach($MasterData as $k => $lcu){
     			                foreach($lcu["patient_actions"] as $sec_key => $sec_row){
     			                    $rowspan = 1; 
     			                    $rowspan = count($lcu["patient_actions"]);
     			                    if($sec_key == min(array_keys($lcu["patient_actions"]))) {
     			                        
     			                        $return['htmlx'] .='<tr><td rowspan="'.$rowspan.'">'.$rowcount.'</td>';
     			                        $return['htmlx'] .='<td rowspan="'.$rowspan.'">'.$lcu["firstname"].'</td>';
     			                        $return['htmlx'] .='<td rowspan="'.$rowspan.'">'.$lcu["lastname"].'</td>';
     			                        
     			                        $return['htmlx'] .='<td>'.$sec_row["user_name"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["action_type_user"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["date_of_action"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["time_of_action"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["time_duration"].'</td></tr>';
     			                        
     			                        $return['htmlx'] .='';
     			                        $return['htmlx'] .='';
     			                        
     			                    } else {
     			                        
     			                        $return['htmlx'] .='<td>'.$sec_row["user_name"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["action_type_user"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["date_of_action"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["time_of_action"].'</td>';
     			                        $return['htmlx'] .='<td>'.$sec_row["time_duration"].'</td></tr>';
     			                        
     			                    }
     			                }
     			                $rowcount++;
     			            }
 			            }
                        $return['htmlx'] .='</tbody> </table>';

     			        echo json_encode($return);
    			        exit;
			  

    			      
			    } else{
			        /* ------------------------------------Display all results ------------------------------------------ */
			        $i = 0;
			        $MasterData = array();
			        if(!empty($patientDetails)){
			            
			            foreach($patientDetails as $keypatient => $patient)
			            {
			                if ($user_documentation[$patient['ipid']]){
			                    $MasterData[$patient['ipid']]['ipid'] = $patient['ipid'];
			                    $MasterData[$patient['ipid']]['lastname'] = $patient['lastname'];
			                    $MasterData[$patient['ipid']]['firstname'] = $patient['firstname'];
			                    $MasterData[$patient['ipid']]['last_update_user'] = $username[$patient['last_update_user']];
			                    
			                    $MasterData[$patient['ipid']]['patient_actions'] = $user_documentation[$patient['ipid']];
			                } else {
			                    if ($total_display == 0){
			                        $MasterData[$patient['ipid']]['ipid'] = $patient['ipid'];
			                        $MasterData[$patient['ipid']]['lastname'] = $patient['lastname'];
			                        $MasterData[$patient['ipid']]['firstname'] = $patient['firstname'];
			                        $MasterData[$patient['ipid']]['last_update_user'] = $username[$patient['last_update_user']];
			                        
			                        $MasterData[$patient['ipid']]['patient_actions'] = array(array(
			                            "user_name"=>$username[$patient['last_update_user']],
			                            "action_type_user"=>'-',
			                            "date_of_action"=>$patient['last_update'],
			                            "time_of_action"=>$patient['last_update_time'],
			                            "time_duration"=>'-' ));
			                    }
			                }
			                
			                $i++;
			                
			            }
			            $this->view->lastcontactupdate = $MasterData;
			            
			        }
   
			      }
			}
			
			//ISPC-2827 Ancuta 27.03.2021
			public function overviewefaAction(){
 
			    set_time_limit(0);
			    $logininfo = new Zend_Session_Namespace('Login_Info');
			    $hidemagic = Zend_Registry::get('hidemagic');
			    $clientid = $logininfo->clientid;
			    $userid = $logininfo->userid;
			    $default_boxes = array('1', '2', '3', '4', '5', '6', '8');
			    
			    $this->view->client_id = $clientid;
			    $this->view->user_id = $userid;
			    $this->view->userid = $logininfo->userid;
			    $this->view->hidemagic = $hidemagic;
			    $this->view->clientid = $logininfo->clientid;
			    
			    $previleges = new Pms_Acl_Assertion();
			    $return = $previleges->checkPrevilege('overview', $logininfo->userid, 'canview');
			    $Notifications_arr  = new Notifications();
			    $user_notifications_settings = $Notifications_arr ->get_notification_settings($userid);
			    $p_users = new PatientUsers();
			    $user_patients = $p_users->getUserPatients($logininfo->userid); //get user's patients by permission
			    
			    if(!$return)
			    {
			        $this->_redirect(APP_BASE . "error/previlege");
			    }
			    
			    
			    $modules = new Modules();
			    $this->view->ModulePrivileges = $modules->get_client_modules($clientid);
			  
			    
			    if($logininfo->usertype != 'SA')
			    {
			        $eipd = Doctrine_Query::create()
			        ->select('epid')
			        ->from('PatientQpaMapping')
			        ->where('userid =' . $logininfo->userid)
			        ->orderBy('id DESC');
			        $epidarray = $eipd->fetchArray();
			        
			        $comma = ",";
			        $epidval = "'0'";
			        foreach($epidarray as $key => $val)
			        {
			            $epidval .= $comma . "'" . $val['epid'] . "'";
			            $comma = ",";
			        }
			    }
			    else
			    {
			        if($logininfo->clientid > 0)
			        {
			            $eipd = Doctrine_Query::create()
			            ->select('epid')
			            ->from('PatientQpaMapping')
			            ->where('clientid =' . $logininfo->clientid)
			            ->orderBy('id DESC')
			            ->limit(15);
			            $epidarray = $eipd->fetchArray();
			            
			            $comma = ",";
			            $epidval = "'0'";
			            foreach($epidarray as $key => $val)
			            {
			                $epidval .= $comma . "'" . $val['epid'] . "'";
			                $comma = ",";
			            }
			        }
			        else
			        {
			            $epidval = "'0'";
			        }
			    }
			    
			    $ipidq = Doctrine_Query::create()
			    ->select('e.ipid')
			    ->from('EpidIpidMapping e')
			    ->where("e.epid in (" . $epidval . ")")
			    ->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
			    ->orderby('e.id DESC');
			    $ipidarray = $ipidq->fetchArray();
			    
			    $comma = ",";
			    $ipidval = "'0'";
			    foreach($ipidarray as $key => $val)
			    {
			        $ipidval .= $comma . "'" . $val['ipid'] . "'";
			        $comma = ",";
			    }
			    $columnarray = array("pk" => "id", "ln" => "last_name");
			    
			    
			    $lastipid = Doctrine_Query::create()
			    ->select('*')
			    ->from('EpidIpidMapping e')
			    ->where("e.clientid = " . $logininfo->clientid)
			    ->andWhere('e.ipid IN (' . $user_patients['patients_str'] . ')')
			    ->orderby('e.id DESC');
			    $lastipidarray = $lastipid->fetchArray();
			    
			    $comma = ",";
			    $newipidval = "'0'";
			    foreach($lastipidarray as $key => $val)
			    {
			        $newipidval .= $comma . "'" . $val['ipid'] . "'";
			        $comma = ",";
			    }
			    
			    $all_user = Doctrine_Query::create()
			    ->select("*")
			    ->from('User')
			    ->where('isdelete = 0 ');
			    $all_userarray = $all_user->fetchArray();
			    $allUsersArray = array();
			    if(count($all_userarray) > 0)
			    {
			        
			        User::beautifyName($all_userarray);
			        foreach($all_userarray as $key => $val_user)
			        {
			            $allUsersArray[$val_user['id']] = $val_user;
			        }
			    }
			    
			    
			    $user = Doctrine_Query::create()
			    ->select("*")
			    ->from('User')
			    ->where('clientid = ' . $logininfo->clientid . ' or usertype="SA"')
			    ->andWhere('isactive=0 and isdelete = 0 and no10contactsbox = 0')
			    ->orderBy('last_name ASC');
			    $userarray = $user->fetchArray();
			    
			    $comma = ",";
			    $usercomma = "'0'";
			    if(count($userarray) > 0)
			    {
			        
			        User::beautifyName($userarray);
			        foreach($userarray as $key => $valu)
			        {
			            $clientUsersArray[$valu['id']] = $valu;
			            $usercomma .= $comma . "'" . $valu['id'] . "'";
			            $comma = ",";
			        }
			    }
 
	            $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
	            $this->view->order = $orderarray['DESC'];
	            
	            $sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
	            $sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
	            $sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
	            $sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
	            $sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
	            $sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
	            $sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
	            $sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
	            $sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
	            $sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
	            $sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
	            $sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
	            
	            // if super admin check if patient is visible or not
	            if($logininfo->usertype == 'SA')
	            {
	                $sql = "*,";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
	                $sql .= "IF(pm.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
	            }
	        
	            
	            /* ------------ BOX Patients  Start---------------- */
	            $clientarr = Pms_CommonData::getClientData($logininfo->clientid);
	            $ust = new UserSettings();
	            $ustarr = $ust->getUserSettings($userid);
	            
		          
                $patient = Doctrine_Query::create()
                ->select('count(*)')
                ->from('PatientMaster pm')
                ->andWhere('isdelete = 0 and last_update_user in(' . $usercomma . ')')
                ->leftJoin('pm.EpidIpidMapping ep')
                ->andWhere('ep.clientid=' . $logininfo->clientid)
                ->andWhere('ep.ipid=pm.ipid')
                ->andWhere('pm.ipid IN (' . $user_patients['patients_str'] . ')')
                ->orderBy('pm.last_update DESC');
                if($ustarr['entries_last_xx_days'] > 0)
                {
                    $comp_date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $ustarr['entries_last_xx_days'], date('Y')));
                    $patient->andWhere('DATE(pm.last_update) >=?', $comp_date);
                }
                
                $patientarray = $patient->fetchArray();

                $maxcontact = $clientarr[0]['maxcontact'];
                
                if(!empty($ustarr['last_xx_entries']))
                {
                    $limit = $ustarr['last_xx_entries'];
                }
                else
                {
                    $limit = 10;
                    if($maxcontact > 0)
                    {
                        $limit = $maxcontact;
                    }
                }
                
                $patient->select($sql);
                if($logininfo->hospiz == 1)
                {
                    $patient->where('ishospiz = 1');
                }
                
                $patient->orderBy('last_update DESC');
                $patient->limit($limit);
                $patient->offset($_GET['pgno'] * $limit);
                $patientlimit = $patient->fetchArray();
                
                //get last update users and patient ipids
                $last_update_user[] = '99999999';
                $patient_ipids[] = '99999999';
                
                foreach($patientlimit as $k_pat_limit => $v_pat_limit)
                {
                    $last_update_users[] = $v_pat_limit['last_update_user'];
                    $patient_ipids[] = $v_pat_limit['ipid'];
                }
                
                $last_update_users = array_values(array_unique($last_update_users));
                $patient_ipids = array_values(array_unique($patient_ipids));
                
                //get last update users data
                $last_update_users_data = Pms_CommonData::getUsersData($last_update_users);
                
                
                $patloc = Doctrine_Query::create()
                ->select('ipid, location_id, clientid')
                ->from('PatientLocation')
                ->whereIn('ipid',$patient_ipids)
                ->andWhere("valid_till='0000-00-00 00:00:00' ")
                ->andWhere("isdelete='0' ")
                ->groupBy('location_id');
                $plarray = $patloc->fetchArray();
                
                $locmaster = Doctrine_Query::create()
                ->select('id, aes_decrypt(location,"encrypt") as name, client_id')
                ->from('Locations IndexBY id')
                ->where('client_id =' . $clientid . '')
                ->andWhere("isdelete='0' ");
                $lmarray = $locmaster->fetchArray();
                
                foreach($plarray  as $k=>$pl){
                    $patient2location[$pl['ipid']] = $lmarray[$pl['location_id']]['name'];
                }
  
                
                $pdiag =  new PatientDiagnosis();
                $pdiagno_array = $pdiag->get_ipids_main_diagnosis($patient_ipids,$clientid);
                
                foreach($patientlimit as $dk=>$pdata){
                    $patientlimit[$dk]['enc_id'] = Pms_Uuid::encrypt($pdata['id']);
                    $patientlimit[$dk]['patient_name'] = $pdata['last_name'].', '.$pdata['first_name'];
                    $patientlimit[$dk]['birth_date'] = date('d.m.Y',strtotime($pdata['birthd']));
                    $patientlimit[$dk]['current_location'] = $patient2location[$pdata['ipid']];
                    $patientlimit[$dk]['main_diagnosis'] = !empty($pdiagno_array[$pdata['ipid']]['all_str']) ? implode(', ',$pdiagno_array[$pdata['ipid']]['all_str']) : "";
                }
                
                
                //get patients healthinsurance
                $phelathinsurance = new PatientHealthInsurance();
                $healthinsu_array = $phelathinsurance->get_patients_healthinsurance($patient_ipids);
                
                foreach($healthinsu_array as $k_health_insu => $v_health_insu)
                {
                    $healthinsu_arr[$v_health_insu['ipid']] = $v_health_insu;
                }
                
                $this->view->last_update_users_data = $last_update_users_data;
                $this->view->patient_health_insurances = $healthinsu_arr;
                $this->view->patients_arr = $patientlimit;
//                 $grid = new Pms_Grid($patientlimit, 1, $patientarray[0]['count'], "efalastcontactgrid.html");
                
                
//                 $this->view->contentitem1 = $grid->renderGrid();
                
                $this->view->patients_box = $this->view->render('efa/overviewpatients.html');
	            /* ------------ BOX -  Patients ---------------- */
                
                
                
	            /* ------------ BOX -  Messages ---------------- */
                $messages = Doctrine_Query::create()
                ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
                ->from('Messages m')
                ->leftJoin("m.MessagesDeleted m2 ON m.id = m2.messages_id AND m2.messages_id IS NOT NULL AND m2.recipient = '" . $userid. "'")
                ->where(' m2.messages_id IS NULL ')
                ->andWhere('m.recipient = ' . $userid)
                ->andWhere('m.folder_id = ?', 0)
                ->andWhere('m.delete_msg = ?', '0')
                ->andWhere('m.read_msg = ?', '0')
                ->fetchArray();

                foreach($messages as $km=>$msg){
                    $messages[$km]['sender_name'] = $allUsersArray[$msg['sender']]['nice_name'];
                }
                $this->view->received_messages = $messages;
                $this->view->messages_content = $this->view->render('efa/overviewmessages.html');
                
                
                /* ------------ BOX -  User text box ---------------- */
                //ISPC-2827 Lore 30.03.2021
                $userbox = new ClientUserTextBox();
                $box = $userbox->get_client_user_text_box($clientid, $userid);
                $user_text_box = trim(strip_tags($box[0]['content']));
                $this->view->user_text_box = $user_text_box;
                
                $this->view->user_personal_box = $this->view->render('efa/overviewuserbox.html');

                
                /* ------------ BOX -  Calendar ---------------- */
                $this->view->overview_calendar = $this->view->render('efa/overviewcalendar.html');
                
			            
			}
	
}
?>