<?php
/**
 * 
 * @author claudiu
 * 
 * Extend the Zend_View_Helper_HeadScript :: append & prepend  
 * add filemtime and our version number at the end of the url
 * xxx/jquery-1.8.0.js becomes xxx/jquery-1.8.0.js?t=15035839461.1 (1.1 is the version)
 * xxx/jquery-1.8.0.js?var1=a1 becomes xxx/jquery-1.8.0.js?var1=a1&t=15035839461.1 (1.1 is the version)
 * 
 * microtime(true) is added for development env
 *
 */
class Version_Control_HeadScript extends Zend_View_Helper_HeadScript
{
	
	protected $_filemtime_var_name = "t";//js_filemtime";
	
	/**
	 * will be populated with application.ini -> version
	 * @var string
	 */
	protected $_version = null;
	
	
	/**
	 * patern to search for .js files
	 * @var regex string pattern
	 */
	protected $_preg_match = '/^(.*)(\.js)([\?]?)(.*)$/i';

	
	/**
	 * realpath of the js files
	 * ! change in the __construct to match your configs
	 * @var string
	 */
	protected $_file_path = null;
	
	/**
	 * + 07.03.2018
	 * @var string
	 */
	protected $_ipad_path = '/_ipad';
	
	public function __construct() 
	{
		$this->_file_path = PUBLIC_PATH;
		
		$appInfo = Zend_Registry::isRegistered('appInfo') ? Zend_Registry::get('appInfo') : array('version' => time());
		
		$this->_version = isset($appInfo['version']) ? $appInfo['version'] : "unknownVersion";
		
		if (APPLICATION_ENV == 'development') 
		    $this->_version .=  "_development_" . microtime(true);
		
		parent::__construct();
	}
	
	
    public function append($value)
    {
    	if ( $this->_isValid($value) 
    			&& ! empty($value->attributes['src'])
    			&& preg_match($this->_preg_match, $value->attributes['src'], $matches)) 
    	{

    		$glue = ! empty($matches[3]) ? "&" :"?";
    		
    		if ( defined("RES_FILE_PATH") && strlen(RES_FILE_PATH)) {
    		    
    		    if (substr($matches[1], 0, strlen(RES_FILE_PATH)) == RES_FILE_PATH) {
    		        $matches[1] = substr($matches[1], strlen(RES_FILE_PATH));
    		        
    		        if (RES_FILE_PATH == IPAD_STYLE_PATH) {
    		            $matches[1] = $this->_ipad_path . $matches[1] ;
    		        }
    		    }
    		}
    		
    		$file_path = $this->_file_path . $matches[1]. $matches[2];
    		  			
     		$value->attributes['src'] .= $glue . $this->_filemtime_var_name . "=" . (is_file($file_path) ? (int)filemtime($file_path) : 'rev') . $this->_version;    
     		
//    		$value->attributes['src'] .= $glue . $this->_filemtime_var_name . "=" . time().'&'.$t;    			
    	
    	}
    	
    	return parent::append($value);
    }
    
    
    public function prepend($value)
    {
    	if ( $this->_isValid($value)
    			&& ! empty($value->attributes['src'])
    			&& preg_match($this->_preg_match, $value->attributes['src'], $matches))
    	{
    
    		$glue = ! empty($matches[3]) ? "&" :"?";
    		 
    		if ( defined("RES_FILE_PATH") && strlen(RES_FILE_PATH)) {
    		
    		    if (substr($matches[1], 0, strlen(RES_FILE_PATH)) == RES_FILE_PATH) {
    		        $matches[1] = substr($matches[1], strlen(RES_FILE_PATH));
    		        
    		        if (RES_FILE_PATH == IPAD_STYLE_PATH) {
    		            $matches[1] = $this->_ipad_path . $matches[1] ;
    		        }
    		    }
    		}
    		
    		$file_path = $this->_file_path . $matches[1]. $matches[2];
    		
     		$value->attributes['src'] .= $glue . $this->_filemtime_var_name . "=" . (is_file($file_path) ? (int)filemtime($file_path) : 'rev') . $this->_version;
     		
    		//$value->attributes['src'] .= $glue . $this->_filemtime_var_name . "=" . time();
    		 
    	}
    	 
    	return parent::prepend($value);
    }
    
    
    public function  toString($indent = null) 
    {
        if (APPLICATION_ENV != 'production') 
        {
            /*
             * inform developer that the same js is included multipe times (if js uses param ...)
             */
            $jss = $this->getContainer()->getArrayCopy();
            
            if ( ! empty($jss)) 
            {
                $jsSRCs = (array)(array_filter($jss, function($js){return ! empty($js->attributes) && ! empty($js->attributes['src']);}));
                if ( ! empty($jss)) 
                {
                    $jsSRCs = array_column(array_column($jsSRCs, 'attributes'), 'src');

                    if ( ! empty($jss)) {
                        $jsSRCs = array_map(function($js){return preg_replace('/^(.*)\?(.*)$/', '\1', $js);}, $jsSRCs);
                        $jsDuplicates =  array_unique( array_diff_assoc( $jsSRCs, array_unique( $jsSRCs ) ) );
                        if ( ! empty($jsDuplicates)) {
                            if ($logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log')) {
                                $logger->log('Duplicated js files (fix by removing this duplicates, one js should be included only once) :' . PHP_EOL . print_r($jsDuplicates, true), 3);
                            }
                        }
                    }
                }
            }
        }
        
        return parent::toString($indent);
        
    }
    
}