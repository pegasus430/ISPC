<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('WeeklyMeetingusers', 'MDAT');

class WeeklyMeetingusers extends BaseWeeklyMeetingusers
{


    public function getRecentGroup(){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('WeeklyMeetingusers')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andwhere('create_user = ?', $logininfo->userid)
            ->orderBy('id DESC')
            ->limit(30);

        $groupsarray = $groups_sql->fetchArray();

        $returnarray = array();
        $tmid = $groupsarray[0]['meeting'];
        foreach ($groupsarray as $row){
            if ($row['meeting']==$tmid){
                $returnarray[]=$row['user'];
            }

        }

        return $returnarray;

    }

    public function getMeetingUsers($meetingid){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('WeeklyMeetingusers')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('meeting = ?',$meetingid);
        $groupsarray = $groups_sql->fetchArray();

        $returnarray = array();
        foreach ($groupsarray as $row){
            $returnarray[]=$row['user'];
        }

        return $returnarray;
    }

    public function setMeetingUsers($meetingid, $userarr){

        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $Q = Doctrine_Query::create()
            ->update('WeeklyMeetingusers')
            ->set('isdelete','1')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('meeting = ?',$meetingid);
        $result = $Q->execute();

        foreach ($userarr as $user){
            $tmu=new WeeklyMeetingusers();
            $tmu->user=$user;
            $tmu->meeting=$meetingid;
            $tmu->clientid=$clientid;
            $tmu->save();
        }

    }

}

?>