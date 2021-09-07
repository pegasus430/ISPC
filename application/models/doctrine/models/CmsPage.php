<?php

Doctrine_Manager::getInstance()->bindComponent('CmsPage', 'SYSDAT');

/**
 * Class CmsPage
 * ISPC-2562, elena, 24.08.2020 (page for videos and files)
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class CmsPage extends BaseCmsPage
{

    /**
     * returns last version of page
     *
     * @param $page_name
     * @return bool|float|int|mixed|string
     */
    public function getLastVersion($page_name){
        $pageOne =  $this->getTable()->createQuery()
            ->select('*')
            ->from('CmsPage')
            ->where('isdelete = 0')
            ->andWhere("page_name=?",$page_name )
            ->orderBy("id DESC")
            ->limit(1);
        $page = $pageOne->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

        return $page;

    }

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}