<?php
/**
 * this started from the ideea to have a base plugin...
 * a good idea at the time, a not optimal idea now cause it's restricting
 * 
 * TODO
 * - upload_qq_file changed into a helper
 * - returnDatatablesEmptyAndExit helper
 * - js+getPatientMasterData should have been included via helper _actionStack .. to much todo
 * 
 * 
 * @author claudiu 
 * August 2017
 *
 */
abstract class Pms_Controller_Action extends Zend_Controller_Action
{
    
    
	protected $translator;

	/**
	 * ! this was NOT created as a reference, 
	 * change to reference only if you need
	 * use ->setPatientMasterData() to append data 
	 * 
	 * @var array
	 * @example $this->getPatientMasterData()
	 */
	protected $_patientMasterData = null;
	
	protected $dec_id = null;
	protected $enc_id = null;
	protected $ipid = null;
	protected $epid = null;
	
	
	/**
	 * 
	 * @var Zend_Session_Namespace
	 */
	protected $logininfo = null; //this is the full login_info session
	//next 4 are for backwards compatibility
	//please use $this->logininfo->clientid
	protected $clientid = null;
	protected $userid = null;
	protected $usertype = null;
	protected $filepass = null;
	
	
	/**
	 * this is the full qqFileUpload session 
	 * @var Zend_Session_Namespace
	 */
	protected $qqfileupload = null;
	
	/**
	 * if you want to customize your upload_qq_file, add extension for the temp folder(where files are saved)  
	 * @var string
	 */
	protected $qq_temp_folder_ext = null; //
	
	/**
	 * default extensions not allowed in upload_qq_file
	 * @var array
	 */
	private $qq_ExcludeExtension = array('exe', 'php'); 
	
	
	
	private $_doctrine_OBJ = [];
	
		
	//add to this next 2 arrays if you have some action name that repeats on all controllers
	protected $actions_with_js_file = array(); // this array will be extended in each child controller init
	protected $actions_with_patientinfo_and_tabmenus = array(); // this array will be extended in each child controller init
	protected $actions_with_layout_patientnew = array(); // this array will be extended in each child controller init
	
	/**
	 * this action will include a patientHeader, patientIcons, patientNavigation file
	 * this actions will also use layout_new.phtml
	 * 
	 * @param string|array $action ,array overwrites, string extends
	 * @return Pms_Controller_Action , for a more fluent interface
	 */
	protected function setActionsWithPatientinfoAndTabmenus($action = '')
	{
	    if( ! empty($action)) {
	        
	        if (is_array($action)) {
	            $this->actions_with_patientinfo_and_tabmenus = $action;
	        } else {
    	        array_push($this->actions_with_patientinfo_and_tabmenus, $action); 
	            
	        }
	    }
	    return $this;
	}
	
	/**
	 * this action will include /javascript/view/controller/action.js file
	 * 
	 * @param string|array $action ,array overwrites, string extends
	 * @return Pms_Controller_Action , for a more fluent interface
	 */
	protected function setActionsWithJsFile($action = '')
	{
	    if( ! empty($action)) {
	        
	        if (is_array($action)) {
	            $this->actions_with_js_file = $action;
	        } else {
    	        array_push($this->actions_with_js_file, $action); 
	            
	        }
	    }
	    
	    return $this;
	}
	
	/**
	 * this action will use layout_new.phtml
	 *
	 * @param string|array $action ,array overwrites, string extends
	 * @return Pms_Controller_Action , for a more fluent interface
	 */
	protected function setActionsWithLayoutNew($action = '')
	{
	    if( ! empty($action)) {
	        
	        if (is_array($action)) {
	            $this->actions_with_layout_patientnew = $action;
	        } else {
    	        array_push($this->actions_with_layout_patientnew, $action); 
	            
	        }
	    }
	    
	    return $this;
	}
	
	
	
	
	
	/**
	 * TODO : deprecate after we switch to _helper->log
	 *
	 * @var Application_Controller_Helper_Log
	 * @deprecated
	 */
	protected static $_logger = null;
	
	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		parent::__construct($request, $response, $invokeArgs);
		
		// Initialize my way
// 		$this->translator = Zend_Registry::get('Zend_Translate');
		$this->translator = new Zend_View_Helper_Translate();
		
		
		$this->logininfo = new Zend_Session_Namespace('Login_Info');
		
		$this->qqfileupload = new Zend_Session_Namespace('qqFileUpload');
		
		
		//ISPC-2609 Ancuta 01.09.2020 - allow also in invoicenew - for controller to be used in cron
		
		if(isset($this->logininfo->allow_cron_in_controllers) && ! empty($this->logininfo->allow_cron_in_controllers) && in_array($this->getRequest()->getControllerName(),$this->logininfo->allow_cron_in_controllers)){
            // do not redirect to no client 		    
		} 
		else if( ! $this->logininfo->clientid && $this->getRequest()->getControllerName() != 'client') 
		{
		    
			//redirect to select client error
			//only client controller is allowd to have have no client selected
			$this->redirect(APP_BASE . "error/noclient" , array(
					"exit" => true
			));
			
			exit; //for readbility
		}
		
		$this->clientid = $this->logininfo->clientid;
		$this->userid = $this->logininfo->userid;
		$this->usertype = $this->logininfo->usertype;
		$this->filepass = $this->logininfo->filepass;
		
		$this->_setViewVariable('logininfo', $this->logininfo->getIterator());
		
		
		//set locale and renderer
		$this->_template_init_view();
		
		//append javascript files for each action
		$this->_template_init_js_file();
		
		//patient header
		$this->_template_init_patientinfo_and_tabmenus();
	
	}

	/**
	 * magique
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::__call()
	 */
	public function __call($method, $args)
	{
	    if ('Action' == substr($method, -6)) {
	        
	        
	        // If the action method was not found, forward to overview/overview
	        $this->getHelper('Log')->info("Unknown action " .  $this->getRequest()->getControllerName() . "/{$method} called, redirecting user to overview/overview");
	        return $this->_forward('overview', 'overview');
	    }
	
	    // all other methods throw an exception
	    throw new Exception("Invalid method {$method} called", 500);
	}
	
	
	/**
	 * we don't have a default indexAction for controllers, so will forward to overview/overview 
	 */
	protected function indexAction() {
	   return $this->_forward('overview', 'overview');;   
	}
	
    
    
	
	
	/**
	 * Translator wrapper
	 * Jul 19, 2017 @claudiu 
	 * 
	 * @param string $string  The string to be translated
	 * @return The translated string
	 */
// 	protected function translate($string)
// 	{
// 		return ($this->translator->translate($string));
// 	}
	protected function translate($string)
	{
        return call_user_func_array(array($this->translator, 'translate'), func_get_args());
	    
	}
	
	
	public function getPatientMasterData ( $key = null) {
	    
	    if (is_null($key)) {	        
    	    return $this->_patientMasterData;
	    } else {
	        return isset($this->_patientMasterData[$key]) ? $this->_patientMasterData[$key] : null;
	    }
	}
	
	public function setPatientMasterData(  $data , $key = null )
	{
	    	
	    if ( ! is_array($this->_patientMasterData) ) {
	        $this->_patientMasterData = ! empty($this->_patientMasterData) ? array($this->_patientMasterData) : array();
	    }
	
	    if ( is_null($key) && is_array($data)) {
	        foreach ($data as $k => $v) {
	            $this->_patientMasterData[$k] =  $v;
	        }
	    } elseif ( ! is_null($key)) {
	        $this->_patientMasterData[ $key ] = $data;
	    }
	}
	
	/**
	 * TODO: change/remove this fn if you change _patientMasterData reference, 
	 */
	public function getMasterData_extradata()
	{
	    if (isset($this->_doctrine_OBJ['PatientMaster'])) {
	        
	        $pmOBJ = $this->_doctrine_OBJ['PatientMaster'];
    	    
    	    if ($pmOBJ instanceof PatientMaster) {
    	        
    	        $pmOBJ->getMasterData_extradata($this->ipid);
    	        
    	        //re-set
    	        $this->_patientMasterData = $pmOBJ->get_patientMasterData();
    
    	        $this->_setViewVariable('patientMasterData',  $this->_patientMasterData);
    	        
    	        $this->setParam('__patientMasterData', $this->_patientMasterData);
    	         
    	        
    	    }
	    }
	}
	
	
	/**
	 * read the allowed vars type for viewer
	 * todo : proxy
	 * 
	 * @param unknown $var
	 * @param string $value
	 */
	private function _setViewVariable($var, $value = null) 
	{
	    if ( ! empty($var)) {
	        $this->view->{$var} = $value;
	    }
	}
	
	
	/**
	 * 
	 * TODO : check [production] values for date_default_timezone & locale
	 * 
	 * @cla on 23.04.2018 - removed date_default_timezone_set & setlocale, add this only on bootstrap.dev
	 * @cla on 23.04.2018 + pdf_print_template & bypass_template are now from _params, not only from $_POST
	 * 
	 * Jul 21, 2017 @claudiu
	 * locale -a
	 * 
	 * set
	 * setlocale(LC_ALL, 'de_DE.UTF-8');
	 * setNoRender if pdf print
	 *
	 */
	private function _template_init_view()
	{
		/*
		date_default_timezone_set('Europe/Berlin');
		setlocale(LC_ALL, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE', 'de_DE@euro', 'deu_deu'); //i only have on my machine de_DE
		*/
		if ($this->getRequest()->isPost()
			&& ( $this->getRequest()->getParam('pdf_print_template') == "pdf_print_template" || $this->getRequest()->getParam('bypass_template') == "1" )
		    )
		{
			//pdf print template
			$this->_helper->layout->setLayout('layout_ajax');
			$this->_helper->viewRenderer->setNoRender(true);
			
		} else {
		    
		    if( empty($this->actions_with_patientinfo_and_tabmenus) && empty($this->actions_with_layout_patientnew)) {
		        return;
		    }
		    
		    $actionName = $this->getRequest()->getActionName();
		    if( ! in_array($actionName, $this->actions_with_patientinfo_and_tabmenus) 
		        && ! in_array($actionName, $this->actions_with_layout_patientnew)) {
		        return;
		    }
		    
		    $this->_helper->layout->setViewSuffix('phtml');
		    $this->_helper->layout->setLayout('layout_new');
		}
	}
	
	/**
	 * 
	 * Jul 21, 2017 @claudiu 
	 * 
	 * if empty $actions_with_js_file return;
	 * elseif is xhr return;
	 * else append javascript files from: /public/javascript/view/controller/action.js return;
	 * 
	 * 
	 *
	 */
	private function _template_init_js_file()
	{		
		if( empty($this->actions_with_js_file)) {
			return;
		}
		
		if( $this->getRequest()->isXmlHttpRequest()) {
			return;
		}
		
		$actionName = $this->getRequest()->getActionName();
		if( ! in_array($actionName, $this->actions_with_js_file)) {
			return;
		}
		//Include js file of this action
		$controllerName = $this->getRequest()->getControllerName();
		
		//sanitize $js_file_name ?
		$actionName = Pms_CommonData::normalizeString($actionName);
		$controllerName = Pms_CommonData::normalizeString($controllerName);
					
		$pc_js_file =  PUBLIC_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";//this is only on pc... so remember to put the ipad version
		$js_filename = RES_FILE_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".js";//$js_filename is for http ipad/pc
			
		
		if( file_exists( $pc_js_file )) {
			$this->view->headScript()->appendFile($js_filename);
		}
		
		/*
		 * if deviceType is mobile. we also include /_ipad/.mobile.js file
		 * _ipad hardcoded here
		 */
		if ($this->_helper->viewRenderer->getDeviceType() == 'mobile') {
		    
		    $pc_js_file =  PUBLIC_PATH . "/_ipad/javascript/views/" . $controllerName . "/".  $actionName . ".mobile.js";
		    $js_filename = RES_FILE_PATH . "/javascript/views/" . $controllerName . "/".  $actionName . ".mobile.js";
		    
		    if( file_exists( $pc_js_file )) {
		        $this->view->headScript()->appendFile($js_filename);
		    }
		}
		
		
	
	}
	
	/**
	 * 
	 * + update @cla 28.05.2018
	 * + changed the order of if's
	 * + force populate $this->dec_id and $this->_patientMasterData
	 *  
	 * Jul 21, 2017 @claudiu 
	 * 
	 * set
	 * $this->view->patientinfo
	 * $this->view->tabmenus
	 * $this->_patientMasterData
	 *
	 */
	private function _template_init_patientinfo_and_tabmenus()
	{
		if( empty($this->actions_with_patientinfo_and_tabmenus)) {
			return;
		}
		
		
		$actionName = $this->getRequest()->getActionName();
		if( ! in_array($actionName, $this->actions_with_patientinfo_and_tabmenus)) {
			return;
		}
			
		
		if (is_null($this->enc_id)) {
		    $enc_id = $this->getRequest()->getQuery('id', null);
		    $enc_id = is_null($enc_id) ? $this->getRequest()->getPost('id', null) : $enc_id;
		    $enc_id = is_null($enc_id) ? $this->getRequest()->getParam('id', null) : $enc_id;
		    $this->enc_id = $enc_id;
		}
		$this->_setViewVariable('enc_id',  $this->enc_id);
		
		
		if (is_null($this->dec_id)  && ! is_null($this->enc_id)) {
		    
		    if( strlen($this->enc_id) > 0)	{
		        $this->dec_id = Pms_Uuid::decrypt($this->enc_id);
		    }    
		}
		$this->_setViewVariable('dec_id',  $this->dec_id);
		
		
		if (is_null($this->dec_id)) {
		    return;
		}
		
		
		if( $this->getRequest()->isXmlHttpRequest()) 
		{
		    /*
		     * get ipid that was set on checkPermissionOnRun
		     */
		    if( is_null($this->ipid)) {
		        
		        $last_ipid_session = new Zend_Session_Namespace('last_ipid');
		        
		        if ($last_ipid_session->dec_id == $this->dec_id) {
		            
    		        $this->ipid = $last_ipid_session->ipid;		            
        		    $this->_setViewVariable('ipid',  $this->ipid);
		        }
		    }
		    
		    return;
		}
// 		$this->_helper->layout->setLayout('layout_patientnew');
		
		$patientmaster = new PatientMaster();
		
		
		//$view->patientinfo is used in Patient header
		$this->_setViewVariable('patientinfo', $patientmaster->getMasterData($this->dec_id, 1));
		
		$this->_patientMasterData = $patientmaster->get_patientMasterData();
		
		//$view->patientMasterData is used in new Stammdaten&Versorger
		$this->_setViewVariable('patientMasterData',  $this->_patientMasterData);
		
		
		
		if( is_null($this->ipid)) {
			$this->ipid = $this->_patientMasterData['ipid'];
		}
		
		if (is_null($this->epid)) {
			$this->epid = $this->_patientMasterData['epid'];
		}
		
		$this->_setViewVariable('ipid',  $this->ipid);
		$this->_setViewVariable('epid',  $this->epid);
		
		
		$tm = new TabMenus();
		
		$patientNavigation = $tm->getMenuTabs(true);
		
		$this->_setViewVariable('tabmenus',  $patientNavigation['html']);
		
		
		//TODO: change this to recursive... i didit only for development only, not production
		$patientNavigationMobile = [];
		
		foreach ($patientNavigation['first_menu'] as $primary_menu) {
		    
	        $sub_sub_menus = [];
		    foreach ($patientNavigation ['second_menu'] [$primary_menu['id']] as $sub_sub_menu) {
		        
		        if ($sub_sub_menu['menu_link'] == '') {
		            $sub_sub_menus[$sub_sub_menu['menu_title']] = array_column($patientNavigation ['second_menu'] [ $sub_sub_menu['id'] ], 'menu_title', 'menu_link');
		        }
	            else {
	                $sub_sub_menus[$sub_sub_menu['menu_title']] = $sub_sub_menu['menu_title'];
	            }
		    }
		    
		    $primary_menu_group = array_merge(
		        ! empty($primary_menu['menu_link']) ? [$primary_menu['menu_link'] => $primary_menu['menu_title']] : [],
		        $sub_sub_menus
		    );

		    $patientNavigationMobile[ $primary_menu['menu_title'] ] =  $primary_menu_group;
		
		}$this->_setViewVariable('patient.navigation.array',  $patientNavigationMobile);
		
		
		$this->setParam('__patientMasterData', $this->_patientMasterData);
		
		
		$this->_doctrine_OBJ['PatientMaster'] = $patientmaster;
		
		$this->setParam('__patientMasterData', $this->_patientMasterData);
		
		$this->mark("<font color=red>-- IPID --</font> {$this->ipid}" . PHP_EOL , true);
		
		//render placeholders for mobile version
		if ($this->_helper->viewRenderer->getDeviceType() == 'mobile') {
		//if (ISPC_WEBSITE_VIEW_VERSION == "mobile") {
    		
    		$this->view->render('templates/placeholderPatientIconsSystem.phtml');
    		$this->view->render('templates/placeholderPatientIconsNew.phtml');
    		$this->view->render('templates/placeholderPatientNavigation.phtml');
    		$this->view->render('templates/placeholderPatientDetailsSimple.phtml');
    		
		    
		}		
		
		
	}
	

	
	/**
	 * TODO: this must be moved to a common class PMS_QQ_FILE_UPLOADER .. and all the others
	 * 
	 * ! for qq fine-uploader only !
	 * ! for single file upload only !
	 * 
	 * example:
	 * VoluntaryworkersController and MemberController for single file
	 * 
	 * $fileupload_result = $this->upload_qq_file( array(
	 *			"allowed_file_extensions" => array('pdf'),
	 * 			"max-filesize" => 5 * 1000 * 1024,
	 * 			"action" => "sendemail2vws",
	 * 	));
	 * return json_encode($fileupload_result);
	 * 
	 * $attachment_this = $this->get_last_uploaded_file("sendemail2vws", $post['email']['attachment']); // use like this for single file upload
	 * $attachments_all = $this->get_last_uploaded_file("sendemail2vws"); // use like this for multiple file upload
	 * 
	 * $this->set_last_uploaded_file('sendemail2vws'); //invalidate all
	 * $this->set_last_uploaded_file('sendemail2vws', $post['email']['attachment']); //invalidate one
	 * 
	 * Jul 25, 2017 @claudiu 
	 * 
	 * 
	 * 
	 * @param array $params
	 * @return multitype:boolean string |multitype:boolean string unknown |Ambigous <multitype:boolean string , multitype:boolean string unknown >
	 */
	protected function upload_qq_file( $params = array(
	    
		"action" => "upload_one_qq_file", // mandatory

	    "allowed_file_extensions" => array('pdf'), // optional, defaults to any
	    
	    "excluded_file_extensions" => array('exe', 'php'), // optional, defaults to $this->$qq_ExcludeExtension
		
	    "max-filesize"   => 104857600,//optional in bytes, defaults to php.ini upload_max_filesize, 
	    
	    "public_file_path" => PDF_PATH, //optional, local path where to store the file until you save to ftp, defaults to PDF_PATH
	    
	    "clientid" => null, // optional, if you upload for another client, defaults to $this->logininfo-clientid
	    "filepass" => null, //optional, if you upload for another client, defaults to $this->logininfo-filepass 
	    "zip_file" => true, //zip the file after you finish, so it's stored localy as encryped
	))
	{
	    
		$response = array(
				"success"	=> false, 
				"error"		=> "fatal error, contact admin"
		);

		
		$qq_ExcludeExtension = ! empty($params['excluded_file_extensions']) && is_array($params['excluded_file_extensions']) ? $params['excluded_file_extensions'] :  $this->qq_ExcludeExtension;
		
		$upload_adapter = new Zend_File_Transfer_Adapter_Http();
		$upload_adapter->setOptions(array('useByteString' => false));
		$upload_adapter->setValidators(array(
		    'ExcludeExtension' => $qq_ExcludeExtension,
		    //'Extension' => allowed_file_extensions,
		    //'Size'  => array('min' => 1, 'max' => max-filesize),
		    //'Count' => array('min' => 1, 'max' => 100),
		));
		$fileData = $upload_adapter->getFileInfo();
		
	    $clientid = empty($params['clientid']) ? $this->logininfo->clientid : $params['clientid'] ;
	    $params['clientid'] = $clientid;
	    
	    //this is were files are uploaded
	    $public_file_path = ! empty($params['public_file_path']) && is_dir($params['public_file_path']) ? $params['public_file_path'] : PDF_PATH;
	    $params['public_file_path'] = $public_file_path; 
	    
	    //upload_max_filesize
	    $max_filesize = ! empty($params['max-filesize']) ? (int)$params['max-filesize'] : ini_get('upload_max_filesize');
	    $max_filesize = $this->return_bytes($max_filesize);
	    $params['max-filesize'] = $max_filesize;
	    
		$upload_adapter->addValidator('Size', false, array('max' => $max_filesize));
		
		//allowed extensions
		if ( ! empty($params['allowed_file_extensions']) && is_array($params['allowed_file_extensions'])) {
		    $upload_adapter->addValidator('Extension', false, $params['allowed_file_extensions']);
		}
		
		$postData = $this->getRequest()->getPost();
		
		/*
		$fileMimeType = $upload_adapter->getMimeType();
		*/
		
		if (empty($fileData['qqfile']) 
		    || empty($postData['qqfilename'])
		    || empty($postData['qquuid']) ) 
		{
			// fn was designed for js qquploader javascript/fine-uploader/fine-uploader.min.js
			return $response;
		}
		
		
		if(empty($params['action'])) {
		    $response["success"]	= false;
		    $response["error"]		= "cannot move to our folder, you have no action";
		    return $response;
		}
		
	
		//$qqfile_name = $upload_adapter->getFileName();
		/*
		$extension = explode(".", $fileData['qqfile']['name']);
		$extension = strtolower($extension[count($extension) -1]);
		*/
		
		$info = pathinfo($fileData['qqfile']['name']);
		$extension = strtolower($info['extension']);
			
		//TODO : replace next 2 if's with the ->  if ( ! $upload_adapter->isValid()) then return all errors
		/*
		 * replaced with Extension validator
		if ( isset($params['allowed_file_extensions']) && ! in_array($extension , $params['allowed_file_extensions'] ))
		{
			$response["success"]	= false;
			$response["error"] 		= "invalid file extension, only: ". implode(" , ",$params['allowed_file_extensions']);
			return $response;
		}
		*/
		
		/*
		 * replaced with Size validator
		if (empty($fileData['qqfile']['size']) 
		    || (! empty($max_filesize) && $fileData['qqfile']['size'] > $max_filesize) )
		{
			$response["success"]	= false;
			$response["error"]		= "max-filesize: ". $max_filesize . " yours:" . filesize($_FILES['qqfile']['tmp_name']);
			return $response;
		}
		*/
		
		//sanitize filename, this will be saved in db for later download
		$qqfilename = Pms_CommonData::filter_filename($fileData['qqfile']['name'], true);
		
		$qquuid = $postData['qquuid'];
		//add newUuid in response if you want to customize the array_keys
		
		//this is the nane we use to save the file.. so there are no UTF-8
		$filename = trim(time(). '.' . $extension);
		
		//create unique new folder in $public_file_path
		$temp_folder_extension = $params['action'] . "_";
		if( ! is_null($this->qq_temp_folder_ext)){
			$temp_folder_extension = $this->qq_temp_folder_ext . "_";
		}
		
		$temp_folder = (Pms_CommonData::uniqfolder_v2( $public_file_path , $temp_folder_extension));
		$unique_folder = pathinfo($temp_folder, PATHINFO_BASENAME);
		
		//forced return ... something went wrong when creating temp folder
		if( ! is_dir($temp_folder)) {
			$response["success"]	= false;
			$response["error"]		= "cannot creat temp folder, inform admin";
			$response["qqfilename"]	= $qqfilename;
			$response["qquuid"]		= $qquuid;
			return $response;
		}
	
			
		$full_path_filename = $temp_folder . "/". $filename;
		
		//setDestination
        $upload_adapter->addFilter('Rename', array(
            'target' => $full_path_filename,
            'overwrite' => true
        ));
        
        
   
	    if ( ! $upload_adapter->isValid()) {
	        
	        $response["success"]	= false;
	        $response["error"]		= $upload_adapter->getMessages();
	        
	    } else {
	        
		    try {
    		    // upload received file(s) , NOTE the (S) ... but I used for single file !
    		    $upload_adapter->receive();
    		    
    		    $fileInfo = $upload_adapter->getFileInfo();
    		    $fileInfo = $fileInfo['qqfile'];
    		    
    		    //save to session our new file for later ftp upload
    		    self::set_last_uploaded_file( $params['action'], $qquuid, $full_path_filename, $qqfilename , $clientid, $fileInfo);
    		    
    		    $test = self::get_last_uploaded_file( $params['action'], $qquuid , $clientid);
    		    
    		    if($test !== false && $params['zip_file']) {

    		        $this->_qq_zip_file($params, $test);
    		        
    		        $test = self::get_last_uploaded_file( $params['action'], $qquuid , $clientid);
    		        
    		    }
    		    
    		    if($test !== false ) {
    		    	
    		    	//ISPC-2465 Carmen 14.10.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
    		    	$filetype =  substr($test[$qquuid]['filepath'], -3);
    		    	$imgbinary = fread(fopen($test[$qquuid]['filepath'], "r"),filesize($test[$qquuid]['filepath']));
    		    		
    		    	$base64data = 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
    		    	
    		        $response = array(
    		        "success"		=> true,
    		            "qqfilename"	=> $qqfilename,
    		            "qquuid"		=> $qquuid,
    		        	"qqusrc" 		=> $base64data
    		        );
    		    }
    		    
    		    //append for debug all the infos we stored
    		    if ( APPLICATION_ENV == 'development') {
    		        // ! attention this array includes sensitive data !
    		        $response['debug'] = self::get_last_uploaded_file( $params['action'], $qquuid , $clientid);;
    		    }
    		    
		    } catch (Zend_File_Transfer_Exception $e) {
		        
		        $response["success"]	= false;
		        $response["error"]		= $e->getMessage();
		        
		        //throw new Zend_Exception($e->getMessage(), 1);
		    }
		    
			
	    }
			
		return $response;
	}
	
	
	/**
	 * @cla on 23.04.2018
	 * created ONLY for $this->upload_qq_file
	 * 
	 * @param unknown $params
	 * @param unknown $uploaded_files
	 */
	private function _qq_zip_file( $params =  array(), $uploaded_files =  array())
	{
	    $action = $params['action'];
	    
	    $clientid = empty($params['clientid']) ? $this->logininfo->clientid : $params['clientid'] ;
	    $clientid_sufixed =  "client_" . $clientid;
	    
	    $filepass = empty($params['filepass']) ? $this->logininfo->filepass : $params['filepass'] ;
	    
	    //$this->_log_info("session BEFORE zipping a file");
	    //$this->_log_info(print_r($this->qqfileupload->{$action}->{$clientid_sufixed} , true));
	    
	    
	    foreach ($uploaded_files as $file) {
	       //$file['filepath']
            if ( ! is_file($file['filepath'])) {
                
                //$this->_log_info("not is_file {$file['filepath']}");
                
                continue;
            }
            
	        $pathinfo = pathinfo($file['filepath']);

	        $zip_file = $pathinfo['dirname'] . ".zip";
	        
	        $folder_temp_path = basename($pathinfo['dirname']);
	        
	        $parent_pathinfo = pathinfo($pathinfo['dirname']);
	        
	        $legacy_path = basename($parent_pathinfo['dirname']); // this is included in the archive
	        
	        $localhost_dir = $parent_pathinfo['dirname'];
	        
	        if (empty($folder_temp_path) || empty($legacy_path) || empty($localhost_dir)) {
	            
	            //$this->_log_info("one is empty {$folder_temp_path} | {$legacy_path} | {$localhost_dir}");
	            
	            continue;
	        }
	        
	        $cmd_create_zip = "sh -c \"cd '{$localhost_dir}/../' && zip -9 -r -P {$filepass} '{$zip_file}' {$legacy_path}/{$folder_temp_path}/* \"";
	        
	        @exec($cmd_create_zip);
	         
	        if (is_file($zip_file)) {
	            //$this->_log_info("ziped {$zip_file}");
	            
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['isZipped'] = true;
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['clientid'] = $clientid;
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['filepass'] = $filepass;
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['original_filepath'] = $file['filepath']; //save the old filepath, maybe you need for debug
    	        
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['filepath'] = $zip_file;//change the path to the ziped one
    	        $this->qqfileupload->{$action}->{$clientid_sufixed} [ $file['qquuid'] ] ['legacy_path'] = $legacy_path;
    	        
    	        //remove the not-zipped one with the full folder is in
    	        $cmd_remove_unzipped = "sh -c \"cd '{$localhost_dir}/' && rm -r {$folder_temp_path}\"";
    	        @exec($cmd_remove_unzipped);
    	        
    	        
    	       
    	         
	        } else {
	            //$this->_log_info("failed to zip {$zip_file}");
	            
	        }
	        
	    }
	    
	    
	    //$this->_log_info("session AFTER zipping a file");
	    //$this->_log_info(print_r($this->qqfileupload->{$action}->{$clientid_sufixed} , true));
	    
	    return;
	    
	}
	
	
	/**
	 * 
	 * delete_last_uploaded_file("action_name") - will clear all uploaded files from this action, of this client
	 * delete_last_uploaded_file("action_name", "qquuid77") - will clear just the file 'qquuid77' from this 'action_name' of this cleint
	 * delete_last_uploaded_file("action_name", "qquuid88" , 100) - will clear just the file 'qquuid88' from this 'action_name' of client 100
	 * 
	 * @param string $action
	 * @param string $qquuid
	 * @param string $clientid
	 */
	protected function delete_last_uploaded_file( $action = "action_name", $qquuid = null, $clientid = null)
	{
	    if (empty($action)) {
	        //what do you want to remove?
	        return;
	    }
	    
	    $clientid = empty($clientid) ? $this->logininfo->clientid : $clientid ;
	     
	    $clientid_sufixed =  "client_" . $clientid;

	    if( is_null($qquuid)) { //delete all previous files from this action
	        
	        $all_files = $this->qqfileupload->{$action}->{$clientid_sufixed};
	        	
	        foreach ($all_files as $file) {
	            
	            if( ! empty($file['filepath']) && is_file($file['filepath'])) {
	                    
	                @unlink($file['filepath']);
	            }
	        }
	        
	        $this->qqfileupload->{$action}->{$clientid_sufixed} = array();
	    
	    } elseif ($file = $this->qqfileupload->{$action}->{$clientid_sufixed}[$qquuid]) { //delete just one file from this $qquuid
            
	        if( ! empty($file['filepath']) && is_file($file['filepath'])) {
	            
	            @unlink($file ['filepath']);
	            
	        }
	        
	        $this->qqfileupload->{$action}->{$clientid_sufixed} [$qquuid] = null;    	    
	    }
	    
	    return true;
	}
	
	
	/**
	 *  ! misleading name ! 
	 *  this can be used also as delete_last_uploaded_file !
	 *  
	 * example:
	 * set_last_uploaded_file("action_name") - will clear all uploaded files from this action, of this client
	 * set_last_uploaded_file("action_name", "qquuid77") - will clear just the file 'qquuid77' from this 'action_name' from this client
	 * set_last_uploaded_file("action_name", "qquuid77" , '/temp/file.pdf', 'file pdf nicename') - will replace file 'qquuid77' from this 'action_name' with the new values 
	 * 
	 * Jul 25, 2017 @claudiu 
	 * 
	 * @param string $action
	 * @param string $qquuid
	 * @param string $filepath
	 * @param string $filename
	 */
	protected function set_last_uploaded_file( $action = "action_name", $qquuid = null, $filepath = null, $filename = null , $clientid = null , $fileInfo = null)
	{
	    $result = false;
	    
	    $clientid = empty($clientid) ? $this->logininfo->clientid : $clientid ;
	    
		$clientid_sufixed =  "client_" . $clientid;
		
		
		/*
		 * check if there is another file with the same uuid
		 * and.. delete it
		 */
		$this->delete_last_uploaded_file($action, $qquuid, $clientid);
		
		
		//append new file
		if( ! is_null($filepath) && file_exists($filepath)) {
		    $this->qqfileupload->{$action}->{$clientid_sufixed} [$qquuid] = array(
		        "action"	=> $action,
		        "qquuid"	=> $qquuid,
		        "filepath"	=> $filepath,
		        "filename"	=> $filename,
		        "ipid"		=> $this->ipid,
		        "dec_id"	=> $this->dec_id,
		        "fileInfo"  => $fileInfo,
		    );
		
		    $result = true;
		}
		

		return $result;
			
	}
	
	/**
	 * Jul 25, 2017 @claudiu 
	 * 
	 * @param string $action
	 * @param string $qquuid
	 * @return Ambigous <boolean, multitype:unknown >
	 * array( qquuid => array(action, qquuid, filepath, filename))
	 * false for single if file not exists 
	 * false for multiple if array is empty 
	 * (for multiple check yourself if file still exists)
	 */
	protected function get_last_uploaded_file( $action = "action_name", $qquuid = null, $clientid = null)
	{
	    $clientid = empty($clientid) ? $this->logininfo->clientid : $clientid ;
	    
		$result = false;
		
		$clientid_sufixed =  "client_" . $clientid;
			
		if( is_null($qquuid)) {
			//return all files without any test
			$result = $this->qqfileupload->{$action}->{$clientid_sufixed};
		}
		else {
						
			if( ! is_array($qquuid)) {
				$qquuid = array($qquuid);
			}
			
// 			$all_files = (array)$this->qqfileupload->{$action};
// 			$files = array();
// 			foreach ($all_files as $k_clientid => $data) {
			
// 				if ($k_clientid == $clientid_sufixed) {
// 					$files = $data;
// 				}
// 			}
			
			
			foreach( $qquuid as $single_qquuid) {
					
// 				$file = $files [$single_qquuid];
				$file = $this->qqfileupload->{$action}->{$clientid_sufixed}[$single_qquuid];
					
				if( ! empty($file['filepath']) && is_file($file['filepath'])) {
					
					if( ! is_array($result)) {
						$result = array();//added like this so can test $result === false
					}
					
					$result[$single_qquuid] = $file;
				}
			}
		}

		return $result;
	}
	
	protected function returnDatatablesEmptyAndExit()
	{
			
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer->setNoRender(true); // disable view rendering
			
		$response = array();
		$response['draw'] = (int)$this->getRequest()->getParam('draw'); //? get the sent draw from data table
		$response['recordsTotal'] = 0;
		$response['recordsFiltered'] = 0;//count($resulted_data); // ??
		$response['data'] = array();
	
		ob_end_clean();
		ob_start();
		
		//$this->getHelper('Json')
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->sendJson($response);
			
		// 			header("Content-type: application/json; charset=UTF-8");
		// 			echo json_encode($response);
		exit;
	}
	
	
	

	/**
	 * wrapper for ZFDebug Log mark()
	 * used for debug purposes only
	 * @param string $name
	 * @param string $logFirst
	 */
	protected function mark($name = 'ZF1', $logFirst = false)
	{
	    //$this->getInvokeArg('bootstrap')->getEnvironment()
	    if ( APPLICATION_ENV == 'production') {
	        return;
	    }
	     
	    $frontController = Zend_Controller_Front::getInstance();
	    if (($_zf_debug = $frontController->getPlugin('ZFDebug_Controller_Plugin_Debug'))) {
	        if (($_loger = $_zf_debug->getPlugin('log'))) {
	             
	            $_loger->mark($name , $logFirst);
	        }
	    }
	}
	
	

	private function return_bytes($val) {
	    
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= (1024 * 1024 * 1024); //1073741824
	            break;
	        case 'm':
	            $val *= (1024 * 1024); //1048576
	            break;
	        case 'k':
	            $val *= 1024;
	            break;
	    }
	
	    return $val;
	}
	
	
	

	/**
	 * 
	 * @param string $message
	 * @param int $errorLevel  Optional
	 * @deprecated use $this->getHelper('Log')->info()
	 */
	protected static function _log_info($message)
	{
	
	    $num_args = func_num_args();
	
	    $errorLevel = $num_args > 1 ? func_get_arg(1) : Zend_Log::INFO;
	
	    if (is_null(self::$_logger)) {
	
	        try {
	            self::$_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
	        } catch (Zend_Controller_Action_Exception $e) {
	            //die($e->getMessage());
	        }
	    }
	
	    if (self::$_logger) {
	
	        self::$_logger->log($message, $errorLevel);
	
	    } else {
	         
	        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
	        $logger = new Zend_Log($writer);
	        $logger->log($message, Zend_Log::INFO);
	
	    }
	
	}
	
	
	/**
	 *
	 * @param string $message
	 * @param int $errorLevel  Optional
	 * @deprecated use $this->getHelper('Log')->error()
	 */
	protected static function _log_error($message = '')
	{
	    $num_args = func_num_args();
	
	    $errorLevel = $num_args > 1 ? func_get_arg(1) : Zend_Log::ERR;
	
	    if (is_null(self::$_logger)) {
	
	        try {
	            self::$_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
	        } catch (Zend_Controller_Action_Exception $e) {
	            //die($e->getMessage());
	        }
	    }
	
	    if (self::$_logger) {
	
	        self::$_logger->log($message, $errorLevel);
	
	    } else {
	
	        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
	        $logger = new Zend_Log($writer);
	        $logger->log($message, Zend_Log::ERR);
	
	    }
	}
	
	
}