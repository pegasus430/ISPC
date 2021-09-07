<?php
/**
* Maria:: Migration CISPC to ISPC 22.07.2020
*/
Doctrine_Manager::getInstance()->bindComponent('Selectboxlist', 'MDAT');

class Selectboxlist extends BaseSelectboxlist
{

    public function getList($listname, $associatedvalue = false)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $groups_sql = Doctrine_Query::create()
            ->select('listentry, associatedvalue')
            ->from('Selectboxlist')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('listname = ?', $listname)
            ->orderBy('id');
        $groupsarray = $groups_sql->fetchArray();

        $returnarray = array();

        foreach ($groupsarray as $row) {

            if ($associatedvalue) {
                $re = array($row['listentry'], $row['associatedvalue']);
            } else {
                $re = trim($row['listentry']);
            }
            $returnarray[] = $re;
        }

        return $returnarray;

    }

    public function getListOrDefault($listname, $associatedvalue = false)
    {
        $list = $this->getList($listname, $associatedvalue); //read the list of supplies
        if(!$list){
            $list = ClientConfig::getDefaultConfig($listname);
        }
        
        return $list;

    }

    public function replaceList($listname, $listarray)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $Q = Doctrine_Query::create()
            ->update('Selectboxlist')
            ->set('isdelete', '1')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('listname = ?', $listname);
        $Q->execute();

        foreach ($listarray as $item) {
            $sbl = new Selectboxlist();
            $sbl->clientid = $clientid;
            $sbl->listname = $listname;


            if (is_array($item)) {
                $sbl->listentry = $item[0];
                $sbl->associatedvalue = $item[1];
            } else {
                $sbl->listentry = $item;
            }

            $sbl->save();
        }
    }

}

?>
