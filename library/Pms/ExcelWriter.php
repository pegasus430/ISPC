<?php

/**
 * Simple Class for Excel-File or CSV creation
 * Maria:: Migration CISPC to ISPC 22.07.2020 
 */
class Pms_ExcelWriter{
    public $xls_mode;
    public $xls_last_row;
    public $xls_last_col;

    public function init()
    {
        $this->xls_mode = 'xls';
        $this->xls_last_row = 0;
        $this->xls_last_col = 0;
    }



    //Some XLS-Functions
    public function beginFile($mode='xls')
    {
        $this->xls_mode = $mode;
        if ($this->xls_mode == 'xls') {
            $this->xlsBOF();
        }
    }
    private function xlsBOF()
    {
        if ($this->xls_mode == 'xls') {
            echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
            return;
        }
    }
    private function xlsEOF()
    {
        if ($this->xls_mode == 'xls') {
            echo pack("ss", 0x0A, 0x00);
            return;
        }
    }
    private function csvWrite($Row, $Col, $Value, $isLabel)
    {
        while ($this->xls_last_row < $Row) {
            $this->xls_last_row++;
            $this->xls_last_col = 0;
            echo("\n");
        }
        while ($this->xls_last_col < $Col) {
            $this->xls_last_col++;
            echo(";");
        }

        if ($isLabel) {
            $Value = str_replace('"', '""', $Value);
            $Value = '"' . $Value . '"';
            echo $Value;
        } else {
            if ($Value !== "") {
                echo $Value;
            }
        }
    }
    public function writeNumber($Row, $Col, $Value)
    {
        if ($this->xls_mode == 'xls') {
            if ($Value !== "") {
                echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
                echo pack("d", $Value);
                return;
            }
        }
        if ($this->xls_mode == 'csv') {
            $this->csvWrite($Row, $Col, $Value, false);
            return;
        }
    }
    public function writeLabel($Row, $Col, $Value)
    {
        if ($this->xls_mode == 'xls') {
            $Value = utf8_decode($Value);
            $L = strlen($Value);
            echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
            echo $Value;
            return;
        }
        if ($this->xls_mode == 'csv') {
            $this->csvWrite($Row, $Col, $Value, true);
            return;
        }
    }
    public function toBrowser($file)
    {
        if ($this->xls_mode == 'xls') {
            $this->xlsEOF();
            $fileName = $file . ".xls";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=" . $fileName);
            exit;
        }
        if ($this->xls_mode == 'csv') {
            $fileName = $file . ".csv";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/text");
            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=" . $fileName);
            exit;
        }
    }
}
