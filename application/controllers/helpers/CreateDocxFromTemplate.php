<?php
/**
 * todo: create a standard for debug(), log_error(), log_info()
 * 
 * depends on Phpdocx
 * depends on Application_Controller_Helper_Log
 * depends on Pms_Tokens
 * depends on Pms_CommonData :: ftp_put_queue
 * depends on Pms_CommonData :: filter_filename
 * depends on Pms_CommonData :: array_flatten - for test
 * 
 * @author claudiu 
 * May 23, 2018
 *
 */
class Application_Controller_Helper_CreateDocxFromTemplate extends Zend_Controller_Action_Helper_Abstract
// implements Debug-Logger-Model
{
    
    CONST _default_font_family = "DejaVu Sans";
    
    // $token = mb_convert_encoding($val, 'HTML-ENTITIES', 'UTF-8');
    private $_environment = null;

    private $_logger = null;
    
    private $_token_controller = null;
    
    private $_client_id = null;//ISPC-2609 Ancuta 26.09.2020
    
    /**
     * TODO:
     * $_tokens_Service implements Tokens_Interface;
     * 
     * TODO: interface Tokens_Interface 
     * {
     * public function getTokens4Viewer($controller =  null);
     * public function createTokens4Docx(array $values = array() , $html_tokens = array());
     * }
     */ 
    private $_tokens_Service = null;

    /**
     * this must be UNIQUE ! we don't do any hash_file ... so on dowload you may get 'lucky'
     * if you get 'lucy' change $this->_browser_output_file = readfile
     * 
     * @var unknown
     */
    private $_output_file = null;

    private $_output_file_type = null;
    
    private $_browser_output_file = null;
    
    private $_browser_output_filename = null;
    
    private $_all_docx_files = array();
    
    private $_output_file_merged = null;
    
    private $_template_variables = array();
    
    private $_html_tokens = array(
        
        'address'       => 'block',
    	'SAPV_Rechnungsempfaenger'  => 'block',// Added by Carmen 20.02.2019 ISPC-1236
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
    	'invoice_items_html_short'  => 'inline', // Added by Carmen 20.02.2019 ISPC-1236
        'control_sheet'             => 'inline',
        'internal_invoice_items_html'=> 'inline',
    	'internal_invoice_items_html_short'  => 'inline', // Added by Carmen 08.03.2019 ISPC-1236
    	'html_anlage2a' => 'inline',  // Added by Carmen 13.06.2019 ISPC-2399
    	'html_anlage2b' => 'inline',  // Added by Carmen 13.06.2019 ISPC-2399
    	'html_new' => 'inline',  // Added by Carmen 30.07.2019 ISPC-2394
    	'html_old' => 'inline',  // Added by Carmen 30.07.2019 ISPC-2394
    	'report_period' => 'inline', // Added by Carmen 30.07.2019 ISPC-2394
    	
    );
    

    
    /**
     * TODO: overload Zend_Controller_Action_HelperBroker::_loadHelper (line 372) with params for helper construct
     * @param string $token_controller, if null will use controllerName
     */
    public function __construct($token_controller = null, $tokens_Service = null)
    {
        $this->logininfo = new Zend_Session_Namespace('Login_Info');
        
        try {
            $this->_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
        } catch (Zend_Controller_Action_Exception $e) {
            //die($e->getMessage());
        }

        
        //ISPC-2609 Ancuta 26.09.2020
        $params = array();
        $params = $this->getRequest()->getParams();
        if(isset($params['clientid']) && !empty($params['clientid'])){
            $this->_client_id = $params['clientid'];
        } else {
            $this->_client_id = $this->logininfo->clientid;
        }
        
        $this->_token_controller = ! is_null($params['controller']) ? $params['controller'] : $this->_token_controller;
        
        
        if(isset($params['controller']) && !empty($params['controller'])){
            $this->_token_controller = $params['controller'];
        } else{
            $this->_token_controller = ! is_null($token_controller) ? $token_controller : $this->getRequest()->getControllerName();
        }
        // --
        
//         dd( $params,$this->_token_controller ,$this->_client_id );
        if (is_null($tokens_Service)) {
            
            $this->_tokens_Service = new Pms_Tokens();

            $this->_tokens_Service->setSelectedTokenController($this->_token_controller);
        }
        
        
        $this->_set_defaults();
    }
    
    public function __get($name)
    {
        if (method_exists($this, 'get' . $name)) {
            return $this->{'get' . $name}();
            
        } elseif (property_exists($this, "_" . $name)) {
            
            return $this->{"_" . $name};
            
        } else {
            //err
        }
    }
    
    private function _set_defaults()
    {        
        $name = Pms_CommonData::filter_filename('default_docxname_' . microtime(true));
		//ISPC-2609 Ancuta 26.09.2020
		//$this->_output_file = PDFDOCX_PATH . '/' . $this->logininfo->clientid . '/' . $name ;
        $this->_output_file = PDFDOCX_PATH . '/' . $this->_client_id . '/' . $name ;
        
        $name = Pms_CommonData::filter_filename('default_merged_docxname_' . microtime(true));
		//ISPC-2609 Ancuta 26.09.2020
        //$this->_output_file_merged = PDFDOCX_PATH . '/' . $this->logininfo->clientid . '/' . $name ;
        $this->_output_file_merged = PDFDOCX_PATH . '/' . $this->_client_id . '/' . $name ;
        
    }
    
    public function setHtmlTokens(array $tokens =  array('token' => 'block|inline'))
    {
        $this->_html_tokens = $tokens;
    }
    
    public function getHtmlTokens()
    {
        return $this->_html_tokens;
    
    }
    
    
    public function setTokenController($token_controller = '')
    {
        $this->_token_controller = $token_controller;
        
        if ($this->_tokens_Service && method_exists($this->_tokens_Service, 'setSelectedTokenController')) {
            $this->_tokens_Service->setSelectedTokenController($this->_token_controller);
        }
        
    }
    
    
    
    public function setBrowserFilename($name = '') 
    {
        $name = Pms_CommonData::filter_filename($name);
        
        $this->_browser_output_filename = $name;
    }
    
    /**
     * !! without extension !!
     * 
     * @param string $filepath
     */
    public function setOutputFile($filepath = '')
    {
        //validate path ?
        $this->_output_file = $filepath;
    }
    
    
    public function getTokens4Viewer()
    {
        return $this->_tokens_Service->getTokens4Viewer();
    }
    
    public function create_docx($template_file = 'full_path', $variables = array())
    {
        if (is_null($this->_output_file_type)) {
            $this->_output_file_type = 'docx';
        }
        
        if (empty($template_file) || ! file_exists($template_file) || ! is_file($template_file)) {
            // error, this template does not exist;
            $this->log_error('ERROR! this template does not exist :' . $template_file);
            return;
        }
        
        $docx_available_tokens = $this->_tokens_Service->createTokens4Docx($variables , $this->_html_tokens);
        
//         dd($docx_available_tokens, $variables);
        
        $textVariables = $docx_available_tokens['text'];
        
        $htmlVariables = $docx_available_tokens['html'];
       
//         dd($docx_available_tokens);
       
        $docx = new CreateDocxFromTemplate($template_file);
        
        $this->_template_variables = $docx->getTemplateVariables();
        
//         dd($this->_template_variables);
       
        $this->process_template($docx, $textVariables, $htmlVariables);
     
        $doc_res = $this->save_file($docx, $this->_output_file, $this->_output_file_type);
        
        if ( $doc_res === false ) {
            
            throw new Zend_Exception('Error generating document, please try again.');            
        }
    }
    
    public function create_pdf($template_file = 'full_path', $variables = array())
    {
        $this->_output_file_type = 'pdf';
        
        $this->create_docx($template_file, $variables);
        
    }
    
    
    /**
     * ! download_file is a terminus... file will be deleted at the end !
     * 
     * call file_save_on_ftp() before this if you need-it
     */
    public function download_file()
    {
        if (is_null($this->_browser_output_filename)) {
            $this->_browser_output_filename =  $this->_token_controller . '_' . date('d_m_Y');
        }
        
        $this->_browser_output_filename .=  '.' . $this->_output_file_type;
        
        if ( ! is_null($this->_browser_output_file) && file_exists($this->_browser_output_file)) {

            ob_end_clean();
            ob_start();
            
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            
            switch($this->_output_file_type)
            {
                case 'pdf':
                    header('Content-type: application/pdf');
                    break;
                    
                case 'doc':
                case 'docx':
                    header('Content-type: application/vnd.ms-word');
                    break;
                    
                case 'rtf':
                    header("Content-type: application/rtf");
                    break;
                    
                case 'odt':
                    header('Content-type: application/vnd.oasis.opendocument.text');
                    break;
                    
                default:
                    exit;
                    break;
            }
            
            header("Content-Disposition: attachment; Filename=\"{$this->_browser_output_filename}\"");
            
            
            //readfile($this->_browser_output_file);
            if ($handle = @fopen($this->_browser_output_file, "rb")) {
                
                while ( ! feof($handle)) {
                    
                    print(@fread($handle, 1024*8));
                    ob_flush();
                    flush();
                }
                
                fclose($handle);
            }
            
            @unlink($this->_browser_output_file);
            
            exit;
               
        }
    }
    
    
    /**
     * $legacy_path = uploads
     * $legacy_path = clientuploads
     * $legacy_path = null ... this is a foster file, you cannot link this to a ispc record
     * 
     * @param string $legacy_path
     * @return boolean
     */
    public function file_save_on_ftp( $legacy_path = null )
    {
        $foster_file = false;
        
        if (is_null($legacy_path)) {
            $foster_file = true;
            $legacy_path = $this->_token_controller;
        }
				
        $upload = Pms_CommonData :: ftp_put_queue($this->_browser_output_file,  $legacy_path, $is_zipped = NULL, $foster_file );
        
        return $upload;
        
    }
    

    
    /**
     * this is just a copy/rewrite from Andrei@orw DocUtil::process_template
     *
     * @param CreateDocxFromTemplate $docx            
     * @param unknown $textVariables            
     * @param unknown $htmlVariables            
     * @return boolean|CreateDocxFromTemplate
     */
    private function process_template(CreateDocxFromTemplate $docx, $textVariables = array(), $htmlVariables = array())
    {
        if ( ! ($docx instanceof CreateDocxFromTemplate)) {
            
            $this->debug('ERROR: ! docx instanceof CreateDocxFromTemplate'); //?? how do you reach this?
            
            return false;
        }
        
        try {
            // $docx = new CreateDocxFromTemplate( $file );
            // $docx->setTemplateSymbol('@');
            
           
            
            
            if (is_array($textVariables) && ! empty($textVariables)) {
                foreach ($textVariables as $var => $value) {
                    // pr( $var );
                    // pr( $value );
                    
                    if (is_array($value)) {
                        $docx->replaceTableVariable($value, array(
                            'parseLineBreaks' => true
                        ));
                    } else {
                        $docx->replaceVariableByText(array(
                            $var => $value
                        ), array(
                            'parseLineBreaks' => true
                        ));
                        
                        $docx->replaceVariableByText(array(
                            $var => $value
                        ), array(
                            'parseLineBreaks' => true,
                            'target' => 'header'
                        ));
                        
                        $docx->replaceVariableByText(array(
                            $var => $value
                        ), array(
                            'parseLineBreaks' => true,
                            'target' => 'footer'
                        ));
                    }
                }
            }
            
            
            // set html options
            $html_options = array(
                'isFile' => false,
                'parseDivsAsPs' => false,
                'downloadImages' => false,
                "strictWordStyles" => false
            );
			// Maria:: Migration ISPC to CISPC 08.08.2020
            //TODO-2713 Ancuta 05.12.2019 Replace html tokens in header and footer 
            $html_options_header = array(
                'isFile' => false,
                'parseDivsAsPs' => false,
                'downloadImages' => false,
                "strictWordStyles" => false,
                'target' => 'header'
            );
            $html_options_footer = array(
                'isFile' => false,
                'parseDivsAsPs' => false,
                'downloadImages' => false,
                "strictWordStyles" => false,
                'target' => 'footer'
            );
            // -- 
            
            
            //dd($htmlVariables);
            foreach ($htmlVariables as $token => $value) {
                // pr( $token );
                // pr( $value );
            
                // CakeLog::debug( 'token='.print_r($token, true) );
                // CakeLog::debug( 'token='.print_r($value, true) );
                $val = $this->process_html_token($docx, $token, $value);
            
                // pr( $val );
            
                // $val = $value;//'<div>'.$value.'</div>';
            
                // CakeLog::debug( 'val='.print_r($val, true) );
            
                if ($val !== false) {
                    // force change utf-8 in html entities, because on one server it did not return corectly utf-8
                    $val = mb_convert_encoding($val, 'HTML-ENTITIES', 'UTF-8');
                    $this->debug($val);
                    //                      $docx->replaceVariableByHTML( $token, 'block', $val, $html_options);
                    $this->_replaceVariableByHTML($docx, $token, 'block', $val, $html_options);
                    
					// Maria:: Migration ISPC to CISPC 08.08.2020
                    //TODO-2713 Ancuta 05.12.2019 Replace html tokens in footer
                    $this->_replaceVariableByHTML($docx, $token, 'block', $val, $html_options_header);
                                       
                    $this->_replaceVariableByHTML($docx, $token, 'block', $val, $html_options_footer);
                    // -- 
            
            
                }
            
            }
            
        } catch (Exception $e) {
            
            
            $this->log_error( __METHOD__ . __LINE__ . ' error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
            
            return false;
        }
        
        return $docx;
    }
    
    
    /**
     * ! mergeing removes the original file 
     */
    public function merge_as_docx() 
    {
        $first_file = null;
        $rest_of_files = array();
        
        foreach ($this->_all_docx_files as $filename) {
        	
            if (file_exists($filename)) {
            	
                if (is_null($first_file)) {
                	
                    $first_file = $filename;
                    
                } else {
                    
                    $rest_of_files[] = $filename;
                }
            } 
        }
        
        if ( ! empty($first_file)) {

            $merge = new MultiMerge();
            
            $merge_options = array(
                'mergeType' => '0',
                'numbering' => 'continue',
            );
            
            
            $merge->mergeDocx($first_file, $rest_of_files, $this->_output_file_merged, $merge_options);
            
            $this->_browser_output_file = $this->_output_file_merged.'.docx';
            
            unlink( $first_file );
            
            array_map("unlink" , $rest_of_files);
            
            return true;
        }
        
        return;
    }
   
    public function merge_as_pdf()
    {
        $merge = $this->merge_as_docx();
        
        if ($merge === true) {
            
            //CreateDocx only accepts docm and docx..
            rename($this->_output_file_merged, $this->_output_file_merged . ".docx");
            $this->_output_file_merged .= ".docx";
            
            $this->_output_file_type = 'pdf';
            
            $docx = new CreateDocxFromTemplate($this->_output_file_merged);
            
            $doc_res = $this->save_file($docx, $this->_output_file, $this->_output_file_type);
            
            if ($doc_res) {
                unlink($this->_output_file_merged);
            }
            
            return true;
        }
        
        return;
    }
    
    /**
     *
     * this is an adapt from Andrei@orw DocUtil::get_file
     *
     * @param CreateDocxFromTemplate $docx
     * @param unknown $output_file
     * @param string $type
     * @return boolean
     */
    private function save_file( CreateDocxFromTemplate $docx, $output_file, $type = 'pdf' )
    {
        if ( file_exists( $output_file . '.docx') ) {
            unlink($output_file . '.docx');
        }
        	
        if ( file_exists( $output_file . '.pdf') ) {
            unlink($output_file . '.pdf');
        }
        	
        try {
            
            $docx->createDocx( $output_file );

            if ( file_exists( $output_file . '.docx') ) {
            
                array_push($this->_all_docx_files, $output_file . '.docx');
                
            } else {
                
                $this->log_error(__METHOD__ . __LINE__. ' createDocx failed');
                
                return false;
            }
            
            
            
            if ( $type === 'docx' ) {
                
                if ( file_exists( $output_file . '.docx') ) {
                	
                    $this->_browser_output_file = $output_file . '.docx';
                    
                    return true;
                }
                	
                return false;
                
            } else {
                
                //$docx->enableCompatibilityMode();   // Alex+Ancuta- commented on 18.11.2019- New phpdocx 9.5 added
                
                
                try {
                    
                    $docx->transformDocument($output_file . '.docx', $output_file . "." . $type);
                    
                    unlink($output_file . '.docx');
                    
                    $this->_browser_output_file = $output_file . "." . $type;
                    
                    return true;
                    
                } catch( Exception $e ) {
                    
                    $this->log_error(__METHOD__ . __LINE__ . 'File not generated -1- .');
                    
                    return false;
                }
                	
            }
            
        } catch( Exception $e ) {
            
            $this->log_error(__METHOD__ . __LINE__ . 'File not generated -2- .' . $e->getMessage());
            
            return false;
        }
    }

    
    /**
     * this is just a copy/rewrite from Andrei@orw DocUtil::_replaceVariableByHTML
     *
     * @param unknown $docx            
     * @param unknown $var            
     * @param string $type            
     * @param string $html            
     * @param unknown $options            
     */
    private function _replaceVariableByHTML(CreateDocxFromTemplate $docx, $var, $type = 'block', $html = '<html><body></body></html>', $options = array())
    {
        // $old_debug = Configure::read('debug');
        // Configure::write('debug', 0);
        if (isset($options['target'])) {
            $target = $options['target'];
        } else {
            $target = 'document';
        }
        if (isset($options['firstMatch'])) {
            $firstMatch = $options['firstMatch'];
        } else {
            $firstMatch = false;
        }
        
        $html = $this->fix_html_table_border($html);
        
        $options['type'] = $type;
        $htmlFragment = new WordFragment($docx, $target);
        $htmlFragment->embedHTML($html, $options);
        
        $temp = $htmlFragment->__toString();
        
        // $temp = str_replace('<w:gridCol w:w="1"/>', '<w:gridCol/>', $temp);
        // $temp = str_replace('<w:gridCol w:w="1"/>', '<w:gridCol/>', $temp);
        
        $temp = str_replace('<w:tblCellSpacing w:w="30" w:type="dxa"', '<w:tblCellSpacing w:w="0" w:type="dxa"', $temp);
        
        // CakeLog::error( 'wordfragment='.print_r($temp, true) );
        
        unset($htmlFragment);
        $htmlFragment = new WordFragment($docx, $target);
        $htmlFragment->addRawWordML($temp);
        
        $docx->replaceVariableByWordFragment(array(
            $var => $htmlFragment
        ), $options);
        
        // Configure::write('debug', $old_debug);
    }

    
    /**
     * this is just a copy/rewrite from Andrei@orw DocUtil::fix_html_table_border
     *
     * @param unknown $html_string
     * @return mixed
     */
    private function fix_html_table_border( $html_string )
    {
        //search <table style="">
    
        $search_pat = '/(<table.*style=")([^"]*)(?!border[^"])+"/misU';
    
        //preg_match( $search_pat, $html_string, $matches );
    
        //pr( '$matches=' . print_r($matches, true) );
    
        $html_string = preg_replace($search_pat, '\1\2 border:0.1mm solid #FFFFFF;" ', $html_string);
    
        return $html_string;
    }
    
    /**
     * this is just a copy/rewrite from Andrei@orw DocUtil::process_html_token
     *
     * @param CreateDocxFromTemplate $docx            
     * @param unknown $token            
     * @param unknown $html            
     * @return boolean|string
     */
    private function process_html_token(CreateDocxFromTemplate $docx, $token, $html)
    {
        if (! ($docx instanceof CreateDocxFromTemplate)) {
            return false;
        }
        
        $found_fonts_attrs = array();
        
        $dom = $docx->getDOMDocx();
        $docXPath = new DOMXPath($dom);
        
        // $search = $docx->getTemplateSymbol(). $token . $docx->getTemplateSymbol();
        $search = $token;
        
        $query = '//w:p/w:r[w:t[text()[contains(., "' . $search . '")]]]';
        
        // $query = '//w:p/w:r';
        
        $foundNodes = $docXPath->query($query);
        
        // pr( $foundNodes );
        
        foreach ($foundNodes as $node) {
            $nodeText = $node->ownerDocument->saveXML($node);
            $cleanNodeText = strip_tags($nodeText);
            if (strpos($cleanNodeText, $search) !== false || strpos($cleanNodeText, $token) !== false) {
                
                // prepare node token xml
                $docDOM_node = new DOMDocument();
                $docDOM_node->loadXML('<w:root xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
                                               xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
                                               xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
                                               xmlns:mv="urn:schemas-microsoft-com:mac:vml"
                                               xmlns:o="urn:schemas-microsoft-com:office:office"
                                               xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
                                               xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
                                               xmlns:v="urn:schemas-microsoft-com:vml"
                                               xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
                                               xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
                                               xmlns:w10="urn:schemas-microsoft-com:office:word"
                                               xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
                                               xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
                                               xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
                                               xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
                                               xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
                                               xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
                                               mc:Ignorable="w14 wp14">' . $nodeText . '</w:root>');
                $docXpath_node = new DOMXPath($docDOM_node);
                
                // $ret = $docDOM_node->saveXML();
                
                // pr ( $ret );
                
                // get curent token block original font attributes
                $font_query = '//w:rFonts';
                $xmlfontNodesFont = $docXpath_node->query($font_query)->item(0);
                $font_allowed_attributes = array(
                    'ascii',
                    'hAnsi',
                    'cs'
                );
                
                if ($xmlfontNodesFont) {
                    foreach ($xmlfontNodesFont->attributes as $attribute_name => $attribute_node) {
                        $found_fonts_attrs[$token]['font_data'][$attribute_name] = $attribute_node->nodeValue;
                        
                        if (in_array($attribute_name, $font_allowed_attributes)) {
                            $found_fonts_attrs[$token]['font']['name'] = $attribute_node->nodeValue;
                        }
                    }
                }
                if (! isset($found_fonts_attrs[$token]['font']['name'])) {
                    $found_fonts_attrs[$token]['font']['name'] = self::_default_font_family;
                }
                
                // get curent token block original font color
                $font_color_query = '//w:color';
                $xmlfontNodesColor = $docXpath_node->query($font_color_query)->item(0);
                
                // pr( $xmlfontNodesColor);
                
                if ($xmlfontNodesColor) {
                    foreach ($xmlfontNodesColor->attributes as $attribute_name => $attribute_node) {
                        $found_fonts_attrs[$token]['font']['color'] = $attribute_node->nodeValue;
                    }
                }
                
                // get curent token block original font decorations [bold]
                $font_bold_query = '//w:b';
                $xmlfontNodesBold = $docXpath_node->query($font_bold_query)->item(0);
                
                if ($xmlfontNodesBold) {
                    // foreach ($xmlfontNodesBold->attributes as $attribute_name => $attribute_node)
                    // {
                    $found_fonts_attrs[$token]['font']['isbold'] = '1';
                    // }
                }
                
                // get curent token block original font decorations [underline]
                $font_underline_query = '//w:u';
                $xmlfontNodesUnderline = $docXpath_node->query($font_underline_query)->item(0);
                
                // pr( $xmlfontNodesUnderline );
                
                if ($xmlfontNodesUnderline) {
                    foreach ($xmlfontNodesUnderline->attributes as $attribute_name => $attribute_node) {
                        $found_fonts_attrs[$token]['font']['isunderline'] = '1';
                    }
                }
                
                // get curent token block original font decorations [italic]
                $font_italic_query = '//w:i';
                $xmlfontNodesItalic = $docXpath_node->query($font_italic_query)->item(0);
                
                // pr( $xmlfontNodesItalic);
                
                if ($xmlfontNodesItalic) {
                    // foreach ($xmlfontNodesItalic->attributes as $attribute_name => $attribute_node)
                    // {
                    $found_fonts_attrs[$token]['font']['isitalic'] = '1';
                    // }
                }
                
                // get curent token block original font size
                $font_size_query = '//w:sz';
                $xmlfontNodesSize = $docXpath_node->query($font_size_query)->item(0);
                
                if ($xmlfontNodesSize) {
                    foreach ($xmlfontNodesSize->attributes as $attribute_name => $attribute_node) {
                        // pr( $attribute_name );
                        // pr( $attribute_node );
                        
                        $found_fonts_attrs[$token]['font']['size'] = $attribute_node->nodeValue / 2;
                    }
                }
            }
        }
        
        // pr( $found_fonts_attrs);
        
        $token_fonts = $found_fonts_attrs;
        $token_html = $token;
        
        $css_style = array();
        if (isset($token_fonts[$token_html]['font']['name']) && strlen($token_fonts[$token_html]['font']['name']) > '0') {
            $css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
        }
        
        if (isset($token_fonts[$token_html]['font']['size']) && strlen($token_fonts[$token_html]['font']['size']) > '0') {
            $css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
            $css_style[] = 'line-height:' . $token_fonts[$token_html]['font']['size'] . 'pt';
        }
        
        if (isset($token_fonts[$token_html]['font']['color']) && strlen($token_fonts[$token_html]['font']['color']) > '0') {
            $css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
        }
        
        if (isset($token_fonts[$token_html]['font']['isbold']) && $token_fonts[$token_html]['font']['isbold'] == '1') {
            $css_style[] = 'font-weight:bold';
        }
        
        if (isset($token_fonts[$token_html]['font']['isitalic']) && $token_fonts[$token_html]['font']['isitalic'] == '1') {
            $css_style[] = 'font-style:italic';
        }
        
        if (isset($token_fonts[$token_html]['font']['isunderline']) && $token_fonts[$token_html]['font']['isunderline'] == "1") {
            $css_style[] = 'text-decoration:underline';
        }
        
        // dummy css control
        if (! empty($css_style)) {
            $css_style[] = '';
        }
        
        $html = html_entity_decode('<div style="' . implode(';', $css_style) . '">' . $html . '</div>', ENT_COMPAT, 'UTF-8');
        
        return $html;
    }

    private function debug($message)
    {
        if ($this->_logger) {
            $this->_logger->debug($message);
        }
    }

    private function log_error($message)
    {
        if ($this->_logger) {
            $this->_logger->error($message);
        } else {
            throw new Zend_Exception($message, 3);
        }
    }

    private function log_info($message)
    {
        if ($this->_logger) {
            $this->_logger->info($message);
        }
    }

    public function __handleExceptions(Exception $e)
    {
        // render a view with error message for the user
    }
    
    
    /**
     * this is NOT used by the helper, it was added as an easy method to test all tokens in a template
     * if no param is supplied it will extract all the tokens and add random values for them
     * 
     * @param string $templates
     * @param string $variables
     */
    public function test_all_invoice_templates($templates = null, $variables = null , $output = 'pdf')
    {
        
        $umlauts = [ "ä", "Ä", "äu", "Äu", "ö", "Ö", "ü", "Ü", "daß", "der Fluß", "das Schloß" ];
        
        $this->_token_controller = 'TEST';
        
        
        if (is_null($templates)) {
            
            $files = Doctrine_Query::create()
            ->select('*')
            ->from('InvoiceTemplates')
            ->where('isdeleted = "0"')
            ->fetchArray();
            
            $templates =  array();
            
            foreach ($files as $file) {
                $templates[] = INVOICE_TEMPLATE_PATH . '/' . $file['file_path'];
            }
            
        } else {
            $templates = is_array($templates) ? $templates : array($templates);
        }
        
        foreach ($templates as $template) {
           
            if ( ! file_exists($template)) {
                continue;
            }
                        
            if (is_null($variables)) {
                
                $docx = new CreateDocxFromTemplate($template);
                $this->_template_variables = $docx->getTemplateVariables();
                
                $all_tokens = Pms_CommonData::array_flatten($this->_template_variables);
                
                $textVariables = array();
                
                foreach ( $all_tokens as $token) {
                     
                    $random_string = $umlauts[array_rand($umlauts, 1)]
                    . strtolower($this->_getRandomWord(rand(3,10)))
                    . " "
                        . $umlauts[array_rand($umlauts, 1)]
                        . strtolower($this->_getRandomWord(rand(3,10)))
                        . $umlauts[array_rand($umlauts, 1)]
                        ;
                     
                    $textVariables[$token] =  mb_convert_case($random_string, MB_CASE_TITLE, 'UTF-8');
                }
                
                $tokenfilter = array('TEST' => $textVariables);
                
            } else {
                $tokenfilter = $variables;
            }

            $name =  time() . mt_rand(9999, 999999999);
            	
			//ISPC-2609 Ancuta 26.09.2020
            //$this->setOutputFile(PDFDOCX_PATH . '/' . $this->logininfo->clientid . '/' . $name);
            $this->setOutputFile(PDFDOCX_PATH . '/' . $this->_client_id . '/' . $name);
            	
            //create a copy and add the original template for compariso
            $name = Pms_CommonData::filter_filename('test_docx_template_copy_' . microtime(true) . ".docx");
			//ISPC-2609 Ancuta 26.09.2020
            //copy($template, PDFDOCX_PATH . '/' . $this->logininfo->clientid . '/' . $name);
            //array_push($this->_all_docx_files, PDFDOCX_PATH . '/' . $this->logininfo->clientid . '/' . $name); 
            copy($template, PDFDOCX_PATH . '/' . $this->_client_id . '/' . $name);
            array_push($this->_all_docx_files, PDFDOCX_PATH . '/' . $this->_client_id . '/' . $name); 
            
            $this->create_docx ($template, $tokenfilter) ;
           
            
        }
        
        $this->setBrowserFilename(" TESTMODE all invoice templates merged as PDF");
        
        
        if ($output == 'pdf') {
            
            $this->merge_as_pdf();
                        
        } else {
            
            $this->merge_as_docx();
        }
                
        $this->download_file();
         
        
    }
    
    private function _getRandomWord($len = 10) {
        $word = array_merge(range('a', 'z'), range('A', 'Z'));
        shuffle($word);
        return substr(implode($word), 0, $len);
    }
    
}
