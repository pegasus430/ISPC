<?php
//ISPC-2698, elena, 22.12.2020

Doctrine_Manager::getInstance()->bindComponent('ClientOptionsBlocks', 'SYSDAT');

class ClientOptionsBlocks extends BaseClientOptionsBlocks
{
    public static function getClientOptionsBlocks($clientid){

        $qblocks = Doctrine_Query::create()
            ->select('*')
            ->from('ClientOptionsBlocks')
            ->where('clientid=?' , $clientid);

        $qdata = $qblocks->execute();


        if($qdata)
        {
            $blocks = $qdata->toArray();
            return $blocks;
        }

        return false;
    }

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}