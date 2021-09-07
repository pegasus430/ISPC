<?php
/**
 * 
 * @author claudiu✍ 
 * Oct 25, 2018
 *
 * moved from the acl plugin the fn that is related to the layout
 * + added layout setup based on device type
 */
class Pms_Plugin_LayoutSetup extends Zend_Controller_Plugin_Abstract
{   
    private $_mobile_suffix = 'phtml';
    
    /*
     * this are the Zend defaults
     * Layout Target = ":script.:suffix";
     * View Target = ":controller/:action.:suffix";
     */
    private $_mobile_Layout_Target = ":device/:script.:suffix";
    
    private $_mobile_View_Target = ":controller/:action.:device.:suffix";
    
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
    
        $bootstrap  = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        
        $userAgent  = $bootstrap->getResource("useragent");
        $device     = $userAgent->getDevice();
    
        $this->_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
        $this->_logger->debug("ISPC_WEBSITE_VIEW_VERSION = " . ISPC_WEBSITE_VIEW_VERSION);
        $this->_logger->debug("\$device->getType() = " . $device->getType() );
        $this->_logger->debug("\$device->getFeature('is_tablet') = " . $device->getFeature('is_tablet'));
        $this->_logger->debug("\Pms_MobileDetect->isTablet = " . (new Pms_MobileDetect())->isTablet());
    
        
        
        if (ISPC_WEBSITE_VIEW_VERSION == 'mobile' 
            && $device->getType() == "mobile" 
            && (APPLICATION_ENV != 'production' || ! $device->getFeature('is_tablet'))
            && ! $device->getFeature('is_tablet')
            )
        {
            //return;
            /*
             * we check to see if a mobile version of the layout/view template exists. 
             * if it does then we change the view suffix
             * this allows us to load the mobile view if it exists and the default view if it doesnt.
             */
            
            
            if (null !== ($layout = Zend_Controller_Action_HelperBroker::getStaticHelper('Layout'))) {
                
                $layout->setDeviceType('mobile');
                
                if (null !== ($inflector = $layout->getInflector()))
                {
        
                    $original_suffix = $inflector->getRules('suffix');
                    $original_target = ! empty($inflector->getTarget()) ? $inflector->getTarget() : $layout->getInflectorTarget();
        
                    $inflector->addRules(array(':device' => array('Word_CamelCaseToDash', 'StringToLower')))
                    ->setStaticRule('device', 'mobile')
                    ;
                    //                 $inflector->setTarget($this->_mobile_Layout_Target);
        
                    $requestedLayout = $layout->getLayoutPath() .  $inflector->filter(['script' => $layout->getLayout(), 'suffix' => $this->_mobile_suffix]);
        
                    $inflector->setStaticRule('original.suffix',  $original_suffix);//this is before the change, will be used in ViewRenderer Helper
                    $inflector->setStaticRule('original.target',  $original_target);//this is before the change, will be used in ViewRenderer Helper
        
                    if (file_exists($requestedLayout) && is_readable($requestedLayout)) {
        
                        $layout->setViewSuffix($this->_mobile_suffix);
                        $layout->setInflectorTarget($this->_mobile_Layout_Target);
                        //                     $inflector->setStaticRule('suffix', $this->_mobile_suffix);
                        //                     $inflector->setTarget($this->_mobile_Layout_Target);
        
                    } else {
                        //                     $inflector->setTarget($original_target);
                        $layout->setInflectorTarget($original_target);
                    }
        
                }
            }
             
    
            
            if (null !== ($viewRenderer   = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')))
            {
                $viewRenderer->setDeviceType('mobile');
        
                if (null !== ($inflector = $viewRenderer->getInflector()))
                {
                    //do some fancy preg_replace if needed... till now the default one was used
                    //$targetApplication = $viewRenderer->getViewScriptPathSpec();
        
                    //                 $original_suffix = $inflector->getRules('suffix');
                    //                 $original_target = $viewRenderer->getViewScriptPathSpec();
        
                    //                 dd($viewRenderer->getScriptPath(), $inflector->getRules('suffix'), $viewRenderer->getViewScriptPathSpec());
        
                    $inflector->setStaticRule('original.suffix', $inflector->getRules('suffix'));//this is before the change, will be used in ViewRenderer Helper
                    $inflector->setStaticRule('original.target', $viewRenderer->getViewScriptPathSpec());//this is before the change, will be used in ViewRenderer Helper
        
                    $inflector->setStaticRule('device', 'mobile');
                    $inflector->setStaticRule('suffix', $this->_mobile_suffix);
        
                    $viewRenderer->setViewScriptPathSpec($this->_mobile_View_Target);
                    $inflector->setTarget($this->_mobile_View_Target);
        
                }
            }
        }
    
        return $request;
    }
    
    
    
    
    
    
    /**
     * append default javascripts and css
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        $appInfo = Zend_Registry::isRegistered('appInfo') ? Zend_Registry::get('appInfo') : [];
        
        
        /**
         * set default title
         */
        $view->headTitle()->setSeparator(' | ');
        
        $view->headTitle()->append($view->translate($request->getControllerName()));
        
        if ($request->getControllerName() != $request->getActionName()) {            
            $view->headTitle()->append($view->translate($request->getActionName()));
        }
        
        if ( ! empty($appInfo['title']))
            $view->headTitle()->append($appInfo['title']);
        
        
        /**
         * set default metas
         */
        if ( ! empty($appInfo['copyright']))
            $view->headMeta($appInfo['copyright'], "copyright");
        
        if ( ! empty($appInfo['copyright']))
            $view->headMeta($appInfo['owner'], "owner");
        
        if ( ! empty($appInfo['author']))
            $view->headMeta($appInfo['author'], "author");
        
        
        
        
        /**
         * add js
         */
        $view->headScript()
            ->appendFile(RES_FILE_PATH . '/javascript/jquery-1.8.0.js')
            
            /*a script from 2007... should not be used*/
            ->appendFile(RES_FILE_PATH . '/javascript/pms/jquery.table_navigation.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/livesearch/jquery.ajaxQueue.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/menu.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/pms/jqcallserver.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/mask/jquery.maskedinput.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/jquery.megamenu.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/popup/popup.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/jquery.ui.timepicker.js')
            	
        
            ->appendFile(RES_FILE_PATH . '/javascript/jquery-ui-1.8.23.custom.min.js')
            
//             ->appendFile(RES_FILE_PATH . '/javascript/jquery.ui.touch-punch.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/jquery.ui.datepicker-de.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/jquery.blockUI.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/instantedit/instantedit.js')
            
            /*ISPC checksession fn*/
            ->appendFile(RES_FILE_PATH . '/javascript/pms/usersession.js' )
            ->appendFile(APP_BASE . 'javascript/pms/formsEditmode.ajax.js' )// one js for all versions of view
            /*ISPC global fn*/
            ->appendFile(RES_FILE_PATH . '/javascript/pms/document.ready.js')
            
            /*ISPC $.fn.liveSearch*/ 
            ->appendFile(RES_FILE_PATH . '/javascript/livesearch_new/livesearch.js')
             /* 
			 // Ancuta updated highcharts on 30.04.2020	 ISPC-2512
            ->appendFile(RES_FILE_PATH . '/javascript/highcharts_v5.0.12/highcharts.js')
            ->appendFile(RES_FILE_PATH . '/javascript/highcharts_v5.0.12/highcharts-more.js')
            ->appendFile(RES_FILE_PATH . '/javascript/highcharts_v5.0.12/moment.js')
            ->appendFile(RES_FILE_PATH . '/javascript/highcharts_v5.0.12/moment-timezone-with-data-2012-2022.js')
            ->appendFile(RES_FILE_PATH . '/javascript/highcharts_v5.0.12/modules/exporting.js')
              */
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/highcharts.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/highcharts-more.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/moment.min.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/moment-timezone-with-data-2012-2022.min.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/modules/series-label.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/modules/exporting.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/modules/export-data.js')
            ->appendFile(RES_FILE_PATH . '/javascript/Highcharts-8.0.4/modules/accessibility.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/chosen_v1.6.2/chosen.jquery.js')
            
            ->appendFile(RES_FILE_PATH . '/javascript/views/user/elviroom.js')
            
            //->appendFile(APP_BASE . 'javascript/bootstrap-notify.js') // one js for all versions of view
            ->appendFile(RES_FILE_PATH . '/javascript/bootstrap-notify.min.js') 
            ;


        //TODO : move this if in the controller where this actions reside
        if (in_array($request->getActionName(), ['addnews', 'editnews', 'editcontactaddress', 'doctorletteradd', 'doctorletteredit'])) {
//         if( Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'addnews'
//             || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'editnews'
//             || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'editcontactaddress'
//             || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'doctorletteradd'
//             || Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'doctorletteredit')
//         {
            $view->headScript()
                ->appendFile(RES_FILE_PATH . '/javascript/tinymce3/jscripts/tiny_mce/tiny_mce.js')
                ->appendFile(RES_FILE_PATH . '/javascript/swampy_browser/sb.js');
        }
        
        
        
//         ISPC-2615 Ancuta 13.07.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
        if (in_array($request->getControllerName(), ['patientcourse','patient', 'patientnew', 'patientform', 'patientformnew','patientmedication','rubin'])) {
            $view->headScript()
                ->appendFile(RES_FILE_PATH . '/javascript/pms/pateintwrongclient.js' );
        }
//         --
        
        
        $view->headScript()
            ->appendFile(RES_FILE_PATH . '/javascript/pms/shortcut.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/livesearch/directpatientsearch.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/pms/jquery.alerts_new.js' )
            ->appendFile(RES_FILE_PATH . '/javascript/jgrowl/jquery.jgrowl.js' )
            ->appendFile(OLD_RES_FILE_PATH . "/jslang/jstranslate", 'text/javascript', array('charset' => 'UTF-8'));

        
        


        /**
         * add css
         */

        if(preg_match("/MSIE 8/i", $_SERVER['HTTP_USER_AGENT']))
        {
            $view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/ie8.css');
        }
        //			else if(eregi("MSIE 7", $_SERVER['HTTP_USER_AGENT']))
        else if(preg_match("/MSIE 7/i", $_SERVER['HTTP_USER_AGENT']))
        {
            $view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/ie7.css');
        }
         
        $view->headLink()
            ->appendStylesheet(RES_FILE_PATH . '/css/jquery.ui.timepicker.css')
            
//             ->appendStylesheet(RES_FILE_PATH . '/css/smoothness/jquery-ui-1.8.23.custom.css')
            
//             ->appendStylesheet(RES_FILE_PATH . '/css/cupertino/jquery-ui-1.8.23.custom.css')
            
            ->appendStylesheet(RES_FILE_PATH . '/css/jquery.alerts.css')
            ->appendStylesheet(RES_FILE_PATH . '/javascript/jgrowl/jquery.jgrowl.css')
            ->appendStylesheet(RES_FILE_PATH . '/javascript/popup/popup.css')
            ->appendStylesheet(RES_FILE_PATH . '/javascript/chosen_v1.6.2/chosen_2016.09.22.css','all')
                ;
        
        
        //add the bootstrap css+js
        //$view->headLink()->appendStylesheet(RES_FILE_PATH . '/bootstrap/4.1.3/css/bootstrap.min.css');
        //for now is only used for login page...
        

             
        
    }
    
    
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        
//         dd(Zend_Layout::getMvcInstance()->getLayout());

//         $view = $this->getLayout()->getView();
        $requestinfo = new Zend_Session_Namespace('Login_Info');
        
        /**
         * this var is not used
         */
        $requestinfo->requesturl = $request->getControllerName() . "/" . $request->getActionName() . "?" . $_SERVER ['QUERY_STRING'];
        // 			$requestinfo->requesturl = Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . "/" . Zend_Controller_Front::getInstance()->getRequest()->getActionName() . "?" . $_SERVER ['QUERY_STRING'];
        
        /**
         * add _clientModules to global _SESSION, so is available in systemwide ... neded first in layouts
        */
        if (empty($requestinfo->_clientModules)) {
            $requestinfo->_clientModules = (new Modules())->get_client_modules($requestinfo->clientid);
        }
        	
        if($requestinfo->usertype == 'SA') {
            $isadmin = 1;
        } elseif($requestinfo->usertype == 'CA' || $requestinfo->usertype == 'SCA') {
            $isadmin = 2;
        } else {
            $isadmin = 0;
        }
        
        
        //ISPC-2827 Ancuta 26.03.2021
        if($requestinfo->isEfaClient =='1' && $requestinfo->isEfaUser =='1'){
            $app_layout = "layout_external";
        } else{
            $app_layout = Zend_Layout::getMvcInstance()->getLayout();
        }
        //--

        
        //don`t load menus data in ajax request layouts
        $excluded_layouts = array('layout_basic', 'layout_blank', 'layout_ajax');
        
        /*
         * @since 29.08.018 @cla added extra to exclude
        */
        $excluded_layouts_2018 = [
            'layout_report',
            'layout_popup',
            'layout_totalblank',
            'layout_printverlauf',
            'layout_networkstatistics',
        ];
        $excluded_layouts = array_merge($excluded_layouts, $excluded_layouts_2018);
        	
        if ( ! empty($app_layout) && ! in_array($app_layout, $excluded_layouts))
        {
        
             
             
            //TODO: inquire how session is stored and what is the max size (done, A: don't worry)
            $NavigationMenus         = new Zend_Session_Namespace('Navigation_Menus');
            /*
             * structure will be like this
              
             $NavigationMenus = {
             'counters' => [// holds int counters
             'inboxmailcount'             => 0, //new messages
             'countIpidsOfUser'           => 0, //patients of user
             'countTodosOfUserFromIpids'  => 0, //todos of user
             'waiting_share_request'      => 0, //waiting share requests for current client
             ],
             'menus' => [// holds the menus arrays
             'getTopParentMenus'  => [],
             'getLeftParentMenus' => [],
             'getAllLeftSubMenus' => [],
             ],
             };
            */
             
            /*
             * the counters will be off-targer for 2 minutes or 20 hops, so if user notices -> reduce this delays
             * controllers todos & message will fetch each time, cause there is where you change this counters
            */
            $setExpirationSeconds    = 120;
            $setExpirationHops       = 20;
             
            $view->inboxmailcount            = 0;
            $view->countIpidsOfUser          = 0;
            $view->countTodosOfUserFromIpids = 0;
            $view->waiting_share_request     = 0;
            $view->patient_to_be_deleted     = 0; //ISPC-2474 Ancuta 02.11.2020 patient that can be deleted 
            if (empty($NavigationMenus->counters) || in_array($request->getControllerName(), ['todos', 'message'])) {//get counters
        
                if (empty($NavigationMenus->counters)) {
            				    //get waiting share requests for current client
            				    $waiting_requests = PatientsMarked::received_requests_get($requestinfo->clientid, 'p');
            				    $waiting_share_request = $view->waiting_share_request = count($waiting_requests);
                } else {
                    $waiting_share_request = $NavigationMenus->counters['waiting_share_request'];
                }

                //ISPC-2474 Ancuta 02.11.2020 patient that can be deleted 
                if (empty($NavigationMenus->counters)) {
                                $delete_requests = Patient4Deletion::patient_2_be_deleted($requestinfo->clientid);
                                $patient_to_be_deleted = $view->patient_to_be_deleted = count($delete_requests);
                } else {
                    $patient_to_be_deleted = $NavigationMenus->counters['patient_to_be_deleted'];
                }
                //--
                
                if (empty($NavigationMenus->counters) || $request->getControllerName() == 'message') {
            				    //get inbox messages count
                				$mail_count = Pms_CommonData::getNewmsg($requestinfo->userid, 0);
                				$view->inboxmailcount = $mail_count;
                } else {
                    $mail_count = $view->inboxmailcount = $NavigationMenus->counters['inboxmailcount'];
                }
        
        
                if (empty($NavigationMenus->counters) || $request->getControllerName() == 'todos') {
                				/*
                				 * added this counter here, because it will be used in countTodosOfUserFromIpids
                				 * slow query, but used getUserPatients
                				 */
                				$p_users = new PatientUsers();
                				$user_patients = $p_users->getUserPatients($requestinfo->userid);
                				$filter_allowed_ipids = Doctrine_Query::create()
                				->select('id, ipid')
                				->from('PatientMaster p')
                				->where('p.isdelete = 0')
                				->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')')
                				->execute(null, Doctrine_Core::HYDRATE_NONE);
                				$filter_allowed_ipids = array_column($filter_allowed_ipids, "1");
        
                				//ISPC-2908,Elena,21.05.2021
                				$filter_allowed_ipids[] = 'XXXXXXXXX' ; //permit todos for voluntaryworkers
        
                				$countTodosOfUserFromIpids = Pms_CommonData::countTodosOfUserFromIpids($filter_allowed_ipids);
        
                				$countIpidsOfUser = $view->countIpidsOfUser = count($filter_allowed_ipids);
                				$view->countTodosOfUserFromIpids = $countTodosOfUserFromIpids;
        
                } else {
                    $view->countIpidsOfUser = $countIpidsOfUser = $NavigationMenus->counters['countIpidsOfUser'];
                    $view->countTodosOfUserFromIpids = $countTodosOfUserFromIpids = $NavigationMenus->counters['countTodosOfUserFromIpids'];
                }
        
            				$NavigationMenus->counters = [
            				    'inboxmailcount'             => $mail_count, //new messages
            				    'countIpidsOfUser'           => $countIpidsOfUser, //patients of user
            				    'countTodosOfUserFromIpids'  => $countTodosOfUserFromIpids, //todos of user
            				    'waiting_share_request'      => $waiting_share_request, //waiting share requests for current client
            				    'patient_to_be_deleted'      => $patient_to_be_deleted, //ISPC-2474 Ancuta 02.11.2020 patient that can be deleted 
            				];
            				$NavigationMenus->setExpirationHops($setExpirationHops, 'counters', true);
            				$NavigationMenus->setExpirationSeconds($setExpirationSeconds, 'counters');
        
            } else {
                $view->inboxmailcount               = $NavigationMenus->counters['inboxmailcount'];
                $view->countIpidsOfUser             = $NavigationMenus->counters['countIpidsOfUser'];
                $view->countTodosOfUserFromIpids    = $NavigationMenus->counters['countTodosOfUserFromIpids'];
                $view->waiting_share_request        = $NavigationMenus->counters['waiting_share_request'];
                $view->patient_to_be_deleted        = $NavigationMenus->counters['patient_to_be_deleted']; //ISPC-2474 Ancuta 02.11.2020 patient that can be deleted 
            }
            //Ancuta 17.07.2020 - added condition for getLeftParentMenus
            if (empty($NavigationMenus->menus) || empty($NavigationMenus->menus['getLeftParentMenus']) ) {
        
                $custom_topmenu_ids = null;
                if ( ! empty($requestinfo->UserSettings) && ! empty($requestinfo->UserSettings['topmenu']['Menus'])) {
                    $custom_topmenu_ids = $requestinfo->UserSettings['topmenu']['Menus'];
                }
                	
                $topmenu    = Menus::getTopParentMenus(0, $isadmin, null, $custom_topmenu_ids);
                $menuarr    = Menus::getLeftParentMenus($isadmin);
                $submenuarr = Menus::getAllLeftSubMenus();
        
                $NavigationMenus->menus = [
                    'getTopParentMenus'     => $topmenu,
                    'getLeftParentMenus'    => $menuarr,
                    'getAllLeftSubMenus'    => $submenuarr,
                ];
        
            } else {
                $topmenu    = $NavigationMenus->menus['getTopParentMenus'];
                $submenuarr = $NavigationMenus->menus['getAllLeftSubMenus'];
                $menuarr    = $NavigationMenus->menus['getLeftParentMenus'];
            }
        
            $grid = new Pms_Grid($topmenu, 1, count($topmenu), "topmenugrid.html");
            $view->topmenus = $grid->renderGrid();
            
//             dd( $menuarr);
            
            $leftmenusMobile = [];
            
            foreach ($menuarr as $primary_menu) {
                
                $primary_menu_group = array_merge( 
                    ! empty($primary_menu['menu_link']) ? [$primary_menu['menu_link'] => $primary_menu['menu_title']] : [],  
                    ! empty($submenuarr [ $primary_menu['id'] ]) ? array_column($submenuarr [ $primary_menu['id'] ], 'menu_title', 'menu_link') : []
                    );
//                 $menuarr_subgroup = [];                
//                 array_walk($primary_menu_group, function($i, $k) use(&$menuarr_subgroup) { strpos($k, '/') !== 0 ? $menuarr_subgroup['/' . $k] = $i : $menuarr_subgroup['/' . $k] = $i ;});
                $menuarr_subgroup = $primary_menu_group;
                $leftmenusMobile[ $primary_menu['menu_title'] ] =  $menuarr_subgroup;
                
            }
            $view->leftmenusMobile = $leftmenusMobile;
            $uses_submenupage = true;
            
            if($uses_submenupage ){
                $menu_id_excluded = 30;
                foreach($submenuarr as $parent_id=>$subs){
                    
                    if($parent_id== $menu_id_excluded){
                        unset($submenuarr[$parent_id]);  
                    }
                }
            }

            //TODO-3958 Ancuta 15.03.2021
            $parent_id2sublinks = array($parent_id2sublinks);
            foreach($submenuarr as $prtid => $smenu){
                foreach($smenu as $sk=>$smenu_info){
                    $parent_id2sublinks[$smenu_info['parent_id']][] = $smenu_info['menu_link'];
                }
            }
            //-- 
            
//             dd($parent_id2sublinks);
            
            $view->allsubmenus = $submenuarr;
            $grid = new Pms_Grid($menuarr, 1, count($menuarr), "leftmenugrid.html");
            $view->parentid2childlinks = $parent_id2sublinks;            //TODO-3958 Ancuta 15.03.2021
            $view->current_page_link = $request->getControllerName() . "/" . $request->getActionName() ;//ISPC-2782 Ancuta 19.01.2021
            $view->leftmenus = $grid->renderGrid();
            
            
            
            //ISPC-2827 Ancuta 26.03.2021
            //----------------------
            
            if($requestinfo->isEfaClient =='1' && $requestinfo->isEfaUser =='1'){
                $params = $request->getParams();
                if(isset($params['id'])){
                    
                    $decid = Pms_Uuid::decrypt($params['id']);
                    $ipid = Pms_CommonData::getIpid($decid);
                    
        			$patientmaster = new PatientMaster();
        			$patientinfo = $patientmaster->get_multiple_patients_details(array($ipid));
        			$view->patient_epid = $patientinfo[$ipid]['EpidIpidMapping']['epid'];
        			$view->patient_name = $patientinfo[$ipid]['last_name'].' '.$patientinfo[$ipid]['first_name'];
        			$view->patient_birth = date('d.m.Y',strtotime($patientinfo[$ipid]['birthd']));
        			
        			list($BirthYear, $BirthMonth, $BirthDay) = explode("-", $patientinfo[$ipid]['birthd']);
        			$bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
        			$curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y"));
    
        			$years = $patientmaster->GetTreatedDays(date("Y-m-d", $bdt_time), date("Y-m-d", $curr_time), true);
        			$returnarray['dob'] = date("d.m.Y", $bdt_time);
        			$returnarray['dobtext'] = $returnarray['dob'] . "(" . $years['years'] . " Jahre)";
        			$view->patient_age  = $returnarray['dobtext'];
        			
        			//Location
        			
        			$patloc = Doctrine_Query::create()
        			->select('ipid, location_id, clientid')
        			->from('PatientLocation')
        			->whereIn('ipid',array($ipid))
        			->andWhere("valid_till='0000-00-00 00:00:00' ")
        			->andWhere("isdelete='0' ")
        			->groupBy('location_id');
        			$plarray = $patloc->fetchArray();
        			
        			$locmaster = Doctrine_Query::create()
        			->select('id, aes_decrypt(location,"encrypt") as name, client_id')
        			->from('Locations IndexBY id')
        			->where('client_id =' . $requestinfo->clientid . '')
        			->andWhere("isdelete='0' ");
        			$lmarray = $locmaster->fetchArray();
        			
        			foreach($plarray  as $k=>$pl){
        			    if($pl['ipid'] == $ipid){
            			    $view->current_location = $lmarray[$pl['location_id']]['name'];
        			    }
        			}
        			
        			//Diagnosis
        			$pdiag =  new PatientDiagnosis();
        			$pdiagno_array = $pdiag->get_ipids_main_diagnosis(array($ipid),$requestinfo->clientid);
        			
        			foreach($patientlimit as $dk=>$pdata){
        			    $patientlimit[$dk]['enc_id'] = Pms_Uuid::encrypt($pdata['id']);
        			    $patientlimit[$dk]['patient_name'] = $pdata['last_name'].', '.$pdata['first_name'];
        			    $patientlimit[$dk]['birth_date'] = date('d.m.Y',strtotime($pdata['birthd']));
        			    $patientlimit[$dk]['current_location'] = $patient2location[$pdata['ipid']];
        			    $patientlimit[$dk]['main_diagnosis'] = !empty($pdiagno_array[$pdata['ipid']]['all_str']) ? implode(', ',$pdiagno_array[$pdata['ipid']]['all_str']) : "";
        			}
        			
        			$view->main_diagnosis =  !empty($pdiagno_array[$ipid]) &&!empty($pdiagno_array[$ipid]['all_str']) ? implode(', ',$pdiagno_array[$ipid]['all_str']) : "";
        			
                }
                
                $page_menu_link = $request->getControllerName() . "/" . $request->getActionName() ;
                
                
                $fdoc = Doctrine_Query::create()
                ->select('*')
                ->from('TabMenus')
                ->where('isdelete = "0"')
                ->andWhere('efa_menu = "1"')
                ->orderBy('sortorder ASC');
                $menuarray = $fdoc->fetchArray(); // proper way
                
                $efa_menus = array();
                foreach($menuarray as $k_menu=>$v_menu)
                {
                    $efa_menus[] = $v_menu['menu_link'];
                    if($v_menu['parent_id'] == '0')
                    {
                        $first_menu[$v_menu['id']] = $v_menu;
                    }
                    elseif($v_menu['parent_id']>'0')
                    {
                    }
                }
                
                $grid = new Pms_Grid($first_menu, 1, count($first_menu), "efatabmenus.html");
                $view->menugrid = $grid->renderGrid();
            
                //ISPC-2880 Ancuta 12.04.2021
                $has_events_add_button = 0;
                if( isset($requestinfo->_clientModules) && $requestinfo->_clientModules['226'] == '1'){
                    $has_events_add_button = 1;
                }
                $view->has_events_add_button = $has_events_add_button;
                // --
                

                $efa_patient_header = $view->render('templates/efapatientheader.phtml');
                $efa_patient_menu = $view->render('templates/efapatientmenu.phtml');
                
                $efa_tabs = array('efa/diagnosis',
                    'patient/patientfileupload',
                    'efa/interventions',
                    'efa/reactions',
                    'efa/vaccinations',
                    'efa/history',
                    'patientform/contactform',//ISPC-2880 Ancuta 12.04.2021
                    'efa/patientproblems',//ISPC-2864 Ancuta 13.04.2021
                    'efa/patientcalendar',//ISPC-2893 Ancuta 20.04.2021
                    'efa/specialcare',//ISPC-2891 Ancuta 20.04.2021
                    'patientformnew/anlage2kinder',//ISPC-2882 Ancuta 22.04.2021
                    'efa/aidsuppliers',//ISPC-2892 Ancuta 23.04.2021
                );
                
                if($requestinfo->isEfaClient =='1'){
                    $efa_tabs[] = 'patient/patientfileupload';
                }
                $efa_menus = array_merge($efa_menus,$efa_tabs);
                
                if(!in_array($page_menu_link,$efa_menus)  || !isset($ipid)){
                    $efa_patient_header = false;
                    $efa_patient_menu = false;
                }
                
                $view->efa_patient_header = $efa_patient_header;
                $view->efa_patient_menu = $efa_patient_menu;
            }
            
            
            
        
        }
        
       
        
    }
}
