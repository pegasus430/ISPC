<?php

/**
 * Generate a ZIP stream
 *
 * @category   Phpdocx
 * @package    streams
 * @copyright  Copyright (c) Narcea Producciones Multimedia S.L.
 *             (http://www.2mdc.com)
 * @license    phpdocx LICENSE
 * @link       https://www.phpdocx.com
 */
class ZipStream
{
    /**
     * @access private
     * @var string
     */
    private $cdrData = '';

    /**
     * @access private
     * @var int
     */
    private $filesEntries = 0;

    /**
     * @access private
     * @var int
     */
    private $offsetValue = 0;

    /**
     * @access private
     * @var string
     */
    private $zipData = '';

    /**
     * Construct
     * 
     * @access public
     */
    public function __construct() { }

    /**
     * Add a new file to the stream
     * @param string $internalFilePath Path in the DOCX
     * @param string $content Content to be added
     * @access public
     */
    public function addFile($internalFilePath, $content)
    {
        $currentDate = getdate();
        $timeCreatedAt = ($currentDate['year'] << 25 | $currentDate['mon'] << 21 | $currentDate['mday'] << 16 | $currentDate['hours'] << 11 | $currentDate['minutes'] << 5  | $currentDate['seconds'] >> 1);

        $contentGz = substr(gzcompress($content), 2, -4);

        $zipData = pack('VvvvVVVVvva' . strlen($internalFilePath) . 'a' . strlen($contentGz) . 'VVVV', 0x04034b50, 0x14, 0x08, 0x08, $timeCreatedAt, 0, 0, 0, strlen($internalFilePath), 0, $internalFilePath, $contentGz, 0x08074b50, crc32($content), strlen($contentGz), strlen($content));
        $this->zipData .= $zipData;
        $this->cdrData .= pack('VvvvvVVVVvvvvvVVa' . strlen($internalFilePath), 0x02014b50, 0x14, 0x14, 0x08, 0x08, $timeCreatedAt, crc32($content), strlen($contentGz), strlen($content), strlen($internalFilePath), 0, 0, 0, 0, 0x20, $this->offsetValue, $internalFilePath);
        $this->offsetValue += strlen($zipData);
        $this->filesEntries++;
    }

    /**
     * Return the stream
     * @param String $fileName File name path
     * @access public
     */
    public function generateStream($fileName)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Pragma: public');
        header('Cache-Control: public, must-revalidate');
        header('Content-Transfer-Encoding: binary');

        echo  $this->zipData .  $this->cdrData . pack('VvvvvVVva' . 0, 0x06054b50, 0, 0, $this->filesEntries, $this->filesEntries, strlen($this->cdrData), $this->offsetValue, 0, '');
    }
}