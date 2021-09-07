<?php
/**
 *  ISPC-2539 !!! Elena
 */
Doctrine_Manager::getInstance()->bindComponent('VerordnungSetStatusHistory', 'MDAT');

class VerordnungSetStatusHistory extends BaseVerordnungSetStatusHistory
{
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    /**
     * @param $sapv_verordnung_id
     * @param $ipid
     * @return array
     */
    public function getHistoryForVerordnung($sapv_verordnung_id, $ipid){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('VerordnungSetStatusHistory')
            //->where("ipid =  ? AND isdelete=0" , $ipid)
            ->where('ipid=?', $ipid)
            ->andWhere('sapv_verordnung_id=?', $sapv_verordnung_id)
            ->andWhere('isdelete="0"')
            ->orderBy("id ASC")
            ->fetchArray();

        if($drop)
        {
            return $drop;
        }
    }

    public static function setHistoryEntryDeleted($id, $ipid){
        $update_op = Doctrine_Query::create()
            ->update('VerordnungSetStatusHistory')


            ->set('isdelete','1')
            //->set('change_date', '?',date('Y-m-d H:i:s'))
            //->set('change_user', '?',$userid)
            ->where("ipid = ?", $ipid)
            ->andWhere("id = ?", $id)
            ->execute();

    }





}