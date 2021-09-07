<?php
use Dompdf\Dompdf;
use Dompdf\Options;
class PatientdiagnosisController extends Pms_Controller_Action {
    //ISPC-2654 Ancuta 07.10.2020
    
    // protected $_patientMasterData = false;
    protected $logininfo = false;

    protected $clientid = false;

    protected $userid = false;

    protected $usertype = false;

    protected $filepass = false;

    protected $dec_id = false;

    protected $enc_id = false;

    protected $ipid = false;

    protected $epid = false;
		
	public function init()
	{

		/* Initialize action controller here */
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->clientid = $logininfo->clientid;
		$this->userid = $logininfo->userid;
		$this->usertype = $logininfo->usertype;
		$this->filepass = $logininfo->filepass;
		$this->logininfo = $logininfo;
		$this->groupid = $logininfo->groupid; //ISPC-2507 Ancuta 05.02.2020
		


		if( strlen($_GET['id']) > '0')	{
			$this->dec_id = Pms_Uuid::decrypt($_GET['id']);
			$this->enc_id = $_GET['id'];
		}
		elseif(strlen($_REQUEST['id'])>'0')
		{
			$this->dec_id = Pms_Uuid::decrypt($_REQUEST['id']);
			$this->enc_id = $_REQUEST['id'];
		}
		else
		{
			//redir to overview if patient encripted is is empty
			$this->_redirect(APP_BASE . "overview/overview");
			exit;
		}
		/* Initialize basic patient verification (patient belongs to this client?) */
		if(!Pms_CommonData::getPatientClient($this->dec_id, $this->clientid))
		{
			//deny acces to this patient as is does not belong to this client
			$this->_redirect(APP_BASE . "overview/overview");
			exit;
		}

		/* Initialize patient common used vars here */
		$this->ipid = Pms_CommonData::getIpid($this->dec_id);
		$this->epid = Pms_CommonData::getEpid($this->ipid);

		//ISPC-791 secrecy tracker
		$user_access = PatientPermissions::document_user_acces();

		//Check patient permissions on controller and action
		$patient_privileges = PatientPermissions::checkPermissionOnRun();

		if(!$patient_privileges)
		{
			$this->_redirect(APP_BASE . 'error/previlege');
			exit;
		}
		
		
		$this
		->setActionsWithPatientinfoAndTabmenus([
		    /*
		     * actions that have the patient header
		     */
		    "overview",
		])
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		     */
		    "overview",
		])
		->setActionsWithLayoutNew([
		    /*
		     * actions that will use layout_new.phtml
		     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
		     */
		    'overview',
		])
		;

	}
 
	
	public function overviewAction()
	{
	    $this->getHelper('viewRenderer')->setViewSuffix('phtml');
	    $clientid = $this->clientid;
	    $userid = $this->userid;
	    $decid = ($this->dec_id !== false) ?  $this->dec_id : Pms_Uuid::decrypt($_GET['id']);
	    $ipid = ($this->ipid !== false) ?  $this->ipid : Pms_CommonData::getIpId($decid);
	    //ISPC-2775 Carmen 04.01.2020
	    if($_REQUEST['ajax']){
	    	$this->_helper->layout->setLayout('layout_ajax');
	    	$this->view->onlyicd = true;
	    }
	    //--
	    // get
	    if (is_null($options) &&  ! empty($_REQUEST['cid'])) {
// 	        $options = PatientdiTable::getInstance()->findOneByIpid($ipid);
	    }
	    
	    $blockname = 'icd'; // similar to patientform/contactform
	    $modules =  new Modules();
	    $clientModules = $modules->get_client_modules($clientid);
	    
	    $pform_icd = new Application_Form_PatientDiagnosis(array(
	        '_block_name'           => $blockname,
            '_clientModules'        => $clientModules,
        ));
	    
	    
	    if (is_null($icd_options) &&  ! empty($ipid)) {
	        
	        $entity = new PatientDiagnosis();
	        $saved = $entity->getAllDiagnosisClinical($this->ipid,$clientid); //APPLY SORITNG!!!! 
	        
	        $icd_options =  ! empty($saved[$this->ipid]) ? $saved[$this->ipid] : array();
	        
	    }
	    
	    $ops_data = $pform_icd->create_diagnosis_clinical($blockname, $icd_options, $ipid,$clientid);
	    $__formHTML = $ops_data;//we need html only;
	    
	    $this->view->{$blockname} = [
	        "__formHTML" => $__formHTML,
	        "__formPDF" => Pms_CommonData::html_prepare_fpdf(Pms_CommonData::html_prepare_dompdf($__formHTML, '12px', 'auto', false)),
	    ];
	    
	    
	    
	    $blockname = 'mre'; // similar to patientform/contactform
	    $pform_mre= new Application_Form_PatientMre();
	   
	    if (is_null($mre_options) &&  ! empty($ipid)) {
	        $mre_options_q = PatientMreTable::getInstance()->findByIpid($ipid);
	        $mre_options  = $mre_options_q->toArray();
	    }
	    
	    $mre_data = $pform_mre->create_mre($blockname, $mre_options, $ipid);
	    $__formHTML = $mre_data;//we need html only;
	    
	    $this->view->{$blockname} = [
	        "__formHTML" => $__formHTML,
	        "__formPDF" => Pms_CommonData::html_prepare_fpdf(Pms_CommonData::html_prepare_dompdf($__formHTML, '12px', 'auto', false)),
	    ];
	    
	    
	    
	    $blockname = 'ops'; // similar to patientform/contactform
	    $pform_ops= new Application_Form_PatientOps();
	    
	    if (is_null($ops_options) &&  ! empty($ipid)) {
	    	$ops_options_q = PatientOpsTable::getInstance()->findByIpid($ipid);
	    	$ops_options  = $ops_options_q->toArray();
	    }
	     
	    $ops_data = $pform_ops->create_ops($blockname, $ops_options, $ipid);
	    $__formHTML = $ops_data;//we need html only;
	     
	    $this->view->{$blockname} = [
	    		"__formHTML" => $__formHTML,
	    		"__formPDF" => Pms_CommonData::html_prepare_fpdf(Pms_CommonData::html_prepare_dompdf($__formHTML, '12px', 'auto', false)),
	    ];
	    
	}
	
	
	public function tabseventsAction()
	{
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout->setLayout('layout_ajax');
	    $ipid = $this->ipid;
	    $clientid = $this->clientid;
	    
	    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
	        $this->_helper->layout()->disableLayout();
	        $this->_helper->viewRenderer->setNoRender(true);
	        $decid = Pms_Uuid::decrypt($_REQUEST['patid']);
	        $ipid = Pms_CommonData::getIpId($decid);
	        
	        switch($_REQUEST['action'])
	        {
	            case 'show_form':
	                switch($_REQUEST['form'])
	                {
	                    case 'icd_add':
	                        if(!empty($_REQUEST['recid'])){
    	                        $modules =  new Modules();
    	                        $clientModules = $modules->get_client_modules($clientid);
    	                        
    	                        $pform_icd = new Application_Form_PatientDiagnosis(array(
    	                            '_block_name'           => 'icd',
    	                            '_clientModules'        => $clientModules,
    	                        ));
    	                        if (is_null($icd_options) &&  ! empty($this->ipid)) {
    	                            
    	                            $entity = new PatientDiagnosis();
    	                            $filter_data = array();
    	                            $filter_data['main_category'] = isset($_REQUEST['main_category'])? $_REQUEST['main_category'] : false;
    	                            $filter_data['secondary_categories'] = isset($_REQUEST['secondary_categories'])? $_REQUEST['secondary_categories'] : false;
    	                            
    	                            $saved = $entity->getAllDiagnosisClinical($this->ipid,$clientid,$filter_data,$_REQUEST['recid']); //APPLY filter !
    	                            
    	                            $icd_options =  ! empty($saved[$this->ipid]) ? $saved[$this->ipid] : array();
    	                            
    	                            $row_data = null;
    	                            if(!empty($_REQUEST['recid']) && !empty($icd_options[$_REQUEST['recid']]) ){
    	                                $row_data = $icd_options[$_REQUEST['recid']];
    	                            }
    	                            
    	                            $row = $pform_icd->create_form_diagnosis_clinical($row_data);
    	                            $this->getResponse()->setBody($row)->sendResponse();
    	                        }
    	                        
	                            
	                        } else{
	                            
    	                        $af = new Application_Form_PatientDiagnosis([
    	                            "_block_name"          => 'icd'
    	                        ]);
    	                        $row = $af->create_form_diagnosis_clinical(null);
    	                        $this->getResponse()->setBody($row)->sendResponse();
	                        }
	                        
	                        
	                        
	                        exit;
	                        break;
	                        //--
	                    case 'mre_add':
	                        $af = new Application_Form_PatientMre();
	                        
	                        if($_REQUEST['recid'])
	                        {
	                            $values = PatientMreTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
	                        }
	                        
	                        $mre_form = $af->create_form($values);
	                        $this->getResponse()->setBody($mre_form)->sendResponse();
	                        
	                        exit;
	                        break;
	                        //--
	                        
                        case 'ops_add':
                        	$afo = new Application_Form_PatientOps();
                        	 
                        	if($_REQUEST['recid'])
                        	{
                        		$values = PatientOpsTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                        	}
                        	 
                        	$ops_form = $afo->create_form($values);
                        	$this->getResponse()->setBody($ops_form)->sendResponse();
                        	 
                        	exit;
                        	break;
                        	//--
	                        
	                    default:
	                        exit;
	                        break;
	                }
	                break;
	                
	            case 'save_form':
	                switch($_REQUEST['form'])
	                {
	                    case 'icdsave':
	                        $form = new Application_Form_PatientDiagnosis();
	                        $post_data[0] = $_POST;
	                        switch($_REQUEST['subaction'])
	                        {
	                        	case 'delete':
	                        		$result = PatientDiagnosisTable::getInstance()->find($_REQUEST['recid']);
	                        		
	                        		if($result)
	                        		{
	                        			$result->delete();
	                        		}
	                        		break;
	                        	default:
	                        		$result = $form->save_form_diagnosis_clinical($ipid, $post_data);
	                        		break;
	                        }
	       
	                        if($result){
	                            echo '1';
	                            exit;
	                        } else{
	                            echo 'error';
	                            exit;
	                            
	                        }
	                        break;
	                    case 'mresave':
	                        
	                        /* $form = new Application_Form_PatientMre();
	                        $post_data = $_POST;
	                        $result = $form->save_mre($ipid, $post_data);
	       
	                        if($result){
	                            echo '1';
	                            exit;
	                        } else{
	                            echo 'error';
	                            exit;
	                            
	                        }
	                        break; */
	                    	$formm = new Application_Form_PatientMre();
	                    	switch($_REQUEST['subaction'])
	                    	{
	                    		case 'delete':
	                    			$result = PatientMreTable::getInstance()->find($_REQUEST['recid']);
	                    	
	                    			if($result)
	                    			{
	                    				$result->delete();
	                    			}
	                    			break;
	                    		default:
	                    			 
	                    			$post_data = $_POST;
	                    			$result = $formm->save_mre($ipid, $post_data);
	                    			break;
	                    	}
	                    	/* $formo = new Application_Form_PatientOps();
	                    	 $post_data = $_POST;
	                    	 $result = $formo->save_ops($ipid, $post_data); */
	                    	 
	                    	if($result){
	                    		$blockname = 'mre'; // similar to patientform/contactform
	                    		//$pform_ops= new Application_Form_PatientOps();
	                    	
	                    		$mre_options_q = PatientMreTable::getInstance()->findByIpid($ipid);
	                    		$mre_options  = $mre_options_q->toArray();
	                    	
	                    	
	                    		$mre_data = $formm->create_mre($blockname, $mre_options, $ipid);
	                    		$__formHTML = $mre_data;//we need html only;
	                    	
	                    	
	                    		echo $__formHTML ;
	                    		exit;
	                    	} else{
	                    		echo 'error';
	                    		exit;
	                    		 
	                    	}
	                    	break;
	   
                        case 'opssave':
                        	
                        	$formo = new Application_Form_PatientOps();
                        	switch($_REQUEST['subaction'])
                        	{
                        		case 'delete':
                        			$result = PatientOpsTable::getInstance()->find($_REQUEST['recid']);
                                    
                                    if($result)
                                    {
                                        $result->delete();
                                    }
                        			break;
                        		default:
                        			
                        			$post_data = $_POST;
                        			$result = $formo->save_ops($ipid, $post_data);
                        			break;
                        	}
                        	/* $formo = new Application_Form_PatientOps();
                        	$post_data = $_POST;
                        	$result = $formo->save_ops($ipid, $post_data); */
                        	
                        	if($result){
                        		$blockname = 'ops'; // similar to patientform/contactform
                        		//$pform_ops= new Application_Form_PatientOps();
                        		
                        			$ops_options_q = PatientOpsTable::getInstance()->findByIpid($ipid);
                        			$ops_options  = $ops_options_q->toArray();

                        		
                        		$ops_data = $formo->create_ops($blockname, $ops_options, $ipid);
                        		$__formHTML = $ops_data;//we need html only;
                        		
                        		
                        		echo $__formHTML ;
                        		exit;
                        	} else{
                        		echo 'error';
                        		exit;
                        		 
                        	}
                        	break;
      
	                        
	                    default:
	                        exit;
	                        break;
	                }
	                break;
	                
	            case 'roworder':
	                $order_data = array();
	                if(!empty($_REQUEST['order']) && !empty($this->ipid)){
	                    foreach($_REQUEST['order'] as $order=>$diagno_id){
	                        $order_data[ $diagno_id ] = $order+1;
	                    }
	                    if(!empty($order_data)){
	                        foreach($order_data as $pat_diagno_id=>$custom_order){
	                            $update = Doctrine_Query::create()
	                            ->update('PatientDiagnosis')
	                            ->set('custom_order', $custom_order)
	                            ->where("ipid = ?", $this->ipid )
	                            ->andWhere('id =?', $pat_diagno_id);
	                            $update_res = $update->execute();
	                        }
	                        
	                        
	                        
	                        //get data for refresh
	                        $blockname = 'icd'; // similar to patientform/contactform
	                        
	                        $modules =  new Modules();
	                        $clientModules = $modules->get_client_modules($clientid);
	                        
	                        $pform_icd = new Application_Form_PatientDiagnosis(array(
	                            '_block_name'           => $blockname,
	                            '_clientModules'        => $clientModules,
	                        ));
	                        if (is_null($icd_options) &&  ! empty($this->ipid)) {
	                            
	                            $entity = new PatientDiagnosis();
	                            $filter_data = array();
	                            $filter_data['main_category'] = isset($_REQUEST['main_category'])? $_REQUEST['main_category'] : false;
	                            $filter_data['secondary_categories'] = isset($_REQUEST['secondary_categories'])? $_REQUEST['secondary_categories'] : false;
	                            
	                            $saved = $entity->getAllDiagnosisClinical($this->ipid,$clientid,$filter_data); //APPLY filter !
	                            
	                            
	                            
	                            $icd_options =  ! empty($saved[$this->ipid]) ? $saved[$this->ipid] : array();
	                        }
	                        $icd_options['filter'] = true;
	                        $icd_options['ro'] = true;
	                        $ops_data = $pform_icd->create_diagnosis_clinical($blockname, $icd_options, $ipid,$clientid);
	                      
	                        if($ops_data){
	                            echo  json_encode($ops_data);
	                        } else{
	                            echo  "0";
	                            exit;
	                        }
	                        
	                        
	                        
	                        exit; 
	                    } else {
	                        echo  "0";
	                        exit; 
	                    }
	                    
	                } else {
	                    echo  "0";
	                    exit;
	                }
	                
	                break;
	            case 'filterdata':
	                switch($_REQUEST['form'])
	                {
                        case 'icd_filter':
                     
                            
                        		$blockname = 'icd'; // similar to patientform/contactform
                        		
                        		$modules =  new Modules();
                        		$clientModules = $modules->get_client_modules($clientid);
                        		
                        		$pform_icd = new Application_Form_PatientDiagnosis(array(
                        		    '_block_name'           => $blockname,
                        		    '_clientModules'        => $clientModules,
                        		));
                        		if (is_null($icd_options) &&  ! empty($this->ipid)) {
                        		    
                        		    $entity = new PatientDiagnosis();
                        		    $filter_data = array();
                        		    $filter_data['main_category'] = isset($_REQUEST['main_category'])? $_REQUEST['main_category'] : false;
                        		    $filter_data['secondary_categories'] = isset($_REQUEST['secondary_categories'])? $_REQUEST['secondary_categories'] : false;
                        		    
                        		    $saved = $entity->getAllDiagnosisClinical($this->ipid,$clientid,$filter_data); //APPLY filter !
                        		    
                        	
                        		    
                        		    $icd_options =  ! empty($saved[$this->ipid]) ? $saved[$this->ipid] : array();
                        		}
                        		$icd_options['filter'] = true;
                        		$ops_data = $pform_icd->create_diagnosis_clinical($blockname, $icd_options, $ipid,$clientid);
                        		
                        		if($ops_data){
                            		echo  json_encode($ops_data);
                        		} else{
                            		echo  "0";
                        		    exit;
                        		}
                        		exit;
                       
                        	break;
      
	                        
	                    default:
	                        exit;
	                        break;
	                }
	                break;
	 
	            default:
	                exit;
	                break;
	                
	        }
	    }
	}
	
		
}
	