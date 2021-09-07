<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('PatientWhitebox', 'MDAT');

class PatientWhitebox extends BasePatientWhitebox
{
    public function getCurrentPatientWhitebox($ipid)
    {
        $drop = Doctrine_Query::create()
            ->select("*")
            ->from('PatientWhitebox')
            ->where("ipid='" . $ipid . "'")
            ->andWhere("isdeleted=0")
            ->orderBy('create_date', 'desc')
            ->limit(1);
        $loc = $drop->fetchArray();

        if ($loc) {
            return $loc;
        }
    }

    public function setPatientWhiteboxesAsDeleted($ipid)
    {
        $Q = Doctrine_Query::create()->update('PatientWhitebox')
            ->set('isdeleted', '1')
            ->where("isdeleted = ?", 0)
            ->andWhere('ipid = ?', $ipid);
        $Q->execute();

        return true;

    }

    public function getPatientWhiteBoxHistory($ipid){
        $drop = Doctrine_Query::create()
            ->select("*")
            ->from('PatientWhitebox')
            ->where("ipid='" . $ipid . "'")
            ->orderBy('create_date', 'desc');
        $loc = $drop->fetchArray();

        if ($loc) {
            return $loc;
        }

    }



}