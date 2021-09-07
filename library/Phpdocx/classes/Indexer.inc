<?php

/**
 * Return information of a DOCX file
 *
 * @category   Phpdocx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.08.25
 * @link       http://www.phpdocx.com
 */
class Indexer
{
    /**
     * @var array Stores the file internal structure like body, header and footer
     */
    private $documentStructure;

    /**
     * @var ZipArchive ZIP file
     */
    private $documentZip;

    /**
     * Class constructor
     *
     * @param string $path File path
     */
    public function __construct($path)
    {
        // check if the file exists.
        // Throw an error if not exists
        if (!file_exists($path)) {
            throw new Exception('The file ' . $path . ' does not exist');
        }

        // import the file content
        $this->documentZip = new ZipArchive();
        $this->documentZip->open($path);

        // init the document structure array as empty
        $this->documentStructure = array(
            'body' => array(),
            'comments' => array(),
            'endnotes' => array(),
            'footers' => array(),
            'footnotes' => array(),
            'headers' => array(),
            'images' => array(),
            'links' => array(),
        );

        // parse the document
        $this->parse($path);
    }

    /**
     * Return a file as array or JSON
     *
     * @var string $type Output type: 'array' (default), 'json'
     * @return mixed $output
     * @throws Exception If the output type format not supported
     */
    public function getOutput($output = 'array')
    {
        // if the chosen output type is not supported throw an exception
        if (!in_array($output, array('array', 'json'))) {
            throw new Exception('The output "' . $output . '" is not supported');
        }

        // output the document after index
        return $this->output($output);
    }

    /**
     * Extract image contents from a XML string
     *
     * @var String $xml XML string
     */
    protected function extractImages($xml)
    {
        // check if the XML is not empty
        if (!empty($xml)) {
            // load XML content
            $contentDOM = new DOMDocument();
            $contentDOM->loadXML($xml);

            // do a xpath query getting only image tags
            $contentXpath = new DOMXPath($contentDOM);
            $contentXpath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
            $imageEntries = $contentXpath->query('//rel:Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"]');

            // iterate text content and extract text strings. Add a blank space to separate each string
            foreach ($imageEntries as $imageEntry) {
                $imageString = $this->documentZip->getFromName('word/' . $imageEntry->getAttribute('Target'));

                $this->documentStructure['images'][] = array(
                    'content' => $imageString,
                    'path' => $imageEntry->getAttribute('Target'),
                );
            }
        }
    }

    /**
     * Extract link contents from a XML string
     *
     * @var String $xml XML string
     */
    protected function extractLinks($xml)
    {
        // load XML content
        $contentDOM = new DOMDocument();
        $contentDOM->loadXML($xml);

        // do a global xpath query getting only text tags
        $contentXpath = new DOMXPath($contentDOM);
        $contentXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $linkEntries = $contentXpath->query('//w:instrText');

        // iterate link contents and extract the URL
        foreach ($linkEntries as $linkEntry) {
            // if empty text avoid adding the content
            if ($linkEntry->textContent == ' ') {
                continue;
            }

            // remove HYPERLINK and " strings
            $content = str_replace('HYPERLINK', '', $linkEntry->textContent);
            $content = str_replace(array('&quot;', '"'), '', $content);
            $this->documentStructure['links'][] = trim($content);
        }
    }

    /**
     * Extract text contents from a XML string
     *
     * @var String $xml XML string
     * @return String Text content
     */
    protected function extractTexts($xml)
    {
        // load XML content
        $contentDOM = new DOMDocument();
        $contentDOM->loadXML($xml);

        // do a global xpath query getting only text tags
        $contentXpath = new DOMXPath($contentDOM);
        $contentXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $textEntries = $contentXpath->query('//w:t');

        // iterate text content and extract text strings. Add a blank space to separate each string
        $content = '';
        foreach ($textEntries as $textEntry) {
            // if empty text avoid adding the content
            if ($textEntry->textContent == ' ') {
                continue;
            }
            $content .= ' ' . $textEntry->textContent;
        }

        return trim($content);
    }

    /**
     * Return a file as array or JSON
     *
     * @var string $type Output type: 'array' (default), 'json'
     * @return mixed $output
     * @throws Exception If the output type format not supprt
     */
    protected function output($type = 'array')
    {
        // array as default
        $output = $this->documentStructure;

        // export as the choosen type
        if ($type == 'json') {
            $output = json_encode($output);
        }

        return $output;
    }

    /**
     * Parse a DOCX file
     *
     * @var String $path File path
     */
    private function parse($path)
    {
        // parse the Content_Types
        $contentTypesContent = $this->documentZip->getFromName('[Content_Types].xml');
        $contentTypesXml = simplexml_load_string($contentTypesContent);

        // get the rels extension, rels as default
        $contentTypesDom = dom_import_simplexml($contentTypesXml);
        $contentTypesXpath = new DOMXPath($contentTypesDom->ownerDocument);
        $contentTypesXpath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/content-types');
        $relsEntries = $contentTypesXpath->query('//rel:Default[@ContentType="application/vnd.openxmlformats-package.relationships+xml"]');
        $relsExtension = 'rels';
        if (isset($relsEntries[0])) {
            $relsExtension = $relsEntries[0]->getAttribute('Extension');
        }

        // iterate over the Content_Types and add the header, footer and body contents
        foreach ($contentTypesXml->Override as $override) {
            foreach ($override->attributes() as $attribute => $value) {
                // before adding a content remove the first character to get the right file path
                // removing the first slash of each path
                if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml') {
                    // body content
                    
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);
                    
                    $this->documentStructure['body'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml') {
                    // headers content
                     
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);

                    $this->documentStructure['headers'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml') {
                    // footers content
                    
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);
                    
                    $this->documentStructure['footers'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.comments+xml') {
                    // comments content
                    
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);
                    
                    $this->documentStructure['comments'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.footnotes+xml') {
                    // footnotes content
                    
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);
                    
                    $this->documentStructure['footnotes'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                } else if ($value == 'application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml') {
                    // endnotes content
                    
                    // get the file content
                    $content = $this->documentZip->getFromName(substr($override->attributes()->PartName, 1));

                    // extract the text content
                    $textContent = $this->extractTexts($content);
                    
                    $this->documentStructure['endnotes'][] = $textContent;

                    // extract links
                    $this->extractLinks($content);

                    // extract images from the same file name plus rels extension
                    $relsPath = str_replace('word/', 'word/_rels/', substr($override->attributes()->PartName, 1)) . '.' . $relsExtension;
                    $contentRels = $this->documentZip->getFromName($relsPath);

                    // extract images
                    $this->extractImages($contentRels);
                }
            }
        }
    }
}