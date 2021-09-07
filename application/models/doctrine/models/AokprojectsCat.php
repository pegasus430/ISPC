<?php
Doctrine_Manager::getInstance()->bindComponent('AokprojectsCat', 'MDAT');
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 

class AokprojectsCat extends BaseAokprojectsCat
{

    /**
     * for table generating purposes only
     * helpful if the table have more than 10 fields
     *
     * @return string
     * @throws Doctrine_Export_Exception
     * @throws Doctrine_Table_Exception
     */
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}