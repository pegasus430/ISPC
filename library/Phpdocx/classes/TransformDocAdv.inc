<?php

/**
 * Transform DOCX to PDF, ODT, SXW, RTF, DOC, TXT, HTML or WIKI
 *
 * @category   Phpdocx
 * @package    trasform
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.05.01
 * @link       http://www.phpdocx.com
 */

require_once dirname(__FILE__) . '/CreateDocx.inc';

abstract class TransformDocAdv
{

    /**
     *
     * @access protected
     * @var ZipArchive
     */
    protected $_allowedExtensions;

    /**
     *
     * @access protected
     * @var array
     */
    protected $_phpdocxconfig;

    /**
     * Construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->_phpdocxconfig = PhpdocxUtilities::parseConfig();
        $this->_allowedExtensions = array('docx', 'pdf', 'odt', 'sxw', 'rtf', 'doc', 'txt', 'html', 'htm', 'wiki');
    }

    /**
     * Prepare docx before pdf transformation
     *
     * @access public
     * @param $docSource
     * @param $temdir
     * @return string
     */
    public function prepareDocx($docSource, $tempDir = null, $options)
    {
        if ($tempDir === null) {
            $tempDir = $this->getTempDirPath();
            $tempName = $tempDir . '/tempDocX_' . uniqid() . '.docx';
        } else {
            $tempName = $tempDir . '/tempDocX_' . uniqid() . '.docx';
        }
        copy($docSource, $tempName);


        if (file_exists(dirname(__FILE__) . '/RepairPDF.inc')) {
            $originalDocx = new zipArchive();
            $originalDocx->open($tempName);
            $docXML = $originalDocx->getFromName('word/document.xml');
            $numberingXML = $originalDocx->getFromName('word/numbering.xml');
            $repairedXML = RepairPDF::repairPDFConversion($docXML, $numberingXML, $options);
            $originalDocx->addFromString('word/document.xml', $repairedXML);
            //close the zip
            $originalDocx->close();
        }

        return $tempName;
    }

    /**
     * Transform DOCX to PDF, ODT, SXW, RTF, DOC, TXT, HTML or WIKI
     *
     * @access public
     * @param $docSource
     * @param $docDestination
     * @param string $tempDir
     * @param array $options
     * @param string $version 32, 64 or null (default). If null autodetect
     * @return void
     */
    abstract public function transformDocument($docSource, $docDestination, $tempDir = null, $options = array(), $version = null);

    /**
     * To add support of sys_get_temp_dir for PHP versions under 5.2.1
     *
     * @access protected
     * @return string
     */
    protected function getTempDirPath()
    {
        if ($this->_phpdocxconfig['settings']['temp_path']) {
            return $this->_phpdocxconfig['settings']['temp_path'];
        }
        if (!function_exists('sys_get_temp_dir')) {

            function sys_get_temp_dir()
            {
                if ($temp = getenv('TMP')) {
                    return $temp;
                }
                if ($temp = getenv('TEMP')) {
                    return $temp;
                }
                if ($temp = getenv('TMPDIR')) {
                    return $temp;
                }
                $temp = tempnam(__FILE__, '');
                if (file_exists($temp)) {
                    unlink($temp);
                    return dirname($temp);
                }
                return null;
            }

        } else {
            return sys_get_temp_dir();
        }
    }

    /**
     * 
     */
    protected function getTemplateImage($name, $id)
    {
        $templateImage = '<pic:pic xmlns:r="http://schemas.openxmlformats.org/package/2006/relationships" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
                    <pic:nvPicPr>
                        <pic:cNvPr id="0" name="' . $name .'"/>
                        <pic:cNvPicPr/>
                    </pic:nvPicPr>
                    <pic:blipFill>
                        <a:blip r:embed="' . $id . '"/>
                        <a:stretch>
                            <a:fillRect/>
                        </a:stretch>
                    </pic:blipFill>
                    <pic:spPr>
                        <a:xfrm>
                            <a:off x="0" y="0"/>
                            <a:ext cx="4876800" cy="3657600"/>
                        </a:xfrm>
                        <a:prstGeom prst="rect">
                            <a:avLst/>
                        </a:prstGeom>
                    </pic:spPr>
                </pic:pic>';
        return $templateImage;
    }

}
