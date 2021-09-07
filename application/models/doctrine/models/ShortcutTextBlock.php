<?php
Doctrine_Manager::getInstance()->bindComponent('ShortcutTextBlock', 'SYSDAT');
// ISPC-2577, elena, 07.09.2020
class ShortcutTextBlock extends BaseShortcutTextBlock
{
    public function getShortcutTextBlocks(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;

        $userdata = Pms_CommonData::getUserData($userid);
        $groupid = $userdata[0]['groupid'];

        $course = Doctrine_Query::create()
            ->select('*')
            ->from('ShortcutTextBlock');
            //->where('groupid="' . $groupid . '"')
            //->andwhere('' . $prev . ' = 1');
        $cs = $course->execute();


        if($cs)
        {
            $csarr = $cs->toArray();
            return $csarr;
        }
    }

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

}