<?php
//ISPC-2657, elena, 25.08.2020 (ELSA: Reaktionen)
//Maria:: Migration CISPC to ISPC 02.09.2020
Doctrine_Manager::getInstance()->bindComponent('SaeReactions', 'MDAT');

class SaeReactions extends BaseSaeReactions
{
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    public static function getDateKnowledgeOption(){

        return [
            'full',
            'year and month only',
            'year only'
        ];

    }

    public static function getPatientSaeReactions($ipid){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('SaeReactions')
            ->where("ipid=?", $ipid)
            ->andWhere("isdelete=0")
            ->orderBy('create_date', 'DESC')
        ;
        //print_r($drop->getSqlQuery());
        $droparray = $drop->fetchArray();
        return $droparray;

    }

    public function deleteSaeReaction(){
        $doctrineOperation = Doctrine_Query::create()
            ->update("SaeReactions")
            ->set('isdelete',1)
            ->where("id=?", $this->id);
        $doctrineOperation->execute();

    }



}