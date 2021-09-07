<?php
Doctrine_Manager::getInstance()->bindComponent('Mediprepare', 'MDAT');

/**
 * Class Mediprepare
 * ISPC-2804,Elena,18.02.2021
 */
class Mediprepare extends BaseMediprepare
{
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    public function getPreparations($ipids, $date){
        if(!is_array($ipids)){
            $ipids = [$ipids];
        }
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Mediprepare')
            ->whereIn("ipid", $ipids)
            ->andWhere('prepare_for_date=?', $date)
            ->andWhere("isdelete=0")
            ->orderBy('create_date', 'DESC')
        ;
        //print_r($drop->getSqlQuery());
        $droparray = $drop->fetchArray();
        return $droparray;

    }

    public function getPreparationsLog( $days, $archive = false){
     //DATE_ADD("2017-06-15 09:34:21", INTERVAL -3 HOUR)
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Mediprepare');
        if(!$archive){
            $drop->where('prepare_for_date>=DATE_ADD(NOW(), INTERVAL -'. $days . ' DAY)');
        }else{
            $drop->where('prepare_for_date<DATE_ADD(NOW(), INTERVAL -'. $days . ' DAY)');
        }

        $drop->andWhere("isdelete=0")
            ->orderBy('id', 'DESC')
        ;
        //print_r($drop->getSqlQuery());
        $droparray = $drop->fetchArray();
        return $droparray;

    }



}