<?php
Doctrine_Manager::getInstance()->bindComponent('TeamMeetingDetails', 'MDAT');

class TeamMeetingDetails extends BaseTeamMeetingDetails
{

    public function get_team_meeting_details($meeting = 0, $clientid = 0)
    {
        $team_meeting = Doctrine_Query::create()->select("*")
            ->from('TeamMeetingDetails')
            ->where("meeting = ?", $meeting)
            ->andWhere("client = ?", $clientid)
            ->andWhere("isdelete = 0")
            ->fetchArray();
        
        return $team_meeting;
    }
    
    /**
     * @author claudiu on 01.03.2018
     * @param unknown $ipids
     * @param number $meeting
     * @param number $clientid
     * @return void|Ambigous <multitype:, Doctrine_Collection>
     */
    public function get_ipid_details($ipids = array(), $meeting = 0, $clientid = 0)
    {
        if (empty($ipids)) {
            return;
        }
        
        if ( ! is_array($ipids)) {
            $ipids = array($ipids);
        }
        
        if (empty($clientid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }
        
        $team_meeting = Doctrine_Query::create()->select("*")
        ->from('TeamMeetingDetails')
        ->where("meeting = ?", $meeting)
        ->andWhere("client = ? ", $clientid)
        ->andWhereIn('patient', $ipids)
        ->andWhere("isdelete = 0")
        ->fetchArray();
    
        return $team_meeting;
    }


	//Maria:: Migration CISPC to ISPC 22.07.2020
    public static function get_patient_last_entries($ipids){

        $ipid_to_entries=array();

        foreach ($ipids as $ipid) {
            $team_meeting = Doctrine_Query::create()
                ->select("d.patient as ipid, d.meeting, d.problem, d.todo, d.targets") //IM-162,elena,03.12.2020
                ->from('TeamMeetingDetails d')
                ->where("d.patient=?",$ipid)
                ->andWhere("d.isdelete='0'")
                ->orderBy("d.create_date DESC")
                ->limit(5);

            $tmarray = $team_meeting->fetchArray();
            $last_mid=false;

            foreach($tmarray as $tm){
                if($last_mid==false || $last_mid==$tm['meeting']){
                    $last_mid=$tm['meeting'];
                    $ipid_to_entries[$ipid][]=array($tm['problem'], $tm['todo'], $tm['targets']);//IM-162,elena,03.12.2020
                }
            }

        }

        return $ipid_to_entries;
    }

}

?>