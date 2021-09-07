<?php

// namespace SmartqStandalone;

// # use Symfony\Component\HttpKernel\Bundle\Bundle;
// use BG\Barcode\Base1DBarcode;
// use BG\Barcode\Base2DBarcode;

class Pms_DeKbv_BarcodeService
{

    /**
     * Generate a Barcode based on a text (or string of numbers where applicable)
     *
     * ... uses the "Bitgrave Barcode Generator" (see folder 3rdParty/BGBarcodeGenModified)
     * to generate bar codes. See 3rdParty/SMQ_README.md and documentation coming with the
     * API.
     *
     * Options:
     *   "w"                : width of the resulting image
     *   "h"                : height of the resulting image
     *   "color"            : pencil color of the resulting image
     *   "jpg"              : (boolean) return JPG instead of PNG (not yet tested)
     *   "secure_utf8_conv" : (boolean) use a more secure conversion from UTF-8 
     *                        to ANSI where applicable
     *   "disable_utf8_conv": prevent any conversion from UTF-8 to ANSI
     *   "auto_abbr"        : crop / abbreviate the committed text if necessary 
     *                        (instead of throwing an Exception)
     *   "vertical"         : rotate (1D-)Barcodes by 90 degree
     * 
     * @param  string  $text     text to be encoded
     * @param  string  $mode     'datamatrix', "qrcode", "c39", "ean8", "postnet", ...
     * @param  array   $options  [description]
     * @return string  path to the resulting barcode picture file
     * @throws \Exception        Something went wrong
     */
//     public function textToBarcode( $text = '', $mode = 'datamatrix' , $options = array() ) {

//         $basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;

//         // target path for generated images:
//         if ( isset($options['use_folder']) ) {
//             $tmpPath  = rtrim( $options['use_folder'] , '/\\' ) . DIRECTORY_SEPARATOR;
//         } 
//         else {
//             $tmpPath  = $basePath . 'temp' . DIRECTORY_SEPARATOR;
//         }

//         $convertToJpg = isset($options['jpg']) ? (bool) $options['jpg'] : false;
//         $secureMode   = isset($options['secure_utf8_conv']) ? (bool) $options['secure_utf8_conv'] : false;
//         $disableUtf8Conv = isset($options['disable_utf8_conv']) ? (bool) $options['disable_utf8_conv'] : false;
//         $autoAbbreviate  = isset($options['auto_abbr']) ? (bool) $options['auto_abbr'] : false;

//         $width   = isset($options['w']) ? (float) $options['w'] : 0;
//         $height  = isset($options['h']) ? (float) $options['h'] : 0;
//         $fgColor = isset($options['color']) && is_array($options['color']) ? $options['color'] : [0,0,0];

//         // UID is used to avoid file name conflicts
//         $uidPool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
//         $rndUid  = '';
//         for( $i=0; $i<7 ; $i++ ) {
//             $rnd = mt_rand( 0, 61 );
//             $rndUid .= $uidPool[$rnd];
//         }

//         // Mode May be 'datamatrix', "qrcode", "c39", "ean8", "postnet" etc.:
//         $modeLc = strtolower( $mode );

//         // $isUtf8 = mb_detect_encoding( $text, 'UTF-8', true ) === 'UTF-8';

//         $needsIso8859Conversion = in_array( $modeLc, array(  
//             'datamatrix'
//             // ToDo ... ?
//         ) );

//         $text = trim( $text );

//         if ( ( $needsIso8859Conversion || $secureMode )  &&  ! $disableUtf8Conv ) {
//             $text = $this->convertUtf8Latin1( $text , $secureMode );
//         }

//         switch ( $modeLc ) {
//             case 'datamatrix': $maxLenAll = 1310; break;
//             // ToDo ...
//             default: $maxLenAll = 0; break;
//         }

//         if ( $maxLenAll && strlen($text) > 1310 ) {

//             $maxLen = $maxLenAll - 4;

//             if ( $autoAbbreviate ) {
//                 $text = trim( substr( $text, 0, $maxLen ) );
//                 $i=0;
//                 while ( $i <= 100 && in_array( $text[ $maxLen - 1 - $i ] , array(' ',"\n","\r") ) ) {
//                     $i++;
//                 }
//                 if ( $i ) {
//                     $text = substr( $text, 0, $i ) . ' ...';
//                 }
//             } 
//             else {
//                 throw new Exception( __METHOD__ . ' : text too long ('
//                                       . strlen($text) . ' characters).' );
//             }

//         } 

//         $pngPath = "";
        
//         if ( in_array( $modeLc, array( 'datamatrix' , 'pdf417' , 'qrcode' , 'RAW' , 'RAW2' ) ) ) {

//             $width  = $width ? $width : 4;
//             $height = $height ? $height : 4;

      
            
// //             require_once( $basePath . '3rdParty/BGBarcodeGenModified/Base2DBarcode.php');

// //             $fileName = 'Barcode2D_' . date( 'YmdHis' ) . '_' . $rndUid;
            
// //             $myDataMatrix = new Base2DBarcode();
// //             $myDataMatrix->savePath = $tmpPath;

// //             $pngPath = $myDataMatrix->getBarcodeFilenameFromGenPath( $myDataMatrix->getBarcodePNGPath( $text, $mode , $fileName , $width , $height , $fgColor ) ); //  

//             //to be changed to TCPDF2DBarcode
//             /*
//              // include 2D barcode class (search for installation path)
//              require_once(dirname(__FILE__).'/tcpdf_barcodes_2d_include.php');
//              // set the barcode content and type
//              $barcodeobj = new TCPDF2DBarcode('http://www.tcpdf.org', $modeLc);
//              // output the barcode as SVG image
//              $barcodeobj->getBarcodeSVG(6, 6, 'black');
//              */
//         } 
//         else {

//             $width  = $width ? $width : 2;
//             $height = $height ? $height : 30;
//             $vertical = isset($options['vertical']) ? (bool) $options['vertical'] : false;

// //             require_once( $basePath . '3rdParty/BGBarcodeGenModified/Base1DBarcode.php');

// //             $fileName = 'Barcode1D_' . date( 'YmdHis' ) . '_' . $rndUid;
            
// //             $myDataMatrix = new Base1DBarcode();
// //             $myDataMatrix->savePath = $tmpPath;

// //             $pngPath = $myDataMatrix->getBarcodeFilenameFromGenPath( $myDataMatrix->getBarcodePNGPath( $text, $mode , $fileName , $width , $height , $fgColor , $vertical ) );

//             //to be changed to TCPDFBarcode
//         }
        
//         if ( $convertToJpg && is_file($pngPath) ) {
//             // needs GD-lib ...
//             // $jpgPath = dirname( $pngPath ) . DIRECTORY_SEPARATOR . $fileName . '.jpg';
//             $jpgPath = preg_replace( '~\\.[Pp][Nn][Gg]~', '.jpg', $pngPath ) ;
//             $image = @imagecreatefrompng( $pngPath );
//             imagejpeg( $image, $jpgPath, 90 );
//             imagedestroy( $image );
//             unlink( $pngPath );
//             return $jpgPath;
//         } 
//         else {
//             return $pngPath;
//         }

//     }


    /**
     * @claudiu - this is a FMA association table ?
     */
    public function convertUtf8Latin1( $text = '' , $secureMode = false ) {

        if ( mb_detect_encoding( $text, 'UTF-8', true ) === 'UTF-8' ) {

            $text = str_replace( array('—', '–') , '-', $text );
            $text = str_replace( array('⅓',  '⅔',  '⅛',  '⅜',  '⅝',  '⅞'  ) , 
                                 array('1/3','2/3','1/8','3/8','5/8','7/8') , 
                                 $text );

            if ( $secureMode || ! function_exists('mb_convert_encoding') ) {
                $text = str_replace(
                    array( 'Ă','ă','Č','č','Ď','ď','Ĕ','ĕ','Ğ','ğ','Ĳ', 'ĳ', 
                      'Ň','ň','Ř','ř','Š','š','Ť','ť','Ů','ů','Ž','ž',
                      'Œ', 'œ', 'Ş','ş','Ș','ș','Ț','ț','đ',
                      '„', '“', '”', '‚', '‘', '’', '•', 
                      '†', '‰',     '€' ) ,
                    array( 'A','a','C','c','D','d','E','e','G','g','Ij','ij',
                      'N','n','R','r','S','s','T','t','U','u','Z','z',
                      'Oe','oe','S','s','S','s','T','t','ð',
                      '"', '"', '"', "'", "'", "'", '-', 
                      '+', '/1000', 'EUR' ) ,
                    $text
                );
                $text = utf8_decode( $text );
            } 
            else {
                $text = str_replace(
                    array( 'Ă','ă','Č','č','Ď','ď','Ĕ','ĕ','Ğ','ğ','Ĳ', 'ĳ', 
                      'Ň','ň','Ř','ř','Ť','ť','Ů','ů','Ş','ş','Ș','ș',
                      'Ț','ț','đ' ) ,
                    array( 'A','a','C','c','D','d','E','e','G','g','Ij','ij',
                      'N','n','R','r','T','t','U','u','S','s','S','s',
                      'T','t','ð' ) ,
                    $text
                );
                $text = mb_convert_encoding( $text , 'Windows-1252' , 'UTF-8' );
            }

        }

        return $text;

    }


    protected function sanitize ( $text ) {
    	
            if ( is_string($text) ) {
                $text = htmlspecialchars( (string) $text );
                $text = str_replace( '~', '&#x7E;', $text );
                $text = str_replace( array("\r\n", "\n\r"), '~', $text );
                return str_replace( "\n", '~', $text );
            }
            else {
                return (string) $text;
            }
            
    }// eo function $sanitize
    
    
    protected function sanitizeDOMAttribs ( DOMNode $domNode) {
    	foreach ( $domNode->childNodes as $node ) {
    		foreach ( $node->attributes as $attr ) {
    			$attr->nodeValue = $this->sanitize( $attr->nodeValue );
    		}
    		if($node->hasChildNodes()) {
    			$this->sanitizeDOMAttribs($node);
    		}
    	}
    } // eo function $sanitizeDOMAttribs
    
    
   protected function doLoop ( $name=null , $data=null ) {
    
    	if ( is_array($data) && count($data)
    			&&   array_keys($data) === range( 0, count($data) - 1 ) ) {
    				$childStr = '';
    				foreach ( $data as $value ) {
    					$childStr .= $this->doLoop( $name , $value );
    				}
    				return $childStr;
    			}
    
    			$nodeStr  = '<' . $name . ' ';
    			$childStr = '';
    
    			foreach ( $data as $key => $value ) {
    
    				if ( is_array( $value ) || is_object( $value ) ) {
    					$childStr .= $this->doLoop( $key , $value );
    				}
    				else {
    					$nodeStr .= $key . '="' . $this->sanitize($value) . '" ';
    				}
    
    			}
    
    			if ( trim($childStr) == '' ) {
    				$nodeStr = rtrim($nodeStr) . '/>';
    			}
    			else{
    				$nodeStr = rtrim($nodeStr) . '>' . $childStr . '</' .$name . '>';
    			}
    
    			return $nodeStr;
    
    } // eo function $doLoop
    
    /**
     * Generate an XML that is compatible with the XML for a DataMatrix in a MediPlan
     *
     * Attention: this does NO UTF-8 conversion so far (so that must be done by the DataMatrix
     * generator!)
     * 
     * @param   array|\DOMNode  $data0  an array, DOMNode or DOMDocument that contains the data
     * @return  string          Mediplan-DataMatrix compatible XML translation of $data0
     * @throws  \Exception      Something went wrong
     */
    public function generateMediplanDMapXML( $data0 ) {
      
        if ( empty($data0) ) {
        	die(__METHOD__ . ' : unable to process an empty value.');
            throw new Exception( __METHOD__ . ' : unable to process an empty value.' );
        }
        elseif ( $data0 instanceof DOMNode ) {
            $this->sanitizeDOMAttribs( $data0 );
            if ( $data0 instanceof DOMDocument ) {
                return $data0->saveXML($data0->firstChild);
            }
            else {
                return $data0->ownerDocument->saveXML($data0);
            }
        } 
        elseif ( is_array( $data0 ) ) {
            if ( isset( $data0['MP'] )) {
                return $this->doLoop( 'MP' , $data0['MP'] );
            } 
            else {
                return $this->doLoop( 'MP' , $data0 );
            }
        }
        else {
            if ( is_object($data0) ) {
            	die(__METHOD__ . ' : unable to process an object of class "' 
                                      . get_class($data0) . '".');
                throw new Exception( __METHOD__ . ' : unable to process an object of class "' 
                                      . get_class($data0) . '".' );
            }
            else {
            	die(__METHOD__ . ' : unable to process a scalar value.');
                throw new Exception( __METHOD__ . ' : unable to process a scalar value.' );
            }
        }

    }


}
