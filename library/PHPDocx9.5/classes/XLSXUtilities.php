<?php

/**
 * This class offers some utilities to work with existing Excel (.xlsx) documents
 * 
 * @category   Phpdocx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
require_once dirname(__FILE__) . '/CreateDocx.php';

class XLSXUtilities
{
    /**
     * Search and replace shared strings and cell values in an Excel document
     *
     * @access public
     * @param string $source path to the document
     * @param string $target path to the output document
     * @param array $data strings to be searched and replaced
     * @param string scope sharedStrings, sheet
     * @param array $options
     *        sheetName : sheet name to replace the value when using sheet as scope. All if null
     *        sheetNumber : sheet number to replace the value when using sheet as scope. All if null
     * @return void
     */
    public function searchAndReplace($source, $target, $data, $scope, $options = array())
    {
        $xlsxFile = new ZipArchive();

        // make a copy of the the document into its final destination so we do not overwrite it
        copy($source, $target);

        $xlsxFile->open($target);

        $contentTypesXML = $xlsxFile->getFromName('[Content_Types].xml');

        $contentTypesDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $contentTypesDOM->loadXML($contentTypesXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $contentTypesXPath = new DOMXPath($contentTypesDOM);
        $contentTypesXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // get application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml file
        $query = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"]';
        $mainXMLPathNodes = $contentTypesXPath->query($query);

        if ($mainXMLPathNodes->length > 0) {
            // sharedStrings contents
            if ($scope == 'sharedStrings') {
                // get application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml files
                $query = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"]';
                $sharedStringsXMLPathNodes = $contentTypesXPath->query($query);
                $sharedStringsXML = $xlsxFile->getFromName(substr($sharedStringsXMLPathNodes->item(0)->getAttribute('PartName'), 1));

                $sharedStringsDOM = new DOMDocument();
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $sharedStringsDOM->loadXML($sharedStringsXML);
                libxml_disable_entity_loader($optionEntityLoader);

                $sharedStringsXPath = new DOMXPath($sharedStringsDOM);
                $sharedStringsXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                // replace the data
                foreach ($data as $key => $value) {
                    $this->searchToReplace($sharedStringsXPath, $key, $value);
                }

                $xlsxFile->addFromString(substr($sharedStringsXMLPathNodes->item(0)->getAttribute('PartName'), 1), $sharedStringsDOM->saveXML());
            }

            // worksheet contents
            if ($scope == 'sheet') {
                // get sheets from $mainXMLPathNodes to get the correct order of the sheets
                $mainXML = $xlsxFile->getFromName(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1));

                $mainDOM = new DOMDocument();
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $mainDOM->loadXML($mainXML);
                libxml_disable_entity_loader($optionEntityLoader);

                $mainXPath = new DOMXPath($mainDOM);
                $mainXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                $query = '//xmlns:sheets/xmlns:sheet';
                // query by sheet name if set
                if (isset($options['sheetName'])) {
                    $query .= '[@name="'.$options['sheetName'].'"]';
                }
                // query by sheet number if set
                if (isset($options['sheetNumber'])) {
                    $query .= '['.$options['sheetNumber'].']';
                }
                $sheetNodes = $mainXPath->query($query);

                // get sheet rels to get the sheet contents
                $mainRelsXML = $xlsxFile->getFromName(str_replace('xl/', 'xl/_rels/', substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1)) . '.rels');

                $mainRelsDOM = new DOMDocument();
                $optionEntityLoader = libxml_disable_entity_loader(true);
                $mainRelsDOM->loadXML($mainRelsXML);
                libxml_disable_entity_loader($optionEntityLoader);

                $mainRelsXPath = new DOMXPath($mainRelsDOM);
                $mainRelsXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
                
                $worksheetsData = array();
                foreach ($sheetNodes as $sheetNode) {
                    $query = '//xmlns:Relationship[@Id="'.$sheetNode->getAttribute('r:id').'"]';
                    $sheetContentNodes = $mainRelsXPath->query($query);
                    $worksheetsData['xl/' . $sheetContentNodes->item(0)->getAttribute('Target')] = $xlsxFile->getFromName('xl/' . $sheetContentNodes->item(0)->getAttribute('Target'));
                }
                
                // replace the data
                foreach ($worksheetsData as $worksheetKey => $worksheetValue) {
                    $worksheetDataDOM = new DOMDocument();
                    $optionEntityLoader = libxml_disable_entity_loader(true);
                    $worksheetDataDOM->loadXML($worksheetValue);
                    libxml_disable_entity_loader($optionEntityLoader);

                    $worksheetXPath = new DOMXPath($worksheetDataDOM);
                    $worksheetXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                    foreach ($data as $dataValue) {
                        $query = '//xmlns:sheetData/xmlns:row[@r="'.$dataValue['row'].'"]/xmlns:c['.$dataValue['col'].']/xmlns:v';
                        $dataNode = $worksheetXPath->query($query);
                        if ($dataNode->length > 0) {
                            $dataNode->item(0)->nodeValue = $dataValue['value'];
                        }
                    }

                    $worksheetsData[$worksheetKey] = $worksheetDataDOM->saveXML();
                }

                // save the data in the XLSX file
                foreach ($worksheetsData as $worksheetKey => $worksheetValue) {
                    $xlsxFile->addFromString($worksheetKey, $worksheetValue);
                }
            }
        }

        // close the zip file
        $xlsxFile->close();
    }

    /**
     * Removes a sheet from an Excel document
     * 
     * @access public
     * @param string $source path to the document
     * @param string $target path to the output document
     * @param array $options
     *        sheetName (array): sheet names to remove
     *        sheetNumber (array): sheet numbers to remove
     * @return void
     */
    public function removeSheet($source, $target, $options)
    {
        $xlsxFile = new ZipArchive();

        // make a copy of the the document into its final destination so we do not overwrite it
        copy($source, $target);

        $xlsxFile->open($target);

        $contentTypesXML = $xlsxFile->getFromName('[Content_Types].xml');

        $contentTypesDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $contentTypesDOM->loadXML($contentTypesXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $contentTypesXPath = new DOMXPath($contentTypesDOM);
        $contentTypesXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // get application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml file
        $query = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"]';
        $mainXMLPathNodes = $contentTypesXPath->query($query);

        // get sheets from $mainXMLPathNodes to get the correct order of the sheets
        $mainXML = $xlsxFile->getFromName(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1));

        $mainDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $mainDOM->loadXML($mainXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $mainXPath = new DOMXPath($mainDOM);
        $mainXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $query = '//xmlns:sheets/xmlns:sheet';
        // query by sheet name if set
        if (isset($options['sheetName'])) {
            $query .= '[';
            foreach ($options['sheetName'] as $sheetName) {
                $query .= '@name="'.$sheetName.'" or ';
            }
            $query = substr($query, 0, -4);
            $query .= ']';
        }
        // query by sheet number if set
        if (isset($options['sheetNumber'])) {
            $query .= '[';
            foreach ($options['sheetNumber'] as $sheetNumber) {
                $query .= 'position()='.$sheetNumber.' or ';
            }
            $query = substr($query, 0, -4);
            $query .= ']';
        }
        
        $sheetNodes = $mainXPath->query($query);

        foreach ($sheetNodes as $sheetNode) {
            $sheetNode->parentNode->removeChild($sheetNode);
        }

        // save the data in the XLSX file
        $xlsxFile->addFromString(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1), $mainDOM->saveXML());

        // close the zip file
        $xlsxFile->close();
    }

    /**
     * This is the method that selects the nodes that need to be manipulated and call to the replaceString method
     * 
     * @access private
     * @param XPath $XPath the node to be changed
     * @return void
     */
    private function searchToReplace($xPath, $searchTerm, $replaceTerm)
    {
        $query = '//xmlns:t';
        $tNodes = $xPath->query($query);
        $searchTerm = htmlspecialchars($searchTerm);
        $replaceTerm = htmlspecialchars($replaceTerm);

        foreach ($tNodes as $tNode) {
            if ($tNode->nodeValue == $searchTerm) {
                $tNode->nodeValue = $replaceTerm;
            }
        }
    }

}
