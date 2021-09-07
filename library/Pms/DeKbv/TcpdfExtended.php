<?php

// namespace SmartqStandalone;

// if there is no existing TCPDF we use our own ...
// if ( ! class_exists ( '\\TCPDF' , false ) ) {
//     require_once( __DIR__ . DIRECTORY_SEPARATOR . '3rdParty/TCPDF/tcpdf.php');
// }



/**
 * main configuration file
 */
require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/config/lang/ger.php');
require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/config/tcpdf_config.php');

// includes some support files

/**
 * unicode data
*/
require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/unicode_data.php');

/**
 * html colors table
*/
require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/htmlcolors.php');
require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/tcpdf.php');



class Pms_DeKbv_TcpdfExtended extends Pms_PDF
{

    protected $multicellHeaders = array();

    protected $multicellFooters = array();

    protected $multicellParamsDefaults = array();

    protected $multicellHeaderParamsDefaults = array();

    protected $multicellFooterParamsDefaults = array();

    protected $stdTempPath = null;

    protected $_replaceVarsReg = array();

    protected $_localImageReg = array();

    protected $multicellParamsBase = array(
        'w' => 0,  // 0 == 100%, other numbers: width in mm (or whatever unit was selected)
        'width' => null, // alias for "w"
        'h' => 0, // 0 == "auto", other numbers: height in mm (or whatever unit was selected)
        'height' => null, // alias for "h"
        'x' => '', // or a number
        'y' => '', // or a number
        'border' => 0, // 0 | 1 OR any combination of "LTBR"
        'ln' => 0 , // line break 0|1|2
        'fill' => 0 ,
        'reseth' => true ,
        'align' => 'L' , // L | R | C | J
        'valign' => 'T', // T | M | B
        'stretch' => 0,
        'fit_cell' => false, // reduce font size if text is too big
        'maxh' => 0,
        'autopadding' => true,
        'is_html' => false
        // // optional ...
        // 'padding' => [1,1,1,1] ,  // mm (or whatever unit was selected)
        // 'margin'  => [0,0,0,0] ,  // mm (or whatever unit was selected)
        // 'font_size' => 14  // points
    );

    protected $currentTextColor = array(0,0,0,100,false,''); // == black as CMYK
    protected $currentTextColorRetained = array(0,0,0,100,true,'');


    public function setColor( $type, $col1=0, $col2=-1, $col3=-1, $col4=-1, $ret=false, $name='') {
        if ( 'text' == $type ) {
            if ( $ret ) {
                $this->currentTextColorRetained = array( $col1, $col2, $col3, $col4, true, $name );
            } 
            else {
                $this->currentTextColor = array( $col1, $col2, $col3, $col4, false, $name );
            }
        }
        return parent::setColor( $type, $col1, $col2, $col3, $col4, $ret, $name );
    }

    public function getTextColor( $ret=false ) {
        if ( $ret ) {
            return $this->currentTextColorRetained;
        } 
        else {
            return $this->currentTextColor;
        }
    }


    public function getStdTempPath() {

        if ( ! isset( $this->stdTempPath ) ) {
            $basePath = __DIR__ . DIRECTORY_SEPARATOR;
            $this->stdTempPath = __DIR__ . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        } 

        return $this->stdTempPath;

    }

    public function setStdTempPath( $path ) {
        $this->stdTempPath = $path;
    }


    /**
     * [Output description]
     *
     * This proxy method eases the usage of the original Output() method in that we do not
     * have to care for an output path, file name or the destination, if we do not want to 
     * do that.
     *
     * The file name (parameter $fileName) can contain the variables %uid%, %time% and %date%.
     * The %date% will be replaced by a reverse date / date('Ymd').
     *
     * Options:
     *   'dest'   : "destination" (see original \TCPDF::Output() method) - which can be ...
     *                 I : display _i_nline in Browser
     *                 D : send to browser and force the _d_ownload dialogue
     *                 F : save as _f_ile
     *                 E : send as _e_mail
     *                 S : ?
     *                 O : (upper case "o") same as "I"
     *   'path'   : a base path (directory) to the file to override the default temp path
     * 
     * @param  [type]  $fileName  e.g. "my_pdf_%date%_%time%_%uid%.pdf"
     * @param  array   $options   [description]
     * @return [type]             [description]
     */
    public function OutputEasy( $fileName = null, $options = array() ) {

    	if ( empty( $options['path'] ) ) {
            $options['path'] = $this->getStdTempPath();
        } 

        if ( empty( $options['dest'] ) ) {
            if ( 'cli' === php_sapi_name() ) {
                $options['dest'] = 'F'; // in CLI: save File
            }
            else {
                $options['dest'] = 'D'; // in Browser: force Download\
                $options['path'] = '';
            }
        }

        $uidPool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $rndUid  = '';
        for( $i=0; $i<7 ; $i++ ) {
            $rnd = mt_rand( 0, 61 );
            $rndUid .= $uidPool[$rnd];
        }

        if ( empty($fileName) ) {
            $fileName = 'pdf_' . date('YmdHis') . '_' . $rndUid . '.pdf';
        }
        elseif ( false !== strpos( $fileName , '%' ) ) {
            $fileName = str_replace( '%uid%', $rndUid, $fileName );
            $fileName = str_replace( '%time%', date('His'), $fileName );
            $fileName = str_replace( '%date%', date('Ymd'), $fileName );
            $fileName = str_replace( '%', '_', $fileName );
        }

        $fullPath = $options['path'] . $fileName;
        $this->Output( $fullPath , $options['dest'] );

        return $fullPath;

    }

    // --

    public function addMulticellHeader( $content = '' , $params = array() ) {

        $params = array_merge ( $this->getMulticellHeaderParams(), $params );

        if( ! isset($params['content']) ) {
            $params['content'] = $content;
        }

        array_push( $this->multicellHeaders, $params );

    }


    public function addHtmlHeader( $content = '' , $params = array() ) {
        $params['is_html'] = true;
        $this->addMulticellHeader( $content , $params );
    }

    public function addHeaderImage( $filePath = '' , $params = array() ) {

        if( ! isset($params['file']) ) {
            $params['file'] = $filePath;
        }

        $params['_type_switch_'] = 'image';

        array_push( $this->multicellHeaders, $params );

    }

    // --

    public function addMulticellFooter( $content = '' , $params = array() ) {

        $params = array_merge ( $this->getMulticellFooterParams(), $params );

        if( ! isset($params['content']) ) {
            $params['content'] = $content;
        }

        array_push( $this->multicellFooters, $params );

    }


    public function addHtmlFooter( $content = '' , $params = array() ) {
        $params['is_html'] = true;
        $this->addMulticellFooter( $content , $params );
    }

    public function addFooterImage( $filePath = '' , $params = array() ) {

        if( ! isset($params['file']) ) {
            $params['file'] = $filePath;
        }

        $params['_type_switch_'] = 'image';

        array_push( $this->multicellFooters, $params );
    }

    // --

    public function getMulticellParams() {
        return array_merge( $this->multicellParamsBase , $this->multicellParamsDefaults );
    }

    public function setMulticellParamsDefaults( array $defaultParams ) {
        $this->multicellParamsDefaults = $defaultParams;
    }

    // --

    public function getMulticellHeaderParams() {
        return array_merge( $this->multicellParamsBase , $this->multicellHeaderParamsDefaults );
    }

    public function setMulticellHeaderParamsDefaults( array $defaultParams ) {
        $this->multicellHeaderParamsDefaults = $defaultParams;
    }

    // --

    public function getMulticellFooterParams() {
        return array_merge( $this->multicellParamsBase , $this->multicellFooterParamsDefaults );
    }

    public function setMulticellFooterParamsDefaults( array $defaultParams ) {
        $this->multicellFooterParamsDefaults = $defaultParams;
    }


    /**
     * Register a local image for use inside html blocks
     * 
     * After registering an image with this function you can use either the replace callback - see
     * description writeMulticellNP() - or pipe your HTML directly through self::replaceVarsCallback()
     * like this: $html = $pdf->replaceVarsCallback( $html );
     * (If you use the first option anyway because it concerns a footer or header you may NOT use the
     *  second option additionally! Don't mix the two options.)
     *
     * You refer the image as a replace variable starting with "%img_b64:". In whole the image tag
     * could for example look like: '<img ... src="%img_b64:my_image_name%" ... >'
     *
     * If you do neither work with HTML based content nor use addHtmlFooter()/addHtmlHeader() for the
     * concerned image you do NOT need this feature, because you can then better use native TCPDF 
     * features for that - like this:
     *
     *   $imageContent = file_get_contents('/var/www/html/image.png');
     *   $pdf->Image('@'.$imageContent);
     *   // or respectively $pdf->writeImageFileNP('@'.$imageContent , [...] );
     *
     * You can alternatively use addHeaderImage()/addFooterImage() to place an image in the header or
     * footer (uses also writeImageFileNP() to actually insert the image).
     * 
     * @param  [type] $imageName [description]
     * @param  [type] $path      [description]
     * @return [type]            [description]
     */
    public function registerLocalImage( $imageName , $path ) {
        $this->_localImageReg[ $imageName ] = $path;
    }

    public function _getLocalImageBase64( $imageName ) {
        if ( ! isset($this->_localImageReg[ $imageName ]) || '_sys_img_not_found' === $imageName ) {
            return 'data:image/png;base64,'
            . 'iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAMAAACahl6sAAADAFBMVEW/v9g/P4p/f7EPD2zv7/UvL4Cfn8QfH3bPz+JfX51PT5Nv'
            . 'b6ePj7qvr87f3+sAAGP///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
            . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgZDhcAAAHVUlEQVR4nO2d'
            . '6ZqrIAyGEfel2vu/2jMkrAFbe7oYefh+TKcgkrciCAEU90wkzjbgUyog3FRAuKmAcFMB4aYCwk0FhJsKyAfUCPmn5TMmHDyL2JSk'
            . 'F7JpmTCpv1ckZZ0O/sOQOmqrZRhTbZ5+DjLtgOwZ1LSetW1zPshEcq9SCf/UhOH32bd2m88HMYbX5LtWZ44jGTVbKJ/zHJB+B0zL'
            . '3DpbF4YjYLveVyxiNwpSV6ifgMzuJ1v//hsTIMqolqb9Exy7DfbUM0mTqB2+CCJdoRDmKzGgfwC41fZMQfQJIL0t/DdVehIG2MPa'
            . 'MLx2x7YMQCoLoq6GiA2AoK6K71rvMkCsz3kCyGQD1e0yxAbARRLQxqwPQXzOE0CkDVSZ32MD8C6Cv2FODEEmk3mdAMFCtcTVFjMQ'
            . 'YXOEf2IDWij+kDpovOOqytNJIL35mgLBkEQtwAzkbgqFsN8CAwadSn3UwQk5gjT3XZCbBxLWv9xAWl0dLfAZGWCqqyqqtriBmBZR'
            . 'pkGgARm0af5zITuQUYeOUMQiA1pdpGaa+jmIlkwf8nEQ0yJCexiDmJscithEYziBLNqcHp6WqHH2mSV+nDQgg9A6GcS0iPhBzYVU'
            . 'o02eArFGnwwyYNlp0GAKIk2iFZI3JPlDkF6CDo8VvQmiW0Thvvgg0KPqrN2CJH8I8uNaCz5WiO5iENd8QD9q4QyCDQk2IxGIK1BV'
            . 'Krk6tpPQf+QBcgOQIQJZnYVTkpHPYzw2dVI3IxTEDs5p9SQ5KxBpQOoYRBKQjSRnBdJBnrVtTXwDRgoyMAbBFnFLglQURITJWYFA'
            . 'izjoOGIApBmxYaPp2YHAp0iCNH5xgufgkTOIaup0M0JAggcsahw/EJVrlQSBQSBT5UKPpA6T8wNRbXMTgcz+V0msrRwlExDTWJgw'
            . 'ZwDYY0azYBjCq7bQU7fyA2ljkOBBEU/gvD1Q7rap2QXRj/EyHDL+Igj+1Gh/ANIE16AhJ8DnMOWX8q6oD2J0tEPyNoh+oBojEOEK'
            . 'j4nzu+20tTwdRP+0MgJZQvtaWwBRa70FOh1Ee0FtP9CCyNDy6E4YPEd7PUbO0C+BfEW3Gfy24/GO+QOVSTXcVEC4qYBwUwHhpgLC'
            . 'TQWEmwoINxUQbiog3FRAuKmAvJLHB9e77Gfy9hn0+BMMTAk6+nW/N2NyvQv1+B6ec7Knj4FIa15gU7DexfNY8QWZrHmBTdWOuXxB'
            . 'YHC0oTYNobluygBfkM1+8W1C70nV3AXOOLGe9kENlmKxa9W/HT3tq/oMSG1spCBYspQ7B5fwLEFSvCw/Xb73QPCTG3soSB+WOjKv'
            . 'nB/IvHk+htRspvBfmzk7EOXUsT6rCAQvQ38FELtqIQniVpjwB2k356C+NIiycr1nAOLPRrk0SLe5+UGXBhG6cro8iD+H7togtZue'
            . 'eW0QZWaTA8i82bnYVwbpYObJcn0QXEgy6xmOJPJaIM1mFx9cG0R99FmA4Dz/DEBGWwtfHERC5ZUBCE4rzwBkADMzAMFqKwcQ6O3m'
            . 'AKImiQ85gKhq65YDCPR2cwBRVk1JEBygi3bW4Qqi/mmTIBcaMgXb1ZgoBakJyBgkZQqiIGh/xM2Iv3m3i82cJ4hZ2+MbO5ubRPsS'
            . 'w+z4gUDhWRIg2sPWV+jaDTd54QoiEiDoTLAKbxGuIE0KJPCGtmSPQ6YgZodDckO7a1LRvRp5gTyVXu8ih+eHvqEyqYabCgg3FRBu'
            . 'KiDcVEC4qYBwUwHhpgLCTQWEmwoINxUQo6FTy1xudKTn5zoGEiye8CM6O2o1hbsVSTdkJenolfBPRPeUN5FVNS2vDCC9B+Lv+1cH'
            . '2R4BcbGJSKQ5XmDeArn5weFrII6AdE9ByP7TXwNp9zM9AuKcEfsg9dGb7xUQ+o4W3MipFvcGi5g/1n4EpNoHaSvtiKBvkvkICA3F'
            . 'kgVrW2pato6A9PsgKkBvivYDEM/CiR7wFETv44q/RhIES66/k+u3QGYXisZ65fkpiCqN9hU9aRC6k+DXQCoK4p3sKQhMlDgCcrAx'
            . 'OQ3EbBv6FORgU3IayKpv5MuDwESJLED0aqbrg0x4I18fRK9muj6IagllDiDq75gDiF7NdAYIPvzOJPS/QfrNvF/i1yAosjPx/4Pg'
            . 'IswMQLDaygBE9Tfk9e+RRq9munytJfRqpgxA9CLMDEBwEWYGILgIMwMQFbHkAIK93QxAVG+3ygEEVzPlAAKDdDmAqN5uDg0ixHQM'
            . 'QEYK4jmtDoFAb/cZyDGODwxiD4TJtwc8JtEAtwWBRZj7IJCQLHD4CghumQUODHAl+gPnOLl/tnG+Pe622PRb6JMgeIpfuBXQ0dM3'
            . 'Bil+KajyK8ZxDsS4vCKQUUq8kGRrse+A6MUhtd6CLXQuaTtMnG+PA6n2QKy+4nqLgpcHeYr9OAcin4IcvSDvgYTeUOKADV5ZF1ws'
            . 'B9I9AakPc7wJ0kwuz8iRPO/FCftTBx53CtJP3QvzKQ42N7saJL7VJeV8XU3cL+Z3vAvCRgWEmwoINxUQbiog3FRAuOkfR1tSFm9N'
            . 'ZG4AAAAASUVORK5CYII=';
        }
        elseif ( ! is_readable ( $this->_localImageReg[ $imageName ] ) ) {
            return $this->_getLocalImageBase64( '_sys_img_not_found' );
        }
        else {
            $path = $this->_localImageReg[ $imageName ];
            $exifType = exif_imagetype ( $this->_localImageReg[ $imageName ] );
            if ( false === $exifType ) {
                // this is not an image ...
                return $this->_getLocalImageBase64( '_sys_img_not_found' );
            }
            $mimeType = image_type_to_mime_type( $exifType );
            $content = file_get_contents( $this->_localImageReg[ $imageName ] );
            return 'data:' . $mimeType . ';base64,' . base64_encode( $content );
        }
        
    }


    protected function _replaceVarsPercent ( $matches ) {
        $var = $matches[1];
        if ( strpos( $var , '->' ) !== false ) {
            $arr0 = explode( '->' , $var );
            $object = null;
            $method = null;
            $proper = null;
            $params = null;
            // $_replaceVarsReg
            $objectName = trim($arr0[0]);

            if( in_array( $objectName , array('','this','tcpdf','pdf') ) ) {
                $object = $this;
            }
            elseif ( in_array( $objectName , $this->_replaceVarsReg ) ) {
                $object = $this->_replaceVarsReg[$objectName];
            }

            if ( $object ) {
                $params = explode( '(', $arr0[1] );
                if( isset($params[1]) ) {
                    $method = trim( $params[0] );
                    $str = rtrim( ltrim( $params[1] ) , ' )' );
                    $params = explode( ',', $params[1] );
                    foreach ( $params as &$param ) {
                        $param = trim( $param );
                        if ( '"' == $param[0] ) {
                            $param = trim( $param, '"' );
                        } 
                        elseif ( "'" == $param[0] ) {
                            $param = trim( $param, "'" );
                        }
                    }
                    unset($param);
                }
                else {
                    $proper = trim($params[0]);
                }
            }
            if( $object && $method && is_callable( array( $object, $method ) ) && method_exists( $object, $method ) ) {
                if( count($params) ) {
                    return call_user_func_array( array( $object, $method ) , $params );
                }
                else {
                    return call_user_func( array( $object, $method ) );    
                }
            }
            elseif ( $object && $proper && property_exists ( $object , $proper ) ) {
                return $object->$proper;
            }
        }
        elseif( 0 === strpos( $var , 'img_b64:' ) ) {
            $arr = explode( ':', $var );
            return $this->_getLocalImageBase64( trim($arr[1]) );
        }
        elseif( array_key_exists( $var , $this->_replaceVarsReg ) ) {
            // use registerReplaceRef() for this feature!
            return $this->_replaceVarsReg[$var];
        }
        return '(var '.$var.' unknown)';
    }

    /**
     * register a variable by reference to be evaluated when replaceVarsCallback() is called
     *
     * @param  string $name [description]
     * @param  mixed  &$var [description]
     * @return [type]       [description]
     */
    public function registerReplaceRef ( $name , &$var ) {
        $this->_replaceVarsReg[$name] =& $var;
    }

    /**
     * register an object or callback to be evaluated when replaceVarsCallback() is called
     *
     * @param  string $name [description]
     * @param  mixed  $var  [description]
     * @return [type]       [description]
     */
    public function registerReplaceVar ( $name , $var ) {
        $this->_replaceVarsReg[$name] = $var;
    }

    /**
     * [replaceVarsCallback description]
     *
     * ... to be used with the "callback" option of writeMulticellNP() and the like.
     *
     * Up to now, variables start and end with a percent sign and stand for functions
     * that produce a return that will be inserted in place of the variable.
     *
     * Examples:
     * "This is page %this->getNumPages()%"
     * "This is page %TCPDF->getNumPages()%"
     * "This is page %->getNumPages()%"
     *
     * By default only methods of $this (TCPDF / TcpdfExtended) can be called. You may use
     * "this", "pdf" or "TCPDF" to name the current instance - or you may simply leave the
     * name away, as shown in the upper examples. If you want to call a method of another
     * class / instance, you must register the object / instance using 
     * $this->registerReplaceVar(), e.g. $this->registerReplaceVar( 'myObject' , $myObject ).
     * After that you can use a method of this object to replace a variable correspondingly
     * to the first examples by using the registered name:
     * 
     * '... %myObject->returnWhatever( "param_1" , "param_2" )% ...
     * 
     * 
     * @param  [type] $str   the original string with "variables"
     * @return [type]      [description]
     */
    public function replaceVarsCallback( $str ) {
        return  preg_replace_callback(
                    '~%([\\w\\-][\\w\\d\\-\\>\\:]*?(?:\\(.*?\\))?)%~',
                    array( $this , '_replaceVarsPercent' ),
                    $str
                );
    }

    /**
     * MultiCell() with named parameters
     *
     * See also writeHTMLCellNP() as an alteranive to use the "html" option and
     * see original code comments (3rdParty\TCPDF\tcpdf.php) on method MultiCell().
     *
     * The 2nd and 3rd parameter do exactly the same. You may use the 2nd parameter
     * to commit commonly/repeatedly used settings as a variable and the 3rd for
     * settings only specific to the current cell - or you use only the 2nd parameter
     * and leave the 3rd uncommitted. Settings from the 3rd param. override those of
     * the 2nd.
     *
     * Custom Parameters / Options:
     *   'w'          : width
     *   'width'      : width (overrides param 'w')
     *   'h'          : height
     *   'height'     : height (overrides param 'h')
     *   'content'    : the content (overrides first function parameter!)
     *   'callback'   : a callback that can receive a static / current content and manipulate
     *                  it (the return will be written to the box). This ist especially useful
     *                  when working with Footers and Headers (see addHtmlHeader() etc.).
     *                  Will be something like  [$pdf,'replaceVarsCallback']  in most cases.
     *   'border'     : border (String made from L, T, B and R - default 0 )
     *   'align'      : text align inside the cell (default 'J' )
     *   'fill'       : background (default false )
     *   'ln' / 'br'  : line break (default 1 )
     *   'x'          : horizontal position (default '' )
     *   'y'          : verticalc position (default '' )
     *   'reseth'     : reset the last cell height (default true )
     *   'stretch'    : font stretch mode ( see original TCPDF::MultiCell - default 0 )
     *   'ishtml'     : switch HTML content parsing on (default false )
     *   'autopadding':   (default true )
     *   'maxh'       : maximum heigth (default 0 )
     *   'valign'     : vertical align (default 'T' - for 'B' and 'M' set h AND maxh to an identical value!)
     *   'fitcell'    : adapt font size to make content fitting the cell (default false)
     *                  (set h AND maxh to an identical value!)
     *   'padding'    : override preset padding only for this cell (left,top,right,bottom)
     *   'margin'     : override preset margin only for this cell (left,top,right,bottom)
     *   'font_size'  : override preset font size only for this cell
     *   'font_style' : override preset font style only for this cell
     * 
     * @param   string  $content        the content of the cell (if there is no custom parameter 'content')
     * @param   array   $customParams   named parameters (see method getMulticellParams() )
     * @param   array   $customParams2  named parameters (see method getMulticellParams() )
     * @return  int  the number of cells or 1 for html mode.
     */
    public function writeMulticellNP( $content = '' , $customParams = array() , $customParams2 = array() ) {

        $p = array_merge( $this->getMulticellParams() , $customParams , $customParams2 );

        if( ! isset($p['content']) ) {
            $p['content'] = $content;
        }

        if ( isset($p['callback']) ) {
            if ( is_callable($p['callback']) ) {
                $p['content'] = call_user_func( $p['callback'] , $p['content'] );
            }
            else {
                $p['content'] = '(err.: not a callable) ' . $p['content'];
            }
        }

        if ( isset($p['width']) ) {
            $p['w'] = $p['width'];
        }
        if ( isset($p['height']) ) {
            $p['h'] = $p['height'];
        }

        if ( isset($p['br']) ) {
            $p['ln'] = (int) $p['br'];
        }

        if ( isset($p['padding']) ) {
            $oldPad  = $this->getCellPaddings();
            $pad = $p['padding'];
            $this->setCellPaddings( $pad[0], $pad[1], $pad[2], $pad[3] );
        }
        if ( isset($p['margin']) ) {
            $oldMarg = $this->getCellMargins();
            $mrg = $p['margin'];
            $this->setCellMargins( $mrg[0], $mrg[1], $mrg[2], $mrg[3] );
        }

        if ( isset($p['font_size']) ) {
            $oldFontSz = $this->getFontSizePt();
            $this->setFontSize($p['font_size']);
        }

        if ( isset($p['font_style']) ) {
            $oldFontStyle = $this->getFontStyle();
            $this->SetFont( '', $p['font_style'] );
        }

        if ( isset($p['font_color']) ) {
            $oldFontColor = $this->getTextColor();
            call_user_func_array( array( $this, 'SetTextColor' ) , $p['font_color'] );
        }

        $countCells = $this->MultiCell(
            $p['w'], $p['h'], $p['content'], $p['border'], 
            $p['align'], $p['fill'], $p['ln'], 
            $p['x'], $p['y'], $p['reseth'], $p['stretch'], 
            $p['is_html'], $p['autopadding'], 
            $p['maxh'], $p['valign'], $p['fit_cell']
        );

        // reset temporary global settings ...
        if ( isset($p['padding']) ) {
            if ( empty($oldPad) ) {
                $this->setCellPaddings( 0, 0, 0, 0 );
            } 
            else {
                $this->setCellPaddings( $oldPad['L'],$oldPad['T'],$oldPad['R'],$oldPad['B'] );
            }
        }
        if ( isset($p['margin']) ) {
            if ( empty($oldMarg) ) {
                $this->setCellMargins( 0, 0, 0, 0 );
            } 
            else {
                $this->setCellMargins( $oldMarg['L'],$oldMarg['T'],$oldMarg['R'],$oldMarg['B'] );
            }
        }
        if ( isset($p['font_size']) ) {
            $this->setFontSize($oldFontSz);
        }
        if ( isset($p['font_style']) ) {
            $this->SetFont( '', $oldFontStyle );
        }
        if ( isset($p['font_color']) ) {
            call_user_func_array( array( $this, 'SetTextColor' ) , $oldFontColor );
        }

        return $countCells;
    }


    public function writeHTMLCellNP( $html = '' , $customParams = array() , $customParams2 = array() ) {
        $customParams2['is_html'] = true;
        $this->writeMulticellNP( $html , $customParams , $customParams2 );
    }


    /**
     * [insertImageFileNP description]
     *
     * Provides TCPDF's native Image()-method with named parameters. If you commit a file path that ends
     * with a one of the well known image file extensions (.jpg/.png/...) it is not necessary to provide
     * a "type" parameter as the function finds it itself. 
     * 
     * See also original code comments (3rdParty\TCPDF\tcpdf.php) on method Image().
     *
     * To place images in HTML-Headers and -Footers see self::registerLocalImage() and 
     * self::addHeaderImage()/self::addFooterImage().
     *
     * If you prepend a "#" (instead of a "@") you can address an image in $this->_localImageReg.
     * 
     * @param  [type] $filePath     [description]
     * @param  array  $customParams [description]
     * @return [type]               [description]
     */
    public function writeImageFileNP( $filePath, $customParams = array() ) {

        $basicParams = array(
                // 'file'   => '',
                'x'      => '',
                'y'      => '',
                'w'      => 0,
                'h'      => 0,
                'type'   => '',
                'link'   => '',
                'align'  => 'T', // TMB
                'resize' => false,
                'dpi'    => 300,
                'palign' => '',
                'ismask' => false,
                'imgmask'=> false,
                'border' => 0,
                'fitbox' => false,
                'hidden' => false,
                'fitonpage' => false,
                'alt'    => false,
                'altimgs'=>array()
        );        

        $p = array_merge( $basicParams , $customParams );

        if( empty( $p['file'] ) ) {
            $p['file'] = $filePath;
        }
        if( empty( $p['file'] ) ) {
        	die(__METHOD__ . ' : missing file path.');
            throw new Exception( __METHOD__ . ' : missing file path.', 1);
        }

        if ( isset($p['width']) ) {
            $p['w'] = $p['width'];
        }
        if ( isset($p['height']) ) {
            $p['h'] = $p['height'];
        }

        if ( isset($p['br']) && (int) $p['br'] ) {
            $p['align'] = 'N';
        }

        if( '#' == $p['file'][0] ) {
            $str = substr( $p['file'], 1 );
            if ( isset( $this->_localImageReg[$str] ) ) {
                $p['file'] = '@' . $this->_localImageReg[$str];
            }
        }

        return $this->Image( 
            $p['file'],   
            $p['x'], $p['y'], 
            $p['w'], $p['h'], 
            $p['type'],   $p['link'], $p['align'], 
            $p['resize'], $p['dpi'],  $p['palign'], 
            $p['ismask'], $p['imgmask'], 
            $p['border'], $p['fitbox'], 
            $p['hidden'], $p['fitonpage'], 
            $p['alt'], $p['altimgs'] 
        );

    }

    public function Header() {
        if ( empty($this->multicellHeaders) ) {
            parent::Header();
        } else {
            foreach ( $this->multicellHeaders as $customParams ) {
                if ( isset($customParams['_type_switch_']) && 'image' === $customParams['_type_switch_'] ) {
                    switch ( $customParams['_type_switch_'] ) {
                        case 'image':
                            unset( $customParams['_type_switch_'] );
                            $this->writeImageFileNP( '' , $customParams );
                            break;
                        default:
                            unset( $customParams['_type_switch_'] );
                            $this->writeMulticellNP( '' , $customParams , array() );
                            break;
                    }
                } 
                else {
                    $this->writeMulticellNP( '' , $customParams , array() );
                }
            }
        }
    }

    public function Footer() {
        if ( empty($this->multicellFooters) ) {
            parent::Footer();
        } else {
            foreach ( $this->multicellFooters as $customParams ) {
                if ( isset($customParams['_type_switch_']) && 'image' === $customParams['_type_switch_'] ) {
                    switch ( $customParams['_type_switch_'] ) {
                        case 'image':
                            unset( $customParams['_type_switch_'] );
                            $this->writeImageFileNP( '' , $customParams );
                            break;
                        default:
                            unset( $customParams['_type_switch_'] );
                            $this->writeMulticellNP( '' , $customParams , array() );
                            break;
                    }
                } 
                else {
                    $this->writeMulticellNP( '' , $customParams , array() );
                }
            }
        }
    }

}
