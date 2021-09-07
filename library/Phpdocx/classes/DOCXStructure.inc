<?php

/**
 * Storage DOCX internal structure
 * 
 * @category   Phpdocx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @version    2016.08.25
 * @link       http://www.phpdocx.com
 */
class DOCXStructure
{
    /**
     * DOCX structure
     * @access private
     * @var array
     */
    private $docxStructure;

    /**
     * Parse a DOCX file
     * 
     * @access public
     * @param string $path File path
     */
    public function __construct() { }

    /**
     * Getter docxStructure
     * @param string $format array or stream
     * @return mixed DOCX structure
     */
    public function getDocx($format) {
        return $docxStructure;
    }

    /**
     * Add new content to the DOCX
     * @param string $internalFilePath Path in the DOCX
     * @param string $content Content to be added
     */
    public function addContent($internalFilePath, $content)
    {
        $this->docxStructure[$internalFilePath] = $content;
    }

    /**
     * Add a new file to the DOCX
     * @param string $internalFilePath Path in the DOCX
     * @param string $file File path to be added
     */
    public function addFile($internalFilePath, $file)
    {
        $this->docxStructure[$internalFilePath] = file_get_contents($file);
    }

    /**
     * Delete content in the DOCX
     * @param string $internalFilePath Path in the DOCX
     */
    public function deleteContent($internalFilePath)
    {
        if (isset($this->docxStructure[$internalFilePath])) {
            unset($this->docxStructure[$internalFilePath]);
        }
    }

    /**
     * Get existing content from the DOCX
     * @param string $internalFilePath Path in the DOCX
     * @param string $content Content to be added
     * @return mixed File content or false
     */
    public function getContent($internalFilePath)
    {
        if (isset($this->docxStructure[$internalFilePath])) {
            return $this->docxStructure[$internalFilePath];
        }

        return false;
    }

    /**
     * Parse an existing DOCX
     * @param string $path File path
     */
    public function parseDocx($path)
    {
        $zip = new ZipArchive();

        if ($zip->open($path) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $this->docxStructure[$zip->getNameIndex($i)] = $zip->getFromName($fileName);
            }
        } else {
            throw new Exception('Error while trying to open the (base) template as a zip file');
        }
    }

    /**
     * Save docxStructure as ZIP
     * @param string $path File path
     * @param bool $forceFile Force DOCX as file, needed for charts when working with streams
     */
    public function saveDocx($path, $forceFile = false) {
        // check if the path has as extension
        if(substr($path, -5) !== '.docx') {
            $path .= '.docx';
        }

        // check if stream mode is true
        if (file_exists(dirname(__FILE__) . '/ZipStream.inc') && CreateDocx::$streamMode === true && $forceFile === false) {
            $docxFile = new ZipStream();

            foreach ($this->docxStructure as $key => $value) {
                $docxFile->addFile($key, $value);
            }
            $docxFile->generateStream($path);
        } else {
            $docxFile = new ZipArchive();

            // if dest file exits remove it to avoid duplicate content
            if (file_exists($path) && is_writable($path)) {
                unlink($path);
            }

            if ($docxFile->open($path, ZipArchive::CREATE) === TRUE) {
                foreach ($this->docxStructure as $key => $value) {
                    $docxFile->addFromString($key, $value);
                }

                $docxFile->close();

                if (!is_writable($path) || !is_readable($path)) {
                    die('Error while trying to write to ' . $path . ' please check write access.');
                }
            } else {
                throw new Exception('Error while trying to write to ' . $path);
            }
        }
    }
    
}