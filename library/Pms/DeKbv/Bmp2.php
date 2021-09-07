<?php

//namespace SmartqStandalone\MediPlanType;

// require_once( dirname(__DIR__) . DIRECTORY_SEPARATOR . 'TcpdfService.php');

// use SmartqStandalone\TcpdfExtended;

/* require_once( 'SmartqBundle/BarcodeService.php' ); // or similar
$barcodeSvc = new \SmartqStandalone\BarcodeService();

$xml = $barcodeSvc->generateMediplanDMapXML( $mpData );
$out = $barcodeSvc->textToBarcode( $xml , 'datamatrix' , ['w'=>4,'h'=>4,'jpg'=>true] );
// please note: option "jpg" is only used to demonstrate the possibility to
// force the generation of (conversion to) a JPEG file instead of a PNG
 */
class Pms_DeKbv_Bmp2
{

    protected $tcpdfService = null;
    // protected $tcpdfName    = '';

    protected $barcodeService = null;

    protected $pdf = null;

    /**
     * [$generic description]
     *
     * references \SmartqStandalone\MediPlanService and its more generic methods
     * 
     * @var object
     */
    protected $generic = null;

    /**
     * [$version description]
     *
     * not yet in use. Intended to implement different flavours of the same basic MedPlan
     * 
     * @var string
     */
    protected $version = '';

    /**
     * [$dataMatrixArray description]
     *
     * This is our basic data tree. It contains the data as a multidimensional array
     * that maps the content in accordance with the datafield/attribute pathes of the
     * BMP specification as they are also used to build the DataMatrix-XML
     * 
     * @var array
     */
    // protected $dataMatrixArray = [];
//     protected $dataMatrixDOM = [];
    protected $dataMatrixDOM = array();


    /**
     * [$dataMatrixXml description]
     *
     * Filled if self::importDataMatrixXml() is used to import the data to preserve the original XML
     * 
     * @var null
     */
    protected $dataMatrixXml = null;


    /**
     * [__construct description]
     */
    public function __construct ( array $options ) {
    	
        $this->tcpdfService   = $options['tcpdf_service'];
        $this->barcodeService = $options['barcode_service'];
        $this->version        = $options['version'];
        $this->generic        = $options['generic']; // GENERIC is now my controller(not service) @claudiu 30.08.2017 
        // $this->tcpdfService->startNewDocument( 'L', 'mm', 'A4', [] );
    }

    
    
    public function importDataMatrixArray( $data = array() , $options = array()  ) {
    	
    	$rootElementName = 'MP';
    	$defaultElementName = 'UNKNOWN';
    			
    	$domDoc = $this->data2domNode($data, $rootElementName, null, $defaultElementName);
    	
    	$this->importDataMatrixDOM( $domDoc , $options );
    	
    }
    
    protected function data2domNode($elementContent, $elementName, DOMNode $parentNode = null, $defaultElementName = 'item') {
    	
    	$parentNode = is_null($parentNode) ? new DOMDocument('1.0', 'utf-8') : $parentNode;
    	$name = is_string($elementName) ? $elementName : $defaultElementName;
    	
    	if (!is_array($elementContent)) {
    		$content = htmlspecialchars($elementContent);
    		$element = new DOMElement($name, $content);
    		$parentNode->appendChild($element);
    	} else {
    		$element = new DOMElement($name);
    		$parentNode->appendChild($element);
    		foreach ($elementContent as $key => $value) {
    			$elementChild = $this->data2domNode($value, $key, $element);
    			$parentNode->appendChild($elementChild);
    		}
    	}
    	return $parentNode;
    }

    /**
     * Import XML data that is structured like the XML used for the DataMatrix as working base
     *
     * Options:
     *    'encoding' : use another encoding than "ISO-8859-1" to resolve the XML data, 
     *                 e.g. "UTF-8" (if not set, UTF-8 will be auto-recognized).
     *
     * @param  [type] $xml [description]
     * @param  array  $options  see description of method importDataMatrixArray()
     * @return [type]      [description]
     */
    public function importDataMatrixXml( $xml , $options = array() ) {

        $domDoc = new DOMDocument();
        $domDoc->preserveWhiteSpace = false; // !!!

        if ( isset($options['encoding']) ) {
            $encLc = strtolower($options['encoding']);
            if ( 'utf-8' != $encLc && 'utf8' != $encLc ) {
                if ( function_exists('mb_convert_encoding') ) {
                    $xml = mb_convert_encoding( $xml , 'UTF-8' , $options['encoding'] );
                }
                else {
                    $xml = utf8_encode( $xml );
                }
            }
        }
        elseif ( mb_detect_encoding( $xml, 'UTF-8', true ) !== 'UTF-8' ) {
            if ( function_exists('mb_convert_encoding') ) {
                $xml = mb_convert_encoding( $xml , 'UTF-8' , 'Windows-1252' );
            }
            else {
                $xml = utf8_encode( $xml );
            }
        }

        $domDocLoaded = $domDoc->loadXML( '<'.'?xml version="1.0" encoding="UTF-8"?>'."\n".trim($xml) );
        
        if ( ! $domDocLoaded ) {
        	die( __METHOD__ . ' : could not decode xml data.' . trim($xml));
            throw new Exception( __METHOD__ . ' : could not decode xml data.', 1 );
        } else {
            $this->importDataMatrixDOM( $domDoc , $options );
        }

    }


    /**
     * [importDataMatrixArray description]
     *
     * ... used to directly fill the basic data tree with a compatible array.
     *
     * *** In this case the data MUST be UTF-8 encoded ! ***
     *
     * 
     * @param  array  $data     [description]
     * @param  array  $options  (currently not in use)
     * @return [type]           [description]
     */
    public function importDataMatrixDOM( $dom , $options = array() ) {

        $MP = $dom->firstChild;

        $MP_A_present = false;
        $MP_P_present = false;

        if ( empty($MP) || 'MP' !== $MP->nodeName ) {
        	die(__METHOD__ . ' : incompatible data (first child in dom is not an <MP> node).');
            throw new Exception( __METHOD__ . ' : incompatible data (first child in dom is not an <MP> node).', 1 );
        }

        foreach ( $MP->childNodes as $child ) {
            if ( 'A' !== $child->nodeName ) {
                $MP_A_present = true;
                /*
                $attr = $child->getAttribute('n');
                if ( empty( $attr ) ) {
                    throw new \Exception( __METHOD__ . ' : incompatible data (no editor name(MP.A.n)).', 1 );
                }
                /* */
            }
            if ( 'P' !== $child->nodeName ) {
                $MP_P_present = true;
                /*
                $attr = $child->getAttribute('f');
                if ( empty( $attr ) ) {
                    throw new \Exception( __METHOD__ . ' : incompatible data (no patient name (MP.P.f)).', 1 );
                }
                /* */
            }
        }

        if ( ! $MP_A_present ) {
        	die(__METHOD__ . ' : incompatible data (no editor data / MP.A missing).');
            throw new Exception( __METHOD__ . ' : incompatible data (no editor data / MP.A missing).', 1 );
        }

        if ( ! $MP_P_present ) {
        	die(__METHOD__ . ' : incompatible data (no patient data / MP.P missing).');
            throw new Exception( __METHOD__ . ' : incompatible data (no patient data / MP.P missing).', 1 );
        }

        $this->dataMatrixDOM = $dom;

    }


//     /*
    public function dumpDataMatrixDOM( $returnString = false ) {
        
    	$options = array();
    	
    	if ($returnString) {
            
    		return $this->generateDataMatrixXML( null, $options );
        
    	} else {
        	
        	if (defined(DEBUGMODE) && DEBUGMODE) {
        		$options['debug'] = true;
        	}
            echo "\n\n" . $this->generateDataMatrixXML( null, $options ) . "\n\n";
//              var_export ( $this->dataMatrixDOM , false );
            // var_dump( $this->dataMatrixDOM );
            return null;
        }
    }
    /* */

    public function getDataMatrixDOM() {
        return $this->dataMatrixDOM;
    }

    /**
     * Read an xml key file and provide the relevant content as a key/value array
     *
     * See http://applications.kbv.de/keytabs/ita/schluesseltabellen.asp
     * (files that start with "BMP_")
     *
     * A wrong encoding is automatically sanitized. The values in the output array 
     * will always be encoded as UTF-8.
     *
     * ########################################################
     * ##  ToDo: this could use a good caching mechanism !!  ##
     * ########################################################
     * 
     * @param  [type] $fileName [description]
     * @param  array  $options  [description]
     * @return [type]           [description]
     */
    public function keyFileToArray( $fileName , $options = array() ) {
        
    	$fullPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'MediPlanData'
                  . DIRECTORY_SEPARATOR . $fileName.'.xml';
    	
        $domDoc = new DOMDocument();
        //$domDoc = new DOMDocument("1.0" , "ISO-8859-1"); LMU 
        
        $domDoc->preserveWhiteSpace = false; // !!!
        $domDocLoaded = $domDoc->load( $fullPath );
		//$domDocLoaded = $domDoc->loadxml(file_get_contents($fullPath)); LMU
        
        $encoding = trim( $domDoc->xmlEncoding );

        if ( $domDocLoaded ) {
            $keyNodes = $domDoc->getElementsByTagName('key');
        } 
        else {
        	die(__METHOD__ . ' : unable to load/parse file ' . $fullPath);
            throw new Exception( __METHOD__ . ' : unable to load/parse file ' . $fullPath , 1);
        }

        // $needsSanitation is NOT a boolean but an integer, meaning:
        // 0 : no sanitation necessary - it's already UTF-8
        // 1 : try to apply iconv(), fall back to utf8_encode()
        // 2 : apply utf8_encode()
        if ( empty( $encoding ) ) {
            $needsSanitation = ( mb_detect_encoding( $text, 'UTF-8', true ) === 'UTF-8' ) ? 0 : 2;
        }
        else {
            $needsSanitation = in_array( strtolower($encoding) , array('utf-8','utf8','utf 8') ) ? 0 : 1;
        }

        $keyTrans = array();

        foreach ( $keyNodes as $keyNode ) {
            $content = $keyNode->getAttribute('DN');
            if ( 2 === $needsSanitation ) {
                try {
                    $content = iconv( $encoding, "UTF-8", $content );
                } 
                catch ( Exception $e ) {
                    $content = utf8_encode( $content );
                }
            } 
            elseif ( 2 === $needsSanitation ) {
                $content = utf8_encode( $content );
            } 
            $keyTrans[ $keyNode->getAttribute('V') ] = $content;
        }

        unset( $domDoc );

        return $keyTrans;

    }


    /**
     * Translate a DOM node WITH EMPTY "LEAFS" into an associative array
     *
     * "With empty leafs" means here: the source should not contain text/cdata inside of the
     * leafs (== nodes that contain no further sub-nodes), as these strings will be ignored.
     *
     * Consequently, content/strings can only be transported in the Attributes.
     *
     * If a node can appear multiple times this must be announced using the "multiple"
     * option (e.g. [... 'multiple' => ['MP.S','MP.S.M','MP.S.X','MP.S.R'] ]). The 
     * corresponding array fields will then contain a numbered array of values
     * (e.g. there will be something like $x['MP']['S'][0]['M'][0] ).
     *
     * ***********************************************************************
     * ATTENTION: if 'MP.S.M','MP.S.X' and 'MP.S.R' occur in a mixed manner **
     * inside of the same 'MP.S' their ORDER GETS LOST in the resulting     **
     * array !!! Prefer working directly based on a/the DOM if possible!    **
     * ***********************************************************************
     * 
     * Options:
     *   'skip'     : array of node pathes (e.g. "MP.S.M") that shall not be included in the array
     *   'multiple' : array of node pathes (e.g. "MP.S.M") that can occur more then one time inside
     *                their direct parent ("MP.S" in this case) - see also description
     *
     * @param  [type] $node    [description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public function getArrayFromNode( $node , $options = array() ) {

        $rtnArr = array();

        $options = array_merge( array(
            'skip' => array(),
            'multiple' => array()
        ) , $options );

        $path     = $node->nodeName;
        $parent   = $node->parentNode;
        $ancestor = $parent;
        while( $ancestor && XML_DOCUMENT_NODE != $ancestor->nodeType ) {
            $path = $ancestor->nodeName . '.' . $path;
            $ancestor = $ancestor->parentNode;
        }

        if ( $node->nodeType === XML_ELEMENT_NODE ) {
            foreach ( $node->attributes as $attribute ) {
                $rtnArr[ $attribute->nodeName ] = $attribute->nodeValue;
            }
        }

        foreach ( $node->childNodes as $child ) {

            if ( $child->nodeType !== XML_ELEMENT_NODE ) {
                continue;
            }

            $cPath = $path . '.' . $child->nodeName;

            if ( in_array( $cPath , $options['skip'] ) ) {
                continue;
            }
            elseif ( in_array( $cPath , $options['multiple'] ) ) {

                if( ! isset($rtnArr[$child->nodeName]) ) {
                    $rtnArr[$child->nodeName] = array();
                }

                $cData = $this->getArrayFromNode( $child , $options );
                array_push( $rtnArr[$child->nodeName] , $cData );

            } 
            else {

                $cData = $this->getArrayFromNode( $child , $options );
                $rtnArr[$child->nodeName] = $cData;

            }
        } // eo "foreach ( $node->childNodes ... "

        return $rtnArr;

    }


    /**
     * 
     * @claudiu 29.08.2017 
     * ENT_XML1 added in 5.4.0, lucy me the fn is not used yet
     * 
     * Convert clear text to a DOM attribute (which is formatted like DataMatrix data / tilde for lbr)
     *
     * Options:
     *   'tilde'   :  a string that shall be used to replace a tilde instead of &#x7E; (default)
     * 
     * @param  string $text     text to be sanitized
     * @param  array  $options  see description
     * @return string           sanitized text
     */
    public function clearTextToAttrib( $text , $options = array() ) {
        if ( is_string($text) ) {
            if ( empty( $options['charset'] ) ) {
                $options['charset'] = 'UTF-8';
            }
            // $text = \htmlspecialchars( (string) $text );
            $text = html_entity_decode( $text, ENT_COMPAT|ENT_XML1, $options['charset'] );
            if ( isset( $options['tilde'] ) ) {
                if ( ! empty( $options['tilde'] ) ) {
                    $text = str_replace( '~', $options['tilde'], $text );
                    $text = str_replace( array("\r\n", "\n\r"), '~', $text );
                    $text = str_replace( "\n", '~', $text );
                } 
            }
            else {
                $text = str_replace( '~', '&#x7E;', $text );
                $text = str_replace( array("\r\n", "\n\r"), '~', $text );
                $text = str_replace( "\n", '~', $text );
            }
            return $text;
        }
        else {
            return (string) $text;
        }            
    }

    public function clearTextToAttribRef( &$ref , $key='' , $options = array() ) {
        if ( is_string($ref) ) {
            $ref = $this->clearTextToAttrib( $ref , $options );
        }
    }

    /**
     * Convert a DOM attribute (which is formatted like DataMatrix data) to clear text
     * 
     * Options:
     *   'tilde'   :  a string that shall be recognized as tilde instead of the default &#x7E;
     * 
     * @param  string $text     text to be sanitized
     * @param  array  $options  see description
     * @return string           sanitized text
     */
    public function attribToClearText( $text , $options = array() ) {
        if ( is_string($text) ) {
            $text = str_replace( '~', "\n", $text );
            if ( isset( $options['tilde'] ) ) {
                if ( ! empty( $options['tilde'] ) ) {
                    $text = str_replace( $options['tilde'], '~', $text );
                } 
            }
            return htmlspecialchars( $text );
        }
        else {
            return $text;
        }
    }

    public function attribToClearTextRef( &$ref , $key , $options = array() ) {
        if ( is_string($ref) ) {
            $ref = $this->attribToClearText( $ref , $options );
        }
    }


    
    /**
     * Lore 18.03.2020 TODO-2999 // Maria:: Migration ISPC to CISPC 08.08.2020
     * @param string $text
     * @param array $options
     * @return string
     */
    public function attribToClearTextEntityDecode( $text , $options = array() ) {
        if ( is_string($text) ) {
            $text = str_replace( '~', "\n", $text );
            if ( isset( $options['tilde'] ) ) {
                if ( ! empty( $options['tilde'] ) ) {
                    $text = str_replace( $options['tilde'], '~', $text );
                }
            }
            // Lore 20.12.2019 ISPC-2329 - in the field "INDIKATION/ KOMMENTAR" the symbol "<" or ">" in pdf dont show correct
            return html_entity_decode( $text, ENT_COMPAT|ENT_XML1, $options['charset'] );
        }
        else {
            return $text;
        }
    }
    
    
    
    /**
     * Generate an XML structure that is compatible with the XML for the DataMatrix in a MediPlan
     *
     * Attention: this does NO conversion from UTF-8 to latin1 so far (so that must be done by the 
     * DataMatrix generator!)
     *
     * Options:
     *   'dom'        :  data tree or sub-node to be used instead of $this->dataMatrixDOM
     * 
     * @param   DOMDcument|DOMElement|null       $dom      [description]
     * @param   array           $options  [description]
     * @return  string          Mediplan-DataMatrix compatible XML translation of $topNode
     * @throws  \Exception      Something went wrong
     */
    public function generateDataMatrixXML( $dom = null, $options = array() ) {

        if ( isset($dom) ) {
            $topNode = $dom;
        }
        elseif ( isset( $options['dom'] ) ) {
            $topNode = $options['dom'];
        }
        else {
            $topNode = $this->dataMatrixDOM;
            
        }
        
        if ( empty($topNode) ) {
        	die( __METHOD__ . ' : unable to process an empty value.' );
            throw new Exception( __METHOD__ . ' : unable to process an empty value.' );
        }
        elseif ( $topNode instanceof DOMNode ) {
            if ( $topNode instanceof DOMDocument ) { 
            	if ( isset( $options['debug'])) {
            		$topNode->formatOutput = true;
	            	
            	}
                return $topNode->saveXML($topNode->firstChild);
            }
            else {
                return $topNode->ownerDocument->saveXML($topNode);
            }
        } 
        else {
            if ( is_object($topNode) ) {
            	die(__METHOD__ . ' : unable to process an object of class "' 
                                      . get_class($topNode) . '".');
                throw new Exception( __METHOD__ . ' : unable to process an object of class "' 
                                      . get_class($topNode) . '".' );
            }
            elseif ( is_array($topNode) ) {
            	die(__METHOD__ . ' : unable to process an array.');
                throw new Exception( __METHOD__ . ' : unable to process an array.' );
            }
            elseif ( empty($topNode) ) {
            	die(__METHOD__ . ' : unable to process an empty value.');
                throw new Exception( __METHOD__ . ' : unable to process an empty value.' );
            }
            else {
            	die(__METHOD__ . ' : unable to process a scalar value.');
                throw new Exception( __METHOD__ . ' : unable to process a scalar value.' );
            }
        }

    }

    
    private $that = null;
    private $options = null;
    
    protected  function doLog ( $text )  {
            if ( $this->options['debug'] == '@echo' ) {
                echo "\n" . $text ;
            }
            else {
                error_log( "\n".$text, 3, $this->options['debug'] );
            }

    } //eo $doLog;
    
    
    
    protected function doDebugLine  ( $text ) {
    	if ( ! empty($this->options['debug']) ) {
    		$this->doLog( $text );
    	};
    } //eo $this->doDebugLine;
    
    

    protected function doDebugInput  () { 
    	//use( &$that, &$options, &$doLog, &$pznResult, &$rowsPerSection,	&$pznList) {
    	if ( ! empty($this->options['debug']) ) {
    		$inputMatrix = $this->that->generateDataMatrixXML();
    		$this->doLog( "\n------------------------\n\n".__FILE__."\n".date('Y-m-d H:i:s') );
    		$this->doLog( "\nInput-DOM as DM-XML:\n".$inputMatrix );
    		$this->doLog( "\nOptions:\n".print_r($this->options , true ) );
    		$this->doLog( "\nRows Per Section:\n".print_r($rowsPerSection , true ) );
    		$this->doLog( "\nPZN List:\n".print_r($pznList , true ) );
    		$this->doLog( "\nPZN Result:\n".print_r($pznResult , true ) );
    		$this->doLog( "\n---" );
    	};
    } //eo $this->doDebugInput;
    
    
    protected function doDebugLoop ( $markerName='loop', $sectCount=-1, $mrxCount=-1 ) {
    	//use( &$that, &$options, &$doLog, &$rowsPerSecRest, &$mrxOffset, &$sectionOffset,	&$pageRowsCount, &$startNewPage ) {
    	if ( ! empty($options['debug']) ) {
    		$this->doLog( "\n-- ".$markerName.' --' );
    		$this->doLog( '  $pageRowsCount: '.$pageRowsCount );
    		// $doLog( '  count($sectionsForDataMatrix): '.count($sectionsForDataMatrix) );
    		$this->doLog( '  $startNewPage: '.($startNewPage?'yes':'no') );
    		if($sectCount>-1) {
    			$this->doLog( '  $sectCount / $i: ' . $sectCount );
    			$this->doLog( '  $rowsPerSecRest['.$sectCount.']: '.$rowsPerSecRest[$sectCount] );
    			if($mrxCount>-1) {
    				$this->doLog( '  $mrxCount: '.$mrxCount );
    			}
    		}
    		$this->doLog( '  $sectionOffset: '.$sectionOffset );
    		$this->doLog( '  $mrxOffset: '.$mrxOffset );
    	};
    } //eo $this->doDebugLoop;
    
    
    
    /**
     * [generatePDF description]
     *
     * Options:
     *   'dom'        : alternative DOMDocument to be used if not $this->dataMatrixDOM 
     *                  shall be used (previously imported via one of the "import~" functions.)
     *   'dest'       : see \SmartqBundle\TcpdfExtended::OutputEasy()
     *   'path'       : see \SmartqBundle\TcpdfExtended::OutputEasy()
     *   'repeat_title' : repeat the title of a table section if there is an inline line break
     *   'eco_page_break' : do only start a long section at the top of the new page, if less than 4 lines are left
     *   'debug'      : (empty)|'/path/to/file.log'|'@echo'
     *   'image_reg'  : register a local image path under a name
     *   'footer_logo': e.g. [ 'file' => '#smartq_footer_logo', 'w' => 20, 'h' => 10 ]
     *   'footer_html': HTML code to be inserted in the middle part of the footer - can contain
     *                  image references like '<img ... src="%img_b64:smartq_footer_logo%" ... >'
     *                  - see SmartqStandalone\TcpdfExtended::registerLocalImage()
     *                  (file SmartqBundle/TcpdfExtended.php)
     *                  (A "smartq_footer_logo" (200*100px) is already registered.)
     *   'footer'     : simple Multicell content
     *
     * 
     * @param  string  $fileName  see \SmartqBundle\TcpdfExtended::OutputEasy()
     * @param  array   $options   see description above
     * @return string             the local path to the file
     */
    public function generatePDF ( $fileName , $options = array() ) {

        //================================================+
        // Debugging sub-functions (see option "debug")
        //================================================+


        $this->that = $this;


        //================================================+
        // ... eo Debugging sub-functions (see option "debug")
        //================================================+
        // Getting Services, Prepare some data, basic validation ...
        //================================================+

        $origMbInternalEnc = mb_internal_encoding();
        mb_internal_encoding("UTF-8");

        $errorMsg = '';

        $options = array_merge( array(
                'dom'   => null ,
                'dest'  => null ,
                'path'  => null ,
                'repeat_title' => true ,
                'eco_page_break' => true ,
                'language' => null ,
                'debug' => null
            ) ,
            $options
        );

        $pdfSrvc        = $this->tcpdfService;
        $barcodeService = $this->barcodeService;
        $customization  = $this->version;

        // UTF-8 non breaking space:
        $nbsp = chr(0xC2).chr(0xA0);

        if ( isset( $options['dom'] ) ) {
            $domDoc = $options['dom'];
        }
        else {
            $domDoc = $this->dataMatrixDOM;
        }

        if ( empty( $domDoc ) ) {
        	die(__METHOD__ . ' : DOM node missing.');
            throw new Exception( __METHOD__ . ' : DOM node missing.', 1 );
        }

        if ( ! is_object( $domDoc ) ) {
        	die(__METHOD__ . ' : DOM node variable contains wrong data type.');
            throw new Exception( __METHOD__ . ' : DOM node variable contains wrong data type.', 1 );
        }

        if ( ! $domDoc instanceof DOMDocument ) {
        	die(__METHOD__ . ' : DOM node variable contains object of wrong class.');
            throw new Exception( __METHOD__ . ' : DOM node variable contains object of wrong class.', 1 );
        }

        $xpath = new DOMXPath($domDoc);

        $MP_nodeList = $xpath->query('/MP');
        if ( ! $MP_nodeList->length ) {
        	die(__METHOD__ . ' : <MP> root node missing.');
            throw new Exception( __METHOD__ . ' : <MP> root node missing.', 1 );
        }
        $MP_node = $MP_nodeList->item(0);

        if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
            $bytes = openssl_random_pseudo_bytes( 16 );
            $uid   = strtoupper( bin2hex($bytes) );
        } 
        else {
            $uid   = strtoupper( md5( uniqid( '' , true ) ) );
        }

        if( empty($options['version']) ) {
            // Usually the version will not be changed from outside of this API!
            // when making changes to the output you will usually adapt this value:
			//$options['version'] = '2.3';//ISPC-2551 Ancuta 31.03.2020 Commented 
            $options['version'] = '2.6';//ISPC-2551 Ancuta 31.03.2020 New version // Maria:: Migration ISPC to CISPC 08.08.2020
        }

        $dmVersion = str_replace( '.', '', $options['version'] );
        if( (float) $options['version'] < 10 ) {
            $dmVersion = '0' . $dmVersion; // e.g. "023" instead of "2.3"
        }

        // v =  required
        $MP_node->setAttribute( 'v' , $dmVersion );
        
        // U =  required
        $MP_node->setAttribute( 'U' , $uid );
        
        // l =  required
        if ( isset($options['language']) ) {
            $MP_node->setAttribute( 'l' , $options['language'] );
        } // $options['language']
        elseif ( $MP_node->hasAttribute( 'l' ) ) {
            $options['language'] = $MP_node->getAttribute( 'l' );
        }
        else {
            $MP_node->setAttribute( 'l' , 'de-DE' );
            $options['language'] = 'de-DE';
        }

        // $dataArray is used for clear text output (does NOT contain MP.S.* !! )
        $dataArray = $this->getArrayFromNode( $domDoc , array( 'skip' => array( 'MP.S' ) ) );
        array_walk_recursive ( $dataArray , array( $this , 'attribToClearTextRef' ) );
        $dataArray = array_replace_recursive( array(
            'MP' => array(
            	
            	//$MPP patient
                'P'  => array(
                    'g'   => '' , //required first name
                    'f'   => '' , //required last name
                	'b'   => '' , //required dob $MPPb
                	
                    //'egk' => '' , //optional insured id
                    //'s'   => '' , //optional sex
                    //'t'   => '' , //optional title
                    //'v'   => '' , //optional prefix
                    //'z'   => '' , //optional sufix
                ) ,
            		
            	//$MPA doctor||pharmacy||hospital
                'A'  => array(
                    'n'   => '' , //required name
                	't'   => date('Y-m-d') ,//required print date $MPAt
                	
                	//'lanr'=> '' ,//optional doctor
                	//'idf' => '' ,//optional pharmacy
                	//'kik' => '' ,//optional hospital
                    //'s'   => '' ,//optional street $MPAs
                    //'z'   => '' ,//optional zip
                    //'c'   => '' ,//optional city $MPAc
                    //'p'   => '' ,//optional phone
                    //'e'   => '' ,//optional email
                    
                ) ,
            	//$MPO patient header extra infos
                'O'  => array(
                	//'ai'   => '',//optional alergies, intolerances
                	//'p'   => '',//optional pregnant
                	//'b'   => '',//optional breastfeeding
                	//'w'   => '',//optional weight
                	//'h'   => '',//optional height
                	//'c'   => '',//optional creatinine
                	//'x'   => '',//optional freetext
                )
            )
        ) , $dataArray );

        // the following arrays are mainly used to ease the building of the header 
        // using a kind of a template ( seek for "$headerContent =" ... ) :
        $MP  = $dataArray['MP'];
        $MPP = $options['Pdf_header']['P'];//$MP['P'];// ISPC-2551 Ancuta 31.03.2020
        $MPA = $options['Pdf_header']['A'];//$MP['A'];// ISPC-2551 Ancuta 31.03.2020
        $MPO = $options['Pdf_header']['O'];//$MP['O'];// ISPC-2551 Ancuta 31.03.2020

        //patient birthdate de format
        $MPPb = '0.0.0000';
        if ( ! empty( $MPP['b'] ) ) {
//             $dt = new Datetime( $MPP['b'] ); 
//             $MPPb = $dt->format('d.m.Y');            
            $MPPb =  date('d.m.Y', strtotime( $MPP['b']));
        }

        $MPAs = ''; // street
        if ( isset( $MPA['s'] ) ) {
            if ( mb_strlen ( $MPA['s'], 'UTF-8' ) > 30 ) {
                if ( preg_match( '~\D\d{1,4}$~', $MPA['s'] ) ) {
                    $MPAs = preg_replace( '~^(.{25}).*(\d+)$~u', '$1. $2', $MPA['s'] );
                } 
                else {
                    $MPAs = mb_substr( $MPA['s'], 0, 29, 'UTF-8' ) . '.';
                }
                
            } 
            else {
                $MPAs = $MPA['s'];
            }
        }
        $MPAc = ''; // city
        if ( isset( $MPA['c'] ) ) {
            if ( isset( $MPA['c'] ) && mb_strlen ( $MPA['c'], 'UTF-8' ) > 20 ) {
                $MPAc = mb_substr( $MPA['c'], 0, 19, 'UTF-8' ) . '.';
            }
            else {
                $MPAc = $MPA['c'];
            }
        }
        
        
        //TODO-3136 Ancuta 13.05.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
        $MPAe = ''; // EMAIL
        $MPAe_font = '12pt'; //font for email
        if ( isset( $MPA['e'] ) ) {
            if ( isset( $MPA['e'] ) && mb_strlen ( $MPA['e'], 'UTF-8' ) > 30  ) {
                $MPAe = mb_substr( $MPA['e'], 0, 40, 'UTF-8' );
                $MPAe_font = '10pt'; //font for email
            } elseif ( isset( $MPA['e'] ) && mb_strlen ( $MPA['e'], 'UTF-8' ) <= 30  ) {
                $MPAe = mb_substr( $MPA['e'], 0, 30, 'UTF-8' );
                $MPAe_font = '12pt'; //font for email
            }
            else {
                $MPAe = $MPA['e'];
            }
        }
        //-- 
        
        
        // printing date:
        $MPAt = date('d.m.Y');


        // fetch PZN-based data (all in one query) ...

        $pznList = array();
        foreach ( $MP_node->childNodes as $sectionNode ) {
            if ( $sectionNode->nodeType !== XML_ELEMENT_NODE || 'S' !== $sectionNode->nodeName ) {
                continue;
            }
            foreach ( $sectionNode->childNodes as $mrx ) { // => <M>, <X> or <R> node 
                if ( 'M' == $mrx->nodeName ) {
                    $pzn = trim( $mrx->getAttribute('p') );
                    if ( ! empty( $pzn ) && ! in_array( $pzn, $pznList ) ) {
                        array_push( $pznList, $pzn );
                    }
                }
            }
        }
        $pznResult = $this->getPznData( $pznList );
        if ( 'error' == $pznResult['status'] ) {
            $errorMsg .= '| ' .  $pznResult['error'];
        }
        // (see SmartqStandalone\MediPlanService::getPznData() for a result example)
        $pznData = $pznResult['products'];

        // note down the rows per section ...
        // (and add new nodes if necessary)

        $rowsPerSection = array();
        $i=-1;
        $addNewNode = array();
        foreach ( $MP_node->childNodes as $sectionNode ) {
            if ( $sectionNode->nodeType !== XML_ELEMENT_NODE || 'S' !== $sectionNode->nodeName ) {
                continue;
            }
            $i++;
            $rowsPerSection[$i] = 0;
            $STitleCode = trim( $sectionNode->getAttribute('c') );
            $STitleText = trim( $sectionNode->getAttribute('t') );
            // $countKids  = $sectionNode->childNodes->length;
            if( '' !== $STitleCode || '' !== $STitleText ) {
                // this section has a section title (repeated titles are handled separately) ...
                $rowsPerSection[$i]++;
            }
            foreach ( $sectionNode->childNodes as $mrx ) { // => <M>, <X> or <R> node 
                if ( 'M' == $mrx->nodeName ) {
                    // number of rows depends on number of active substances or long dosage scheme ...
                    $pzn = trim( $mrx->getAttribute('p') );
                    $tiFreetextLength = mb_strlen( trim( $mrx->getAttribute('t') ) );
                    $tiFreetextLengthvalues = mb_strlen( trim( $mrx->getAttribute('td') ) );
                    if ( ! empty( $pzn ) && in_array( $pzn, $pznList ) ) {
                        $rowsPerSection[$i] += count( $pznData[$pzn]['substances'] ) === 3 ? 2 : 1;
                    }
                    elseif( $mrx->getElementsByTagName('W')->length === 3 ) {
                        $rowsPerSection[$i] += 2;
                    }
                    else {
                    	$rowsPerSection[$i]++;
                    }
                    //ISPC-2573 Carmen 08.05.2020
                    /* elseif( $tiFreetextLength > 22 ) {
                        $rowsPerSection[$i] += 2;
                        // insert a new <X> node after the current one
                        array_push( $addNewNode , array(
                            'type' => 'X' ,
                            'attr' => array( 't' => 'Dosierschema: ' . $mrx->getAttribute('t') ) ,
                            'pos'  => 'after' ,
                            'ref'  => $mrx
                        ) );
                        $mrx->setAttribute( 't', 'siehe nächste Zeile' ); // <= Spec. says: 20 char. are allowed
                    } */
                    if( $tiFreetextLengthvalues > 0 ) {
                    	$rowsPerSection[$i] ++;
                    	// insert a new <X> node after the current one
                    	array_push( $addNewNode , array(
                    			'type' => 'X' ,
                    			//'attr' => array( 't' => 'Dosierschema: ' . $mrx->getAttribute('td') ) ,
                    			'attr' => array( 't' => $mrx->getAttribute('td') ) ,
                    			'pos'  => 'after' ,
                    			'ref'  => $mrx
                    	) );
                    	//$mrx->setAttribute( 't', 'siehe nächste Zeile' ); // <= Spec. says: 20 char. are allowed
                    	$mrx->removeAttribute( 'td');
                    }
                    //--
                    /* else {
                        $rowsPerSection[$i]++; 
                    } */
                }
                else {
                    $rowsPerSection[$i]++;
                }
            }
        }

        // this actually adds nodes if defined above ...
        foreach ( $addNewNode as $dscr ) {
            $mrx = $dscr['ref'];
            $_NEW_NODE_ = $mrx->ownerDocument->createElement( $dscr['type'] );
            foreach ( $dscr['attr'] as $key => $value ) {
                $_NEW_NODE_->setAttribute( $key , $value );
            }
            if ( 'after' === $dscr['pos'] ) {
                if ( $mrx->nextSibling ) {
                    $mrx->parentNode->insertBefore( $_NEW_NODE_, $mrx->nextSibling );
                }
                else {
                    $mrx->parentNode->appendChild( $_NEW_NODE_ );
                }

            } else {
                $mrx->parentNode->insertBefore( $_NEW_NODE_, $mrx );
            }
        }

        unset( $addNewNode, $dscr, $mrx, $attrDsc, $tiFreetextLength );

        $this->doDebugLine('Rows per section done.');

        $this->doDebugInput();

        // we make a copy of the array to be able to preserve the original values:
        $rowsPerSecRest = $rowsPerSection;


        //================================================+
        // Preparation of the DataMatrices ( => $dmXmlPerPage ) ...
        //================================================+


        $latin1ConvStrict = true; // if we may not use windows-1252 instead of ISO-8859-1

        $MP_node = $domDoc->firstChild;
        if ( $MP_node->hasAttribute('a') ) {
            $MP_node->removeAttribute('a');
        }
        if ( $MP_node->hasAttribute('z') ) {
            $MP_node->removeAttribute('z');
        }

        $newDom    = new DOMDocument();
        $cloneMp   = $newDom->importNode( $MP_node, false );
        $newMPNode = $newDom->appendChild($cloneMp);

        // initial xml for page 2 and further (without patient data, editor's data etc.):
        $xmlStartPn = $this->generateDataMatrixXML( $newDom );
        $xmlStartPn = preg_replace( '~</MP>$~', '', $xmlStartPn );
        // $xmlStartPn = $barcodeService->convertUtf8Latin1( $xmlStartPn , $latin1ConvStrict );

        // this array contains the xml representation of all section nodes (<S>):
        $sectXmlArr = array();

        // add <P ...>, <A ...> and <O ...> to $newMPNode and convert all
        // sections <S ...>...</S> into an array of their XML representations:
        foreach ( $MP_node->childNodes as $l1Child ) {
            if ( $l1Child->nodeName != 'S' ) {
                $cloneC = $newDom->importNode( $l1Child, false );
                $newMPNode->appendChild($cloneC);
            }
            else {
                $sXml = $this->generateDataMatrixXML( $l1Child );
                // $sXml = $barcodeService->convertUtf8Latin1( $sXml , $latin1ConvStrict );
                array_push( $sectXmlArr, $sXml );
            }
        }

        // initial xml for page 1 (including patient data, editor's data & "parameter block" <O>):
        $xmlStartP1 = $this->generateDataMatrixXML( $newDom );
        $xmlStartP1 = preg_replace( '~</MP>$~', '', $xmlStartP1 );
        // $xmlStartP1 = $barcodeService->convertUtf8Latin1( $xmlStartP1 , $latin1ConvStrict );

        // #ToDo: Optionally set $xmlStartPn = $xmlStartP1 ...
        if ( true ) {
            $xmlStartPn = $xmlStartP1;
        }

        $default_rows_per_page = 12;//ISPC-2551 Ancuta 01.04.2020
        // Build the XML for the single DataMatrix instances (=="pages") ...
//         $maxBytesPerDM = 1400;
        $maxBytesPerDM = 862;//ISPC-2551 Ancuta 01.04.2020
        $dmXmlPerPage  = array( $xmlStartP1 );
        $currentXml    =& $dmXmlPerPage[0];
        $justStNP      = true; // "Just started new page"
        for( $i=0; $i<count($sectXmlArr); $i++ ) {
            $sectXml = $sectXmlArr[$i];
            // note: 17 characters are needed for a="X", z="X" and </MP> (see next "for" loop):
            $overlength = strlen($currentXml) + strlen( $sectXml ) + 17 - $maxBytesPerDM;
            if ( $overlength > 0 ) {

                if ( ! $justStNP ) {
                    // the current DM is "full" - we leave the current DM / "page "
                    // and switch to the next DM / "page":
                    $newPageNum = count($dmXmlPerPage);
                    $dmXmlPerPage[$newPageNum] = $xmlStartPn;
                    $currentXml =& $dmXmlPerPage[$newPageNum];
                    $justStNP   = true;
                    $i--; // repeat with same $i (no endless loop because $justStNP is true)
                    continue;

                }
                else { // ... we're at the top of a new "page" ...

                    // in this case the section does not fit in whole into one single DataMatrix
                    // and we must split it up
                    // (note: all Strings are ISO-8859-1, so we don't need the mb_*-functions here)

                    if ( $overlength < 8) {
                        $overlength = 8; // => "...</M></S>"
                    }

                    // we search the last fitting </M>, </X> or </R> ...
                    $maxLength = strlen( $sectXml ) - $overlength - 4; 
                    // ( 4 => we need to add a "</S>" at the end!)
                    $subString = substr( $sectXml, 0, $maxLength );

                    // finding the position of the last occurence of a rear end of 
                    // an <M>,<R> or <X> node inside $subString ...
                    $revSubStr = strrev($subString);
                    $matches = null;
                    $success = preg_match ( '~>/[^<]* [MRX]<|>[MRX]/<~', 
                                            // '~(<[MRX] [^>]*/>|</[MRX]>)[\s\S]*?$~',
                                            $revSubStr, $matches , PREG_OFFSET_CAPTURE );

                    // Fehler abfangen => $success==0 !!

                    // the length we take for the current DM / "page"
                    $rPosition = $matches[0][1];
                    $usableLength = $maxLength - $rPosition;


                    // moves all later array items one place up:
                    for( $k=count($sectXmlArr); $k>$i+1; $k-- ) {
                        $sectXmlArr[$k] = $sectXmlArr[$k-1];
                    }
                    $sOpenTag = preg_replace( '~^\s*(<S[^>]*>)[\s\S]*$~', '$1' , $sectXml );

                    // Diese Ergänzung findet auf ausdrückliche mündliche Anweisung
                    // durch Herrn Zenz am 13.6.2017 statt:
                    if ( false === strpos( $sOpenTag, 't=' ) && false === strpos( $sOpenTag, 'c=' ) ) {
                        $sOpenTag = str_replace( '>', ' c="412">', $sOpenTag );
                    }

                    $sectXmlArr[$i+1] = $sOpenTag . substr( $sectXml, $usableLength );
                    $sectXml          = substr( $sectXml, 0, $usableLength ) . '</S>';
                    // not necessary: $sectXmlArr[$i]   = $sectXml;

                }

            }
            $currentXml .= $sectXml;
            $justStNP   = false;
        }
        unset( $currentXml );

        // add pagination and "</MP>" to each
        $view_xml_content="";
        $dmPageAmount = count( $dmXmlPerPage );
        for( $i=0; $i<$dmPageAmount; $i++ ) {
            if ( $dmPageAmount > 1 ) {
                $mpPagin = '<MP a="' . ($i+1) . '" z="' . $dmPageAmount . '"';
                $dmXmlPerPage[$i] = preg_replace( '~^<MP~', $mpPagin, $dmXmlPerPage[$i], 1);
            }
            $dmXmlPerPage[$i] .= '</MP>';
            $view_xml_content .= $dmXmlPerPage[$i];
            $dmXmlPerPage[$i]  = $barcodeService->convertUtf8Latin1( $dmXmlPerPage[$i] , $latin1ConvStrict );
        }
//         $options['view_xml_file'] = 1;
        if($options['view_xml_file'] == '1'){
            
            header('Content-Encoding: UTF-8');
            header('Content-type: text/plain; charset=utf-8; codepage="4110"');
            header("Content-Disposition: attachment; filename=Datamatrix.xml");
            echo $view_xml_content;
            exit;
        }

        //================================================+
        // ... eo Preparation of the DataMatrices
        //================================================+
        // Preparation of the printed output / Presets
        //================================================+

        $codeToSTitle = $this->keyFileToArray( 'KBV_BMP2_ZWISCHENUEBERSCHRIFT' );
        $codeToForm   = $this->keyFileToArray( 'KBV_BMP2_DARREICHUNGSFORM' );
        $codeToUnit   = $this->keyFileToArray( 'KBV_BMP2_DOSIEREINHEIT' );

        $this->doDebugLine('Start new document ...');
        $pdf = $pdfSrvc->startNewDocument( 'L', 'mm', 'A4', array() );

        $this->doDebugLine('(Start new document done)');

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Smart-Q GmbH');
        $pdf->SetTitle('Medikamentenplan');
        $pdf->SetSubject('');
        $pdf->SetKeywords('Medikamentenplan, Behandlung');

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 12, '', true);
        $pdf->setFooterFont( array( PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA ) );
        $pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins( 8.5, 51, 7);
        // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetHeaderMargin(8);
        $pdf->SetFooterMargin(18);

        // $pdf->setPrintHeader(false);

        // set auto page breaks ( TRUE|FALSE , ...mm (Space btw. Text & Footer) )
        $pdf->SetAutoPageBreak( FALSE, 0 );
        // set image scale factor
        $pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

        //what php version modules?
        $pdf->setHtmlVSpace(array( 
            // 'h1' => [0 => ['h' => 1, 'n' => 3], 1 => ['h' => 1, 'n' => 2]],
            'p' => array(0=>array('n'=>0), 1=>array('n'=>0) ),
            'h2' => array(0=>array('n'=>0), 1=>array('n'=>0) )
        ) );        

        // pre-register standard footer image :
        $pdf->registerLocalImage( 
            'smartq_footer_logo' , 
            dirname( __FILE__ ) . '/MediPlanData/smartq_logo_druck_200x100.png' 
        );

        // $options['image_reg'] - register other images (e.g. for the footer) ... :
        $regImages = array();
        $MediPlanDataFolder = dirname( __FILE__ ) . '/MediPlanData';
        if ( isset($options['image_reg']) && is_array($options['image_reg']) ) {
            foreach( $options['image_reg'] as $name => $value ) {
                $path = str_replace( '%MediPlanData%', $MediPlanDataFolder, $path );
                $pdf->registerLocalImage();
            }
        }

        // --- Header ... ---

        $pdf->setMulticellHeaderParamsDefaults( array( 
            'border'=>'LTBR',
            'h'=>40, 'maxh'=>40,
            'font_size'=>12,
            'padding'=> array( 1,0.5,1,0.5 ) 
        ) );


        $pdf->addHtmlHeader(
            '<h2 style="text-align: center; font-size: 20pt;">Medikationsplan&nbsp;&nbsp;&nbsp;</h2>'
            . '<p style="text-align: right;">Seite %->PageNo()% von %->getAliasNbPages()%&nbsp;</p>'
            , 
            array(
                'w'=>73.2,  // should actually be 68.5, but I had to bugfix an alignment issue
                'border'=>'LTB', 
                'font_size'=>13.8,
                'padding'=>array(1,0.5,0,0.5) ,
                'callback' => array($pdf,'replaceVarsCallback')
            )
        );


        // --- Parameter Block ... ---
        
        if ( isset($MPO['x']) ) {
            // free text:
            $str = $this->attribToClearText( $MPO['x'] );
            $paramBlock = explode( "\n", $str );
        }
        else {
            $paramBlock = array();
        }

        if ( isset($MPO['ai']) ) {
            $str = trim( $MPO['ai'] );
            $str = preg_replace( '~\\s+~', ' ', $MPO['ai'] );
            $str = preg_replace( '~\\s*([\\,\\;])\\s*~', '$1 ', $MPO['ai'] );
            $arr = explode( ' ', $str );
            if ( mb_strlen( $arr[0] ) < 13 ) {
                array_push( $paramBlock, 'Allerg./Unv.: ' . $arr[0] );
                array_shift( $arr );
            } else {
                array_push( $paramBlock, 'Allerg./Unv.:' );
            }
            
            if ( isset($arr[1]) && mb_strlen($arr[0]) + mb_strlen($arr[1]) < 25 ) {
                array_push( $paramBlock, $arr[0] . ' ' . $arr[1] );
                array_shift( $arr );
                array_shift( $arr );
            } else { 
                array_push( $paramBlock, mb_substr( $arr[0], 0, 25 ) );
                array_shift( $arr );
            }

            if ( isset( $arr[1] ) && mb_strlen($arr[0]) < 23 ) {
                array_push( $paramBlock, $arr[0] . ' ...' );
            }
            elseif ( isset( $arr[1] ) ) {
                array_push( $paramBlock, mb_substr( $arr[0], 0, 22 ) . ' ...' );
            }
            else {
                array_push( $paramBlock, mb_substr( $arr[0], 0, 25 ) );
            }
        }

        if ( isset($MPO['p']) && ! empty($MPO['p']) && isset($MPO['b']) && ! empty($MPO['b']) ) {
            array_push( $paramBlock, 'schwanger, stillend' );
        }
        elseif ( isset($MPO['p']) && ! empty($MPO['p'])) {
            array_push( $paramBlock, 'schwanger' );
        }
        elseif ( isset($MPO['b']) && ! empty($MPO['b']) ) {
            array_push( $paramBlock, 'stillend' );
        }

        if ( isset($MPO['c']) && ! empty($MPO['c']) ) {
            array_push( $paramBlock, 'Krea.: ' . substr( $MPO['c'], 0, 12 ) . ' mg/dl' );
        }

        if ( isset($MPO['w']) && ! empty($MPO['w']) ) {
            array_push( $paramBlock, 'Gewicht: ' . substr( $MPO['w'], 0, 12 ) . ' kg' );
        }

        if ( isset($MPO['h']) && ! empty($MPO['h']) ) {
            array_push( $paramBlock, 'Größe: ' . substr( $MPO['h'], 0, 12 ) . ' cm' );
        }

        $len = count($paramBlock);
        $paramString = '';
        for( $i=0; $i < $len && $i < 5 ; $i++ ) {
            $paramString .= ( $i ? '<br>' : '' ) . $paramBlock[$i];
        }

        // // add/prepend to view the table borders:
        // <style> td { border: 1px solid #eeeeee; } </style>
        //TODO-3136 Ancuta 13.05.2020 - changed the email line MPA - from               <td width="61%" style="font-size: 12pt;">E-Mail: {$MPA['e']}</td> to <td width="61%" style="font-size: {$MPAe_font};">E-Mail: {$MPAe}</td>
        $headerContent = <<<____________EOTXT
          <table width="100%">
            <tr>
              <td colspan="2" width="66%" style="font-size: 14pt; height: 12.2mm">für:
                  <b>{$MPP['g']} {$MPP['f']}</b>
              </td>
              <td  width="34%" align="right" style="font-size: 14pt;">geb. am: <b>{$MPPb}</b></td>
            </tr>
            <tr>
              <td colspan="2" width="66%">ausgedruckt von:</td>
              <td  width="34%" align="right" rowspan="4">
                 {$paramString}
              </td>
            </tr>
            <tr>
              <td colspan="2" width="66%">{$MPA['n']}</td>
            </tr>
            <tr>
              <td colspan="2" width="66%">{$MPAs}, {$MPA['z']} {$MPAc}</td>
            </tr>
            <tr>
              <td colspan="2" width="66%">Tel.: {$MPA['p']}</td>
            </tr>
            <tr>
              <td width="61%" style="font-size: {$MPAe_font};">E-Mail: {$MPAe}</td>
              <td width="39%" align="right" colspan="2" rowspan="2">
                ausgedruckt am: {$MPAt}</td>
            </tr>
          </table>
____________EOTXT;

        $headerContent = preg_replace( '~^ +~' , '' , $headerContent );

        $pdf->addHtmlHeader( $headerContent , array(
            'x'=>76.5, 'w'=>168.5, 'padding'=>array(1,0.5,1,0)
            // , 'callback' => [$pdf,'replaceVarsCallback']
        ) );


        // --- Footer ... ---


        $pdf->setMulticellFooterParamsDefaults( array( 
            'border'=>'T',
            'h'=>8.75, 'maxh'=>8.75,
            'font_size'=>9,
            'padding'=>array(1,0.5,1,0.5) 
        ) );

        $pdf->addMulticellFooter( 
            'Für Vollständigkeit und Aktualität des Medikationsplans wird keine Gewähr übernommen.'
            . "\n" . $options['language'] . ' Version ' . $options['version'] , array( 
            'w'=>120 ,
            'font_size'=>8,
            'padding'=>array(0,0.5,1,0.5)
            // , 'callback' => [$pdf,'replaceVarsCallback']
        ) );

        $footConsuW = 0;

        if ( ! empty( $options['footer_logo'] ) ) {

            $options['footer_logo'] = array_merge(
                array('border'=>'T') ,
                $options['footer_logo']
            );

            // //  $tempDir = $pdf->getStdTempPath();
            $pdf->addFooterImage( $options['footer_logo']['file'] , $options['footer_logo'] );

            // getimagesize ( $filename );


            if( isset( $options['footer_logo']['width'] ) ) {
                $footConsuW += $options['footer_logo']['width'];
            } 
            elseif ( isset( $options['footer_logo']['w'] ) ) {
                $footConsuW += $options['footer_logo']['w'];
            }
            else {
                $footConsuW += 20;
            }
        }


        if ( isset( $options['footer_html'] ) ) {
            $pdf->addHtmlFooter(
                $options['footer_html'] , array( 
                'w' => 110 - $footConsuW
            ) );
        }
        elseif ( isset( $options['footer'] ) ) {
            $pdf->addMulticellFooter( 
                $options['footer'] , array( 
                'w' => 110 - $footConsuW
            ) );
        }
        else {
            $pdf->addMulticellFooter( 
                "SMART-Q Softwaresysteme GmbH | www.smart-q.de\nUniversitätsstraße 136 (BMZ), 44799 Bochum" , array( 
                'w' => 110 - $footConsuW
            ) );
        }
        

        $pdf->addHtmlFooter(
            $errorMsg , array( 
            'font_size' => 7,
            'w' => 50
        ) );
        
        // ... eo footer



        //================================================+
        // Composition
        //================================================+

        // for the "time of ingestion" columns:
        $baseParTI = array( 'w'=>8, 'align'=>'C', 'padding'=>array(0,0.5,0,0.5) );

        $dataMatrixNum = -1;
        $mrxOffset = 0;
        $sectionOffset = 0;
        $skipMedicationLoop = false;

        do { // start a new page ...

            $this->doDebugLine("* * * *  starting new page  * * * *" );

            $pdf->AddPage();
            $dataMatrixNum++;

            // ========= DATAMATRIX output - start ===================

            $resetX  = $pdf->GetX();
            $resetY  = $pdf->GetY();

            if ( isset($dmXmlPerPage[$dataMatrixNum]) ) {

                $this->doDebugLine( "(generating DataMatrix - this may take some seconds ... )" );

                $matrixContent = $dmXmlPerPage[$dataMatrixNum];

                $this->doDebugLine( $matrixContent );
                
                $style = array(
                    'border' => 0,
                    'vpadding' => 0,
                    'hpadding' => 0,
                    'fgcolor' => array( 0, 0, 0 ),
                    'bgcolor' => array( 255, 255, 255 ) , // false, //array(255,255,255)
                    'module_width' => 1, // width of a single module in points
                    'module_height' => 1 // height of a single module in points
                );
                $pdf->write2DBarcode( $matrixContent, 'DATAMATRIX', 248, 8, 40, 40, $style, 'N');

            } 
            else {
                // #ToDo ...
            }

            $pdf->SetX( $resetX );
            $pdf->SetY( $resetY );

            // ========= DATAMATRIX output - end ===================


            // ========= Medication Data Table - Start ===================


            $pdf->setMulticellParamsDefaults( array( 
                'border'=>'LTBR', 'fill'=>true,
                'w'=>18,
                'h'=>8.75, 'maxh'=>8.75,
                'align'=>'L', 'valign'=>'M',
                'padding'=>array(1,0.5,0.5,0.5)
            ) );

            // bold font for the header ... :
            $pdf->SetFont('dejavusans', 'B', 12, '', true);

            // set color for background
            $pdf->SetFillColor( 208, 208, 208 );

            $pdf->writeMulticellNP( 'Wirkstoff'   , array( 'w'=>41 ) );
            $pdf->writeMulticellNP( 'Handelsname' , array( 'w'=>44 ) );
            $pdf->writeMulticellNP( 'Stärke'      );
            $pdf->writeMulticellNP( 'Form'        );

            // set font for small header cells:
            $pdf->SetFont('dejavusans', '', 6, '', true);

            $pdf->writeMulticellNP( 'mor-gens'  , $baseParTI );
            $pdf->writeMulticellNP( 'mit-tags'  , $baseParTI );
            $pdf->writeMulticellNP( 'abends'    , $baseParTI );
            $pdf->writeMulticellNP( 'zur Nacht' , $baseParTI );
            // for future reference: insert an image (e.g. from "temp" folder) ... :
            // $tempDir = $pdf->getStdTempPath();
            // $pdf->writeImageFileNP( $tempDir.'time_of_ingestion_header.png' , [ 'w'=>32 , 'border'=>1 ] );

            // reset bold font for the rest of the header ... :
            $pdf->SetFont('dejavusans', 'B', 12, '', true);

            $pdf->writeMulticellNP( 'Einheit'  , array( 'w'=>20 ) );
            $pdf->writeMulticellNP( 'Hinweise' , array( 'w'=>64 ) );
            $pdf->writeMulticellNP( 'Grund'    , array( 'w'=>43, 'br'=>1 ) );


            // ----- Body ... -----

            // ! MAXIMAL 15 Zeilen pro Seite ... !

            // ACHTUNG ToDo - Kombinationsarzneimittel mit 3 Wirkstoffen - Zeilenhöhe 17,5 mm (2x8.75) ... :
            $pdf->setMulticellParamsDefaults( array( 
                'fit_cell' => true ,
                'border'=>'LTBR',
                'w'=>18,
                'h'=>8.75, 'maxh'=>8.75,
                'align'=>'L', 'valign'=>'M',
                'padding'=>array(1,0.5,0.5,0.5)
            ) );

            // reset font:
            $pdf->SetFont('dejavusans', '', 12, '', true);

            $i=-1;
            $pageRowsCount = 0;
            $startNewPage = false;
            $mrxCount = 0;

            $this->doDebugLine('(last point before big loop)');

            if ( $skipMedicationLoop ) {

                $pdf->writeMulticellNP( '(Technisch bedingtes Blatt)' , array( 'w'=>280, 'br'=>1, 'border'=>'' ) );

            } 
            else foreach ( $MP_node->childNodes as $sectionNode ) {

                // skip <P>, <A> and <O> ...
                if ( $sectionNode->nodeType !== XML_ELEMENT_NODE || 'S' !== $sectionNode->nodeName ) {
                    continue;
                }

                // --- control page breaks on basis of sections ... ---

                $i++;

                if ( $i < $sectionOffset  ) {
                    continue;
                }

                $this->doDebugLoop( '=== Start section ===' , $i , $mrxCount );

                $sectionOffset = $i;

                if( ! ( 0 == $pageRowsCount && $rowsPerSecRest[$i] > $default_rows_per_page  ) ) {
                // ... if we start at the top and section is longer than 15 rows the page
                // break is managed by the inner loop (that handles <M>/<R>/<X>-nodes).
                    if( 0 == $mrxOffset  &&  $pageRowsCount + $rowsPerSecRest[$i] > $default_rows_per_page ) {
                    // ... if the inner loop did not take over the page break control (== no "mrx" 
                    // offset) and the section would be too big for the rest of the current page: 
                    // break it ... except if $options['eco_page_break'] is true and there are 
                    // at least 3 lines left on the page.
                        if ( ! $options['eco_page_break']  ||  ( $default_rows_per_page - $pageRowsCount ) < 3 ) {
                            $this->doDebugLine( '  (initiating new page at section level)' );
                            $startNewPage = true;
                            break;
                        } 
                    }
                }

                // --- ... eo control page breaks by sections ---

                // --- section title ... ---   $options['repeat_title']

                if( 0 == $mrxOffset || $options['repeat_title'] ) {
                    // ... add the title only if we do not continue a section
                    // from a previous page or if we forced to do so

                    $STitle = null;
                    $STitleCode = trim( $sectionNode->getAttribute('c') );
                    $STitleText = trim( $sectionNode->getAttribute('t') );
                    if ( strlen( $STitleCode )  &&  '' === $STitleText ) {
                        if ( isset( $codeToSTitle[$STitleCode] ) ) {
                            $STitle = $codeToSTitle[$STitleCode];
                        }
                        else {
                            $STitle = $STitleCode;
                        }
                    } 
                    else {
                        $STitle = $sectionNode->getAttribute('t');
                    }

                    if ( strlen($STitle) ) {
                        $text = $this->attribToClearText( $STitle );
                        if( 0 == $mrxOffset ) {
                            // this is the regular title at the top/beginning of the section
                            $pdf->writeMulticellNP( $text , array( 'w'=>280, 'br'=>1, 'border'=>'', 'font_style'=>'B' ) );
                            $rowsPerSecRest[$i]--;
                            $this->doDebugLine( '  (Section title: ' . $text . ' )' );
                        }
                        else {
                            // this is a repeated title at the top of a page
                            // (we only get here if $options['repeat_title'] is true)
                            $pdf->writeMulticellNP( $text . ' (Forts.)' , array( 'w'=>280, 'br'=>1, 'border'=>'', 'font_color'=>array('196') ) );
                            $this->doDebugLine( '  (Repeated section title: ' . $text . ' (Forts.)' . ' )' );
                        }
                        $pageRowsCount++;
                        
                    }

                }

                // ... eo section title 

                // $mrxOffset

                $mrxCount = 0;
                $nextMrx  = null;
                foreach ( $sectionNode->childNodes as $mrx ) { // => <M>, <X> or <R> node

                    if ( $mrx->nodeType !== XML_ELEMENT_NODE ) {
                        continue;
                    }

                    $mrxCount++;

                    if ( $mrxCount < $mrxOffset  ) {
                        continue;
                    }

                    $this->doDebugLoop( '  - Start Row '.$mrx->nodeName.' -' , $i , $mrxCount );

                    $nextMrx = $mrx;

                    switch ( $mrx->nodeName ) {

                        case 'X': // <X ... />
                            if ( $pageRowsCount + 1 > $default_rows_per_page ) {
                                $delta = $mrxCount - $mrxOffset;
                                $rowsPerSecRest[$i] -= $delta;
                                $rowsPerSecRest[$i]++; // (the current row will be repeated)
                                $mrxOffset = $mrxCount;
                                $this->doDebugLine( '  (initiating new page at row level, node <X> )' );
                                $startNewPage = true;
                                break;
                            } 
                            else {
                                $text = $this->attribToClearText( $mrx->getAttribute('t') );
                                $pdf->writeMulticellNP( $text , array( 'w'=>280, 'br'=>1, 'border'=>'' ) );
                                $pageRowsCount++;
                                $nextMrx = $mrx->nextSibling;
                                break;
                            }
                            

                        case 'R': // <R ... />
                            if ( $pageRowsCount + 1 > $default_rows_per_page ) {
                                $delta = $mrxCount - $mrxOffset;
                                $rowsPerSecRest[$i] -= $delta;
                                $rowsPerSecRest[$i]++; // (the current row will be repeated)
                                $mrxOffset = $mrxCount;
                                $this->doDebugLine( '  (initiating new page at row level, node <R> )' );
                                $startNewPage = true;
                                break;
                            } 
                            else {
                                $text = $this->attribToClearText( $mrx->getAttribute('t') );
                                $pdf->writeMulticellNP( $text , array( 'w'=>280, 'br'=>1 ) );
                                $pageRowsCount++;
                                $nextMrx = $mrx->nextSibling;
                                break;
                            }

                        default: // <M ... >(<W ... >)</M>

                            // reset output variables ...
                            $substances    = '';
                            $tradeName     = '';
                            $concentration = '';
                            $dosageForm    = '';
                            $tiFreeText    = '';
                            $tiMorning     = '';
                            $tiLunch       = '';
                            $tiEvening     = '';
                            $tiNight       = '';
                            $ingestUnit    = '';
                            $information   = '';
                            $reason        = '';                        

                            $subCount = 0; // "substances counter"

                            $pzn = ltrim( rtrim( $mrx->getAttribute('p') ) , ' 0' ); // " 00246 " => "246"

                            $substanceArr = array();
                            $concentArr = array();

                            if ( strlen( $pzn ) && isset( $pznData[$pzn] )) {
                                $data = $pznData[$pzn];
                                $tradeName    = trim( $data['name'] );
                                $substanceArr = $data['substances'];
                                $concentArr   = $data['concentrations'];
                                $subCount     = count( $substanceArr );
                            } 

                            $str = trim( $mrx->getAttribute('a') );
                            if ( strlen( $str ) ) {
                                $tradeName = trim( $this->attribToClearText( $str ) );
                            }

                            if ( strlen( $tradeName ) > 50 ) {
                                $tradeName = mb_substr ( $tradeName, 0, 48, 'UTF-8' ) . '...';
                            }

                            $substList = $mrx->getElementsByTagName('W');
                            if ( $substList->length ) {
                                $substanceArr = array();
                                $concentArr = array();
                                foreach ( $mrx->childNodes as $w ) {
                                    if ( 'W' === $w->nodeName ) {
                                        $subCount++;
                                        $str = trim( $w->getAttribute('w') );
                                        $str = $this->attribToClearText( $str );
                                        $str = str_replace( ' ', $nbsp, $str );
                                        array_push( $substanceArr, $str );
                                        $str = trim( $w->getAttribute('s') );
                                        $str = $this->attribToClearText( $str );
                                        $str = str_replace( ' ', $nbsp, $str );
                                        array_push( $concentArr, $str );
                                    }
                                }
                                unset($w);
                            }

                            foreach ( $substanceArr as &$str ) {
                                if ( strlen( $str ) > 80 ) {
                                    $str = mb_substr ( $str, 0, 78, 'UTF-8' ) . '...';
                                }
                            }
                            unset($str);
                            foreach ( $concentArr as &$str ) {
                                if ( strlen( $str ) > 11 ) {
                                    $str = mb_substr ( $str, 0, 11, 'UTF-8' );
                                }
                            }
                            unset($str);

                            if( count($substanceArr) > 3 ) {
                                $substances    = 'Kombi-Präp.';
                                $concentration = '';
                            } 
                            else {
                                $substances    = implode( "\n", $substanceArr );
                                $concentration = implode( "\n", $concentArr );
                            }

                            // ATTENTION WHEN CHANGING LINE CONSUMPTION: do also adapt the
                            // "foreach" after comment "note down the rows per section ..."!

                            $lines = 1;
                            if( $subCount === 3 ) { // 4+ => "Kombi-Präp."
                                $lines = 2;
                                $height = array( 'h'=>17.5, 'maxh'=>17.5 );
                            }
                            else {
                                $height = array();
                            }

                            if ( $pageRowsCount + $lines > 15 ) {
                                $delta = $mrxCount - $mrxOffset;
                                $rowsPerSecRest[$i] -= $delta;
                                $rowsPerSecRest[$i]++; // (the current row will be repeated)
                                $mrxOffset = $mrxCount;
                                $this->doDebugLine( '  (initiating new page at row level, node <M> / ' . $tradeName . ' )' );
                                $startNewPage = true;
                                break;
                            } 
                            else {

                                $str = trim( $mrx->getAttribute('fd') );
                                if ( strlen($str) ) {
                                    $dosageForm = $this->attribToClearText( $str );
                                }
                                else {
                                    $str = trim( $mrx->getAttribute('f') );
                                    if ( strlen($str) ) {
                                        $dosageForm = isset( $codeToForm[$str] ) ? $codeToForm[$str] : '('.$str.')';
                                    }
                                    else {
                                        // ToDo - this should be found in the PZN data ...
                                        $dosageForm = '';
                                    }
                                }

                                if ( strlen( $dosageForm ) > 7 ) {
                                    $dosageForm = mb_substr ( $dosageForm, 0, 7, 'UTF-8' );
                                }
                                
                                // "ti" - time of ingestion ...
                                $str = trim( $mrx->getAttribute('t') );
                                if ( strlen($str) ) {
                                    $tiFreeText = $this->attribToClearText( $str );
                                } 
                                else {
                                    $tiFreeText = null;
                                    $str = trim( $mrx->getAttribute('m') );
                                    $tiMorning  = strlen($str) ? $str : '0';
                                    $str = trim( $mrx->getAttribute('d') );
                                    $tiLunch    = strlen($str) ? $str : '0';
                                    $str = trim( $mrx->getAttribute('v') );
                                    $tiEvening  = strlen($str) ? $str : '0';
                                    $str = trim( $mrx->getAttribute('h') );
                                    $tiNight    = strlen($str) ? $str : '0';
                                }

                                $str = trim( $mrx->getAttribute('dud') );
                                if ( strlen($str) ) {
                                    $ingestUnit = $this->attribToClearText( $str );
                                } 
                                else {
                                    $str = trim( $mrx->getAttribute('du') );
                                    if ( strlen($str) ) {
                                        $ingestUnit = isset( $codeToUnit[$str] ) ? $codeToUnit[$str] : $str;
                                    }
                                    else {
                                        $ingestUnit = '';
                                    }
                                }

                                $str = trim( $mrx->getAttribute('i') );
                                if ( strlen($str) ) {
                                    //$information = $this->attribToClearText( $str );
                                    $information = $this->attribToClearTextEntityDecode( $str );  //TODO-2999 Lore 18.03.2020
                                } 
                                else {
                                    $information = '';
                                }

                                $str = trim( $mrx->getAttribute('r') );
                                if ( strlen($str) ) {
                                    //$reason = $this->attribToClearText( $str );
                                    $reason = $this->attribToClearTextEntityDecode( $str );   //TODO-2999 Lore 18.03.2020
                                } 
                                else {
                                    $reason = '';
                                }

                                $pdf->writeMulticellNP( $substances      , array( 'w'=>41 ) , $height );
                                $pdf->writeMulticellNP( $tradeName       , array( 'w'=>44 ) , $height );
                                $pdf->writeMulticellNP( $concentration   , array( 'align'=>'R' ) , $height );
                                $pdf->writeMulticellNP( $dosageForm      , $height );
                                if ( $tiFreeText ) {
                                    $pdf->writeMulticellNP( $tiFreeText   , array( 'w'=>32 ) , $height );
                                } 
                                else {
                                    $pdf->writeMulticellNP( $tiMorning   , $baseParTI , $height );
                                    $pdf->writeMulticellNP( $tiLunch     , $baseParTI , $height );
                                    $pdf->writeMulticellNP( $tiEvening   , $baseParTI , $height );
                                    $pdf->writeMulticellNP( $tiNight     , $baseParTI , $height );
                                }
                                
                                $pdf->writeMulticellNP( $ingestUnit      , array( 'w'=>20 ) , $height );
                                $pdf->writeMulticellNP( $information     , array( 'w'=>64 ) , $height );
                                $pdf->writeMulticellNP( $reason          , array( 'w'=>43, 'br'=>1 ) , $height );

                                $pageRowsCount += $lines;

                                $nextMrx = $mrx->nextSibling;

                                break;

                            }

                    } // eo switch ( $mrx->nodeName )

                    if ( ! $nextMrx ) {
                        $this->doDebugLine( "\n  NO MRX LEFT - "
                                    . 'resetting $mrxOffset and $rowsPerSecRest['.$i."] to 0 !\n" );
                        $mrxOffset = 0;
                        $rowsPerSecRest[$i] = 0;
                    }

                    if ( $startNewPage ) {
                        break;
                    }

                } // eo foreach ( $sectionNode->childNodes as $mrx ) // $mrxCount

                if ( $startNewPage ) {
                    break;
                }

            } // eo foreach ( $MP_node->childNodes as $sectionNode ) // $i

            // add an empty page for an extra DataMatrix ...
            if ( ! $startNewPage  &&  isset($dmXmlPerPage[$dataMatrixNum+1])) {
                $startNewPage = true;
                $skipMedicationLoop = true;
            }

        } while ( $startNewPage );

        //============================================================+
        // END OF PDF COMPOSITION PART
        //============================================================+

        // remove options from the options list that are not necessary for OutputEasy:
        unset( $options['dom'] , $options['footer'] , $options['footer_html'] , $options['language'] );

        mb_internal_encoding( $origMbInternalEnc );
        
        $pathToFTP = $pdf->toFTP($fileName);
        
        $pathToFile = $pdf->OutputEasy( $fileName , $options );

        $result =  array(
        		"pathToFTP"=>$pathToFTP, 
        		"pathToFile"=>$pathToFile);
        // echo "\n\nMEMORY: " . memory_get_usage(true) . "\n\n";

        return $result;

    }



    /**
     * Fetches data for medical products by an array/list of PZNs
     *
     * ########################################################
     * ##  ToDo: this could use a good caching mechanism !!  ##
     * ########################################################
     *
     * ATTENTION: the PZNs are translated into
     *
     * Result example for $this->getPznData( ['11305464','1566347'] ):
     *   [
     *     'status' => 'ok',
     *     'error' => '',
     *     'products' => [
     *       11305464 => [
     *         'name' => 'FOSTER® NEXThaler® 200 Mikrogramm/6 Mikrogramm pro Dosis Pulver zur Inhalation',
     *         'substances' => [
     *           0 => 'Beclometason dipropionat',
     *           1 => 'Formoterol hemifumarat-1-Wasser'
     *         ],
     *         'concentrations' => [
     *           0 => '200 µg',
     *           1 => '6 µg'
     *         ],
     *         'pzn_list' => [
     *           0 => '11305464',
     *           1 => '11305470'
     *         ]
     *       ],
     *       1566347 => [
     *         'name' => 'KALINOR® 1,56g Kalium/2,5g Citrat Brausetabletten',
     *         'substances' => [
     *           0 => 'Kaliumcitrat-1-Wasser',
     *           1 => 'Kaliumhydrogencarbonat'
     *         ],
     *         'concentrations' => [
     *           0 => '2.17 g',
     *           1 => '2 g'
     *         ],
     *         'pzn_list' => [
     *           0 => '2135106',
     *           1 => '1566347',
     *           2 => '7515598',
     *           3 => '1566353'
     *         ]
     *       ]
     *     ]
     *   ]
     *
     *
     * @param  array|string  $pznList  [description]
     * @return [type]        [description]
     */
    public function getPznData( $pznList ) {
    	
    	if ( empty($pznList)) {
    		return;
    	}
    	
    	$url = "http://" . Zend_Registry::get('mmilicserver') . "/rest/pharmindexv2";
    	
    	$function = 'getProducts';
    	$licensekey = Zend_Registry::get('mmilicserial');
    	$licensename = Zend_Registry::get('mmilicname');
    	    	
    	$url = $url . '/' . $function . '/' . $licensekey . '/' . $licensename . '/';

    	$curl = curl_init();
    
//     	$url= "http://dev.smart-q.de:7779/rest/pharmindexv2/getProducts/9F95-6JMS-KAUZ-LFCM/SMARTQ05122014/";
    
    	$params = array();
    
    	if ( is_string( $pznList ) ) {
    		$pznList = explode( ',', $pznList );
    	}
    
    	foreach ( $pznList as &$str ) {
    		$str =  ltrim( rtrim( (string) $str ) , ' 0' );
    	}
    	
    	unset($str);
    
    	$params['pzn_orlist'] = $pznList;
    
    	$params = urlencode( json_encode($params) );
    	$params = str_replace( '+', '%20', $params ); // ... just in case
    
    	$url .= $params;
    
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	$resultJson = curl_exec($curl);
    	curl_close($curl);
    
    	if ( empty( $resultJson ) ) {
    		$products = array();
    		foreach ( $pznList as $pzn ) {
    			$products[ $pzn ] = array(
    					'name' => '(PZN: ' . $pzn . ' )' ,
    					'substances'     => array() ,
    					'concentrations' => array()
    			);
    		}
    		return array(
    				'status'   => 'error' ,
    				'error'    => 'empty result' ,
    				'products' => $products
    		);
    	}
    
    	$dataArr = json_decode( $resultJson , true );
    
    	if ( 0 != $dataArr['STATUS']['code'] ) {
    		$products = array();
    		foreach ( $pznList as $pzn ) {
    			$products[ $pzn ] = array(
    					'name' => '(PZN: ' . $pzn . ' )' ,
    					'substances'     => array() ,
    					'concentrations' => array()
    			);
    		}
    		return array(
    				'status'  => 'error' ,
    				'error'   => $dataArr['STATUS']['message'] ,
    				'products' => $products
    		);
    	}
    
    	$products = array();
    	$prodLength = count( $dataArr['PRODUCT'] );
    
    	//TODO-3263 Ancuta 08.07.2020  comented the following code, as data should be returned - at least for the ones that were found  
    	/* if ( count($pznList) !== $prodLength ) {
    		$products = array();
    		foreach ( $pznList as $pzn ) {
    			$products[ $pzn ] = array(
    					'name' => '(PZN: ' . $pzn . ' )' ,
    					'substances'     => array() ,
    					'concentrations' => array()
    			);
    		}
    		return array(
    				'status'  => 'error' ,
    				'error'   => 'One or more committed PZN numbers are invalid.' ,
    				'products' => $products
    		);
    	} */
        // --
    	
    	$pznListRest = $pznList;
    
    	for ( $p=0; $p<$prodLength; $p++ ) {
    
    		$item = $dataArr['PRODUCT'][$p];
    
    		$prod= array(
    				'name' => $item['NAME'] ,
    				'id' => $item['ID'],
    				'substances'     => array() ,
    				'concentrations' => array()
    		);
    
    		$ingList = $item['ITEM_LIST'][0]['COMPOSITIONELEMENTS_LIST'];
    
    		$len = (int) $item['ACTIVESUBSTANCE_COUNT'];
    		for( $i=0 ; $i<$len ; $i++ ) {
    
    			$ing = $ingList[$i];
    
    			array_push( $prod['substances'] , $ing['MOLECULENAME'] );
    
    			$unitRaw = $ing['MOLECULEUNITCODE'];
    			switch ( $unitRaw ) {
    				case 'MCG': $unit = 'µg'; break;
    				default: $unit = strtolower( $unitRaw );  break;
    			}
    
    			if ( empty( $ing['MASSTO'] ) ) {
    				array_push( $prod['concentrations'] , $ing['MASSFROM'].' '.$unit );
    			}
    			else {
    				$str = $ing['MASSFROM'] . '-' . $ing['MASSTO'];
    				array_push( $prod['concentrations'] , $str . $unit );
    			}
    
    		}
    
    		$packagePzns = array();
    		foreach ( $item['PACKAGE_LIST'] as $package ) {
    			$str = ltrim( rtrim( (string) $package['PZN'] ) , ' 0' );
    			array_push( $packagePzns, $str );
    		}
    		$prod['pzn_list'] = $packagePzns;
    
    		$arr = array_intersect( $packagePzns , $pznListRest );
    		if ( count($arr) ) {
    			$pzn = current($arr);
    			unset( $pznListRest[ array_search( $pzn, $pznListRest ) ]);
    		}
    		else {
    			$pzn = $packagePzns[0];
    		}
    
    		$products[ $pzn ] = $prod;
    
    	}
    
    	//TODO-3263 Ancuta 08.07.2020 - fill data for the pzn that did not return a product
    	foreach($pznList as $pznn){
    	    if(!array_key_exists($pznn, $products)){
    	        $products[$pznn]['name'] = '(PZN: ' . $pznn . ' )' ;
    	        $products[$pznn]['id'] = "";
    	        $products[$pznn]['substances'] = array();
    	        $products[$pznn]['concentrations'] = array();
    	        $products[$pznn]['pzn_list'] = array('');
    	    }
    	}
    	//
    	
    	
    	
    	$out = array(
    			'status'  => 'ok' ,
    			'error'   => '' ,
    			'products' => $products
    	);

    	return $out;
    
    }
    
}