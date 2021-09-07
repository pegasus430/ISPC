<?php
Doctrine_Manager::getInstance()->bindComponent('AokprojectsTherapiesteuerung', 'MDAT');
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
class AokprojectsTherapiesteuerung extends BaseAokprojectsTherapiesteuerung
{
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }
}