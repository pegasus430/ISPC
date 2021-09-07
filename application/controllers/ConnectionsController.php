<?php

/**
 * @author  Jun 25, 2020  ancuta
 * * ISPC-2612 Ancuta 25.06.2020
 * // Maria:: Migration ISPC to CISPC 08.08.2020
 */
class ConnectionsController extends Pms_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->setActionsWithJsFile([
            'lists',
            'listconnections',
            'intenseconnectionslists',
            'addintenseconnection'
        ])->setActionsWithLayoutNew([
            /*
             * actions that will use layout_new.phtml
             * Actions With Patientinfo And Tabmenus also use layout_new.phtml
             */
            'lists',
            'listconnections',
            'intenseconnectionslists',
            'addintenseconnection',
        ]);

        // phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
    }

    /**
     * ISPC-2615 Ancuta 13.07.2020
     * @return string
     */
    public function checkwrongclientAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        //$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
        $userid = $this->logininfo->userid;
        $clientid = $this->logininfo->clientid;
       	$usertype = $this->logininfo->usertype;
        
       	//TODO-3501 Ancuta 12.10.2020
       	$modules = new Modules();
       	$show_connections = $modules->checkModulePrivileges("241", $clientid);
       	if( ! $show_connections){
       	    $this->_helper->json->sendJson(array());
       	    exit;
       	}
       	// -- 
       	
        $users_settings = User::get_connected_user_settings($userid);
        
        if($users_settings[$userid]['status'] == "slave")
        {
        	$parent_id = $users_settings[$userid]['parent'];
        	$duplicated_users = UserTable::getInstance()->findActiveDuplicatedUsersAndClients($parent_id);
        	
        }
        else
        {
        	$duplicated_users = UserTable::getInstance()->findActiveDuplicatedUsersAndClients($userid);
        }
        
        $connected_clients = array();
        if(!empty($duplicated_users))
        {
        	$connected_clients = array_column($duplicated_users, 'clientid');
        }

        $encid = $_REQUEST['id'];
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        switch($_REQUEST['action'])
        {
        	case 'findconn':
        		//print_r($_SESSION); exit;
		        $result = array();
		        if ($ipid)
		        {
			        if (!array_key_exists("stayincurrentpatient",$_SESSION['wrongClientSession'][$userid][$ipid]))
			        {
			        	$patients_linked = new PatientsLinked();
			        	$linked_patients = $patients_linked->get_related_patients($ipid);
			
			        	if(!empty($linked_patients))
			        	{
			        		$client_details = Doctrine_Query::create()
							->select(" id, AES_DECRYPT(client_name, :key) as client_name")
							->from('Client indexBy id')
							->where('isdelete = 0')
							->fetchArray(array("key" =>  Zend_Registry::get('salt')));
			        		
				        	foreach ($linked_patients as $k_link => $v_link)
				        	{
				        		$linked_ipids[] = $v_link['target'];
				        		$linked_ipids[] = $v_link['source'];
				        	}
			        	     	
			        		$cpts = Doctrine_Query::create()
			        		->select("pm.*, em.ipid, em.epid, em.clientid")
			        		->from('PatientMaster pm')
			        		->leftJoin('pm.EpidIpidMapping em')
			        		->where('pm.ipid = em.ipid')
			        		->andWhereIn("pm.ipid", $linked_ipids)
			        		->fetchArray();
			        		
			        		$activeconnpat = array();
			        		$isdischarged = array();
			        		foreach($cpts as $cpt)
			        		{
			        			if($cpt['ipid'] == $ipid && $cpt['isdischarged'] == '1')
			        			{
			        				$isdischarged[$ipid]['epid'] = $cpt['EpidIpidMapping']['epid'];
			        				$isdischarged[$ipid]['client_name'] = $client_details[$cpt['EpidIpidMapping']['clientid']]['client_name'];
			        			}
			        			else if($cpt['isdelete'] == '0' && $cpt['isdischarged'] == '0' && $cpt['isstandbydelete'] == '0' && $cpt['isstandby'] == '0' && $cpt['ipid'] != $ipid)
			        			{
			        				if((!empty($connected_clients) && in_array($cpt['EpidIpidMapping']['clientid'], $connected_clients)) || $usertype == 'SA')
			        				{
				        				$activeconnpat[$cpt['ipid']]['id'] = $cpt['id'];
				        				$activeconnpat[$cpt['ipid']]['epid'] = $cpt['EpidIpidMapping']['epid'];
				        				$activeconnpat[$cpt['ipid']]['clientid'] = $cpt['EpidIpidMapping']['clientid'];
				        				$activeconnpat[$cpt['ipid']]['client_name'] = $client_details[$cpt['EpidIpidMapping']['clientid']]['client_name'];
			        				}
			        			}
			        		}
			        		
			        		if(!empty($activeconnpat))
			        		{
			        			$result = array_merge($isdischarged, $activeconnpat);
			        		}
			        		else 
			        		{
			        			$_SESSION['wrongClientSession'][$userid][$ipid]['stayincurrentpatient'] = '1';
			        		}
			        		
			        	}
			        	
			        	}
		        	}
		       
			       	$this->_helper->json->sendJson($result);
	      			break;
       
        		case 'showconn':
	        		$html = $this->view->translate('patientwrongclientdescription').'<br /><br />';
	        		foreach($_POST as $pipid => $detipid)
	        		{
	        			if($pipid == $ipid)
	        			{
	        				$text = $this->view->translate('NO, stay in current discharge  pateitn from current client!');
	        				$html .= '<button class="currentpatient">'.$text.'</button><br />';
	        			}
	        			else 
	        			{
	        				$pencid = Pms_Uuid::encrypt($detipid['id']);
	        				//$html .= '<button class="changepatient" data-pid="'.$pencid.'" data-pclid="'.$detipid['clientid'].'" >go to '.$detipid['client_name'].', '.$detipid['epid'].'</button><br />';
	        				$html .= '<button class="changepatient" data-pid="'.$pencid.'" data-pclid="'.$detipid['clientid'].'" >'.$this->view->translate('go_to_active_client').''.$detipid['client_name'].'</button><br />';
	        			}
	        		}
	        		echo $html;
	        		break;
        		
        		case 'changeconn':
        		    if( APPLICATION_ENV != 'production' ) {
    	    			$path = str_ireplace('http://'.$_SERVER['HTTP_HOST'].'/', '', APP_BASE);
        			
	        			$redirect_url = $path . "patientcourse/patientcourse?id=" . $_POST['pencid'];

        		    } else {
        		        
        		        $redirect_url = "patientcourse/patientcourse?id=" . $_POST['pencid'];
        		    }
        			
        			echo $redirect_url;
        			break;
        		
        		default:
	        		$_SESSION['wrongClientSession'][$userid][$ipid]['stayincurrentpatient'] = '1';
	        		//print_r($_SESSION); exit;
	        		break;
        	}
       exit;
        	
        // FIRST CHECK IF IN SESSION - the current USER - saved  to stay in current patient
        
       
        // SECOND check if current patient is connected
        // If connected  - check if patient is discharged
        // If discharged - check if  patient has any OTHER connected active patients in OTHER clients
        // If connected active- show button for each connected active patient from OTHER clients "go to MESSE, Patient PNMS23213", "GO TO NR_Homecare, Pateint MOE1232"
        // Button to stay in current patient even if dicharged= "NO, stay in current discharge  pateitn from current client"  < If this is clicked - data is saved to session
        
        
        
        
        
        
        
        /* $resul = array();
          $resul['result'] = 'notConnected';
        
        echo json_encode($resul); */
    }
    
/*     public function listsnewcolumnsAction()
    {
        exit;
        $lists = Pms_CommonData::connection_lists();
        $sqls = '';
        $columns = array();
        foreach ($lists as $model => $ldata) {
//             if ($model && isset($ldata['list_ident_column']) && ! empty($ldata['list_ident_column'])) {
            $entity_pc = null;
            $entity_pc = new $model();
            
            $columns  = array();
            $columns = array_keys($entity_pc->getTable()->getColumns());
            
//             if(!in_array('connection_id',$columns) ){
                $sqls[ $ldata['db_name']] .= "ALTER TABLE `" . $ldata['db_table_name'] . "` ";
                $sqls[ $ldata['db_name']] .= " ADD `connection_id` INT(11) NULL DEFAULT NULL COMMENT 'id from connection_master' AFTER `" . $ldata['client_column'] . "`";
                $sqls[ $ldata['db_name']] .= ", ADD `master_id` INT(11) NULL DEFAULT NULL COMMENT 'id of the master entry - connection' AFTER `connection_id`;<br/><br/><br/>";
//             }
        }
        dd($sqls);
        dd($modelss,  $sqls,$columnsm);
    } */

    public function listsAction()
    {
        $lists = Pms_CommonData::connection_lists();
        $this->view->lists = $lists;
        
    }

    
    
    
    public function listconnectionsAction()
    {
        if (empty($_REQUEST['type'])) {
            $this->redirect(APP_BASE . 'connections/lists?flg=err');
            exit();
        }

        $current_list_type = $_REQUEST['type'];
        if(!empty($_REQUEST['category'])){
            $current_list_category  =  $_REQUEST['category'];
        }

        // get list details
        $lists = Pms_CommonData::connection_lists();
        if($current_list_category){
            $list_details = $lists[$current_list_type.'.'.$current_list_category];
        } else{
            $list_details = $lists[$current_list_type];
            
        }
        
        if (strpos($current_list_type, '.')){
            $lista_data_Array = explode('.', $current_list_type);
            $current_list_type = $lista_data_Array[0];
            $current_list_category = $lista_data_Array[1];
            $list_details['category'] = $current_list_category;
        }
        
        $list_details['type'] = $current_list_type;
        $this->view->connection_info = $list_details;

        // get all client details
        $client_details = Client::get_all_clients();

        
        // list all connections for the current list
        if($current_list_category){
            $connection_details = ConnectionMasterTable::_find_connection_details($current_list_type,null,$current_list_category);
        } else{
            $connection_details = ConnectionMasterTable::_find_connection_details($current_list_type);
        }

        $connection_followers_clients = array();
        foreach ($connection_details as $conncetion_id => $con_data) {
            $connection_details[$conncetion_id]['parent'] = $client_details[$con_data['clientid']]['client_name'];

            $connection_followers_clients[$conncetion_id] = array();
            foreach ($con_data['ConnectionFollowers'] as $k => $con_followers) {
                $connection_followers_clients[$conncetion_id][] = $client_details[$con_followers['clientid']]['client_name'];
            }

            $connection_details[$conncetion_id]['kids'] = implode(', ', $connection_followers_clients[$conncetion_id]);
        }

        $this->view->connection_data = $connection_details;
    }
    
    private function retainValues($values)
    {
        foreach($values as $key => $val)
        {
            $this->view->$key = $val;
        }
    }
    public function addlistconnectionAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = new Application_Form_ConnectionMaster(array());

            $post = $_POST;

            if ($form->validate($_POST)) {
                $result = $form->save_connection_details($_POST);

                if($_REQUEST ['category']){
                    $this->redirect ( APP_BASE . 'connections/listconnections?type=' . $_REQUEST ['type'].'&category='. $_REQUEST ['category']);
                } else {
                    $this->redirect ( APP_BASE . 'connections/listconnections?type=' . $_REQUEST ['type'] );
                }
                exit ();
            } else {

                $form->assignErrorMessages ();
                $current_connection['clientid'] = $_POST['clientid'];
                $current_connection['ConnectionFollowersClients'] = $_POST['ConnectionFollowersClients'];
                $this->view->current_connection = $current_connection;
            }
        }

        if (empty($_REQUEST['type'])) {
            $this->redirect(APP_BASE . 'connections/lists?flg=err');
            exit();
        }
        $current_list_type = $_REQUEST['type'];

        // get all client details
        $client_details = Client::get_all_clients();

        $connection_id = null;
        if (isset($_REQUEST['connection_id'])) {
            $connection_id = $_REQUEST['connection_id'];
        }
        
        if (isset($_REQUEST['category'])) {
            $list_category = $_REQUEST['category'];
        }

        // get all clents already involved in a current conection type-so they are no longer listed for a NEW connection
        if($list_category){
            $connection_details = ConnectionMasterTable::_find_connection_details($current_list_type,null,$list_category);
        } else{
            $connection_details = ConnectionMasterTable::_find_connection_details($current_list_type);
        }
        $connection_followers_clients = array();
        $used_clients = array();
        foreach ($connection_details as $db_conn_id => $con_data) {
            if ($connection_id != $db_conn_id) {
                $used_clients[] = $con_data['clientid'];
            }
            $connection_details[$db_conn_id]['parent_name'] = $client_details[$con_data['clientid']]['client_name'];
            $connection_details[$db_conn_id]['clientid'] = $con_data['clientid'];

            $connection_followers_clients[$db_conn_id] = array();
            foreach ($con_data['ConnectionFollowers'] as $k => $con_followers) {
                $connection_followers_clients[$db_conn_id][] = $client_details[$con_followers['clientid']]['client_name'];
                $connection_details[$db_conn_id]['ConnectionFollowersClients'][] = $con_followers['clientid'];
                if ($connection_id != $db_conn_id) {
                    $used_clients[] = $con_followers['clientid'];
                }
            }

            $connection_details[$db_conn_id]['kids_name'] = implode(', ', $connection_followers_clients[$db_conn_id]);
        }
        
        

        if (! empty($connection_details[$connection_id])) {
            $current_connection = $connection_details[$connection_id];
        } else {
            $current_connection['list_type'] = $current_list_type;
            if($list_category){
                $current_connection['list_category'] = $list_category;
            }
        }
        
        $lists = Pms_CommonData::connection_lists();
        if($list_category){
            $list_details = $lists[$current_list_type.'.'.$list_category];
        } else{
            $list_details = $lists[$current_list_type];
        }
        $current_connection['menu_name'] = $list_details['menu_name'];

        
        // remove clients used in OTHER connections of current type 
        foreach($client_details as $clid => $cldata){
            if(in_array($clid,$used_clients)){
                unset($client_details[$clid]);
            }
        }
        
        
        $this->view->current_connection = $current_connection;
        $this->view->clients = $client_details;
    }
    
    //Ancuta ISPC-2614
    public function intenseconnectionslistsAction()
    {
        
        $connections = IntenseConnectionsTable::_get_intense_connections();
        
        $connection = array();
        $client_details = Client::get_all_clients();
        foreach($connections as $k=>$data){
            $connection[$data['id']]['id'] = $data['id'];
            $connection[$data['id']]['date'] = date('d.m.Y',strtotime($data['create_date']));
            foreach($data['IntenseConnectionsClients'] as $k=>$ic ){
                if($ic['connection_parent'] == 'yes'){
                    $connection[$data['id']]['parent'] = $client_details[$ic['clientid']]['client_name'];
                } else{
                    $connection[$data['id']]['child'] = $client_details[$ic['clientid']]['client_name'];
                }
            }
        }
        $this->view->lists = $connection;
        // get data from DB  - wher we have connections created 
    }
    
    public function addintenseconnectionAction()
    { 
        if ($this->getRequest()->isPost()) {
            $form = new Application_Form_IntenseConnections(array());
            
            $post = $_POST;
            if ($form->validate($_POST)) {
                $result = $form->save_connection_details($_POST);
                
                $this->redirect ( APP_BASE . 'connections/intenseconnectionslists' );
                exit ();
                
            } else {
                
                $form->assignErrorMessages ();
                $connec = $_POST;
                // Nu raman salvate valorile la save j
                $this->view->current_connection = $connec;
                
            }
        }
        
        if(!empty($_REQUEST['connection_id'])){
            $intense_connection_id = $_REQUEST['connection_id'];
            $current_connection_array  = IntenseConnectionsTable::_find_connection_By_id($intense_connection_id);
            
            if(!empty($current_connection_array)){
                foreach($current_connection_array as $k=>$con){
                    
                    $current_connection['connection_id'] = $con['id'];
                    if(!empty($con['IntenseConnectionsClients'])){
                        foreach($con['IntenseConnectionsClients'] as $k=>$ic){
                            if($ic['connection_parent'] == 'yes'){
                                $current_connection['parent'] = $ic['clientid'];
                            } else{
                                $current_connection['child'] = $ic['clientid'];
                            }
                        }
                    }
                    
                    if(!empty($con['IntenseConnectionsOptions'])){
                        foreach($con['IntenseConnectionsOptions'] as $k=>$io){
                            $current_connection['connection_options'][$io['option_type']][] = $io['option_name'];
                        }
                    }
                }
            }
            
            $this->view->current_connection = $current_connection;
        }
        
        // get all active clients
        $clientsarray = Client::get_all_clients();
        $client_data = array();
        $client_data[''] = $this->translate('please select');
        foreach($clientsarray as $k => $ki){
            $client_data[$ki['id']] = $ki['client_name'];
        }
        
        $this->view->clientsarray = $client_data;
        $ver_blocks = Pms_CommonData::connection_lists_versorgers();
        $stamdaten_blocks = Pms_CommonData::connection_lists_stammdaten();
//         dd($stamdaten_blocks,$ver_blocks);
        // Options
        // Connected lists 
      /*   $intense_options_s = array(
            'patient_falls'=>'patient_falls',
            'patient_status'=>'patient_status',
            'patient_location'=>'patient_location',
            'patient_medication'=>'patient_medication',
            'patient_files'=>'patient_files',
            'patient_vw_work'=>'patient_vw_work',
            'patient_stamdaten' => $stamdaten_blocks,
            'patient_versorger' => $ver_blocks,
        );
        
        $intense_options = array(
            'patient_falls'=>array(
                'option_name' => 'Patient falls',
                'ConnectedLists' => array('DischargeMethod','DischargeLocation'),
                'option_models' =>array('PatientMaster','PatientActive','PatientStandby','PatientStandbyDetails','PatientStandbyDelete','PatientStandbyDeleteDetails','PatientReadmission') 
            ),
            
            'patient_status'=>array(
                'option_name' => "Patient status",
                "option_models" => array('PatientCrisisHistory'),
            ),
            
            
            'patient_location'=>array(
                'option_name' => 'Patient locations',
                'ConnectedLists' => array('Locations'),
                'option_tables' => array('PatientLocation')
            ), 
            'patient_medication'=>array(
                'option_name' => 'Patient medication',
                'ConnectedLists' => array('Medication'),
                'option_tables' => array('PatientLocation')
            ),
            'patient_files'=>array(
                'option_name' => 'Patient files',
            ),
            'patient_vw_work'=>array(
                'option_name' =>'Patient vw work',
                'ConnectedLists' => array('Voluntaryworkers'), // list for grund ?? 
                'option_tables' =>array('PatientHospizvizits')
            ),
            'patient_stamdaten' => $stamdaten_blocks,
            'patient_versorger' => $ver_blocks,
        );
         */
        
        $intense_options = Pms_CommonData::intense_connection_options();
//         DD($intense_options);   
        $this->view->intense_options = $intense_options;
        
        
    }
  
    
  
    
    
    ###########################################################
    ###########################################################
    ###########################################################
    ###########################################################
    public function sharepatientAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $linked_patients = new PatientsLinked();
        $client = new Client();
        
        
        if($_REQUEST['cid'] > 0)
        {
            $clientid = $_REQUEST['cid'];
        }
        else if($logininfo->clientid > 0)
        {
            $clientid = $logininfo->clientid;
        }
        else
        {
            $this->view->client_name = "Select Client";
            $clientid = '0';
        }
        
        //TODO-2686 Lore 29.11.2019
        //TODO-2686 Ancuta 19.12.2019
        if(($_REQUEST['cid'] > 0) && ($_REQUEST['cid'] != $logininfo->clientid)){
            $clientid = $logininfo->clientid;
            $this->_redirect(APP_BASE . 'connections/sharepatient');//TODO-2686 Ancuta 19.12.2019
            exit;
        }
        
        //get all client patients
        $client_pats = Doctrine_Query::create()
        ->select("p.ipid,e.epid")
        ->from('PatientMaster p')
        ->Where('isdelete = 0');
        $client_pats->leftJoin("p.EpidIpidMapping e");
        $client_pats->andWhere('e.clientid = ' . $clientid);
        
        
        $client_patients = $client_pats->fetchArray();
        
        $client_patients_ipids[] = '9999999999';
        foreach($client_patients as $patient_data)
        {
            $client_patients_ipids[] = $patient_data['ipid'];
            $ipid2epid[$patient_data['ipid']] = $patient_data['EpidIpidMapping']['epid'];
            $ipid2encid[$patient_data['ipid']] = Pms_Uuid::encrypt($patient_data['id']);
            
        }
        
        //get all client connected patients
        //TODO-2371
        $sp = Doctrine_Query::create()
        ->select('*')
        ->from('PatientsMarked')
        ->where('source = ?', $clientid)
        ->andWhereIn('ipid',$client_patients_ipids);
        $sp->andWhere('status != "c" ');  //TODO-2808 Ancuta 14.01.2020
        $s_patients = $sp->fetchArray();
        
        $ipids2client = array();
        $epids2client = array();
        foreach($s_patients as $kp=>$shared_p){
            $ipids2client[$ipid2encid[$shared_p['ipid']]][]= $shared_p['target'];
            $encrp2client[$ipid2encid[$shared_p['ipid']]][]= $shared_p['target'];
            if ( ! in_array($shared_p['target'],$epids2client[$ipid2epid[$shared_p['ipid']]])){
                $epids2client[$ipid2epid[$shared_p['ipid']]][]= $shared_p['target'];
            }
        }
        
        //set associated clients
        $this->view->connected_epids2client = $epids2client;
        $this->view->js_connected_epids2client= json_encode($epids2client);
        
        if(!empty($_REQUEST['st']) && !empty($_REQUEST['sid']))
        {
            $change_status = $marked_patients->change_status($_REQUEST['sid'], strtolower($_REQUEST['st']));
            
            if($change_status)
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&case=status&cid=' . $clientid);
            }
            else
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=err&case=status&cid=' . $clientid);
            }
        }
        
        //processs post data
        if($this->getRequest()->isPost())
        {
            $source_ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_POST['patientid']));
            
            
            //TODO-2686 Ancuta 19.12.2019
            // validate
            if(!isset($_POST['target_client']) || empty($source_ipid)){
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=no_target_client');
                exit;
            }
            
            //TODO-2808 Ancuta 14.01.2020 - do not calculate canceled - if canceled - then the connection can be allowed again
            $ipid2targetclient = array();
            $ipid2targetclient = Doctrine_Query::create()
            ->select('*')
            ->from('PatientsMarked')
            ->where('source = ?', $clientid)
            ->andWhere('target = ?', $_POST['target_client'])
            ->andWhere('ipid =?',$source_ipid)
            ->andWhere('status != "c" ')
            ->fetchArray();
            
            //TODO-3979 Ancuta 23.02.2021
            //check if ipid it is a target in PatientsLinked. If so - check if the source is in the tharget client from post .
              $ipid2targetipidclient = array();
            $ipid2targetipidclient = Doctrine_Query::create()
            ->select('*')
            ->from('PatientsLinked')
            ->where('target = ?', $source_ipid)
            ->fetchArray();
            
            $link_pats = array();
            foreach($ipid2targetipidclient as $k=>$pl){
                $link_pats[] = $pl['source'];
            }
            
            if(!empty($link_pats)){
                $pepidss =  array();
                $pepidss = Doctrine_Query::create()
                ->select('ipid,clientid')
                ->from('EpidIpidMapping')
                ->whereIn('ipid', $link_pats)
                ->fetchArray();
                
                foreach($pepidss as $k => $ep_data){
                    if($ep_data['clientid'] == $_POST['target_client']){
                        $ipid2targetclient[] = $ep_data;
                    }
                }  
            }
            //--
            
            if(!empty($ipid2targetclient)){
                //
                $client_dets = $client->getClientDataByid($_POST['target_client']);
                $target_client_details =$client_dets[0];
                
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=patient_exists_in_target&p='.$_POST['patientsearch_share'].'&tc='.$target_client_details['client_name']);
                exit;
            }
            //--
            //processing incoming patient id encrypted
            $mark_patient = new PatientsMarked();
            $mark_patient->ipid = $source_ipid;
            $mark_patient->intense_system = '1';
            $mark_patient->source = $clientid;
            $mark_patient->target = $_POST['target_client'];
            $mark_patient->copy = $_POST['allow_copy'];
            $mark_patient->copy_options = implode(',', $_POST['copy_options']);
            $mark_patient->copy_files = $_POST['copy_files'];
            $mark_patient->request = $_POST['request_share'];
            $mark_patient->shortcuts = implode(',', $_POST['shortcut']);
            $mark_patient->status = 'p';
            $mark_patient->save();
            
            if($mark_patient->id)
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&case=save&cid=' . $clientid);
            }
            else
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=err&case=save&cid=' . $clientid);
            }
        }
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);
        
        //set associated clients
        $this->view->associated_clients = $associated_clients;
        
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        $asociated_clients_ids = array_unique($asociated_clients_ids);
        asort($asociated_clients_ids);
        $asociated_clients_ids = array_values($asociated_clients_ids);
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //set clients details
        $this->view->clients_details = $clientsarray;
        
        //set curent client name
        $this->view->client_name = $clientsarray[$clientid]['client_name'];
        $this->view->clientid = $clientid;
        
        
        //get client verlauf shortcuts
        $allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');
        
        $cs = new Courseshortcuts();
        $ltrarray = $cs->getFilterCourseData();
        
        foreach($ltrarray as $k_letter => $v_letter)
        {
            // 				if(in_array($v_letter['shortcut'], $allowed_shortcuts))
                // 				{
            $final_letters[$v_letter['shortcut']] = $v_letter;
            // 				}
                }
                
                ksort($final_letters);
                $this->view->sharing_shortcuts = $final_letters;
                
                
                $all_requests = array();
                
                //get patients data
                $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
                $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
                $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
                $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
                $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
                $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
                $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
                $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
                $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
                $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
                $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
                $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
                
                // if super admin check if patient is visible or not
                if($logininfo->usertype == 'SA')
                {
                    $sql = "*, e.epid, e.clientid,";
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
                    $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                }
                
                //received patients linked
                $crpl = $linked_patients->client_received_linked_patients($client_patients_ipids,true);
                $this->view->client_received_patients_linked = $crpl;
                
                
                foreach($crpl as $link)
                {
                    $linked_ipids[] = $link['source'];
                    $linked_ipids[] = $link['target'];
                    
                    //ipids for gathering data
                    $all_requests[] = $link['source'];
                    $all_requests[] = $link['target'];
                }
                
                $all_requests = array_unique($all_requests);
                
                if(empty($all_requests))
                {
                    $all_requests[] = '999999999999';
                }
                
                $patient = Doctrine_Query::create()
                ->select($sql)
                ->from('PatientMaster p')
                ->where("p.isdelete = 0")
                ->andWhereIn('p.ipid', $all_requests)
                ->leftJoin("p.EpidIpidMapping e");
                
                $pat_details = $patient->fetchArray();
                
                foreach($pat_details as $k_pat_details => $v_pat_details)
                {
                    $patient_details[$v_pat_details['ipid']] = $v_pat_details;
                }
                
                $this->view->patient_details = $patient_details;
                
                $cd = $client->getClientData();
                
                foreach($cd as $k_client => $v_client)
                {
                    $clients_data[$v_client['id']] = $v_client;
                }
                $this->view->clients_data = $clients_data;
    }
    
    public function fetchsentrequestAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $linked_patients = new PatientsLinked();
        $client = new Client();
        
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);
        
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        $asociated_clients_ids = array_values(array_unique($asociated_clients_ids));
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //get "shared to" patients
        $all_requests = array();
        $sent_requests = $marked_patients->sent_requests_get($clientid,false,true);
        
        foreach($sent_requests as $k_shared_pat => $v_sent_requests)
        {
            //ipids for gathering data
            $all_requests[] = $v_sent_requests['ipid'];
        }
        
        //get involved patients data
        //get patients data
        $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
        $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
        $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
        $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
        $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
        $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
        $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
        $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
        $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
        $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
        $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
        $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
        
        // if super admin check if patient is visible or not
        if($logininfo->usertype == 'SA')
        {
            $sql = "*, e.epid, e.clientid,";
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
            $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        }
        
        $all_requests = array_unique($all_requests);
        
        if(empty($all_requests))
        {
            $all_requests[] = '999999999999';
        }
        
        $patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        // 				->where("p.isdelete = 0")
        ->andWhereIn('p.ipid', $all_requests)
        ->leftJoin("p.EpidIpidMapping e");
        
        $pat_details = $patient->fetchArray();
        
        foreach($pat_details as $k_pat_details => $v_pat_details)
        {
            $patient_details[$v_pat_details['ipid']] = $v_pat_details;
        }
        
        $this->view->patient_details = $patient_details;
        
        $requests_count = count($sent_requests);
        $grid = new Pms_Grid($sent_requests,1,$requests_count,"list_ishare_sent_requests.html");
        $grid->appbase = APP_BASE;
        $grid->res_filepath = RES_FILE_PATH;
        $grid->client_details = $clientsarray;
        $this->view->sent_requests_list_grid = $grid->renderGrid();
        //			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        if(!empty($sent_requests))
        {
            $response['callBackParameters']['sent_requests_list'] =$this->view->render('connections/fetchsentrequest.html');
        }
        else
        {
            $response['callBackParameters']['sent_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate("no_sent_requests").'</td>
				</tr>';
            
            
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function fetchreceivedrequestAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $linked_patients = new PatientsLinked();
        $client = new Client();
        
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;
        
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);

        
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        $asociated_clients_ids = array_values(array_unique($asociated_clients_ids));
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        //get "shared from" patients
        $all_requests = array();
        $received_requests = $marked_patients->received_requests_get($clientid,false,true);
        
        
        foreach($received_requests as $k_received_pat => $v_received_requests)
        {
            //ipids for gathering data
            $all_requests[] = $v_received_requests['ipid'];
        }
  
        if(!empty($all_requests)){
            
            //get involved patients data
            //get patients data
            $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
            $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
            $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
            $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
            $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
            $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
            $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
            $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
            $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
            $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
            $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
            $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
            $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
            
            // if super admin check if patient is visible or not
            if($logininfo->usertype == 'SA')
            {
                $sql = "*, e.epid, e.clientid,";
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
                $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
            }
            
            $all_requests = array_unique($all_requests);
            
            if(empty($all_requests))
            {
                $all_requests[] = '999999999999';
            }
            
            $patient = Doctrine_Query::create()
            ->select($sql)
            ->from('PatientMaster p')
            // 				->where("p.isdelete = 0")
            ->andWhereIn('p.ipid', $all_requests)
            ->leftJoin("p.EpidIpidMapping e");
            
            $pat_details = $patient->fetchArray();
        }

        $patient_details = array();
        $client_patients_ipids = array();
        foreach($pat_details as $k_pat_details => $v_pat_details)
        {
            $patient_details[$v_pat_details['ipid']] = $v_pat_details;
            $client_patients_ipids[] = $v_pat_details['ipid'];      //ISPC-2592 Lore 23.06.2020
        }

        $this->view->patient_details = $patient_details;
        
        //ISPC-2592 Lore 23.06.2020
        $cspl = $linked_patients->client_sent_linked_patients($client_patients_ipids,true);
        
        $cspl_ipids = array();
        $target_ipids = array();
        foreach($cspl as $k_cspl => $v_cspl)
        {
            $cspl_ipids[] = $v_cspl['target'];
            $target_ipids[$v_cspl['source']] = $v_cspl['target'];
        }
        
        if(!empty($cspl_ipids))
        {
            $patient_cspl = Doctrine_Query::create()
            ->select($sql)
            ->from('PatientMaster p')
            // 				->where("p.isdelete = 0")
            ->andWhereIn('p.ipid', $cspl_ipids)
            ->leftJoin("p.EpidIpidMapping e");
            $pat_details_cspl = $patient_cspl->fetchArray();
            $patient_details_cspl = array();
            foreach($pat_details_cspl as $k_pat_details_cspl => $v_pat_details_cspl)
            {
                $patient_details_cspl[$v_pat_details_cspl['ipid']] = $v_pat_details_cspl;
            }
        }
        $this->view->target_ipids = $target_ipids;
        $this->view->pat_details_cspl = $patient_details_cspl;
        //.
        
        
        $requests_count = count($received_requests);
        $grid = new Pms_Grid($received_requests,1,$requests_count,"list_ishare_received_requests.html");
        $grid->appbase = APP_BASE;
        $grid->res_filepath = RES_FILE_PATH;
        $grid->client_details = $clientsarray;
        $this->view->received_requests_list_grid = $grid->renderGrid();
        //			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        if(!empty($received_requests))
        {
            $response['callBackParameters']['received_requests_list'] =$this->view->render('connections/fetchreceivedrequest.html');
        }
        else
        {
            $response['callBackParameters']['received_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_received_requests').'</td>
				</tr>';
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function fetchsharedrequestAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $linked_patients = new PatientsLinked();
        $client = new Client();
        
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;
        
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);
        
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        $asociated_clients_ids = array_values(array_unique($asociated_clients_ids));
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //get all client patients
        $client_pats = Doctrine_Query::create()
        ->select("p.ipid")
        ->from('PatientMaster p')
        // 				->Where('isdelete = 0')
        ;
        $client_pats->leftJoin("p.EpidIpidMapping e");
        $client_pats->andWhere('e.clientid = ' . $clientid);
        $client_patients = $client_pats->fetchArray();
        
        $client_patients_ipids[] = '9999999999';
        foreach($client_patients as $patient_data)
        {
            $client_patients_ipids[] = $patient_data['ipid'];
        }
        
        //shared patients linked
        $all_requests = array();
        $cspl = $linked_patients->client_sent_linked_patients($client_patients_ipids,true);
        $this->view->client_sent_patients_linked = $cspl;
        
        foreach($cspl as $k_link => $link)
        {
            //ipids for gathering data
            $all_requests[] = $link['source'];
            $all_requests[] = $link['target'];
        }
        
        //get involved patients data
        //get patients data
        $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
        $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
        $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
        $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
        $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
        $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
        $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
        $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
        $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
        $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
        $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
        $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
        
        // if super admin check if patient is visible or not
        if($logininfo->usertype == 'SA')
        {
            $sql = "*, e.epid, e.clientid,";
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
            $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        }
        
        $all_requests = array_unique($all_requests);
        
        if(empty($all_requests))
        {
            $all_requests[] = '999999999999';
        }
        
        $patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        // 				->where("p.isdelete = 0")
        ->andWhereIn('p.ipid', $all_requests)
        ->leftJoin("p.EpidIpidMapping e");
        
        $pat_details = $patient->fetchArray();
        
        foreach($pat_details as $k_pat_details => $v_pat_details)
        {
            $patient_details[$v_pat_details['ipid']] = $v_pat_details;
        }
        
        $this->view->patient_details = $patient_details;
        
        $requests_count = count($cspl);
        $grid = new Pms_Grid($cspl,1,$requests_count,"list_ishare_shared_requests.html");
        $grid->appbase = APP_BASE;
        $grid->res_filepath = RES_FILE_PATH;
        $grid->client_details = $clientsarray;
        $this->view->shared_requests_list_grid = $grid->renderGrid();
        //			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        if(!empty($cspl))
        {
            $response['callBackParameters']['shared_requests_list'] =$this->view->render('connections/fetchsharedrequest.html');
        }
        else
        {
            $response['callBackParameters']['shared_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_shared_patients').'</td>
				</tr>';
        }
        
        echo json_encode($response);
        exit;
    }
    
    public function fetchreceivedsharedrequestAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $linked_patients = new PatientsLinked();
        $client = new Client();
        
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;
        
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);
        
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //othewise we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        $asociated_clients_ids = array_values(array_unique($asociated_clients_ids));
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //get all client patients
        $client_pats = Doctrine_Query::create()
        ->select("p.ipid")
        ->from('PatientMaster p')
        // 				->Where('isdelete = 0')
        ;
        $client_pats->leftJoin("p.EpidIpidMapping e");
        $client_pats->andWhere('e.clientid = ' . $clientid);
        $client_patients = $client_pats->fetchArray();
        
        $client_patients_ipids[] = '9999999999';
        foreach($client_patients as $patient_data)
        {
            $client_patients_ipids[] = $patient_data['ipid'];
        }
        
        //received shared patients linked
        $crpl = $linked_patients->client_received_linked_patients($client_patients_ipids,true);
        $this->view->client_received_patients_linked = $crpl;
        
        
        foreach($crpl as $link)
        {
            $linked_ipids[] = $link['source'];
            $linked_ipids[] = $link['target'];
            
            //ipids for gathering data
            $all_requests[] = $link['source'];
            $all_requests[] = $link['target'];
        }
        
        //get involved patients data
        //get patients data
        $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
        $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
        $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
        $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
        $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
        $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
        $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
        $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
        $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
        $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
        $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
        $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
        
        // if super admin check if patient is visible or not
        if($logininfo->usertype == 'SA')
        {
            $sql = "*, e.epid, e.clientid,";
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
            $sql .= "IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        }
        
        $all_requests = array_unique($all_requests);
        
        if(empty($all_requests))
        {
            $all_requests[] = '999999999999';
        }
        
        $patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        // 				->where("p.isdelete = 0")
        ->andWhereIn('p.ipid', $all_requests)
        ->leftJoin("p.EpidIpidMapping e");
        
        $pat_details = $patient->fetchArray();
        
        foreach($pat_details as $k_pat_details => $v_pat_details)
        {
            $patient_details[$v_pat_details['ipid']] = $v_pat_details;
        }
        
        $this->view->patient_details = $patient_details;
        
        $requests_count = count($crpl);
        $grid = new Pms_Grid($crpl,1,$requests_count,"list_ishare_shared_requests.html");
        $grid->appbase = APP_BASE;
        $grid->res_filepath = RES_FILE_PATH;
        $grid->client_details = $clientsarray;
        $this->view->received_shared_requests_list_grid = $grid->renderGrid();
        //			$this->view->navigation = $grid->dotnavigation("groupnavigation.html",5,$_GET['pgno'],$limit);
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        if(!empty($crpl))
        {
            $response['callBackParameters']['received_shared_requests_list'] =$this->view->render('connections/fetchreceivedsharedrequest.html');
        }
        else
        {
            $response['callBackParameters']['received_shared_requests_list'] = '<tr id="TableTwo_Trtwo" class="row" >
					<td id="TableTwo_Trtwo_tdOne" valign="top" colspan="9" style="text-align: center;">'.$this->view->translate('no_shared_patients').'</td>
				</tr>';
        }
        
        echo json_encode($response);
        exit;
    }
    
    
    public function processpatientAction()
    {
        
        set_time_limit(0);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $shareid = $_REQUEST['sid'];
        
        $hidemagic = Zend_Registry::get('hidemagic');
        
        if($_REQUEST['cid'] > 0)
        {
            $clientid = $_REQUEST['cid'];
        }
        else if($logininfo->clientid > 0)
        {
            $clientid = $logininfo->clientid;
        }
        else
        {
            $this->view->client_name = "Select Client";
            $clientid = '0';
        }
        
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        $share_patients = new PatientsShare();
        
        $share_details = $marked_patients->share_get($_REQUEST['sid'], $clientid);
        if(!empty($share_details)){
            $s_pid= $share_details['0']['ipid'];
            
            $linked_patients = new PatientsLinked();
            $existing_connections = $linked_patients->linked_patients($s_pid);
            //     			dd($existing_connections);
        }
        
        
        
        if($this->getRequest()->isPost())
        {
            if(!empty($_REQUEST['sid']))
            {
                // check intense connection between clients 
                //get shared(marked) request
                $marked_patient = $marked_patients->share_get($_REQUEST['sid']);
                $client_intense_connection = IntenseConnectionsTable::_find_intense_connectionBetweenClients($marked_patient[0]['source'],$marked_patient[0]['target'],null,true);
                $allowed_intense_options = array();
                
                if(!empty($client_intense_connection)){
                    // get all "blocks"  that have intense connection - so we can sync 
                    foreach($client_intense_connection as $connection_id => $connection_data){
                        foreach($connection_data['IntenseConnectionsOptions'] as $ko=>$opt )
                            $allowed_intense_options[] = $opt['option_name'];
                    }
                }
                
                
                $copy_options = explode(',', $marked_patient[0]['copy_options']);
                $medications = new PatientDrugPlan();
                
                //copy patient data procedure
                if($_POST['combine'] === '0')// CREATE NEW PATIENT 
                {
                    //load required models
                    $patient_master = new PatientMaster();
                    $epid_ipid = new EpidIpidMapping();
                   
                    
                    
                    $diagnosis = new PatientDiagnosis();
                    
                    
                    
                    //Patient Master general copy
                    $save_pm = $patient_master->clone_record($marked_patient[0]['ipid'], $marked_patient[0]['target']);
                    if(!empty($client_intense_connection) && in_array('patient_falls',$allowed_intense_options) ){
                        $patient_master->intense_connection_patient_admissions($marked_patient[0]['ipid'],$save_pm['ipid']);
                    }
                    
                    
                    //Create patient new epid based on client id and save it in epid_ipid
                    $target_epid = Pms_Uuid::GenerateEpid($marked_patient[0]['target']);
                    
                    $data['epid'] = $target_epid;
                    $data['ipid'] = $save_pm['ipid'];
                    $save_epid = $epid_ipid->epid_cloned_patient($data, $marked_patient[0]['target']);
                    
                    if(in_array('1', $copy_options))  //copy stammdaten data
                    {
                        
                        
//                         if(!empty($client_intense_connection) && in_array('PatientLocation',$allowed_intense_options) ){
                            //                             $patient_master->intense_connection_patient_admissions($marked_patient[0]['ipid'],$save_pm['ipid']);
//                         }
                        
                        //STAMDATEM
                         /* 
                        [16] => PatientMaster
                        [17] => PatientOrientation
                        [18] => PatientMobility2
                        [19] => PatientMedipumps
                        [20] => PatientHospizverein
                        [21] => PatientTherapieplanung
                        [22] => PatientMaintainanceStage
                        [23] => PatientSupply
                        [24] => PatientReligions
                        [25] => PatientGermination
                        [26] => PatientLives
                        [27] => PatientAcp
                        [28] => PatientMobility
                        [29] => PatientEmploymentSituation
                        [30] => Stammdatenerweitert_orientierung
                        [31] => Stammdatenerweitert_wunsch
                        [32] => Stammdatenerweitert_ernahrung
                        [33] => Stammdatenerweitert_hilfsmittel
                        [34] => Stammdatenerweitert_stastszugehorigkeit
                        [35] => Stammdatenerweitert_familienstand
                        [36] => Stammdatenerweitert_kunstliche
                        [37] => Stammdatenerweitert_ausscheidung
                        [38] => Stammdatenerweitert_vigilanz
                         
                         */
                        
                        
                        
                        //SAPV Verordung copy:: SPECIAL CLONE
                        $sapvverordnung = new SapvVerordnung();
                        $pc_listener = $sapvverordnung->getListener()->get('IntenseConnectionListener');
    					$pc_listener->setOption('disabled', true);
                        $sapvs = $sapvverordnung->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
    					$pc_listener->setOption('disabled', false);
                        
    					
                        //ContactPerson copy :: SPECIAL CLONE 
    					$contact_person = new ContactPersonMaster();
    					$contact_persons = $contact_person->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'],$marked_patient[0]['source'],$marked_patient[0]['target']);
                        
                        // All patient blocks that need to be cloned
                        $models_array = array(
                            'PatientTherapieplanung',
                            'PatientOrientation',
                            'PatientMobility',
                            'PatientMobility2',
                            'PatientLives',
                            'PatientHospizverein',
                            'PatientGermination',
                            'PatientEmploymentSituation',
                            'PatientCrisisHistory',
                            'PatientSupply',
                            'PatientMoreInfo',
                            'PatientReligions',
                            'Stammdatenerweitert',
                            /*
                             Stammdatenerweitert_orientierung
                             Stammdatenerweitert_wunsch
                             Stammdatenerweitert_ernahrung
                             Stammdatenerweitert_hilfsmittel
                             Stammdatenerweitert_stastszugehorigkeit
                             Stammdatenerweitert_familienstand
                             Stammdatenerweitert_kunstliche
                             Stammdatenerweitert_ausscheidung
                             Stammdatenerweitert_vigilanz
                             */
                            'PatientMaintainanceStage',
                            //'PatientAcp',
                            
                        );
                        
                        $params = array();
                        $params['excluded_columns'] = array('id','ipid','change_date','change_user');
                        $params['source_ipid'] = $marked_patient[0]['ipid'];
                        $params['target_ipid'] = $save_pm['ipid'];
                        
                        foreach($models_array as $model_name){
                            $params['model_name'] = $model_name;
                            $patient_master->copy_source2target($params);
                        }
              
                        if( ! empty($client_intense_connection) && in_array('PatientLocation',$allowed_intense_options) ) {
                            //Locations
                            $PatientLocation = new PatientLocation();
                            $patient_locations_arr = $PatientLocation->clone_records($marked_patient[0]['ipid'],$save_pm['ipid'],$marked_patient[0]['target']);
                        }
                        
                        
                        
                        // VERSORGER
                        /*
                        [5] => FamilyDoctor
                        [6] => PatientChurches
                        [7] => PatientRemedies
                        [8] => PatientPhysiotherapist
                        [9] => PatientSpecialists
                        [10] => PatientPharmacy
                        [11] => PatientPflegedienste
                        [12] => PatientHealthInsurance
                        [13] => PatientSupplies
                        [14] => PatientSuppliers
                        [15] => PatientHomecare 
                        */
                        
                        // Family Doctor copy
                        $familydoc = new FamilyDoctor();
                        $family_doctor = $familydoc->clone_record($save_pm['familydoc_id'], $marked_patient[0]['target']); //TODO-2413 remove ipid as param - not needed in cline function
                        
                        //Patient health insurance copy
                        $PatientHealthInsurance = new PatientHealthInsurance();// subdivisions !!!! 
                        $patient_health_insurance = $PatientHealthInsurance->clone_record($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        //Pflegedienste
                        $patient_pflegedienste = new PatientPflegedienste();
                        $pflege = $patient_pflegedienste->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        
                        //PatientSpecialists
                        $PatientSpecialists_obj = new PatientSpecialists();// types  !!!!!
                        $PatientSpecialists = $PatientSpecialists_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']); 
                        
                        
                        //Ehrenamtliche
                        $patient_voluntary = new PatientVoluntaryworkers();
                        $volunteer = $patient_voluntary->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        //Apotheke
                        $patient_pharmacy = new PatientPharmacy();
                        $pharmacy = $patient_pharmacy->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        //PatientHomecare
                        $PatientHomecare_obj = new PatientHomecare();
                        $PatientHomecare = $PatientHomecare_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        //PatientPhysiotherapist
                        $PatientPhysiotherapist_obj = new PatientPhysiotherapist();
                        $PatientPhysiotherapist = $PatientPhysiotherapist_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        //PatientSupplies
                        $PatientSupplies_obj = new PatientSupplies();
                        $PatientSupplies = $PatientSupplies_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        //PatientSuppliers
                        $PatientSuppliers_obj = new PatientSuppliers();
                        $PatientSuppliers = $PatientSuppliers_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        //PatientChurches
                        $PatientChurches_obj = new PatientChurches();
                        $PatientChurches = $PatientChurches_obj->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        
                        
                        //Update PM with follownig data:
                        $p_master = Doctrine::getTable('PatientMaster')->findOneByIpid($save_pm['ipid']);
                        
                        //#1 Family Doctor
                        $p_master->familydoc_id = $family_doctor;
                        
                        //save
                        $p_master->save();
                    }
                    
                    if(in_array('2', $copy_options))  //copy diagnosis data
                    {
                        //Diagnosis
                        $diag = $diagnosis->clone_records($marked_patient[0]['ipid'], $marked_patient[0]['source'], $save_pm['ipid'], $marked_patient[0]['target']);
                    }
                    
                    if(in_array('3', $copy_options))  //copy medications data
                    {
                        //Medications
                        $medis = $medications->clone_records($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target'], $marked_patient[0]['source']);
                        // insert in patient_drugplan_share - medication from  source, and target
                        $source_medication = $medications->getPatientAllDrugs($marked_patient[0]['ipid']);
                        
                        if(!empty($source_medication)){
                            foreach($source_medication as $k=>$dr){
                                $insert_source_meds = new PatientDrugPlanShare();
                                $insert_source_meds->ipid = $marked_patient[0]['ipid'];
                                $insert_source_meds->drugplan_id = $dr['id'];
                                $insert_source_meds->create_date = date("Y-m-d H:i:s",time());
                                $insert_source_meds->save();
                            }
                        }
                    }
                    
                    
                    //save shortcuts in copy mode (non combine)
                    $save_shared_shortcuts = $share_patients->save_shortcuts($_REQUEST['sid'], $_POST, $save_pm['ipid'],1);// 1= save as  connetion done through intense system
                    
                    
                    
                    // COPY FILES
                    if($marked_patient['0']['copy_files'] == "1"){
                        
                        // 						    $copy_files = $share_patients->save_files($marked_patient[0]['ipid'], $save_pm['ipid'], $marked_patient[0]['target']);
                        
                        // copy_files
                        // selectat toate fisierele pacientului sursa
                        
                        // copy in target patient and chack with source id if patient file was transfered alredy.
                        
                        
                        // de facut un cron
                        // - luam toti pacienti cu fisiere "sharate"
                        // si pt fieecare copiem fisierele de la soursa la target
                        
                        //
                        
                    }
                }
                else
                {
                    if(!empty($_POST['patientid'])){
                        //save shortcuts in share mode (combine mode)
                        $save_shared_shortcuts = $share_patients->save_shortcuts($_REQUEST['sid'], $_POST,false,1); //matched pat id is in the post>ipid to match
                        $target_ipid = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_POST['patientid']));
                        
                        // check if source i
                        
                        
                        //        						  dd($existing_connections);
                        
                        if(in_array('3', $copy_options))  //copy medications data
                        {
                            // insert in patient_drugplan_share - medication from  source, and target
                            $target_ipid_med = $target_ipid;
                            $meds_ipids = array($marked_patient[0]['ipid'],$target_ipid_med );
                            
                            $share_drug_src = Doctrine_Query::create()
                            ->select("*")
                            ->from('PatientDrugPlanShare')
                            ->whereIn('ipid',$meds_ipids);
                            $share_drug_src_arra = $share_drug_src->fetchArray();
                            
                            foreach($share_drug_src_arra as $k=>$s)
                            {
                                $existing_inshare_med[$s['ipid']][] = $s['ipid'].$s['drugplan_id'];
                            }
                            
                            $source_medication = $medications->getPatientAllDrugs($marked_patient[0]['ipid']);
                            if(!empty($source_medication)){
                                foreach($source_medication as $k=>$dr){
                                    // check if data already exists in
                                    $ident_src = $marked_patient[0]['ipid'].$dr['id'];
                                    if( !in_array($ident_src,$existing_inshare_med[$marked_patient[0]['ipid']])){
                                        $insert_source_meds = new PatientDrugPlanShare();
                                        $insert_source_meds->ipid = $marked_patient[0]['ipid'];
                                        $insert_source_meds->drugplan_id = $dr['id'];
                                        $insert_source_meds->create_date = date("Y-m-d H:i:s",time());
                                        $insert_source_meds->save();
                                    }
                                }
                            }
                            
                            $target_medication = $medications->getPatientAllDrugs($target_ipid_med);
                            if(!empty($target_medication)){
                                foreach($target_medication as $tk=>$tdr){
                                    // check if data already exists in
                                    $ident_tr = $target_ipid_med.$tdr['id'];
                                    if( !in_array($ident_tr,$existing_inshare_med[$target_ipid_med])){
                                        $insert_target_meds = new PatientDrugPlanShare();
                                        $insert_target_meds->ipid = $target_ipid_med;
                                        $insert_target_meds->drugplan_id = $tdr['id'];
                                        $insert_target_meds->create_date = date("Y-m-d H:i:s",time());
                                        $insert_target_meds->save();
                                    }
                                }
                            }
                        }
                        
                    } else{
                        $this->_redirect(APP_BASE . 'connections/processpatient?flg=err&sid=' . $_REQUEST['sid']);
                    }
                }
                if($save_shared_shortcuts)
                {
                    $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&case=ssave&cid=' . $clientid);
                }
                else
                {
                    $this->_redirect(APP_BASE . 'connections/sharepatient?flg=err&case=ssave&cid=' . $clientid);
                }
            }
        }
        
        //get marked for share
        $copy_options_master = array('1' => 'Stammdaten', '2' => 'Diagnosen', '3' => 'Medikamente');//TODO-3838 CRISTI C. 08.02.2021
        
        
        $coptions = explode(',', $share_details[0]['copy_options']);
        foreach($coptions as $option)
        {
            $copied_data[] = $copy_options_master[$option];
        }
        $this->view->copied_options = $copied_data;
        $this->view->share_details = $share_details;
        
        //array with clients ids for gathering client data (source and target)
        $required_clients_ids = array($share_details[0]['source'], $share_details[0]['target']);
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $required_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //set clients details
        $this->view->clients_details = $clientsarray;
        
        //set curent client name
        $this->view->client_name = $clientsarray[$clientid]['client_name'];
        $this->view->source_client_name = $clientsarray[$share_details[0]['source']]['client_name'];
        
        
        $shortcuts = $marked_patients->allowed_target_shortcuts($share_details[0]['source'], $share_details[0]['target'], explode(',', $share_details['0']['shortcuts']));
        
        $this->view->source_shortcuts = $shortcuts['source_shortcuts'];
        $this->view->target_shortcuts = $shortcuts['target_shortcuts'];
        $this->view->allowed_shortcuts = $shortcuts['allowed_shortcuts'];
        
        //get patient data by ipid
        $sql = "p.id, e.epid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
        $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
        $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
        $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
        $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
        $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
        $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
        $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
        $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
        $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
        $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
        $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
        
        // if super admin check if patient is visible or not
        if($logininfo->usertype == 'SA')
        {
            $sql = "*, p.id, e.epid,";
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
            $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
        }
        
        $patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        ->where("p.isdelete = 0")
        ->andWhere('p.ipid LIKE "' . $share_details[0]['ipid'] . '"')
        ->leftJoin("p.EpidIpidMapping e")
        ;
        
        $pat_details = $patient->fetchArray();
        $this->view->patient_details = $pat_details[0]['EpidIpidMapping']['epid'] . ', ' . $pat_details[0]['first_name'] . ' ' . $pat_details[0]['last_name'];
    }
    
    //edit share in patient_marked untill is accepted
    public function editshareAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $hidemagic = Zend_Registry::get('hidemagic');
        $assoc_group = new AssociatedGroups();
        $assoc_clients = new GroupAssociatedClients();
        $marked_patients = new PatientsMarked();
        
        $marked = $marked_patients->share_get($_REQUEST['sid'], false);
        
        $this->view->marked = $marked;
        
        if($marked[0]['status'] == 'a')
        {
            $this->_redirect(APP_BASE . 'connections/sharepatient?flg=err&case=edit&cid=' . $clientid);
        }
        
        
        if($_REQUEST['cid'] > 0)
        {
            $clientid = $_REQUEST['cid'];
        }
        else if($logininfo->clientid > 0)
        {
            $clientid = $logininfo->clientid;
        }
        else
        {
            $this->view->client_name = "Select Client";
            $clientid = '0';
        }
        
        //get associated clients of current clientid
        $associated_clients = $assoc_clients->associated_clients_get($clientid, true);
        
        //set associated clients
        $this->view->associated_clients = $associated_clients;
        //array with clients ids for gathering client data
        if(is_numeric($clientid))
        {
            $asociated_clients_ids[] = $clientid; //always we need to gather curent client data
        }
        else
        {
            $asociated_clients_ids[] = '999999999';
        }
        
        foreach($associated_clients as $k_aclient => $v_aclient)
        {
            foreach($v_aclient as $asociated_client_id => $asociated_client_value)
            {
                $asociated_clients_ids[] = $asociated_client_id;
            }
        }
        
        if($this->getRequest()->isPost())
        {
            $update_marked = $marked_patients->share_update($_REQUEST['sid'], $_POST);
            if($update_marked)
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&case=edit&cid=' . $clientid);
            }
            else
            {
                $this->_redirect(APP_BASE . 'connections/sharepatient?flg=err&case=edit&cid=' . $clientid);
            }
        }
        
        $asociated_clients_ids = array_unique($asociated_clients_ids);
        asort($asociated_clients_ids);
        $asociated_clients_ids = array_values($asociated_clients_ids);
        
        $sql_c = "*, AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax";
        
        //get clients data
        $clients = Doctrine_Query::create()
        ->select($sql_c)
        ->from('Client')
        ->where('isdelete = 0')
        ->andWhereIn('id', $asociated_clients_ids);
        $clients_arrays = $clients->fetchArray();
        
        foreach($clients_arrays as $k_clients_arr => $v_clients_arr)
        {
            $clientsarray[$v_clients_arr['id']] = $v_clients_arr;
        }
        
        //set clients details
        $this->view->clients_details = $clientsarray;
        
        //set curent client name
        $this->view->client_name = $clientsarray[$clientid]['client_name'];
        
        
        //get client verlauf shortcuts
        // 			$allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');
        
        $cs = new Courseshortcuts();
        $ltrarray = $cs->getFilterCourseData();
        
        foreach($ltrarray as $k_letter => $v_letter)
        {
            // 				if(in_array($v_letter['shortcut'], $allowed_shortcuts))
                // 				{
            $final_letters[$v_letter['shortcut']] = $v_letter;
            // 				}
                }
                
                ksort($final_letters);
                $this->view->sharing_shortcuts = $final_letters;
                
                $all_shared_patients = array_merge(array($marked[0]['ipid']), array($received_patients_ids));
                
                //get patients data
                $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
                $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
                $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
                $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
                $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
                $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
                $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
                $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
                $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
                $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
                $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
                $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
                
                // if super admin check if patient is visible or not
                if($logininfo->usertype == 'SA')
                {
                    $sql = "*, e.epid, e.clientid,";
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
                    $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                }
                
                $patient = Doctrine_Query::create()
                ->select($sql)
                ->from('PatientMaster p')
                ->where("p.isdelete = 0")
                ->andWhereIn('p.ipid', $all_shared_patients)
                ->leftJoin("p.EpidIpidMapping e");
                $pat_details = $patient->fetchArray();
                
                $this->view->patient_details = $pat_details;
        }
        
        //edit patient shortcuts before share is accepted
        public function editsharedshortcutsAction()
        {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $hidemagic = Zend_Registry::get('hidemagic');
            
            $linked_patients = new PatientsLinked();
            $client = new Client();
            $course_shortcuts = new Courseshortcuts();
            $patient_share = new PatientsShare();
            
            if($_REQUEST['cid'] > 0)
            {
                $clientid = $_REQUEST['cid'];
            }
            else if($logininfo->clientid > 0)
            {
                $clientid = $logininfo->clientid;
            }
            else
            {
                $this->view->client_name = "Select Client";
                $clientid = '0';
            }
            
            //execute post data
            if($this->getRequest()->isPost())
            {
                //			1. get link data
                $p_link_data = $linked_patients->get_link_data($_REQUEST['lid']);
                
                //			2. get source ipid by decrypting patient id and then get ipid
                $source = Pms_CommonData::getIpid(Pms_Uuid::decrypt($_REQUEST['patient_id']));
                
                //			3.find what is the target
                if($p_link_data[0]['source'] == $source)
                {
                    $target = $p_link_data[0]['target'];
                }
                else
                {
                    $target = $p_link_data[0]['source'];
                }
                
                //			4. delete all shortcuts from requested link id and above ipid as source
                $del_shortcuts = $patient_share->delete_shortcuts($_REQUEST['lid'], $source);
                
                
                // edit copy files data
                if(isset($_POST['disable_files_share'])){
                    
                    $mod = Doctrine::getTable('PatientsLinked')->find($_REQUEST['lid']);
                    $mod->copy_files = $_POST['disable_files_share'];
                    $mod->save();
                    
                }
                
                if(isset($_POST['disable_medis_share'])){
                    
                    $mod = Doctrine::getTable('PatientsLinked')->find($_REQUEST['lid']);
                    $mod->copy_meds = $_POST['disable_medis_share'];
                    $mod->save();
                    
                }
                
                
                //			5. Insert new submited shortcuts if there are any
                if(!empty($_REQUEST['shortcut']))
                {
                    $ins_shortcuts = $patient_share->insert_new_shortcuts($source, $target, $_REQUEST['lid'], $_REQUEST['shortcut']);
                    
                    if($ins_shortcuts)
                    {
                        $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&op=sedit');
                    }
                }
                else
                {
                    $this->_redirect(APP_BASE . 'connections/sharepatient?flg=suc&op=sedit');
                }
            }
            
            
            //get client data
            $client_dets = $client->getClientDataByid($clientid);
            
            $this->view->client_name = $client_dets['0']['client_name'];
            $this->view->clientid = $clientid;
            
            //get link data
            $link_data = $linked_patients->get_link_data($_REQUEST['lid']);
            
            $link_ipids[] = '9999999999';
            foreach($link_data as $link)
            {
                $link_ipids[] = $link['source'];
                $patient_source =  $link['source'];
                $link_ipids[] = $link['target'];
                $patient_target =  $link['target'];
                $copy_files = $link['copy_files'];
                $copy_meds = $link['copy_meds'];
            }
            
            $this->view->copy_files = $copy_files;
            $this->view->copy_meds = $copy_meds;
            //get all client shortcuts
            $shortcuts = $course_shortcuts->getClientShortcuts($clientid);
            foreach($shortcuts as $shortcut)
            {
                //			$client_shortcuts[$shortcut['shortcut_id']] = $shortcut;
                $client_shortcuts_letter[$shortcut['shortcut']] = $shortcut;
                
                $client_shortcuts_ids[] = $shortcut['shortcut_id'];
            }
            ksort($client_shortcuts_letter);
            
            //		filter client shortcuts
            
            
            // DE SCOS CELE PE CARE NU LE ARE CLIENTUL SURSA
            
            $allowed_shortcuts = array('A', 'B', 'D', 'E', 'G', 'H', 'I', 'K', 'M', 'N', 'S', 'Q', 'T');
            foreach($client_shortcuts_letter as $k_shortcut => $shortcut)
            {
                // 				if(in_array($k_shortcut, $allowed_shortcuts))
                    // 				{
                $client_shortcuts_allowed[$k_shortcut] = $shortcut;
                // 				}
                    }
                    // print_r($client_shortcuts_allowed); exit;
                    $this->view->client_shortcuts = $client_shortcuts_allowed;
                    //get all link shortcuts
                    $link_shortcuts = $linked_patients->get_link_shortcuts($_REQUEST['lid']);
                    
                    $this->view->link_shortcuts = $link_shortcuts[$patient_source];
                    // 			$this->view->link_shortcuts = "mamaliga";
                    // print_r($link_shortcuts); exit;
                    foreach($link_shortcuts as $pipid => $link_shortcut_data)
                    {
                        foreach($link_shortcut_data as $link_shortcut ){
                            
                            if(in_array($link_shortcut['shortcut'], $client_shortcuts_ids))
                            {
                                $client_link_shortcuts_ids[] = $link_sortcut['shortcut'];
                            }
                        }
                    }
                    
                    $this->view->client_link_shortcuts_ids = $client_link_shortcuts_ids;
                    
                    //get curent patient details to be used as source when inserting
                    $sql = "*, e.epid, e.clientid, AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
                    $sql .="AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name,";
                    $sql .="AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,";
                    $sql .="AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,";
                    $sql .="AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation,";
                    $sql .="AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,";
                    $sql .="AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,";
                    $sql .="AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip,";
                    $sql .="AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,";
                    $sql .="AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,";
                    $sql .="AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile,";
                    $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                    $sql .="AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
                    
                    // if super admin check if patient is visible or not
                    if($logininfo->usertype == 'SA')
                    {
                        $sql = "*, e.epid, e.clientid,";
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
                        $sql .="IF(isdischarged != 1 AND isstandby != 1 AND isstandbydelete != 1, 0,( IF(isstandbydelete = 1, 3, ( IF(isstandby = 1,2, (IF(isdischarged = 1,1,0)))) )) ) as status,";
                    }
                    
                    $patient = Doctrine_Query::create()
                    ->select($sql)
                    ->from('PatientMaster p')
                    ->where("p.isdelete = 0")
                    ->andWhereIn('p.ipid', $link_ipids)
                    ->leftJoin("p.EpidIpidMapping e");
                    $pat_details = $patient->fetchArray();
                    
                    foreach($pat_details as $patient)
                    {
                        if($patient['EpidIpidMapping']['clientid'] == $clientid)
                        {
                            $shortcuts_source_patient = $patient['id'];
                            $patients_link_data['source'] = $patient['ipid'];
                        }
                        else
                        {
                            $patients_link_data['target'] = $patient['ipid'];
                        }
                        
                        $patients_details[$patient['ipid']] = $patient;
                    }
                    
                    $this->view->patients_details = $patients_details;
                    $this->view->shortcut_source_patient = Pms_Uuid::encrypt($shortcuts_source_patient);
                    $this->view->direction = $patients_link_data; //source and target ipids(aka share direction)
            }
            
}?>