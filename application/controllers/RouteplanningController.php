<?php
/**
 * 
 * @author claudiu✍ 
 * Feb 20, 2019
 *
 */
class RouteplanningController extends Pms_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
                
        $this
        ->setActionsWithPatientinfoAndTabmenus([
            /*
             * actions that have the patient header
            */
            'index'
        ])
        ->setActionsWithJsFile([
            /*
             * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
            */
            'leaflet',
            'index',
        ])
        ->setActionsWithLayoutNew([
            /*
             * actions that will use layout_new.phtml
            * Actions With Patientinfo And Tabmenus also use layout_new.phtml
            */
            'leaflet',
            'index',
        ])
        ;
    }


    private function _populateCurrentMessages()
    {
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
    }
    
    
//     public function indexAction()
//     {
//         $this->redirect(APP_BASE . $this->getRequest()->getControllerName() . '/leaflet');
//         exit;//for read-ability
//     }
    
    public function indexAction()
//     public function leafletAction()
    {
        if ($this->ipid) {
            $this->_helper->layout->setLayout('layout_routeplanning');// TODO-2247 Added by Ancuta 17.04.2019
        }
        /**
         * view() variables
         */
        $jsonCenterLocation = [];
        $jsonUserPatients   = [];
        $jsonActivePatients = [];
        $activePatients = [];
        $usersPatients = [];
        $isSinglePatient = false;
        
        /**
         * get center or map location 
         * it can be user addres or hospital address, this depends of a km_calculation_settings
         */
        $jsonCenterLocation = $this->_leaflet_get_centerLocation();
        
                
        
        $ipids = [];
        
        if ($this->ipid) {
            /**
             * a patient ipid is selected
             * display map only for this only - this will be done for now
             * if were up to me...my choice ... display this ipid with route selected and allow to show also all patients of user, keeping this ipid in route allways
             */
            $selectedWaypoints = [];
            
            $ipids = [$this->ipid] ;
            
            $_currentPatient = $this->getPatientMasterData(); 
            
            $statusIcons = reset(array_filter($_currentPatient['system_icon_data'], function($i){return $i['__function']=='get_patients_status'; }));
 
            $usersPatients[] = [
                
                'isInRoute'     => 0,
                
                'nice_name'     => $_currentPatient['nice_name'],
                'nice_name_epid'=> $_currentPatient['nice_name_epid'],
                'enc_id'        => $this->enc_id,
                 
//                 'currentLocation' => [                    
//                     'street'    => $_currentPatient['locstreet'],
//                     'zip'       => $_currentPatient['loczip'],
//                     'city'      => $_currentPatient['loccity'],
//                     'country'   => 'Germany',
//                 ],
//                 'statusIcon' => [
//                     'image'     => $statusIcons[$this->ipid]['show']['image'],
//                     'color'     => $statusIcons[$this->ipid]['show']['color'],
//                     'custom'    => !empty($statusIcons[$this->ipid]['show']['custom'])
//                 ]  
            ];
            
            
            
            $isSinglePatient = true;
            
        } else {    
            /**
             * get all active patients
             */
            $activePatients = $this->_leaflet_get_activePatients();
            
            $ipids = array_unique(array_column($activePatients, 'ipid'));
            
            
            /**
             * get locations
             */
            $locations = $this->_leaflet_get_patients_locationActive($ipids);
            
            /**
             * get icons
             */
            $statusIcons = $this->_leaflet_get_patients_statusIcons($ipids);
            
            /**
             * get all active patients of current user
             */
            $usersPatients = $this->_leaflet_activePatients_extract_usersPatients($activePatients);
            

            /**
             * add CurrentLocation and StatusIcon to $usersPatients
             */
            array_walk($usersPatients, function(&$patient) use($locations, $statusIcons) {
                $loc = array_filter($locations, function($loc) use($patient){
                    return $patient['ipid'] == $loc['ipid'];
                });
                $currentLocation = reset($loc)['master_details'];
                
                $patient['currentLocation'] = [
                    'street'    =>  ! empty($currentLocation['street']) ?  $currentLocation['street'] :  null,
                    'zip'       =>  ! empty($currentLocation['zip']) ?  $currentLocation['zip'] :  null,
                    'city'      =>  ! empty($currentLocation['city']) ?  $currentLocation['city'] :  null,
                    'country'   =>  'Germany',
                ];
                $patient['statusIcon'] = [
                    'image'     => $statusIcons[$patient['ipid']]['show']['image'],
                    'color'     => $statusIcons[$patient['ipid']]['show']['color'],
                    'custom'    => ! empty($statusIcons[$patient['ipid']]['show']['custom'])
                ];
            });
            
            /**
             * add CurrentLocation and StatusIcon to $activePatients
             */
            array_walk($activePatients, function(&$patient) use($locations, $statusIcons) {
                $loc = array_filter($locations, function($loc) use($patient){
                    return $patient['ipid'] == $loc['ipid'];
                });
                $currentLocation = reset($loc)['master_details'];
                    
                $patient['currentLocation'] = [
                    'street'    =>  ! empty($currentLocation['street']) ?  $currentLocation['street'] :  null,
                    'zip'       =>  ! empty($currentLocation['zip']) ?  $currentLocation['zip'] :  null,
                    'city'      =>  ! empty($currentLocation['city']) ?  $currentLocation['city'] :  null,
                    'country'   =>  'Germany',
                ];
                $patient['statusIcon'] = [
                    'image'     => $statusIcons[$patient['ipid']]['show']['image'],
                    'color'     => $statusIcons[$patient['ipid']]['show']['color'],
                    'custom'    => ! empty($statusIcons[$patient['ipid']]['show']['custom'])
                ];
            });
            
            
            if($_REQUEST['pcount'] == "1"){
                echo "<pre/>";
                print_R('user patients: '.count($usersPatients));
            }
            /**
             * remove patients without adress
             */
            $usersPatients = array_filter($usersPatients, function ($pat) {
                return ! empty($pat['currentLocation']['street']) || ! empty($pat['currentLocation']['zip']) || ! empty($pat['currentLocation']['city']);
            });
            

            if($_REQUEST['pcount'] == "1"){
                echo "<pre/>";
                print_R('user patients with location: '.count($usersPatients));
            }
                
            $activePatients = array_filter($activePatients, function ($pat) {
                return ! empty($pat['currentLocation']['street']) || ! empty($pat['currentLocation']['zip']) || ! empty($pat['currentLocation']['city']);
            });
            
            if($_REQUEST['pcount'] == "1"){
                echo "<pre/>";
                print_R('active patients with location: '.count($activePatients));
            }
            
        }
   
        
        /**
         * format arrays for json
         */
        foreach ($usersPatients as $patient) {
            $jsonUserPatients[] = [
                'waypoint' => [
                    'latLong'   => null,//[52.4176591, 13.247352],
                    'address'   => implode(", ", $patient['currentLocation']),
                    '_address' => $patient['currentLocation']
                ],
                'title'         => $patient['nice_name'],
                'nice_name_epid'=> $patient['nice_name_epid'],
                'status_icon'   => $patient['statusIcon'],
                'description'   => implode(", ", $patient['currentLocation']),
                'enc_id'        => $patient['enc_id'],
                'type'          => 'patient', 
                'isInRoute'     => (int)$patient['isInRoute']
            ];  
        }        
        foreach ($activePatients as $patient) {
            $jsonActivePatients[] = [
                'waypoint' => [
                    'latLong'   => null,//[52.4176591, 13.247352],
                    'address'   => implode(", ", $patient['currentLocation']),
                    '_address' => $patient['currentLocation']
                ],
                'title'         => $patient['nice_name'],
                'nice_name_epid'=> $patient['nice_name_epid'],
                'status_icon'   => $patient['statusIcon'],
                'description'   => implode(", ", $patient['currentLocation']),
                'enc_id'        => $patient['enc_id'],
                'type'          => 'patient',
                'isInRoute'     => (int)$patient['isInRoute']
            ];
        }
        
        if($_REQUEST['pcount'] == "1"){
            echo "<pre/>";
            print_R('jsonActivePatients: ');
            print_R($jsonActivePatients);
        }
        
        $__L_Configs = $this->getInvokeArg('bootstrap')->getOption('openstreetmap');

        
        //add here the service/version
        if($this->ipid){
            $__L_Configs['router']['serviceUrl'] .= 'route/v1'; // TODO-2247 Added By Ancuta
        } else{
            $__L_Configs['router']['serviceUrl'] .= 'trip/v1';
        }
        
        Zend_Json::$useBuiltinEncoderDecoder = true;
        $this->view->headScript()->appendScript(
            PHP_EOL .
            "var __L_Configs = " . Zend_Json::encode($__L_Configs) . ";". PHP_EOL.
            "var jsonActivePatients = " . Zend_Json::encode($jsonActivePatients). ";". PHP_EOL .
            "var jsonUserPatients = " . Zend_Json::encode($jsonUserPatients) . ";". PHP_EOL .
            "var jsonCenterLocation = " . Zend_Json::encode($jsonCenterLocation) . ";". PHP_EOL.
            "var isSinglePatient = " . (int)$isSinglePatient . ";". PHP_EOL.
            PHP_EOL,
            $type = 'text/javascript', $attrs = array());
    
        
        /**
         * add them as array for view.. maybe you need them
         */
        $this->view->CenterLocation = $jsonCenterLocation;
        $this->view->UserPatients   = $jsonUserPatients;
        $this->view->ActivePatients = $jsonActivePatients;
        $this->view->isSinglePatient = $isSinglePatient;
        
    }
    
    

    /**
     * center of map depends on settings, if to use hospital or user addresss
     */
    private function _leaflet_get_centerLocation()
    {
        $jsonCenterLocation = [];
        
        $userdata = Pms_CommonData::getUserData($this->logininfo->userid);
        
        
        if($this->ipid) {
            
            $ipids = [$this->ipid] ;
            
            $_currentPatient = $this->getPatientMasterData();
        
            $address = [
                'street'    =>  ! empty($_currentPatient['locstreet']) ?  $_currentPatient['locstreet'] :  null,
                'zip'       =>  ! empty($_currentPatient['loczip']) ?  $_currentPatient['loczip'] :  null,
                'city'      =>  ! empty($_currentPatient['loccity']) ? $_currentPatient['loccity'] :  null,
                'country'   =>  'Germany',
            ];
            $jsonCenterLocation = [
                'waypoint' => [
                    'latLong'   => null,
                    'address'   => implode(", ", $address),
                    '_address'   => $address
                ],
                'title'         => $_currentPatient['nice_name'],
                'status_icon' => null,
                'description'   => implode(", ", $address),
                'type'          => 'patient',
                'isMapCenter'   => 1
            ];
        
        } else if($userdata[0]['km_calculation_settings'] == "user") {
        
            User::beautifyName($userdata);
            $address = [
                'street'    =>  ! empty($userdata[0]['street1']) ?  $userdata[0]['street1'] :  null,
                'zip'       =>  ! empty($userdata[0]['zip']) ?  $userdata[0]['zip'] :  null,
                'city'      =>  ! empty($userdata[0]['city']) ?  $userdata[0]['city'] :  null,
                'country'   =>  'Germany',
            ];
            $jsonCenterLocation = [
                'waypoint' => [
                    'latLong'   => null,
                    'address'   => implode(", ", $address),
                    '_address'   => $address
                ],
                'title'         => $userdata[0]['nice_name'],
                'status_icon'   => null,
                'description'   => implode(", ", $address),
                'type'          => 'user',
                'isMapCenter'   => 1
            ];
        
        
        } else {
        
            $clientdata = Pms_CommonData::getClientData($this->logininfo->clientid);
        
            $address = [
                'street'    =>  ! empty($clientdata[0]['street1']) ?  $clientdata[0]['street1'] :  null,
                'zip'       =>  ! empty($clientdata[0]['postcode']) ?  $clientdata[0]['postcode'] :  null,
                'city'      =>  ! empty($clientdata[0]['city']) ?  $clientdata[0]['city'] :  null,
                'country'   =>  'Germany',
            ];
        
            $jsonCenterLocation = [
                'waypoint' => [
                    'latLong'   => null,
                    'address'   => implode(", ", $address),
                    '_address'  => $address
                ],
                'title'         => $clientdata[0]['client_name'],
                'status_icon'   => $patient['CurrentLocation']['location_type'] == 1 ? $systemIcons['sys_subicons']['is_hospital_icon'] : null,
                'description'   => implode(", ", $address) . (! empty($clientdata[0]['phone']) ? "<br>". $clientdata[0]['phone'] : '' ),
                'type'          => 'client',
                'isMapCenter'   => 1
            ];
        }
        return $jsonCenterLocation;
    }
    

	
	private function _leaflet_get_activePatients()
	{	    
	    $salt = Zend_Registry::get('salt');
	    $hidemagic = Zend_Registry::get('hidemagic');
	    
	    $today = ['start' => date('Y-m-d')];  
	    
	    $sqlSelect = "e.* , p.id";
	    
	    // if super admin check if patient is visible or not
	    if ($this->logininfo->usertype == 'SA') {
	        $sqlSelect .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.first_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as first_name";
	        $sqlSelect .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.middle_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as middle_name";
	        $sqlSelect .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(p.last_name,'" . $salt . "') using latin1),'" . $hidemagic . "') as last_name";
	    } else {
	        $sqlSelect .= ", AES_DECRYPT(p.first_name,'" . $salt . "') as first_name";
	        $sqlSelect .= ", AES_DECRYPT(p.middle_name,'" . $salt . "') as middle_name";
	        $sqlSelect .= ", AES_DECRYPT(p.last_name,'" . $salt . "') as last_name";
	    }
	    
	    
	    $ipids = null;

	    $p_users = new PatientUsers();
	    $user_patients = $p_users->getUserPatients($this->logininfo->userid); //get user's patients by permission
	    
	    $userIPIDs = $user_patients['patients'];
	    /*
	     * previous fn returns 'patients' => [ '%' => '%'] when you have all patients, else it returns 'patients' => [an array off ipids]
	    */
	    if ($userIPIDs === ['%' => '%']) {
	        $ipids = null;
	    } else {
	        $ipids = $userIPIDs;
	        $ipids = array_filter($ipids, function($item){return $item != '' && $item!='X';});
	    }
	    
	     $activePatients = Pms_CommonData::patients_active(
	        $sqlSelect, //$select = "*"
	        $this->logininfo->clientid , //$client
	        null, //$periods
	        $ipids, //$ipids
	        'e.epid', //$order_by
	        'ASC', //$sort
	        ' p.isdischarged = 0 AND p.isstandbydelete = 0 AND p.isarchived = 0  AND p.isstandby = 0 ',//$search_sql
	        false, //$limit
	        '0',//$page
	        false //$include_standby
	        
        ); 
	    
	  
	/*     $patient = Doctrine_Query::create()
	    ->select($sqlSelect)
	    ->from('EpidIpidMapping e')
	    ->leftJoin("e.PatientMaster p")
	    ->where('p.isdelete = 0')
	    ->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')');
	    $patient->andWhere('p.isstandbydelete=0');
	    $patient->andWhere('p.isdischarged = 0');
	    $patient->andWhere('p.isstandby = 0');
	    $patient->andWhere('p.isarchived = 0');
	    $patient->andWhere('e.clientid = ' . $this->logininfo->clientid);
	    $activePatients = $patient->fetchArray();
 */
	    if($_REQUEST['pcount'] == "1"){
	        echo "<pre/>";
	        print_R('active patients '.count($activePatients));
	    }
	     
	    
	    PatientMaster::beautifyName($activePatients);

	    array_walk($activePatients, function(&$row) {$row['enc_id'] = Pms_Uuid::encrypt($row['PatientMaster']['id']);});
	    return $activePatients;
	}
	
	
	private function _leaflet_get_patients_locationActive($ipids) 
	{
	    if (empty($ipids)) {
	        return []; //fail-safe
	    }
	    
	    $locations = PatientLocation::get_multiple_period_locations($ipids, ['start' => date('Y-m-d'), 'end' => date('Y-m-d')]);
	    
	    
	    /**
	     * this is applied only on development
	     */
	    if (APPLICATION_ENV == "development") {
    	     $xThis = $this;
    	     array_walk($locations, function(&$patient) use ($xThis){
        	     if ( empty($patient['master_details']['street']) && empty($patient['master_details']['city']) && empty($patient['master_details']['zip'])) {
        	     $patient['master_details']['street'] = $xThis->_randomBerlinStreet();
        	     $patient['master_details']['city'] = 'Berlin';
        	     }
    	     });
	     }

	    
	    return $locations;
	}
	
	
	private function _leaflet_activePatients_extract_usersPatients($activePatients)
	{
	    $userPatients = [];
	    
	    
	    $epids = array_column($activePatients, 'epid');
	    
	    if (! empty($epids)) {
    	    $userQpaMapping = Doctrine_Core::getTable('PatientQpaMapping')->createQuery()
    	    ->select('id, epid')
    	    ->where('userid = ?', $this->logininfo->userid)
    	    ->andWhereIN('epid', $epids)
    	    ->fetchArray();
    	    
    	    if ( ! empty($userQpaMapping)) {
    	        
    	        $userEpidsAssigned = array_column($userQpaMapping, 'epid');
    	        
    	        $userPatients = array_filter($activePatients, function($patient) use ($userEpidsAssigned) {
    	            
    	            return in_array($patient['epid'], $userEpidsAssigned);
    	        });
    	    }
	    }
	    
	    
	    return $userPatients;
	}
	
	
	


	private function _leaflet_get_patients_statusIcons($ipids = [])
	{
	    if (empty($ipids)) {
	        return []; //fail-safe
	    }
	    
	    
	    $icons = IconsPatient::get_patients_status($ipids);
	    
	    return $icons;
	}
	
	
	/**
	 * fn used only on APPLICATION_ENV == "development"
	 */
	private function _randomBerlinStreet()
	{
	    $street = [
	    "Ackerstraße",
	    "Bernauer Straße",
	    "Chausseestraße",
	    "Ebertstraße",
	    "Fasanerieallee",
	    "Frankfurter Allee",
	    "Friedrichstraße",
	    "Gollanczstraße",
	    "Heerstraße (Berlin)",
	    "Invalidenstraße",
	    "Jüdenstraße (Berlin-Mitte)",
	    "Kaiserdamm",
	    "Karl-Liebknecht-Straße",
	    "Karl-Marx-Allee",
	    "Kopenhagener Straße",
	    "Kurfürstendamm",
	    "Legiendamm",
	    "Lehrter Strasse",
	    "Leipziger Straße",
	    "Leuschnerdamm",
	    "Majakowskiring",
	    "Mehringdamm",
	    "Motzstraße",
	    "Niederkirchnerstraße",
	    "Oranienburger Straße",
	    "Ossietzkystraße",
	    "Paul-Lincke-Ufer",
	    "Prenzlauer Allee",
	    "Rigaer Straße",
	    "Rosa-Luxemburg-Straße",
	    "Schönhauser Allee",
	    "Schwedter Straße",
	    "Siegesallee",
	    "Sonnenallee (Berlin)",
	    "Straße der Pariser Kommune",
	    "Straße des 17. Juni",
	    "Tangentiale Verbindung Ost",
	    "Tauentzienstraße",
	    "Tiergartenstraße",
	    "Turmstraße",
	    "Unter den Linden",
	    "Voßstraße",
	    "Warschauer Straße",
	    "Wilhelmstrasse",
	    "Wilhelmstraße (Spandau)",
	    ];
	    
	    return $street[rand(0, count($street)-1)];
	}
	
}