<?php
/**
 * 
 * @author claudiu
 * 
 * Extend the Zend_View_Helper_HeadLink :: append & prepend 
 * add our version number at the end of the file
 * 
 *
 */
class Version_Control_HeadLink extends Zend_View_Helper_HeadLink
{

    /**
     * keys for the 2 ariables
     * @var string
     */
	protected $_filemtime_var_name = "t";//css_filemtime";
	
	
	/**
	 * will be populated with application.ini -> version
	 * @var string
	 */
	protected $_version = null;
	
	
	/**
	 * patern to search for .css files
	 * @var regex string pattern
	 */
	protected $_preg_match = '/^(.*)(\.css)([\?]?)(.*)$/i';
	
	
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

		parent::__construct();
	}
	
	
    public function append($value)
    {
        
    	if ( $this->_isValid($value) 
    			&& ! empty($value->href)
    			&& preg_match($this->_preg_match, $value->href, $matches)) 
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
    			    			
     		$value->href .= $glue . $this->_filemtime_var_name . "=" . (is_file($file_path) ? (int)filemtime($file_path) : 'rev') . $this->_version;
     		     		
//    		$value->href .= $glue . $this->_filemtime_var_name . "=" . time();    			
    	
    	}
    	
    	return parent::append($value);
    }
    
    
    public function prepend($value)
    {
    	if ( $this->_isValid($value)
    			&& ! empty($value->href)
    			&& preg_match($this->_preg_match, $value->href, $matches))
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
    
     		$value->href .= $glue . $this->_filemtime_var_name . "=" . (is_file($file_path) ? (int)filemtime($file_path) : 'rev') . $this->_version;
     		     		
//    		$value->href .= $glue . $this->_filemtime_var_name . "=" . time();
    		 
    	}
    	 
    	return parent::prepend($value);
    }
    
}