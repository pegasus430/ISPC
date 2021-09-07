<?php
Doctrine_Manager::getInstance()->bindComponent('AokprojectsHerzinsuffienz', 'MDAT');
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 

class AokprojectsHerzinsuffienz extends BaseAokprojectsHerzinsuffienz
{

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}