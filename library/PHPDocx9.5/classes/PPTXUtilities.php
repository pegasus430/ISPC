<?php

/**
 * This class offers some utilities to work with existing PowerPoint (.pptx) documents
 * 
 * @category   Phpdocx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
require_once dirname(__FILE__) . '/CreateDocx.php';

class PPTXUtilities
{
    /**
     * Search and replace text in a PowerPoint document
     * 
     * @param string $source path to the document
     * @param string $target path to the output document
     * @param array $data strings to be searched and replaced
     * @param array $options
     *        slideNumber : slide number to replace the value. All if null
     * @return void
     */
    public function searchAndReplace($source, $target, $data, $options = array())
    {
        $pptxFile = new ZipArchive();

        // make a copy of the the document into its final destination so we do not overwrite it
        copy($source, $target);

        $pptxFile->open($target);

        $contentTypesXML = $pptxFile->getFromName('[Content_Types].xml');

        $contentTypesDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $contentTypesDOM->loadXML($contentTypesXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $contentTypesXPath = new DOMXPath($contentTypesDOM);
        $contentTypesXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // get application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml file
        $query = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"]';
        $mainXMLPathNodes = $contentTypesXPath->query($query);

        // get sheets from $mainXMLPathNodes to get the correct order of the slides
        $mainXML = $pptxFile->getFromName(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1));

        $mainDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $mainDOM->loadXML($mainXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $mainXPath = new DOMXPath($mainDOM);
        $mainXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/presentationml/2006/main');

        $query = '//xmlns:sldIdLst/xmlns:sldId';
        // query by sheet number if set
        if (isset($options['slideNumber'])) {
            $query .= '['.$options['slideNumber'].']';
        }
        $slideNodes = $mainXPath->query($query);

        // get sheet rels to get the sheet contents
        $mainRelsXML = $pptxFile->getFromName(str_replace('ppt/', 'ppt/_rels/', substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1)) . '.rels');

        $mainRelsDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $mainRelsDOM->loadXML($mainRelsXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $mainRelsXPath = new DOMXPath($mainRelsDOM);
        $mainRelsXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $slidesData = array();
        foreach ($slideNodes as $slideNode) {
            $query = '//xmlns:Relationship[@Id="'.$slideNode->getAttribute('r:id').'"]';
            $slideContentNodes = $mainRelsXPath->query($query);
            $slidesData['ppt/' . $slideContentNodes->item(0)->getAttribute('Target')] = $pptxFile->getFromName('ppt/' . $slideContentNodes->item(0)->getAttribute('Target'));
        }

        // replace the data
        foreach ($slidesData as $slideKey => $slideValue) {
            $slideDataDOM = new DOMDocument();
            $optionEntityLoader = libxml_disable_entity_loader(true);
            $slideDataDOM->loadXML($slideValue);
            libxml_disable_entity_loader($optionEntityLoader);

            $slideXPath = new DOMXPath($slideDataDOM);
            $slideXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/drawingml/2006/main');

            foreach ($data as $dataKey => $dataValue) {
                $this->searchToReplace($slideXPath, $dataKey, $dataValue);
            }

            $slidesData[$slideKey] = $slideDataDOM->saveXML();
        }

        // save the data in the PPTX file
        foreach ($slidesData as $slideKey => $slideValue) {
            $pptxFile->addFromString($slideKey, $slideValue);
        }

        // close the zip file
        $pptxFile->close();
    }

    /**
     * Removes a slide from a PowerPoint document
     * 
     * @access public
     * @param string $source path to the document
     * @param string $target path to the output document
     * @param array $options
     *        slideNumber (array): slide numbers to remove
     * @return void
     */
    public function removeSlide($source, $target, $options)
    {
        $pptxFile = new ZipArchive();

        // make a copy of the the document into its final destination so we do not overwrite it
        copy($source, $target);

        $pptxFile->open($target);

        $contentTypesXML = $pptxFile->getFromName('[Content_Types].xml');

        $contentTypesDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $contentTypesDOM->loadXML($contentTypesXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $contentTypesXPath = new DOMXPath($contentTypesDOM);
        $contentTypesXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // get application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml file
        $query = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"]';
        $mainXMLPathNodes = $contentTypesXPath->query($query);

        // get sheets from $mainXMLPathNodes to get the correct order of the slides
        $mainXML = $pptxFile->getFromName(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1));

        $mainDOM = new DOMDocument();
        $optionEntityLoader = libxml_disable_entity_loader(true);
        $mainDOM->loadXML($mainXML);
        libxml_disable_entity_loader($optionEntityLoader);

        $mainXPath = new DOMXPath($mainDOM);
        $mainXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/presentationml/2006/main');

        $query = '//xmlns:sldIdLst/xmlns:sldId';
        // query by sheet number if set
        if (isset($options['slideNumber'])) {
            $query .= '[';
            foreach ($options['slideNumber'] as $slideNumber) {
                $query .= 'position()='.$slideNumber.' or ';
            }
            $query = substr($query, 0, -4);
            $query .= ']';
        }
        
        $slideNodes = $mainXPath->query($query);

        foreach ($slideNodes as $slideNode) {
            $slideNode->parentNode->removeChild($slideNode);
        }

        // save the data in the PPTX file
        $pptxFile->addFromString(substr($mainXMLPathNodes->item(0)->getAttribute('PartName'), 1), $mainDOM->saveXML());

        // close the zip file
        $pptxFile->close();
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
