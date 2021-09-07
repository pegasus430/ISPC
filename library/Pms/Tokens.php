<?php
/**
 * 
 * @author claudiu  
 * Jul 26, 2017 
 *
 * if you don't init the class with $controller param(or param is wrong), you will get all the tokens
 */
class Pms_Tokens
{
	
    private $_tokens = array();
    
    private $_tokens_missing = array(); // this is for debug purposes to see what tokens will not be replaced because you did not provide a value for it
    
	private $_token_prefix = "$";
	
	private $_token_sufix = "$";
	
	private $_selected_token_controller = null;
	
	private $_selected_token_list = null;
	
	private $translator = null;
	
	private $_default_tokens_array = null;
	
	/**
	 * used only for docx, only this are allowed to be replaceVariableByHTML
	 * @var unknown
	 */
	private $_default_html_tokens =  array(
	    
	        'address'       => 'block',
			'SAPV_Rechnungsempfaenger'  => 'block', //ISPC-1236 Carmen 20.02.2019
	        'footer'        => 'block',
	        'benutzer_adresse' => 'block',
	        'recipient'     => 'block',
	        'comment'       => 'block',
			'client_address' => 'block', // Added by Carmen 13.06.2019 ISPC-2399
			'healthinsurance_address' => 'block', // Added by Carmen 13.06.2019 ISPC-2399
			// Maria:: Migration ISPC to CISPC 08.08.2020
			'invoiceheader' => 'block', // Added by Carmen 01.07.2019 ISPC-2365
			'invoicefooter' => 'block', // Added by Carmen 01.07.2019 ISPC-2365
			'user_address' => 'block', // Added by Carmen 01.07.2019 ISPC-2365
			'doctor_address' => 'block', // Added by Carmen 30.07.2019 ISPC-2394
			//'report_period' => 'block', // Added by Carmen 30.07.2019 ISPC-2394
			'stample' => 'block', // Added by Carmen 24.11.2020 ISPC-2745
	         
	        'invoice_items_html'        => 'inline',
			'invoice_items_html_short'        => 'inline', //ISPC-1236 Carmen 20.02.2019
	        'control_sheet'             => 'inline',
	        'internal_invoice_items_html'=> 'inline',
			'internal_invoice_items_html_short'        => 'inline', //ISPC-1236 Carmen 08.03.2019
			'html_anlage2a' => 'inline',  // Added by Carmen 13.06.2019 ISPC-2399
			'html_anlage2b' => 'inline',  // Added by Carmen 13.06.2019 ISPC-2399
			// Maria:: Migration ISPC to CISPC 08.08.2020
			'html_new' => 'inline',  // Added by Carmen 30.07.2019 ISPC-2394
			'html_old' => 'inline',  // Added by Carmen 30.07.2019 ISPC-2394
			'report_period' => 'inline', // Added by Carmen 30.07.2019 ISPC-2394
	);
	
	
	/**
	 * this allows to have same $token$ for multiple columns... it will use the one you sent
	 * if you send both, then you better have different tokens for them .. shame on you if not
	 * 
	 * @var unknown
	 */
	private $_token_keys_filter = array();

	private $_token_groups_filter = array();
	
	public function __construct( $controller = null)
	{
		$this->_selected_token_controller = $controller;
		
		$this->translator = new Zend_View_Helper_Translate();
		
		$this->_default_tokens_array = $this->translator->translate('tokens_email_lang');
	}
	
	public function setSelectedTokenController($controller = null) 
	{
	    $this->_selected_token_controller = $controller;
	}
	
	public function setTokenPrefix( $token_prefix = "$") 
	{
	    $this->_token_prefix = $token_prefix;
	}
	
	public function setTokenSufix( $token_sufix = "$" ) 
	{
	    $this->_token_sufix = $token_sufix;
	}
	
	
	/**
	 * from the result you need 'prefixed_array_viewer' for the view
	 * it is formated specialy for display via the tabulate() view helper
	 * echo $this->tabulate( $prefixed_array_viewer, array("class"=>"datatable token-list"));
	 * 
	 * @param array $filter_groups
	 * @return array
	 */
	public function getTokens4Viewer( $filter_groups = array())
	{
	    if ( ! is_null($filter_groups)) {
	        $this->_token_groups_filter = $filter_groups;
	    }
	    
	    return $this->_create_internal_assoc_arrays();
	}
	
	/**
	 * example of usage in VoluntaryworkersController::sendemail2vwsAction()
	 * Jul 26, 2017 @claudiu
	 * 
	 * @return multitype:Ambigous <multitype:string , multitype:NULL unknown > multitype:multitype:string   Ambigous <multitype:, string>
	 */
	private function _create_internal_assoc_arrays()
	{
		$token_prefix             = $this->_token_prefix;
		$token_sufix              = $this->_token_sufix;
		

		$result = array();
	
		$prefixed_array_viewer = array(); 
		
	
		$replace_pairs = array(); // this is a private array used in filterEmailTokens
		$replace_pairs_reversed = array(); // unused
		
		$replace_pairs_unprefixed = array();
		$replace_pairs_reversed_unprefixed = array(); // unused
		
	
		$working_array = array();
	
		//set available hardcoded default $_tokens
		$this->_setDefaultTokens();
		
		
		// extend the $_tokens array according to our needs
		switch( $this->_selected_token_controller ) {
			
		    /*
		     *  this are covered by the default
		     *  add your cutom case if you don't want to just create a fn create_MYACTION
		    case "ALL" :
		        $this->create_ALL();//get ALL TOKENS
	        break;
		          
			case "MemberEmail" :
			    $this->create_MemberEmail(); // used for member emails
			break;
			
			case "VoluntaryworkersEmail" :{
				$this->create_VoluntaryworkersEmail(); //for vw emails
			}
			break;
						
			case "invoice" :{
			    $this->create_Invoice(); //for invoice/invoice print docx
			}
			break;
			*/
		    
		    case "TEST" :{
		        $this->create_TEST(func_get_args()); //for invoice/invoice print docx
		    }
		    break;
		        
			default:{
				//use the default if nothing fancy
				//example of usage : studypool controller
				if (method_exists($this, "create_" . $this->_selected_token_controller)) {
					$this->{"create_" . $this->_selected_token_controller}();
				}
			}				
		}

		//now filter and order the tokens
		
		$token_groups = $this->_get_token_groups();
		
		$tokens_array = $this->_get_tokens();
		
		if( ! is_null($token_groups)) {

    		array_unshift($token_groups, 'default_tokens'); //force on top this group
    		
    		if (is_array($token_groups)) {
    		    $tokens_array = Pms_CommonData::sortAndFilterArrayByArray($tokens_array, array_unique($token_groups));
    		}
		}
		
		// if you are not here you must append like in TEST
		$tokens_email_lang        = $this->_default_tokens_array; 
		
		//append a table header for the viewer
		$prefixed_array_viewer[] = $tokens_email_lang['table_header'];
	
		foreach( $tokens_array as $tk => $tv)
		{

		    //if ($tk == 'default_tokens') continue;
		    
			$prefixed_array_viewer[] = array(
			    "token_group"=> $this->translator->translate($tk),
			    "attributes" => array("class" => "bold_text", "colspan" => 2)
			);	
			
			foreach($tv as $single_tok) {
			    
	
			    if (is_array($tokens_email_lang[$tk][$single_tok][0])) {
			        //multiple tokens for the same value
			        
			        $prefixed_array_viewer_lang = array();
			        
			        
			        foreach ($tokens_email_lang[$tk][$single_tok] as $multiple_tokens) {
			            
			            $token = $token_prefix . $multiple_tokens[0] . $token_sufix;
    	
        				$replace_pairs[$tk][$token] = $single_tok;
        	
        				$replace_pairs_reversed[$tk][$single_tok][] =  $token;
        				
        				$replace_pairs_unprefixed[$tk][$multiple_tokens[0]] = $single_tok;
        				
        				$replace_pairs_reversed_unprefixed[$tk][$single_tok][] =  $multiple_tokens[0];
        				
    				    $prefixed_array_viewer_lang[$multiple_tokens[1]][] = $token_prefix . $multiple_tokens[0] . $token_sufix ;
			        }
			        if ( ! empty($prefixed_array_viewer_lang)) {
			            
			            foreach ($prefixed_array_viewer_lang as $lang => $mtok) {
        			        $prefixed_array_viewer[] =  array(
        			            "key" => implode(', ', $mtok),
        			            "lang"=> $lang,
        			        );
			            }
			        }
			        
// 			        dd('multiple tokens for the same value', $tv, $single_tok);
			        
			    } else {
			    
    				$token = $token_prefix . $tokens_email_lang[$tk][$single_tok][0] . $token_sufix;
    	
    				$replace_pairs[$tk][$token] = $single_tok;
    	
    				$replace_pairs_reversed[$tk][$single_tok] =  $token;
    				
    				$replace_pairs_unprefixed[$tk][$tokens_email_lang[$tk][$single_tok][0]] = $single_tok;
    				
    				$replace_pairs_reversed_unprefixed[$tk][$single_tok] =  $tokens_email_lang[$tk][$single_tok][0];
    				
//     				$prefixed_array_viewer[$tk][] =  array(
				    $prefixed_array_viewer[] =  array(
				        "key" => $token,
				        "lang"=> $tokens_email_lang[$tk][$single_tok][1],
				    );
			    }
			}
		}
	
		$result = array(
				'prefixed_array_viewer' => $prefixed_array_viewer,
				'replace_pairs' => $replace_pairs,
				'replace_pairs_reversed' => $replace_pairs_reversed,
				'tokens_array' => $tokens_array,
				'replace_pairs_unprefixed' => $replace_pairs_unprefixed,
				'replace_pairs_reversed_unprefixed' => $replace_pairs_reversed_unprefixed,
		);
		
		return $result;
	
	}
	
	
	
	/**
	 * push a group into $this->_selected_token_list
	 * creates the group also in the $this-_tokens
	 *  
	 * @param string $group
	 */
	private function _add_token_group( $group = '')
	{
	    if (empty($group)) return;
	    
	    if ( ! is_array($this->_selected_token_list)) {
	        $this->_selected_token_list = array();
	    }
	    
	    if ( $group != 'default_tokens' && ! empty($this->_token_groups_filter) && ! in_array($group , $this->_token_groups_filter)) {
	        //this group is not allowed
	        return;
	    }
	    
	    if ( ! in_array($group, $this->_selected_token_list)) {
	        array_push($this->_selected_token_list, $group);
	        
	        if ( ! is_array($this->_tokens)) {
	            $this->_tokens = array();
	        }
	        
	        if ( ! is_array($this->_tokens[$group])) {
	            $this->_tokens[$group] = array();
	        }
	    }
	    
	}
	
	protected function _get_token_groups()
	{
	    return $this->_selected_token_list;
	}
	
	
	/**
	 * 
	 * @param string|array $token
	 * @param string $group , if null we will add to the default group
	 */
	private function _add_tokens( $tokens = '' , $group = NULL)
	{
	    if (is_null($group)) {
	        $group = 'default_tokens';
	    }
	    
	    if ( $group != 'default_tokens' && ! empty($this->_token_groups_filter) && ! in_array($group , $this->_token_groups_filter)) {
	        //this group is not allowed
	        return;
	    }
	    
	    if ( $group != 'default_tokens' && ! empty($this->_token_keys_filter) && ! isset($this->_token_keys_filter[$group])) {
	        //keys from this group are not allowed
	        return;
	    }
	    
	    if ( ! is_array($this->_tokens)) {
	        $this->_tokens = array();
	    }
	    
	    if ( ! is_array($this->_tokens[$group])) {
	        $this->_tokens[$group] = array();
	    }
	    
	    $this->_add_token_group($group);
	    	        
	    if (is_array($tokens)) {
	        foreach ($tokens as $token) {
	            if ( ! in_array($token, $this->_tokens[$group])) {
	                if (isset($this->_token_keys_filter[$group])) {
	                    if (in_array($token, $this->_token_keys_filter[$group])) {
	                        array_push($this->_tokens[$group], $token);
	                    } else {
	                        $this->_tokens_missing[$group][] = $token;
	                    }
	                } else {
	                    array_push($this->_tokens[$group], $token);
	                }
	            }
	        }
	    } elseif ( ! in_array($tokens, $this->_tokens[$group])) {
	        
	        if (isset($this->_token_keys_filter[$group])) {
	            if (in_array($tokens, $this->_token_keys_filter[$group])) {
	                 array_push($this->_tokens[$group], $tokens);
	            } else {
	                $this->_tokens_missing[$group][] = $tokens;
                }
	        } else {
	            array_push($this->_tokens[$group], $tokens);
	        }
	    }
	}
	
	protected function _get_tokens( $group = null )
	{
	    if ( ! is_null($group) && isset($this->_tokens[$group])) {	        

	        return $this->_tokens;

	    } else {
	        
    	    return $this->_tokens;
	        
	    }
	}
	
	
	//
	/**
	 * fill in the default tokens that you hardcoded in $this->_setDefaultTokens()
	 * ... this entire fn for defaults needs to be changed... so i have this values ready available....
	 * 
	 * ! by refence !
	 * @param unknown $values
	 */
	private function _set_default_tokens_values( &$values )
	{
		//fill defaults if they are not set
		if( ! isset($values['default_current_date'])) {
			$values['default_current_date'] = date('d.m.Y', time());
		}
	}
	 
	/**
	 * TODO deprecate after ->createTokens4Email
	 * 
	 * this was created for direct text replace...
	 * 
	 * example of usage in Application_Form_VwEmailsLog::save2email_log()
	 * Jul 26, 2017 @claudiu
	 *
	 * @param string $message
	 * @param unknown $values
	 * @return string
	 */
	public function filterTokens($message = "", $values = array('client'=>array(), 'user'=>array(), 'patient'=>array(), 'voluntaryworker'=>array(), 'member'=>array()))
	{
		if( empty($message) || empty($values) || ! is_array($values)) {
			return $message;
		}
	
		foreach ($values as $group => $columns) {
		    $this->_token_keys_filter[$group] = is_array($columns) ? array_keys($columns) : array();
		}
		
		//append defaults if you have none
		$this->_set_default_tokens_values($values['default_tokens']); 
		
		$tokens = $this->_create_internal_assoc_arrays();
		$replace_pairs = $tokens['replace_pairs'];
	
		$replace_pairs_processed = array();
	
		foreach( $replace_pairs as $k_arr => $v_arr) {
			foreach( $v_arr as $k=>$v) {
			    
			    if (empty($k)) {
			        continue; //cannot replace empty tokens
			    }
			    
				$replace_pairs_processed [$k] = isset($values[$k_arr][$v]) ? $values[$k_arr][$v] : "";
			}
		}
	
		return strtr( $message , $replace_pairs_processed );
	}
	
	
	/**
	 * TODO wrapper for filterTokens
	 */
	/*
	public function createTokens4Email()
	{
		//return call_user_func_array(array($this, 'filterTokens'), func_get_args());
	    return $this->filterTokens(func_get_args());
	}
	*/
	
	/**
	 * we will be only replaceing tokens for which isset(column)
	 * 
	 * @param unknown $values
	 * @return unknown|multitype:string
	 */
	public function createTokens4Docx($values = array('client'=>array(), 'user'=>array(), 'patient'=>array(), 'voluntaryworker'=>array(), 'member'=>array(), 'contact_person'=>array()) , $html_tokens = array())
	{
		if( empty($values) || ! is_array($values)) {
			return $values;
		}
		
		foreach ($values as $group => $columns) {
		    $this->_token_keys_filter[$group] = is_array($columns) ? array_keys($columns) : array();
		}
		
		//append defaults if you have none
		$this->_set_default_tokens_values($values['default_tokens']); 
		
		
		$tokens = $this->_create_internal_assoc_arrays($values);
		
		
		$replace_pairs = $tokens['replace_pairs_unprefixed'];
	
		$replace_pairs_processed = array('html'=> array(), 'text' => array());
		
		$html_tokens = is_null($html_tokens) ? $this->_default_html_tokens : $html_tokens;
	
		foreach( $replace_pairs as $k_arr => $v_arr) {
			foreach( $v_arr as $k=>$v) {
			    
			    
			    if (empty($k)) {
			        continue; //cannot replace empty tokens
			    }
			    
			    if (isset($html_tokens[$k])) { 
    				$replace_pairs_processed ['html'] [ $k ] = isset($values[$k_arr][$v]) ? $values[$k_arr][$v] : "";    
			    } else { 
    				$replace_pairs_processed ['text'] [ $k ] = isset($values[$k_arr][$v]) ? $values[$k_arr][$v] : "";
			    }
			    
			    $replace_pairs_processed ['text'] [ $k . "_text" ]  = isset($values[$k_arr][$v]) ? $values[$k_arr][$v] : "";
			}
		}
		
		return $replace_pairs_processed;
	}
	
	
	
	
	
	
	
	/**
	 * default_ token will be available in any controller
	 * 
	 * also you need to add a default value for this token in $this->_set_default_tokens_values()
	 * 
	 * Jul 28, 2017 @claudiu 
	 * 
	 */
	private function _setDefaultTokens()
	{
	    $group = 'default_tokens';
		
		$tokens_email_lang = $this->translator->translate('tokens_email_lang');
		
		$defaultTokens = $tokens_email_lang[$group];
		
		foreach ($defaultTokens as $key => $val) {
		    
		    if (substr($key, 0, 8) == "default_") {
    		    $this->_add_tokens($key, $group);
		    }
		}		
	}
	
    private function _setClientTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'client';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setUserTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'user';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setPatientTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'patient';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
	
    private function _setContactPersonTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'contact_person';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    
    /**
     * ISPC-2411
     * Ancuta 13.12.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
     */
    private function _setRecipientTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'recipient';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setMemberTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'member';
         
        $this->_add_token_group($group);
        
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
        
        $invoiceTokens = $tokens_email_lang[$group];
        
        foreach ($invoiceTokens as $key => $val) {
        
            $this->_add_tokens($key, $group);
        }
        
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group); 
    }
    
    private function _setVoluntaryworkerTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'voluntaryworker';
     
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
        $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setInvoiceTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'invoice';
         
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
	    $invoiceTokens = $tokens_email_lang[$group];
    
	    foreach ($invoiceTokens as $key => $val) {
    
    	    $this->_add_tokens($key, $group);
	    }
    	     
	    //add my extra tokens to each token-list we have chosen
	    //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setSAPVTokens()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        $group = 'sapv';
             
        $this->_add_token_group($group);
    
        $tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
	    $invoiceTokens = $tokens_email_lang[$group];
    
        foreach ($invoiceTokens as $key => $val) {
    
            $this->_add_tokens($key, $group);
        }
    
        //add my extra tokens to each token-list we have chosen
        //$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setAnlage6Tokens()
    {
    	// filter what tokens-list do we need
    	// if you don't chose all lists will be used
    	$group = 'anlage6';
    	 
    	$this->_add_token_group($group);
    
    	$tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
    	$invoiceTokens = $tokens_email_lang[$group];
    
    	foreach ($invoiceTokens as $key => $val) {
    
    		$this->_add_tokens($key, $group);
    	}
    
    	//add my extra tokens to each token-list we have chosen
    	//$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setHealthinsuranceTokens()
    {
    	// filter what tokens-list do we need
    	// if you don't chose all lists will be used
    	$group = 'healthinsurance';
    
    	$this->_add_token_group($group);
    
    	$tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
    	$invoiceTokens = $tokens_email_lang[$group];
    
    	foreach ($invoiceTokens as $key => $val) {
    
    		$this->_add_tokens($key, $group);
    	}
    
    	//add my extra tokens to each token-list we have chosen
    	//$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setHospicereportTokens()
    { // Maria:: Migration ISPC to CISPC 08.08.2020
    	// filter what tokens-list do we need
    	// if you don't chose all lists will be used
    	$group = 'hospicereport';
    
    	$this->_add_token_group($group);
    
    	$tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
    	$invoiceTokens = $tokens_email_lang[$group];
    
    	foreach ($invoiceTokens as $key => $val) {
    
    		$this->_add_tokens($key, $group);
    	}
    
    	//add my extra tokens to each token-list we have chosen
    	//$this->_add_tokens('my_special_token', $group);
    }
    
    private function _setFamilydoctorTokens()
    {
    	// filter what tokens-list do we need
    	// if you don't chose all lists will be used
    	$group = 'familydoctor';
    
    	$this->_add_token_group($group);
    
    	$tokens_email_lang = $this->translator->translate('tokens_email_lang');
    
    	$invoiceTokens = $tokens_email_lang[$group];
    
    	foreach ($invoiceTokens as $key => $val) {
    
    		$this->_add_tokens($key, $group);
    	}
    
    	//add my extra tokens to each token-list we have chosen
    	//$this->_add_tokens('my_special_token', $group);
    }
    
	private function _setALLTokens()
	{
		// filter what tokens-list do we need
		// if you don't chose all lists will be used	
		$tokens_email_lang = $this->translator->translate('tokens_email_lang');
		
		foreach ($tokens_email_lang as $group => $group_tokens) {

		    $this->_add_token_group($group);
		    
    		foreach ($group_tokens as $key => $val) {
    		     
    		    $this->_add_tokens($key, $group);
    		}
		}		
	}
	
	
    
	/**
	 * Aug 9, 2017 @claudiu 
	 * 
	 * @param array $tokens
	 */
	private function create_Studypool()
	{
	    // filter what tokens-list do we need
	    // if you don't chose all lists will be used
	    // the order you set here will be kept in display
		$this->_setContactPersonTokens();
		$this->_setPatientTokens();
		$this->_setClientTokens();
		
		//add my extra tokens to each token-list we have chosen
		$this->_add_tokens('survey_url', 'default_tokens');
	}
	
	
	private function create_VoluntaryworkersEmail()
	{
	    // filter what tokens-list do we need
	    // if you don't chose all lists will be used
	    // the order you set here will be kept in display
	    $this->_setVoluntaryworkerTokens();
	    $this->_setUserTokens();
	    $this->_setClientTokens();
	    
	    //add my extra tokens to each token-list we have chosen
	    //$this->_add_tokens('tokenfiled', 'voluntaryworker');
	    //$this->_add_tokens('tokenfiled default', 'default_tokens');
	}
	
	private function create_MemberEmail()
    {
        // filter what tokens-list do we need
        // if you don't chose all lists will be used
        // the order you set here will be kept in display
		$this->_setMemberTokens();
		$this->_setUserTokens();
		$this->_setClientTokens();
		
		//add my extra tokens to each token-list we have chosen
		//$this->_add_tokens('tokenfiled', 'member');
		//$this->_add_tokens('tokenfiled default', 'default_tokens');
	}
	
	private function create_Invoice()
	{
	    // filter what tokens-list do we need
        // if you don't chose all lists will be used
        // the order you set here will be kept in display
	    $this->_setInvoiceTokens();
	    $this->_setPatientTokens();
	    $this->_setUserTokens();
	    $this->_setClientTokens();
	    $this->_setSAPVTokens();
	}
	
	private function create_Reportsnew()
	{
		// filter what tokens-list do we need
		// if you don't chose all lists will be used
		// the order you set here will be kept in display		
		$this->_setPatientTokens();
		$this->_setClientTokens();
		$this->_setAnlage6Tokens();
		$this->_setHealthinsuranceTokens();
		$this->_setHospicereportTokens(); // Maria:: Migration ISPC to CISPC 08.08.2020
		$this->_setFamilydoctorTokens();
	}
	
	/**
	 * this is to see ALL
	 */
	private function create_ALL()
	{
	    $this->_setALLTokens();
	}
	
	private function create_TEST($vars = null)
	{
	    $this->_setALLTokens();

	    if ($vars[0] && is_array($vars[0])) {
	        foreach ($vars[0] as $group => $vals) {
	            
	            foreach ($vals as $key => $val) {
	                $this->_add_tokens($key, $group);
	                
	                if ( ! isset($this->_default_tokens_array[$group][$key])) {
	                    $this->_default_tokens_array[$group][$key] = [$key, " TEST MODE {$key} "] ; 
	                }
	            }
	            
	        }
	    }	
// 	    dd($this->_default_tokens_array);
// 	    dd($this->_tokens);
	}
	/**
	 * @author Carmen
	 * Edited by Ancuta 11.09.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
	 * ISPC-2411 
	 */
	private function create_Survey()
	{
		// filter what tokens-list do we need
		// if you don't chose all lists will be used
		// the order you set here will be kept in display
		$this->_setRecipientTokens();//13.12.2019
		$this->_setContactPersonTokens();
		$this->_setPatientTokens();
		$this->_setClientTokens();
		
		//add my extra tokens to each token-list we have chosen
		$this->_add_tokens('survey_link', 'default_tokens');
	}
	
}