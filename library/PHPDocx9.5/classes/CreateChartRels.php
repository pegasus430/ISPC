<?php

/**
 * Create relationships used by images, charts...
 *
 * @category   Phpdocx
 * @package    elements
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class CreateChartRels extends CreateElement
{
    /**
     * 
     * @access protected
     */
    protected $_xml;

    /**
     *
     * @access private
     * @static
     */
    private static $_instance = NULL;

    /**
     * Construct
     *
     * @access public
     */
    public function __construct()
    {
        
    }

    /**
     * Destruct
     *
     * @access public
     */
    public function __destruct()
    {
        
    }

    /**
     * Magic method, returns current XML
     *
     * @access public
     * @return string Return current XML
     */
    public function __toString()
    {
        return $this->_xml;
    }

    /**
     * Singleton, return instance of class
     *
     * @access public
     * @return CreateChartRels
     */
    public static function getInstance()
    {
        if (self::$_instance == NULL) {
            self::$_instance = new CreateChartRels();
        }
        return self::$_instance;
    }

    /**
     * Create relationship document to use in DOCX file
     *
     * @access public
     * @param int $idChart
     */
    public function createRelationship($idChart)
    {
        $this->generateRELATIONSHIPS();
        $this->generateRELATIONSHIP($idChart);
        $this->cleanTemplate();
    }

    /**
     * Create relationship colors document to use in DOCX file
     *
     * @access public
     * @param int $idChart
     * @param int $id Optional, use 2 as default
     */
    public function createRelationshipColors($idChart, $id = 2)
    {
        $xml = '<Relationship Id="rId'.$id.'" Target="colors'.$idChart.'.xml" Type="http://schemas.microsoft.com/office/2011/relationships/chartColorStyle"/>';

        $this->_xml = str_replace('</Relationships>', $xml . '</Relationships>', $this->_xml);
    }

    /**
     * Create relationship style document to use in DOCX file
     *
     * @access public
     * @param int $idChart
     * @param int $id Optional, use 3 as default
     */
    public function createRelationshipStyle($idChart, $id = 3)
    {
        $xml = '<Relationship Id="rId'.$id.'" Target="style'.$idChart.'.xml" Type="http://schemas.microsoft.com/office/2011/relationships/chartStyle"/>';
        
        $this->_xml = str_replace('</Relationships>', $xml . '</Relationships>', $this->_xml);
    }

    /**
     * New relationship, added to relationships XML
     *
     * @access protected
     * @param int $idChart
     * @param int $id Optional, use 1 as default
     */
    protected function generateRELATIONSHIP($idChart, $id = 1)
    {
        $xml = '<Relationship Id="rId' . $id . '" Type="http://schemas.open'
                . 'xmlformats.org/officeDocument/2006/relationships/package" '
                . 'Target="../embeddings/Microsoft_Excel_Worksheet' . $idChart
                . '.xlsx"></Relationship>__GENERATECHARTSPACE__';

        $tag = '__GENERATERELATIONSHIPS__';

        $this->_xml = str_replace($tag, $xml, $this->_xml);
    }

    /**
     * Main tags of relationships XML
     *
     * @access protected
     */
    protected function generateRELATIONSHIPS()
    {
        $this->_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/'
                . 'package/2006/relationships">__GENERATERELATIONSHIPS__'
                . '</Relationships>';
    }

}
