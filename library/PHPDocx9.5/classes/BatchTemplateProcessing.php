<?php

/**
 * 
 * @category   Phpdocx
 * @package    processing
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
require_once dirname(__FILE__) . '/CreateDocx.php';

class BatchTemplateProcessing
{
    /**
     * @access private
     * @var string
     * @static
     */
    private static $_csvDelimiter = ';';

    /**
     * @access private
     * @var array
     * @static
     */
    private static $_csvData;

    /**
     * @var array
     * @access private
     */
    private $_parsedContent;

    /**
     * @var array
     * @access private
     */
    private $_parsedXML;

    /**
     * @var array
     * @access private
     */
    private $_parsedXMLDOM;

    /**
     * @var array
     * @access private
     */
    private $_templateContent;

    /**
     * @var SimpleXML
     * @access private
     */
    private $_templateRels;

    /**
     * @access private
     * @var string
     * @static
     */
    private static $_templateSymbol = '$';

    /**
     * @var ZipArchive
     * @access private
     */
    private $_templateZip;

    /**
     * @var array
     * @access private
     */
    private $_variableArray;

    /**
     * @var array
     * @access private
     */
    private $_xmlDocuments;

    /**
     * Class constructor
     */
    public function __construct()
    {
        
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        
    }

    /**
     * This is the main class method that does all the needed manipulation to
     * replace all variables in the template with the ones supplied by the CSV file
     * @access public
     * @example ../examples/easy/BatchTemplateJob.php
     * @param string $template path to the template
     * @param string $csv path to the csv file with the data
     * @param array $options, 
     * Values:
     * 'name' (string) generic name. If there is no file name in the csv the generated docx will be named 'name_1.docx', 'name_2.docx' , ...
     * 'folder' (string) destination folder
     * 'templateSymbol' (string)
     * 'csvDelimiter' (string)
     * @return void
     */
    public function batchTemplateJob($template, $csv, $options)
    {
        if (isset($options['templateSymbol'])) {
            self::$_templateSymbol = $options['templateSymbol'];
        }
        if (isset($options['csvDelimiter'])) {
            self::$_csvDelimiter = $options['csvDelimiter'];
        }
        if (!isset($options['name'])) {
            $options['name'] = '';
        }
        if (!isset($options['folder'])) {
            $options['folder'] = '';
        }
        // parse csv data
        $this->parseCSV($csv);
        // extract the contents of the template into memory for easy replication
        $this->extractTemplateFiles($template);
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $this->_templateRels = simplexml_load_string($this->_templateContent['word/_rels/document.xml.rels']);
        libxml_disable_entity_loader($optionEntityLoader);
        // create the array with all the XML documents that should be parsed
        $this->_xmlDocuments = array();
        $this->_xmlDocuments['word/document.xml'] = $this->_templateContent['word/document.xml'];
        // check if there are headers and footers that should be parsed
        $this->_templateRels->registerXPathNamespace('rels', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $query = '//rels:Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header"] | //rels:Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer"]';
        $hfNodes = $this->_templateRels->xpath($query);
        // insert them in the _xmlDocuments array
        for ($j = 0; $j < count($hfNodes); $j++) {
            $this->_xmlDocuments['word/' . (string) $hfNodes[$j]['Target']] = $this->_templateContent['word/' . (string) $hfNodes[$j]['Target']];
        }
        // prepare the PHPDocX variables for replacement
        $this->repairTemplateVariables($this->_variableArray);
        foreach (self::$_csvData as $row => $data) {
            // initialize the file name variable
            $fileName = '';
            //make a copy of the documents we have to inset/modify
            $this->_parsedContent = $this->_templateContent;
            $this->_parsedXML = array();
            $this->_parsedXML = $this->_xmlDocuments;
            // load the document on SimpleXML
            $this->_parsedXMLDOM = array();
            foreach ($this->_parsedXML as $key => $value) {
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $this->_parsedXMLDOM[$key] = simplexml_load_string($value);
                libxml_disable_entity_loader($optionEntityLoader);
            }
            // start the actual variable substitutions
            foreach ($data as $key => $value) {
                switch ($value['type']) {
                    case 'text':
                        $this->replaceTextVariable($value['varName'], $value['val']);
                        break;
                    case 'table':
                        $this->replaceTableVariable($value['varName'], $value['val']);
                        break;
                    case 'list':
                        $this->replaceListVariable($value['varName'], $value['val']);
                        break;
                    case 'image':
                        $this->replaceImageVariable($value['varName'], $value['val']);
                        break;
                    case 'file':
                        $fileName = $value['val'];
                        break;
                }
            }
            // replace the original XML files by the parsed ones
            foreach ($this->_parsedXMLDOM as $path => $xml) {
                $this->_parsedContent[$path] = $xml->saveXML();
            }
            if (!empty($fileName)) {
                $this->generateDocx($this->_parsedContent, $options['folder'] . '/' . $fileName . '.docx');
            } else {
                $this->generateDocx($this->_parsedContent, $options['folder'] . '/' . $options['name'] . '_' . $row . '.docx');
            }
        }
    }

    /**
     * Extracts all the contents from the template file
     *
     * @param string $template
     * @return void
     */
    private function extractTemplateFiles($template)
    {
        $this->_templateZip = new ZipArchive();
        try {
            $openTemplate = $this->_templateZip->open($template);
            if ($openTemplate !== true) {
                throw new Exception('Error while opening the template: please, check the path');
            }
        } catch (Exception $e) {
            PhpdocxLogger::logger($e->getMessage(), 'fatal');
        }
        // read each file and create a new array of contents
        for ($i = 0; $i < $this->_templateZip->numFiles; $i++) {
            $this->_templateContent[$this->_templateZip->getNameIndex($i)] = $this->_templateZip->getFromName($this->_templateZip->getNameIndex($i));
        }
    }

    /**
     * Generates a docx out of the parsed files
     *
     * @param array $docxContents
     * @param string $path
     * @return void
     */
    private function generateDocx($docxContents, $path)
    {
        if (file_exists($path)) {
            //PhpdocxLogger::logger('You are trying to overwrite an existing file', 'info');
        }
        try {
            $zipDocx = new ZipArchive();
            $createZip = $zipDocx->open($path, ZipArchive::CREATE);
            if ($createZip !== true) {
                throw new Exception('Error trying to generate a docx form template: please, check the path and/or writting permissions');
            }
        } catch (Exception $e) {
            //PhpdocxLogger::logger($e->getMessage(), 'fatal');
        }
        // insert all files in zip
        foreach ($this->_parsedContent as $key => $value) {
            $zipDocx->addFromString($key, $value);
        }
        // close zip
        $zipDocx->close();
    }

    /**
     * Parses the csv file
     *
     * @param string $csv
     * @return void
     */
    private function parseCSV($csv)
    {
        $dataSource = fopen($csv, "r");
        $rawData = array();
        $counter = 0;
        while (($csvData = fgetcsv($dataSource, 1000, self::$_csvDelimiter)) !== false) {
            if (!empty($csvData)) {
                $rawData[$counter] = $csvData;
            }
            $counter++;
        }
        fclose($dataSource);

        $keys = $rawData[0];
        $types = $rawData[1];

        for ($i = 2; $i < count($rawData); $i++) {
            for ($j = 0; $j < count($keys); $j++) {
                self::$_csvData[$i - 2][$j]['varName'] = trim($keys[$j]);
                self::$_csvData[$i - 2][$j]['type'] = trim($types[$j]);
                self::$_csvData[$i - 2][$j]['val'] = $rawData[$i][$j];
            }
        }
        // fix this because it does not take into account table variables
        $this->_variableArray = $keys;
    }

    /**
     * Prepares a single PHPDocX variable for substitution
     *
     * @param string $var
     * @param string $content
     * @return string
     */
    private function repairSingleVariable($var, $content)
    {
        $documentSymbol = explode(self::$_templateSymbol, $content);
        foreach ($documentSymbol as $documentSymbolValue) {
            $tempSearch = trim(strip_tags($documentSymbolValue));
            if ($tempSearch == $var) {
                $pos = strpos($content, $documentSymbolValue);
                if ($pos !== false) {
                    $content = substr_replace($content, $var, $pos, strlen($documentSymbolValue));
                }
            }
            if (strpos($documentSymbolValue, 'xml:space="preserve"')) {
                $preserve = true;
            }
        }
        if (isset($preserve) && $preserve) {
            $query = '//w:t[text()[contains(., "' . self::$_templateSymbol . $var . self::$_templateSymbol . '")]]';
            $docDOM = new DOMDocument();
            $optionEntityLoader = libxml_disable_entity_loader(true);
            $docDOM->loadXML($content);
            libxml_disable_entity_loader($optionEntityLoader);
            $docXPath = new DOMXPath($docDOM);
            $docXPath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $affectedNodes = $docXPath->query($query);
            foreach ($affectedNodes as $node) {
                $space = $node->getAttribute('xml:space');
                if (isset($space) && $space == 'preserve') {
                    //Do nothing 
                } else {
                    $str = $node->nodeValue;
                    $firstChar = $str[0];
                    if ($firstChar == ' ') {
                        $node->nodeValue = substr($str, 1);
                    }
                    $node->setAttribute('xml:space', 'preserve');
                }
            }
            $content = $docDOM->saveXML($docDOM->documentElement);
        }
        return $content;
    }

    /**
     * Run over the PHPDocX variables array to repair them in case they are broken in the WordML code
     *
     * @param array $varArray
     * @param string $content
     * @return void
     */
    private function repairTemplateVariables($varArray)
    {
        $expandedVarArray = array();
        foreach ($varArray as $key => $value) {
            $valueArray = explode('##', $value);
            foreach ($valueArray as $key => $var) {
                $expandedVarArray[] = $var;
            }
        }
        foreach ($expandedVarArray as $key => $var) {
            foreach ($this->_xmlDocuments as $file => $content) {
                $this->_xmlDocuments[$file] = $this->repairSingleVariable($var, $content);
            }
        }
    }

    /**
     * Replace image variable
     *
     * @param string $var
     * @param string $val File path
     * @return void
     */
    private function replaceImageVariable($var, $val)
    {
        $search = self::$_templateSymbol . $var . self::$_templateSymbol;
        $query = '//wp:docPr[@descr="' . $search . '"]';
        $imageNodes = $this->_parsedXMLDOM['word/document.xml']->xpath($query);
        if (is_array($imageNodes) && count($imageNodes) > 0) {
            $image = dom_import_simplexml($imageNodes[0]);
            $blip = $image->parentNode->getElementsByTagNameNS('http://schemas.openxmlformats.org/drawingml/2006/main', 'blip')->item(0);
            $id = $blip->getAttribute('r:embed');
            $query = '//rels:Relationship[@Id="' . $id . '"]';
            $imagePath = $this->_templateRels->xpath($query);
            $this->_parsedContent['word/' . (string) $imagePath[0]['Target']] = file_get_contents($val);
        }
    }

    /**
     * Replace list variable
     *
     * @param string $var
     * @param array $val
     * @return void
     */
    private function replaceListVariable($var, $val)
    {
        $varValues = explode('##', $val);
        $search = self::$_templateSymbol . $var . self::$_templateSymbol;
        foreach ($this->_parsedXMLDOM as $key => $dom) {
            $query = '//w:p[w:r/w:t[text()[contains(., "' . $search . '")]]]';
            $foundNodes = $dom->xpath($query);
            foreach ($foundNodes as $node) {
                $domNode = dom_import_simplexml($node);
                foreach ($varValues as $key => $value) {
                    $newNode = $domNode->cloneNode(true);
                    $textNodes = $newNode->getElementsBytagName('t');
                    foreach ($textNodes as $text) {
                        $sxText = simplexml_import_dom($text);
                        $strNode = (string) $sxText;
                        $strNode = str_replace($search, $value, $strNode);
                        $sxText[0] = $strNode;
                    }
                    $domNode->parentNode->insertBefore($newNode, $domNode);
                }
                $domNode->parentNode->removeChild($domNode);
            }
        }
    }

    /**
     * Replace table variable
     *
     * @param string $var
     * @param array $val
     * @return void
     */
    private function replaceTableVariable($var, $val)
    {
        $varKeys = explode('##', $var);
        $rowValues = explode('||', $val);
        $search = array();
        for ($j = 0; $j < count($varKeys); $j++) {
            $search[$j] = self::$_templateSymbol . $varKeys[$j] . self::$_templateSymbol;
        }
        $queryArray = array();
        for ($j = 0; $j < count($search); $j++) {
            $queryArray[$j] = '//w:tr[w:tc/w:p/w:r/w:t[text()[contains(., "' . $search[$j] . '")]]]';
        }
        $query = join(' | ', $queryArray);
        $foundNodes = $this->_parsedXMLDOM['word/document.xml']->xpath($query);
        foreach ($rowValues as $key => $rowValue) {
            foreach ($foundNodes as $node) {
                $domNode = dom_import_simplexml($node);
                if (!is_object($referenceNode) || !$domNode->parentNode->isSameNode($parentNode)) {
                    $referenceNode = $domNode;
                    $parentNode = $domNode->parentNode;
                }
                $vals = explode('##', $rowValue);
                $newNode = $domNode->cloneNode(true);
                $textNodes = $newNode->getElementsBytagName('t');
                foreach ($textNodes as $text) {
                    for ($k = 0; $k < count($search); $k++) {
                        $sxText = simplexml_import_dom($text);
                        $strNode = (string) $sxText;
                        $strNode = str_replace($search[$k], $vals[$k], $strNode);
                        $sxText[0] = $strNode;
                    }
                }
                $parentNode->insertBefore($newNode, $referenceNode);
            }
        }
        // remove the original nodes
        foreach ($foundNodes as $node) {
            $domNode = dom_import_simplexml($node);
            $domNode->parentNode->removeChild($domNode);
        }
    }

    /**
     * Do the actual substitution of the variable for its corresponding text
     *
     * @param string $var
     * @param string $val
     * @return void
     */
    private function replaceTextVariable($var, $val)
    {
        $search = self::$_templateSymbol . $var . self::$_templateSymbol;
        foreach ($this->_parsedXMLDOM as $key => $dom) {
            $query = '//w:t[text()[contains(., "' . $search . '")]]';
            $foundNodes = $dom->xpath($query);
            foreach ($foundNodes as $node) {
                $strNode = (string) $node;
                $strNode = str_replace($search, $val, $strNode);
                $node[0] = $strNode;
            }
        }
    }

}
