<?php

/**
 * 
 * 
 * TODO : reminder: Feb 15, 2018 : @author claudiu 
 * when you do some production changes to this file, 
 * add in the postDispatch the header to refresh the jstranslate on the upload(or any other actions that need a hard refresh) 
 * 
 *
 * TODO: reminder: 12.03.2018 serverMove remove the functions after the first upload
 */
class Pms_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    private $_acl = null;

    private $_logoutNow = false;
    // used in pre and post dispatch
    public function __construct(Zend_Acl $acl)
    {
        $this->_acl = $acl;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        Zend_Registry::set('acl', $this->_acl);
        
        $view = Zend_Layout::getMvcInstance()->getView();
        
        $action = strtolower($request->getActionName());
        $controller = strtolower($request->getControllerName());
        
        if ($this->_acl->isLogin()) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            
            if ($logininfo->usertype == "SA" && $logininfo->showinfo != "show") {
                $logininfo->showinfo = "hide";
            } else {
                $logininfo->showinfo = "show";
            }
            
            if (isset($_REQUEST['showinfo']) && $_REQUEST['showinfo'] == "show") {
                $logininfo->showinfo = "show";
            }
            if (isset($_REQUEST['showinfo']) && $_REQUEST['showinfo'] == "hide") {
                $logininfo->showinfo = "hide";
            }
            
            /**
             * 10.01.2018
             * if logininfo is SA or CA do a re-check to match the db
             * forced return;
             * fn will continue in postDispatch
             */
            if ($logininfo->usertype == "SA" || $logininfo->usertype == "CA") {
                
                $logarray = Doctrine_Query::create()->select('id,  usertype')
                    ->from('User')
                    ->where("id = ?")
                    ->fetchOne(array(
                    $logininfo->userid
                ), Doctrine_Core::HYDRATE_ARRAY);
                
                if ($logininfo->usertype != $logarray['usertype']) {
                    
                    $request->setDispatched(true);
                    
                    $this->_logoutNow = true;
                    
                    return;
                }
            }
            
            /**
             * 12.03.2018
             * hardCoded, can be removed after serve move has finished
             * this was not removed...
             * it remains here
             * it is used if you want to kick-out all nonSA's to a blank-text page
             * text is hardcoded in IndexController::logoutmaintenanceAction, logic is Pms_Plugin_Acl
             * you must set in the .ini => serverMove.redirectUser = 1
             * forced return;
             * fn will continue in postDispatch
             */
            if ($logininfo->usertype != 'SA' 
                && Zend_Registry::isRegistered('serverMove') 
                && ($serverMove_cfg = Zend_Registry::get('serverMove'))) 
            {
                if ($serverMove_cfg['redirectUser']) {
                    
                    $request->setDispatched(true);
                    
                    $this->_logoutNow = true;
                    
                    return;
                }
            }
            
            // WHY IS THIS HERE? COMMENT IT FOR NOW
            
            /*
             * $referred = Doctrine::getTable('MenuClient')->findBy('clientid', $logininfo->clientid);
             *
             * if($referred)
             * {
             * $view->msgnotify = "";
             * $refarr = $referred->toArray();
             *
             * if(count($refarr) < 1 && $logininfo->usertype != "SA")
             * {
             * $view->msgnotify = '$.jGrowl("You Dont Have Permissions Please Contact to SuperAdmin<br><a href=\'Index/logout\'>Logout</a>", { sticky: true });';
             * }
             * }
             */
        } else 
            if ($controller == 'cron' || $controller == 'elvi') { // cron controller does not require authentication
            } else {
                // entrust cut off
                /*
                 * @cla on 03.07.2018
                 * you are NOT loghed in, but you requested errorsController,
                 * log this errors first
                 */
                if ($controller == 'error' && $this->getResponse()->isException()) {
                    try {
                        try {
                            $exception = $this->getResponse()->getException();
                            // $message = PHP_EOL . "Exception : ". $exception->getMessage()
                            // . PHP_EOL . "Trace : " . $exception->getTraceAsString() . PHP_EOL;
                            if (is_array($exception)){
                                $exception = $exception[0]->getMessage();
                            } else {
                                $exception = $exception->getMessage();
                            }
                        } catch (Exception $e) {
                            
                            $exception = $e->getMessage();
                        }
                        
                        if ($logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log')) {
                            $logger->log("NOT loghed in, but errorController requested .... he is redirected to index/index .. trace the error" . print_r($exception, true), 3);
                        }
                    } catch (Exception $e) {}
                }
                
                $request->setControllerName('index');
                $request->setActionName('index');
                
                // TODO : i've added this return for readability... check if it's ok
                return;
            }
        
        // check if superadmin or super client admin
        if ($logininfo && ($logininfo->usertype == 'SA' || $logininfo->sca == '1' || $logininfo->multiple_clients == '1')) {
            
            if ($logininfo->sca == '1') {
                $usersca_clients = Usersa2Client::getusersaclients();
                foreach ($usersca_clients as $usca) {
                    $usersca_clients_str .= '"' . $usca['client'] . '",';
                }
                $sca_clients = 'c.id in (' . substr($usersca_clients_str, 0, - 1) . ')';
            } elseif ($logininfo->multiple_clients == '1') {
                $users_clients = User2Client::getuserclients();
                foreach ($users_clients as $us) {
                    $users_clients_str .= '"' . $us['client'] . '",';
                }
                $sca_clients = 'c.id in (' . substr($users_clients_str, 0, - 1) . ')';
            } else {
                $sca_clients = '1';
            }
            
            $clist = Doctrine_Query::create()
            	->select("id ,
					    lower(CONVERT(AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') using latin1)) as client_name,
					    AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as clientname")
                ->from('Client c')
                ->where('c.isdelete=0')
                ->andWhere($sca_clients);
            // TODO-2564 Ancuta: 30.09.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
            if ($logininfo->multiple_clients == '1') {
                $clist->andWhere('isactive = 0');
            }
            // --               
            $clist->orderBy('h__0 ASC');
            $client_list_arr = $clist->fetchArray();
            
            $clientarray = array(
                "0" => "Select Client"
            );
            
            $clients_arr = array();
            foreach ($client_list_arr as $key => $val) {
                $clientarray[$val['id']] = $val['clientname'];
                $clients_arr[] = $val['id'];
            }
            
            // get clients where the user is disabled
            $inactive_clients = array(
                '0'
            );
            if ($logininfo->multiple_clients == '1') {
                
                $connected_user_details = User::get_connected_user_settings($logininfo->userid);
                
                $connected_user = $logininfo->userid;
                if ($connected_user_details[$logininfo->userid]['status'] == "slave" && ! empty($connected_user_details[$logininfo->userid]['parent'])) {
                    $connected_user = $connected_user_details[$logininfo->userid]['parent'];
                }
                
                // Check to see if in all conected clients we have an active user
                $inactive_user_clients_data = Doctrine_Query::create()->select('clientid')
                    ->from('User')
                    ->where("duplicated_user = ?", $connected_user)
                    ->andwhereIn('clientid', $clients_arr)
                    ->andwhere('isactive = 1 OR isdelete = 1 ')
                    ->fetchArray();
                
                if (! empty($inactive_user_clients_data)) {
                    foreach ($inactive_user_clients_data as $k => $cl) {
                        $inactive_clients[] = $cl['clientid'];
                    }
                }
            }
            
            $view->inactive_user_clients = $inactive_clients;
            $view->adminclientarray = $clientarray;
            
            $logarray = Doctrine_Query::create()->select('u.id, 
				    u.user_title, 
				    u.last_name, 
				    u.first_name, 
				    us.topmenu')
                ->from('User u')
                ->leftJoin('u.UserSettings as us')
                ->where("id = ?", $logininfo->userid)
                ->fetchArray();
            
            User::beautifyName($logarray);
            
            $logininfo->UserSettings = $logarray[0]['UserSettings'];
            
            $logininfo->loguname = 
            // $view->loguname = $logarray [0] ['user_title'] . " " .$logarray [0] ['last_name'] . ", " . $logarray [0] ['first_name'];
            $view->loguname = $logarray[0]['nice_name'];
            $view->logutime = date("d.m.Y H:i", time());
        }
        
        // if no SA/SCA then it's "regular" user
        if ($logininfo && ($logininfo->clientid > 0 && $logininfo->usertype != 'SA' && $logininfo->sca != '1' && $logininfo->multiple_clients != '1')) {
            // $clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
            // $inarray = $clientlist->toArray();
            $inarray = Doctrine_Query::create()->select("id, inactivetime")
                ->from('Client')
                ->where("id = ?", $logininfo->clientid)
                ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            
            $seconds = $inarray['inactivetime'] * 60000; // minutes to microseconds
            
            $view->inactive_time = $seconds;
            
            if ($logininfo->usertype == 'CA') {
                // $news = Doctrine_Query::create()->select("*,AES_DECRYPT(news_title,'" . Zend_Registry::get('salt') . "') as news_title,AES_DECRYPT(news_content,'" . Zend_Registry::get('salt') . "') as news_content")->from('News')->where('clientid=' . $logininfo->clientid . ' and issystem=1 and acknowledge=0');
                
                // $newsexec = $news->execute();
                // $sysarray = $newsexec->toArray();
                
                // if($logininfo->setlater != 1 && count($sysarray) > 0)
                // {
                // if($sysarray [0] ['viewcount'] <= 2 && $sysarray [0] ['acknowledge'] != 1)
                // {
                // // $view->systemnews = "javascript:centerPopup({sr:'news/systemnews?popup=popup',ht:'250px',wt:'350px'});loadPopup();";
                // // $view->closebutton = '<a id="popupContactClose" onclick="closepopup()">x</a>';
                // }
                // }
            } else {
                
                $logininfo->setlate = 1;
            }
            
            $logarray = Doctrine_Query::create()->select('u.id, 
				    u.user_title, 
				    u.last_name, 
				    u.first_name, 
				    us.topmenu')
                ->from('User u')
                ->leftJoin('u.UserSettings as us')
                ->where("id = ?", $logininfo->userid)
                ->fetchArray();
            
            User::beautifyName($logarray);
            
            $logininfo->UserSettings = $logarray[0]['UserSettings'];
            
            $logininfo->loguname = 
            // $view->loguname = $logarray['user_title'] . " " . $logarray [0] ['last_name'] . ", " . $logarray [0] ['first_name'];
            $view->loguname = $logarray[0]['nice_name'];
            $view->logutime = date("d.m.Y H:i", time());
        } elseif ($logininfo && $logininfo->sca == '1' && $logininfo->clientid) {
            // $clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
            // $inarray = $clientlist->toArray();
            $inarray = Doctrine_Query::create()->select("id, inactivetime")
                ->from('Client')
                ->where("id = ?", $logininfo->clientid)
                ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            
            $seconds = $inarray['inactivetime'] * 60000; // minutes to microseconds
            
            $view->inactive_time = $seconds;
        } elseif ($logininfo && $logininfo->multiple_clients == '1' && $logininfo->clientid) {
            // $clientlist = Doctrine::getTable('Client')->find($logininfo->clientid);
            // $inarray = $clientlist->toArray();
            $inarray = Doctrine_Query::create()->select("id, inactivetime")
                ->from('Client')
                ->where("id = ?", $logininfo->clientid)
                ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
            
            $seconds = $inarray['inactivetime'] * 60000; // minutes to microseconds
            
            $view->inactive_time = $seconds;
        } else {
            $view->inactive_time = '6000000';
        }
        
        if (empty($inarray['inactivetime'])) {
            $inactivetime = 100;
        } else {
            $inactivetime = $inarray['inactivetime'];
        }
        
        $logininfo->inactivetime = $inactivetime;
        
        // @cla awaiting the HAR file....
        // update session
        // if($logininfo->userid > 0 && $controller != 'usersessions' && ($action != 'check' || $action != 'checknew')
        // && ($controller != 'index' && $action != 'logout'))
        if ($logininfo->userid > 0 && ($controller != 'usersessions' || ($action != 'check' && $action != 'checknew')) && ($controller != 'index' || $action != 'logout')) {
            // $setses = Doctrine::getTable('User')->find($logininfo->userid);
            // $setses->logintime = date("Y-m-d H:i:s");
            // $setses->save();
            $setses = Doctrine_Query::create()->update('User')
                ->set('logintime', '?', date("Y-m-d H:i:s"))
                ->where('id = ?', $logininfo->userid)
                ->execute();
            // time stored in session
            
            $logininfo->lastactive = time();
            
            // user session
            $usersession = new UserSessions();
            $usersession->update_session();
        }
    }

    /**
     * !! ATTENTION multiple exit points
     *
     * (non-PHPdoc)
     *
     * @see Zend_Controller_Plugin_Abstract::postDispatch()
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $requestinfo = new Zend_Session_Namespace('Login_Info');
        
        /**
         * 10.01.2018
         * preDispatch logic continuation
         */
        if ($this->_logoutNow) {
            
            /**
             * 12.03.2018
             * hardCoded, can be removed after serve move has finished
             * this was not removed...
             * it remains here
             * it is used if you want to kick-out all nonSA's to a blank-text page
             * text is hardcoded in IndexController::logoutmaintenanceAction, logic is Pms_Plugin_Acl
             * you must set in the .ini => serverMove.redirectUser = 1
             */
            if ($requestinfo->usertype != 'SA' && Zend_Registry::isRegistered('serverMove') && ($serverMove_cfg = Zend_Registry::get('serverMove'))) {
                if ($serverMove_cfg['redirectUser']) {
                    $request->setDispatched(true);
                    $this->_logoutNow = false;
                    $this->getResponse()->setRedirect(APP_BASE . 'index/logoutmaintenance');
                    return;
                }
            }
            
            /**
             * kick-out is user is nonSA, and maybe a mistake was made in php code
             */
            PatientPermissions::LogRightsError(null, 'ACL: usertype does NOT match, you may have in php a $logininfo->usertype = \'SA\'');
            $request->setDispatched(true);
            $this->getResponse()->setRedirect(APP_BASE . 'index/logout');
            return;
        }
        
        /**
         * if both clientid&userid are empty, do not try to fetch extra data, just return
         */
        if (empty($requestinfo->clientid) && empty($requestinfo->userid)) {
            return;
        }
        
        Zend_Registry::set('acl', $this->_acl);
        
        return;
    }

    /**
     * @cla on 20.04.2018, for ISPC-2176
     * replaced the _OLD with this one, check snv if you need getClientTabmenuAccess_OLD
     * it allows you to have the same patient-menu-link with multiple names
     *
     * @param number $clientid            
     * @return boolean
     */
    public static function getClientTabmenuAccess($clientid = 0)
    {
        $tablink = Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . "/" . Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        
        $tab_menu = Doctrine_Query::create()->select('tm.id')
            ->from('TabMenus tm')
            ->leftJoin('tm.TabMenuClient tmc ON (tm.id = tmc.menu_id AND tmc.clientid = ?)', $clientid)
            ->where("menu_link = ?", $tablink)
            ->andWhere("tmc.id IS NOT NULL")
            ->fetchArray();
        
        if (! empty($tab_menu)) {
            return true;
        } else {
            return false;
        }
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $userprivileges = Links::checkLinkPermission();
        if (! $userprivileges) {
            PatientPermissions::LogRightsError(true);
            $this->getResponse()->setRedirect(APP_BASE . 'error/previlege', 301); // redirect & exit
            return;
        }
    }
}

?>