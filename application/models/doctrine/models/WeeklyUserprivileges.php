<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('WeeklyUserprivileges', 'SYSDAT');
class WeeklyUserprivileges extends BaseWeeklyUserprivileges
{

    public  function getPatientsOberaerzte($ipid){
        $epid=Pms_CommonData::getEpid($ipid);
        if ($epid){
            $assignid = Doctrine_Query::create()
                ->select('*')
                ->from('PatientQpaMapping')
                ->where("epid = '".$epid."'");

            $assignidexec = $assignid->execute();
            $assignidarray = $assignidexec->toArray();
        }
        $out=array();
        foreach ($assignidarray as $usertopatient){
            $uid=$usertopatient['userid'];
            if (!in_array($uid, $out)){
                if ($this->getUservalue($uid, 'oberarzt')){
                    $out[]=$uid;
                }
            }
        }
        return $out;
    }
    public  function getPatientsAssistenzaerzte($ipid){
        $epid=Pms_CommonData::getEpid($ipid);
        if ($epid){
            $assignid = Doctrine_Query::create()
                ->select('*')
                ->from('PatientQpaMapping')
                ->where("epid = '".$epid."'");

            $assignidexec = $assignid->execute();
            $assignidarray = $assignidexec->toArray();
        }
        $out=array();
        foreach ($assignidarray as $usertopatient){
            $uid=$usertopatient['userid'];
            if (!in_array($uid, $out)){
                if ($this->getUservalue($uid, 'assistenzarzt')){
                    $out[]=$uid;
                }
            }
        }
        return $out;
    }
    public function getUservalue ($userid, $key )
    {
        $key=strtolower($key);
        $groups_sql = Doctrine_Query::create()
            ->select('k,v')
            ->from('WeeklyUserprivileges')
            ->where('userid = ?', $userid)
            ->andWhere('k = ?', $key)
            ->andWhere('v = ?', 1)
            ->andWhere('isdelete = 0')
            ->limit(1);

        $groupsarray = $groups_sql->fetchArray();

        if ($groupsarray)
        {
            return 1;
        }
        return 0;
    }

    public function setPrevilegedUsers ( $userids, $clientid, $previlege)
    {
        $previlege=strtolower($previlege);
        $Q = Doctrine_Query::create()
            ->update('WeeklyUserprivileges')
            ->set('isdelete','1')
            ->where("k=?",$previlege)
            ->andWhere("clientid=?",$clientid);
        $Q->execute();

        foreach ($userids as $userid){
            if($userid>0){
                $lmup=new WeeklyUserprivileges();
                $lmup->userid=$userid;
                $lmup->clientid=$clientid;
                $lmup->k=$previlege;
                $lmup->v=1;
                $lmup->save();
            }
        }
    }

    public function getPrevilegedUsers (  $clientid, $previlege)
    {
        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('WeeklyUserprivileges')
            ->where('k = ?', $previlege)
            ->andWhere("clientid=?",$clientid)
            ->andWhere('v = ?', 1)
            ->andWhere('isdelete = 0');

        $groupsarray = $groups_sql->fetchArray();
        $returnarray=array();
        foreach ($groupsarray as $item){
            $returnarray[]=$item['userid'];
        }

        return $returnarray;
    }

}
?>

