<?php
Doctrine_Manager::getInstance()->bindComponent('TutorialFile', 'SYSDAT');
/**
 * Class TutorialFile
 * ISPC-2562, elena, 24.08.2020 (page for videos and files)
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class TutorialFile extends BaseTutorialFile
{
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}