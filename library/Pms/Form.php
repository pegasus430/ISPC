<?php

// require_once("Zend/Form.php");
/**
 * class update Jul 13, 2017 claudiu  
 * __construct() was introduced so we can pass some variables without using the globals from Zend_Registry
 * $logininfo
 * $_block_name
 * $_patientMasterData
 * 
 * class update Jul 20, 2017 claudiu 
 * $this->translate() Translator wrapper introduced
 * 
 * @claudiu 17.11.2017 
 * changes to getErrorMessages(), so you can use Pms_Form and fetch the parent messages (ex: wlassessmentAction)
 */
class Pms_Form extends Zend_Form
{
	protected $error_message = array();
	
	protected $validators = array('Text'=>'isstring','Email'=>'email','Number'=>'isnumeric');
	
	/**
	 * 
	 * @var Zend_Session_Namespace
	 * 
	 * @update : 2017.12.05
     * ->userid
     * ->clientid
     * ->groupid
     * ->usertype
     * ->requesturl
     * ->setlater
     * ->parentid
     * ->username
     * ->loginclientid
     * ->sca
     * ->multiple_clients
     * ->mastergroupid
     * ->hospiz
     * ->showinfo
     * ->inactivetime
     * ->lastactive
     * ->filepass
     * ->loguname
	 */
	protected $logininfo = null;
	
	/**
	 * 
	 * @var string
	 */
	protected $_model = null;
	
	/**
	 * 
	 * @var string
	 */
	protected $_block_name = null;
	
	
	/**
	 * @see $this->filter_by_block_name
	 * @example array( $this->_block_name => array(create_form_xxx => array( allowed_element_1, allowed_element_99 )) )
	 * 
	 * @var array
	 */
	protected $_block_name_allowed_inputs = null;
	
	
	/**
	 * available checkboxes for each block
	 * @var array
	 */
	protected $_block_feedback_options = null;
	
	/**
	 * current value
	 * @var array
	 */
	protected $_block_feedback_values = null;
	
	/**
	 * 
	 * @var array 
	 * @see PatientMaster -> get_patientMasterData();
	 */
	protected $_patientMasterData = null;

	/**
	 * 
	 * @var string
	 */
	protected $_ipid = null;

	/**
	 * 
	 * @var array 
	 * @see ExtraForms -> get_client_forms()
	 */
	protected $_clientForms = null;

	/**
	 * 
	 * @var array 
	 * @see Modules -> get_client_modules()
	 * 
	 * @deprecated use $this->_patientMasterData['ModulePrivileges'][NUMBER]
	 * 
	 */
	protected $_clientModules = null;
	
	/**
	 *
	 * @var array 
	 * @see Client
	 * 
	 * @deprecated
	 */
	protected $_client = null;
	
	/**
	 * 
	 * @var Zend_Mail_Transport_Smtp
	 */
	protected $_mail_transport     = null;
	protected $_mail_forceDefaultSMTP   = true;//force the usage of ISPC's own smtp
	protected $_mail_FromEmail;
	protected $_mail_FromName;
	protected $_mail_ReplyTo;  //TODO-2393 p3 Ancuta 04.07.2019// Maria:: Migration ISPC to CISPC 08.08.2020
	
	
	protected $qqfileupload = null; //this is the full qqFileUpload session
	
	protected $_translate_lang_array =  null;
	
	/**
	 * 
	 * @var Zend_View_Helper_Translate
	 */
	protected $translator;
	
	/**
	 * map a create_form_xxx() to a save_fom_xxx();
	 * @var array(string)
	 */
	protected $map_create_save = array();
	
	/**
	 * map a create_form_xxx() to a validate_fom_xxx();
	 * @var array(string)
	*/
	protected $map_create_validate = array();
	
	
	protected static $_fn_options = [];
	
	/**
	 * @update @cla on 17.2.2018 -  _patientMasterData is a reference
	 * @param string $options
	 */
	public function __construct( $options = null)
	{
		//all properties are accesible via getAttrib()
		
	    $this->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH. '/forms/decorator/', 'DECORATOR');
	   	    
		$this->logininfo = new Zend_Session_Namespace('Login_Info');	
		
		if (isset($options['_patientMasterData'])) {
		    
		    
// 			$this->_patientMasterData = $options['_patientMasterData'];
		    $this->setPatientMasterDataReference($options['_patientMasterData']);
			
			if (is_null($this->_ipid)) {
			    $this->_ipid = $this->_patientMasterData['ipid'];
			}
			
			unset($options['_patientMasterData']);
		}
		
		if (isset($options['_block_name'])) {
			$this->_block_name = $options['_block_name'];
			unset($options['_block_name']);
		}
		
		if (isset($options['_block_feedback_options'])) {
		    $this->setBlockFeedbackOptionsReference($options['_block_feedback_options']);
			unset($options['_block_feedback_options']);
		}
		
		if (isset($options['_block_feedback_values'])) {
		    $this->setBlockFeedbackValuesReference($options['_block_feedback_values']);
			unset($options['_block_feedback_values']);
		}
		
		if (isset($options['_clientForms'])) {
		    $this->_clientForms = $options['_clientForms'];
		    unset($options['_clientForms']);
		}
		
		if (isset($options['_clientModules'])) {
		    $this->_clientModules = $options['_clientModules'];
		    unset($options['_clientModules']);
		}
		
		if (isset($options['_client'])) {
		    $this->_client = $options['_client'];
		    unset($options['_client']);
		}
		
		if (isset($options['_fn_options'])) {
		    self::$_fn_options = $options['_fn_options'];
		    unset($options['_fn_options']);
		}
		
		$this->translator = new Zend_View_Helper_Translate();
		
		parent::__construct($options);
	}
	
	public function getErrorMessages($parent = null)
	{
		return is_null($parent)? $this->error_message : parent::getErrorMessages();	
	}
	
	public function assignErrorMessages()
	{
		$view = Zend_Layout::getMvcInstance()->getView();
		
		$error="";		
		foreach($this->error_message as $key=>$val)
		{
			$error = "error_".$key;
			
			$view->$error = $val;
		}
	}
	
	/**
	 * Translator wrapper
	 * Jul 20, 2017 @claudiu 
	 * 
	 * @update 2017.12.18 : first translate from the $_translate_lang_array, then use default
	 * @update 2018.01.11 : changed to use it with params for vsprintf 
	 * //translate('%1\$s + %2\$s', $value1, $value2, $locale);
	 * 
	 * @param string $string  The string to be translated
	 * @return The translated string
	 */
	protected function translate($string)
	{
	    $lang_array = ! empty($this->_translate_lang_array) ? $this->getTranslator()->translate($this->_translate_lang_array) :  null;
	    
	    if (empty($lang_array) || ! isset($lang_array[$string])) {
	        //original translator
	        //return call_user_func_array(array($this->getTranslator(), 'translate'), func_get_args());
	        return call_user_func_array(array($this->translator, 'translate'), func_get_args());
	    } else {
	        //...i've groupped translations into arrays, a good idea at the time.. a BAD ideea now (2018)
	        
	        $messageid =  $lang_array[$string];
	        
	        //from original translate
	        $options   = func_get_args();
	        array_shift($options);
	        $count  = count($options);
	        $locale = null;
	        if ($count > 0) {
	            if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
	                $locale = array_pop($options);
	            }
	        }
	        if ((count($options) === 1) and (is_array($options[0]) === true)) {
	            $options = $options[0];
	        }
	        	        
	        if (count($options) === 0) {
	            return $messageid;
	        }
	         
	        return vsprintf($messageid, $options);
	    }
	}
	
	
	/**
	 * find by key in our mutidimensional _patientMasterData
	 * Dec 5, 2017 @claudiu
	 *  
	 * @param unknown $key
	 * @param string $array
	 * @param string $ipid
	 * @return Ambigous <multitype:, mixed>
	 */
	protected function findKeyInPatientMasterData($key, $array =  null, $ipid = null)
	{
	    if (is_null($array)) $array = $this->_patientMasterData;
	    if (is_null($ipid)) $ipid = $array['ipid'];
	    $result = array();
	    if (is_array($array) && ! empty($array)) {
	        if (isset($array[$key]) && isset($array[$key][$ipid])) {
	            $result = reset($array[$key][$ipid]);
	        } else {
	            foreach ($array as $subarray) {
	                if ( ! empty($subarray)) $result = array_merge($result, $this->findKeyInPatientMasterData($key, $subarray, $ipid));
	            }
	        }
	    }
	    return $result;
	}
	
	
	/**
	 * @date 2017.12.05
	 * @param string $email
	 * @param string $email_subject
	 * @param string $email_body 
	 * @param string $email_attachment_path 
	 */
	protected function sendEmail($email_receiver = '', $email_subject = '', $email_body = '', $email_attachment_path = null)
	{
	    $emailValidator = new Zend_Validate_EmailAddress();
        if(empty($email_receiver) || ! $emailValidator->isValid($email_receiver)) {
           return;
        }
        
        $attachment = null;
        if( ! is_null($email_attachment_path) && file_exists($email_attachment_path) )
        {
            $att = new Zend_Mime_Part(file_get_contents($email_attachment_path));
            $att->type        = mime_content_type($email_attachment_path);
            $att->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $att->encoding    = Zend_Mime::ENCODING_BASE64;
            $att->filename    = basename($email_attachment_path);
        }
        
        if (is_null($this->_mail_transport) || ! $this->_mail_transport instanceof Zend_Mail_Transport_Smtp ) {
            $c_smpt_s       = new ClientSMTPSettings();
            $smtp_settings  = $c_smpt_s->get_mail_transport_cfg( $this->logininfo->clientid, true, $this->_mail_forceDefaultSMTP);
            
            $this->_mail_transport    = new Zend_Mail_Transport_Smtp( $smtp_settings['host'], $smtp_settings['config'] );
            $this->_mail_FromEmail    = $smtp_settings['sender_email'];
            $this->_mail_FromName     = $smtp_settings['sender_name'];
            $this->_mail_ReplyTo      = $smtp_settings['sender_email']; //TODO-2393 p3 Ancuta 04.07.2019
        }
            
        $mail = new Zend_Mail('UTF-8');
        $mail->setFrom($this->_mail_FromEmail, $this->_mail_FromName)
        ->setReplyTo($this->_mail_ReplyTo) //TODO-2393 p3 Ancuta 04.07.2019
        ->addTo($email_receiver)
        ->setSubject($email_subject);
         
        if ($attachment && $attachment instanceof Zend_Mime_Part) {
            $mail->addAttachment($attachment);
        }
         
        if(Pms_CommonData::assertIsHtml($email_body)) {
            $mail->setBodyHtml($email_body);
        } else {
            $mail->setBodyText($email_body);
        }
         
        $mail->send($this->_mail_transport);
	
        return true;
	
	}
	
	/**
	 * @date 2017.12.07
	 * 
	 * @param unknown $options
	 * @return Zend_Form_SubForm
	 */
	protected function subFormTable ($options = array())
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
	    $subform->setDecorators(array('FormElements' , array('SimpleTable', $options) , 'Fieldset'));
	    return $subform;
	    
	}
	/**
	 * @date 2017.12.07
	 * 
	 * @param unknown $options
	 * @return Zend_Form_SubForm
	 */
	protected function subFormTableRow ($options = array())
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
	    $subform->setDecorators(array('FormElements' , array('SimpleTableRow', $options) ));
	    $subform->setElementDecorators(array(
	        'ViewHelper',
	        array('Errors'),
	        array(array('data' => 'HtmlTag'), array('tag' => 'td'))
	    ));
	    
	    return $subform;
	    
	}
	
	/**
	 * @date 2018.12.24
	 * 
	 * @param unknown $options
	 * @return Zend_Form_SubForm
	 */
	protected function subFormContactformBlock ($options = array())
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
	    $subform->setDecorators(array('FormElements' , array('SimpleTable', $options), array('SimpleContactformBlock', $options)) );
	    $subform->setElementDecorators(array(
	        'ViewHelper',
	        array('Errors'),
// 	        array(array('data' => 'HtmlTag'), array('tag' => 'td'))
	    ));
	    
	    return $subform;
	}
	
	
	protected function qqSaveFiles($action_name = null,  $options = array() )
	{
	    
	    $result = array();
	    	
	    $ipid = $options['ipid'];
	    	
	    $qquuid = ! isset($options['qquuid']) ?: $options['qquuid'];
	    	
	    $remove_after_save =  $options['remove_after_save'];
	    	
	    $save_options = $options['options'];
	    
	    $last_files = $this->qqGetFiles( $action_name, $qquuid );

	    if ( ! empty($last_files)) {
	        	
	        $af = new Application_Form_PatientFileUpload();
	        $result = $af->saveFiles( $ipid, $last_files, $qquuid, $save_options);
	    
	        //result should containn an array with ids from saved files
	        if ( ! empty($result)) {
	            	
	            //$result['redirect'] = true;
	            //$response['redirect_location'] = true;
	            if ( $remove_after_save == true ) {
	                //$this->set_last_uploaded_file( $action_name, $qquuid); //unlink last saved files from this action
	                $this->qqUnlinkFiles( $action_name); //unlink all files from this action
	            }	
	        }	
	    } //else we have no files to upload
	    	
	    return $result;
	    	
	}
	
			
// 	protected function get_last_uploaded_file( $action = "action_name", $qquuid = null )
	protected function qqGetFiles( $action = "action_name", $qquuid = null )
	{
	    $this->qqfileupload = new Zend_Session_Namespace('qqFileUpload');
	    	    
	    $result = false;
	
	    $clientid_sufixed =  "client_" . $this->logininfo->clientid;
	    	
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
	
// 	protected function set_last_uploaded_file( $action = "action_name", $qquuid = null, $filepath = null, $filename = null )
	protected function qqUnlinkFiles( $action = "action_name", $qquuid = null, $filepath = null, $filename = null )
	{
	    $this->qqfileupload = new Zend_Session_Namespace('qqFileUpload');
	    
	    $clientid_sufixed =  "client_" . $this->logininfo->clientid;
	
	    if( is_null($qquuid)) {
	        //delete all previous files from this action
	        	
	        $all_files = $this->qqfileupload->{$action}->{$clientid_sufixed};
	        	
	        foreach ($all_files as $file) {
	            if( ! empty($file['filepath']) && is_file($file['filepath'])) {
	                @unlink($file['filepath']);
	            }
	        }
	        	
	        $this->qqfileupload->{$action}->{$clientid_sufixed} = array();
	
	    }
	    else{
	        //delete just one file from this $qquuid
	        if(($file = self::get_last_uploaded_file($action, $qquuid)) !== false) {
	            @unlink($file [$qquuid] ['filepath']);
	            unset($this->qqfileupload->{$action}->{$clientid_sufixed} [$qquuid]);
	        }
	        	
// 	        //append new file
// 	        if( ! is_null($filepath) && file_exists($filepath)) {
// 	            $this->qqfileupload->{$action}->{$clientid_sufixed} [$qquuid] = array(
// 	                "action"	=> $action,
// 	                "qquuid"	=> $qquuid,
// 	                "filepath"	=> $filepath,
// 	                "filename"	=> $filename,
// 	                "ipid"		=> $this->ipid,
// 	                "dec_id"	=> $this->dec_id,
// 	            );
// 	        }
	        	
	        	
	    }
	    	
	}
	
	
	
	
	
	
	/**
	 * 
	 * !! multiple return types !!
	 * 
	 * this was created for versorger, to work on all create_xxx
	 * it's a universal validate to work with triggerValidateFunction
	 * 
	 * return === true is no errors or failed
	 * return form->__toString() on errors (change to form->render() if needed)
	 *
	 * @param unknown $data
	 * @return string|Zend_Form|boolean
	 */
	public function create_form_isValid()
	{
	    
	    
	    $ipid = null;
	    $post = null;
	     
	    $numargs = func_num_args();
	    $call = func_get_arg($numargs - 1);//this is hardcoded in Pms_Form
	    $numargs --;
	     
	    $arg_0 = func_get_arg(0);
	    $arg_1 = func_get_arg(1);
	     
	    if ($numargs >= 2) {
	        if (is_string($arg_0)) {
	            $ipid = $arg_0;
	            $post = $arg_1;
	        } else {
	            $ipid = $arg_1;
	            $post = $arg_0;
	        }
	    } else {
	        $post = $arg_0;
	    }
	     
	    $create_fn = $this->getCreateFunction($call['create_function']);
	     
	    
	    
	    if ($create_fn && method_exists($this, $create_fn)) {
	        
	        $form = $this->{$create_fn}($post);
	        
	        if ($form instanceof Zend_Form) {
	            
	            if ($form->isValid($post)) {
	                 
	                return $form;
	                 
	            } else {
	                
	                
	                $errors = $form->getMessages();
	                
	                foreach ($errors as $k => $page_err) {
	                    foreach ($page_err as $field => $field_errors){
	                        $this->addErrorMessages($field_errors);
	                    }
	                }
	                
	                return ($form->__toString());
	            }
	            
	        } else {
	            
	            return true; //something went very wrong .. but still true
	        }
	         
	    } else {
	        
	        return true;
	    }
	}
	
	
	
	/**
	 * setter
	 * 
	 * map a 'create_xxx' => 'save_xxx', so I can later call triggerSaveFunction('create_xxx', $data)
	 * 
	 * @param string $create_fn
	 * @param string $save_fn
	 * @return array(string)
	 */
	public function mapSaveFunction($create_fn = '', $save_fn = '')
	{
	    if (empty($create_fn) || empty($save_fn)) {
	        
	        return false;
	        
	    } else {
	        
	        $this->map_create_save[$create_fn] = $save_fn;
	        
	        return true;
	    }
	}
	
	
	/**
	 * 
	 * @param string $create_fn
	 * @return array(string)
	 */
	public function getSaveFunction($create_fn = null)
	{
	    return is_null($create_fn) ? $this->map_create_save : $this->map_create_save[$create_fn];
	}
	
	
	/**
	 * 
	 * @param string $create_function
	 * @param unknown $data
	 * @return boolean|mixed
	 */
	public function triggerSaveFunction($create_function = "", $data = array()) 
	{
	    if (empty($create_function)) {
	        return false; //fail-safe
	    } 
	    
	    $saveMethodName = $this->getSaveFunction($create_function);
	    
	    
	    if ( ! empty($saveMethodName) 
	        && method_exists($this, $saveMethodName)) 
	    {
	        return call_user_func_array(array($this, $saveMethodName), $data) ;

	    } else {
	        
	        return false;
	    }
	    
	}
	
	
	
	
	/**
	 * setter
	 *
	 * map a 'create_xxx' => 'save_xxx', so I can later call triggerSaveFunction('create_xxx', $data)
	 *
	 * @param string $create_fn
	 * @param string $validate_fn
	 * @return array(string)
	 */
	public function mapValidateFunction($create_fn = '', $validate_fn = '')
	{
	    if (empty($create_fn) || empty($validate_fn)) {
	        
	        return false;
	        
	    } else {
	         
	        $this->map_create_validate[$create_fn] = $validate_fn;
	         
	        return true;
	    }
	}

	
	/**
	 * 
	 * @param string $create_fn
	 * @return array(string)
	 */
	public function getValidateFunction($create_fn = null)
	{
	    return is_null($create_fn) ? $this->map_create_validate : $this->map_create_validate[$create_fn];
	}
	
	
	/**
	 * defaults to true if no validate fn is defined
	 * 
	 * @param string $create_function
	 * @param array $data
	 * @return boolean|mixed
	 */
	public function triggerValidateFunction($create_function = "", $data = array())
	{
	    if (empty($create_function)) {
	        return false; //fail-safe
	    }
	    
	    
	    $validateMethodName = $this->getValidateFunction($create_function);

	    
	    if ( ! empty($validateMethodName)
	        && method_exists($this, $validateMethodName))
	    {
	        return call_user_func_array(array($this, $validateMethodName), array_merge($data, array(array('__call'=> true, 'create_function' => $create_function, "validateMethodName"=>$validateMethodName)))) ;
	
	    } else {
	         
	        return true;
	    }
	     
	}
	
	
	/**
	 * 
	 * @param string $key_fn
	 * @param string $fromFn
	 * @return boolean|string
	 */
    public function getCreateFunction($key_fn = null, $fromFn = 'validate')
	{
	    if (empty($key_fn)) {
	        return false; //fail-safe
	    }
	    
	    if ($fromFn == 'validate') {
	        
	        $validat_fn = $this->map_create_validate[$key_fn];
	        
	        $allmethods = array_keys(array_filter($this->map_create_validate, function($item) use ($validat_fn){
	            return $item == $validat_fn;
	        }));
	        
	        foreach ($allmethods as $method) {
	            if (method_exists($this, $method)){
	                return $method;
	            }
	        }
	        
	        
	    } elseif ($fromFn == 'save') {
	        
	        
	        $save_fn = $this->map_create_save[$key_fn];
	         
	        $allmethods = array_keys(array_filter($this->map_create_save, function($item) use ($save_fn){
	            return $item == $validat_fn;
	        }));
             
            foreach ($allmethods as $method) {
                if (method_exists($this, $method)){
                    return $method;
                }
            }	        
	    }
	    
	    return false;
	    
	}
	
	
	/**
	 * filter/remove elements from create_form_XXX, to send only the ones needed for our block
	 * 
	 * if no $_block_name_allowed_inputs defined for this $_block_name/create_form_XXX, then send all
	 * 
	 * example : WlAssessment uses less inputs then PatientDetails, so in Application_Form_PatientMaster we filter
	 * 
	 * Zend_Form_Element_Hidden elements are NOT removed
	 * __removed => this are removed
	 * __allowed => only this are allowed, this superseds the neeed for __removed
	 *
	 * @param Zend_Form $zform
	 * @param string $keyFn
	 * @return Zend_Form
	 */
	protected function filter_by_block_name(Zend_Form $subform, $keyFn = '')
	{
	    
	    $this->feedback_by_block_name($subform, $keyFn);
	    
	    
	    if (empty($this->_block_name)
	        || empty($keyFn)
	        || empty($this->_block_name_allowed_inputs)
	        || ! isset($this->_block_name_allowed_inputs [$this->_block_name] [$keyFn]))
	    {
	        return $subform; //nothing to filter/remove
	    }
	
	    $haystack = $this->_block_name_allowed_inputs [$this->_block_name] [$keyFn];
	
	    $elements = $subform->getElements();
	    
	    
	    $subforms = $subform->getSubForms();
	    
	    foreach($subforms as $sf) {

	        $sfName = $sf->getName();
	        
	        if ( ! empty($haystack['__removed'])
	            && in_array($sfName, $haystack['__removed']))
	        {
	            $subform->removeSubForm($sfName);
	            continue;
	        }
	         
	        if ( ! empty($haystack['__allowed'])
	            && ! in_array($sfName, $haystack['__allowed']))
	        {
	            $subform->removeSubForm($sfName);
	            continue;
	        }
	    }
	
	    foreach ($elements as $element) {
	
	        if ($element->getType() == 'Zend_Form_Element_Hidden') { //Zend_Form_Element_Hidden elements are not removed
	            continue;
	        }
	
	        $elName = $element->getName();
	
	        if ( ! empty($haystack['__removed']) 
	            && in_array($elName, $haystack['__removed'])) 
	        {
                $subform->removeElement($elName);
                continue;
	        }
	        
	        if ( ! empty($haystack['__allowed']) 
	            && ! in_array($elName, $haystack['__allowed'])) 
	        {
	            $subform->removeElement($elName);
	            continue;
	        }
	        
	    }

	    
	    return $subform;
	}
	
	
	protected function feedback_by_block_name(Zend_Form $subform, $keyFn = '')
	{
	    if (empty($this->_block_name)
	        || empty($keyFn)
	        || empty($this->_model)
	        || empty($this->_block_feedback_options)
	        || ! isset($this->_block_feedback_options [$this->_block_name] [$keyFn]))
	    {
	        return $subform; //nothing to filter/remove
	    }

        $__feedback_options = [
            "__block_name" => $this->_block_name,
            "__parent"     => $this->_model,
            "__fnName"     => $keyFn,
            "__parentID"   => '',
            "__meta"       => [
                /*
                "todo",
                "feedback",
                "benefit_plan",
    	        "heart_monitoring",
    	        "referral_to",
    	        "further_assessment",
    	        "training_nutrition",
    	        "training_adherence",
    	        "training_device",
    	        "training_prevention",
    	        "training_incontinence",
    	        "organization_careaids",
    	        "inclusion_COPD",
    	        "inclusion_measures",
                */
            ],
            "__meta_val" => []
        ];
	   
	    
	    
	    foreach ($this->_block_feedback_options [$this->_block_name] [$keyFn] as $opt) {

	        $__feedback_options['__meta'][$opt] = (! empty($this->_block_feedback_values [$this->_model ][$this->_block_name][$keyFn])) ? $this->_block_feedback_values [$this->_model ][$this->_block_name][$keyFn][$opt] : 'no';
	        
	        
	        /*
	         * @since 19.02.2019
	         * each $opt can have some values saved in "{$opt}_val" field 
	         */
	        if (isset($this->_block_feedback_values [$this->_model ][$this->_block_name][$keyFn]["{$opt}_val"])) {
	            
	            $__feedback_options['__meta_val'][$opt] = $this->_block_feedback_values [$this->_model ][$this->_block_name][$keyFn]["{$opt}_val"];	            
	            
	        }
	        
	    }
	    
	    $__feedback_options = Zend_Json::encode($__feedback_options);
        $subform->setAttrib("data-feedback_options", $__feedback_options);
	        
	        
        $classes = $subform->getAttrib('class') ?: '';
        $subform->setAttrib('class', $classes . " has_feedback_options");
        
        
        return $subform;

	}
	
	/**
	 * 
	 * @param Zend_Form $subform
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	protected function __setElementsBelongTo(Zend_Form $subform, $elementsBelongTo)
	{
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    return $subform;
	}
	
	
	/**
	 * reference used to Pms_Controller_Action
	 * 
	 * @param array $patientMasterData
	 * @return Pms_Form
	 */
	public function setPatientMasterDataReference( &$patientMasterData )
	{
	    $this->_patientMasterData =& $patientMasterData;
	    
	    return $this;
	}
	
	
	/**
	 * 
	 * @param array $feedback_options
	 * @return Pms_Form
	 */
	public function setBlockFeedbackOptionsReference( &$feedback_options )
	{
	    $this->_block_feedback_options =& $feedback_options;
	     
	    return $this;
	}
	
	
	/**
	 * 
	 * @param array $feedback_options
	 * @return Pms_Form
	 */
	public function setBlockFeedbackValuesReference( &$feedback_values )
	{
	    $this->_block_feedback_values =& $feedback_values;
	     
	    return $this;
	}
	
	
	
	public static function setFnOptions( $options = [] , $fn = null)
	{
	    if ($fn === null) {	        
    	    self::$_fn_options = $options;
	    } else {
	        self::$_fn_options[$fn] = $options;
	    }
	}
	
	public static function getFnOptions( $fn = null)
	{
	    if ($fn === null) {
	        return self::$_fn_options;
	    }
	    elseif ( ! empty(self::$_fn_options) && isset(self::$_fn_options[$fn])) {
	        return self::$_fn_options[$fn];
	    } 
	    
	    return null;
	}
	
	
	
}

?>