<?php
Doctrine_Manager::getInstance()->bindComponent('SystemsSyncStaging', 'SYSDAT');
class SystemsSyncStaging extends BaseSystemsSyncStaging
{
    public static function getPatients($clientid, $ipidthere=0){
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncStaging')
            ->Where('clientid=?',$clientid)
            ->andWhere('isdelete=0');

        if($ipidthere!==0){
            $sql->andWhere('ipid_there = ?',$ipidthere);
        }

        $patients = $sql->fetchArray();


        $out=array();

        foreach ($patients as $pat){
            if(strlen($pat['last_name'])>0){
                $out[$pat['ipid_there']]=$pat;
            }
        }

        return $out;
    }

    public static function mark_done($ipid_there){
        $cl_group = Doctrine_Query::create()
            ->update("SystemsSyncStaging")
            ->set('isdelete', 1)
            ->where("ipid_there=?",$ipid_there);
        $cl_group->execute();
    }


}
?>