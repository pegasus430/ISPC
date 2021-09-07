<?php

// namespace SmartqStandalone;

// require_once( __DIR__ . DIRECTORY_SEPARATOR . 'TcpdfExtended.php');

// use SmartqStandalone\TcpdfExtended;


class Pms_DeKbv_TcpdfService
{

    protected $documents  = array();

    protected $currentDoc     = null;
    protected $currentDocName = null;

    protected $globalTempPath = null;


    /**
     * Initiate a new TCPDF instance ("PDF document") and select it as the current working base
     *
     * You may assign a "name" to the new document (as option "name" in the 4th parameter)
     * to be able to switch to it as current working base when you work on more than one
     * document at a time.
     * 
     * @param  string $orientation _P_ortrait | _L_andscape | empty string for "choose automatically"
     * @param  string $unit        'mm'
     * @param  string $format      A4 | A3 | ...
     * @param  array  $options     e.g.: ['name'=>'harry' , 'charset'=>'UTF-8']
     * @return [type]              [description]
     */
    public function startNewDocument(
        $orientation = 'P' ,
        $unit        = 'mm' ,
        $format      = 'A4' ,
        $options     = array()
    ) {

        $this->basePath = __DIR__ . DIRECTORY_SEPARATOR;

        if ( empty( $orientation ) ) { $orientation = 'P'; } 
        if ( empty( $unit ) )        { $unit        = 'mm'; } 
        if ( empty( $format ) )      { $format      = 'A4'; } 

        if ( ! isset($options['multidoc_mode']) ) {
            $options['multidoc_mode'] = false;
        } 

        if ( empty($options['charset']) ) {
            $options['charset'] = 'UTF-8';
        } 

        if ( in_array( $options['charset'] , array('UTF-8','utf-8','UTF8','utf8') ) ) {
            $options['charset'] = 'UTF-8';
            $unicode = true;
        } 
        else {
            $unicode = false; // <= probably too simple but for the first approach ...
        }

        $pdfDoc = new Pms_DeKbv_TcpdfExtended( $orientation, $unit, $format, $unicode, $options['charset'], false);

        if ( $options['multidoc_mode'] ) {

            if ( empty($options['name']) ) {
                $options['name'] = 'pdf-' . count( $this->documents );
            } 

            $this->documents[ $options['name'] ] = $pdfDoc;
            $this->currentDoc                    = $pdfDoc;
            $this->currentDocName                = $options['name'];

            return $this;

        } 
        else {

            return $pdfDoc;

        }
        


    }

    /**
     * Change the current document in multidoc_mode
     * 
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function switchDocument ( $name = null ) {

        if( isset( $this->documents[ $name ] ) ) {
            $this->currentDoc = $this->documents[ $name ];
            $this->currentDocName = $name;
        }
        else {
            // FEHLERMELDUNG ?
        }

        return $this;

    }

    /**
     * get the real Tcpdf Object (the TcpdfExtended, to be exact) in multidoc_mode
     * 
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getTcpdfObject ( $name = null ) {

        if( empty($name) ) {
            return $this->currentDoc;
        }
        elseif( isset( $this->documents[ $name ] ) ) {
            return $this->documents[ $name ];
        }
        else {
            // FEHLERMELDUNG ?
            return null;
        }

    }



    public function deleteDocument( $name = null ) {

        if( empty($name) ) {
            $name = $this->currentDocName;
        }

        if ( ! empty($name) && isset( $this->documents[ $name ] ) ) {

            unset( $this->documents[ $name ] );

            if ( $name === $this->currentDocName ) {
                unset( $this->currentDoc );
                $this->currentDoc     = null;
                $this->currentDocName = null;
            }
            
        }

    }

    public function setGlobalTempPath( $path ) {
        $this->globalTempPath = $path;
    }

    public function resetGlobalTempPath() {
        $this->globalTempPath = null;
    }

    /**
     * [OutputEasy description]
     *
     * See same method in class \SmartqStandalone\TcpdfExtended
     * 
     * @param  [type]  $fileName  [description]
     * @param  array   $options   [description]
     * @return [type]             [description]
     */
    public function OutputEasy( $fileName = null, $options = array() ) {

        if ( empty( $options['path'] ) && ! empty( $this->globalTempPath ) ) {
            $options['path'] = $this->globalTempPath;
        } 

        // See \SmartqStandalone\TcpdfExtended::OutputEasy()
        $fullPath = $this->currentDoc->OutputEasy( $fileName , $options );

        $this->deleteDocument();

        return $fullPath;

    }

    /**
     * [__call description]
     *
     * TcpdfService acts as a proxy to the actual current TCPDF-instance in use
     * 
     * @param  [type]  $name       method name
     * @param  [type]  $arguments  array of arguments of the original method call
     * @return [type]              (depends on the original function)
     */
    public function __call ( $name , $arguments ) {
        return call_user_func_array( array( $this->currentDoc , $name ) , $arguments );
    }
    /* */




}
